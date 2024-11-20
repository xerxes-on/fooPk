<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\Client\ClientFormApproveRequest;
use App\Admin\Http\Requests\Client\ClientFormCompareRequest;
use App\Admin\Http\Requests\Client\ClientFormToggleRequest;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Events\AdminActionsTaken;
use App\Events\UserQuestionnaireChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Questionnaire\StoreAnswerFromUserFormRequest;
use App\Http\Traits\Questionnaire\CanPrepareQuestionnaireEditData;
use App\Jobs\PreliminaryCalculation;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use App\Models\User;
use App\Services\Questionnaire\Converter\Edit\QuestionnaireEditWEBConverterService;
use App\Services\Questionnaire\QuestionnaireDiffSearcher;
use App\Services\Users\UserNutrientsService;
use App\Services\Users\UserRecipeCalculationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Internal\Models\AdminStorage;

/**
 * Admin controller for clients Questionnaire.
 *
 * @package App\Admin\Http\Controllers
 */
final class ClientQuestionnaireAdminController extends Controller
{
    use CanPrepareQuestionnaireEditData;

    /**
     * Set questionnaire as approved.
     */
    public function approveQuestionnaire(ClientFormApproveRequest $request, UserNutrientsService $nutrientsService): JsonResponse
    {
        try {
            $user = User::whereId($request->userId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        # approve formular
        $user->latestQuestionnaire()->update(['is_approved' => $request->approve]);

        UserQuestionnaireChanged::dispatch($request->clientId);

        if ($request->approve) {
            $nutrientsService->checkAndUpdateDietData($user);
            // TODO:: think about invalidation of recipes which user already has, do we need that?
            // no need to run full job ActionsAfterChangingFormular, only
            // TODO:: review job, if formular unapproved, do not need to run preliminary

            // TODO:: if ingestions has been changed need to regenerate subscription
            if ($user->hasChangedMealPerDay) {
                app(UserRecipeCalculationService::class)->processMealPerDayChanges($user->id);
            }
            $jobStartHash = AdminStorage::generatePreliminaryJobHash($user->getKey());
            PreliminaryCalculation::dispatch($user, false, $jobStartHash)->onQueue('high')->delay(now()->addSeconds(5));
            UserQuestionnaireChanged::dispatch($user->id);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Set questionnaire as ready to edit by client.
     */
    public function toggleQuestionnaireVisibility(ClientFormToggleRequest $request): JsonResponse
    {
        try {
            $client  = User::whereId($request->clientId)->firstOrFail();
            $records = $client->latestQuestionnaire()->update(['is_editable' => $request->is_editable]);
            UserQuestionnaireChanged::dispatch($request->clientId);

            //			$client->forgetFormularCache();
            //notify user if questionnaire visibility has been forced
            //if (!$visibilityWasForced) {
            //temporary disabled TODO: should we?
            //  $client->notify(new NotifyClientAboutAbilityToEditFormular);
            //}
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        if ((int)$records === 0) {
            return response()->json(['success' => false, 'message' => 'Unable to toggle questionnaire visibility']);
        }

        return response()->json(['success' => true, 'message' => trans('common.success')]);
    }

    /**
     * Return answers for questionnaire.
     */
    public function getQuestionnaireAnswers(ClientFormCompareRequest $request): JsonResponse
    {
        try {
            $user     = User::whereId($request->clientId)->firstOrFail();
            $formular = $user
                ->questionnaire()
                ->where('id', $request->questionnaireId)
                ->with('answers', function (HasMany $relation) {
                    $relation
                        ->whereIn(
                            'questionnaire_answers.questionnaire_question_id',
                            QuestionnaireQuestion::baseOnly()->pluck('id')->toArray()
                        )
                        ->distinct()
                        ->with('question');
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        $requiredAnswers = $formular
            ->answers
            ->mapWithKeys(function (QuestionnaireAnswer $item) use ($user) {
                $service = new $item->question->service(
                    $item,
                    auth()->user()->lang,
                    questionnaireType: QuestionnaireSourceRequestTypesEnum::WEB,
                    user: $user
                );
                return [$item->question->slug => $service->getFormattedAnswer()];
            })
            ->toArray();

        return response()->json(['success' => true, 'data' => $requiredAnswers]);
    }

    /**
     * Show create new client questionnaire page.
     */
    public function create(Request $request, int $clientId): RedirectResponse|View
    {
        try {
            $user      = User::whereId($clientId)->firstOrFail();
            $questions = [];
            QuestionnaireQuestion::baseOnly()
                ->get()
                ->each(function (QuestionnaireQuestion $item) use ($user, &$questions, $request) {
                    $service = new $item->service(
                        $item,
                        auth()->user()->lang,
                        questionnaireType: QuestionnaireSourceRequestTypesEnum::WEB,
                        user: $user
                    );
                    $questions[] = $service->getResource()->toArray($request);
                })
                ->toArray();
        } catch (\Throwable $e) {
            return redirect()->back()->with(['error_message' => $e->getMessage()]);
        }
        return view(
            'admin::client.questionnaire',
            [
                'client'    => $user,
                'questions' => $questions
            ]
        );
    }

    /**
     * Show edit latest client questionnaire page.
     */
    public function edit(Request $request, int $clientId): RedirectResponse|View
    {
        try {
            $user      = User::whereId($clientId)->firstOrFail();
            $questions = $this->prepareQuestionnaireEditData($user, $request, QuestionnaireSourceRequestTypesEnum::WEB_EDITING);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with(['error_message' => $e->getMessage()]);
        }

        return view(
            'admin::client.questionnaire',
            [
                'client'    => $user,
                'questions' => $questions
            ]
        );
    }

    /**
     * Store answers from client questionnaire.
     */
    public function store(
        StoreAnswerFromUserFormRequest       $request,
        QuestionnaireDiffSearcher            $diffService,
        QuestionnaireEditWEBConverterService $converterService,
        UserNutrientsService                 $nutrientsService
    ): RedirectResponse {
        if (empty($request->answers)) {
            return redirect()->back()->with('error_message', 'Questionnaire was not saved due to missing answers');
        }

        // if questionnaire hasn't existed before and nothing was changed
        if ($request->client->latestBaseQuestionnaire()->exists() && !$diffService->findDiffWithLatestQuestionnaireOverWeb($request->client, $request->answers)) {
            return redirect()->back()->with('error', 'Nothing was changed');
        }

        try {
            $converterService->convertFromWeb($request->client, $request->answers, $request->user()?->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', $e->getMessage());
        }

        $nutrientsService->checkAndUpdateDietData($request->client);

        UserQuestionnaireChanged::dispatch($request->client->id);
        AdminActionsTaken::dispatch();
        return redirect(route('admin.model.edit', ['adminModel' => 'users', 'adminModelId' => $request->client->id]))
            ->with('success_message', trans('common.formular_was_updated'));
    }
}
