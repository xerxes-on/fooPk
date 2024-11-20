<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Class PagesSeeder
 */
final class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $contentHTML = <<<'HTML'
<section>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!--PRICE HEADING START-->
                <div class="price-heading clearfix">
                    <p><strong>Schritt 1</strong></p>
                    <h1>Wähle ein Paket und starte deinen Foodpunk Lifestyle</h1>

                    <p>
                        Nach der Buchung erstellen unsere Ernährungswissenschaftler deinen individuellen Plan. Du kannst die gewählte Mitgliedschaft immer zum gewählten Laufzeitende kündigen.
                    </p>

                    <p>
                        Das erwartet dich:

                        <ul class="price-list-additional">
                            <li><strong>Individueller Ernährungsplan</strong> mit 90 Rezepten – genau auf deine Allergien und Lebensmittelwünsche abgestimmt und exakt für deinen Bedarf an Protein, Kohlenhydraten und Fett berechnet.</li>
                            <li>Die Möglichkeit unseren <strong>Experten-Support</strong> jederzeit zu kontaktieren.</li>
                            <li>Eine <strong>große Community</strong> mit tausenden Gleichgesinnten und zu jeder Zeit ein offenes Ohr.</li>
                            <li>Die <strong>praktische App:</strong> Erstelle auf Knopfdruck deine Einkaufsliste, halte deine Ergebnisse im Tagebuch fest und kreiere eigene Speed-Mahlzeiten mit unserem Baukasten.</li>
                            <li><strong>Große Flexibilität:</strong> Ein Plan, der sich an deinen Alltag und deinen Appetit anpasst. Tausche einzelne Zutaten oder ganze Mahlzeiten. Egal, was du tauschst, wir berechnen immer alles auf deinen Bedarf, so musst du nicht mitdenken, sondern kannst einfach genießen.</li>
                            <li><strong>Monatlich 10 neue Rezepte,</strong> die genau auf deinen Ernährungsplan abgestimmt sind.</li>
                            <li><strong>Monatliche Aktualisierung:</strong> Jeden Monat kannst du den Fragebogen neu ausfüllen und unsere Experten berechnen deinen individuellen Plan neu auf dein Gewicht, dein Ziel und alle weiteren Angaben.</li>
                            <li><strong>Tausende Rezept auf dem Marktplatz:</strong> Dein Ernährungsplan enthält 90 individuelle Rezepte, dazu monatlich 10 neue. Wenn du noch mehr möchtest, kannst du für Foodpunkte weitere kreative Rezepte auf dem Marktplatz shoppen. Wir stellen sicher, dass alle Rezepte, die dir auf dem Marktplatz angezeigt werden, genau auf deinen Bedarf berechnet sind.</li>
                        </ul>
                    </p>

                </div>
                <!--//PRICE HEADING END-->
            </div>
        </div>
    </div>

    <div class="container">

        <!--BLOCK ROW START-->
        <div class="row">

            <div class="col-md-3">
                <!--PRICE CONTENT START-->
                <div class="generic_content clearfix">

                    <!--HEAD PRICE DETAIL START-->
                    <div class="generic_head_price clearfix">

                        <!--HEAD CONTENT START-->
                        <div class="generic_head_content clearfix">

                            <!--HEAD START-->
                            <div class="head_bg"></div>
                            <div class="head">
                                <span>1 MONAT</span>
                            </div>
                            <!--//HEAD END-->

                        </div>
                        <!--//HEAD CONTENT END-->

                        <!--PRICE START-->
                        <div class="generic_price_tag clearfix">
                            <span class="price">
                                <span class="sign">€</span>
                                <span class="currency">19</span>
                                <span class="cent">,99</span>
                                <span class="month">/ Monat</span>
                            </span>
                        </div>
                        <!--//PRICE END-->

                    </div>
                    <!--//HEAD PRICE DETAIL END-->

                    <!--FEATURE LIST START-->
                    <div class="generic_feature_list">
                        <ul>
                            <li>Nur 4,62 € pro Woche</li>
                            <li>Monatlich kündbar</li>
                            <li>
                                Einmalig 69 €<br>
                                Startgebühr
                            </li>
                        </ul>
                    </div>
                    <!--//FEATURE LIST END-->

                    <!--BUTTON START-->
                    <div class="generic_price_btn clearfix">
                        <a href="javascript:void(0)" data-cb-type="checkout" data-cb-plan-id="1-monat">
                            Wählen &amp; weiter
                        </a>

                    </div>
                    <!--//BUTTON END-->

                </div>
                <!--//PRICE CONTENT END-->
            </div>

            <div class="col-md-3">
                <!--PRICE CONTENT START-->
                <div class="generic_content clearfix">

                    <!--HEAD PRICE DETAIL START-->
                    <div class="generic_head_price clearfix">

                        <!--HEAD CONTENT START-->
                        <div class="generic_head_content clearfix">

                            <!--HEAD START-->
                            <div class="head_bg"></div>
                            <div class="head">
                                <span>3 MONATE</span>
                            </div>
                            <!--//HEAD END-->

                        </div>
                        <!--//HEAD CONTENT END-->

                        <!--PRICE START-->
                        <div class="generic_price_tag clearfix">
                            <span class="price">
                                <span class="sign">€</span>
                                <span class="currency">39</span>
                                <span class="cent">,99</span>
                                <span class="month">/ 3 Monate</span>
                            </span>
                        </div>
                        <!--//PRICE END-->

                    </div>
                    <!--//HEAD PRICE DETAIL END-->

                    <!--FEATURE LIST START-->
                    <div class="generic_feature_list">
                        <ul>
                            <li>Nur 3,08 € pro Woche!</li>
                            <li><span>Spare 33 %!</span></li>
                            <li>
                                Einmalig 69 €<br>
                                Startgebühr
                            </li>
                        </ul>
                    </div>
                    <!--//FEATURE LIST END-->

                    <!--BUTTON START-->
                    <div class="generic_price_btn clearfix">
                        <a href="javascript:void(0)" data-cb-type="checkout" data-cb-plan-id="3-monate">
                            Wählen &amp; weiter
                        </a>
                    </div>
                    <!--//BUTTON END-->

                </div>
                <!--//PRICE CONTENT END-->
            </div>

            <div class="col-md-3">
                <!--PRICE CONTENT START-->
                <div class="generic_content active clearfix">

                    <!--HEAD PRICE DETAIL START-->
                    <div class="generic_head_price clearfix">

                        <!--HEAD CONTENT START-->
                        <div class="generic_head_content clearfix">

                            <!--HEAD START-->
                            <div class="head_bg"></div>
                            <div class="head">
                                <span>6 MONATE</span>
                            </div>
                            <!--//HEAD END-->

                        </div>
                        <!--//HEAD CONTENT END-->

                        <!--PRICE START-->
                        <div class="generic_price_tag clearfix">
                        <span class="price">
                            <span class="sign">€</span>
                            <span class="currency">69</span>
                            <span class="cent">,99</span>
                            <span class="month">/ 6 Monate</span>
                        </span>
                        </div>
                        <!--//PRICE END-->

                    </div>
                    <!--//HEAD PRICE DETAIL END-->

                    <!--FEATURE LIST START-->
                    <div class="generic_feature_list">
                        <ul>
                            <li>Nur 2,69 € pro Woche!</li>
                            <li><span>Spare 41 %!</span></li>
                            <li>
                                Einmalig 69 €<br>
                                Startgebühr
                            </li>
                        </ul>
                    </div>
                    <!--//FEATURE LIST END-->

                    <!--BUTTON START-->
                    <div class="generic_price_btn clearfix">
                        <a href="javascript:void(0)" data-cb-type="checkout" data-cb-plan-id="6-monate">
                            Wählen &amp; weiter
                        </a>
                    </div>
                    <!--//BUTTON END-->

                </div>
                <!--//PRICE CONTENT END-->
            </div>

            <div class="col-md-3">
                <!--PRICE CONTENT START-->
                <div class="generic_content clearfix">

                    <!--HEAD PRICE DETAIL START-->
                    <div class="generic_head_price clearfix">

                        <!--HEAD CONTENT START-->
                        <div class="generic_head_content clearfix">

                            <!--HEAD START-->
                            <div class="head_bg"></div>
                            <div class="head">
                                <span>12 MONATE</span>
                            </div>
                            <!--//HEAD END-->

                        </div>
                        <!--//HEAD CONTENT END-->

                        <!--PRICE START-->
                        <div class="generic_price_tag clearfix">
                        <span class="price">
                            <span class="sign">€</span>
                            <span class="currency">99</span>
                            <span class="cent">,99</span>
                            <span class="month">/ Jahr</span>
                        </span>
                        </div>
                        <!--//PRICE END-->

                    </div>
                    <!--//HEAD PRICE DETAIL END-->

                    <!--FEATURE LIST START-->
                    <div class="generic_feature_list">
                        <ul>
                            <li>Nur 1,92 € pro Woche!</li>
                            <li><span>Spare 58 %!</span></li>
                            <li>
                                Einmalig 69 €<br>
                                Startgebühr
                            </li>
                        </ul>
                    </div>
                    <!--//FEATURE LIST END-->

                    <!--BUTTON START-->
                    <div class="generic_price_btn clearfix">
                        <a href="javascript:void(0)" data-cb-type="checkout" data-cb-plan-id="12-monate">
                            Wählen &amp; weiter
                        </a>
                    </div>
                    <!--//BUTTON END-->

                </div>
                <!--//PRICE CONTENT END-->
            </div>

        </div>
        <!--//BLOCK ROW END-->

    </div>

</section>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p>Du kannst immer zum Laufzeitende per Email an info@foodpunk.de kündigen. Die Abbuchung in Höhe von 19,99 / 39,99 / 69,99 / 99,99 (je nach gewähltem Paket) erfolgt zu Beginn und dann im gewählten Intervall. Es werden einmal pro Monat 19,99 / einmal alle 3 Monate 39,99 / einmal alle 6 Monate 69,99 oder einmal pro Jahr 99,99 abgebucht (je nach gewähltem Paket). Die Startgebühr fällt nur einmalig zu Beginn an.</p>
            </div>
        </div>
    </div>
</footer>
HTML;

        Page::create([
            'de' => [
                'title'   => 'Pricing Table',
                'slug'    => 'foodpunk-experience-buchen',
                'content' => $contentHTML,
            ]
        ]);
    }
}
