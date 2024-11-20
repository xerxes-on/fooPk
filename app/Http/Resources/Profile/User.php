<?php

declare(strict_types=1);

namespace App\Http\Resources\Profile;

use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class User.
 *
 * Serve user profile data.
 *
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $chargebee_id
 * @property string $lang
 * @property string $avatar_url
 * @property string $notes
 * @property-read \App\Models\Diet $dietdata
 * @property string|null $main_goal
 * @property string $push_notifications
 * @property-read \Modules\Chargebee\ChargebeeSubscription $assignedChargebeeSubscriptions
 * @property-read \App\Models\UserSubscription[] $subscriptions
 *
 * @used-by \App\Http\Controllers\API\ProfileApiController::getProfileData()
 * @package App\Http\Resources
 */
final class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'email'        => $this->email,
            'chargebee_id' => $this->chargebee_id,
            'lang'         => $this->lang,
            'avatar'       => $this->avatar_url,
            'notes'        => $this->notes,
            'dietdata'     => empty($this?->dietdata) ?
                trans('common.empty_nutrients') :
                new Resources\DietData(collect($this->dietdata)),
            'subscriptions'     => ChargeBeeSubscription::collection($this->assignedChargebeeSubscriptions),
            'user_subscription' => Subscription::collection($this->subscriptions),
            'main_goal'         => is_string($this->main_goal) ?
                trans_fb('questionnaire.questions.' . QuestionnaireQuestionSlugsEnum::MAIN_GOAL . ".options.$this->main_goal") : null,
            'push_notifications' => $this->push_notifications,
        ];
    }
}
