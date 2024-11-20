<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Questionnaire;

use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\Questionnaire\QuestionDependency;
use App\Http\Controllers\API\APIBase;
use App\Http\Requests\API\Questionnaire\Create\PreviousQuestionFormRequest;
use App\Http\Requests\API\Questionnaire\Create\SaveAnswerFormRequest;
use App\Http\Requests\API\Questionnaire\Create\SearchIngredientFormRequest;
use App\Http\Requests\API\Questionnaire\Create\StartQuestionnaireCreationFormRequest;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireTemporary;
use App\Services\Users\UserService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Modules\Ingredient\Http\Resources\IngredientWithTagsSearchApiResource;
use Modules\Ingredient\Services\IngredientSearchService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * API controller for temporary questionnaire.
 * TODO: be aware of temporary solution to escape questioner loop....need to take care of it later
 * @package App\Http\Controllers\API
 */
final class QuestionnaireAnonymousAPIController extends APIBase
{
    /**
     * Initiate new questionnaire flow or resume existing one.
     *
     * @route POST /api/v1/questionnaire/start
     */
    public function start(StartQuestionnaireCreationFormRequest $request): JsonResponse
    {
        try {
            // Try to obtain users latest question user stopped on
            $questionData = QuestionnaireTemporary::with('question')
                ->whereFingerprint($request->fingerprint)
                ->latest()
                ->firstOrFail();
            /** @var \App\Services\Questionnaire\Question\BaseQuestionService $service */
            $service = new $questionData->question->service(
                $questionData,
                $request->lang,
                $request->fingerprint,
                QuestionnaireSourceRequestTypesEnum::API
            );
            if ($service->hasDependency()) {
                ## todo: uncomment when dependency loop fix
                //				try {
                $service->buildDependency($questionData, $request->fingerprint);
                //				} catch (QuestionDependency $e) {
                //					return $this->sendError('question_dependency_failed', $e->getMessage());
                //				}
            }
        } catch (ModelNotFoundException|QuestionDependency) {
            // get first questions and its data
            $question = QuestionnaireQuestion::active()->whereOrder(1)->firstOrFail();
            $service  = new $question->service(
                $question,
                $request->lang,
                $request->fingerprint,
                QuestionnaireSourceRequestTypesEnum::API
            );
            QuestionnaireTemporary::create([
                'questionnaire_question_id' => $question->id,
                'fingerprint'               => $request->fingerprint,
                'lang'                      => $request->lang,
            ]);
        }
        return $this->sendResponse($service->getResource(), trans('common.success', locale: $request->lang));
    }

    /**
     * Save answer and proceed to next question.
     *
     * @route POST /api/v1/questionnaire/save-proceed
     */
    public function saveAndProceed(SaveAnswerFormRequest $request): JsonResponse
    {
        //  save question and proceed to next
        QuestionnaireTemporary::updateOrCreate([
            'questionnaire_question_id' => $request->question->id,
            'fingerprint'               => $request->fingerprint,
            'lang'                      => $request->lang,
        ], [
            'answer' => $request->answer,
        ]);

        /**
         * Which question should we take next?
         * @var \App\Services\Questionnaire\Question\BaseQuestionService $oldQuestionService
         */
        $oldQuestionService = new $request->question->service(
            $request->question,
            $request->lang,
            $request->fingerprint,
            QuestionnaireSourceRequestTypesEnum::API
        );

        try {
            $nextQuestion = $oldQuestionService->defineNextQuestion(new QuestionnaireTemporary(), $request->fingerprint);
        } catch (ModelNotFoundException) {
            return $this->sendError('question_not_found');
        } catch (\App\Exceptions\NoMoreQuestions) {
            // start creating user at this point
            $email = app(UserService::class)->createNewUserFromTemporaryQuestionnaire($request->fingerprint);
            return $this->sendResponse([
                'email' => $email,
                'info'  => trans('auth.email_verification.required', ['email' => $email], $request->lang),
                'extra' => trans('questionnaire.info.temporary_saved', locale: $request->lang),
            ], 'email confirmation required');
        } catch (BindingResolutionException $e) {
            logError($e);
            return $this->sendError(
                'automatic_user_creation_failed',
                'Start creating user from api',
                ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
            );
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
            $request->lang,
            $request->fingerprint,
            QuestionnaireSourceRequestTypesEnum::API
        );

        if ($nextQuestionService->hasDependency()) {
            try {
                $nextQuestionService->buildDependency(new QuestionnaireTemporary(), $request->fingerprint);
            } catch (QuestionDependency $e) {
                ## TODO: temporary solution to escape questioner loop
                // get first questions and its data
                $question            = QuestionnaireQuestion::active()->whereOrder(1)->firstOrFail();
                $nextQuestionService = new $question->service(
                    $question,
                    $request->lang,
                    $request->fingerprint,
                    QuestionnaireSourceRequestTypesEnum::API
                );
                QuestionnaireTemporary::updateOrCreate(['questionnaire_question_id' => $question->id], [
                    'fingerprint' => $request->fingerprint,
                    'lang'        => $request->lang,
                ]);
                ## end temporary solution
                //				return $this->sendError('question_dependency_failed', $e->getMessage());
            }
        }

        return $this->sendResponse($nextQuestionService->getResource(), trans('common.success', locale: $request->lang));
    }

