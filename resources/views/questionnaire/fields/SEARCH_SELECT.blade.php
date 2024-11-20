@php
    $answer          = $question['answer'] ?? old($question['slug'], []);
    $user            = auth()->user();
    $searchRouteName = $user->hasRole(App\Enums\Admin\Permission\RoleEnum::USER->value) ?
        route('ingredients.search.client-designated'):
        route('admin.ingredients.search.client-designated', ['clientId' => $client->id]);
    if (isset($answer[0]['key'])) {
        $answer = $question['answer'];
    } elseif (!empty($answer)) {
        $answer = \Modules\Ingredient\Models\Ingredient::withOnly('translations')->whereIn('id', $answer)->get();
    }
@endphp
<div class="form-group{{ $question['is_required'] ? ' required' : '' }}  @error($question['slug']) has-error @enderror"
     data-key={!! ($question['id']) !!}>
    <label class="form-check-label" for="{{$question['slug']}}">{{$question['title']}}</label>
    <div class="form-input">
        <select class="form-control"
                type="search"
                name="{{$question['slug']}}[]"
                multiple="multiple"
                data-route="{{ $searchRouteName }}"
                id="{{$question['slug']}}">
            @foreach($answer as $data)
                {{-- Answer can already be formed inside service, so values are build different here --}}
                @if(isset($data['key']))
                    <option value="{{$data['key']}}" selected>{{$data['value']}}</option>
                @else
                    <option value="{{$data->id}}" selected>
                        {{$data->translations->where('locale', $user->lang)->first()->name}}
                    </option>
                @endif
            @endforeach
        </select>
    </div>
</div>
