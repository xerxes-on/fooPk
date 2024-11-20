<template>
  <div class="subscription-information-wrapper">
    <h1>
      {{ $t('common.subscription_details') }}
      <button class="btn btn-tiffany" data-dismiss="modal" type="button" @click="fetchSubscriptionData(true)">
        {{ $t('common.refresh') }}
        <i v-if="busy" id="subscription-refresh-spinner" aria-hidden="true" class="fa fa-spinner fa-spin"
           style="margin-left: 5px;"></i>
        <i v-else :title="$t('common.refresh')" class="fa fa-refresh" style="cursor: pointer; margin-left: 5px;"></i>
      </button>
    </h1>


    <div class="subscriptions-list">

      <div v-if="subscriptions && subscriptions.length">

        <div v-for="subscriptionData in subscriptionsFiltered" :key="subscriptionData.id" class="content-panel">
          <ul>

            <li v-if="subscriptionData.plan && subscriptionData.plan.name ">
              <strong>{{ $t('common.subscription') | capitalize }}:</strong>
              <span> {{ subscriptionData.plan.name }}</span></li>
            <li><strong>{{ $t('common.status') | capitalize }}:</strong>
              <span v-if="['active','in_trial'].includes(subscriptionData.status)">{{
                  $t('common.active')
                }}, {{ $t('common.since') }} {{
                  'active' === subscriptionData.status ? subscriptionData.activated_at : subscriptionData.started_at
                }}</span>
              <span v-if="'non_renewing' === subscriptionData.status">{{
                  $t('common.cancelled')
                }}, {{ $t('common.until') }} {{ subscriptionData.cancelled_at }}</span>
              <span v-if="'cancelled' === subscriptionData.status">{{ $t('common.cancelled') }}</span>

            </li>
            <li><strong>{{ $t('common.next_billing') | capitalize }}:</strong>
              <span>{{ subscriptionData.next_billing_at || $t('common.none') }}</span></li>

            <template v-if="'active' === subscriptionData.status">

              <li v-if="subscriptionData.next_billing_amount"><strong>{{
                  $t('common.amount_short') | capitalize
                }}:</strong>
                <span>{{ parseFloat(subscriptionData.next_billing_amount).toFixed(2) }} {{
                    subscriptionData.currency_code
                  }}</span></li>
              <li v-if="subscriptionData.payment_method && subscriptionData.payment_method.type">
                <strong>{{ $t('common.payment_method') | capitalize }}:</strong> <span>{{
                  subscriptionData.payment_method.type
                }}</span></li>
            </template>

          </ul>
        </div>

      </div>
      <div v-else>
        {{ $t('common.no_active_subscription') | capitalize }}
      </div>

      <div v-if="userHasActiveSubscriptions">
        <slot name="cancel-subscription-message"></slot>
      </div>

    </div>


  </div>


</template>

<script>
export default {
  name: 'SubscriptionsList',
  data() {
    return {
      subscriptions: [],
      busy: false,
    };
  },
  methods: {
    fetchSubscriptionData(firedManually = false) {
      self = this;
      $.ajax({
        type: 'POST',
        beforeSend: () => {
          self.busy = true;
        },
        url: '/user/chargebee/update-subscription-data',
        data: {
          _token: $('meta[name=csrf-token]').attr('content'),
          fired_manually: firedManually ? 1 : 0,
        },
        complete: () => {
          self.busy = false;
        },
        success: function (resp) {
          let {data: user} = resp;
          if (user && user.assigned_chargebee_subscriptions) {
            self.subscriptions = user.assigned_chargebee_subscriptions.filter(s => s.assigned_user_id === user.id).map(s => s.data);
          }
        },
        error: function (err) {
          console.log(err);
        },
      });
    },
  },
  computed: {
    userHasActiveSubscriptions() {
      return this.subscriptions.filter(s => 'active' === s.status).length;
    },
    subscriptionsFiltered() {
      return this.userHasActiveSubscriptions
          ? this.subscriptions.filter(s => 'active' === s.status)
          : this.subscriptions;
    },
  },
  mounted() {

    this.fetchSubscriptionData();

  },
};
</script>

<style scoped>

</style>