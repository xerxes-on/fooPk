<template>
  <select>
    <slot></slot>
  </select>
</template>

<script>
export default {
  name: 'select2',
  props: ['options', 'value'],
  mounted: function () {
    const vm = this;
    $(this.$el).val(this.value)
        // init select2
        .select2({width: '90%', data: this.options})
        // emit event on change.
        .on('select2:select', function (e) {
          vm.$emit('input', this.value);
          const option = e.params.data.data;
          vm.$emit('ingredientSelected', {
            proteins: option.proteins,
            fats: option.fats,
            carbohydrates: option.carbohydrates,
            calories: option.calories,
            unit: option.unit,
            unitDefaultAmount: option.unitDefaultAmount,
          });
        });
  },
  watch: {
    // update value
    value: function (value) {
      $(this.$el).val(value);
    },
    // update options
    options: function (options) {
      $(this.$el).select2({data: options});
    },
  },
  destroyed: function () {
    $(this.$el).off().select2('destroy');
  },
};
</script>
