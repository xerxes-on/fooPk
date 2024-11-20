<template>
  <fieldset class="default-radio" :class="question.slug + '-radio'">

    <legend class="questionnaire-title">{{ question.title }}
      <span v-if="question.is_required" class="questionnaire-title__required-sign">*</span>
    </legend>

    <p class="questionnaire-sub-title">{{ question.subtitle }}</p>

    <div class="default-radio-grid" :class="question.slug + '-radio-grid'">
      <div v-for="option in question.options" class="default-radio-grid-item"
           :class="question.slug + '-radio-grid-item'">
        <input :name="question.slug"
               type="radio"
               :id="option.key"
               :required="question.is_required"
               class="default-radio-input"
               :class="question.slug + '-radio-input'"
               :value="option.key"
               @input="updateValue($event)"
               :checked="question.answer===option.key"
        >
        <label :for="option.key" class="default-radio-label" :class="[option.key, question.slug + '-radio-label']">
          <span class="radio-label-desc" :class="[question.slug + '-radio-label-desc']" v-text="option.value"></span>
        </label>
      </div>
    </div>

  </fieldset>
</template>


<script>
import Tooltip from '../../components/Tooltip.vue';

export default {
  name: 'QuestionnaireRadio',
  components: {Tooltip},
  props: {
    inputValue: {},
    question: {type: Object},
  },
  methods: {
    updateValue(event) {
      this.$emit('update:inputValue', event.target.value);
    },
  },
};
</script>
