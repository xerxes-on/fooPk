<div class="client-notes">

    <h3 class="title">
        @lang('common.notes')
        <button class="btn btn-default btn-sm" data-client-notes-form-show>@lang('common.add_note')</button>
    </h3>

    <div class="form-group "
         style="display: none"
         data-client-notes-new-form-wrapper
         data-client-notes-new-form-action="{{route('admin.client-notes.store')}}"
         data-client-notes-new-form-client-id="{{$client->id}}">

        <label for="admin_note" class="control-label">@lang('common.add_note')</label>

        <textarea class="form-control" rows="6" id="admin_note" name="admin_note" autocomplete="off"></textarea>
        <input type="hidden" name="client_id" value="{{ $client->id }}">

        <div class="btn-group">
            <button class="btn btn-primary" data-client-notes-send-note>@lang('common.create')</button>
            <button class="btn btn-tiffany" data-client-notes-form-hide>@lang('common.cancel')</button>
        </div>

    </div>

    <ul class="list-group " data-client-notes-list>
        <div class="hidden" data-client-notes-list-item-template>
            <li class="list-group-item justify-content-between align-items-center client-note-list-item"
                data-note-id="__note_id__">
                <i class="fa fa-edit" data-client-notes-list-item-edit data-note-id="__note_id__"></i>
                <p class="mb-1">__note_text__</p>
                <div class="client-note-meta"><small>__created_at__ by <strong>__author_name__</strong></small></div>
                <div class="hidden" data-client-notes-list-item-edit-btns data-note-id="__note_id__">
                    <button class="btn btn-primary" data-client-notes-edit-note>@lang('common.save')</button>
                    <button class="btn btn-danger" data-client-notes-cancel-edit>@lang('common.cancel')</button>
                </div>
            </li>
        </div>

        @forelse($client->clientNotes as $note)
            <li class="list-group-item justify-content-between align-items-center client-note-list-item"
                data-note-id="{{ $note->id }}">
                @if($note->author_id == $user->id)
                    <i class="fa fa-edit"
                       data-client-notes-list-item-edit data-note-id="{{ $note->id }}"
                       aria-hidden="true"></i>
                @endif

                <p class="mb-1">{{ $note->text }}</p>
                <div class="client-note-meta">
                    <small>{{ $note->created_at->format('Y-m-d H:i:s')  }} by
                        @if($note->author_id === $user->id)
                            <strong>{{$user->name}}</strong>
                        @elseif(!is_null($noteAuthor = $note?->author?->name))
                            <strong>{{$noteAuthor}}</strong>
                        @else
                            <strong>Author Deleted</strong>
                        @endif
                    </small>
                </div>

                <div class="hidden" data-client-notes-list-item-edit-btns data-note-id="{{ $note->id }}">
                    <button class="btn btn-primary" type="button" data-client-notes-edit-note>
                        @lang('common.save')
                    </button>
                    <button class="btn btn-danger" type="button" data-client-notes-cancel-edit>
                        @lang('common.cancel')
                    </button>
                </div>
            </li>
        @empty
            <li class="list-group-item d-flex justify-content-between align-items-center no-notes-item">
                <div class="d-flex w-100 justify-content-between">
                    <h4 class="mb-1">@lang('common.no_notes')</h4>
                </div>
            </li>
        @endforelse
    </ul>
</div>
