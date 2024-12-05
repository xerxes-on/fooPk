/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/*
 |--------------------------------------------------------------------------
 | Base configuration
 |--------------------------------------------------------------------------
 |
 | Here we initialize Mix itself and bring in additional configuration
 | upon requirements.
 | Additional documentation is found here:
 | https://laravel-mix.com/docs/6.0/api
 */
const mix = require('laravel-mix');
// mix.webpackConfig({
// optimization: {
//     providedExports: false,
//     sideEffects: false,
//     usedExports: false
// }
// stats: {
//     children: true
// }
// });

/*
 |--------------------------------------------------------------------------
 | Paths Config
 |--------------------------------------------------------------------------
 |
 | Project common paths are defined here.
 | Additionally we define source and destination folders.
 */
let paths = {
	folders: {
		js: 'js',
		css: 'css',
		sass: 'sass',
		fonts: 'fonts',
		img: 'images',
		node: 'node_modules',
	},
	public: 'public',
	assets: 'resources/assets',
};
paths.src = {
	js: `${paths.assets}/${paths.folders.js}`,
	sass: `${paths.assets}/${paths.folders.sass}`,
	fonts: `${paths.assets}/${paths.folders.fonts}`,
	img: `${paths.assets}/${paths.folders.img}`,
};
paths.modules = {
	admin: {
		sass: `app/Admin/${paths.src.sass}`,
		js: `app/Admin/${paths.src.js}`
	},
	flexmeal: {
		sass: `modules/FlexMeal/${paths.src.sass}`,
		js: `modules/FlexMeal/${paths.src.js}`
	},
    ingredient: {
        sass: `modules/Ingredient/${paths.src.sass}`,
        js: `modules/Ingredient/${paths.src.js}`
    },
    pushNotification: {
        sass: `modules/PushNotification/${paths.src.sass}`,
        js: `modules/PushNotification/${paths.src.js}`
	},
	shoppingList: {
		sass: `modules/ShoppingList/${paths.src.sass}`,
		js: `modules/ShoppingList/${paths.src.js}`
    },
    course: {
        sass: `modules/Course/${paths.src.sass}`,
        js: `modules/Course/${paths.src.js}`
	}
}
paths.dest = {
	js: `${paths.public}/${paths.folders.js}`,
	css: `${paths.public}/${paths.folders.css}`,
	fonts: `${paths.public}/${paths.folders.fonts}`,
	img: `${paths.public}/${paths.folders.img}`,
	vendor: `${paths.public}/vendor`,
};

/*
 |--------------------------------------------------------------------------
 | Main Application - Client side
 |--------------------------------------------------------------------------
 | Names used in here describe output name, not the source one.
 |
 | app.js - contains vju components for user side application.
 | error-page.js - scripts for Error page only
 | customImage.js - commonly used in views/post/edit.blade.php | Not used now!
 | app.css - Main css
 | modal.css - used in recipes.feed.list.blade | TODO: remove after resolving the issue of duplication
 | loader.css - contains css preloader
 | error-page.css - styles for Error page only
 | styles.css - styles for Error page only
 | pricing-page.css - styles for Pricing page only
 | pricingTable.css // Not used now
 | Copied images and fonts
 */
mix
    .js(`${paths.src.js}/app.js`, paths.dest.js).
		vue({version: 2}).
		js(`${paths.src.js}/navigation.js`, paths.dest.js).js(`${paths.modules.shoppingList.js}/shopping-list.js`, paths.dest.js).
		js(`${paths.src.js}/dismissibleAlert.js`, paths.dest.js).
		js(`${paths.src.js}/questionnaireValidation.js`, paths.dest.js).js(`${paths.src.js}/error-page.js`, paths.dest.js).js(`${paths.modules.course.js}/course.js`, `${paths.dest.js}/course.js`).
    // .js(`${paths.src.js}/customImage.js`,paths.dest.js) | not used now
    sass(`${paths.src.sass}/app.scss`, paths.dest.css).
		sass(`${paths.src.sass}/modal.scss`, paths.dest.css).
		sass(`${paths.src.sass}/loader.scss`, paths.dest.css).sass(`${paths.src.sass}/pages/error-page/styles.scss`, `${paths.dest.css}/error-page.css`).sass(`${paths.src.sass}/pages/pricing-page/style.scss`, `${paths.dest.css}/pricing-page.css`).sass(`${paths.src.sass}/pages/questionnaire/style.scss`, `${paths.dest.css}/questionnaire.css`).sass(`${paths.modules.flexmeal.sass}/flexmeal.scss`, `${paths.dest.css}/flexmeal.css`).sass(`${paths.modules.course.sass}/course.scss`, `${paths.dest.css}/course.css`).sass(`${paths.modules.course.sass}/articles.scss`, `${paths.dest.css}/articles.css`).sass(`${paths.src.sass}/pages/choose-device/styles.scss`, `${paths.dest.css}/choose-device.css`)
		//.sass(`${paths.assets}/${paths.sass}/pricingTable.scss`, `${paths.dest.css}/css`) // Not used now
	.copyDirectory(paths.src.fonts, paths.dest.fonts)
	.copyDirectory(paths.src.img, paths.dest.img)
	.copy('./node_modules/vue-select/dist/vue-select.js.map', paths.dest.js);

