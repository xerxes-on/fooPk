<?php

namespace Modules\FlexMeal\Services;

use App\Enums\MealtimeEnum;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Service to validate whether user requested specific meal change on the flexmeal.
 * Changing mealtime of the flexmeal requires confirmation if the flexmeal is already planned.
 * Due to business requirements, changing mealtime from breakfast to lunch or dinner and vice versa will lead
 * to removal of the flexmeal from the plan.
 *
 * @package Modules\FlexMeal\Services
 */
class FlexmealUpdateValidationService
{
    private string $hashSource;

    private MealtimeEnum $existingMeal;
    private MealtimeEnum $newMeal;

    private bool $isMealPlanReplacementRequired = false;

    public function __construct(
        private readonly int     $flexmealId,
        string                   $newMeal,
        string                   $oldMeal,
        private readonly User    $user,
        private readonly ?string $signature,
    ) {
        $this->hashSource   = "$this->flexmealId $newMeal {$this->user->id}";
        $this->existingMeal = MealtimeEnum::tryFromValue($oldMeal);
        $this->newMeal      = MealtimeEnum::tryFromValue($newMeal);
    }

    public function performConfirmationCheck(): void
    {
        $emptySignature = empty($this->signature);

        $this->isMealPlanReplacementRequired = $this->isExistInMealPlan() && ($this->isReplacingFromBreakfast() || $this->isReplacingToBreakfast());

        if ($emptySignature && $this->isMealPlanReplacementRequired) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => '',
                'data'    => [
                    'code'         => 'flexmeal_confirmation_require',
                    'title'        => trans('common.warning_title'),
                    'text'         => trans('meal_plan.replacement.confirmation_alert'),
                    'confirm_text' => trans('meal_plan.buttons.confirm'),
                    'cancel_text'  => trans('meal_plan.buttons.cancel'),
                    'signature'    => Hash::make($this->hashSource),
                ],
            ], ResponseAlias::HTTP_ACCEPTED));
        }

        if (!$emptySignature && !Hash::check($this->hashSource, $this->signature)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => '',
                'data'    => null,
                'errors'  => ['signature' => 'Invalid signature'],
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY));
        }
    }

    public function isMealPlanReplacementRequired(): bool
    {
        return $this->isMealPlanReplacementRequired;
    }

    private function isExistInMealPlan(): bool
    {
        return $this->user->plannedFlexmeals()->where('flexmeal_id', $this->flexmealId)->count() > 0;
    }

    private function isReplacingFromBreakfast(): bool
    {
        return $this->existingMeal === MealtimeEnum::BREAKFAST && in_array($this->newMeal, [MealtimeEnum::LUNCH, MealtimeEnum::DINNER], true);
    }

    private function isReplacingToBreakfast(): bool
    {
        return $this->newMeal === MealtimeEnum::BREAKFAST && in_array($this->existingMeal, [MealtimeEnum::LUNCH, MealtimeEnum::DINNER], true);
    }
}
