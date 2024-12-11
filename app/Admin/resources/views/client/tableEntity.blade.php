@php
    use Modules\Chargebee\Enums\Admin\Client\Filters\ClientChargebeeSubscriptionFilterEnum;use App\Enums\Admin\Client\Filters\ClientFormularFilterEnum;use App\Enums\Admin\Client\Filters\ClientSubscriptionFilterEnum;use App\Enums\Admin\Client\Filters\ClientConsultantFilterEnum;use App\Enums\Admin\Permission\PermissionEnum;

    $hideRecipesRandomizer = !$isConsultant && $user->hasPermissionTo(PermissionEnum::ADD_RECIPES_TO_CLIENT->value);
@endphp
<div class="text-left mb-3">
    @can(PermissionEnum::CREATE_CLIENT->value, '\App\Models\Admin')
        <a href="{{ url('admin/users/create') }}" class="btn btn-primary">
            <i class="fas fa-plus" aria-hidden="true"></i> @lang('admin.buttons.new_record')
        </a>
    @endcan

        @if($hideRecipesRandomizer)
            <button type="button" id="add-recipes-to-select-users" class="btn btn-info ladda-button"
                    data-style="expand-right"
                onclick="addRecipes2selectUsers()">
            <span class="ladda-label"><i class="fas fa-plus" aria-hidden="true"></i> @lang('common.add_recipe')</span>
        </button>

        <button type="button" id="add-randomize-recipes-to-select-users" class="btn btn-info"
                onclick="addRandomizeRecipes2selectUsers()">
            <i class="fas fa-plus" aria-hidden="true"></i> @lang('admin.buttons.add_random_recipes')
        </button>
    @endif
</div>

