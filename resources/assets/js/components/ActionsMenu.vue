<template>
  <div class="wrapper-actions-menu">
    <div class="modal-recipe-replace">
      <button aria-expanded="true"
              aria-haspopup="true"
              aria-label="Dropdown toggler"
              class="btn-clear dropdown-toggle"
              data-toggle="dropdown"
              type="button">
        <span aria-hidden="true" class="fa fa-pencil fa-lg"></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-right">
        <li>
          <button class="modal-button" type="button" @click.prevent="openRecipeModal()">
            {{ $t('common.replace_recipe') }}
          </button>
        </li>
        <li>
          <button class="modal-button" type="button" @click.prevent="openFlexModal()">
            {{ $t('common.replace_by_flexmeal') }}
          </button>
        </li>
        <li>
          <button class="modal-button" type="button" @click.prevent="updateEatOut()">
            {{ $t('common.skip_meal') }}
          </button>
        </li>
      </ul>
    </div>

    <recipe-replacement-modal :recipe="recipe"
                              :date="date"
                              :meal-time="mealTime"
                              :recipe-type="recipeType"
                              :seasons="seasons"></recipe-replacement-modal>
    <flexmeal-replacement-modal :recipe="recipe"
                                :date="date"
                                :meal-time="mealTime"
                                :recipe-type="recipeType"></flexmeal-replacement-modal>
  </div>
</template>

<script>
import {bus} from '../app';

export default {
  props: ['recipe', 'mealTime', 'date', 'seasons', 'recipeType'],

  components: {
    recipeReplacementModal: () => import( './recipe-replacement/recipe-replacement-modal.vue'),
    flexmealReplacementModal: () => import( './recipe-replacement/flexmeal-replacement-modal.vue'),
  },

  data() {
    return {};
  },

  methods: {
    openRecipeModal() {
      bus.$emit(`open_recipe_${this.recipe}_${this.mealTime}_${this.date}_modal`);
    },

    openFlexModal() {
      bus.$emit(`open_flexmeal_${this.recipe}_${this.mealTime}_${this.date}_modal`);
    },

    updateEatOut: function () {
      this.$emit('inputData', this.isEatOut);
    },
  },
};
</script>
