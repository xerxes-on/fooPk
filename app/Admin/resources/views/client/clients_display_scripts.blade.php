@php
    use App\Enums\Admin\Permission\PermissionEnum;
    use App\Models\Admin;
@endphp
@push('scripts')
    <script>
        window.FoodPunk = {};
        window.FoodPunk.i18n = {
            wait: '{{ __('admin.messages.wait')}}',
            inProgress: '{{ __('admin.messages.in_progress')}}',
            noUserSelected: "{{ __('admin.messages.no_user_selected')}}",
            noItemSelected: "{{ __('admin.messages.no_item_selected')}}",
            saved: '{{ __('admin.messages.saved')}}',
            randomizeRecipesSettings: '{{ __('admin.messages.randomize_recipes_settings') }}'
        };
        window.FoodPunk.route = {
            datatableAsync: "{{ route('admin.datatable.async')}}",
            addToUserRandom: "{{ route('admin.recipes.add-to-user-random') }}",
            randomizeRecipeTemplate: '{{ route('admin.client.randomize-recipe-template')}}',
            addToUser: "{{ route('admin.recipes.add-to-user') }}"
        }
        window.FoodPunk.static = {
            url: '{{ url('/')  }}',
            canDelete: {{ auth()->user()->can(PermissionEnum::DELETE_CLIENT->value, Admin::class)}},
            hideRecipesRandomizer: {{$hideRecipesRandomizer}}
        }
        window.FoodPunk.functions ={}
    </script>
@endpush