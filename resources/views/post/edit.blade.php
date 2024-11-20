<div class="container">
    <div class="row">
        <div class="col-xs-12">
                <div class="create-new">
                    {!! Form::open(['route' => ['post.update', $post->id], 'method' => 'PUT', 'files' => true]) !!}
                    @include('post.form')
                    {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
