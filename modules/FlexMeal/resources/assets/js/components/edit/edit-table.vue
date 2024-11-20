<template>
  <div class="table-wrapper">
    <div class="flexmeal-extra-data-group flex-centered">
      <div class="flexmeal-extra-data-group-inner-wrap">
        <label :for="`mealtimeOf${flexmeal.id}`" class="flexmeal-extra-data-group-label">{{
            $t('common.ingestion')
          }}</label>
        <select class="flexmeal-extra-data-group-input"
                v-model="mealtime"
                name="meal"
                :id="`mealtimeOf${flexmeal.id}`"
                @change="changeIngestion">
          <option v-for="mealtime in ingestions" :value="mealtime" :selected="mealtime === flexmeal.mealtime">
            {{ mealtime | capitalize }}
          </option>
        </select>
      </div>
      <div class="flexmeal-extra-data-group-inner-wrap">
        <label :for="`flexlistname${flexmeal.id}`" class="flexmeal-extra-data-group-label">{{
            $t('common.name')
          }}</label>
        <input :id="`flexlistname${flexmeal.id}`"
               :placeholder="$t('common.flexmeal_name')"
               class="flexmeal-extra-data-group-input"
               name="flexmeal"
               maxlength="191"
               :value="flexmeal.name"
               type="text">
      </div>
      <imageUploader :preview-image="flexmeal.image"></imageUploader>
      <div class="flexmeal-extra-data-group-inner-wrap">
        <label for="flexmealNotes" class="flexmeal-extra-data-group-label">{{ $t('common.notes') }}</label>
        <textarea class="flexmeal-extra-data-group-textarea flexmeal-extra-data-group-input"
                  name="notes"
                  id="flexmealNotes">{{flexmeal.notes}}</textarea>
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
      <edit-ingredient v-for="(item ,n) in ingredientsUsed"
                       :key="item.id"
                       ref="ingredient"
                       :used-ingredient="item"
                       :keyId="n"
                       @destroyComponent="deleteIngredient(n)">
      </edit-ingredient>
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
                     :selected-ingestion="flexmeal.mealtime"
                     :ingestion-data="ingestionData"></nutrient-data>
    </table>
  </div>
</template>

<script>
import {bus} from '../../../../../../../resources/assets/js/app';

export default {
  name: 'edit-table',
  components: {
    editIngredient: () => import('./edit-ingredient.vue'),
    imageUploader: () => import('../../../../../../../resources/assets/js/components/vue-image-uploader.vue'),
    nutrientData: () => import('../include/nutrition-data-table.vue'),
  },
  props: {
    flexmeal: {
      type: Object,
      required: true,
    },
    ingestions: {
      type: Array,
      default: () => [],
      required: true,
    },
    ingestionData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      ingredientsUsed: this.flexmeal.used_ingredients.length > 0 ? this.flexmeal.used_ingredients : [],
      totalCalories: 0,
      totalCarbohydrates: 0,
      totalFats: 0,
      totalProteins: 0,
      mealtime: this.flexmeal.mealtime,
    };
  },
  created() {
    bus.$on('ingredientChanged', this.handleCalculations);
    bus.$on(`deleteAllIngredients${this.flexmeal.id}`, this.deleteAllIngredients);
  },

  methods: {

    uuid: () => Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),

    handleCalculations: function () {
      //Rest nutrients
      this.totalCalories = 0;
      this.totalCarbohydrates = 0;
      this.totalFats = 0;
      this.totalProteins = 0;

      if (this.$refs.ingredient.length === 0) {
        bus.$emit('flexmealEmpty');
      }

      // Calculate totals
      for (const ingredientComponent of this.$refs.ingredient) {
        this.totalCalories += parseFloat(ingredientComponent.calculatedCalories);
        this.totalCarbohydrates += parseFloat(ingredientComponent.calculatedCarbohydrates);
        this.totalFats += parseFloat(ingredientComponent.calculatedFats);
        this.totalProteins += parseFloat(ingredientComponent.calculatedProteins);
      }

      bus.$emit('flexmealSubmissionPossibilityChanged', {
        cannotBeSubmitted: this.totalCalories <= 0,
        cannotBeCleared: this.$refs.ingredient.length <= 0
      });
    },

    addIngredient: function () {
      this.$set(this.ingredientsUsed, this.ingredientsUsed.length, this.uuid());
      bus.$emit('ingredientAdded')
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

    changeIngestion: function () {
      bus.$emit('changeIngestion', this.mealtime);
    },

    deleteAllIngredients: function () {
      this.ingredientsUsed = [];

      // Timing is broken without it
      this.$nextTick(() => {
        this.handleCalculations();
      });
    },
  },
};
</script>
