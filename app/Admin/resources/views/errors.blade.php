@if ($errors->any())
    <div class="alert alert-danger">
        <p class="text-uppercase text-bold">Warning!</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
