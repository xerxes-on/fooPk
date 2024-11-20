<template>
  <div class="image-uploader">
    <img :src="previewImageData" class="image-uploader-image" alt="Flexmeal preview"/>
    <template v-if="previewImageData.includes('flexmeal.jpg')">
      <label :for="`${name}-label`" class="btn-tiffany text-center image-uploader-label">
        <span class="fa fa-upload" aria-hidden="true"></span>
      </label>
    </template>
    <template v-else>
      <button type="button" class="btn-clear btn-tiffany text-center" :aria-label="$t('common.delete')"
              @click="clearImage">
        <span class="fa fa-remove" aria-hidden="true"></span>
      </button>
    </template>
    <input type="file"
           :id="`${name}-label`"
           :name="name"
           :ref="name"
           class="image-uploader-input"
           accept=".jpg, .jpeg, .png"
           @change=uploadImage>
    <input type="hidden" :name="`old_${name}`" :value="oldImageData">
  </div>
</template>

<script>
export default {
  name: 'imageUploader',
  props: {
    previewImage: {
      type: String,
      required: false,
      default: '/images/flexmeal.jpg'
    },
    name: {
      type: String,
      required: false,
      default: 'image'
    }
  },
  data() {
    return {
      previewImageData: this.previewImage,
      oldImageData: this.previewImage,
    }
  },
  methods: {
    clearImage() {
      this.previewImageData = '/images/flexmeal.jpg';
      this.oldImageData = '';
      this.$refs[this.name].value = '';
    },
    uploadImage(e) {
      const image = e.target.files[0];
      const reader = new FileReader();
      reader.readAsDataURL(image);
      reader.onload = e => {
        this.previewImageData = e.target.result;
      };
    }
  }
}
</script>

<style>
.image-uploader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.image-uploader-image {
  max-width: 250px;
  border-radius: 15px;
}

.image-uploader-input {
  width: 1px;
  height: 1px;
  opacity: 0;
  visibility: hidden;
}

.image-uploader-label {
  cursor: pointer;
}
</style>