/*
 |--------------------------------------------------------------------------
 | Main Application - Admin side
 |--------------------------------------------------------------------------
 | Names used in here describe output name, not the source one.
 |
 | admin-custom.js - contains vju components for admin part.
 | admin.js - contains jQuery parts that are commonly used in Sections/{Challenge, Recipe, Users},
 | TODO: Probably should be separated in correct way (chunked) or organized correctly inside source
 | customSearch.js - commonly used in Sections/{Challenge, Recipe, Ingredients}.
 | users.js - used only in admin (boostrap file).
 | elfinderPopupImage.js - commonly used in Sections/{Challenge, Recipe, Ingredients, Inventory}.
 | ingredientCategories.js - used only in Sections/IngredientCategories.
 | ingredientCategorySelect.js - used only in Sections/Allergy.
 | ingredients.js - used only in Sections/Ingredients.
 |
 | switch.scss - used only in Sections/Users.
 | progressBar.css - used only in admin (boostrap file).
 | ingredientCategories.css - used only Sections/ingredientCategories.
 | dataTablesPagination.css - used only in admin (boostrap file).
 | customImage.css - used only in Sections/{Challenge, Recipe, Ingredients, Inventory}.
 | customAdmin.css - used only in admin (boostrap file).
 */
mix.js(`${paths.modules.admin.js}/admin.js`,
		`${paths.dest.js}/admin/admin-custom.js`).vue({version: 2})
    .js(`${paths.modules.admin.js}/admin-components.js`, `${paths.dest.js}/admin/admin.js`)
    .js(`${paths.modules.pushNotification.js}/admin/notifications.js`, `${paths.dest.js}/admin/notifications.js`)
    .js(`${paths.modules.admin.js}/customSearch.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.admin.js}/dTInputPaginationPlugin.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.admin.js}/users.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.admin.js}/elfinderPopupImage.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.ingredient.js}/admin/ingredientCategories.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.ingredient.js}/admin/ingredientCategorySelect.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.ingredient.js}/admin/ingredients.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.admin.js}/common.js`, `${paths.dest.js}/admin`)
    .js(`${paths.modules.ingredient.js}/admin/sections/ingredients.js`, `${paths.dest.js}/admin/ingredients`)
    .js(`${paths.modules.ingredient.js}/admin/sections/ingredient-tags.js`, `${paths.dest.js}/admin/ingredients`)
    .js(`${paths.modules.ingredient.js}/admin/sections/ingredient-categories.js`, `${paths.dest.js}/admin/ingredients`)
    .js(`${paths.modules.admin.js}/recipes/recipes.js`, `${paths.dest.js}/admin/recipes`)
    .js(`${paths.modules.admin.js}/recipes/recipe-tags.js`, `${paths.dest.js}/admin/recipes`)
	.js(`${paths.modules.admin.js}/client/formular.js`, `${paths.dest.js}/admin/client`)
	.js(`${paths.modules.admin.js}/client/jobsStatus.js`, `${paths.dest.js}/admin/client`)
	.js(`${paths.modules.admin.js}/client/tab-balance.js`, `${paths.dest.js}/admin/client`)
	.js(`${paths.modules.admin.js}/client/tab-calculations.js`, `${paths.dest.js}/admin/client`)
	.js(`${paths.modules.admin.js}/client/tab-challenges.js`, `${paths.dest.js}/admin/client`)
	.js(`${paths.modules.admin.js}/client/tab-chargebee-subscriptions.js`, `${paths.dest.js}/admin/client`)

    .sass(`${paths.modules.admin.sass}/switch.scss`,
				`${paths.dest.css}/admin`).sass(`${paths.modules.admin.sass}/progressBar.scss`,
    `${paths.dest.css}/admin`).sass(`${paths.modules.ingredient.sass}/ingredientCategories.scss`,
				`${paths.dest.css}/admin`).sass(`${paths.modules.admin.sass}/dataTablesPagination.scss`,
				`${paths.dest.css}/admin`).sass(`${paths.modules.admin.sass}/customImage.scss`,
				`${paths.dest.css}/admin`).sass(`${paths.modules.admin.sass}/customAdmin.scss`,
				`${paths.dest.css}/admin`);

