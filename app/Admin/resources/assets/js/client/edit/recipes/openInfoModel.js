// Displays recipe details in a modal by fetching data from the server
export function openInfoModal(element, recipeId) {
    let route = window.FoodPunk.route.searchRecipesPreview;
    route = route.replace('%', recipeId);

    $.ajax({
        type: 'GET',
        url: route,
        dataType: 'json',
        beforeSend: function () {
            $(element).append('<span class="fa fa-spinner fa-spin" aria-hidden="true"></span>');
        },
        success: function (data) {
            $(element).find('span.fa.fa-spinner.fa-spin').remove();
            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: data.message,
                });
                return;
            }
            const modal = $('#recipeDetailsModal');
            modal.modal('show');
            modal.find('.modal-title').html(data.title);
            modal.find('.modal-body').html(data.data);
        },
        error: function (jqXHR) {
            $(element).find('span.fa.fa-spinner.fa-spin').remove();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON.message,
            });
            console.error(jqXHR);
        },
    });
}
