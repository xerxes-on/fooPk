<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Events\UserQuestionnaireChanged;
use App\Exceptions\NoMoreQuestions;
use App\Exceptions\PublicException;
use App\Exceptions\Questionnaire\AlreadyAvailableForEdit;
use App\Exceptions\Questionnaire\QuestionDependency;
use App\Http\Requests\Questionnaire\NextQuestionRequest;
use App\Http\Requests\Questionnaire\PreviousQuestionRequest;
use App\Http\Requests\Questionnaire\StoreAnswerFromUserFormRequest;
use App\Http\Traits\Questionnaire\CanPrepareQuestionnaireEditData;
use App\Models\QuestionnaireQuestion;
use App\Services\Questionnaire\ClientQuestionnaire;
use App\Services\Questionnaire\Converter\Create\QuestionnaireCreateWEBConverterService;
use App\Services\Questionnaire\Converter\Edit\QuestionnaireEditWEBConverterService;
use App\Services\Questionnaire\QuestionnaireDiffSearcher;
use App\Services\Questionnaire\QuestionnaireUserSession;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Modules\Ingredient\Http\Resources\IngredientWithTagsSearchApiResource;
use Modules\Ingredient\Services\IngredientSearchService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Questionnaire controller
 *
 * TODO: Probably should separate create and edit features into different controllers.
 * @package App\Http\Controllers
 */
final class QuestionnaireController extends Controller
{
    use CanPrepareQuestionnaireEditData;

    /**
     * Show questions for authenticated user
     */
    public function create(Request $request): RedirectResponse|Factory|View
    {
        // block if exists questionnaire
        // case:: back button in firefox doesn't reload page and resave partly questionnaire
        $user = $request->user();
        if (is_null($user) || (!is_null($user) && $user->isQuestionnaireExist())) {
            return redirect()->route('recipes.list')->with('warning', trans('common.formular_already_exist'));
        }

        return view('questionnaire.questions');
    }

    /**
     * Initiate new questionnaire flow or resume existing one.
     *
     * @route POST /user/questionnaire/start
     */
    public function start(Request $request, QuestionnaireUserSession $questionnaireUserSession): JsonResponse
    {
        $user = $request->user();
        if (is_null($user)) {
            return $this->sendError('user_not_found', 'User not found');
        }

        try {
            // Try to obtain user latest question user stopped on
            $questionData = $questionnaireUserSession->getLastQuestion($user->id);

            /** @var \App\Services\Questionnaire\Question\BaseQuestionService $service */
            $service = new $questionData->service(
                $questionData,
                $user->lang,
                $user->id,
                QuestionnaireSourceRequestTypesEnum::WEB,
                $user
            );

            if ($service->hasDependency()) {
                $service->buildDependency($questionData, $user->id);
            }
        } catch (ModelNotFoundException) {
            $question = QuestionnaireQuestion::active()->whereOrder(1)->firstOrFail();
            $service  = new $question->service(
                $question,
                $user->lang,
                $user->id,
                QuestionnaireSourceRequestTypesEnum::WEB,
                $user
            );
        } catch (QuestionDependency $e) {
            return $this->sendError('question_dependency_failed', $e->getMessage());
        }

        return $this->sendResponse($service->getResource(), trans('common.success'));
    }

