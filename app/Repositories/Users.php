<?php

namespace App\Repositories;

use App\Http\Requests\FormularFormRequest as Request;
use App\Models;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * Repository for managing users.
 * TODO: Part of logic should be better refactored into service class
 * TODO: Logic methods related to specific user can be moved to User repo helper
 */
class Users
{
    /**
     * create User 'Try for free!'
     *
     * @param Request $request
     * @return \App\Models\User|null
     */
    public function createUserFromRequest(Request $request): Models\User|null
    {
        // TODO: move it to request handler
        # validate request
        $request->validate(
            [
                'email'      => 'required|email:rfc,dns|unique:users',
                'first_name' => 'string|nullable|alpha_num',
                'last_name'  => 'string|nullable|alpha_num',
                //'g-recaptcha-response' => 'recaptcha',
            ]
        );

        $customerEmail = $request->get('email');
        $firstName     = ($request->has('first_name') && !empty($request->get('first_name'))) ? $request->get(
            'first_name'
        ) : 'First Name';
        $lastName = ($request->has('last_name') && !empty($request->get('last_name'))) ? $request->get(
            'last_name'
        ) : 'Last Name';

        # check User exists
        $existUser = !((User::ofEmail($customerEmail)->count() == 0));

        if ($existUser) {
            abort(Response::HTTP_NOT_FOUND, trans('User with such email exists!'));
        }

        # find or create User
        $user = User::firstOrCreate(
            [
                'email' => $customerEmail,
            ],
            [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'password'   => \Hash::make('8Y5jXLBpi4vj'),
                'status'     => false
            ]
        )->assignRole('user');

        # ATTENTION --> refresh model to load attributes with default values
        return $user->fresh();

        /*# set end Subscription
        $endDate = \Carbon\Carbon::now()->addDays(7);

        # create subscription
        if (empty($user->challenge)) {
            \App\Models\UserChallenge::create([
                'user_id'      => $user->id,
                'challenge_id' => 1,
                'ends_at'      => $endDate,
                'active'       => true,
            ]);
        }*/

        # generate token from reset password
        /*$token = \Password::getRepository()->create($user);

        # send email
        \Mail::send('emails.welcome', ['user' => $user, 'token' => $token], function (Message $message) use ($user) {
            $message->from(config('mail.from.address'), config('mail.from.name'))
                ->to($user->email, $name = null)
                ->bcc(config('mail.from.address'), $name = null)
                ->bcc('andrey.rayfurak@lindenvalley.de', $name = null)
                ->subject('Try for free!');
        });

        // new version of mail sending over queue, top one is deprecated
        $mailObject = new \App\Mail\MailMailable('emails.welcome', ['user' => $user, 'token' => $token]);

        $mailObject->from(config('mail.from.address'), config('mail.from.name'))
                   ->to($user->email, $name = null)
                   ->bcc(config('mail.from.address'), $name = null)
                   ->subject('Los geht\'s!')
                   ->onQueue('emails');

        \Mail::queue($mailObject);

        */

        //\Auth::login($user);

        //return $user;
    }
}
