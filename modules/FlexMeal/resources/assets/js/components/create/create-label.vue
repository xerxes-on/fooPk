<template>
  <div class="col-xs-12">
    <div v-for="ingestion in ingestions" :data-ingestion="ingestion" class="col-md-4 col-xs-12 js-ingestion-label">
      <label :class="`ingestion-label ingestion-label-${ingestion}`">
        <input :value="ingestion" class="sr-only" name="meal" type="radio"
               v-on:change="setSelectedElement($event, ingestion)">
        <span class="ingestion-label-text">{{ $t(`common.${ingestion}`) }}</span>
      </label>
    </div>
    <div class="clearfix"></div>
  </div>
</template>

<script>
import {bus} from '../../../../../../../resources/assets/js/app';

export default {
  name: 'create-label',
  props: {
    ingestions: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      selectedIngestion: '',
    };
  },
  created() {
    bus.$on('flexMealIngredientsLoaded', this.toggleActiveIngestionField);
  },
  methods: {
    toggleActiveIngestionField: function () {
      let nodes = document.getElementsByClassName('js-ingestion-label');
      for (let node of nodes) {
        if (node.dataset.ingestion === this.selectedIngestion) {
          node.classList.remove('col-md-4');
          node.firstChild.classList.remove(`ingestion-label-${this.selectedIngestion}`);
          node.firstChild.classList.add(`ingestion-label-${this.selectedIngestion}-big`, 'ingestion-label-active');
          continue;
        }
        node.style.display = 'none';
      }
    },

    setSelectedElement: function (event, ingestion) {
      this.selectedIngestion = ingestion;
      bus.$emit('flexMealIngestionSelected', ingestion);
    },
  },
};
</script>
