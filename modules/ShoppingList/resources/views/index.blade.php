@extends('layouts.app')

@section('title', trans('shopping-list::common.page_title'))

@section('styles')
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.min.css"
          rel="stylesheet">

    <script>
        window.foodPunk.routes = {
            clearList: "{{ route('purchases.list.clear') }}",
            deleteRecipe: "{{ route('purchases.recipe.delete') }}",
            changeServing: "{{ route('purchases.recipe.changeServings') }}",
            addIngredient: "{{ route('purchases.ingredient.new') }}",
            deleteIngredient: "{{ route('purchases.ingredient.delete') }}",
            checkIngredient: "{{ route('purchases.ingredient.check') }}",
        };
        window.foodPunk.i18n = {
            generateListTitle: "@lang('shopping-list::messages.success.generate_list_title')",
            generateListText: "@lang('shopping-list::messages.success.generate_list_text')",
            confirm: "@lang('common.confirm')",
            generate: "@lang('common.generate')",
            cancel: "@lang('common.cancel')",
            requiredField: "@lang('common.field_is_required')",
            clearListQuestion: "@lang('shopping-list::messages.success.clear_list_question')",
            areYouSure: "@lang('common.are_you_sure')",
            emptyField: "@lang('shopping-list::messages.error.field_is_empty')",
            customIngredientLabel: "@lang('shopping-list::common.labels.custom')",
            dateFormat: "@lang('survey_questions.date_format')",
        };
        window.foodPunk.misc = {
            icon: "{{ asset('/images/icons/ic_delete.svg') }}",
        };
    </script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="shopping-list_panel active">
                    <span class="shopping-list_panel_title">@lang('shopping-list::common.labels.generate_list')</span>
                    <div class="shopping-list_panel_content">
                        {!! Form::open(['route' => 'purchases.list.createByDate', 'method' => 'POST', 'id' => 'createByDate']) !!}
                        <div class="col-sm-9 col-md-8 col-xs-12 p-l0">
                            <div class="shopping-list_panel_content_half">
                                {!! Form::label('date_start', trans('shopping-list::common.labels.start_date'), ['class' => 'shopping-list_panel_content_label']) !!}
                                <div class="input-group date"
                                     data-provide="datepicker"
                                     data-date-format="dd.mm.yyyy"
                                     data-date-autoclose="true"
                                     data-date-today-highlight="true"
                                     data-date-week-start="1"
                                     data-date-language="{{ app()->getLocale() }}">
                                    {!! Form::text('date_start', null, [
                                        'required' => true,
                                        'class' => 'shopping-list_panel_content_input',
                                        'placeholder' => 'dd.mm.yyyy',
                                        'autocomplete' => 'off'
                                        ]) !!}
                                    <div class="input-group-addon shopping-list_panel_content_calendar">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="shopping-list_panel_content_half">
                                {!! Form::label('date_end', trans('shopping-list::common.labels.end_date'), ['class' => 'shopping-list_panel_content_label']) !!}
                                <div class="input-group date"
                                     data-provide="datepicker"
                                     data-date-format="dd.mm.yyyy"
                                     data-date-autoclose="true"
                                     data-date-today-highlight="true"
                                     data-date-week-start="1"
                                     data-date-language="{{ app()->getLocale() }}">
                                    {!! Form::text('date_end', null, [
                                        'required' => true,
                                        'class' => 'shopping-list_panel_content_input',
                                        'placeholder' => 'dd.mm.yyyy',
                                        'autocomplete' => 'off'
                                        ]) !!}
                                    <div class="input-group-addon shopping-list_panel_content_calendar">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix visible-xs"></div>
                        <div class="col-sm-3 col-md-4 text-center p-r0 p-l0">
                            {!! Form::submit(trans('shopping-list::common.buttons.show_recipes'), ['class' => 'btn btn-tiffany purchase-btn']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="shopping-list_panel shopping-list_panel_buttons">
                    <div class="shopping-list_panel_content">
                        <a href="{{ route('purchases.list.print') }}" target="_blank"
                           class="btn btn-tiffany link-with-icon link-with-icon-printer flex-grow">
                            @lang('shopping-list::common.buttons.print_list')
                        </a>
                        <a id="clear_list"
                           href="{{ route('purchases.list.clear') }}"
                           class="btn btn-gray link-with-icon link-with-icon-clear flex-grow">
                            @lang('shopping-list::common.buttons.clear_list')
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <ul class="content-links" role="tablist">
                    <li role="presentation" class="content-links_item active font-12">
                        <a href="#ingredients" role="tab" data-toggle="tab">@lang('common.ingredients')</a>
                    </li>
                    <li role="presentation" class="content-links_item font-12">
                        <a href="#recipes" role="tab" data-toggle="tab">@lang('common.recipes')</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active" id="ingredients">
                        <div class="row">
                            <div class="col-sm-4 ingredients-main-list-form">
                                <div class="shopping-list_panel sidebar-single-form">
                                    <label for="custom_ingredient"
                                           class="shopping-list_panel_title">@lang('shopping-list::common.labels.add_new_item')</label>
                                    <div class="form-single-inner">
                                        <div class="form-group form-single-field">
                                            <input type="text"
                                                   class="form-control shopping-list_panel_content_input"
                                                   name="custom_ingredient"
                                                   id="custom_ingredient">
                                        </div>
                                        <div class="form-group text-center form-single-btn">
                                            <button class="btn btn-pink-full" id="new_list_ingredient" type="button">
                                                @lang('shopping-list::common.labels.to_list')
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-8 ingredients-main-list-col" id="ingredients_wrapper">
                                @include('shopping-list::includes.ingredientsList')
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="recipes">
                        @include('shopping-list::includes.recipesList')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.de.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
    <script src="{{ mix('vendor/sweetalert/sweetalert.min.js')}} "></script>
    <script src="{{ mix('js/shopping-list.js') }}"></script>
@endsection
