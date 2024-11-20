<template>
  <div class="default-text" :class="question.slug + '-text'">
    <label :for="question.slug" class="questionnaire-title">{{ question.title }}
      <span v-if="question.is_required" class="questionnaire-title__required-sign">*</span>
    </label>
    <input :type="inputType"
           :id="question.slug"
           class="default-text-input"
           :class="question.slug + '-text-input'"
           :name="question.options[0].key"
           :placeholder="question.options[0].value"
           :required="question.is_required"
           :value="question.answer"
           @input="updateValue($event)"
    >
  </div>
</template>

<script>
export default {
  name: 'QuestionnaireInput',
  props: {
    inputValue: {},
    question: {type: Object},
  },
  computed: {
    inputType() {
      return this.question.type === 'NUMBER' ? 'number' : 'text';
    },
  },
  methods: {
    updateValue(event) {
      this.$emit('update:inputValue', event.target.value ?? '');
    },
  },
};
</script>

