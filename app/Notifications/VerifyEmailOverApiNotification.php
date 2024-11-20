<?php

namespace App\Notifications;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Throwable;

/**
 * Send verification email for newly created user registered over API.
 *
 * @package App\Notifications
 */
final class VerifyEmailOverApiNotification extends VerifyEmail
{
    public function __construct(public User $user)
    {
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param string $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage())
            ->greeting(trans('email.greeting_user', ['name' => $this->user->full_name], $this->user->lang))
            ->subject(trans('email.verification.title', locale: $this->user->lang))
            ->line(trans('email.verification.verification_line', locale: $this->user->lang))
            ->action(trans('email.verification.action_link', locale: $this->user->lang), $url)
            ->line(trans('email.verification.no_further_action', locale: $this->user->lang));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $linkData = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Build the Dynamic Link URL
        $dynamicLinkUrl = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=' . env('GOOGLE_API_KEY');
        $data           = [
            'dynamicLinkInfo' => [
                'domainUriPrefix' => env('GOOGLE_DYNAMIC_LINKS_DOMAIN'),
                'link'            => $linkData,
                'androidInfo'     => [
                    'androidPackageName' => env('GOOGLE_ANDROID_APP_ID')
                ],
                'iosInfo' => [
                    'iosBundleId' => env('GOOGLE_IOS_APP_ID')
                ]
            ]
        ];
        $responseLink = null;
        try {
            $client       = new Client();
            $response     = $client->post($dynamicLinkUrl, ['json' => $data]);
            $responseData = json_decode($response->getBody(), true);
            $responseLink = $responseData['shortLink'] ?? null;
        } catch (Throwable $e) {
            logError($e, [
                'user' => $notifiable->getKey(),
                'url'  => $dynamicLinkUrl,
                'data' => json_encode($data)
            ]);
        }

        return is_null($responseLink) ? $linkData : (string)$responseLink;
    }
}
