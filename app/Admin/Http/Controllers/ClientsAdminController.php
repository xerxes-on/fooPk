<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use AdminSection;
use App\Admin\Http\Requests\Client\ClientCalculationsRequest;
use App\Admin\Http\Requests\Client\ClientCreateFormRequest;
use App\Admin\Http\Requests\Client\ClientFormApproveRequest;
use App\Admin\Http\Requests\Client\ClientFormRequest;
use App\Admin\Http\Requests\Client\ClientManageChargebeeSubscriptionRequest;
use App\Admin\Http\Requests\Subscriptions\StoreSubscriptionRequest;
use App\Admin\Services\Client\ClientNutrientsCalculationService;
use App\Admin\Services\Client\ClientService;
use App\Enums\Admin\Permission\PermissionEnum;
use App\Enums\FormularCreationMethodsEnum;
use App\Events\AdminActionsTaken;
use App\Helpers\Calculation;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadProfileImageRequest;
use App\Jobs\PreliminaryCalculation;
use App\Models\{AllergyTypes, Recipe, RecipeTag, Seasons, User, UserSubscription};
use App\Repositories\Formular as FormularRepository;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\Request as RequestFacade;
use Modules\Chargebee\Services\ChargebeeService;
use Modules\Internal\Models\AdminStorage;

/**
 * Clients controller.
 *
 * TODO: Class has 21 public methods.
 * TODO: Class has a coupling between objects value of 37.
 * TODO: Class has an overall complexity of 66 which is very high.
 * TODO: remove deprecated methods
 * @package App\Http\Controllers\Admin
 */
final class ClientsAdminController extends Controller
{
    public function create(ClientCreateFormRequest $request, ClientService $service): RedirectResponse
    {
        $userID = $service->create($request);
        return to_route('admin.model.edit', ['adminModel' => 'users', 'adminModelId' => $userID])
            ->with('success_message', trans('common.record_created_successfully'));
    }

    public function update(ClientFormRequest $request, ClientService $service): RedirectResponse
    {
        $userID = $service->update($request);
        return to_route('admin.model.edit', ['adminModel' => 'users', 'adminModelId' => $userID])
            ->with('success_message', trans('common.record_updated_successfully'));
    }

    /**
     * TODO:: move into chargebee module
     * Assign chargebee subscription to client
     * @throws \Exception
     */
    public function assignChargebeeSubscription(ClientManageChargebeeSubscriptionRequest $request): RedirectResponse|JsonResponse
    {
        $user = User::findOrFail($request->client_id);

        $subscriptionId = $request->input('chargebee_subscription_id');

        app(ChargebeeService::class)->configureEnvironment();

        $subscription = app(ChargebeeService::class)->getSubscriptionByUUID($subscriptionId);
        if (!empty($subscription)) {
            app(ChargebeeService::class)->updateUserSubscriptions($user, [$subscription]);
        }

        //refresh user's subscriptions scope
        app(ChargebeeService::class)->refreshUserSubscriptionData($user);
        app(ChargebeeService::class)->syncUserFoodpointsInvoices($user);

        AdminActionsTaken::dispatch();

        return $request->expectsJson() ?
            response()->json(['message' => trans('common.success')]) :
            redirect("/admin/users/$user->id/edit")->with('success_message', trans('common.record_updated_successfully'));
    }

    /**
     * Process client Calculations in Client Calculations tab
     */
    public function calculations(ClientCalculationsRequest $request, ClientNutrientsCalculationService $service): RedirectResponse
    {
        $responseKey   = 'success_message';
        $responseValue = trans('common.record_recalculated_successfully');
        try {
            $actionMethod = constant("\App\Enums\Admin\Client\ClientCalculationActionsEnum::$request->action")->value;
            $service->$actionMethod($request->validated());

            AdminActionsTaken::dispatch();
        } catch (\Throwable $e) {
            logError($e);
            $responseKey   = 'error_message';
            $responseValue = 'Unable to process calculations. Unknown action';
        } finally {
            return redirect()
                ->back()
                ->with($responseKey, $responseValue);
        }
    }

    /**
     * Show client formular
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     * @depreated
     */
    public function formularPage(int $id, FormularRepository $formularRepo): \Illuminate\View\View
    {
        $client = User::findOrFail($id);
        # get all active Questions
        $questions    = $formularRepo->getSurveyQuestion();
        $allergyTypes = AllergyTypes::with('allergies')->get();

        $answer = [];

        if ($client->isFormularExist()) {
            array_map(
                function ($item) use (&$answer) {
                    $answer[$item['survey_question_id']] = $item;
                },
                $client->formular->answers->toArray()
            );
        }

        return view('admin::client.formular', compact('client', 'questions', 'allergyTypes', 'answer'));
    }

