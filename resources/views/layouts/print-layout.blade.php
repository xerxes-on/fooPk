<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php $fonts = [['family'=>'Nunito Sans', 'wght' => [300,400,500,600,700,800]]];@endphp
    <x-googleFonts :fonts="$fonts"/>

    <!-- Styles -->
    <link href="{{ mix('css/loader.css') }}" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @yield('styles')
    <style>
        body {
            padding-top: 0;
        }
    </style>
</head>
<body>
<div class="loading" id="loading" style="display: none">Loading&#8230;</div>
<div class="container" id="app">
    @yield('content')
</div>
<!--PDF_REMOVE_IT_START-->
<script>
    window.onload = function () {

        var elements = document.getElementsByClassName('print-hide');
        while (elements.length > 0) {
            elements[0].parentNode.removeChild(elements[0]);
        }

        //var ua = navigator.userAgent.toLowerCase();
        //var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
        var generatePdf = {{ ((request()->has('is_app') || Cookie::get('is_app')) || request()->has('pdf')) ? 'true' : 'false' }};

        if (generatePdf) {
            var elements_for_hide = document.getElementsByClassName('wkhtmltopdf-print-hide');
            while (elements_for_hide.length > 0) {
                elements_for_hide[0].parentNode.removeChild(elements_for_hide[0]);
            }
        }

        if (generatePdf) {

            jQuery(document).ready(function ($) {

                var documentHtml = document.documentElement.innerHTML;

                $('#loading').show();

                data = new FormData();
                data.set('html', documentHtml);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', "{{ route('generate_pdf') }}", true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                xhr.send(data);
                xhr.onload = function () {
                    var contentType = xhr.getResponseHeader('content-type');
                    var fileName = xhr.getResponseHeader('content-disposition').split('filename=')[1].split(';')[0];

                    var link = document.createElement('a');
                    document.body.appendChild(link);
                    link.setAttribute('type', 'hidden');
                    link.setAttribute('target', '_self');
                    link.href = 'data:' + contentType + ';base64,' + xhr.responseText;
                    link.download = fileName;
                    link.click();
                    document.body.removeChild(link);
                    $('#loading').hide();
                };
            });
        } else {
            window.print();
        }
    };
</script>
<!--PDF_REMOVE_IT_END-->
</body>
</html>