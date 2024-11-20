/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

// vue-i18n && laravel-vue-i18n-generator
import VueInternationalization from 'vue-i18n';
import Locale from './vue-i18n-locales.generated';

Vue.use(VueInternationalization);

const lang = document.documentElement.lang.substr(0, 2);
// or however you determine your current app locale

const i18n = new VueInternationalization({
    locale: lang,
    messages: Locale,
});

export const bus = new Vue();

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 * TODO: recommended to separate and load components when needed...but due to time constrains and complexity of the project, its better wait until redesign
 */

Vue.component('favorite', require('./components/Favorite.vue').default);
Vue.component('purchases-list', require('./components/PurchasesList.vue').default);
Vue.component('cooked', require('./components/Cooked.vue').default);
Vue.component('actions-menu', require('./components/ActionsMenu.vue').default);
Vue.component('actions', require('./components/Actions.vue').default);
Vue.component('vue-stars', require('./components/VueStars.vue').default);
Vue.component('v-select', require('vue-select').default);
Vue.component('v-pagination', require('laravel-vue-pagination').default);
Vue.component('apply-recipe', require('./components/ApplyRecipe2date.vue').default);
Vue.component('subscriptions-list', require('./components/SubscriptionsList.vue').default);
Vue.component('avatar-uploader', require('./components/AvatarUploader.vue').default);
Vue.component('recipe-diets', require('./components/RecipeDiets.vue').default);
Vue.component('recipe-ingestions', require('./components/RecipeIngestions.vue').default);
Vue.component('flex-meal-creation', require('../../../modules/FlexMeal/resources/assets/js/components/FlexMealCreation.vue').default);
Vue.component('flex-meal-archive', require('../../../modules/FlexMeal/resources/assets/js/components/FlexMealArchive.vue').default);
Vue.component('flex-meal-edit-modal', require('../../../modules/FlexMeal/resources/assets/js/components/edit/edit-modal.vue').default);
Vue.component('questionnaire', require('./components/questionnaire/Questionnaire.vue').default);
Vue.component('cooking-mode', require('./components/CookingMode.vue').default);

//filters
Vue.filter('capitalize', (string) => {
    if (!string) return '';
    string = string.toString();
    return string.charAt(0).toUpperCase() + string.slice(1);
});

new Vue({
    el: '#app',
    i18n,
});

window.addEventListener('load', function () {
    $('[data-toggle="popover"]').popover();
});
/* If browser back button was used, reload page, WEB-685 issue fixer */
window.addEventListener("pageshow", function (event) {
    let historyTraversal = event.persisted || (typeof window.performance != "undefined" && window.performance.getEntriesByType("navigation")[0].type == 'back_forward');
    if (historyTraversal) {
        // Handle page restore.
        window.location.reload();
    }
});