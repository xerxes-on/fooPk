<template>
  <tfoot>
  <tr class="ingredient-total">
    <td class="borderless ingredient-total-text text-white">{{ $t('common.total') }}</td>
    <td></td>
    <td class="ingredient-total-proteins">{{ totalProteins.toFixed(1) }}</td>
    <td class="ingredient-total-fats">{{ totalFats.toFixed(1) }}</td>
    <td class="ingredient-total-carbohydrates">{{ totalCarbohydrates.toFixed(1) }}</td>
    <td class="ingredient-total-calories" colspan="2">{{ totalCalories.toFixed(1) }}</td>
  </tr>
  <tr class="personal-goals">
    <td class="borderless personal-goals-text text-white">{{ $t('common.goal') }}</td>
    <td></td>
    <td class="personal-goals-icon personal-goals-icon-protein">
      <span>{{ ingestion.EW }}</span>
    </td>
    <td class="personal-goals-icon personal-goals-icon-fat">
      <span>{{ ingestion.F }}</span>
    </td>
    <td class="personal-goals-icon personal-goals-icon-carbs">
      <span>{{ ingestion.KH }}</span>
    </td>
    <td class="personal-goals-icon personal-goals-icon-calories" colspan="2">
      <span>{{ ingestion.Kcal }}</span>
    </td>
  </tr>
  </tfoot>
</template>

<script>
import {bus} from "../../../../../../../resources/assets/js/app";

export default {
  name: "nutrition-data-table",
  props: ['totalProteins', 'selectedIngestion', 'totalFats', 'totalCarbohydrates', 'totalCalories', 'ingestionData'],
  data() {
    return {
      ingestion: this.ingestionData[this.selectedIngestion],
    }
  },
  created() {
    bus.$on('changeIngestion', this.changeIngestionGoals);
  },
  methods: {
    changeIngestionGoals(selectedIngestion) {
      this.ingestion = this.ingestionData[selectedIngestion];
    }
  }
}
</script>