    /**
     * Update user formular
     * @depreated
     */
    public function storeFormular(Request $request, FormularRepository $formularRepo): RedirectResponse
    {
        return redirect()->back()->with('error_message', 'Deprecated method');
        # get user
        $_client = User::findOrFail($request->client_id);

        # remove the token
        $values              = $request->except('_token', 'client_id');
        $values[1]['answer'] = parseDateString($values[1]['answer']);
        $values[4]['answer'] = parseDateString($values[4]['answer']);

        if (!empty($values)) {
            # get ingestions TODO: Is not used in code
            //$ingestions = Ingestion::active()->get()->pluck('key')->toArray();
            $isFormularExist = $_client->isFormularExist();

            list(
                'answersData'    => $answersData,
                'diffAnswer'     => $diffAnswer,
                'canRecalculate' => $canRecalculate
            ) = $formularRepo->getAnswerData($_client, $values, $isFormularExist);

            if ($diffAnswer || !$isFormularExist) {
                # clear formular cache
                $_client->forgetFormularCache();

                # create new formular
                $formular = $_client->formulars()->create(
                    [
                        'approved'        => false,
                        'creator_id'      => \Auth::id(),
                        'creation_method' => FormularCreationMethodsEnum::FREE
                    ]
                );

                $newAnswersData = [];
                foreach ($answersData as $key => $answer) {
                    $newAnswersData[] = [
                        'user_id'            => $_client->getKey(),
                        'survey_question_id' => $key,
                        'answer'             => $answer
                    ];
                }

                # sort formular answers by question Id
                usort($newAnswersData, fn($a, $b) => $a['survey_question_id'] - $b['survey_question_id']);

                # create formular answers
                $formular->answers()->createMany($newAnswersData);

                # check empty dietData
                if (empty($_client->dietdata) && $isFormularExist) {
                    /*if (!empty($_client->dietdata)) {
                        foreach ($ingestions as $type) {
                            $client['predefined_values']['ingestion'][$type]['percents'] = $_client->dietdata['predefined_values']['ingestion'][$type]['percents'];
                        }

                        $client['Kcal'] = $client['predefined_values']['Kcal'] = $_client->dietdata['Kcal'];
                        $client['KH'] = $client['predefined_values']['KH'] = $_client->dietdata['KH'];
                        $client['predefined_values']['ew_percents'] = $_client->dietdata['ew_percents'];
                    }*/

                    # calc dietData
                    if ($dietData = Calculation::calcUserNutrients($_client->id)) {
                        $_client->dietdata = $dietData;
                        $_client->save();
                    }
                }
            }
        }
        AdminActionsTaken::dispatch(); // TODO: later replace with users` formular cache actions

        return redirect('admin/users/' . $_client->id . '/edit')
            ->with('success_message', trans('common.formular_was_updated'));
    }

    /**
     * approved formular
     * @deprecated
     */
    public function approveFormular(ClientFormApproveRequest $request)
    {
        return redirect()->back()->with('error_message', 'Deprecated method');
        # get user Id
        $user = User::find($request->userId);

        # check empty dietData
        if ($request->approve) {
            if (empty($user->dietdata)) {
                if ($dietData = Calculation::calcUserNutrients($user->getKey())) {
                    $user->dietdata = $dietData;
                    $user->save();
                }
            }


            // TODO:: think about invalidation of recipes which user already has, do we need that?
            // no need to run full job ActionsAfterChangingFormular, only

            $jobStartHash = AdminStorage::generatePreliminaryJobHash($user->getKey());
            PreliminaryCalculation::dispatch($user, false, $jobStartHash)->onQueue('high')->delay(now()->addSeconds(5));
        }

        # approve formular
        $user->formular->update(['approved' => $request->approve]);

        # clear formular cache
        $user->forgetFormularCache();

        return response()->json(['success' => true]);
    }

    /**
     * @deprecated
     */
    public function toggleFormularVisibility(Request $request, int $clientId): JsonResponse|RedirectResponse
    {
        return redirect()->back()->with('error_message', 'Deprecated method');
        if (!$request->user()->hasPermissionTo(PermissionEnum::MANAGE_CLIENT_FORMULAR->value)) {
            redirect()->back()->with('error_message', 'Action forbidden');
        }

        try {
            $client   = User::findOrFail($clientId);
            $formular = $client->formulars()->firstOrFail();

            $visibilityWasForced = $formular->forced_visibility;

            $formular->forced_visibility = !$visibilityWasForced;
            $formular->save();
            $client->forgetFormularCache();
            //notify user if formular visibility has been forced
            //if (!$visibilityWasForced) {
            //temporary disabled
            //  $client->notify(new NotifyClientAboutAbilityToEditFormular);
            //}
        } catch (ModelNotFoundException $e) {
            return $request->expectsJson() ?
                response()->json(['success' => false, 'message' => $e->getMessage()]) :
                back()->with('error_message', $e->getMessage());
        }

        return $request->expectsJson() ?
            response()->json(['success' => true, 'message' => trans('common.success')]) :
            back()->with('message', trans('common.success'));
    }

