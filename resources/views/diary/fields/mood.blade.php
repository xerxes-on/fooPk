@php
    $mood = 0;

    if (isset($post) && !empty($post->mood)) {
        $mood =	$post->mood;
    }elseif(isset($diary) && !empty($diary->mood)) {
        $mood =	$diary->mood;
    }
@endphp
<div class="form-group required">
    {!! Form::label('mood', trans('common.mood'), ['class' => 'create-new_label']) !!}

    <div class="range_mood_wrapper">
        <div class="range_mood_wrapper_img">
            <img src="{{asset('/images/icons/ic_mood_sad.svg')}}" width="24" height="24" alt="icon"/>
        </div>
        <div class="range_mood_wrapper_slide">
            <input type="text" class="mood" name="mood" value="" mood="{{ $mood }}"/>
        </div>
        <div class="range_mood_wrapper_img">
            <img src="{{ asset('/images/icons/ic_mood_happy.svg') }}" width="24" height="24" alt="Icon"/>
        </div>
    </div>
</div>
