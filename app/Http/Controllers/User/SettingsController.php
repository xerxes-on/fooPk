<?php

namespace App\Http\Controllers\User;

use App\Enums\Questionnaire\QuestionnaireQuestionSlugsEnum;
use App\Enums\Questionnaire\QuestionnaireSourceRequestTypesEnum;
use App\Exceptions\PublicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UserSettingsFormRequest;
use App\Http\Requests\UploadProfileImageRequest;
use App\Mail\MailMailable;
use App\Models\Admin;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use App\Services\Users\UserAvatarService;
use App\Services\Users\UserProfileService;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

/**
 * User settings controller.
 *
 * @package App\Http\Controllers\User
 */
final class SettingsController extends Controller
{
    /**
     * Settings page.
     */
    public function index(): View|Factory
    {
        $user                       = \Auth::user();
        $latestQuestionnaire        = $user->latestBaseQuestionnaire()->first();
        $latestQuestionnaireAnswers = $latestQuestionnaire
            ?->answers
            ->mapWithKeys(function (QuestionnaireAnswer $item) use ($user) {
                $service = new $item->question->service(
                    $item,
                    $user->lang,
                    questionnaireType: QuestionnaireSourceRequestTypesEnum::WEB,
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

        $goal = $latestQuestionnaireAnswers[QuestionnaireQuestionSlugsEnum::MAIN_GOAL] ?? '';

        return view('user.settings.index', [
            'user'                => $user,
            'dietdata'            => !empty($user->dietdata) ? $user->dietdata : false,
            'latestQuestionnaire' => $latestQuestionnaire,
            'baseQuestions'       => $baseQuestions,
            'latestAnswers'       => $latestQuestionnaireAnswers,
            'goal'                => $goal
        ]);
    }

    /**
     * Upload profile image.
     */
    public function uploadProfileImage(
        UploadProfileImageRequest $request,
        UserAvatarService         $service
    ): JsonResponse|RedirectResponse {
        return $service->processUpdate($request);
    }

    /**
     * Delete profile image.
     */
    public function deleteProfileImage(Request $request, UserAvatarService $service): JsonResponse|RedirectResponse
    {
        return $service->processDelete($request);
    }

    /**
     * Save settings.
     */
    public function save(UserSettingsFormRequest $request, UserProfileService $service): Redirector|RedirectResponse
    {
        try {
            $service->processStore($request);
            return redirect()
                ->back()
                ->with('success', trans('common.information_updated'));
        } catch (PublicException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete user account.
     * 6.11.2018 - just send email to admin
     * TODO: maybe mark user as inactive, logout user and remove its password to prevent login?
     */
    public function deleteSelf(): Response|ResponseFactory
    {
        $admins = Admin::role('admin')->get();
        $emails = [];

        foreach ($admins as $admin) {
            $emails[] = $admin->email;
        }

        $mailObject = new MailMailable('emails.deleteUser', ['user' => \Auth::user()]);
        $mailObject
            ->from('extern@foodpunk.de', 'Foodpunk Portal')
            ->to($emails)
            ->subject(trans('common.delete_client_request'))
            ->onQueue('emails');
        \Mail::queue($mailObject);

        return response(['status' => 'ok']);
    }

    /**
     * Send email to admin for membership upgrade.
     */
    public static function sendEmail(): Response|ResponseFactory
    {
        $user      = \Auth::user();
        $userid    = $user->id;
        $useremail = $user->email;
        $time      = Carbon::now();
        send_raw_admin_email(
            "User wants to upgrade to yearly membership: $useremail (#$userid) at $time",
            'Membership upgrade'
        );
        return response(['message' => trans('common.request_has_been_sent')]);
    }
}
