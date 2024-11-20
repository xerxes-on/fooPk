<template>
  <div class="avatar-uploader">
    <div>
      <span :key="`avatar-${avatarViewIteration}`"
            :style="avatarUrl ?  `background-image: url('${avatarUrl}')` : ''"
            class="img"
            @click="trigger = true"></span>
    </div>
    <div class="avatar-toolbox">
      <button v-if="avatarUrl"
              :aria-label="lang('common.delete')"
              :title="lang('common.delete')"
              class="btn btn-white"
              @click.p.prevent="handleDelete">
        <span aria-hidden="true" class="fa fa-times"></span>
      </button>
      <button :aria-label="lang('common.image')"
              :title="lang('common.image')"
              class="btn btn-tiffany"
              @click.prevent="trigger = true">
        <span aria-hidden="true" class="fa fa-upload"></span>
      </button>
    </div>
    <avatar-cropper
        v-model="trigger"
        :requestOptions="requestOptions"
        :uploadUrl="uploadUrl"
        :mimes="mimes"
        @uploaded="handleUploaded"
    />
  </div>
</template>

<script>
// register it as a component
import AvatarCropper from './vue-avatar-cropper.vue';

export default {
  name: 'AvatarUploader',
  props: {
    profileImageUrl: {
      type: [String, null],
      default() {
        return null;
      },
    },
    trans: Object,
    defaultAvatarUrl: String,
    clientId: {
      type: Number,
      default() {
        return null;
      },
    },
    mimes: {
      type: String,
      default: 'image/png, image/gif, image/jpeg, image/bmp',
    },
  },
  components: {
    AvatarCropper,
  },
  data() {
    return {
      avatarViewIteration: 0,
      trigger: false,
      requestOptions: {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
        },
      },
      avatarUrl: null,
    };
  },
  methods: {
    lang(key) {
      if (this.$i18n) {
        return this.$i18n.t(key);
      }
      return this.trans[key] || key;
    },
    getToken() {
      return $('meta[name=csrf-token]').attr('content');
    },
    handleUploaded({response}) {
      response.json().then(data => {
        if (data.success) {
          this.avatarUrl = data.profile_image_url;
          this.setHeaderAvatar(data.profile_image_url);
          this.avatarViewIteration++;
        }
      });
    },
    setHeaderAvatar(url) {
      if (!this.adminMode) {
        $('.user .img').css('background-image', `url("${url}")`);
      }
    },
    handleDelete() {
      Swal.fire({
        title: this.lang('common.are_you_sure_you_want_to_remove_profile_picture'),
        text: '',
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3097d1',
        cancelButtonColor: '#e6007e',
        confirmButtonText: this.lang('common.yes'),
        cancelButtonText: this.lang('common.no'),
      }).then((result) => {
        if (result.value) {
          $.ajax({
            method: 'DELETE',
            url: this.deleteUrl,
          }).then(res => {
            if (res.success) {
              this.avatarUrl = null;
              this.setHeaderAvatar(this.defaultAvatarUrl);
            }
          });
        }
      });
    },
  },
  created() {
    this.avatarUrl = this.profileImageUrl;
  },
  computed: {
    uploadUrl() {
      return this.adminMode
          ? `/admin/clients/${this.clientId}/profile-image?_token=${this.getToken()}`
          : `/user/settings/profile-image?_token=${this.getToken()}`;
    },
    deleteUrl() {
      return this.adminMode
          ? `/admin/clients/${this.clientId}/profile-image?_token=${this.getToken()}`
          : '/user/settings/profile-image?_token=' + this.getToken();
    },
    adminMode() {
      return !!this.clientId;
    },
  },
};
</script>

<style scoped>
.avatar-uploader .img {
  display: block;
  border-radius: 50%;
  width: 200px;
  height: 200px;
  -o-object-fit: cover;
  object-fit: cover;
  background: url('/images/icons/Account.svg') no-repeat center;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  transition: all 0.3s ease;
  margin-left: auto;
  margin-right: auto;
  cursor: pointer;
}

.avatar-toolbox {
  text-align: center;
  margin-top: 10px;
}
</style>