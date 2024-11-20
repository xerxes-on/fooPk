@if($data !== [])
    <a tabindex="0"
       role="button"
       data-trigger="focus"
       class="btn-with-icon btn-with-icon-question-o"
       data-toggle="popover"
       title="{{ $data['title'] }}"
       data-html="true"
       data-placement="top"
       data-content="{!! $data['content'] !!}">
    </a>
@endif
