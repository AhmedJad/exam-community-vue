<template>
  <div class="exam-managing-form">
    <form @submit.prevent="save">
      <!-- Modal -->
      <div class="modal fade" id="modalFolder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalCenterTitle">{{ $t("Folder Form") }}</h5>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              ></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col mb-3">
                  <label for="nameWithTitle" class="form-label">{{ $t("NAME") }}</label>
                  <input
                    v-model="v$.name.$model"
                    :class="{
                      'is-invalid': v$.name.$error,
                    }"
                    type="text"
                    id="nameWithTitle"
                    class="form-control"
                    :placeholder="$t('ENTER_NAME')"
                  />
                  <div class="invalid-feedback">
                    <div v-for="error in v$.name.$errors" :key="error">
                      {{ $t("NAME") + " " + $t(error.$validator) }}
                    </div>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <div>
                    <label for="exampleFormControlTextarea1" class="form-label">{{
                      $t("DESCRIPTION")
                    }}</label>
                    <textarea
                      v-model="v$.description.$model"
                      :class="{
                        'is-invalid': v$.description.$error,
                      }"
                      class="form-control"
                      id="exampleFormControlTextarea1"
                      rows="3"
                    ></textarea>
                    <div class="invalid-feedback">
                      <div v-for="error in v$.description.$errors" :key="error">
                        {{ $t("DESCRIPTION") + " " + $t(error.$validator) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-outline-secondary"
                data-bs-dismiss="modal"
              >
                {{ $t("CLOSE") }}
              </button>
              <button class="btn btn-primary">
                {{ $t("SAVE_CHANGES") }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import useVuelidate from "@vuelidate/core";
import { required, helpers } from "@vuelidate/validators";
import folderClient from "../../../shared/http-clients/folder-client";
import { computed, inject, reactive, toRefs, watch } from "vue-demi";
import { useI18n } from "vue-i18n";
export default {
  setup(props, context) {
    const { t, locale } = useI18n({ useScope: "global" });
    const folder_store = inject("folder_store");
    const toast = inject("toast");
    const swal = inject("swal");
    const data = reactive({
      uploadedImage: null,
      previewImage: "",
    });
    const form = reactive({
      name: "",
      description: "",
    });
    const rules = {
      name: { required },
      description: { required },
    };
    const v$ = useVuelidate(rules, form);
    //Methods
    function save() {
      if (v$.value.$invalid) {
        v$.value.$touch();
        return;
      }
      if (!props.selectedFolder) {
        store();
      } else {
        update();
      }
    }
    //Commons
    function store() {
      folderClient
        .store(getForm())
        .then((response) => {
          swal({
            icon: "success",
            title: t("SUCCESS"),
            text: t("CREATED_SUCCESSFULLY"),
          });
          context.emit("created", response.data);
          $("#modalFolder").modal("hide");
        })
        .catch((error) => {});
    }
    function update() {
      folderClient
        .update(getForm())
        .then((response) => {
          swal({
            icon: "success",
            title: t("SUCCESS"),
            text: t("UPDATED_SUCCESSFULLY"),
          });
          context.emit("updated", response.data);
          $("#modalFolder").modal("hide");
        })
        .catch((error) => {});
    }
    function getForm() {
      return {
        id: props.selectedFolder ? props.selectedFolder.id : null,
        name: form.name,
        parent_id: props.parent ? props.parent.id : null,
        description: form.description,
      };
    }
    function setForm() {
      v$.value.$reset();
      form.name = props.selectedFolder ? props.selectedFolder.name : "";
      form.description = props.selectedFolder ? props.selectedFolder.description : "";
    }
    //Watchers
    watch(
      () => {
        folder_store.onFormShow;
      },
      (value) => {
        setForm();
      },
      { deep: true }
    );
    return {
      ...toRefs(data),
      ...toRefs(form),
      v$,
      save,
    };
  },
  props: ["selectedFolder", "parent"],
};
</script>

<style scoped lang="scss">
.exam-managing-form {
}
</style>
