<template>
  <div>
    <div v-if="isCooked" class="recipe-card_completed-wrap">
      <img :alt="$t('common.recipe_is_completed')" src="/images/icons/ic_check-done.svg" width="35">
      {{ $t('common.completed') }}
    </div>

    <div v-else class="is-completed-recipe" @click.prevent="showModal = true">
      <span class="pull-left">{{ $t('common.completed') }}?</span>
      <img :alt="$t('common.recipe_is_not_completed')" src="/images/icons/ic_check_white.svg" width="24px"/>
    </div>

    <modal v-if="showModal" @close="showModal = false">
      <h4 slot="header" class="modal-title">
        {{ $t('common.recipe_reviews') }}
      </h4>

      <div slot="body">
        <div class="star-rating">
          <form>
            <div class="star-rating__wrap">
              <input id="star-rating-5" v-model="rate" class="star-rating__input" required type="radio" value="5">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-5"
                     title="5 out of 5 stars"></label>

              <input id="star-rating-4" v-model="rate" class="star-rating__input" required type="radio" value="4">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-4"
                     title="4 out of 5 stars"></label>

              <input id="star-rating-3" v-model="rate" class="star-rating__input" required type="radio" value="3">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-3"
                     title="3 out of 5 stars"></label>

              <input id="star-rating-2" v-model="rate" class="star-rating__input" required type="radio" value="2">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-2"
                     title="2 out of 5 stars"></label>

              <input id="star-rating-1" v-model="rate" class="star-rating__input" required type="radio" value="1">
              <label class="star-rating__ico fa fa-star-o fa-lg" for="star-rating-1"
                     title="1 out of 5 stars"></label>
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
  </div>
</template>

<script>
// register it as a component
import Modal from './Modal';

export default {
  props: ['recipe', 'cooked', 'date'],

  components: {
    Modal,
  },

  data: function () {
    return {
      isCooked: '',
      showModal: false,
      rate: '',
    };
  },

  mounted() {
    this.isCooked = this.isCook ? true : false;
  },

  computed: {
    isCook() {
      return this.cooked;
    },
  },

  methods: {
    unCook(recipe, date) {
      axios.post('/user/uncook/' + recipe + '/' + date).then(response => this.isCooked = false).catch(response => console.log(response.data));
    },

    submitAndClose() {
      if (this.rate.length === 0) {
        alert('Select please rating');
      } else {
        let currentObj = this;

        axios.post('/user/to-cook', {
          recipe: this.recipe,
          date: this.date,
          rate: this.rate,
        }).then(function (response) {
          currentObj.isCooked = true;
          currentObj.showModal = false;
        }).catch(response => console.log(response.data));
      }
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