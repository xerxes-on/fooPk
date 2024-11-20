@php if(empty($url)) {return;} @endphp
        <!-- Google fonts & Google Preconnect -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" onload="this.rel = 'stylesheet'" href="{{$url}}">
<noscript>
    <link rel="stylesheet" href="{{$url}}">
</noscript>