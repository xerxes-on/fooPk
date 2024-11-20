<template>
  <form :action="saveActionRoute" method="POST" v-on:submit="submit" enctype="multipart/form-data">
    <input :value="csrf" name="_token" type="hidden">
    <flexmeal-create-label :ingestions="Object.keys(ingestionGoals)"></flexmeal-create-label>

    <flexmeal-create-table v-if="shouldRender"
                           :key="shouldRender"
                           :selected-ingestion="selectedIngestion"
                           :ingestion-data="selectedIngestionData"
    ></flexmeal-create-table>
  </form>
</template>

<script>
import {bus} from '../../../../../../resources/assets/js/app';

export default {
  name: 'flex-meal-creation',
  props: {
    ingestionGoals: {
      type: Object,
      required: true,
    },
  },
  components: {
    flexmealCreateLabel: () => import('./create/create-label.vue'),
    flexmealCreateTable: () => import('./create/create-table.vue'),
  },

  data() {
    return {
      saveActionRoute: window.foodPunk.routes.flexmealSave,
      csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      ingredients: [],
      selectedIngestionData: {},
      selectedIngestion: '',
      shouldRender: false,
    };
  },

  mounted() {
    bus.$on('flexMealIngestionSelected', this.getData);
  },

  methods: {
    forceRerender() {
      this.shouldRender = true;
      bus.$emit('flexMealIngredientsLoaded');
    },

    submit() {
      return confirm(this.$i18n.t('common.are_you_sure'));
    },

    getData(payload) {
      this.selectedIngestionData = this.ingestionGoals;
      this.selectedIngestion = payload;
      this.forceRerender();
    },

    handleResultSuccess(response) {
      if (response.data.length > 0 && response.status === 200) {
        this.ingredients = response.data;
      }
      this.forceRerender();

      document.getElementById('loading').style.display = 'none';
    },

    handleResultError(response) {
      console.warn(response);
      document.getElementById('loading').style.display = 'none';
    },
  },
};
</script>
