@extends('layouts.app')

@section('title', trans('common.posts_list'))

@section('styles')
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.fp.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <button type="button" class="btn btn-tiffany pull-right create-post hidden-xs">
                    <span>@lang('common.create_post')</span>
                </button>

                <!-- Nav content list -->
                <ul class="content-links" role="navigaion">
                    <li class="content-links_item {{ active('diary.statistics') }}">
                        <a href="{{ route('diary.statistics') }}">@lang('common.diary_statistics')</a>
                    </li>
                    <li class="content-links_item {{ active('posts.list') }}">
                        <a href="{{ route('posts.list') }}">@lang('common.posts')</a>
                    </li>
                </ul>

                <button type="button" class="btn btn-tiffany pull-right create-post visible-xs">
                    <span>@lang('common.create_post')</span>
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-offset-1 col-md-offset-2 col-sm-10 col-md-8">
                <div class="diary_posts">
                    @foreach($posts as $post)
                        <x-post :post="$post"></x-post>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="diaryModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">

                <div class="modal-container">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h3 class="modal-title">@lang('common.create_post')</h3>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body transparent-body"></div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="{{ mix('vendor/ion-rangeslider/ion.rangeSlider.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $('.btn.create-post').on('click', function () {
                getPostForm();
            });

            $('.btn.edit-post').on('click', function () {
                let postId = $(this).attr('data-post');
                getPostForm(postId);
            });
        });

        function getPostForm(postId) {
            postId = postId || null;
            $.ajax({
                type: 'GET',
                url: "{{ route('post.form') }}",
                dataType: 'json',
                data: {postId: postId},
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    if (data.success) {
                        $('#loading').hide();
                        $('#diaryModal .modal-body').empty().append(data.payload);
                        initIonRangeSlider();
                        initUploadImage();
                        $('#diaryModal').modal('show');
                    }
                },
            });
        }

        function initIonRangeSlider() {
            let mood = $('#diaryModal .mood').attr('mood');
            $('#diaryModal .mood').ionRangeSlider({
                min: 0,
                max: 10,
                step: 1,
                from: mood,
                grid: true,
                grid_snap: true,
            });
        }

        function initUploadImage() {
            // Find DOM elements under this form-group element
            var $mainImage = $('#mainImage');
            var $uploadImage = $('#uploadImage');
            var $hiddenImage = $('#hiddenImage');
            var $remove = $('#remove');
            var $oldImage = $('#oldImage');

            // Hide 'Remove' button if there is no image saved
            if (!$mainImage.attr('src')) {
                $remove.hide();
            }

            // Initialise hidden form input in case we submit with no change
            $hiddenImage.val($oldImage.val());

            // Only initialize cropper plugin if crop is set to true
            $('#remove').click(function () {
                $mainImage.attr('src', 'https://via.placeholder.com/150x150/00a65a/ffffff/?text=A');
                $hiddenImage.val('');
                $remove.hide();
            });

            $uploadImage.change(function () {
                var fileReader = new FileReader(),
                    files = this.files,
                    file;

                if (!files.length) {
                    return;
                }
                file = files[0];

                if (/^image\/\w+$/.test(file.type)) {
                    fileReader.readAsDataURL(file);
                    fileReader.onload = function () {
                        {{-- $uploadImage.val(""); --}}
                        $mainImage.attr('src', this.result);
                        $hiddenImage.val(this.result);
                        $remove.show();
                    };
                } else {
                    alert('Please choose an image file.');
                }
            });
        }
    </script>
@append