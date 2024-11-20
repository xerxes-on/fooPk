<?php

namespace App\Http\Traits\Questionnaire;

use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use App\Models\User;
use Illuminate\Http\Request;

trait CanPrepareQuestionnaireEditData
{
    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function prepareQuestionnaireEditData(
        User                                $user,
        Request                             $request,
        QuestionnaireSourceRequestTypesEnum $questionnaireType = QuestionnaireSourceRequestTypesEnum::API
    ): array {

        $latestQuestionnaire = $user->latestBaseQuestionnaireWithAllRequiredAnswers()->first();

        // if hasn't been found latest full questionnaire - show latest as it is
        if (empty($latestQuestionnaire)) {
            $latestQuestionnaire = $user->latestBaseQuestionnaire()->firstOrFail();
        }


        $questions = [];
        $latestQuestionnaire
            ->answers
            ->each(function (QuestionnaireAnswer $item) use (&$questions, $request, $user, $questionnaireType) {
                $service = new $item->question->service(
                    $item,
                    $request->user()->lang,
                    questionnaireType: $questionnaireType,
                    user: $user
                );
                $questions[] = $service->getResource()->toArray($request);
            });
        // some questions can be missing, so we need to add them manually
        QuestionnaireQuestion::baseOnly()->get()->each(
            function (QuestionnaireQuestion $item) use (&$questions, $request, $user, $questionnaireType) {
                if (!in_array($item->slug, array_column($questions, 'slug'))) {
                    $service = new $item->service(
                        $item,
                        $request->user()->lang,
                        questionnaireType: $questionnaireType,
                        user: $user
                    );
                    $questions[] = $service->getResource()->toArray($request);
                }
            }
        );
        usort($questions, static fn(array $first, array $second) => $first['order'] <=> $second['order']);

        return $questions;
    }
}