    /**
     * set User Calculate Automatically
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setCalculateAutomatically(Request $request): JsonResponse
    {
        # get user
        $user = User::findOrFail($request->get('userId'));

        # get approve
        $approve = $request->get('approve') === 'true';

        # set calc_auto
        $user->update(['calc_auto' => $approve]);

        return response()->json(['success' => true]);
    }

    /**
     * get Formular Answers
     * TODO: move to service method upon refactoring
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @depreated
     */
    public function getFormularAnswers(Request $request): JsonResponse
    {
        // TODO: create correct form request and perform validation there
        # get user Id
        $userId = $request->get('userId');
        $user   = User::findOrFail($userId);

        # get formular Id
        $formularId = $request->get('formularId');

        if (empty($formularId)) {
            return response()->json(['success' => false]);
        }
        $formular = $user->formulars()->where('id', $formularId)->first();

        $count_and_time = [9, 10, 11];
        $disease        = [15, 16];

        $lastAnswers = [];

        if (is_null($formular?->answers)) {
            return response()->json(['success' => false]);
        }

        foreach ($formular->answers as $answer) {
            $objAnswers = json_decode($answer->answer);
            $answerData = [];

            if (is_object($objAnswers)) {
                foreach ($objAnswers as $name => $objAnswer) {
                    if (!is_null($objAnswer)) {
                        if (in_array($answer->survey_question_id, $count_and_time)) {
                            $answerData[] = trans('survey_questions.' . $name) . ': ' . $objAnswer;
                        } elseif ($name === 'no_matter') {
                            $answerData[] = trans('survey_questions.' . $answer->question->key_code . '_' . $name);
                        } elseif (in_array($answer->survey_question_id, $disease)) {
                            $answerData[] = $objAnswer;
                        } else {
                            $answerData[] = trans('survey_questions.' . $name);
                        }
                    }
                }
            } elseif ($answer->question->id === 1) {
                $answerData[] = parseDateString($answer->answer, 'd.m.Y');
            } elseif ($answer->question->id === 4 &&
                (
                    str_contains($answer->answer, '.') ||
                    str_contains($answer->answer, '-')
                )
            ) {
                $dateOfBirth  = Carbon::parse($answer->answer);
                $answerData[] = $dateOfBirth->format('d.m.Y') . ' (' . $dateOfBirth->age . ')';
            } else {
                $answerData[] = $answer->answer;
            }

            $lastAnswers[$answer->question->id] = [
                'key_code' => $answer->question->key_code,
                'answer'   => $answerData,
            ];
        }

        return response()->json(['success' => true, 'data' => $lastAnswers]);
    }

    /**
     * create Subscription from user
     */
    public function subscription(Request $request): RedirectResponse
    {
        $user            = User::findOrFail($request->id);
        $chargebeePlanId = $user->getLastChargebeePlanId();
        $user->createSubscription();
        $challengeId     = ChargebeeService::getChallengeIdByChargebeePlanId($chargebeePlanId, $user->lang);
        $user->addCourseIfNotExists($challengeId);
        return redirect("/admin/users/$user->id/edit")->with('success_message', trans('Subscription created!'));
    }

    /**
     * Subscription Edit
     */
    public function subscriptionEdit(StoreSubscriptionRequest $request): JsonResponse
    {
        // TODO: perform validation in form request
        # get subscription by id
        $subscriptionId = (int)$request->route('subscription');
        $subscription   = UserSubscription::findOrFail($subscriptionId);

        # get ends
        $endsAt = Carbon::parse($request->get('ends_at'));

        if ($subscription->created_at > $endsAt) {
            return response()->json(['success' => false]);
        }

        $subscription->ends_at = $endsAt;
        $subscription->save();

        return response()->json(['success' => true]);
    }

    /**
     * Subscription Stop
     */
    public function subscriptionStop(StoreSubscriptionRequest $request): JsonResponse
    {
        # get subscription by id
        $subscriptionId = (int)$request->route('subscription');
        $subscription   = UserSubscription::findOrFail($subscriptionId);

        $subscription->stopSubscription();

        return response()->json(['success' => true]);
    }

