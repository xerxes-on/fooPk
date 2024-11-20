@php
    /**
     * @var \App\Models\User|null $user Came from app.blade as it is included
    */
// Main variables. User may be missing in some cases
$user = $user ?? auth()->user();
$userIsNotNull = !is_null($user);
$userIsNotAdmin = $userIsNotNull && $user->role_name === \App\Enums\Admin\Permission\RoleEnum::USER->value;
@endphp

@if(
    $userIsNotAdmin &&
    is_null($user->dietdata) &&
    $user->isQuestionnaireExist()
)
    @php
        $message = trans('common.formular.messages.not_confirmed');
        $config = ['container' => true];
    @endphp
    <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
@endif

@if(
	$userIsNotAdmin &&
    $user->canEditQuestionnaire() &&
    !in_array(Route::current()->uri, ['user/formular/edit', 'user/formular', 'user/questionnaire','user/questionnaire/edit',
    'user/layouts/choose_device'])
)
    @php
        $message = trans('common.formular.edit.notification');
        $config  = ['container' => true, 'closable'=> true,'type' => 'info', 'dismiss_duration' => config('formular.alert_dismiss_period'), 'dismiss_id' => 'formular'];
    @endphp
    <x-notification-alert :message="$message" :config="$config">
        <p style="text-align: right;">
            <a href="{{ route('questionnaire.edit') }}" style="color: #e6007e;">@lang('common.formular.edit.button')</a>
        </p>
    </x-notification-alert>
@endif

@if ($message = session('success'))
    @php
        $config = ['container' => true, 'closable'=> true, 'type' => 'success'];
    @endphp
    <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
@endif

@if ($message = session('error'))
    @php
        $config = ['container' => true, 'closable'=> true, 'type' => 'danger'];
    @endphp
    <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
@endif

@if ($message = session('warning'))
    @php
        $config = ['container' => true, 'closable'=> true, 'type' => 'warning'];
    @endphp
    <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
@endif

@if ($message = session('info'))
    @php
        $config = ['container' => true, 'closable'=> true, 'type' => 'info'];
    @endphp
    <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
@endif

@if ($errors->any())
    @php
        $message = trans('common.check_errors');
        $config = ['container' => true, 'closable'=> true, 'type' => 'danger'];
    @endphp
    <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
@endif
