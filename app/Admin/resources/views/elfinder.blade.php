<!-- jQuery and jQuery UI (REQUIRED) -->
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>

<!-- elFinder CSS (REQUIRED) -->
<link rel="stylesheet" type="text/css" href="{{ asset('packages/barryvdh/elfinder/css/elfinder.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('packages/barryvdh/elfinder/css/theme.css') }}">

<!-- jQuery and jQuery UI (REQUIRED) -->
<!--[if lt IE 9]>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script
        src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> {{-- TODO: check if local version suits the need --}}
<!--<![endif]-->
<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

{{--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>--}}

<!-- elFinder JS (REQUIRED) -->
{{--<script src="{{ asset('packages/barryvdh/elfinder/js/elfinder.min.js') }}"></script>--}}

<!-- Extra contents editors (OPTIONAL) -->
{{--<script src="js/extras/editors.default.min.js"></script>--}}

<!-- GoogleDocs Quicklook plugin for GoogleDrive Volume (OPTIONAL) -->
<!--<script src="js/extras/quicklook.googledocs.js"></script>-->

<script type="text/javascript" charset="utf-8">
    // Documentation for client options:
    // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
    $().ready(function () {
        $('#elfinder').elfinder({
            // set your elFinder options here
            @if($locale)
            lang: '{{ $locale }}', // locale
            @endif
            customData: {
                _token: '{{ csrf_token() }}',
            },
            cssAutoLoad: false,
            baseUrl: '../packages/barryvdh/elfinder/',
            url: '{{ route("elfinder.connector") }}',  // connector URL
            soundPath: '{{ asset('packages/barryvdh/elfinder/sounds') }}',
        });
    });
</script>

<div id="elfinder"></div>