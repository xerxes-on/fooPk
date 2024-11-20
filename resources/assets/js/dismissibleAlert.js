(function ($) {

    'use strict';

    // Attach event listeners to the close button
    $('.alert-dismissible[data-dismiss-duration]').on('closed.bs.alert', function () {
        if (!this.dataset.dismissId) {
            return;
        }
        const dismissData = {
            'id': this.dataset.dismissId,
            'lastShown': Math.round(new Date().getTime() / 1000), // we must convert milliseconds from date into seconds
            'expires': new Date(
                Date.now() + this.dataset.dismissDuration * 1000).toUTCString(),
        };
        document.cookie = `alert_${dismissData.id}=${dismissData.lastShown}; expires=${dismissData.expires}; samesite=strict; path=/;`;
    });

})(jQuery);