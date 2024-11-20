@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="col-sm-8">
    <div class="form-group required">
        {!! Form::textarea('content', old('content', $post->content), ['required' => 'required', 'class' => 'form-control create-new_textarea', 'placeholder' => trans('common.post_question')]) !!}
    </div>
</div>

<div class="col-sm-4">
    @include('post.image', array('name' => 'image', 'label' => trans('common.post_photo'), 'required' => false))
</div>

<div class="col-xs-12 text-right">
    {!! Form::submit(trans('common.save'), ['class' => 'btn btn-tiffany m-0']) !!}
</div>
