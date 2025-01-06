@include('emails.header')

<p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
    @lang('email.userReactivationAdminSuccess.meal_plan_has_been_reactivated', ['userId' => $user->id,'userEmail'=>$user->email,'reactivationDate'=>$reactivationDate ])
</p>

<p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
    @lang('email.userReactivationAdminSuccess.current_plan_id', ['planId' => $planId ])
</p>

<p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">
    @lang('email.userReactivationAdminSuccess.list_of_added_courses', ['addedCoursesStr' => $addedCoursesStr ])
</p>

@include('emails.footer')