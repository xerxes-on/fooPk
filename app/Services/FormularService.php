<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FormularCreationMethodsEnum;
use App\Models\User;
use Carbon\Carbon;

/**
 * General formular service.
 * @deprecated
 * @package App\Services
 */
final class FormularService
{
    public function getFreeEditPeriod(User $user): int
    {
        $formular       = $user->formular;
        $now            = Carbon::now();
        $freeEditPeriod = config('formular.period_of_free_editing_in_days');
        $daysLeft       = $freeEditPeriod - $formular?->created_at?->diffInDays($now) % $freeEditPeriod;

        if ($formular->creation_method === FormularCreationMethodsEnum::PAID) {
            $previousFormular = $user->formulars()->where('created_at', '<', $formular->created_at)->limit(1)->first();
            $daysLeft         = $freeEditPeriod - $previousFormular?->created_at?->diffInDays($now) % $freeEditPeriod;
        }

        return $daysLeft;
    }
}
