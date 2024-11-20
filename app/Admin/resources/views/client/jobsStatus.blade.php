{{-- pushing to vendor/laravelrus/sleepingowl/resources/views/default/_layout/inner.blade.php --}}
@push('content.top')
    <div class="recalculation-progress">
        <p>Next Recalculation jobs check update in: <b id="js-check-refresh">0</b></p>
    </div>
    <div class="alert alert-warning" id="calculation-status" role="alert" style="display: none"></div>
@endpush

@push('footer-scripts')
    <script>
        jQuery(document).ready(function ($) {
            var time = 30;
            setInterval(function () {
                $('#js-check-refresh').html(--time);
            }, 1000);
            setInterval(function () {
                checkCalculationStatus();
                time = 30;
            }, 30000);
        });

        function checkCalculationStatus() {
            $.ajax({
                type: 'GET',
                url: "{{ route('admin.recipes.check-calculation-status', ['userId' => $client->id]) }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                }, beforeSend: function () {
                    $('#calculation-status').html('<span class="fa fa-spinner fa-spin" aria-hidden="true"></span>');
                },
                success: function (data) {
                    if (data.success === true) {
                        $('#calculation-status').removeAttr('class').addClass(`alert alert-${data.status}`).html(data.message).show();
                    } else {
                        $('#calculation-status').hide();
                    }
                },
                error: function (data) {
                    console.error(data);
                },
            });
        }
    </script>
@endpush
