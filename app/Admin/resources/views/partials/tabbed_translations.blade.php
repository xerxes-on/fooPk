@php
    // Defaults
    $required = $required ?? true;
    $label = $label ?? '';
    $type = match ($type ?? '') {
        'textarea' => 'textarea',
        default => 'text'
    };
    $attribute = $attribute ?? null;
    $helptext = $helptext ?? null;
    $enContent = $model?->translations?->where('locale', 'en')->first();
    $deContent = $model?->translations?->where('locale', 'de')->first();
@endphp

@if($attribute)
    <div class="tabbed-wrapper mb-3">
        <h2>{{$label}} @if($required)
                <span class="form-element-required">*</span>
            @endif</h2>
        <ul class="nav nav-tabs" id="{{$attribute}}Tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active"
                   id="{{$attribute}}-de-tab"
                   data-toggle="tab"
                   href="#{{$attribute}}-de"
                   role="tab"
                   aria-controls="{{$attribute}}-de"
                   aria-selected="true">DE</a>
            </li>
            <li class="nav-item">
                <a class="nav-link"
                   id="{{$attribute}}-en-tab"
                   data-toggle="tab"
                   href="#{{$attribute}}-en"
                   role="tab"
                   aria-controls="{{$attribute}}-en-tab"
                   aria-selected="false">EN</a>
            </li>
        </ul>
        <div class="tab-content py-2" id="{{$attribute}}Content">
            <div class="tab-pane fade show active" id="{{$attribute}}-de" role="tabpanel"
                 aria-labelledby="{{$attribute}}-de-tab">
                <div class="form-group form-element-text">
                    <label for="{{$attribute}}_de"
                           class="control-label @if($required) required @endif">{{ $label }}</label>
                    @if($type === 'textarea')
                        <textarea class="form-control"
                                  id="{{$attribute}}_de"
                                  name="de[{{$attribute}}]"
                                  rows="3"
                                  @if($required) required @endif>{{$deContent->$attribute ?? ''}}</textarea>
                    @else
                        <input class="form-control"
                               type="text"
                               id="{{$attribute}}_de"
                               name="de[{{$attribute}}]"
                               value="{{$deContent->$attribute ?? ''}}"
                               @if($required) required @endif>
                    @endif
                </div>
            </div>
            <div class="tab-pane fade" id="{{$attribute}}-en" role="tabpanel" aria-labelledby="{{$attribute}}-en-tab">
                <div class="form-group form-element-text">
                    <label for="{{$attribute}}_en"
                           class="control-label @if($required) required @endif">{{ $label }}</label>

                    @if($type === 'textarea')
                        <textarea class="form-control"
                                  id="{{$attribute}}_en"
                                  name="en[{{$attribute}}]"
                                  rows="3"
                                  @if($required) required @endif>{{$enContent->$attribute ?? ''}}</textarea>
                    @else
                        <input class="form-control"
                               type="text"
                               id="{{$attribute}}_en"
                               name="en[{{$attribute}}]"
                               value="{{$enContent->$attribute ?? ''}}"
                               @if($required) required @endif>
                    @endif
                </div>
            </div>
        </div>

        @if($helptext)
            <p><small class="form-element-helptext">{!! $helptext !!}</small></p>
        @endif

        @if ($errors->has('translations.*.'.$attribute))
            <div class="form-element-errors mt-2">
                <ul>
                    @foreach ($errors->get('translations.*.'.$attribute) as $error)
                        @if(is_array($error))
                            @php
                                echo custom_implode($error,'</li><li>','<li>','</li>');
                            @endphp
                        @else
                            <li>{!! $error !!}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@else
    <p class="text danger">Attribute must be set</p>
@endif
