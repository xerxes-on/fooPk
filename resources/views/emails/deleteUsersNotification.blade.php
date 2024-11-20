<h2>The users will be subjected to deletion.</h2>
<p>The following users will be subjected to deletion. Please find detailed exported user data in attached.</p>

@if(is_array($data))
    @foreach($data as $key => $group)
        @if(!is_array($group))
            @continue
        @endif
        <p><i>{{ $key }}</i>:
            @foreach($group as $id)
                <b>{{ $id }}</b>,
            @endforeach
            <i>TOTAL - {{ count($group) }}</i>.
        </p>
    @endforeach
@endif
<p>Be advised, first group of users will be deleted on <b>{{now()->addDays(14)->toDateString()}}</b>.</p>
<p>
    If it would be required to prevent deletion of this group, please,
    notify development team before specified date to prevent the deletion task!
</p>
<p>Other group of users would be subjected to deletion immediately.</p>