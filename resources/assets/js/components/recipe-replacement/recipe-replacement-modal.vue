<template>
  <modal v-if="showModal" @close="showModal = false">
    <div slot="header">
      <div class="modal-title">{{ $t('common.select_new_recipe') }}</div>

      <div class="form-group input-group search-recipes_form">
        <div class="input-group-btn">
          <button :aria-label="$t('common.search')" class="search-recipes_form_btn" type="submit"></button>
        </div>
        <input v-model="keywords"
               :placeholder="$t('common.search_placeholder')"
               class="form-control mr-sm-2"
               type="text">
      </div>

      <div class="adv-search-filter-box">
        <div class="row adv-search-filter-fields">
          <div class="col-sm-3 col-xs-6">
            <label class="search-recipes_label" for="seasons">{{ $t('common.months') }}</label>
            <select id="seasons" v-model="search.seasons"
                    class="form-control search-recipes_select changeable-element"
                    @change="getResults">
              <option v-for="(season, seasonIndex) in seasons"
                      v-if="season"
                      :key="`season-${season.id}`"
                      v-bind:value="season.id">
                {{ season.name }}
              </option>
              <option v-else value="0">{{ $t('common.all') }}</option>
            </select>
          </div>
          <div class="col-sm-3 col-xs-6">
            <label class="search-recipes_label" for="ingestion">{{ $t('common.ingestion') }}</label>
            <select id="ingestion"
                    v-model="search.ingestion"
                    class="form-control search-recipes_select changeable-element"
                    @change="getResults">
              <option v-for="(item, index) in ingestions" v-if="ingestions" v-bind:value="item.id">
                {{ item.title }}
              </option>
              <option v-else value="0">{{ $t('common.all') }}</option>
            </select>
          </div>
          <div class="col-sm-3 col-xs-6">
            <label class="search-recipes_label" for="complexity">{{ $t('common.complexity') }}</label>
            <select id="complexity" v-model="search.complexity"
                    class="form-control search-recipes_select changeable-element"
                    @change="getResults">
              <option v-for="(item, index) in complexity" v-if="complexity" v-bind:value="item.id">
                {{ item.title }}
              </option>
              <option v-else value="0">{{ $t('common.all') }}</option>
            </select>
          </div>
          <div class="col-sm-3 col-xs-6">
            <label class="search-recipes_label" for="cost">{{ $t('common.cost') }}</label>
            <select id="cost"
                    v-model="search.cost"
                    class="form-control search-recipes_select changeable-element"
                    @change="getResults">
              <option v-for="(item, index) in cost" v-if="cost" v-bind:value="item.id">
                {{ item.title }}
              </option>
              <option v-else value="0">{{ $t('common.all') }}</option>
            </select>
          </div>
        </div>
        <div class="row adv-search-filter-fields">
          <div class="col-sm-3 col-xs-6">
            <label class="search-recipes_label" for="diet">{{ $t('common.diets') }}</label>
            <select id="diet"
                    v-model="search.diet"
                    class="form-control search-recipes_select changeable-element"
                    @change="getResults">
              <option v-for="(item, index) in diets" v-if="diets" v-bind:value="item.id">
                {{ item.title }}
              </option>
              <option v-else value="0">{{ $t('common.all') }}</option>
            </select>
          </div>
          <div class="col-sm-3 col-xs-6">
            <label class="search-recipes_label" for="favorite">{{ $t('common.favorite') }}</label>
            <select id="favorite"
                    v-model="search.favorite"
                    class="form-control search-recipes_select changeable-element"
                    @change="getResults">
              <option v-for="(item, index) in favorite" v-if="favorite" v-bind:value="item.id">
                {{ item.title }}
              </option>
              <option v-else value="0">{{ $t('common.all') }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div slot="body">
      <div class="select-recipe-list">
        <div v-for="(signature, index) in signatures.data"
             class="select-recipe-list_item"
             v-bind:class="{ active: isActive === index }">
          <label :for="`${index}_${signature.id}`" class="select-recipe-list_item_label">
            <input :id="`${index}_${signature.id}`"
                   v-model="recipeChange"
                   :value="signature.id"
                   class="sr-only"
                   name="checkedRecipe"
                   type="radio"
                   v-on:change="setActive(index)"/>
          </label>

          <div class="search-recipes_list_item_img">
            <img :alt="signature.title" :src="signature.image" height="150" width="150"/>
          </div>

          <div class="select-recipe-list_item_info">
            <div class="search-recipes_list_item_info_wrap">
                                <span :title="signature.title" class="select-recipe-list_item_info_title">{{
                                    signature.title
                                  }}</span>
              <div class="d-flex">
                <div class="select-recipe-list_item_right_label">
                  <vue-stars v-if="signature.complexity"
                             :max="3"
                             :name="`${index}_complexity`"
                             :readonly="true"
                             :title="signature.complexity === null ? '':signature.complexity.title"
                             :value="signature.complexity === null ? 0 :signature.complexity.id">
                    <img slot="activeLabel"
                         slot-scope="props"
                         alt=""
                         height="24"
                         role="presentation"
                         src="/images/icons/ic_hat_black.svg"
                         width="24"
                    />
                    <img slot="inactiveLabel"
                         slot-scope="props"
                         alt=""
                         height="24"
                         role="presentation"
                         src="/images/icons/ic_hat_black_empty.svg"
                         width="24"
                    />
                  </vue-stars>
                </div>
                <div class="search-recipes_list_item_right_label">
                  <vue-stars v-if="signature.price"
                             :max="3"
                             :name="`${index}__price`"
                             :readonly="true"
                             :title="signature.price.title"
                             :value="signature.price.id">
                    <img slot="activeLabel"
                         slot-scope="props"
                         alt=""
                         height="24"
                         role="presentation"
                         src="/images/icons/ic_money.svg"
                         width="24"/>
                    <img slot="inactiveLabel"
                         slot-scope="props"
                         alt=""
                         height="24"
                         role="presentation"
                         src="/images/icons/ic_money_noactive.svg"
                         width="24"/>
                  </vue-stars>
                </div>
              </div>
            </div>
            <div class="search-recipes_list_item_info_wrap my-6px">
              <recipe-ingestions v-if="signature.meal_time" :data="signature.meal_time"/>
              <div v-if="signature.cooking_time !== false" class="select-recipe-list_item_info_cooking-time">
                <span>{{ signature.cooking_time }} {{ signature.unit_of_time }}</span>
              </div>
              <div v-else class="select-recipe-list_item_info_cooking-time_invalid"></div>
            </div>

            <recipe-diets v-if="signature.diets.length"
                          :className="'select-recipe-list_item_right_label mobile-hidden'"
                          :data="signature.diets"/>
          </div>
          <div class="search-recipes_list_item_footer">
            <recipe-diets v-if="signature.diets"
                          :className="'select-recipe-list_item_right_label laptop-hidden'"
                          :data="signature.diets"/>
          </div>
        </div>
      </div>
    </div>

    <div slot="footer">
      <div class="row">
        <div class="col-md-7" style="text-align: left">
          <v-pagination :data="signatures" :limit="1" @pagination-change-page="getResults"></v-pagination>
        </div>

        <div class="col-md-5">
          <div>
            <button class="btn btn-base btn-pink" type="button" @click="showModal = false">
              {{ $t('common.cancel') }}
            </button>
            <button class="btn btn-base btn-tiffany" data-dismiss="modal" type="button" @click="submitAndClose()">
              {{ $t('common.replace') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </modal>
</template>

<script>
import {bus} from '../../app';

export default {
  props: {
    recipe: {
      type: Number,
      required: true,
    },
    mealTime: {
      type: String,
      required: true,
    },
    date: {
      type: String,
      required: true,
    },
    recipeType: {
      type: Number,
      required: true,
    },
    seasons: {
      type: Array,
      required: true,
    },
  },
  components: {
    Modal: () => import( '../Modal'),
    VueStars: () => import( '../VueStars'),
    vSelect: () => import( 'vue-select'),
    vPagination: () => import( 'laravel-vue-pagination'),
    RecipeDiets: () => import( '../RecipeDiets'),
    RecipeIngestions: () => import( '../RecipeIngestions'),
  },

  data() {
    return {
      showModal: false,
      isActive: false,
      recipeChange: '',
      search: {
        search_name: '',
        ingestion: 0,
        complexity: 0,
        cost: 0,
        diet: 0,
        favorite: 0,
        seasons: 0,
      },
      ingestions: [
        {id: 0, title: this.$i18n.t('common.all')},
        {id: 1, title: this.$i18n.t('common.breakfast')},
        {id: 2, title: this.$i18n.t('common.lunch')},
        {id: 3, title: this.$i18n.t('common.dinner')},
      ],
      complexity: [
        {id: 0, title: this.$i18n.t('common.all')},
        {id: 1, title: this.$i18n.t('common.easy')},
        {id: 2, title: this.$i18n.t('common.medium')},
        {id: 3, title: this.$i18n.t('common.complicated')},
      ],
      cost: [
        {id: 0, title: this.$i18n.t('common.all')},
        {id: 1, title: '$'},
        {id: 2, title: '$$'},
        {id: 3, title: '$$$'},
      ],
      diets: [
        {id: 0, title: this.$i18n.t('common.all')},
        {id: 14, title: this.$i18n.t('common.aip')},
        {id: 15, title: this.$i18n.t('common.bulletproof')},
        {id: 16, title: this.$i18n.t('common.vegan')},
        {id: 17, title: this.$i18n.t('common.vegetarisch')},
        {id: 18, title: this.$i18n.t('common.paleo')},
        {id: 19, title: this.$i18n.t('common.pescetarisch')},
        {id: 20, title: this.$i18n.t('common.primal')},
        {id: 21, title: this.$i18n.t('common.glutenfrei')},
        {id: 22, title: this.$i18n.t('common.milchfrei')},
        {id: 23, title: this.$i18n.t('common.nussfrei')},
      ],
      favorite: [
        {id: 0, title: this.$i18n.t('common.all')},
        {id: 1, title: 'Favorite'},
      ],
      keywords: null,
      signatures: {},
    };
  },

  watch: {
    keywords() {
      this.search.search_name = this.keywords;
      this.getResults();
    },
  },
  created() {
    bus.$on(`open_recipe_${this.recipe}_${this.mealTime}_${this.date}_modal`, this.setup);
  },
  methods: {
    setup(payload) {
      this.getResults();
      this.showModal = true;
    },
    getResults(page = 1) {
      axios.post(
          window.foodPunk.routes.getUserRecipes,
          {
            mealtime: this.mealTime,
            recipe_id: this.recipe,
            page: page,
            filters: this.search,
          },
      ).then(response => {
        this.signatures = response.data;
      });
    },

    submitAndClose() {
      if (this.recipeChange === '') {
        return alert(this.$i18n.t('common.select_replacement'));
      }

      $('#loading').show();
      axios.post(window.foodPunk.routes.replaceRecipe, {
        recipe: this.recipe,
        change: this.recipeChange,
        mealtime: this.mealTime,
        date: this.date,
        recipeType: this.recipeType,
      }).then(function () {
        location.reload();
      }).catch(response => {
        $('#loading').hide();
        console.error(response.data);
      });
    },

    setActive: function (el) {
      this.isActive = el;
    },
  },
};
</script>
