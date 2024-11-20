@push('scripts')
    <script>
        window.foodPunk = {
            admin: {
                i18n: {
                    notifications: {
                        dispatchTitle: '@lang('PushNotification::admin.dispatch_title')',
                    },
                    info: {
                        workInProgressWait: '@lang('common.work_in_progress_wait')',
                        workInProgress: '@lang('common.work_in_progress')',
                    },
                    success: '@lang('common.success')',
                    error: '@lang('common.error')',
                },
            },
        };
    </script>
@endpush
<ul class="nav navbar-nav ">
    <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu"><i class="fas fa-bars"></i></a>
    </li>
    @stack('navbar.left')

    @stack('navbar')
</ul>

<ul class="navbar-nav ml-auto">
    @stack('navbar.right')
</ul>
