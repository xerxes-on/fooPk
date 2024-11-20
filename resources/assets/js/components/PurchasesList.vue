<template>
  <button class="btn-with-icon btn-with-icon-cart mr-0"
          title="Add to purchase list"
          type="button"
          @click.prevent="addToShoppingList(recipeId,recipeType,mealDate,mealtime)">
  </button>
</template>

<script>
export default {
  props: {
    recipeId: {
      type: Number,
      required: true,
    },
    recipeType: {
      type: Number,
      required: true,
    },
    mealDate: {
      type: String,
      required: true,
    },
    mealtime: {
      type: Number,
      required: true,
    },
  },
  data: function () {
    return {
      portions: '',
    };
  },

  methods: {
    addToShoppingList(recipeId, recipeType, mealDate, mealtime) {
      this._toggleBtnStatus();

      this.portions = parseInt($('#portions').val());
      this.portions = isNaN(this.portions) ? 1 : this.portions; // Prevent sending null

      axios.post('/user/purchases/recipe/add-to-shopping-list', {
        recipe_id: recipeId,
        recipe_type: recipeType,
        date: mealDate,
        mealtime: mealtime,
        portions: this.portions,
      }).then(response => {
        this._toggleBtnStatus();
        alert(response.data.message);
      }).catch(response => {
        this._toggleBtnStatus();
        console.log(response.data);
      });
    },
    _toggleBtnStatus() {
      if (this.$el.disabled) {
        this.$el.disabled = false;
        this.$el.classList.remove('btn-with-icon-loading');
        this.$el.classList.add('btn-with-icon-cart');
        return;
      }

      this.$el.disabled = true;
      this.$el.classList.remove('btn-with-icon-cart');
      this.$el.classList.add('btn-with-icon-loading');
    },
  },
};
</script>