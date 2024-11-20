<template>
  <div>
    <label class="questionnaire-title">{{ question.title }}
      <span v-if="question.is_required" class="questionnaire-title__required-sign">*</span>
    </label>
    <div class="datepicker">
      <datepicker
          type="date"
          v-model="datepickerValue"
          :language="locale"
          :monday-first="true"
          class="datepicker-input"
          ref="datepickerRef"
      ></datepicker>
      <input type="date" v-model="inputDate" @click="openPicker($event)" class="date-input">
      <span class="datepicker-icon" @click="openPicker($event)">
        <i class="glyphicon glyphicon-calendar"></i>
      </span>
    </div>
  </div>
</template>


<script>
import {defineComponent} from 'vue';
import Datepicker from 'vuejs-datepicker';
import {de, en} from 'vuejs-datepicker/dist/locale';

export default defineComponent({
  components: {Datepicker},
  props: {
    inputValue: {},
    question: {type: Object},
  },
  data() {
    return {
      inputDate: null,
      locale: this.$i18n.locale === 'en' ? en : de,
      datepickerValue: null,
    };
  },
  methods: {
    openPicker(e) {
      e.preventDefault();
      this.$refs.datepickerRef.showCalendar();
    },
    updateValue() {
      this.$emit('update:inputValue', this.formatDate(this.inputDate));
    },
    // change format of date to match api format
    formatDate(date, type) {
      date = new Date(date);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      if (type === 'api') {
        return `${day}.${month}.${year}`;
      }
      return `${year}-${month}-${day}`;
    },
    isValidDateFormat(dateString) {
      const regex = /^\d{4}-\d{2}-\d{2}$/;
      return regex.test(dateString);
    },
  },
  watch: {
    inputDate: function (newVal) {
      this.datepickerValue = newVal;
      this.$emit('update:inputValue', this.formatDate(newVal, 'api'));
    },
    datepickerValue: function (newVal) {
      if (this.isValidDateFormat(this.formatDate(newVal))) {
        this.inputDate = this.formatDate(newVal);
      }
    },
  },
  mounted() {
    if (this.question.answer && this.question.answer.length > 0) {
      // change format of date to match the datepicker plugin
      this.inputDate = this.question.answer.split('.').reverse().join('-');
    }
  },
});
</script>