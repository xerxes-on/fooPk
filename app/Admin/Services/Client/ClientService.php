<?php

declare(strict_types=1);

namespace App\Admin\Services\Client;

use App\Admin\Http\Requests\Client\ClientCreateFormRequest;
use App\Admin\Http\Requests\Client\ClientFormRequest;
use App\Enums\Admin\Permission\RoleEnum;
use App\Events\AdminActionsTaken;
use App\Helpers\Calculation;
use App\Mail\MailMailable;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Course\Enums\CourseId;
use Modules\Ingredient\Jobs\SyncUserExcludedIngredientsJob;
use Modules\Internal\Models\AdminStorage;

/**
 * Service for handling client creation and update.
 *
 * @package App\Admin\Services\Client
 */
final class ClientService
{
    public function create(ClientCreateFormRequest $request): int
    {
        $clientData = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'status'     => $request->status,
        ];
        if (!empty($request->new_password)) {
            $clientData['password'] = Hash::make($request->new_password);
        }

        $user = User::create($clientData)->syncRoles(RoleEnum::USER->value);

        $this->markUserAsTested($user, $request, 'mark_tested');
        $this->handlePreventMealPlanGenerationFlag($request, $user);
        $this->maybeAttachNewClient($request->user(), $user->id);
        $this->maybeAddSubscription($user);

        # generate token for reset password
        $token = \Password::getRepository()->create($user);

        # send email into queue
        $mailObject = new MailMailable('emails.welcome', ['client' => $user, 'token' => $token]);
        $mailObject->from(config('mail.from.address'), config('mail.from.name'))
            ->to($user->email)
            ->bcc(config('mail.from.address'))
            ->subject('Los geht’s! / Let’s go!')
            ->onQueue('emails');
        \Mail::queue($mailObject);

        return $user->id;
    }

    public function update(ClientFormRequest $request): int
    {
        $clientData = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'status'     => $request->has('status') ? 1 : 0,
        ];

        if ($request->has('lang') && !empty($request->get('lang'))) {
            $clientData['lang'] = $request->lang;
        }

        if ($request->has('allow_marketing')) {
            $clientData['allow_marketing'] = $request->allow_marketing;
        }

        if (!empty($request->new_password)) {
            $clientData['password'] = Hash::make($request->new_password);
        }

        # find or create User
        $user = User::updateOrCreate(
            [
                'id' => $request->route('id'),
            ],
            $clientData
        )->syncRoles(RoleEnum::USER->value);

        $this->markUserAsTested($user, $request, 'mark_tested');

        $excludedIngredients = $request->get('excluded_ingredients');
        $excludedRecipes     = $request->get('excluded_recipes', []);
        $bulkExclusions      = $request->get('bulkExclusions');
        $user->saveExcludedIngredients($excludedIngredients);
        $user->saveExcludedRecipes($excludedRecipes);
        $user->bulkExclusions()->sync($bulkExclusions);

        // as fix when user's excluded ingredients or any other user's data has been updated
        $user->touch();

        SyncUserExcludedIngredientsJob::dispatch($user);

        # check empty dietData
        $this->calculateDietData($user);

        AdminActionsTaken::dispatch();

        return $user->id;
    }

    private function markUserAsTested(User $user, Request $request, string $testProperty): void
    {
        if ($request->has($testProperty) && $request->$testProperty) {
            $user->assignRole(RoleEnum::TEST_USER->value);
        } elseif ($request->has($testProperty) && !$request->$testProperty) {
            $user->removeRole(RoleEnum::TEST_USER->value);
        }
    }

    private function maybeAttachNewClient(Admin $admin, int $userID): void
    {
        if ($admin->hasRole(RoleEnum::CONSULTANT->value)) {
            $admin->liableClients()->attach($userID);
        }
    }

    /**create subscription or add subscription if its only first time*/
    private function maybeAddSubscription(User $user): void
    {
        if (!empty($user->subscription)) {
            return;
        }
        $user->setFirstTimeCourse();
        // cover adding proper course CourseId::getFirstTimeChallengeId($user->lang)

        $user->addCourseIfNotExists(CourseId::getFirstTimeChallengeId($user->lang));
        $user->createSubscription();
    }

    private function handlePreventMealPlanGenerationFlag(ClientCreateFormRequest $request, User $user): void
    {
        $data = $request->automatic_meal_generation ? 'on' : 'off';

        if ($request->user()->hasRole(RoleEnum::CONSULTANT->value)) {
            $data = 'off';
        }

        AdminStorage::create(['key' => "meal_plan_generation_$user->id", 'data' => $data]);
    }

    private function calculateDietData(User $user): void
    {
        if (!empty($user->dietdata)) {
            return;
        }

        if (!$user->getQuestionnaireExistsStatus()) {
            return;
        }

        $dietData = Calculation::calcUserNutrients($user->id);

        if ($dietData) {
            $user->dietdata = $dietData;
            $user->save();
        }

    }
}
