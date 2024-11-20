<template>
  <div class="wrapper-apply-recipe">
    <a v-if="isIngestion" class="btn" href="#" @click.prevent="showModal = true">
      <img height="24" src="/images/icons/ic_calendar.svg" width="24" role="presentation" alt=""/>
    </a>

    <div v-else class="control-block">
      <span class="replace-ingredient print-hide" @click.prevent="showModal = true">
        <img role="presentation" alt="Edit" height="24" src="/images/icons/ic_edit_black.svg" width="24">
      </span>
    </div>

    <modal v-if="showModal" @close="closeModal()">
      <div slot="header">
        <div class="modal-title">{{ $t('common.apply_recipe_header') }}</div>
        <div v-if="!isIngestion && !applyResult" class="alert alert-warning" role="alert">
          {{ $t('common.apply_recipe_header_empty_ingestion') }}
        </div>
      </div>

      <div slot="body">
        <div v-if="applyResult" class="alert alert-success" role="alert">{{ resultMsg }}</div>

        <div v-if="!applyResult" class="col-md-8">
          <datepicker
              v-model="myDate"
              :inline="true"
              :language="languages[language]"
              :monday-first="true"
          ></datepicker>
        </div>

        <div v-if="!applyResult" class="col-md-4">
          <label v-for="(name, value) in mealTime" v-if="value != 4" class="radio">{{ $t('common.' + name) }}
            <input v-model="ingestion"
                   name="is_ingestion"
                   type="radio"
                   v-bind:value="value">
            <span class="checkround"></span>
          </label>
        </div>
      </div>

      <div slot="footer" style="display: flex;justify-content: flex-end;align-items: center;">
        <div>
          <button class="btn btn-pink" type="button" @click="closeModal()">
            <span v-if="!applyResult">{{ $t('common.cancel') }}</span>
            <span v-else>OK</span>
          </button>
        </div>

        <div v-if="!applyResult" style="margin-left: 20px;">
          <button class="btn btn-tiffany" data-dismiss="modal" type="button" @click="applyRecipe()">
            {{ $t('common.apply') }}
          </button>
        </div>

        <div v-if="applyResult && !isIngestion && resultUrl">
          <button class="btn btn-pink" type="button" @click="go2recipe()">{{ $t('common.go_to_recipe') }}</button>
        </div>
      </div>
    </modal>
  </div>
</template>

<script>
// register it as a component
import Modal from './Modal';
import Datepicker from 'vuejs-datepicker';
import * as lang from 'vuejs-datepicker/dist/locale';
import {bus} from '../app';

export default {
  props: {
    recipe: Number,
    mealTime: Object,
    recipeType: Number,
    isIngestion: {type: Boolean, default: true},
  },

  components: {
    Modal,
    Datepicker,
  },

  data() {
    return {
      showModal: false,
      ingestion: Number(Object.keys(this.mealTime)[0]),
      applyResult: false,
      resultMsg: '',
      resultUrl: '',

      myDate: new Date(),
      languages: lang,
      language: this.$i18n.locale,
    };
  },

  methods: {
    applyRecipe() {
      $('#loading').show();
      this.showModal = false;

      axios.post('/user/recipes/apply_to_date', {
        recipe_id: this.recipe,
        date: this.myDate,
        mealtime: Number(this.ingestion),
        recipe_type: this.recipeType,
      }).then(({data}) => {
        this.resultMsg = data.message;
        this.resultUrl = data.url;
        this.applyResult = true;

        $('#loading').hide();
        this.showModal = true;
      }).catch((data) => {
        console.log(data);
        $('#loading').hide();
      });
    },

    closeModal() {
      bus.$emit('modal-close');
      this.showModal = false;
      this.applyResult = false;
    },

    go2recipe: function () {
      window.location = this.resultUrl;
    },
  },
};
</script>

<style scoped>
.radio {
  display: block;
  position: relative;
  padding-left: 30px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 20px;
  line-height: 1.5em;
  text-align: left;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none
}

/* Hide the browser's default radio button */
.radio input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  margin: 0;
}

/* Create a custom radio button */
.checkround {
  position: absolute;
  top: 6px;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #fff;
  border-color: #e6007e;
  border-style: solid;
  border-width: 2px;
  border-radius: 50%;
}

/* When the radio button is checked, add a blue background */
.radio input:checked ~ .checkround {
  background-color: #fff;
}

/* Create the indicator (the dot/circle - hidden when not checked) */
.checkround:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the indicator (dot/circle) when checked */
.radio input:checked ~ .checkround:after {
  display: block;
}

/* Style the indicator (dot/circle) */
.radio .checkround:after {
  left: 2px;
  top: 2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #e6007e;
}

.alert {
  margin: 0;
}
</style>