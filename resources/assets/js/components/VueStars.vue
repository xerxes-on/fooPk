<template>
  <div ref="ratingEl"
       :class="{readonly:readonly,notouch:notouch}"
       :style="mapCssProps"
       class="vue-stars"
       :aria-label="title"
       :title="title"
  >
    <input :id="name+'0'" :checked="value===0" :name="name" type="radio" value="0">
    <template v-for="x in max">
      <label :key="'l'+x" :for="name+x">
        <span class="active"><slot name="activeLabel">{{ getActiveLabel(x) }}</slot></span>
        <span class="inactive"><slot name="inactiveLabel">{{ getInactiveLabel(x) }}</slot></span>
      </label>
      <input
          :id="name+x"
          :key="'i'+x"
          :checked="value===x"
          :disabled="readonly"
          :name="name"
          :value="x"
          type="radio"
          @change="updateInput($event.target.value)">
    </template>
  </div>
</template>

<script>
export default {
  name: 'VueStars',

  props: {
    max: {type: Number, required: false, default: 5},
    value: {type: Number, required: false, default: 0},
    title: {type: String, required: false, default: ''},
    name: {type: String, required: false, default: 'rating'},
    char: {type: String, required: false, default: '★'},
    inactiveChar: {type: String, required: false, default: null},
    readonly: {type: Boolean, required: false, default: false},
    activeColor: {type: String, required: false, default: '#FD0'},
    inactiveColor: {type: String, required: false, default: '#999'},
    shadowColor: {type: String, required: false, default: '#FF0'},
    hoverColor: {type: String, required: false, default: '#DD0'},
  },

  computed: {
    ratingChars() {
      return Array.from(this.char);
    },
    inactiveRatingChars() {
      /* Default to ratingChars if no inactive characters have been provided */
      return this.inactiveChar
          ? Array.from(this.inactiveChar)
          : this.ratingChars;
    },
    notouch() {
      /* For iPhone specifically but really any touch device, there is no true hover state, disabled any pseudo-hover activity. */
      return !('ontouchstart' in document.documentElement);
    },
    mapCssProps() {
      return {
        '--active-color': this.activeColor,
        '--inactive-color': this.inactiveColor,
        '--shadow-color': this.shadowColor,
        '--hover-color': this.hoverColor,
      };
    },
  },
  methods: {
    updateInput(v) {
      this.$emit('input', parseInt(v, 10));
    },
    getActiveLabel(x) {
      const s = this.ratingChars;
      return s[Math.min(s.length - 1, x - 1)];
    },
    getInactiveLabel(x) {
      const s = this.inactiveRatingChars;
      return s[Math.min(s.length - 1, x - 1)];
    },
  },
};
</script>

<style scoped>
.vue-stars {
  display: inline-flex;
  flex-flow: row nowrap;
  line-height: 1em;
}

.vue-stars label {
  display: block;
  float: left;
  padding: 0;
  margin: 0;
  text-align: center;
  color: #fd0;
  text-shadow: 0 0 0.3em #ff0;
}

.vue-stars input,
.vue-stars label .inactive,
.vue-stars input:checked ~ label .active,
.vue-stars.notouch:not(.readonly):hover label .inactive,
.vue-stars.notouch:not(.readonly) label:hover ~ label .active {
  display: none;
}

.vue-stars input:checked ~ label .inactive,
.vue-stars.notouch:not(.readonly):hover label .active,
.vue-stars.notouch:not(.readonly) label:hover ~ label .inactive {
  display: inline;
}

.vue-stars.notouch:not(.readonly):hover label {
  color: #dd0;
  text-shadow: 0 0 0.3em #ff0;
}

input:checked ~ label,
.vue-stars.notouch:not(.readonly) label:hover ~ label {
  color: #999;
  text-shadow: none;
}

@supports (color: var(--prop)) {
  .vue-stars label {
    color: var(--active-color);
    text-shadow: 0 0 0.3em var(--shadow-color);
  }

  .vue-stars.notouch:not(.readonly):hover label {
    color: var(--hover-color);
    text-shadow: 0 0 0.3em var(--shadow-color);
  }

  .vue-stars input:checked ~ label,
  .vue-stars.notouch:not(.readonly) label:hover ~ label {
    color: var(--inactive-color);
  }
}
</style>