/*
 |--------------------------------------------------------------------------
 | Vendor assets - Client side
 |--------------------------------------------------------------------------
 | Names used in here describe output name, not the source one.
 |
 | chart.js - used in diary/statistics.blade.php.
 | modernizr.custom.80028.js - used in formular/create.blade.php, form/create.blade.php
 | Error page assets:
 | ionicons.min.css
 | jquery.classycountdown.min.css
 | jquery.classycountdown.min.js
 |
 | bootstrap fonts
 | font awesome
 */
mix.copy(`${paths.folders.node}/chart.js/dist/Chart.min.js`,
		`${paths.dest.vendor}/chart-js`).
		copy(`${paths.src.js}/modernizr.custom.80028.js`,
				`${paths.dest.vendor}/modernizr`).
		copy(`${paths.folders.node}/ionicons/css/ionicons.min.css`,
				`${paths.dest.vendor}/ionicons`).
		copy(
				`${paths.folders.node}/jquery.classycountdown/css/jquery.classycountdown.min.css`,
				`${paths.dest.vendor}/classycountdown`).
		copy(`${paths.folders.node}/jquery-countdown/dist/jquery.countdown.min.js`,
				`${paths.dest.vendor}/jquery-countdown`).
		copy(`${paths.folders.node}/jquery/dist/jquery.min.js`,
				`${paths.dest.vendor}/jquery`).
		version().
		copy(`${paths.folders.node}/ionicons/fonts/*`,
				`${paths.dest.vendor}/fonts`).
		copy(`${paths.folders.node}/bootstrap-sass/assets/fonts/bootstrap`,
				`${paths.dest.vendor}/bootstrap/fonts`).
		copy(`${paths.folders.node}/font-awesome/fonts/*.*`,
				`${paths.dest.vendor}/font-awesome`).
		copy(
				[
					`${paths.folders.node}/ion-rangeslider/css/ion.rangeSlider.css`,
					// `${paths.folders.node}/ion-rangeslider/css/ion.rangeSlider.skinSimple.css`, | file is overridden manually
					`${paths.folders.node}/ion-rangeslider/js/ion.rangeSlider.min.js`,
				],
				`${paths.dest.vendor}/ion-rangeslider`,
		).
		sass(`${paths.src.sass}/vendor/ion.rangeSlider.fp.scss`,
				`${paths.dest.vendor}/ion-rangeslider`).
		copy(`${paths.folders.node}/ion-rangeslider/img/sprite-skin-simple.png`,
				`${paths.dest.vendor}/img`) // path according to ion.rangeSlider.skinSimple.css
		.copyDirectory(`${paths.folders.node}/owl.carousel/dist`,
				`${paths.dest.vendor}/owlcarousel`);

/*
 |--------------------------------------------------------------------------
 | Vendor assets - Admin side
 |--------------------------------------------------------------------------
 | Names used in here describe output name, not the source one.
 |
 | ColorBox - 1.6.4. NOTE: Original styles have been overridden, this why they are not copied from source
 | sweetalert.min.js - used in admin/client/tableEntity.blade.php (currently not used),
 */
mix.copy(`${paths.folders.node}/jquery-colorbox/jquery.colorbox-min.js`,
		`${paths.dest.vendor}/colorbox`).
		copy(`${paths.folders.node}/jquery-colorbox/example1/images/*`,
				`${paths.dest.vendor}/colorbox/images`).sass(`${paths.modules.admin.sass}/vendors/colorbox/colorbox.scss`,
				`${paths.dest.vendor}/colorbox`) //| not used
		.options({processCssUrls: false}).sass(`${paths.modules.admin.sass}/vendors/colorbox/colorboxTheme.scss`,
				`${paths.dest.vendor}/colorbox`).
		options({processCssUrls: false}).
		copy(`${paths.src.js}/sweetalert.min.js`,
				`${paths.dest.vendor}/sweetalert`);

// Sleeping owl css and js files didn't get published, thus I added the line below
mix.copy('vendor/laravelrus/sleepingowl/public', 'public/packages/sleepingowl/')
