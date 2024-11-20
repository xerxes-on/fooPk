<template>
  <div :class="question.slug">
    <label :for="question.slug" class="questionnaire-title">{{ question.title }}
      <span v-if="question.is_required" class="questionnaire-title__required-sign">*</span>
    </label>
    <v-select multiple :class="question.slug + '-select'"
              @search="onSearch"
              @input="handleSelect"
              :options="availableOptions"
              v-model="selectedOptions"
    ></v-select>
  </div>
</template>

<script>
import {defineComponent} from 'vue';

export default defineComponent({
  props: {
    inputValue: {},
    question: {type: Object},
  },
  data() {
    return {
      selectedOptions: [],
      availableOptions: [],
    };
  },
  methods: {
    handleSelect() {
      const labels = new Set(this.selectedOptions.map(option => option.label));
      let newOptions = [];

      this.selectedOptions.forEach(item => {
        if (item.ingredients?.length > 0) {
          item.ingredients.forEach(option => {
            if (!labels.has(option.label)) {
              labels.add(option.label);
              newOptions.push(option);
            }
          });
        }
      });

      // Combine new options with the original selectedOptions, excluding the removed items
      this.selectedOptions = this.selectedOptions.filter(
          option => !option.hasOwnProperty('ingredients') || !option.ingredients.length).concat(newOptions);
    },
    onSearch(search, loading) {
      if (search.length) {
        this.search(loading, search, this);
      }
    },
    search: _.debounce(function (loading, search, vm) {
      $.ajax({
        method: 'POST',
        url: '/user/questionnaire/ingredients/search',
        data: {
          term: search,
          _type: 'query',
          q: search,
        },
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
      }).then(res => {
        res.data.forEach(function (item) {
          let updatedIngredients = [];
          if (item.hasOwnProperty('ingredients') && item.ingredients.length > 0) {
            updatedIngredients = item.ingredients.map(op => {
              return {id: op.key, label: op.value};
            });
          }
          vm.availableOptions.push({
            id: item.key,
            label: item.value,
            ingredients: updatedIngredients,
          });
        });
      });
    }, 350),
    fetchSelectedOptions() {
      this.selectedOptions = this.question.answer.map(item => ({
        id: item.key,
        label: item.value,
      }));
    },
  },
  watch: {
    selectedOptions: function (newQuestion, oldQuestion) {
      this.$emit('update:inputValue', newQuestion.map(item => item.id));
    },
  },

  mounted() {
    if (this.question.answer !== null && Array.isArray(this.question.answer) && this.question.answer.length > 0) {
      this.fetchSelectedOptions();
    }
  },
});
</script>
