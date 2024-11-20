<template>
  <div class="tooltip-container" @mouseover="showTooltip" @mouseleave="hideTooltip">
    <span class="tooltip-icon">?</span>
    <div class="tooltip-text">{{ text }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isTooltipVisible: false,
    };
  },
  props: {
    text: String,
  },
  methods: {
    showTooltip() {
      this.isTooltipVisible = true;
    },
    hideTooltip() {
      this.isTooltipVisible = false;
    },
  },
};
</script>