    /**
     * Search ingredients that should be excluded.
     *
     * @route POST /api/v1/questionnaire/search-ingredients
     */
    public function searchIngredients(SearchIngredientFormRequest $request, IngredientSearchService $service): JsonResponse
    {
        $requiredData = QuestionnaireTemporary::whereFingerprint($request->fingerprint)
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
                    $request->lang
                )
            ),
            trans('common.success', locale: $request->lang)
        );
    }

    /**
     * Retrieve previous question.
     *
     * @route POST /api/v1/questionnaire/previous-question
     */
    public function goToPreviousQuestion(PreviousQuestionFormRequest $request): JsonResponse
    {
        try {
            /** @var \App\Services\Questionnaire\Question\BaseQuestionService $service */
            $service = new $request->currentQuestion->service(
                $request->currentQuestion,
                $request->lang,
                $request->fingerprint,
                QuestionnaireSourceRequestTypesEnum::API
            );
            $previousQuestion        = $service->definePreviousQuestion(new QuestionnaireTemporary(), $request->fingerprint);
            $previousQuestionService = new $previousQuestion->service(
                $previousQuestion,
                $request->lang,
                $request->fingerprint,
                QuestionnaireSourceRequestTypesEnum::API
            );
            if ($previousQuestionService->hasDependency()) {
                $previousQuestionService->buildDependency(new QuestionnaireTemporary(), $request->fingerprint);
            }

            return $this->sendResponse($previousQuestionService->getResource(), trans('common.success', locale: $request->lang));
        } catch (QuestionDependency $e) {
            ## TODO: temporary solution to escape questioner loop
            // get first questions and its data
            $question                = QuestionnaireQuestion::active()->whereOrder(1)->firstOrFail();
            $previousQuestionService = new $question->service(
                $question,
                $request->lang,
                $request->fingerprint,
                QuestionnaireSourceRequestTypesEnum::API
            );
            QuestionnaireTemporary::updateOrCreate(['questionnaire_question_id' => $question->id], [
                'fingerprint' => $request->fingerprint,
                'lang'        => $request->lang,
            ]);
            return $this->sendResponse($previousQuestionService->getResource(), trans('common.success', locale: $request->lang));
            //			return $this->sendError('question_dependency_failed', $e->getMessage());
        } catch (ModelNotFoundException) {
            return $this->sendError(null, 'No previous question found');
        }
    }

    /**
     * Temporarily endpoint to delete all temporary questionnaire data.
     */
    public function deleteTemp(string $fingerprint): JsonResponse
    {
        if ('production' !== app()->environment()) {
            return $this->sendResponse(
                ['count' => QuestionnaireTemporary::whereFingerprint($fingerprint)->delete()],
                'deleted'
            );
        }
        return $this->sendError('not_allowed', status: ResponseAlias::HTTP_FORBIDDEN);
    }
}
