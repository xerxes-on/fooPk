<!DOCTYPE HTML>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Foodpunk MeinPlan - Keine Panik</title>
    {{-- TODO: Untranslated page --}}
    @php $fonts = [
    	['family'=>'Open Sans', 'wght' => [400,700]],
    	['family'=>'Poppins', 'wght' => [400,500]]
    ];
    @endphp
    <x-googleFonts :fonts="$fonts"></x-googleFonts>
    <link href="{{ mix('vendor/ionicons/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ mix('css/error-page.css') }}" rel="stylesheet">
</head>

<body>
<div class="main-area center-text"
     style="background-image:url({{ asset('images/error-page/maintenance-background.jpg') }});">
    <div class="display-table">
        <div class="display-table-cell">

                <span class="logo-wrapper">
                    <img src="{{ asset('/images/icons/foodpunk-logo-black.svg') }}"
                         alt="{{ config('app.name', 'Foodpunk') }}">
                </span>

            <h1 class="title"><b>Keine Panik</b></h1>
            <p class="desc font-white">Wir arbeiten kurz an der Seite und sind gleich wieder zurück!</p>

            <ul class="social-btn">
                <li class="list-heading">Bleibe stets auf dem Laufenden</li>
                <li><a href="https://www.facebook.com/Foodpunk"><i class="ion-social-facebook"></i></a></li>
                <li><a href="https://www.instagram.com/foodpunk.de"><i class="ion-social-instagram-outline"></i></a>
                </li>
            </ul>

        </div><!-- display-table -->
    </div><!-- display-table-cell -->
</div><!-- main-area -->
</body>

</html>