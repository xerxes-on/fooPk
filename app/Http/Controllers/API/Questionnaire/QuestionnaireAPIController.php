<?php

namespace App\Http\Controllers\API\Questionnaire;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionStatusEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\PublicException;
use App\Exceptions\Questionnaire\AlreadyAvailableForEdit;
use App\Exceptions\Questionnaire\NoChangesMade;
use App\Exceptions\Questionnaire\QuestionDependency;
use App\Http\Controllers\API\APIBase;
use App\Http\Requests\API\Questionnaire\Edit\NextQuestionFormRequest;
use App\Http\Requests\API\Questionnaire\Edit\PreviousQuestionFormRequest;
use App\Http\Requests\API\Questionnaire\Edit\SearchIngredientFormRequest;
use App\Http\Resources\Questionnaire\QuestionnairePreviewResource;
use App\Http\Traits\Questionnaire\CanPrepareQuestionnaireEditData;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use App\Services\Questionnaire\ClientQuestionnaire;
use App\Services\Questionnaire\Converter\Edit\QuestionnaireEditAPIConverterService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Modules\Ingredient\Http\Resources\IngredientWithTagsSearchApiResource;
use Modules\Ingredient\Services\IngredientSearchService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * API controller for users questionnaire.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @package App\Http\Controllers\API\Questionnaire
 */
final class QuestionnaireAPIController extends APIBase
{
    use CanPrepareQuestionnaireEditData;

