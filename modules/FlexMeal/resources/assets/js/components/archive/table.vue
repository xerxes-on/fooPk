<template>
  <div v-show="isVisible">
    <!--  we can add table responsive wrapper from bootsrap -->
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
      <tr v-if="flexmeal.used_ingredients.length === 0">
        <td class="borderless"></td>
        <td colspan="6" class="empty-ingredient">{{ $t('common.no_ingredients') }}</td>
      </tr>
      <tr v-else v-for="usedIngredient in flexmeal.used_ingredients" class="ingredient">
        <template v-if="usedIngredient.ingredient">
          <td class="ingredient-amount border-x">
            {{ usedIngredient.amount }} {{ usedIngredient.ingredient.unit.short_name }}
          </td>
          <td class="ingredient-name">
            {{ getTranslatedName(usedIngredient.ingredient.translations) }}
          </td>
          <td class="ingredient-proteins">
            {{
              calculateValue(usedIngredient.ingredient.proteins, usedIngredient.amount, usedIngredient.ingredient.unit.default_amount)
            }}
          </td>
          <td class="ingredient-fats">
            {{
              calculateValue(usedIngredient.ingredient.fats, usedIngredient.amount, usedIngredient.ingredient.unit.default_amount)
            }}
          </td>
          <td class="ingredient-carbohydrates">
            {{
              calculateValue(usedIngredient.ingredient.carbohydrates, usedIngredient.amount,
                  usedIngredient.ingredient.unit.default_amount)
            }}
          </td>
          <td class="ingredient-calories">
            {{
              calculateValue(usedIngredient.ingredient.calories, usedIngredient.amount, usedIngredient.ingredient.unit.default_amount)
            }}
          </td>
        </template>
      </tr>
      </tbody>

      <tfoot>
      <tr class="ingredient-total">
        <td class="borderless ingredient-total-text text-white">{{ $t('common.total') }}</td>
        <td></td>
        <td class="ingredient-total-proteins">{{ flexmeal.calculated_nutrients.proteins }}</td>
        <td class="ingredient-total-fats">{{ flexmeal.calculated_nutrients.fats }}</td>
        <td class="ingredient-total-carbohydrates">{{ flexmeal.calculated_nutrients.carbohydrates }}</td>
        <td class="ingredient-total-calories">{{ flexmeal.calculated_nutrients.calories }}</td>
      </tr>

      <tr class="personal-goals">
        <td class="borderless personal-goals-text text-white">{{ $t('common.goal') }}</td>
        <td></td>
        <td class="personal-goals-icon personal-goals-icon-protein">
          <span>{{ nutrientsGoals.EW }}</span>
        </td>
        <td class="personal-goals-icon personal-goals-icon-fat">
          <span>{{ nutrientsGoals.F }}</span>
        </td>
        <td class="personal-goals-icon personal-goals-icon-carbs">
          <span>{{ nutrientsGoals.KH }}</span>
        </td>
        <td class="personal-goals-icon personal-goals-icon-calories">
          <span>{{ nutrientsGoals.Kcal }}</span>
        </td>
      </tr>
      </tfoot>
    </table>

    <div v-if="flexmeal.notes" class="flexmeal-notes-group">
      <h3 class="flexmeal-notes-group-title">{{ $t('common.notes') }}:</h3>
      <p class="flexmeal-notes-group-content">{{ flexmeal.notes }}</p>
    </div>
  </div>
</template>

<script>
import {bus} from '../../../../../../../resources/assets/js/app';

export default {
  name: 'flex-meal-table',
  props: {
    flexmeal: {
      type: Object,
      required: true,
    },
    nutrientsGoals: {
      type: Object,
      required: true,
      default: {},
    },
  },

  data() {
    return {
      isVisible: false,
    };
  },

  mounted() {
    bus.$on(`toggleTable${this.flexmeal.id}`, payload => {
      this.collapseMenu(payload);
    });
  },

  methods: {
    calculateValue: (value, amount, unitConst, decimals) => {
      value = value || 0;
      amount = amount || 0;
      unitConst = unitConst || 0;
      if (typeof decimals == 'undefined') decimals = 2;
      let calculatedValue = parseFloat(value) / parseFloat(unitConst) * parseFloat(amount);
      return isNaN(calculatedValue) ? 0 : parseFloat(calculatedValue.toFixed(decimals));
    },

    collapseMenu() {
      this.isVisible = !this.isVisible;
    },
    getTranslatedName(translations) {
      let language = document.documentElement.lang;
      let content = null;
      for (const key in translations) {
        if (translations[key].locale === language) {
          content = translations[key].name;
        }
      }

      return content === null ? content : content.charAt(0).toUpperCase() + content.slice(1);
    },
  },
};
</script>
