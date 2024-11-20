<template>
  <div>
    <div class="radio-tabs-new">
      <button v-for="(value,key) in ingestionGoals"
              :class="[{ 'radio-tabs-new-button-active': key === activeIngestionTab }, 'radio-tabs-new-button']"
              type="button"
              @click="changeTab(key)">
        {{ $t('common.' + key) }}
      </button>
    </div>

    <div v-for="(value,key) in ingestionGoals"
         v-show="key === activeIngestionTab"
         :key="`tab_${key}`"
         :class="['tab-item',`tab-item-${key}`]">

      <template v-if="flexMeals[key] && flexMeals[key].data">
        <div
            v-for="flexmeal in flexMeals[key].data"
            :id="`list_${flexmeal.id}_row`"
            :key="flexmeal.id"
            v-on="removeItem(flexmeal.id)"
            :class="`wrapper_${flexmeal.mealtime} table-wrapper list`">
          <flexmeal-label :flexmeal="flexmeal"
                          :ingestions="Object.keys(ingestionGoals)"
                          :nutrients-goals="ingestionGoals"></flexmeal-label>
          <flexmeal-table :flexmeal="flexmeal"
                          :nutrients-goals="ingestionGoals[activeIngestionTab]">
          </flexmeal-table>
        </div>

        <div class="row">
          <div class="pull-right">
            <v-pagination :data="flexMeals[key]" :limit="1" @pagination-change-page="getResults"></v-pagination>
          </div>
        </div>
      </template>

      <h2 v-else class="font-07 heading-text">{{ $t('common.saved_text') }}</h2>
    </div>
  </div>
</template>

<script>
import {bus} from '../../../../../../resources/assets/js/app';

export default {
  name: 'flex-meal-archive',
  props: {
    ingestionGoals: {
      type: Object,
      required: true,
    },
  },
  components: {
    flexmealTable: () => import('./archive/table.vue'),
    flexmealLabel: () => import('./archive/label.vue'),
    vPagination: () => import('laravel-vue-pagination'),
  },
  created() {
    this.getResults();
  },
  data() {
    return {
      flexMeals: this.getDefaultFlexmealStructure(),
      activeIngestionTab: 'breakfast',
      getFlexmealsRoute: window.foodPunk.routes.getFlexmealsForMealtime,
      fetchedMeals: [],
    };
  },
  methods: {
    getDefaultFlexmealStructure() {
      const flexMeals = {};
      for (const key in this.ingestionGoals) {
        flexMeals[key] = {};
      }
      return flexMeals;
    },

    changeTab(ingestionKey) {
      this.activeIngestionTab = ingestionKey;
      if (this.fetchedMeals.includes(ingestionKey)) {
        return;
      }
      this.getResults();
    },

    removeItem(id) {
      bus.$on(`deleteFlexmeal${id}`, function () {
        document.getElementById(`list_${id}_row`).remove();
      });
    },

    getResults(page = 1) {
      document.getElementById('loading').style.display = 'block';
      axios.get(this.getFlexmealsRoute, {
        params: {
          mealtime: this.activeIngestionTab,
          page: page,
        },
      })
          .then(response => this.handleResultSuccess(response))
          .catch(response => this.handleResultError(response));
    },

    handleResultSuccess(response) {
      if (response.data.data.length > 0 && response.status === 200) {
        this.flexMeals[this.activeIngestionTab] = response.data;
      } else {
        this.flexMeals[this.activeIngestionTab] = {};
      }
      this.fetchedMeals.push(this.activeIngestionTab);
      document.getElementById('loading').style.display = 'none';
    },

    handleResultError(response) {
      console.warn(response.data);
      document.getElementById('loading').style.display = 'none';
    },
  },
};
</script>
