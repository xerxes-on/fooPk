@php if(empty($message)) {return;} @endphp

@if($config['container'])
    <div class="container">
        @endif
        @if($config['rowWrapper'])
            <div class="row">
                <div class="col-md-12">
                    @endif
                    <div class="alert alert-{{$config['type']}} alert-block @if($config['closable']) alert-dismissible @endif"
                         @if($config['closable'] && $config['dismiss_duration'] > 0)
                             data-dismiss-duration="{{$config['dismiss_duration']}}"
                         data-dismiss-id="{{$config['dismiss_id']}}"
                         @endif
                         role="alert">
                        @if($config['closable'])
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        @endif
                        <strong>{{$message}}</strong>
                        {{$slot}}
                    </div>
                    @if($config['rowWrapper'])
                </div>
            </div>
        @endif
        @if($config['container'])
    </div>
@endif