    /**
     * Initiate new questionnaire flow or resume existing one.
     *
     * @route POST /api/v1/questionnaire/edit/start
     */
    public function startEditing(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Try to obtain users latest question user stopped on
            $questionData = QuestionnaireTemporary::with('question')
                ->whereFingerprint($user?->id)
                ->latest()
                ->firstOrFail();

            /** @var \App\Services\Questionnaire\Question\BaseQuestionService $service */
            $service = new $questionData->question->service(
                $questionData,
                $user?->lang,
                $user?->id,
                QuestionnaireSourceRequestTypesEnum::API_EDITING,
                $user
            );
            if ($service->hasDependency()) {
                try {
                    $service->buildDependency($questionData, $user?->id);
                } catch (QuestionDependency $e) {
                    return $this->sendError('question_dependency_failed', $e->getMessage());
                }
            }
        } catch (ModelNotFoundException) {
            // get user latest questionnaire id
            $lastQuestionnaireId = $user?->latestQuestionnaire()?->first()?->id;

            try {
                $questionnaire_answer = QuestionnaireAnswer::whereQuestionnaireId($lastQuestionnaireId)
                    ->whereQuestionnaireQuestionId(1)
                    ->firstOrFail();
            } catch (ModelNotFoundException) {
                /**
                 * @note This is a workaround for the case when user has no questionnaire BUT has to create one...
                 */
                $question = QuestionnaireQuestion::where([
                    ['status', QuestionnaireQuestionStatusEnum::ACTIVE->value],
                    ['order', 1],
                    ['is_editable', 1]
                ])->firstOrFail();
                $service = new $question->service(
                    $question,
                    $user?->lang,
                    $user?->id,
                    QuestionnaireSourceRequestTypesEnum::API_EDITING,
                    $user
                );

                return $this->sendResponse($service->getResource(), trans('common.success'));
                #return $this->sendError('user_does_not_have_questionnaire');
            }

            $service = new $questionnaire_answer->question->service(
                $questionnaire_answer,
                $user?->lang,
                $user?->id,
                QuestionnaireSourceRequestTypesEnum::API_EDITING,
                $user
            );
        }
        return $this->sendResponse($service->getResource(), trans('common.success'));
    }

    /**
     * Update answer and proceed to next question.
     *
     * @route POST /api/v1/questionnaire/edit/update-proceed
     */
    public function saveAndProceed(NextQuestionFormRequest $request): JsonResponse
    {
        //  save question and proceed to next
        QuestionnaireTemporary::updateOrCreate([
            'questionnaire_question_id' => $request->question->id,
            'fingerprint'               => $request->user->id,
            'lang'                      => $request->user->lang,
        ], [
            'answer' => $request->answer,
        ]);

        /**
         * Which question should we take next?
         * @var \App\Services\Questionnaire\Question\BaseQuestionService $oldQuestionService
         */
        $oldQuestionService = new $request->question->service(
            $request->question,
            $request->user->lang,
            $request->user->id,
            QuestionnaireSourceRequestTypesEnum::API_EDITING
        );

        try {
            $nextQuestion = $oldQuestionService->defineNextQuestion(new QuestionnaireTemporary(), $request->user->id);
        } catch (ModelNotFoundException) {
            return $this->sendError('question_not_found');
        } catch (\App\Exceptions\NoMoreQuestions) {
            // start creating user at this point
            return $this->sendError('no_more_question');
        } catch (InvalidArgumentException) {
            return $this->sendError(
                null,
                'Unable to create user automatically due to lack of data. Use designated api instead',
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        /** @var \App\Services\Questionnaire\Question\BaseQuestionService $nextQuestionService */
        $nextQuestionService = new $nextQuestion->service(
            $nextQuestion,
            $request->user->lang,
            $request->user->id,
            QuestionnaireSourceRequestTypesEnum::API_EDITING,
            $request->user
        );

        if ($nextQuestionService->hasDependency()) {
            try {
                $nextQuestionService->buildDependency(new QuestionnaireTemporary(), $request->user->id);
            } catch (QuestionDependency $e) {
                //				return $this->sendError('question_dependency_failed', $e->getMessage());
                // get user latest questionnaire id
                ## TODO: temporary solution to escape questioner loop
                $lastQuestionnaireId = $request->user?->latestQuestionnaire()?->first()?->id;

                try {
                    $questionnaire_answer = QuestionnaireAnswer::whereQuestionnaireId($lastQuestionnaireId)
                        ->whereQuestionnaireQuestionId(1)
                        ->firstOrFail();
                } catch (ModelNotFoundException) {
                    return $this->sendError('user_does_not_have_questionnaire');
                }

                $nextQuestionService = new $questionnaire_answer->question->service(
                    $questionnaire_answer,
                    $request->user?->lang,
                    $request->user?->id,
                    QuestionnaireSourceRequestTypesEnum::API_EDITING,
                    $request->user
                );
                ## end temporary solution
            }
        }


        return $this->sendResponse($nextQuestionService->getResource(), trans('common.success', locale: $request->user->lang));
    }

    /**
     * Retrieve previous question.
     *
     * @route POST /api/v1/questionnaire/edit/previous-question
     */
    public function goToPreviousQuestion(PreviousQuestionFormRequest $request): JsonResponse
    {
        try {
            /** @var \App\Services\Questionnaire\Question\BaseQuestionService $service */
            $service = new $request->currentQuestion->service(
                $request->currentQuestion,
                $request->user->lang,
                $request->user->id,
                QuestionnaireSourceRequestTypesEnum::API_EDITING,
                $request->user
            );
            $previousQuestion        = $service->definePreviousQuestion(new QuestionnaireTemporary(), $request->user->id);
            $previousQuestionService = new $previousQuestion->service(
                $previousQuestion,
                $request->user->lang,
                $request->user->id,
                QuestionnaireSourceRequestTypesEnum::API_EDITING,
                $request->user
            );
            if ($previousQuestionService->hasDependency()) {
                $previousQuestionService->buildDependency(new QuestionnaireTemporary(), $request->user->id);
            }

            return $this->sendResponse(
                $previousQuestionService->getResource(),
                trans('common.success')
            );
        } catch (QuestionDependency $e) {
            ## TODO: temporary solution to escape questioner loop
            $lastQuestionnaireId = $request->user?->latestQuestionnaire()?->first()?->id;

            try {
                $questionnaire_answer = QuestionnaireAnswer::whereQuestionnaireId($lastQuestionnaireId)
                    ->whereQuestionnaireQuestionId(1)
                    ->firstOrFail();
            } catch (ModelNotFoundException) {
                return $this->sendError('user_does_not_have_questionnaire');
            }

            $previousQuestionService = new $questionnaire_answer->question->service(
                $questionnaire_answer,
                $request->user?->lang,
                $request->user?->id,
                QuestionnaireSourceRequestTypesEnum::API_EDITING,
                $request->user
            );
            return $this->sendResponse(
                $previousQuestionService->getResource(),
                trans('common.success', locale: $request->user->lang)
            );
            ## end temporary solution
            #return $this->sendError('question_dependency_failed', $e->getMessage());
        } catch (ModelNotFoundException) {
            return $this->sendError(null, 'No previous question found');
        }
    }

    /**
     * Search ingredients that should be excluded.
     *
     * @route POST /api/v1/questionnaire/edit/search-ingredients
     */
    public function searchIngredients(SearchIngredientFormRequest $request, IngredientSearchService $service): JsonResponse
    {
        $requiredData = QuestionnaireTemporary::whereFingerprint($request->user->id)
            ->whereIn(
                'questionnaire_question_id',
                QuestionnaireQuestionIDEnum::userDietAndDiseases()
            )
            ->pluck('answer')
            ->flatMap(fn($item) => $item)
            ->toArray();

        return $this->sendResponse(
            new IngredientWithTagsSearchApiResource(
                $service->searchForIngredientsWithTags(
                    array_merge(($requiredData['allergies'] ?? []), ($requiredData['diseases'] ?? [])),
                    $requiredData['diets'] ?? [],
                    $request->search,
                    $request->user->lang
                )
            ),
            trans('common.success', locale: $request->lang)
        );
    }

    /**
     * Finish editing questionnaire by storing users answers.
     *
     * @route POST /api/v1/questionnaire/edit/finalize
     */
    public function finalizeEditing(Request $request, QuestionnaireEditAPIConverterService $service): JsonResponse
    {
        try {
            $service->convertFromTemporaryEditing($request->user());
            return $this->sendResponse(null, trans('common.success'));
        } catch (NoChangesMade $e) {
            return $this->sendError('no_changes', $e->getMessage());
        } catch (PublicException $e) {
            return $this->sendError('no_answers_to_save', $e->getMessage());
        }
    }

    /**
     * Get filled formular with answers.
     *
     * @route GET /api/v1/questionnaire/latest
     */
    public function getQuestionnaireData(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isQuestionnaireExist()) {
            return $this->sendError('formular_not_exists', trans('api.formular_404'));
        }

        $latestQuestionnaire        = $user->latestBaseQuestionnaire()->first();
        $latestQuestionnaireAnswers = $latestQuestionnaire
            ?->answers
            ->mapWithKeys(function (QuestionnaireAnswer $item) use ($user) {
                $service = new $item->question->service(
                    $item,
                    $user->lang,
                    user: $user
                );
                return [$item->question->slug => $service->getFormattedAnswer()];
            })
            ->toArray();
        $baseQuestions = QuestionnaireQuestion::baseOnly()->get(['id', 'slug']);
        // Some questions can be missing in questionnaire
        $latestQuestionnaireAnswers = array_replace(
            $baseQuestions->pluck('', 'slug')->toArray(),
            $latestQuestionnaireAnswers
        );

        return $this->sendResponse(
            new QuestionnairePreviewResource([
                'questions' => $baseQuestions,
                'answers'   => $latestQuestionnaireAnswers
            ]),
            trans('common.success')
        );
    }

    /**
     * Buy the possibility to edit questionnaire.
     *
     * @route POST /api/v1/questionnaire/edit/buy
     */
    public function buyEditing(Request $request, ClientQuestionnaire $service): JsonResponse
    {
        try {
            $service->processBuyEditing($request->user());
        } catch (PublicException $e) {
            $this->sendError(message: $e->getMessage(), status: ResponseAlias::HTTP_METHOD_NOT_ALLOWED);
        } catch (AlreadyAvailableForEdit) {
            // nothing to do here
        }
        return $this->startEditing($request);
    }

    /**
     * Retrieve user formular status
     *
     * @route GET /api/v1/questionnaire/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        return $this->sendResponse(
            [
                'is_exists'          => $user->getQuestionnaireExistsStatus(),
                'is_approved'        => $user->questionnaire_approved,
                'available_for_edit' => $user->canEditQuestionnaire(),
            ],
            trans('common.success')
        );
    }

    /**
     * Check when user can edit ones formular for free
     *
     * @route GET /api/v1/questionnaire/edit-period
     */
    public function checkEditPeriod(Request $request, ClientQuestionnaire $service): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user    = $request->user();
        $canEdit = $user->canEditQuestionnaire();
        return $this->sendResponse(
            [
                'can_edit' => $canEdit,
                'title'    => $canEdit ? trans('api.formular.edit_check_free.title') : trans('mobile_app.allert_to_change_data'),
                'body'     => $canEdit ?
                    trans('api.formular.edit_check_free.body') :
                    trans(
                        'common.free_change_formular_in_days',
                        [
                            'amount' => config('formular.formular_editing_price_foodpoints'),
                            'number' => $service->getFreeEditPeriod($request->user()),
                        ],
                    ),
                'button' => $canEdit ?
                    trans('api.formular.edit_check_free.button') :
                    trans('api.formular.edit_check_paid.button')
            ],
            ''
        );
    }
}
