<template>
  <div class="hello-form">
    <div
      class="modal fade"
      id="helloFormModal"
      tabindex="-1"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form @submit.prevent="save" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title text-secondary" id="exampleModalLabel">
                {{ $t("FORM") + " " + $t("HELLO") }}
              </h5>
              <button
                type="button"
                class="btn-close"
                aria-label="Close"
                data-dismiss="modal"
              ></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div
                  v-for="(image, index) in previewImages"
                  :key="index"
                  class="col-lg-2 col-md-4 col-6 image-container"
                >
                  <div class="border uploaded-image">
                    <i
                      v-if="selectedHello ? previewImages.length > 1 : true"
                      @click="deleteImage(image, index)"
                      class="image-delete fa fa-times text-secondary"
                    >
                    </i>
                    <img :src="image" />
                  </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 image-container">
                  <label class="add-image text-secondary border" for="image">
                    <div class="action">
                      <i class="fa fa-plus"></i>
                      Add photo
                    </div>
                    <input
                      @change="uploadImages"
                      accept="image/apng, image/avif, image/gif, image/jpeg, image/png, image/svg+xml, image/webp"
                      type="file"
                      id="image"
                    />
                  </label>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-danger">
                {{ $t("SUBMIT") }}
              </button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">
                {{ $t("CLOSE") }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import manyImageClient from "../../../shared/http-clients/many-image-client";
import { inject, reactive, toRefs, watch } from "vue-demi";
import { useI18n } from "vue-i18n";
export default {
  setup(props, context) {
    const { t, locale } = useI18n({ useScope: "global" });
    const hello_store = inject("hello_store");
    const toast = inject("toast");
    const data = reactive({
      uploadedImages: [],
      previewImages: [],
    });
    //Methods
    function uploadImages(e) {
      const image = e.target.files[0];
      let formData=new FormData();
      if (props.selectedHello) {
        formData.append("id", props.selectedHello.id);
        formData.append("image", image);
        manyImageClient.addImage(formData).then((response) => {
          context.emit("updated", response.data.many_image);
          data.previewImages = response.data.many_image.images.map(
            (image) => `/uploads/${image}`
          );
        });
      } else {
        data.uploadedImages.push(image);
        const reader = new FileReader();
        reader.readAsDataURL(image);
        reader.onload = (e) => {
          data.previewImages.push(e.target.result);
        };
      }
    }
    function deleteImage(image, index) {
      if (props.selectedHello) {
        console.log(image);
        manyImageClient.deleteImage(props.selectedHello.id,image.replace("/uploads/",""),index).then((response) => {
          context.emit("updated", response.data.many_image);
          data.previewImages = response.data.many_image.images.map((image) => {
            return `/uploads/${image}`;
          });
        });
      } else {
        data.uploadedImages.splice(index, 1);
        data.previewImages.splice(index, 1);
      }
    }
    function save() {
      if (data.previewImages.length == 0) {
        toast.error(t("IMAGE") + " " + t("required"));
        return;
      }
      if (!props.selectedHello) {
        store();
      } else {
        update();
      }
    }
    //Commons
    function store() {
      manyImageClient
        .store(getForm())
        .then((response) => {
          toast.success(t("CREATED_SUCCESSFULLY"));
          context.emit("created", response.data);
          $("#helloFormModal").modal("hide");
        })
        .catch((error) => {});
    }
    function update() {
      manyImageClient
        .update(getForm())
        .then((response) => {
          toast.success(t("UPDATED_SUCCESSFULLY"));
          context.emit("updated", response.data);
          $("#helloFormModal").modal("hide");
        })
        .catch((error) => {});
    }
    function getForm() {
      let formData = new FormData();
      if (props.selectedHello) {
        formData.append("id", props.selectedHello.id);
      }
      if (data.uploadedImages) {
        data.uploadedImages.forEach((image, index) => {
          formData.append(`images[${index}]`, image);
        });
      }
      return formData;
    }
    function setForm() {
      data.previewImages = props.selectedHello
        ? props.selectedHello.images.map((image) => {
            return `/uploads/${image}`;
          })
        : [];
      data.uploadedImages = [];
    }
    //Watchers
    watch(
      () => {
        hello_store.onFormShow;
      },
      (value) => {
        setForm();
      },
      { deep: true }
    );
    return {
      ...toRefs(data),
      uploadImages,
      deleteImage,
      save,
    };
  },
  props: ["selectedHello"],
};
</script>

<style scoped lang="scss">
.hello-form {
  .image-container{
    padding-top: calc(var(--bs-gutter-x) * 0.5);
    padding-bottom: calc(var(--bs-gutter-x) * 0.5);
  }
  .image-delete {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #fff;
    border-radius: 50%;
    font-size: 14px;
    width: 21px;
    height: 19px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
  }
  .add-image {
    &:hover {
      cursor: pointer;
    }
    .action {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    input[type="file"] {
      display: none;
    }
  }
  .uploaded-image,
  .add-image {
    position: relative;
    height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 5px;
  }
  .uploaded-image {
    img {
      width: 100%;
      height: 100%;
    }
  }
  .form-control {
    padding: 10px;
  }
  .form-group {
    margin-bottom: 10px;
    label {
      margin-bottom: 5px;
    }
  }
  .modal-footer {
    button {
      width: 80px;
    }
  }
}
</style>
