<template>
  <form :action="updateActionRoute"
        method="POST"
        v-on:submit="submit"
        enctype="multipart/form-data"
        :id="`editForm${flexmeal.id}`">
    <input :value="csrf" name="_token" type="hidden">
    <input value="PATCH" name="_method" type="hidden">
    <input :value="flexmeal.id" name="id" type="hidden">
    <flexmeal-edit-table v-if="shouldRender"
                         :key="shouldRender"
                         :flexmeal="flexmeal"
                         :ingestions="ingestions"
                         :ingestion-data="nutrientsGoals"
    ></flexmeal-edit-table>
  </form>
</template>

<script>

import {bus} from "../../../../../../../resources/assets/js/app";

export default {
  name: 'flex-meal-editing',
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
    shouldRender: {
      type: Boolean,
      default: false,
    },
    nutrientsGoals: {
      type: Object,
      default: () => ({}),
      required: false,
    },
  },
  components: {
    flexmealEditTable: () => import('./edit-table.vue'),
  },
  created() {
    bus.$on('submit_flexmeal_form', this.submit);
  },
  data() {
    return {
      updateActionRoute: window.foodPunk.routes.updateFlexmeal,
      csrf: document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    };
  },

  methods: {
    forceRerender() {
      this.shouldRender = true;
    },

    submit() {
      if (!confirm(this.$i18n.t('common.are_you_sure'))) {
        return;
      }

      // Check if form is valid, this was done to show defaults browser validation messages
      if (!this.$el.reportValidity()) {
        return;
      }

      const params = new FormData(this.$el);
      axios.post(window.foodPunk.routes.updateFlexmeal, params)
          .then(response => this._handleInitialSubmitSuccess(response.data, params))
          .catch(response => this._handleSubmitFailure());
    },

    _handleInitialSubmitSuccess(response, formData) {
      bus.$emit('close_flexmeal_edit_modal');
      if (response.success) {
        this._showInfoAndReload(response.message);
        return;
      }

      // Confirmation required
      if (!response.success && response.data.code === 'flexmeal_confirmation_require') {
        this._showRequestConfirmationAlert(response.data, formData);
      }
    },

    _showRequestConfirmationAlert(responseData, formData) {
      Swal.fire({
        title: responseData.title,
        text: responseData.text,
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3097d1',
        cancelButtonColor: '#e6007e',
        confirmButtonText: responseData.confirm_text,
        cancelButtonText: responseData.cancel_text,
      }).then((result) => {
        if (!result.value) {
          return;
        }
        formData.append('signature', responseData.signature);
        axios.post(window.foodPunk.routes.updateFlexmeal, formData)
            .then(response => this._handleConfirmationSubmit(response))
            .catch(response => this._handleSubmitFailure());
      });
    },

    _handleConfirmationSubmit(response) {
      if (response.data.success) {
        this._showInfoAndReload(response.data.message);
        return;
      }

      // Just show error message
      Swal.fire({
        text: response.data.message,
        icon: 'error',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3097d1',
        cancelButtonColor: '#e6007e',
      })
    },

    _showInfoAndReload(message) {
      Swal.fire({
        text: message,
        icon: 'success',
        timer: 1000,
        timerProgressBar: true
      }).then((result) => window.location.reload());
    },

    /*TODO: translate later*/
    _handleSubmitFailure() {
      bus.$emit('close_flexmeal_edit_modal');
      Swal.fire({
        title: 'Oops',
        text: 'Something went wrong, please try again later or contact support',
        icon: 'error',
        showCancelButton: false,
        allowEscapeKey: true,
        allowEnterKey: false,
      })
    },
  },
};
</script>
