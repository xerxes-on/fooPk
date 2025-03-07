/**
 * Adds randomized recipes to a user based on input criteria.
 *
 * @async
 * @function addRandomizeRecipes
 *
 * @description
 * - Prompts the user to input randomization settings using `inputRecipeAmount`.
 * - Sends a POST request to the server with the randomization parameters.
 * - Displays success or error messages based on the server's response.
 *
 * @returns {Promise<void>} Resolves when the process completes or fails.
 */
export async function addRandomizeRecipes() {
    const initFormData = await inputRecipeAmount();
    if (!initFormData) return;

    let {
        amount,
        seasons,
        distribution_type,
        breakfast_snack,
        lunch_dinner,
        recipes_tag,
        distribution_mode
    } = initFormData;

    if (!amount || amount === '0') return;

    $.ajax({
        type: 'POST',
        url: window.FoodPunk.route.recipesAddToUserRandom,
        dataType: 'json',
        data: {
            _token: $('meta[name=csrf-token]').attr('content'),
            userIds: [window.FoodPunk.pageInfo.clientId],
            amount,
            seasons,
            distribution_type,
            breakfast_snack,
            lunch_dinner,
            recipes_tag,
            distribution_mode,
        },
        beforeSend: function () {
            Swal.fire({
                title: window.FoodPunk.i18n.wait,
                text: window.FoodPunk.i18n.inProgress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        },
        success: function (data) {
            Swal.hideLoading();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: window.FoodPunk.i18n.saved,
                    html: data.message,
                });
                return;
            }
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: data.message,
            });
        },
        error: function (jqXHR) {
            Swal.hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON.message,
            });
            console.error(jqXHR);
        },
    });
}

/**
 * Displays a form for the user to input randomization settings for recipes.
 *
 * @async
 * @function inputRecipeAmount
 *
 * @description
 * - Displays a Swal modal with randomization settings form.
 * - Fetches the form template via an AJAX GET request.
 * - Collects and returns the user-inputted values from the form.
 *
 * @returns {Promise<Object|null>} Resolves to an object containing the form values or `null` if canceled.
 */
async function inputRecipeAmount() {
    const {value: formValues} = await Swal.fire({
        title: window.FoodPunk.i18n.randomizeRecipesSettings,
        icon: 'question',
        html: `<div id="randomizeRecipeComponent"></div>`,
        willOpen: () => {
            Swal.showLoading();
            $.get(window.FoodPunk.route.randomizeRecipeTemplate, {}, (payload) => {
                Swal.hideLoading();
                Swal.getHtmlContainer().querySelector('#randomizeRecipeComponent').innerHTML = payload;
            });
        },
        preConfirm: () => {
            const container = Swal.getHtmlContainer();
            const seasons = Array.from(container.querySelectorAll('.selected_seasons'))
                .filter((el) => el.checked)
                .map((el) => el.value);

            return {
                amount: container.querySelector('input[name="amount_of_recipes"]').value,
                seasons,
                distribution_type: container.querySelector('input[name="distribution_type"]:checked')?.value,
                breakfast_snack: container.querySelector('input[name="breakfast_snack"]').value,
                lunch_dinner: container.querySelector('input[name="lunch_dinner"]').value,
                recipes_tag: container.querySelector('input[name="recipes_tag"]:checked')?.value,
                distribution_mode: container.querySelector('input[name="distribution_mode"]:checked')?.value,
            };
        },
    });

    return formValues;
}
