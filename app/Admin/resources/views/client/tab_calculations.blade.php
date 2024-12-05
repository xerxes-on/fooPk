@if(!empty($client_diet_data))

    @if (!$is_consultant && !empty($client_diet_data_top))
        <table class="table">
            <tr>
                <th colspan="7" style="text-align: center">@lang('common.internal_values')</th>
            </tr>
            @foreach($client_diet_data_top as $key =>$value)
                <tr>
                    <td colspan="2">{{$key}}</td>
                    <td colspan="5">{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {!! Form::open(['route' => array('admin.client.calculations', $user_id), 'method' => 'POST']) !!}

    <table class="table">
        <tr>
            <td>@lang('common.calculate_auto')</td>
            <td>
                <div class="switch-wrapper col-xs-3" style="width: 100%">
                    <input id="calc_auto"
                           name="calc_auto"
                           type="checkbox"
                           value="1" {!! (!empty($calc_auto)) ? 'checked="checked"' : '' !!}>
                    <label for="calc_auto">@lang('common.calculate_auto')</label>
                </div>
            </td>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td style="width:18%; font-weight: bold">@lang('common.calories') (Kcal)</td>
            <td style="width:15%; font-weight: bold">@lang('common.carbohydrates') (g)</td>
            <td style="width:12%; font-weight: bold">@lang('common.protein') (g)</td>
            <td style="width:10%; font-weight: bold">@lang('common.fat') (g)</td>
            <td style="width:15%; font-weight: bold">@lang('common.carbohydrates') (%)</td>
            <td style="width:15%; font-weight: bold">@lang('common.protein') (%)</td>
            <td style="width:15%; font-weight: bold">@lang('common.fat') (%)</td>
        </tr>
        <tr>
            <td>
                {!! Form::number('Kcal', $client_diet_data['Kcal'], ['class' => 'form-control', 'style'=>'float:left; width:100%']) !!}
            </td>
            <td>
                {!! Form::number('KH', ($client_diet_data['KH'])?:50, ['class' => 'form-control', 'style'=>'float:left; width:100%']) !!}
            </td>
            <td>
                {!! $client_diet_data['EW'] !!}
                {{Form::hidden('EW_hidden',$client_diet_data['EW'])}}
            </td>
            <td>
                {!! $client_diet_data['F'] !!}
                {{Form::hidden('F_hidden',$client_diet_data['F'])}}
            </td>
            <td>
                {!! $client_diet_data['kh_percents'] !!}
                {{Form::hidden('kh_percents_hidden',$client_diet_data['kh_percents'])}}
            </td>
            <td>
                {!! Form::number('ew_percents',$client_diet_data['ew_percents'], ['class' => 'form-control','style'=>'float:left; width:100%']) !!}
            </td>
            <td>
                {!! $client_diet_data['f_percents'] !!}
                {{Form::hidden('f_percents_hidden',$client_diet_data['f_percents'])}}
            </td>
        </tr>

        @if(!empty($client_diet_data['notices']))
            <tr>
                <td colspan="7">
                    <b>@lang('common.notice')</b>: {{$client_diet_data['notices']}}
                    {{Form::hidden('notices_hidden',$client_diet_data['notices'])}}
                </td>
            </tr>
        @endif

        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td colspan="7">@lang('common.meal_by_type')</td>
        </tr>
        <tr>
            <td>@lang('common.calculate_custom_nutrients')</td>
            <td>
                <div class="switch-wrapper col-xs-3" style="width: 100%">
                    <input id="allow_custom_nutrients" name="allow_custom_nutrients" type="checkbox" value="1"
                           checked="checked">
                    <label for="allow_custom_nutrients">@lang('common.calculate_custom_nutrients')</label>
                </div>
            </td>
            <td colspan="5"></td>
        </tr>

        @foreach($ingestions as $type=>$values)
            <tr>
                <td colspan="7"><b>{{$values['title']}}</b></td>
            </tr>
            @if(!empty($client_diet_data['ingestion'][$values['key']]))
                @foreach($client_diet_data['ingestion'][$values['key']] as $key=>$value)
                    <tr>
                        <td>{{$key}}</td>
                        <td>
                            @if($key=='percents')
                                {!! Form::number('ingestion['.$values['key'].']['.$key.']', $value, ['class' => 'form-control', 'style'=>'float:left; width:70%','step' => '0.1']) !!}
                                %
                            @else
                                {!! Form::number('ingestion['.$values['key'].']['.$key.']', $value, ['class' => 'form-control allowed_custom_nutrients', 'style'=>'float:left; width:70%','readonly'=>'readonly','step' => '0.1']) !!}
                            @endif
                        </td>
                        <td colspan="5"></td>
                    </tr>
                @endforeach
            @endif
        @endforeach

    </table>
    {{Form::hidden('client_id',$user_id)}}

    {!! Form::button(trans('common.reset_calculation'), ['onclick'=>"return confirm('".trans('common.are_you_sure')."')",'type'=>'submit','name'=>'action', 'value'=>\App\Enums\Admin\Client\ClientCalculationActionsEnum::RESET->name, 'class' => 'btn btn-info']) !!}
    {!! Form::button(trans('common.recalculate'), ['type'=>'submit','name'=>'action', 'value'=>\App\Enums\Admin\Client\ClientCalculationActionsEnum::RECALCULATE->name, 'class' => 'btn btn-info']) !!}
    {!! Form::button(trans('common.save_custom_nutrients'), ['type'=>'submit','name'=>'action', 'value'=>\App\Enums\Admin\Client\ClientCalculationActionsEnum::STORE_CUSTOM_NUTRIENTS->name, 'class' => 'btn btn-info allowed_custom_nutrients_btn']) !!}

    {!! Form::close() !!}

    @if(!$is_consultant && !empty($client_diet_data))
        <hr/>
        <table class="table">
            <tr>
                <th colspan="7" style="text-align: center">@lang('common.internal_values')</th>
            </tr>
            @foreach(array_keys($client_diet_data) as $key)
                @if (!is_array($client_diet_data[$key]))
                    <tr>
                        <td colspan="2">{{$key}}</td>
                        <td colspan="5">{{ $client_diet_data[$key] }}</td>
                    </tr>
                @endif
            @endforeach

            @if(!empty($client_diet_data['additional']))
                @foreach(array_keys($client_diet_data['additional']) as $key)
                    <tr>
                        <td colspan="2">{{$key}}</td>
                        <td colspan="5">
                            {{ number_format($client_diet_data['additional'][$key],1, '.', '')}}
                        </td>
                    </tr>
                @endforeach

            @endif
        </table>
    @endif
@else
    <h3>@lang('common.formular_data_empty')</h3>
@endif
@push('footer-scripts')
    <script>
        window.foodPunk.i18n = {
            messages_confirmation: "@lang('admin.messages.confirmation')",
            messages_revert_warning: "@lang('admin.messages.revert_warning')",
            messages_wait: "@lang('admin.messages.wait')",
            messages_in_progress: "@lang('admin.messages.in_progress')",
            messages_saved: "@lang('admin.messages.saved')",
        };
    </script>
    <script src="{{ mix('js/admin/client/main.js') }}"></script>
@endpush