    /**
     * Subscription Delete
     */
    public function subscriptionDelete(StoreSubscriptionRequest $request): JsonResponse
    {
        # get subscription by id
        $subscriptionId = (int)$request->route('subscription');
        try {
            UserSubscription::findOrFail($subscriptionId)->delete();
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * add balance
     */
    public function deposit(Request $request): JsonResponse
    {
        // TODO: perform validation in form request
        # get user by id
        $user = User::findOrFail($request->get('userId'));

        # get amount
        $amount = $request->get('amount');

        # set user balance
        try {
            $user->deposit($amount, ['description' => "Deposit of $amount FoodPoints Admin"]);
        } catch (ExceptionInterface $e) {
            logError($e);
            return response()->json(['success' => false]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Withdraw balance
     */
    public function withdraw(Request $request): JsonResponse
    {
        // TODO: perform validation in form request
        # get user by id
        $user = User::findOrFail($request->get('userId'));

        # get amount
        $amount = $request->get('amount');

        # set user balance
        try {
            $user->withdraw($amount, ['description' => "Withdraw of $amount credits Admin"]);
        } catch (ExceptionInterface $e) {
            logError($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    public function uploadProfileImage(UploadProfileImageRequest $request, int $clientId): JsonResponse|RedirectResponse
    {
        // TODO: perform validation in form request

        try {
            $client          = User::findOrFail($clientId);
            $profileImageUrl = $client->uploadProfileImageFromRequest($request);
        } catch (\Throwable $exception) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => trans('Upload failed') . ' ' . $exception->getMessage()])
                : back()->with('error_message', trans('Upload failed') . ' ' . $exception->getMessage());
        }

        return $request->expectsJson() ?
            response()->json(
                [
                    'success'           => true,
                    'message'           => trans('common.profile_image_uploaded'),
                    'profile_image_url' => $profileImageUrl
                ]
            ) :
            back()->with(['success_message' => trans('common.profile_image_uploaded'), 'profile_image_url' => $profileImageUrl]);
    }

    public function deleteProfileImage(Request $request, int $clientId): JsonResponse|RedirectResponse
    {
        User::findOrFail($clientId)->deleteProfileImage();

        return $request->expectsJson() ?
            response()->json(['success' => true, 'message' => trans('common.success')]) :
            back()->with(['success_message' => trans('common.success')]);
    }

    /**
     * Retrieve a layout for `Randomize recipe settings popup`.
     */
    public function randomizeRecipeTemplate(): Response
    {
        return response()->view(
            'admin::client.randomizeRecipeTemplate',
            [
                'seasons'     => Seasons::get(['id'])->pluck('name', 'id')->toArray(),
                'recipesTags' => RecipeTag::forDistribution()->with('translations')->orderByTranslation('title')->get()
            ]
        );
    }

    /**
     * Search Ingredients by Select2 ajax request.
     */
    public function customSearch(): JsonResponse
    {
        $searchVal = RequestFacade::instance()->q;

        if (is_null($searchVal)) {
            return new JsonResponse([]);
        }

        $query = User::where('id', 'like', "%$searchVal%");

        // Format response for select2
        return new JsonResponse(
            $query->get()
                ->map(
                    fn(User $item) => [
                        'tag_name'    => "[$item->id] $item->full_name",
                        'id'          => $item->id,
                        'custom_name' => null,
                    ]
                )
        );
    }

    /**
     * Obtain ingestion data for client recipe tab.
     */
    public function getRecipesCountData(Request $request): JsonResponse
    {
        $user      = User::findOrFail($request->get('userId'));
        $countData = [];
        $user->allRecipes()->withOnly('ingestions:id')->get(['recipes.id'])->each(function (Recipe $recipe) use (&$countData) {
            $countData[custom_implode($recipe->ingestions->pluck('title')->toArray(), '/')][] = $recipe->id;
        });

        if ($countData === []) {
            return response()->json(['success' => true, 'message' => trans('common.total') . ': 0']);
        }

        uasort($countData, static fn($a, $b) => count($a) - count($b));
        $responseString = trans('common.total') . ':';
        foreach ($countData as $ingestion => $recipeIds) {
            $responseString .= " <b>$ingestion</b> - " . count($recipeIds) . ',';
        }

        return response()->json(['success' => true, 'data' => null, 'message' => rtrim($responseString, ',')]);
    }

    public function analytics(): RedirectResponse|\Illuminate\View\View
    {
        if (!\Auth::user()->hasPermissionTo(PermissionEnum::ANALYTICS->value)) {
            return redirect()->back();
        }

        $content = 'Analytics page (In development)';
        return AdminSection::view($content, 'Analytics');
    }
}
