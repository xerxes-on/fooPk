$(document).on('click', '#uploadImage', function (event) {
    event.preventDefault();
    var updateID = $(this).attr('data-inputid'); // Btn id clicked
    var elfinderUrl = '/admin/elfinder/popup/';

    // trigger the reveal modal with elfinder inside
    var triggerUrl = elfinderUrl + updateID;
    $.colorbox({
        href: triggerUrl,
        fastIframe: true,
        iframe: true,
        width: '70%',
        height: '95%',
    });

});

// function to update the file selected by elfinder
// Function is called like this. So leave as it is
window.parent.processSelectedFile = function (filePath, requestingField) {
    $('#' + requestingField).val(filePath).trigger('change');
};

function getDataUri(url, callback) {
    var image = new Image();

    image.onload = function () {
        var canvas = document.createElement('canvas');
        canvas.width = this.naturalWidth; // or 'width' if you want a special/scaled size
        canvas.height = this.naturalHeight; // or 'height' if you want a special/scaled size

        canvas.getContext('2d').drawImage(this, 0, 0);

        // Get raw image data
        //callback(canvas.toDataURL('image/png').replace(/^data:image\/(png|jpg);base64,/, ''));

        // ... or get as Data URI
        callback(canvas.toDataURL('image/png'));
    };

    image.src = url;
}

jQuery(document).ready(function ($) {

    // Loop through all instances of the image field
    $('.form-group.image').each(function (index) {

        // Find DOM elements under this form-group element
        const $mainImage = $(this).find('#mainImage');
        const $uploadImage = $(this).find('#uploadImage');
        const $hiddenImage = $(this).find('#hiddenImage');
        const $oldImage = $(this).find('#oldImage');
        const $remove = $(this).find('#remove');

        // Hide 'Remove' button if there is no image saved
        if (!$mainImage.attr('src')) {
            $remove.hide();
        }

        // Initialise hidden form input in case we submit with no change
        $hiddenImage.val($oldImage.val());

        $(this).find('#remove').click(function () {
            $mainImage.attr('src',
                'https://via.placeholder.com/150/00a65a/ffffff/?text=A'); // TODO: should be inserted from config somehow
            $hiddenImage.val('');
            $remove.hide();
        });

        $uploadImage.change(function () {
            getDataUri('/uploads/' + this.value, function (dataUri) {
                $mainImage.attr('src', dataUri);
            });
            $hiddenImage.val('uploads/' + this.value);
            $remove.show();
        });

    });
});
