<div id="account_settings" class="tab-pane fade in active col-xs-12">
    <div class="row">
        <div class="col-xs-12">
            <h1>{{ trans('common.account_settings') }}</h1>
        </div>

        <div class="col-md-3">
            <a href="https://www.facebook.com/groups/1515386232105098/" target="_blank">
                <img src="{{ asset('/images/fb-join_'. app()->getLocale() .'.png') }}"
                     alt="{{ trans('common.fb-join') }}"
                     class="img-responsive"/>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-course-widget :course-data="(array)$aboChallengeData"></x-course-widget>

            <h2>@lang('common.profile_picture')</h2>
            <div class="row">
                <div class="col-sm-12">
                    {{--we send translations as property because reuse this component on admin side where $t does not required. to awoid annecessary $t including on admin side--}}
                    <avatar-uploader :profile-image-url="'{{ Auth::user()->avatar_url }}'"
                                     default-avatar-url="/images/icons/Account.svg"
                                     :trans="{{json_encode([
                            'common.are_you_sure_you_want_to_remove_profile_picture' => __('common.are_you_sure_you_want_to_remove_profile_picture'),
                            'common.yes' => __('common.yes'),
                            'common.no' => __('common.no'),
                        ]) }}"
                    />
                </div>
            </div>

            {{-- TODO: not translated--}}
            <h2>Email & Password</h2>
            {!! Form::open(['route' => 'user.settings.save', 'method' => 'POST', 'class' => 'account-settings']) !!}

            <div class="row">
                <div class="col-sm-12">
                    {!! Form::text('email', old('email', $user->email), [
                                'required' => true,
                                'class' => 'form-control account-settings_input',
                                'placeholder' => 'Email'
                            ])
                    !!}
                </div>

                <div class="col-sm-6">
                    {!! Form::text('first_name', old('first_name', $user->first_name), [
                                'required' => true,
                                'class' => 'form-control account-settings_input',
                                'placeholder' => trans('common.first_name')
                            ])
                    !!}
                </div>

                <div class="col-sm-6">
                    {!! Form::text('last_name', old('last_name', $user->last_name), [
                                'class' => 'form-control account-settings_input',
                                'placeholder' => trans('common.last_name')
                            ])
                    !!}
                </div>

                <div class="col-sm-12">
                    <label for="lang" class="account-settings_label">{{ trans('common.language') }}</label>
                    <select id="lang" class="form-control create-new_select" name="lang">
                        @foreach (config('translatable.locales') as $lang => $name)
                            <option @selected($user->lang === $lang) value="{{ $lang }}"> @lang("admin.filters.language.$lang")</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-12">
                    <label for="oldPas" class="account-settings_label">{{ trans('common.change_password') }}</label>
                    <input id="oldPas" type="password" name="old_password" class="form-control account-settings_input"
                           placeholder="{{ trans('common.current_password') }}"
                           autocomplete="off">
                </div>

                <div class="col-sm-12">
                    <input type="password" name="new_password" class="form-control account-settings_input"
                           placeholder="{{ trans('common.new_password') }}"
                           autocomplete="off">
                </div>
            </div>

            {!! Form::submit(trans('common.save'), ['class' => 'btn btn-tiffany']) !!}

            {{-- <div class="account-settings_small-title">{{ trans('common.account') }}</div> --}}

            {{-- <button class="btn btn-white" id="delete_account">{{ trans('common.delete_account') }}</button> --}}
            {!! Form::close() !!}
        </div>
        <div class="col-md-6">

            <subscriptions-list></subscriptions-list>

            <div class="content-panel" style="margin-top: 20px">
                <div class="account-settings_small-title" style="margin-bottom: 10px">
                    <strong style="font-size: 14px">{{trans('common.account')}}</strong>
                </div>
                <a id="upgrade" class="btn btn-white"
                   style="margin-right: 3%">{{ trans('common.upgrade_membership') }}</a>
                <a href="https://foodpunk.com/de/services/mitgliedschaft-verwalten/"
                   class="btn btn-white">{{ trans('common.cancel_membership') }}</a>
            </div>

            <div class="content-panel" slot="cancel-subscription-message" style="margin-top: 15px">
                {{ trans('common.reactivate_subscription_q') }} <a href="mailto:info@foodpunk.de">info@foodpunk.de</a>.
            </div>

            {{--            <img src="{{ asset('/images/settings.jpg') }}" alt="{{ trans('common.account_settings') }}" class="account-settings_image"/>--}}
        </div>
    </div>
</div>

@section('scripts')
    <script src="{{mix('vendor/sweetalert/sweetalert.min.js')}}"></script>
@endsection
