<template>
  <div class="progressbar">
    <div class="progressbar__fill"
         role="progressbar"
         :style="{width: `${percentage}%`}"
         :aria-valuenow="percentage"
         aria-valuemin="0"
         aria-valuemax="100">
      <span class="flex items-center h-full">
        <slot></slot>
      </span>
    </div>
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
  props: {
    percentage: {type: Number, default: 0},
  },
};
</script>
