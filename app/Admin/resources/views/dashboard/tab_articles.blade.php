<div class="row text-left">
    <div class="col-md-11">
        <div class="form-group">
            <select class="form-control js-data-ajax-article" name="wp_article_id">
                @if($article->isNotEmpty())
                    <option value="{{ $article['ID'] }}">{{ $article['post_title'] }}</option>
                @endif
            </select>
        </div>
    </div>

    <div class="col-md-1">
        <button type="button" id="clear-user-dashboard" class="btn btn-danger">
            <i class="fas fa-trash"></i>
        </button>
    </div>

</div>

@push('footer-scripts')
    <script>
        jQuery(document).ready(function ($) {

            $('.js-data-ajax-article').select2({
                placeholder: 'Search for a repository',
                minimumInputLength: 3,
                allowClear: true,
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

            $('#clear-user-dashboard').on('click', function (e) {
                $('.js-data-ajax-article').val('').trigger('change');
            });
        });
    </script>
@endpush