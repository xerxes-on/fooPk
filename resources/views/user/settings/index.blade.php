@extends('layouts.app')

@section('title', trans('common.account_settings'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <!-- Nav content list -->
                <ul class="content-links" role="navigaion">
                    <li class="content-links_item active">
                        <a data-toggle="tab" href="#account_settings">@lang('common.settings')</a>
                    </li>
                    <li class="content-links_item">
                        <a data-toggle="tab" href="#questionnaire">@lang('questionnaire.page_title')</a>
                    </li>
                    <li class="content-links_item">
                        <a data-toggle="tab" href="#challenge">@lang('common.nutritional_value')</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="tab-content">
                    @include('user.settings.components.accountSettingsTab')
                    @include('user.settings.components.questionnaireTab')
                    @include('user.settings.components.challengeTab')
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{mix('vendor/sweetalert/sweetalert.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('#delete_account').on('click', function (e) {
                e.preventDefault();
                if (confirm("{{trans('common.delete_account_confirmation')}}") &&
                    confirm("{{trans('common.delete_account_second_question')}}")) {
                    // /user/settings
                    $.ajax({
                        type: 'POST',
                        url: "{{route('user.settings.delete')}}",
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                        },
                        success: function (resp) {
                            alert("{{trans('common.request_has_been_sent')}}");
                        },
                        error: function (err) {
                            console.log(err);
                        },
                    });
                }

            });

            $('#upgrade').on('click', function (e) {
                e.preventDefault();
                if (confirm("{{trans('common.upgrade_account_confirmation')}}")) {
                    // /user/upgrade_membership
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('user.settings.sendemail') }}",
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                        },
                        success: function (resp) {
                            alert("{{trans('common.request_has_been_sent')}}");
                        },
                        error: function (err) {
                            console.log(err);
                        },
                    });
                }
            });

            $('input[name=old_password]').on('input', setRequired);
            $('input[name=new_password]').on('input', setRequired);

            function setRequired() {
                const old_password = $('input[name=old_password]');
                const new_password = $('input[name=new_password]');

                if (old_password.val().length || new_password.val().length) {
                    old_password.attr('required', true);
                    new_password.attr('required', true);
                } else {
                    old_password.attr('required', false);
                    new_password.attr('required', false);
                }
            }
        });
    </script>
@append