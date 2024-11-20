<template>
  <div class="recipe-diets" v-bind:class="this.className">
    <span v-for="item in this.data" class="recipe-diets-item">{{ item.name }}</span>
  </div>
</template>

<script>
export default {
  name: 'RecipeDiets',
  props: {
    data: {type: Array | Object, required: true},
    className: {type: String, required: false},
  },
};
</script>
