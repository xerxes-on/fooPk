<template>
  <fieldset class="default-checkbox" :class="question.slug + '-checkbox'">
    <legend class="questionnaire-title">{{ question.title }}
      <span v-if="question.is_required" class="questionnaire-title__required-sign">*</span>
    </legend>

    <p class="questionnaire-sub-title">{{ question.subtitle }}</p>

    <div class="default-checkbox-grid" :class="question.slug + '-checkbox-grid'">
      <div v-for="option in question.options" class="default-checkbox-grid-item"
           :class="question.slug + '-checkbox-grid-item'">
        <input type="checkbox"
               :id="option.key"
               class="default-checkbox-input"
               :class="question.slug + '-checkbox-input'"
               :value="option.key"
               v-model="selectedAnswers"
               @change="handleCheckboxChange(option,selectedAnswers.includes(option.key))"
        >
        <label :for="option.key" class="default-checkbox-label" :class="question.slug + '-checkbox-label'">
          <span>{{ option.value }}</span>
          <span v-if="option.tooltip">
            <tooltip :text="option.tooltip"></tooltip>
          </span>
        </label>
      </div>
    </div>
    <div class="default-checkbox-textarea" v-if="showOtherOption">
      <textarea v-model="other" :placeholder="$t('questionnaire.common.other_placeholder')"></textarea>
    </div>
  </fieldset>
</template>


<script>
import Tooltip from '../../components/Tooltip.vue';

export default {
  name: 'QuestionnaireCheckbox',
  components: {Tooltip},
  props: {
    inputValue: {},
    question: {type: Object},
  },
  data() {
    return {
      selectedAnswers: [],
      other: '',
      showOtherOption: false,
    };
  },
  methods: {
    handleCheckboxChange(option, checked) {
      // exclude some options according to exclude attribute in api
      if (option.hasOwnProperty('exclude')) {
        if (checked) {
          this.disableOptions(option.exclude);
        }
      }
    },
    disableOptions(excludes) {
      if (Array.isArray(excludes)) {
        excludes.forEach((item) => {
          this.selectedAnswers = this.selectedAnswers.filter(selectedAnswer => selectedAnswer !== item);
        });
      }
    },
    formatAnswerForResponse(answer) {
      return answer.filter(item => item !== 'other');
    },
  },
  mounted() {
    let answer = [];

    if (typeof this.question.answer === 'object') {
      for (let key in this.question.answer) {
        if (this.question.answer.hasOwnProperty(key)) {
          if (key === 'other') {
            let tempObj = {};
            tempObj[key] = this.question.answer[key];
            answer.push(tempObj);
          } else {
            answer.push(this.question.answer[key]);
          }
        }
      }
    } else {
      answer = Array.isArray(this.question.answer) ? this.question.answer : this.selectedAnswers;
    }

    // fetch selected answers
    this.selectedAnswers = answer;

    // disable some options according to exclude attribute in api
    let options = Array.isArray(this.question.answer) ?
        this.question.options.filter(option => this.question.answer.includes(option.key)) :
        [];
    options.forEach((item) => {
      if (item.hasOwnProperty('exclude')) {
        this.disableOptions(item.exclude);
      }
    });

    // if other option selected add this value to selectedAnswers array
    const otherObject = Array.isArray(answer) ? answer.find(item => typeof item === 'object') : null;

    if (otherObject) {
      this.selectedAnswers.push('other');
      this.other = otherObject['other'];
    }
    // // emit selectedAnswers to parent component as an answer
    this.$emit('update:inputValue', this.formatAnswerForResponse(this.selectedAnswers));
  },
  watch: {
    selectedAnswers(newValue) {
      if (this.question.slug === 'allergies' || this.question.slug === 'diseases') {
        // Check if 'other' is present and toggle the visibility of other options
        const hasOther = newValue.includes('other');
        this.showOtherOption = hasOther;

        // Filter out 'other' string and objects with 'other' key
        newValue = newValue.filter(item => item !== 'other' && (!(item instanceof Object) || !('other' in item)));

        // If 'other' was present, add the object with 'other' key
        if (hasOther) {
          newValue.push({'other': this.other});
        }
      }
      this.$emit('update:inputValue', newValue);
    },

    other: function (newValue) {
      if ((this.question.slug === 'allergies' || this.question.slug === 'diseases') && this.selectedAnswers.includes('other')) {
        // Remove existing objects with 'other' key and then add the new 'other' value
        this.selectedAnswers = this.selectedAnswers.filter(item => !(item instanceof Object && 'other' in item)).concat({'other': newValue});
      }
      this.$emit('update:inputValue', this.selectedAnswers);
    },
  },
};
</script>
