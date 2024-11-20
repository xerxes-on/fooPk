<template>
  <div class="table-wrapper">
    <div class="flexmeal-name-group">
      <input id="flexlistname"
             :placeholder="$t('common.flexmeal_name')"
             class="flexmeal-name-group-input"
             name="flexmeal"
             maxlength="191"
             type="text">
    </div>

    <div class="flexmeal-extra-data-group flex-centered">
      <imageUploader></imageUploader>
      <div class="flexmeal-extra-data-group-inner-wrap">
        <label for="flexmealNotes" class="flexmeal-extra-data-group-label">{{ $t('common.notes') }}</label>
        <textarea class="flexmeal-extra-data-group-input flexmeal-extra-data-group-textarea" name="notes"
                  id="flexmealNotes"></textarea>
      </div>
    </div>

    <table class="fl-table table table-striped">
      <thead>
      <tr>
        <th class="text-center">{{ $t('common.amounts') }}</th>
        <th class="text-center">{{ $t('common.ingredients') }}</th>
        <th class="text-center">{{ $t('common.protein') }}</th>
        <th class="text-center">{{ $t('common.fat') }}</th>
        <th class="text-center">{{ $t('common.carbohydrates') }}</th>
        <th class="text-center">{{ $t('common.calories_word') }}</th>
      </tr>
      </thead>
      <tbody id="ingredient_tbody">

      <create-ingredient v-for="(item ,n) in ingredientsUsed"
                         :key="item"
                         ref="ingredient"
                         :keyId="n"
                         @destroyComponent="deleteIngredient(n)">
      </create-ingredient>

      <tr class="add-ingredient">
        <td class="add-ingredient-column">
          <button :aria-label="$t('common.add_ingredient')"
                  :title="$t('common.add_ingredient')"
                  class="add-ingredient-button"
                  type="button"
                  @click="addIngredient">
          </button>
        </td>
      </tr>
      </tbody>
      <nutrient-data :total-proteins="totalProteins"
                     :total-fats="totalFats"
                     :total-carbohydrates="totalCarbohydrates"
                     :total-calories="totalCalories"
                     :selected-ingestion="selectedIngestion"
                     :ingestion-data="ingestionData"></nutrient-data>
    </table>

    <div class="button-group">
      <button ref="submitButton"
              :class="{'btn-disabled': cannotBeSubmitted}"
              :disabled="cannotBeSubmitted"
              class="btn btn-pink-full"
              type="submit">
        {{ $t('common.save') }}
      </button>
      <button :class="{'btn-disabled': cannotBeCleared}"
              :disabled="cannotBeCleared"
              class="btn btn-pink-full"
              type="button"
              @click="deleteAllIngredients">
        {{ $t('common.delete_all') }}
      </button>
    </div>
  </div>
</template>

<script>
import {bus} from '../../../../../../../resources/assets/js/app';

export default {
  name: 'create-table',
  components: {
    createIngredient: () => import('./create-ingredient.vue'),
    imageUploader: () => import('../../../../../../../resources/assets/js/components/vue-image-uploader.vue'),
    nutrientData: () => import('../include/nutrition-data-table.vue'),
  },
  props: {
    ingestionData: {
      type: Object,
      required: true,
    },
    selectedIngestion: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      ingredientsUsed: [],
      totalCalories: 0,
      totalCarbohydrates: 0,
      totalFats: 0,
      totalProteins: 0,
      cannotBeSubmitted: true,
      cannotBeCleared: true,
    };
  },
  mounted() {
    bus.$on('ingredientChanged', this.handleCalculations);
  },

  methods: {

    uuid: () => Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),

    handleCalculations: function () {
      //Rest nutrients
      this.totalCalories = 0;
      this.totalCarbohydrates = 0;
      this.totalFats = 0;
      this.totalProteins = 0;

      // Exit if no ingredients
      if (typeof this.$refs.ingredient == 'undefined') {
        return;
      }

      if (this.$refs.ingredient.length === 0) {
        this.cannotBeCleared = true;
        this.cannotBeSubmitted = true;
      }

      // Calculate totals
      for (const ingredientComponent of this.$refs.ingredient) {
        this.totalCalories += parseFloat(ingredientComponent.calculatedCalories);
        this.totalCarbohydrates += parseFloat(ingredientComponent.calculatedCarbohydrates);
        this.totalFats += parseFloat(ingredientComponent.calculatedFats);
        this.totalProteins += parseFloat(ingredientComponent.calculatedProteins);
      }

      this.cannotBeSubmitted = this.totalCalories <= 0;
    },

    addIngredient: function () {
      this.$set(this.ingredientsUsed, this.ingredientsUsed.length, this.uuid());
      this.cannotBeCleared = false;
      // Timing is broken without it
      this.$nextTick(() => {
        this.handleCalculations();
      });
    },

    deleteIngredient: function (key) {
      if (!confirm(this.$i18n.t('common.delete_ingredient'))) {
        return;
      }

      this.ingredientsUsed.splice(key, 1);

      // Timing is broken without it
      this.$nextTick(() => {
        this.handleCalculations();
      });
    },

    deleteAllIngredients: function () {
      if (!confirm(this.$i18n.t('common.deleteall_ingredient'))) {
        return;
      }
      this.ingredientsUsed = [];

      // Timing is broken without it
      this.$nextTick(() => {
        this.handleCalculations();
      });
    },
  },
};
</script>
