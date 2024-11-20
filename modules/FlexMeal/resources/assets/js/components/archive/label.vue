<template>
  <div>
    <div class="flexmeal-label">
      <button class="btn btn-pink-full" type="button" v-on:click="toggleIngredientsTable">
        {{ $t('common.show_meal') }}
      </button>
      <div class="flexmeal-label-content">
        <img :src="imageUrl" class="flexmeal-label-content-image" alt="Flexmeal preview">
        <span class="flexmeal-label-content-title">{{ title }}</span>
        <div class="flexmeal-label-content-controls">
          <button aria-label="Rename"
                  class="btn-with-icon flexmeal-label-content-controls-button-rename"
                  title="Rename Meal"
                  type="button"
                  @click="openEditMode()">
          </button>
          <button aria-label="Delete"
                  class="btn-with-icon flexmeal-label-content-controls-button-delete"
                  title="Delete Meal"
                  type="button"
                  @click="deleteMeal()">
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import {bus} from '../../../../../../../resources/assets/js/app';

export default {
  name: 'flexmeal-label',
  components: {
    flexMealEditModal: () => import('../edit/edit-form.vue'),
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
    nutrientsGoals: {
      type: Object,
      default: () => ({}),
      required: false,
    },
  },
  data() {
    return {
      isEdited: false,
      hasIngredients: this.flexmeal.used_ingredients.length > 0,
      title: this.flexmeal.name === null ? this.flexmeal.updated_at : this.flexmeal.name,
      imageUrl: this.flexmeal.image ? this.flexmeal.image : '/images/flexmeal.jpg',
      deleteFlexmealRoute: window.foodPunk.routes.deleteFlexmeal,
    };
  },
  methods: {
    toggleIngredientsTable: function (event) {
      bus.$emit(`toggleTable${this.flexmeal.id}`);
    },

    deleteFlexmeal: function (event) {
      bus.$emit(`deleteFlexmeal${this.flexmeal.id}`);
    },

    openEditMode: function () {
      bus.$emit('open_flexmeal_edit_modal', this.flexmeal);
    },

    submit: function () {
      document.getElementById(`editForm${this.flexmeal.id}`).submit();
    },

    deleteMeal: function () {
      if (!confirm(this.$i18n.t('common.delete_meal'))) {
        return;
      }
      axios.post(this.deleteFlexmealRoute, {
        _token: this.token,
        _method: 'DELETE',
        list_id: this.flexmeal.id,
      }).then(response => {
        this.deleteFlexmeal()
      }).catch(response => {
        console.warn(response);
      });
    },
  },
};
</script>

<style>
.modal-footer .btn + .btn {
  margin: 15px 0;
}
</style>
