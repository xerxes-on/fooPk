@push('footer-scripts')
    <script>
        jQuery(document).ready(function ($) {
            const parent = $('#liableClients').parent().parent();
            const roleElem = $('#role');

            if ({{$consultantId}} !== parseInt(roleElem.val())) {
                parent.hide();
            }

            roleElem.on('change', function () {
                let role = this.value;

                if ({{$consultantId}} === parseInt(role)) {
                    parent.show();
                } else {
                    parent.hide();
                }
            });
        });
    </script>
@endpush
