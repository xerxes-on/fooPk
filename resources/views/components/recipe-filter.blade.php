<div {{ $attributes }}>
    <label for="{{$filterId}}" class="search-recipes_label">{{ $filterTitle }}</label>
    <select name="{{$filterId}}" id="{{$filterId}}" class="form-control search-recipes_select changeable-element">
        @if($includeDefaultValue)
            <option value="0">{{ trans('common.all') }}</option>
        @endif
        @foreach($filterData as $key => $value)
            <option value="{{ $key }}"
                    {{ request()->get($filterId, $selectedValueDefault) == $key ? 'selected' : '' }}>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