<div id="search_block" class="panel-body">
    <form id="user-filter" class="form">

        <div class="form-group">
            <label for="v_search">@lang('common.search'):</label>
            <input type="text"
                   placeholder="Enter Search Keywords (id, First name, Last name, e-mail)"
                   name="v_search"
                   id="v_search" class="form-control">
        </div>

        <div class="row">
            <div class="form-group col-lg-3">
                <label for="filter-formular_approved">@lang('admin.filters.formular.title')</label>
                <select name="formular_approved" id="filter-formular_approved" class="form-control">
                    <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                    @foreach(ClientFormularFilterEnum::forSelect() as $key =>$value)
                        <option value="{{$key}}">@lang('admin.filters.formular.options.' . strtolower($value))</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label for="filter-subscription">@lang('admin.filters.subscription.title')</label>
                <select name="subscription" id="filter-subscription" class="form-control">
                    <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                    @foreach(ClientSubscriptionFilterEnum::forSelect() as $key =>$value)
                        <option value="{{$key}}">@lang('admin.filters.defaults.' . strtolower($value))</option>
                    @endforeach
                </select>
            </div>
            @if(!$isConsultant)
                <div class="form-group col-lg-3">
                    <label for="filter-chargebee-subscription">@lang('admin.filters.chargebee_subscription.title')</label>
                    <select name="chargebee_subscription" id="filter-chargebee-subscription" class="form-control">
                        <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                        @foreach(ClientChargebeeSubscriptionFilterEnum::forSelect() as $key =>$value)
                            <option value="{{$key}}">@lang('admin.filters.defaults.' . strtolower($value))</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="form-group col-lg-3">
                <label for="filter-status">@lang('admin.filters.status.title')</label>
                <select name="status" id="filter-status" class="form-control">
                    <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                    <option value="0">@lang('admin.filters.status.options.disabled')</option>
                    <option value="1">@lang('admin.filters.status.options.active')</option>
                </select>
            </div>
            @if(!$isConsultant)
                <div class="form-group col-lg-3">
                    <label for="filter-newsletter">@lang('admin.filters.newsletter.title')</label>
                    <select name="allow_marketing" id="filter-newsletter" class="form-control">
                        <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                        <option value="0">@lang('admin.filters.defaults.missing')</option>
                        <option value="1">@lang('admin.filters.defaults.exist')</option>
                    </select>
                </div>
            @endif
            <div class="form-group col-lg-3">
                <label for="filter-abo_challenge">@lang('admin.filters.challenge.title')</label>
                <select name="abo_challenge" id="filter-abo_challenge" class="form-control">
                    <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                    <option value="0">@lang('common.no')</option>
                    @foreach($aboChallenges as $value => $aboChallenge)
                        <option value="{{ $value }}">{{ $aboChallenge }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label for="filter-lang">@lang('admin.filters.language.title')</label>
                <select name="lang" id="filter-lang" class="form-control">
                    <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                    @foreach(config('translatable.locales') as $key => $lang)
                        <option value="{{ $key }}">@lang("admin.filters.language.$key")</option>
                    @endforeach
                </select>
            </div>
            @if(!$isConsultant)
                <div class="form-group col-lg-3">
                    <label for="filter-consultant">@lang('admin.filters.consultant.title')</label>
                    <select name="consultant" id="filter-consultant" class="form-control">
                        <option value="" hidden>@lang('admin.filters.defaults.select')</option>
                        @foreach(ClientConsultantFilterEnum::forSelect() as $key =>$value)
                            <option value="{{$key}}">@lang('admin.filters.consultant.options.' . strtolower($key))</option>
                        @endforeach

                        @foreach($consultants as $id => $name)
                            @if($loop->first)
                                <optgroup label="@lang('admin.filters.consultant.title')">
                                    @endif
                                    <option value="{{ $id }}">{{$name}}</option>
                                    @if($loop->last)
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                <div class="btn-group text-right">
                    <button id="reset-filter" class="btn btn-default" type="reset">@lang('admin.buttons.reset')</button>
                    <button id="apply-filter" class="btn btn-info" type="submit">@lang('common.search')</button>
                </div>
            </div>
        </div>
    </form>
</div>

<table id="table-users" class="table table-striped table-bordered" style="width:100%">
    <thead>
    <tr>
        <th style="width:5%">#</th>
        <th>@lang('common.first_name')</th>
        <th>@lang('common.last_name')</th>
        <th>@lang('common.email')</th>
        <th>@lang('admin.filters.formular.title')</th>
        <th>@lang('admin.filters.subscription.title')</th>
        <th>@lang('admin.filters.status.title')</th>
        <th>@lang('admin.filters.language.title')</th>
        <th>@lang('common.registration_date')</th>
        <th></th>
    </tr>
    </thead>
</table>
@if ($hideRecipesRandomizer)
    <div style="display:none">
        <div id="allRecipes-popup-wrapper" style="padding:10px; background:#fff;">
            <table id="allRecipes-popup" class="table table-striped table-bordered" style="width:100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('common.title')</th>
                    <th>@lang('common.day_category')</th>
                    <th>@lang('common.diets')</th>
                    <th>@lang('common.status')</th>
                </tr>
                </thead>
            </table>

            <div class="text-right py-2">
                <button type="button" id="submit-add-recipes" class="btn btn-info ladda-button"
                        data-style="expand-right"
                        onclick="submitAdding()">
                    <span class="ladda-label">@lang('common.submit')</span>
                </button>
            </div>
        </div>
    </div>
@endif
@push('footer-scripts')
    <script>
        const inputRecipeAmount = async function () {
            const {value: formValues} = await Swal.fire({
                title: 'Randomize recipes settings',
                icon: 'question',
                html: `<div id="randomizeRecipeComponent"></div>`,
                willOpen: () => {
                    Swal.showLoading();
                    $.get(
                        '{{route('admin.client.randomize-recipe-template')}}',
                        {}, (payload) => {
                            Swal.hideLoading();
                            Swal.getHtmlContainer().querySelector('#randomizeRecipeComponent').innerHTML = payload;
                        });
                },
                preConfirm: () => {
                    const container = Swal.getHtmlContainer();
                    let seasons = [];
                    let items = container.getElementsByClassName('selected_seasons');
                    for (let i = 0; i < items.length; i++) {
                        let val = items[i].value;
                        if (items[i].checked && val.length > 0) {
                            seasons.push(val);
                        }
                    }
                    return {
                        amount: container.querySelector('input[name="amount_of_recipes"]').value,
                        seasons: seasons,
                        distribution_type: container.querySelector('input[name="distribution_type"]:checked').value,
                        breakfast_snack: container.querySelector('input[name="breakfast_snack"]').value,
                        lunch_dinner: container.querySelector('input[name="lunch_dinner"]').value,
                        recipes_tag: container.querySelector('input[name="recipes_tag"]:checked').value,
                        distribution_mode: container.querySelector('input[name="distribution_mode"]:checked').value,
                    };
                },
            });

            return formValues;
        };

        function submitAdding() {
            let rowsSelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
            let usersSelected = JSON.parse(localStorage.getItem(selectedUsersStorage));

            // check selected rows
            if (rowsSelected === null || rowsSelected.selected.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No item selected!',
                });
                return false;
            }

            // check selected rows
            if (usersSelected === null || usersSelected.selected.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No User selected!',
                });
                return false;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.recipes.add-to-user') }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userIds: usersSelected.selected,
                    recipeIds: rowsSelected.selected,
                },
                beforeSend: function () {
                    $.colorbox.close();
                    Swal.fire({
                        title: '{{trans('admin.messages.wait')}}',
                        text: '{{trans('admin.messages.in_progress')}}',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                    @if($hideRecipesRandomizer)
                    $SubmitAddRecipes.start();
                    @endif
                },
                success: function (data) {
                    if (data.success === true) {
                        Swal.hideLoading();
                        Swal.fire({
                            icon: 'success',
                            title: '{{trans('admin.messages.saved')}}',
                            html: data.message,
                        });
                    } else {
                        Swal.hideLoading();
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: data.message,
                        });
                    }
                    // refresh DataTable
                    @if($hideRecipesRandomizer)
                    $SubmitAddRecipes.stop();
                    @endif
                    localStorage.removeItem(selectedPopupRecipesStorage);
                    localStorage.removeItem(selectedUsersStorage);
                },
                error: function (data) {
                    Swal.hideLoading();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: jqXHR.responseJSON.message,
                    });
                    console.error(jqXHR);
                    $SubmitAddRecipes.stop();
                },
            });
        }
    </script>
@endpush
