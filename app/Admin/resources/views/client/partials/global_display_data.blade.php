{{-- pushing to vendor/laravelrus/sleepingowl/resources/views/default/_layout/base.blade.php --}}
@php
    use App\Enums\Admin\Permission\PermissionEnum;
    use App\Models\Admin;
@endphp

@push('scripts')
    <script>
        window.FoodPunk = window.FoodPunk || {};
        window.FoodPunk.i18n = {
            wait: "@lang('admin.messages.wait')",
            inProgress: "@lang('admin.messages.in_progress')",
            noUserSelected: "@lang('admin.messages.no_user_selected')",
            noItemSelected: "@lang('admin.messages.no_item_selected')",
            saved: "@lang('admin.messages.saved')",
            randomizeRecipesSettings: "@lang('admin.messages.randomize_recipes_settings')",
        };
        window.FoodPunk.route = {
            datatableAsync: '{{ route('admin.datatable.async')}}',
            addToUserRandom: '{{ route('admin.recipes.add-to-user-random') }}',
            randomizeRecipeTemplate: '{{ route('admin.client.randomize-recipe-template')}}',
            addToUser: '{{ route('admin.recipes.add-to-user') }}'
        }
        window.FoodPunk.pageInfo = {
            url: '{{ url('/')  }}',
            canDelete: '{{ auth()->user()->can(PermissionEnum::DELETE_CLIENT->value, Admin::class)}}',
            hideRecipesRandomizer: '{{$hideRecipesRandomizer}}'
        }
        window.FoodPunk.functions = {}
    </script>
@endpush
