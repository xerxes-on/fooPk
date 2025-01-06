/**
 * Opens a modal displaying detailed information about a specific recipe.
 *
 * @function openInfoModal
 * @param {HTMLElement} element - The button or element triggering the modal.
 * @param {number|string} recipeId - The ID of the recipe to fetch details for.
 *
 * @description
 * - Sends a GET request to fetch recipe details from the server.
 * - Displays a loading spinner on the triggering element during the request.
 * - Populates and shows the modal with the fetched recipe details on success.
 * - Displays an error alert if the request fails or the response indicates an error.
 */
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
