@if (!request()->has('is_app') && !Cookie::get('is_app'))
    @php
        $footerData = [
            [
                'de' => [
                    'name' => 'data_prodection',
                    'link' => 'https://foodpunk.com/de/datenschutz/',
                ],
                'en' => [
                    'name' => 'data_prodection',
                    'link' => 'https://foodpunk.com/en/privacy-policy/',
                ]
            ],
            [
                'de' => [
                    'name' => 'disclaimer',
                    'link' => 'https://foodpunk.com/de/haftungsausschluss/',
                ],
                'en' => [
                    'name' => 'disclaimer',
                    'link' => 'https://foodpunk.com/disclaimer/',
                ]
            ],
            [
                'de' => [
                    'name' => 'imprint',
                    'link' => 'https://foodpunk.com/de/impressum/',
                ],
                'en' => [
                    'name' => 'imprint',
                    'link' => 'https://foodpunk.com/imprint/',
                ]
            ],
            [
                'de' => [
                    'name' => 'conditions',
                    'link' => 'https://foodpunk.com/de/agb/',
                ],
                'en' => [
                    'name' => 'conditions',
                    'link' => 'https://foodpunk.com/terms/',
                ]
            ],
            [
                'de' => [
                    'name' => 'revocation',
                    'link' => 'https://foodpunk.com/de/widerruf/',
                ],
                'en' => [
                    'name' => 'revocation',
                    'link' => 'https://foodpunk.com/revocation/',
                ]
            ]
        ];

        $currentLocale = app()->getLocale();
    @endphp

    <div class="footer">
        <div class="footer-nav">
            <div class="container">
                <ul class="footer-nav_bottom-nav">
                    @foreach($footerData as $footerItem)
                        @if(key_exists($currentLocale, $footerItem))
                            <li>
                                <a href="{{ $footerItem[$currentLocale]['link'] }}" rel="noindex"
                                   target="_blank">{{ trans('common.'. $footerItem[$currentLocale]['name']) }}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container clearfix">
                <ul class="footer-bottom_social">
                    <li>
                        <a href="//facebook.com/Foodpunk" target="_blank">
                            <i class="fa fa-facebook"></i>
                        </a>
                    </li>
                    <li>
                        <a href="//instagram.com/foodpunk" target="_blank">
                            <i class="fa fa-instagram"></i>
                        </a>
                    </li>
                </ul>
                <p class="footer-bottom_info">Copyright © {{ date('Y') }} Foodpunk</p>
            </div>
        </div>
    </div>
@endif