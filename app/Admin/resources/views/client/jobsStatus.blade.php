{{-- pushing to vendor/laravelrus/sleepingowl/resources/views/default/_layout/inner.blade.php --}}
@push('content.top')
    <div class="recalculation-progress">
        <p>Next Recalculation jobs check update in: <b id="js-check-refresh">0</b></p>
    </div>
    <div class="alert alert-warning" id="calculation-status" role="alert" style="display: none"></div>
@endpush
@push('scripts')
    <script>
        let $tableRecipes;
        const selectedRecipesStorage = 'selected_recipes';
        const selectedPopupRecipesStorage = 'selected_popup_recipes';
        window.FoodPunk = {};
        window.FoodPunk.i18n ={
            wait: '{{ __('admin.messages.wait')}}',
            inProgress: '{{ __('admin.messages.in_progress')}}',
            noUserSelected: "{{ __('admin.messages.no_user_selected')}}",
            noItemSelected: "{{ __('admin.messages.no_item_selected')}}",
            saved: '{{ __('admin.messages.saved')}}',
            randomizeRecipesSettings: '{{ __('admin.messages.randomize_recipes_settings') }}',
            changeDateTitle: "{{ __('course::common.change_date.title')}}",
            error:"{{ __('admin.messages.error') }}",
            somethingWentWrong:'{{__('admin.messages.something_went_wrong')}}',
            defaultsMissing: '{{ __('admin.filters.defaults.missing') }}',
            defaultsExist: '{{ __('admin.filters.defaults.exist') }}',
            confirmation: '{{ __('admin.messages.confirmation')}}',
            revertInfo: '{{ __('admin.messages.revert_info')}}',
            revertWarning: '{{ __('admin.messages.revert_warning')}}',
            subscriptionId: '{{ __('admin.messages.subscription_id') }}',
            changesApplied: '{{ __('admin.messages.changes_applied')}}',
            confirmDetails: '{{ __('admin.messages.confirm_details')}}',
            subscriptionStopped: '{{ __('admin.messages.subscription_stopped')}}',
            notificationInfoModalButton: "{{ __('PushNotification::admin.notification_info_modal.button')}}",
            success: '{{ __('admin.messages.success')}}',
            deleted: '{{ __('admin.messages.deleted')}}',
            deleteAllRecipesUser: '{{ __('admin.messages.delete_all_recipes_user')}}',
            noItem: '{{ __('admin.messages.no_item')}}',
            recordRecalculatedSuccessfully: '{{ __('common.record_recalculated_successfully')}}',
            deposit: '{{ __('common.deposit')}}',
            csCountMessage: '{{ __('common.cs_count_message')}}',
            infoWithdraw: "{{ __('questionnaire.info.withdraw')}}",
            infoWithdrawNumber: '{{ __('questionnaire.info.withdraw_number')}}',
        }
        window.FoodPunk.route = {
            datatableAsync: "{{ route('admin.datatable.async')}}",
            addToUserRandom: "{{ route('admin.recipes.add-to-user-random') }}",
            randomizeRecipeTemplate: '{{ route('admin.client.randomize-recipe-template')}}',
            addToUser: "{{ route('admin.recipes.add-to-user') }}",
            checkCalculationStatus: "{{ route('admin.recipes.check-calculation-status', ['userId' => $client->id]) }}",
            courseDestroy: "{{ route('admin.client.course.destroy') }}",
            courseEdit: "{{ route('admin.client.course.edit') }}",
            assignChargebeeSubscription:'{{ route("admin.client.assign-chargebee-subscription" ) }}',
            questionnaireApprove:"{{ route('admin.client.questionnaire.approve') }}",
            questionnaireToggle: "{{ route('admin.clients.questionnaire.toggle') }}",
            questionnaireCompare: "{{ route('admin.client.questionnaire.compare') }}",
            subscriptionEdit: '{{ route("admin.client.subscription-edit", ":id") }}',
            subscriptionStop: '{{ route("admin.client.subscription-stop", ":id") }}',
            subscriptionDelete: '{{ route("admin.client.subscription-delete", ":id") }}',
            recipesDeleteByUser: '{{ route('admin.recipes.delete-by-user', ['recipeId' => '%', 'userId' => $client->id]) }}',
            deleteAllRecipes: "{{ route('admin.recipes.delete-all-recipes', [ 'userId'=> $client->id]) }}",
            deleteSelectedRecipes: "{{ route('admin.recipes.delete-selected-recipes') }}",
            recalculateToUser: "{{ route('admin.recipes.recalculate-to-user') }}",
            recipesCountData:'{{route('admin.client.recipes.count-data')}}',
            searchRecipesPreview: "{{ route('admin.search-recipes.preview', ['recipeId' => '%', 'userId' => $client->id]) }}",
            recipesAddToUserRandom: "{{ route('admin.recipes.add-to-user-random') }}",
            clientDeposit: "{{ route('admin.client.deposit') }}",
            recipesGenerateToSub: "{{ route('admin.recipes.generate-to-subscription') }}",
            clientCalcAuto: "{{ route('admin.client.calc-auto') }}",
        }
        window.FoodPunk.static = {
            clientId : '{{ $client->id }}',
            activeChallenge: {{ $subscription ? $subscription->id : 0 }},
            canDeleteAllUserRecipes: {{$canDeleteAllUserRecipes}},
            hideRecipesRandomizer: {{$hideRecipesRandomizer}}
        }
        window.FoodPunk.functions = {}
    </script>
@endpush
@push('footer-scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
@endpush
