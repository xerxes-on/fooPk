<template>
  <modal v-if="showFlexModal" @close="showFlexModal = false">
    <div slot="header">
      <div class="modal-title">{{ $t('common.select_new_flexmeal') }}</div>
    </div>

    <div slot="body">
      <div v-if="flexMeals.data.length > 0" class="select-recipe-list">
        <div v-for="(item, index) in flexMeals.data"
             class="select-recipe-list_item"
             v-bind:class="{ active: isActive === index }">
          <label :for="`${index}_${item.id}`" class="select-recipe-list_item_label">
            <input :id="`${index}_${item.id}`"
                   v-model="flexMealChange"
                   :value="item.id"
                   class="sr-only"
                   name="checkedFlexmeal"
                   type="radio"
                   v-on:change="setActive(index)"
            />
          </label>

          <div class="search-recipes_list_item_img">
            <img :alt="item.name" :src="item.image" height="150" width="150"/>
          </div>

          <div class="select-recipe-list_item_info">
            <div class="search-recipes_list_item_info_wrap">
              <span :title="item.name" class="select-recipe-list_item_info_title">{{ item.name }}</span>
            </div>
            <div class="search-recipes_list_item_info_wrap">
              <recipe-ingestions v-if="item.ingestion" :data="[item.ingestion]"/>
            </div>
            <div class="search-recipes_list_item_info_wrap my-6px">
              <ul v-if="item.calculated_nutrients" class="inline-nutrients">
                <li class="inline-nutrients-item">
                  <span class="inline-nutrients-title">{{ $t('common.carb') }} (g):</span>
                  <span class="inline-nutrients-value">{{ item.calculated_nutrients.carbohydrates }}</span>
                </li>
                <li class="inline-nutrients-item">
                  <span class="inline-nutrients-title">{{ $t('common.protein') }} (g):</span>
                  <span class="inline-nutrients-value">{{ item.calculated_nutrients.proteins }}</span>
                </li>
                <li class="inline-nutrients-item">
                  <span class="inline-nutrients-title">{{ $t('common.fettd') }} (g):</span>
                  <span class="inline-nutrients-value">{{ item.calculated_nutrients.fats }}</span>
                </li>
                <li class="inline-nutrients-item">
                  <span class="inline-nutrients-title">{{ $t('common.calories_word') }}:</span>
                  <span class="inline-nutrients-value">{{ item.calculated_nutrients.calories }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div v-else>{{ $t('common.flexmeal_empty') }}</div>
    </div>

    <div slot="footer">
      <div class="row">
        <div class="col-md-7" style="text-align: left">
          <v-pagination :data="flexMeals" :limit="1" @pagination-change-page="getFlexmeals"></v-pagination>
        </div>

        <div class="col-md-5">
          <div>
            <button class="btn btn-base btn-pink" type="button" @click="showFlexModal = false">
              {{ $t('common.cancel') }}
            </button>
            <button class="btn btn-base btn-tiffany" data-dismiss="modal" type="button" @click="maybeSubmitFlexmeal()">
              {{ $t('common.replace') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </modal>
</template>

<script>
import {bus} from "../../app";

export default {
  name: "flexmeal-replacement-modal",
  components: {
    Modal: () => import( '../Modal'),
    vPagination: () => import( 'laravel-vue-pagination'),
  },
  props: {
    recipe: {
      type: Number,
      required: true,
    },
    mealTime: {
      type: String,
      required: true,
    },
    date: {
      type: String,
      required: true,
    },
    recipeType: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      showFlexModal: false,
      isActive: false,
      flexMealChange: '',
      flexMeals: [],
    };
  },
  created() {
    bus.$on(`open_flexmeal_${this.recipe}_${this.mealTime}_${this.date}_modal`, this.setup);
  },
  methods: {
    setup(payload) {
      $('#loading').show();
      axios.get(window.foodPunk.routes.flexMeals, {
        params: {
          mealtime: this.mealTime,
          separateBreakfast: 1,
          page: 1,
        },
      }).then((response) => {
        this.showFlexModal = true;
        this.flexMeals = response.data;
        $('#loading').hide();
      }).catch(response => {
        $('#loading').hide();
      });
    },

    getFlexmeals(page = 1) {
      axios.get(window.foodPunk.routes.flexMeals, {
        params: {
          mealtime: this.mealTime,
          separateBreakfast: 1,
          page: page,
        },
      }).then((response) => {
        this.flexMeals = response.data;
      }).catch(response => {
        console.error(response);
      });
    },

    setActive(el) {
      this.isActive = el;
    },

    maybeSubmitFlexmeal() {
      if (this.flexMealChange === '') {
        return alert(this.$i18n.t('common.select_replacement'));
      }
      $('#loading').show();
      const self = this;

      // Perform deviant check
      axios.post(window.foodPunk.routes.checkFlexmeal, {
        flexmeal_id: self.flexMealChange,
        desired_mealtime: self.mealTime,
      }).then(response => {
        // If check passed, submit flexmeal and reload page
        if (response.data.success) {
          self._submitFlexmeal();
          return;
        }
        $('#loading').hide();
        if (!response.data.success) {
          self._showDeviationWarning(response.data.message)
        }
      }).catch(response => {
        console.error(response.data);
        $('#loading').hide();
      });
    },

    _showDeviationWarning(message) {
      const self = this;
      this.showFlexModal = false;
      Swal.fire({
        title: self.$i18n.t('common.flexmeal_deviation.error.title'),
        text: message,
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: self.$i18n.t('common.flexmeal_deviation.button.insert'),
        cancelButtonText: self.$i18n.t('common.flexmeal_deviation.button.dont_insert'),
      }).then(function (result) {
        // If user wants to insert deviant flexmeal, submit it
        if (result.value && result.isConfirmed) {
          self._submitFlexmeal();
          return;
        }
        // Ask user if he wants to adjust the flexmeal
        if (result.isDismissed && result.dismiss === 'cancel') {
          self._showFlexmealEditNotification();
        }
      });
    },
    _submitFlexmeal() {
      axios.post(window.foodPunk.routes.replaceWithFlexmeal, {
        date: this.date,
        recipe: this.recipe,
        flexmeal_id: this.flexMealChange,
        mealtime: this.mealTime,
        recipeType: this.recipeType,
      }).then(response => {
        $('#loading').hide();
        if (response.data.errors === null && response.status) {
          location.reload();
        } else {
          console.error(response.data);
        }
      }).catch(response => {
        console.error(response.data);
        $('#loading').hide();
      });
    },
    _showFlexmealEditNotification() {
      const self = this;
      Swal.fire({
        title: this.$i18n.t('common.flexmeal_deviation.adjustment.title'),
        icon: 'question',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: this.$i18n.t('common.flexmeal_deviation.button.adjust'),
        cancelButtonText: this.$i18n.t('common.flexmeal_deviation.button.dont_adjust'),
      }).then(function (result) {
        // If user wants to insert deviant flexmeal, submit it
        if (result.value && result.isConfirmed) {
          bus.$emit('open_flexmeal_edit_modal', self.flexMeals.data.find(o => o.id === self.flexMealChange));
        }
      });
    },
  }
}
</script>
