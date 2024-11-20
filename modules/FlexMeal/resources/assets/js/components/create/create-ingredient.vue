<template>
  <tr class="ingredient">
    <td class="row-text ingredient-amount">
      <label>
        <input :name="`ingredients[${keyId}][amount]`"
               :value="amount"
               class="form-control ingredient-amount-input"
               min="0"
               type="number"
               @input="updateValues($event)"
               @onkeypress="preventLetters"
        />
        <span class="row-text units">{{ unit }}</span>
      </label>
    </td>
    <td class="row-text w-50">
      <select2 :ref="creteSelect2"
               :name="`ingredients[${keyId}][ingredient_id]`"
               required="required"
               :data-ajax--url="getIngredientsRoute"
               data-ajax--cache="true"
               data-ajax--delay="400"
               :data-placeholder="$t('common.search')"
               data-minimum-input-length="3"
      >
      </select2>
    </td>
    <td class="ingredient-proteins proteins">{{ calculatedProteins }}</td>
    <td class="ingredient-fats fats">{{ calculatedFats }}</td>
    <td class="ingredient-carbohydrates carbohydrates">{{ calculatedCarbohydrates }}</td>
    <td class="ingredient-calories calories">{{ calculatedCalories }}</td>
    <td class="row-text">
      <button :aria-label="$t('common.delete_ingredient')"
              :title="$t('common.delete_ingredient')"
              class="btn-with-icon btn-with-icon-trash"
              type="button"
              @click="$emit('destroyComponent')">
      </button>
    </td>
  </tr>
</template>

<script>
import {bus} from '../../../../../../../resources/assets/js/app';

export default {
  components: {
    select2: () => import('../../../../../../../resources/assets/js/components/vue-ingredients-select2.vue'),
  },
  name: 'create-ingredient',

  props: {

    keyId: {
      Type: Number,
      required: true,
    },
  },

  data() {
    return {
      proteins: 0,
      fats: 0,
      carbohydrates: 0,
      amount: 0,
      unitDefaultAmount: 1,
      unit: '-',
      getIngredientsRoute: window.foodPunk.routes.getIngredients,
    };
  },

  computed: {
    calculatedProteins: function () {
      return this.calculateValue(this.proteins, this.amount, this.unitDefaultAmount);
    },
    calculatedFats: function () {
      return this.calculateValue(this.fats, this.amount, this.unitDefaultAmount);
    },
    calculatedCarbohydrates: function () {
      return this.calculateValue(this.carbohydrates, this.amount, this.unitDefaultAmount);
    },
    calculatedCalories: function () {
      let value = this.calculatedFats * 9 + this.calculatedCarbohydrates * 4 + this.calculatedProteins * 4;
      return isNaN(value) ? 0 : value.toFixed(2);
    },
  },

  methods: {
    creteSelect2: function (el) {
      if (el === null) {
        return;
      }
      const vm = this;
      el.$on('ingredientSelected', function (val) {
        vm.proteins = val.proteins;
        vm.fats = val.fats;
        vm.carbohydrates = val.carbohydrates;
        vm.unit = val.unit;
        vm.unitDefaultAmount = val.unitDefaultAmount;
        vm.amount = val.unitDefaultAmount;
        bus.$emit('ingredientChanged');
      });
    },

    updateValues: function (event, el) {
      this.amount = event.srcElement.value;
      bus.$emit('ingredientChanged');
    },

    calculateValue: (value, amount, unitConst, decimals) => {
      value = value || 0;
      amount = amount || 0;
      unitConst = unitConst || 0;
      if (typeof decimals == 'undefined') decimals = 2;
      let calculatedValue = parseFloat(value) / parseFloat(unitConst) * parseInt(amount);
      return isNaN(calculatedValue) ? 0 : parseFloat(calculatedValue.toFixed(decimals));
    },

    preventLetters: function (event) {
      // 8 backspace
      // 48 Digit0
      // 48	0	Digit0
      // 49	1	Digit1
      // 50	2	Digit2
      // 51	3	Digit3
      // 52	4	Digit4
      // 53	5	Digit5
      // 54	6	Digit6
      // 55	7	Digit7
      // 56	8	Digit8
      // 57	9	Digit9
      return (event.charCode === 8 || event.charCode === 0) ? null : event.charCode >= 48 && event.charCode <= 57;
    },
  },
};
</script>
