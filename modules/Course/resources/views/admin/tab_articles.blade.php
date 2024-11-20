<div class="text-left mb-4">
    {!! Form::open(['route' => ['admin.course.attach', $course->getKey()], 'method' => 'POST', 'class' => 'form-inline']) !!}
    <div class="form-group pull-left">
        <label for="days" class="sr-only">@lang('course::common.days')</label>
        <select class="form-control" id="days" name="days" required title="@lang('course::common.days')">
            <option value=""></option>
            @for($i = 1; $i <= $course->duration; $i++)
                @if(in_array($i, $daysPresent))
                    @continue
                @endif
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
    </div>
    <div class="form-group col-md-4">
        <label for="wp_article_id" class="sr-only">@lang('admin.articles.id_field')</label>
        <select class="form-control js-data-ajax-article" id="wp_article_id" name="wp_article_id" required></select>
    </div>

    <button type="submit" id="add-article-to-challenge" class="btn btn-primary">
        <span class="fa fa-plus" aria-hidden="true"></span> @lang('admin.buttons.new_article')
    </button>
    {!! Form::close() !!}
</div>

<h3 class="text-center">@lang('admin.articles.table_title')</h3>

<table id="table-abo_challenge" class="table table-striped table-bordered w-100">
    <thead>
    <tr>
        <th>@lang('course::common.days')</th>
        <th>@lang('admin.articles.id_field')</th>
        <th>@lang('common.title')</th>
        <th></th>
    </tr>
    </thead>
</table>

@push('footer-scripts')
    <script>
        jQuery(document).ready(function ($) {

            $('#table-abo_challenge').DataTable({
                lengthChange: false,
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 20,
                autoWidth: false,
                bFilter: false,
                pagingType: 'numbers',
                language: {
                    oPaginate: {
                        sNext: '<span aria-hidden="true" class="fa fa-forward"></span>',
                        sPrevious: '<span aria-hidden="true" class="fa fa-backward"></span>',
                        sFirst: '<span aria-hidden="true" class="fa fa-step-backward"></span>',
                        sLast: '<span aria-hidden="true" class="fa fa-step-forward"></span>',
                    },
                },

                order: [[0, 'asc']],
                ajax: {
                    url: '/admin/datatable/async',
                    data: function (d) {
                        d.method = 'courses';
                        d.aboChallengeId = '{{ $course->getKey() }}';
                    },
                },
                columns: [
                    {data: 'days'},
                    {data: 'wp_article_id'},
                    {data: 'article_title'},
                    {
                        data: null,
                        className: 'center',
                        orderable: false,
                        width: '65px',
                        render: function (data, type, row) {
                            return '<form action="{{ route('admin.course.destroy', $course->getKey()) }}" method="POST" style="display:inline-block;">' +
                                '{{ csrf_field() }}' +
                                '<input type="hidden" name="_method" value="delete">' +
                                '<input type="hidden" name="wp_article_id" value="' + row.wp_article_id + '">' +
                                '<input type="hidden" name="days" value="' + row.days + '">' +
                                '<button type="submit" class="btn btn-xs btn-danger btn-delete" title="Delete" data-toggle="tooltip">' +
                                '<span aria-hidden="true" class="fa fa-trash"></span>' +
                                '</button>' +
                                '</form>';
                        },
                    },
                ],
            });

            $('.js-data-ajax-article').select2({
                placeholder: 'Search for a repository',
                width: '100%',
                minimumInputLength: 3,
                ajax: {
                    type: 'GET',
                    url: "{{ route('admin.course.find') }}",
                    dataType: 'json',
                    data: function (params) {
                        return {
                            search: params.term,
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.post_title,
                                    id: item.ID,
                                };
                            }),
                        };
                    },
                    cache: true,
                },
            });

        });
    </script>
@endpush
