<div class="row">
    <div class="create-new">
        {!! Form::open(['route' => 'post.store', 'method' => 'POST', 'files' => true]) !!}
        @include('post.form')
        {!! Form::close() !!}
    </div>
</div>
