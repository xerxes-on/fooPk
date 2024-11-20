<template>
  <div class="select-recipe-list_item_info_type">
    <span v-for="item in this.data" class="select-recipe-list_item_info_type_item">{{ item.title }}</span>
  </div>
</template>

<script>
export default {
  name: 'RecipeIngestions',
  props: {
    data: {type: Object | Array, required: true},
  },
};
</script>
