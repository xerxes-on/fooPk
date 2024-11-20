jQuery(document).ready(function ($) {

    // Loop through all instances of the image field
    $('.form-group.image').each(function (index) {

        // Find DOM elements under this form-group element
        var $mainImage = $(this).find('#mainImage');
        var $uploadImage = $(this).find('#uploadImage');
        var $hiddenImage = $(this).find('#hiddenImage');
        var $oldImage = $(this).find('#oldImage');

        // Hide 'Remove' button if there is no image saved
        if (!$mainImage.attr('src')) {
            $remove.hide();
        }

        // Initialise hidden form input in case we submit with no change
        $hiddenImage.val($oldImage.val());

        // Only initialize cropper plugin if crop is set to true
        $('#remove').click(function () {
            $mainImage.attr('src',
                'https://via.placeholder.com/150x150/00a65a/ffffff/?text=A');
            $hiddenImage.val('');
            $remove.hide();
        });

        $uploadImage.change(function () {
            var fileReader = new FileReader(),
                files = this.files,
                file;

            //console.log(files.length);

            if (!files.length) {
                return;
            }
            file = files[0];

            if (/^image\/\w+$/.test(file.type)) {
                fileReader.readAsDataURL(file);
                fileReader.onload = function () {
                    $uploadImage.val('');
                    $mainImage.attr('src', this.result);
                    $hiddenImage.val(this.result);
                    $remove.show();
                };
            } else {
                alert('Please choose an image file.');
            }
        });

    });
});