<template>
  <div class="component-wrapper">

    <div class="recipe-status">
      <button v-if="isCooked" class="btn-clear recipe-card_completed-wrap" type="button" @click.prevent="unCook()">
        <span class="recipe-card_completed-wrap-content">{{ $t('common.completed') }}</span>
      </button>

      <div v-else-if="isEatOut" class="btn-clear recipe-card_completed-wrap">
        <span class="recipe-card_completed-wrap-content">{{ $t('common.skip_meal_short') }}</span>
      </div>

      <button v-else class="btn-clear is-completed-recipe" type="button" @click.prevent="showModal = true">
        <span class="text-center">{{ $t('common.completed') }}?</span>
      </button>
    </div>

    <modal v-if="showModal" @close="showModal = false">
      <h3 slot="header" class="modal-title">{{ $t('common.recipe_reviews') }}</h3>

      <div slot="body">
        <div class="star-rating">
          <form>
            <div class="star-rating__wrap">
              <input id="star-rating-5" v-model="rate" class="star-rating__input" required type="radio" value="5">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-5" title="5 out of 5 stars"></label>

              <input id="star-rating-4" v-model="rate" class="star-rating__input" required type="radio" value="4">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-4" title="4 out of 5 stars"></label>

              <input id="star-rating-3" v-model="rate" class="star-rating__input" required type="radio" value="3">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-3" title="3 out of 5 stars"></label>

              <input id="star-rating-2" v-model="rate" class="star-rating__input" required type="radio" value="2">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-2" title="2 out of 5 stars"></label>

              <input id="star-rating-1" v-model="rate" class="star-rating__input" required type="radio" value="1">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-1" title="1 out of 5 stars"></label>
            </div>
          </form>
        </div>
      </div>

      <div slot="footer">
        <button class="btn btn-tiffany" data-dismiss="modal" type="button" @click="submitAndClose(recipe)">
          {{ $t('common.apply') }}
        </button>
      </div>
    </modal>

    <div v-if="!isOldRecipe" class="recipe-card-edit">
      <actions-menu
          v-if="!isCooked"
          :date="date"
          :meal-time="mealTime"
          :recipe="recipe"
          :seasons="seasons"
          @inputData="updateEatOut"
          :recipe-type="recipeType"
      ></actions-menu>
    </div>
  </div>
</template>

<script>
export default {
  props: ['recipe', 'cooked', 'eatOut', 'date', 'mealTime', 'seasons', 'recipeType'],

  components: {
    Modal: () => import('./Modal'),
    VueStars: () => import('./VueStars'),
  },

  data: function () {
    return {
      isCooked: '',
      isEatOut: '',
      showModal: false, // TODO: If modal is shown, body scroll should be disabled
      rate: '',
      isOldRecipe: '',
    };
  },

  mounted() {
    this.isCooked = !!this.isCook;
    this.isEatOut = !!this._isEatOut;
    this.isOldRecipe = (this.date < new Date().toJSON().slice(0, 10));
  },

  computed: {
    isCook() {
      return this.cooked;
    },

    _isEatOut() {
      return this.eatOut;
    },
  },

  methods: {
    unCook() {
      axios.post(window.foodPunk.routes.unCook, {
        recipe: this.recipe,
        date: this.date,
        recipeType: this.recipeType,
      }).then(response => {
        if (!response.data.success) {
          return;
        }
        this.isCooked = false
      }).catch(response => console.error(response.data));
    },

    updateEatOut() {
      if (confirm(this.$i18n.t('common.skip_meal_question'))) {
        axios.post(window.foodPunk.routes.eatOut, {
          recipe: this.recipe,
          date: this.date,
          isEatOut: !this.isEatOut,
          recipeType: this.recipeType,
          mealtime: this.mealTime,
        }).then(response => {
          this.isEatOut = !this.isEatOut;
          if (this.isEatOut) {
            alert(this.$i18n.t('common.skip_meal_succeed'));
          }
        }).catch(response => console.error(response.data));
      }
    },

    submitAndClose() {

      if (this.rate.length === 0) {
        alert('Select please rating');
        return;
      }
      let currentObj = this;

      axios.post(window.foodPunk.routes.cook, {
        recipe: this.recipe,
        date: this.date,
        rate: this.rate,
        recipeType: this.recipeType,
        mealtime: this.mealTime,
      }).then(function (response) {
        if (!response.data.success) {
          return;
        }
        currentObj.isCooked = true;
        currentObj.showModal = false;
      }).catch(response => console.log(response.data));
    },
  },
};
</script>

<style scoped>
.star-rating {
  display: flex;
  justify-content: center;
}

.star-rating__wrap {
  display: inline-block;
  font-size: 1rem;
  float: left;
  height: 36px;
  padding: 0 10px;
}

.star-rating__wrap:after {
  content: "";
  display: table;
  clear: both;
}

.star-rating__ico {
  float: right;
  margin: 0;
  padding-left: 6px;
  line-height: 36px;
  font-size: 30px;
  cursor: pointer;
  color: #FFB300;
}

.star-rating__ico:last-child {
  padding-left: 0;
}

.star-rating__input {
  display: none;
}

.star-rating__ico:hover:before,
.star-rating__ico:hover ~ .star-rating__ico:before,
.star-rating__input:checked ~ .star-rating__ico:before {
  content: "\f005";
}
</style>
