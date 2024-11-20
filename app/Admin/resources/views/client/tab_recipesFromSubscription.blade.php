<div class="text-left" style="margin-bottom: 10px">
    <button type="button" id="generate-recipes" class="btn btn-info ladda-button" data-style="expand-right"
            onclick="generateRecipe()">
        <span class="ladda-label">@lang('common.subscription_recipes_generate')</span>
    </button>
</div>

<table id="recipesByChallenge" class="table table-striped table-bordered" style="width:100%">
    <thead>
    <tr>
        <th>#</th>
        <th>@lang('common.image')</th>
        <th>@lang('common.title')</th>
        <th>@lang('common.subscription') </th>
        <th>@lang('common.date')</th>
        <th>@lang('common.meal')</th>
        <th>@lang('common.invalid')</th>
        <th>@lang('common.KCAL')</th>
        <th>@lang('common.KH')</th>
        <th>@lang('common.recipe_calculated')</th>
        <th>@lang('common.diets')</th>
    </tr>
    </thead>
</table>

@push('footer-scripts')
    <script>
        {{--
            define global variable. Its important to init DataTable only once the tab is show.
            Otherwise, the DataTable will be initialized multiple times and it will cause an error.
            Also initialisation of DataTable outside of active tab will cause pagination to draw incorrectly.
         --}}
        let $recipesByChallenge;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            let target = $($(e.target).attr("href")).find('#recipesByChallenge')
            if (target.length && !$.fn.DataTable.isDataTable(target)) {
                // target.DataTable().draw(false);
                $recipesByChallenge = $('#recipesByChallenge').DataTable({
                    lengthChange: false,
                    processing: true,
                    serverSide: true,
                    pageLength: 12,
                    searchDelay: 450,
                    paging: {
                        type: 'input',
                        buttons: 10,
                    },
                    ajax: {
                        url: '/admin/datatable/async',
                        data: function (d) {
                            d.method = 'recipesByUserFromActiveChallenge';
                            d.userId = '{{ $client->id }}';
                        },
                    },
                    order: [[4, 'asc']],
                    columns: [
                        {data: 'id', width: '5%'},
                        {data: '_image', orderable: false},
                        {
                            data: 'title',
                            width: '30%',
                            orderable: false,
                        },
                        {data: 'challenge_title', width: '10%'},
                        {data: 'meal_date', width: '8%'},
                        {data: 'meal_time', width: '8%'},
                        {
                            data: 'invalid',
                            orderable: false,
                            width: '5%',
                        },
                        {
                            data: '_kcal',
                            orderable: false,
                            width: '8%',
                        },
                        {
                            data: '_kh',
                            orderable: false,
                            width: '8%',
                        },
                        {
                            data: 'calculated',
                            orderable: false,
                            width: '10%',
                        },
                        {
                            data: '_diets',
                            width: '25%',
                            orderable: false,
                        },
                    ],
                });
            }
        });

        function generateRecipe() {
            Swal.fire({
                title: '{{trans('admin.messages.confirmation')}}',
                text: '{{trans('admin.messages.revert_warning')}}',
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            })
                .then((result) => {
                    if (!result.value) {
                        return;
                    }
                    Swal.fire({
                        title: '{{trans('admin.messages.wait')}}',
                        text: '{{trans('admin.messages.in_progress')}}',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('admin.recipes.generate-to-subscription') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            userId: '{{ $client->id }}',
                        },
                        success: function (data) {
                            if (data.success === true) {
                                Swal.hideLoading();
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{trans('admin.messages.saved')}}',
                                    html: data.message,
                                });
                            } else {
                                Swal.hideLoading();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    html: data.message,
                                });
                            }
                            // refresh DataTable
                            $recipesByChallenge.ajax.reload();
                        },
                    });
                });
        }
    </script>
@endpush