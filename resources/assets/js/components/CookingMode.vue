<template>
  <div class="wrapper">
    <button :class="{
        'btn btn-inactive': !isActivated,
        'btn btn-active': isActivated
      }" type="button" @click="toggleActiveMode()">{{ btnText }}
    </button>
    <a tabindex="0"
       role="button"
       data-trigger="click hover focus"
       class="btn-with-icon btn-with-icon-question-o"
       data-toggle="popover"
       :data-content="i18n.tooltip"
       :aria-label="i18n.tooltip"
       data-placement="top">
    </a>
  </div>
</template>
<script>
import NoSleep from 'nosleep.js';

export default {
  name: "CookingMode",
  props: {
    i18n: Object
  },
  data() {
    return {
      isActivated: false,
      btnText: this.i18n.start,
      wakeLock: null,
    }
  },
  mounted() {
    this.wakeLock = new NoSleep();
  },
  methods: {
    toggleActiveMode() {
      this.isActivated = !this.isActivated;
      if (this.isActivated) {
        this.btnText = this.i18n.stop;
        this.wakeLock.enable();
        return;
      }
      this.btnText = this.i18n.start;
      this.wakeLock.disable();
    },
  }
}
</script>
<style scoped>
.btn-inactive {
  background-color: #fff;
  color: #000;
}

.btn {
  width: 80%;
  transition: background-color ease-in-out 0.16s, color ease-in-out 0.16s;
}

.btn-active {
  background-color: #e6007e;
  color: #fff;
}

.wrapper {
  float: right; /*due to Bootstrap 3*/
  width: 70%;
}
</style>