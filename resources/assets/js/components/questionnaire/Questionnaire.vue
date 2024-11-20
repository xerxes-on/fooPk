<template>
  <div>
    <div v-if="isLoading" class="isLoading">
      <span class="loader"></span>
    </div>
    <div v-else class="container questionnaire">
      <form ref="questionnaireForm" action="/user/questionnaire/store" method="POST">
        <input type="hidden" name="_token" :value="csrfToken">
        <div class="questionnaire-body">
          <transition name="fade" mode="out-in">
            <component :is="question.slug"
                       :key="question.id"
                       :question="question"
                       :inputValue.sync="inputValue"
            />
          </transition>
          <p class="questionnaire-validation-message">{{ validationErrorMessage }}</p>
        </div>
        <div class="questionnaire-footer">
          <progress-bar :percentage="question.progress"/>
          <div class="questionnaire-footer__bottom">
            <div class="questionnaire-footer__left">
              <button @click="prev" type="button" class="questionnaire-footer__prev-btn">
                <i class="questionnaire-footer__prev-btn-arrow"></i>
              </button>
              <span class="questionnaire-footer__left-text"> {{ $t('questionnaire.common.progress') }} {{
                  question.progress
                }} %</span>
            </div>
            <div class="questionnaire-footer__right">

              <button @click="next($event)" class="questionnaire-footer__next-btn">
                {{ question.progress === 100 ? $t('questionnaire.common.submit') : $t('questionnaire.common.next') }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script>

import ProgressBar from './components/ProgressBar.vue';
import ExcludeIngredients from '../questionnaire/questions/ExcludeIngredients.vue';
import Sports from '../questionnaire/questions/Sports.vue';
import DateInput from '../questionnaire/questions/common/DateInput.vue';
import Text from './questions/common/Text.vue';
import Checkbox from './questions/common/Checkbox.vue';
import Radio from './questions/common/Radio.vue';

export default {
  name: 'Questionnaire',
  components: {
    ProgressBar,
    'main_goal': Radio,
    'weight_goal': Text,
    'extra_goal': Checkbox,
    'first_name': Text,
    'main_goal_reason': Checkbox,
    'circumstances': Checkbox,
    'sociability': Radio,
    'difficulties': Checkbox,
    'lifestyle': Radio,
    'diets': Checkbox,
    'meals_per_day': Radio,
    'allergies': Checkbox,
    'exclude_ingredients': ExcludeIngredients,
    'sports': Sports,
    'recipe_preferences': Radio,
    'diseases': Checkbox,
    'motivation': Radio,
    'gender': Radio,
    'birthdate': DateInput,
    'height': Text,
    'weight': Text,
    'fat_content': Radio,
    'features': Checkbox,
  },
  data() {
    return {
      question: {
        answer: '',
        id: '',
        is_required: '',
        options: '',
        order: '',
        progress: 0,
        slug: '',
        subtitle: '',
        title: '',
        type: '',
        questions_count: '',
      },
      inputValue: '',
      validationErrorMessage: '',
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      isLoading: true,
    };
  },
  methods: {
    next(e) {
      e.preventDefault();

      if (this.validateInput()) {
        let data = {
          question_id: this.question.id,
          answer: this.prepareAnswerFormat(),
        };
        this.sendAjaxRequest('POST', '/user/questionnaire/next', data).fail((error) => {
          const jsonResponse = JSON.parse(error.responseText);
          if (error.status === 422) {
            this.validationErrorMessage = jsonResponse.message;
          }
          if (jsonResponse.errors === 'no_more_question') {
            this.validationErrorMessage = '';
            this.submitForm();
          }
          this.isLoading = false;
        });
      }
    },
    prev() {
      if (this.question.order > 1) {
        let data = {
          question_id: this.question.id,
        };
        this.sendAjaxRequest('POST', '/user/questionnaire/previous', data);
      }
    },
    startQuestion() {
      this.sendAjaxRequest('GET', '/user/questionnaire/start');
    },
    prepareAnswerFormat() {
      return JSON.stringify({[this.question.slug]: this.inputValue});
    },
    sendAjaxRequest(method, url, data) {
      return $.ajax({
        method: method,
        url: url,
        data: data,
        headers: {
          'X-CSRF-TOKEN': this.csrfToken,
        },
      }).then(res => {
        if (res.success) {
          this.inputValue = res.data.answer;
          this.question = {...res.data};
          this.validationErrorMessage = '';
          this.isLoading = false;
        }
      });
    },

    submitForm() {
      this.$refs.questionnaireForm.submit();
    },
    validateInput() {
      if (this.question.is_required) {
        if (this.inputValue === null || (Array.isArray(this.inputValue) && this.inputValue.length === 0)) {
          this.validationErrorMessage = this.$i18n.t('questionnaire.validation.required');
          return false;
        }
      }
      if (this.question.slug === 'weight_goal') {
        if (this.inputValue) {
          this.inputValue = this.roundIfValueIsFloat(this.inputValue);

          if (this.inputValue < 40 || this.inputValue > 200) {
            this.validationErrorMessage = this.$i18n.t('questionnaire.validation.invalid_weight_goal');
            return false;
          }
        } else {
          this.inputValue = '';
        }
      }
      if (this.question.slug === 'first_name') {
        if (this.inputValue.length < 2) {
          this.validationErrorMessage = this.$i18n.t('questionnaire.validation.invalid_first_name');
          return false;
        }
      }
      if (this.question.slug === 'sports') {
        if (!this.inputValue) {
          this.inputValue = [];
        }
      }
      if (this.question.slug === 'height') {
        if (this.inputValue) {
          this.inputValue = this.roundIfValueIsFloat(this.inputValue);

          if (this.inputValue < 100 || this.inputValue > 250) {
            this.validationErrorMessage = this.$i18n.t('questionnaire.validation.invalid_height');
            return false;
          }
        }
      }
      if (this.question.slug === 'weight') {
        if (this.inputValue) {
          this.inputValue = this.roundIfValueIsFloat(this.inputValue);

          if (this.inputValue < 40 || this.inputValue > 200) {
            this.validationErrorMessage = this.$i18n.t('questionnaire.validation.invalid_weight');
            return false;
          }
        }
      }
      if (this.question.slug === 'birthdate') {
        if (this.inputValue) {
          if (!/^\d{2}\.\d{2}\.\d{4}$/.test(this.inputValue)) {
            this.validationErrorMessage = this.$i18n.t('questionnaire.validation.invalid_birthdate');
            return false;
          }

          const [day, month, year] = this.inputValue.split('.').map(num => parseInt(num, 10));
          const date = new Date(year, month - 1, day);
          const currentDate = new Date();
          const age = currentDate.getFullYear() - year;

          // Check for valid date and age between 16 and 100
          if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
            this.validationErrorMessage = this.$i18n.t('questionnaire.validation.invalid_birthdate');
            return false;
          }

          if (age < 16 || age > 100) {
            this.validationErrorMessage = this.$i18n.t('questionnaire.validation.invalid_birthdate_age');
            return false;
          }
        }

      }
      return true;
    },

    roundIfValueIsFloat(value) {
      if (!Number.isInteger(parseFloat(this.inputValue))) {
        value = parseFloat(value).toFixed(2);
      }
      return value;
    },
  },
  mounted() {
    this.startQuestion();
  },
};
</script>