<template>
  <fieldset :class="question.slug">
    <legend class="questionnaire-title">{{ question.title }}
      <span v-if="question.is_required" class="questionnaire-title__required-sign">*</span>
    </legend>
    <div class="sports-grid">
      <div v-for="option in updatedOptions" class="sports-grid-item">
        <input type="checkbox" :id="option.mode" class="sports-checkbox" v-model="values[option.mode].isChecked">
        <label class="sports-label" :class="option.mode" :for="option.mode">
          <div class="sports-label-content">
            <div class="tooltip-box">
              <tooltip :text="option.tooltip"></tooltip>
            </div>
            <div>
              <span class="sports-label-title" v-text="option.title"></span>
            </div>
            <div>
              <div class="sports-label-select">
                <input type="number"
                       v-model="values[option.mode].frequency"
                       :id="option.mode + '_frequency'"
                       class="sports-label-input"
                       max="120"
                       min="1">
                <label :for="option.mode + '_frequency'" class="sports-card-select-label">{{ option.frequency }}</label>
              </div>
              <div class="sports-label-select">
                <input type="number"
                       v-model="values[option.mode].duration"
                       :id="option.mode + '_duration'"
                       class="sports-label-input"
                       max="120"
                       min="1">
                <label :for="option.mode + '_duration'">{{ option.duration }}</label>
              </div>
            </div>
          </div>
        </label>
      </div>
    </div>
  </fieldset>
</template>

<script>
import {defineComponent} from 'vue';
import Tooltip from '../components/Tooltip.vue';

export default defineComponent({
  components: {Tooltip},

  props: {
    inputValue: {},
    question: {type: Object},
  },
  data() {
    return {
      values: this.initializeValues(['easy', 'medium', 'intensive']),
      updatedOptions: [],
      answerFormat: [],
      valuesState: {
        easyCheckbox: false,
        easyInput: false,

        mediumCheckbox: false,
        mediumInput: false,

        intensiveCheckbox: false,
        intensiveInput: false,
      },
    };
  },
  methods: {
    initializeValues(modes) {
      const values = {};
      modes.forEach(mode => {
        values[mode] = {isChecked: false, duration: null, frequency: null};
      });
      return values;
    },

    convertOptions() {
      const optionMap = new Map(this.question.options.map(o => [o.key, o]));

      this.updatedOptions = this.question.options.filter((_, index) => index % 3 === 0).map(option => {
        const sportMode = option.key;
        const frequencyKey = `_${sportMode}_frequency`;
        const durationKey = `_${sportMode}_duration`;

        return {
          frequency: optionMap.get(frequencyKey)?.value || 0,
          duration: optionMap.get(durationKey)?.value || 0,
          tooltip: option.tooltip,
          title: option.value,
          mode: sportMode,
        };
      });
    },

    fetchOptions() {
      // fetch options for defining selected answers
      if (Array.isArray(this.question.answer) && this.question.answer.length > 0) {
        this.question.answer.forEach(item => {
          const [key, obj] = Object.entries(item)[0];

          if (this.values[key]) {
            this.values[key].isChecked = true;
            this.values[key].frequency = obj.frequency;
            this.values[key].duration = obj.duration;
          }
        });
      }
    },

    updateChecked(mode, newVal) {
      this.valuesState[`${mode}Input`] = newVal.duration || newVal.frequency;
      this.valuesState[`${mode}Checkbox`] = newVal.isChecked;
    },
    updateInputState(mode, newVal) {
      this.values[mode].isChecked = newVal;
    },
    updateCheckboxState(mode, newVal) {
      if (!newVal) {
        this.values[mode].frequency = null;
        this.values[mode].duration = null;
      }
    },
  },
  mounted() {
    this.convertOptions();
    this.fetchOptions();
  },

  watch: {
    values: {
      handler(newVal, oldVal) {
        // create required answer format for api
        for (let key in newVal) {
          if (newVal.hasOwnProperty(key)) {
            const obj = {};
            obj[key] = {
              duration: parseInt(newVal[key].duration),
              frequency: parseInt(newVal[key].frequency),
            };

            if (newVal[key].isChecked) {
              this.answerFormat = this.answerFormat.filter(item => !item.hasOwnProperty(key));
              this.answerFormat.push(obj);
            } else {
              this.answerFormat = this.answerFormat.filter(item => !item.hasOwnProperty(key));
            }
          }
        }

        this.$emit('update:inputValue', this.answerFormat);
      },
      deep: true,
    },

    'values.easy': {
      handler(newVal) {
        this.updateChecked('easy', newVal);
      },
      deep: true,
    },
    'values.medium': {
      handler(newVal) {
        this.updateChecked('medium', newVal);
      },
      deep: true,
    },
    'values.intensive': {
      handler(newVal) {
        this.updateChecked('intensive', newVal);
      },
      deep: true,
    },

    'valuesState.easyInput': {
      handler(newVal) {
        this.updateInputState('easy', newVal);
      },
      deep: true,
    },
    'valuesState.mediumInput': {
      handler(newVal) {
        this.updateInputState('medium', newVal);
      },
      deep: true,
    },
    'valuesState.intensiveInput': {
      handler(newVal) {
        this.updateInputState('intensive', newVal);
      },
      deep: true,
    },

    'valuesState.easyCheckbox': {
      handler(newVal) {
        this.updateCheckboxState('easy', newVal);
      },
      deep: true,
    },
    'valuesState.mediumCheckbox': {
      handler(newVal) {
        this.updateCheckboxState('medium', newVal);
      },
      deep: true,
    },
    'valuesState.intensiveCheckbox': {
      handler(newVal) {
        this.updateCheckboxState('intensive', newVal);
      },
      deep: true,
    },
  },
});
</script>
