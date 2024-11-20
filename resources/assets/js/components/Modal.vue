<template>
  <transition name="modal">

    <div class="modal-mask">
      <div class="modal-wrapper">

        <div class="modal-container">

          <div class="modal-header">
            <button class="close" type="button" @click="$emit('close')">
              <img src="/images/icons/ic_close.svg" alt=""/>
            </button>

            <slot name="header"></slot>
          </div>

          <div class="modal-body">
            <slot name="body"></slot>
          </div>

          <div class="modal-footer">
            <slot name="footer"></slot>
          </div>

        </div>

      </div>
    </div>

  </transition>
</template>

<script>
import {bus} from "../app";

export default {
  mounted() {
    document.body.classList.add('modal-open');
    bus.$on('modal-close', () => {
      document.body.classList.remove('modal-open')
    });
    this.$on('close', () => {
      document.body.classList.remove('modal-open')
    });
  },
}
</script>
