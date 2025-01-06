(function ($) {

    'use strict';

    $(document).on('change', '#js-ingredient-switcher', function () {
        const convertToPieces = this.checked;
        // Recipes
        $('.js-convert-ingredients[data-is-convertable="1"]').each(function (index, item) {
            item = $(item);
            const amountElement = item.find('.ingredient-amount-anchor');
            const textElement = item.find('.ingredient-title-anchor');
            const fractionMap = JSON.parse(amountElement.attr('data-fraction-map'));
            let piece = amountElement.attr('data-piece');
            let modifier = parseInt(item.attr('data-portion-modifier'));
            modifier = (isNaN(modifier) || modifier < 1) ? 1 : modifier;
            piece = piece * modifier;
            let pieceFraction = piece - Math.floor(piece);
            pieceFraction = pieceFraction.toString().replace(',', '.');
            let fractionAmount = fractionMap[pieceFraction] || '';
            if (convertToPieces) {
                let text = piece - pieceFraction;
                text = text > 0 ? text + fractionAmount : fractionAmount;
                amountElement.text(text);
                textElement.text(
                    (piece > 0 && piece < 2) ?
                        textElement.attr('data-alternative-unit') + ' ' + textElement.attr('data-text') :
                        textElement.attr('data-alternative-unit') + ' ' + textElement.attr('data-text-multiple')
                );
                return;
            }
            amountElement.text(amountElement.attr('data-amount') * modifier + ' ' + amountElement.attr('data-unit'));
            textElement.text(textElement.attr('data-text'));
        });
        // Shopping list
        $('.shopping-list_ingredients_list_item[data-is-convertable="1"]').each(function (index, item) {
            item = $(item);
            const amountElement = item.find('.shopping-list_ingredients_list_item_amount');
            const textElement = item.find('.shopping-list_ingredients_list_item_text');
            const piece = amountElement.attr('data-piece');
            const fractionMap = JSON.parse(amountElement.attr('data-fraction-map'));
            let pieceFraction = piece - Math.floor(piece);
            pieceFraction = pieceFraction.toString().replace(',', '.');
            let fractionAmount = fractionMap[pieceFraction] || '';

            if (convertToPieces) {
                let textAmount = piece - pieceFraction;
                textAmount = textAmount > 0 ? textAmount + fractionAmount : fractionAmount;

                amountElement.text(textAmount + ' ' + amountElement.attr('data-alternative-unit'));
                textElement.text((piece > 0 && piece < 2) ? textElement.attr('data-text') : textElement.attr('data-text-multiple'));
                return;
            }
            amountElement.text(amountElement.attr('data-amount') + ' ' + amountElement.attr('data-unit'));
            textElement.text(textElement.attr('data-text'));
        });

    });
})(jQuery);