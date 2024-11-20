<template>
  <button v-if="isFavorited"
          :aria-label="$t('common.favorited_recipes')"
          class="btn-clear btn-favorite btn-favorite-active"
          type="button"
          @click.prevent="unFavorite(recipe)">
  </button>
  <button v-else
          :aria-label="$t('common.favorite')"
          :class="{ 'btn-favorite-dark': color === 'black', 'btn-favorite-light':  color !== 'black' }"
          class="btn-clear btn-favorite"
          type="button"
          @click.prevent="favorite(recipe)">
  </button>
</template>

<script>
export default {
  props: {
    color: {type: String, required: false, default: 'black'},
    recipe: {},
    favorited: {},
  },

  data: function () {
    return {
      isFavorited: '',
    };
  },

  mounted() {
    this.isFavorited = !!this.isFavorite;
  },

  computed: {
    isFavorite() {
      return this.favorited;
    },
  },

  methods: {
    favorite(recipe) {
      axios.post('/user/favorite/' + recipe).then(response => this.isFavorited = true).catch(response => console.log(response.data));
    },

    unFavorite(recipe) {
      axios.post('/user/unfavorite/' + recipe).then(response => this.isFavorited = false).catch(response => console.log(response.data));
    },
  },
};
</script>
