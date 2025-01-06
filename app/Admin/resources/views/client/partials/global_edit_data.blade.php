{{-- pushing to vendor/laravelrus/sleepingowl/resources/views/default/_layout/base.blade.php --}}
@push('scripts')
    <script>
        const selectedRecipesStorage = 'selected_recipes';
        const selectedPopupRecipesStorage = 'selected_popup_recipes';
        window.FoodPunk = window.FoodPunk || {};
        window.FoodPunk.functions = {}
        window.FoodPunk.i18n = {
            wait: "@lang('admin.messages.wait')",
            inProgress: "@lang('admin.messages.in_progress')",
            noUserSelected: "@lang('admin.messages.no_user_selected')",
            noItemSelected: "@lang('admin.messages.no_item_selected')",
            saved: "@lang('admin.messages.saved')",
            randomizeRecipesSettings: "@lang('admin.messages.randomize_recipes_settings')",
            changeDateTitle: "@lang('course::common.change_date.title')",
            error: "@lang('admin.messages.error')",
            somethingWentWrong: "@lang('admin.messages.something_went_wrong')",
            defaultsMissing: "@lang('admin.filters.defaults.missing')",
            defaultsExist: "@lang('admin.filters.defaults.exist')",
            confirmation: "@lang('admin.messages.confirmation')",
            revertInfo: "@lang('admin.messages.revert_info')",
            revertWarning: "@lang('admin.messages.revert_warning')",
            subscriptionId: "@lang('admin.messages.subscription_id')",
            changesApplied: "@lang('admin.messages.changes_applied')",
            confirmDetails: "@lang('admin.messages.confirm_details')",
            subscriptionStopped: "@lang('admin.messages.subscription_stopped')",
            notificationInfoModalButton: "@lang('PushNotification::admin.notification_info_modal.button')",
            success: "@lang('admin.messages.success')",
            deleted: "@lang('admin.messages.deleted')",
            deleteAllRecipesUser: "@lang('admin.messages.delete_all_recipes_user')",
            noItem: "@lang('admin.messages.no_item')",
            recordRecalculatedSuccessfully: "@lang('common.record_recalculated_successfully')",
            deposit: "@lang('common.deposit')}",
            fpCountMessage: "@lang('admin.messages.fp_count_message')",
            infoWithdraw: "@lang('questionnaire.info.withdraw')",
            infoWithdrawNumber: "@lang('questionnaire.info.withdraw_number')",
        }
        window.FoodPunk.route = {
            datatableAsync: "{{route('admin.datatable.async')}}",
            addToUserRandom: "{{ route('admin.recipes.add-to-user-random') }}",
            randomizeRecipeTemplate: "{{ route('admin.client.randomize-recipe-template')}}",
            addToUser: "{{ route('admin.recipes.add-to-user') }}",
            checkCalculationStatus: "{{ route('admin.recipes.check-calculation-status', ['userId' => $clientID]) }}",
            courseDestroy: "{{ route('admin.client.course.destroy') }}",
            courseEdit: "{{ route('admin.client.course.edit') }}",
            assignChargebeeSubscription: "{{ route("admin.client.assign-chargebee-subscription" ) }}",
            questionnaireApprove: "{{ route('admin.client.questionnaire.approve') }}",
            questionnaireToggle: "{{ route('admin.clients.questionnaire.toggle') }}",
            questionnaireCompare: "{{ route('admin.client.questionnaire.compare') }}",
            subscriptionEdit: "{{ route("admin.client.subscription-edit", ":id") }}",
            subscriptionStop: "{{ route("admin.client.subscription-stop", ":id") }}",
            subscriptionDelete: "{{ route("admin.client.subscription-delete", ":id") }}",
            recipesDeleteByUser: "{{ route('admin.recipes.delete-by-user', ['recipeId' => '%', 'userId' => $clientID]) }}",
            deleteAllRecipes: "{{ route('admin.recipes.delete-all-recipes', [ 'userId'=> $clientID]) }}",
            deleteSelectedRecipes: "{{ route('admin.recipes.delete-selected-recipes') }}",
            recalculateToUser: "{{ route('admin.recipes.recalculate-to-user') }}",
            recipesCountData: "{{ route('admin.client.recipes.count-data') }}",
            searchRecipesPreview: "{{ route('admin.search-recipes.preview', ['recipeId' => '%', 'userId' => $clientID]) }}",
            recipesAddToUserRandom: "{{ route('admin.recipes.add-to-user-random') }}",
            clientDeposit: "{{ route('admin.client.deposit') }} ",
            clientWithdraw: "{{ route('admin.client.withdraw') }}",
            recipesGenerateToSub: "{{ route('admin.recipes.generate-to-subscription') }}",
            clientCalcAuto: "{{ route('admin.client.calc-auto') }}",
        }
        window.FoodPunk.pageInfo = {
            clientId: {{ $clientID }},
            activeChallenge: {{ $subscription ? $subscription->id : 0 }},
            canDeleteAllUserRecipes: '{{$canDeleteAllUserRecipes}}',
            hideRecipesRandomizer: '{{$hideRecipesRandomizer}}'
        }
    </script>
@endpush

@push('footer-scripts')
    <script src="//cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
@endpush