    /**
     * Save answer and proceed to next question.
     *
     * @route POST /user/questionnaire/next
     */
    public function next(
        NextQuestionRequest      $request,
        QuestionnaireUserSession $questionnaireUserSession,
    ): JsonResponse {
        $user = $request->user();
        if (is_null($user)) {
            return $this->sendError('user_not_found', 'User not found');
        }

        try {
            $questionnaireUserSession->updateOrCreateQuestionRecord(
                $request->question->id,
                $user->id,
                $request->answer
            );

            $oldQuestionService = new $request->question->service(
                $request->question,
                $user->lang,
                $user->id,
                QuestionnaireSourceRequestTypesEnum::WEB,
                $user
            );

            $nextQuestion = $oldQuestionService->defineNextQuestion(new QuestionnaireQuestion(), $user->id);

            $nextQuestionService = new $nextQuestion->service(
                $nextQuestion,
                $user->lang,
                $user->id,
                QuestionnaireSourceRequestTypesEnum::WEB,
                $user
            );

            if ($nextQuestionService->hasDependency()) {
                $nextQuestionService->buildDependency(new QuestionnaireQuestion(), $user->id);
            }
        } catch (ModelNotFoundException) {
            return $this->sendError('question_not_found');
        } catch (NoMoreQuestions) {
            return $this->sendError(
                'no_more_question',
                'no more question',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (BindingResolutionException $e) {
            logError($e);
            // TODO:: what to do in that case ?
            return $this->sendError($e->getMessage());
        } catch (InvalidArgumentException $e) {
            // TODO:: what to do in that case ?
            return $this->sendError($e->getMessage());
        } catch (QuestionDependency $e) {
            return $this->sendError('question_dependency_failed', $e->getMessage());
        }

        return $this->sendResponse($nextQuestionService->getResource(), trans('common.success'));
    }

    /**
     * Retrieve previous question.
     *
     * @route POST /user/questionnaire/previous
     */
    public function goToPreviousQuestion(PreviousQuestionRequest $request): JsonResponse
    {
        $user = $request->user();
        if (is_null($user)) {
            return $this->sendError('user_not_found', 'User not found');
        }

        try {
            /** @var \App\Services\Questionnaire\Question\BaseQuestionService $service */
            $service = new $request->currentQuestion->service(
                $request->currentQuestion,
                $user->lang,
                $user->id,
                QuestionnaireSourceRequestTypesEnum::WEB,
                $user
            );

            $previousQuestion        = $service->definePreviousQuestion(new QuestionnaireQuestion(), $user->id);
            $previousQuestionService = new $previousQuestion->service(
                $previousQuestion,
                $user->lang,
                $user->id,
                QuestionnaireSourceRequestTypesEnum::WEB,
                $user
            );

            if ($previousQuestionService->hasDependency()) {
                $previousQuestionService->buildDependency(new QuestionnaireQuestion(), $user->id);
            }

            return $this->sendResponse($previousQuestionService->getResource(), trans('common.success'));
        } catch (QuestionDependency $e) {
            return $this->sendError('question_dependency_failed', $e->getMessage());
        } catch (ModelNotFoundException) {
            // TODO: maybe translate
            return $this->sendError(null, 'No previous question found');
        }
    }

    /**
     * Search ingredients that should be excluded.
     *
     * @route POST /user/questionnaire/ingredients/search
     */
    public function searchIngredients(
        Request                  $request,
        IngredientSearchService  $service,
        QuestionnaireUserSession $questionnaireUserSession
    ): JsonResponse {
        $user = $request->user();
        if (is_null($user)) {
            return $this->sendError('user_not_found', 'User not found');
        }

        $requiredData = $questionnaireUserSession->getAnswersByQuestionIds(
            QuestionnaireQuestionIDEnum::userDietAndDiseases(),
            $user->id
        );

        return $this->sendResponse(
            new IngredientWithTagsSearchApiResource(
                $service->searchForIngredientsWithTags(
                    array_merge(($requiredData['allergies'] ?? []), ($requiredData['diseases'] ?? [])),
                    $requiredData['diets'] ?? [],
                    $request->q,
                    $user->lang
                )
            ),
            trans('common.success')
        );
    }

    /**
     * Form for editing existing answers.
     */
    public function edit(Request $request): RedirectResponse|View|Factory
    {
        /**@var \App\Models\User $user */
        $user = $request->user();

        if ($user->isQuestionnaireExist() && !$user->canEditQuestionnaire()) {
            return redirect()->route('recipes.list')->with('warning', trans('common.formular_already_exist'));
        }

        try {
            $questions = $this->prepareQuestionnaireEditData($user, $request, QuestionnaireSourceRequestTypesEnum::WEB_EDITING);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return view(
            'questionnaire.edit',
            [
                'client'    => $user,
                'questions' => $questions
            ]
        );
    }

    /**
     * Store answers from first time client questionnaire.
     */
    public function storeFirstQuestionnaire(
        Request                                $request,
        QuestionnaireCreateWEBConverterService $converterService
    ): RedirectResponse|JsonResponse {

        // case:: back button in firefox doesn't reload page and resave partly questionnaire
        $user = $request->user();
        if ($user->isQuestionnaireExist()) {
            return redirect()->route('recipes.list')->with('warning', trans('common.formular_already_exist'));
        }

        // block if exists questionnaire
        try {
            $converterService->convertFromWebSession($user);
            return redirect()->route('recipes.list')->with('success', trans('common.formular_user_updated'));
        } catch (\Throwable $e) {
            return $this->sendError('error', $e->getMessage());
        }
    }

    /**
     * Store answers from client questionnaire.
     */
    public function store(
        StoreAnswerFromUserFormRequest       $request,
        QuestionnaireDiffSearcher            $diffService,
        QuestionnaireEditWEBConverterService $converterService
    ): RedirectResponse {
        if (empty($request->answers)) {
            return redirect()->back()->with('error', 'Questionnaire was not saved due to missing answers');
        }

        if (!$diffService->findDiffWithLatestQuestionnaireOverWeb($request->client, $request->answers)) {
            return redirect()->back()->with('error', 'Nothing was changed');
        }

        try {
            $converterService->convertFromWeb($request->client, $request->answers);
        } catch (PublicException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('recipes.list')->with('success', trans('common.formular_user_updated'));
    }

    /**
     * Buy option to edit existing answers
     */
    public function buyEditing(Request $request, ClientQuestionnaire $service): RedirectResponse
    {
        try {
            $user = $request->user();
            $service->processBuyEditing($user);
            UserQuestionnaireChanged::dispatch($user->id);
        } catch (PublicException $e) {
            return back()->withErrors($e->getMessage());
        } catch (AlreadyAvailableForEdit) {
            // nothing to do here
        }
        return redirect()->route('questionnaire.edit');
    }

    /**
     * Check when user can edit ones formular for free
     *
     * @route GET /user/formular/check
     */
    public function checkEditPeriod(
        Request             $request,
        ClientQuestionnaire $service
    ): JsonResponse {
        return response()->json(
            trans(
                'common.free_change_formular_in_days',
                [
                    'amount' => config('formular.formular_editing_price_foodpoints'),
                    'number' => $service->getFreeEditPeriod($request->user())
                ]
            )
        );
    }
}
