<template>
  <modal v-if="isEdited" @close="closeAndDestroy">
    <h3 slot="header" class="modal-title">{{ $t('common.edit') }}</h3>

    <div slot="body">
      <edit-form :flexmeal="flexmeal"
                 :ingestions="ingestions"
                 :should-render="isEdited"
                 :nutrients-goals="ingestionGoals"></edit-form>
    </div>

    <div slot="footer">

      <div class="button-group">
        <button :class="{'btn-disabled': cannotBeCleared}"
                :disabled="cannotBeCleared"
                class="btn btn-pink-full"
                type="button"
                @click="deleteAllIngredients">
          {{ $t('common.delete_all') }}
        </button>
        <button ref="submitButton"
                :class="{'btn-disabled': cannotBeSubmitted}"
                :disabled="cannotBeSubmitted"
                class="btn btn-pink-full"
                type="button"
                @click="submit">
          {{ $t('common.save') }}
        </button>
      </div>
    </div>
  </modal>
</template>

<script>

import {bus} from "../../../../../../../resources/assets/js/app";

export default {
  name: 'flex-meal-edit-modal',
  components: {
    Modal: () => import('../../../../../../../resources/assets/js/components/Modal.vue'),
    editForm: () => import('./edit-form.vue'),
  },
  props: {
    ingestionGoals: {
      type: Object,
      default: () => ({}),
      required: false,
    },
  },

  data() {
    return {
      flexmeal: {},
      isEdited: false,
      hasIngredients: false,
      title: '',
      imageUrl: '/images/flexmeal.jpg',
      cannotBeSubmitted: false,
      cannotBeCleared: false,
      ingestions: [],
    };
  },
  created() {
    bus.$on('open_flexmeal_edit_modal', this.setup);
    bus.$on('close_flexmeal_edit_modal', this.closeAndDestroy);
    bus.$on('flexmealEmpty', this.preventSubmitOrEdit);
    bus.$on('flexmealSubmissionPossibilityChanged', this.preventSubmit);
    bus.$on('ingredientAdded', this.enableClearBtn);
  },
  methods: {
    setup(payload) {
      this.flexmeal = payload;
      this.ingestions = Object.keys(this.ingestionGoals);
      this.hasIngredients = payload.used_ingredients.length > 0;
      this.title = payload.name === null ? payload.updated_at : payload.name;
      if (payload.image) {
        this.imageUrl = payload.image;
      }
      this.isEdited = true;
    },
    preventSubmit(event) {
      this.cannotBeSubmitted = event.cannotBeSubmitted;
      this.cannotBeCleared = event.cannotBeCleared;
    },

    preventSubmitOrEdit() {
      this.cannotBeSubmitted = true;
      this.cannotBeCleared = true;
    },

    enableClearBtn() {
      this.cannotBeCleared = false;
    },

    forceRerender() {
      this.shouldRender = true;
    },

    closeAndDestroy() {
      this.$data.cannotBeSubmitted = false;
      this.$data.cannotBeCleared = false;
      this.isEdited = false;
    },

    deleteAllIngredients: function () {
      if (!confirm(this.$i18n.t('common.deleteall_ingredient'))) {
        return;
      }
      bus.$emit(`deleteAllIngredients${this.flexmeal.id}`);
    },

    submit() {
      bus.$emit('submit_flexmeal_form');
    },
  },
};
</script>
