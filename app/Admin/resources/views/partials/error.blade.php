@if ($errorsCollection)
    <div class="form-element-errors mt-2">
        <ul>
            @foreach ($errorsCollection as $error)
                @if(is_array($error))
                    @php
                        echo '<li>' . implode('</li><li>', $error) . '</li>';
                    @endphp
                @else
                    <li>{!! $error !!}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endif
