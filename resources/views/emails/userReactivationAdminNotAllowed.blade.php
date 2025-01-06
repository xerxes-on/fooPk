@include('emails.header')

<p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
    @lang('email.userReactivationAdminNotAllowed.line1', ['userId' => $user->id,'userEmail'=>$user->email,'reactivationDate'=>$reactivationDate ])
</p>

<p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
    @lang('email.userReactivationAdminNotAllowed.line2', ['orderId' => $orderId, 'firstName'=>$user->first_name, 'lastName'=>$user->last_name ])
</p>

<p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
    @lang('email.userReactivationAdminNotAllowed.line3', ['planId' => $planId ])
</p>

<p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
    @lang('email.userReactivationAdminNotAllowed.conditions'): {!! $reactivationConditions !!}
</p>

@include('emails.footer')