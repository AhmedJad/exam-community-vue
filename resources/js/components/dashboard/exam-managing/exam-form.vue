<template>
  <div class="exam-managing-form">
    <form @submit.prevent="save">
      <!-- Modal -->
      <div class="modal fade" id="modalExam" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalCenterTitle">{{ $t("EXAM_FORM") }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-12 mb-3">
                  <label for="nameWithTitle" class="form-label">{{ $t("NAME") }}</label>
                  <input v-model="v$.title.$model" :class="{
                    'is-invalid': v$.title.$error,
                  }" type="text" id="nameWithTitle" class="form-control" :placeholder="$t('ENTER_NAME')" />
                  <div class="invalid-feedback">
                    <div v-for="error in v$.title.$errors" :key="error">
                      {{ $t("NAME") + " " + $t(error.$validator) }}
                    </div>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="nameWithTitle" class="form-label">{{
                    $t("START_DATE")
                  }}</label>
                  <input v-model="v$.start_date.$model" :class="{
                    'is-invalid': v$.start_date.$error,
                  }" type="datetime-local" id="nameWithTitle" class="form-control" />
                  <div class="invalid-feedback">
                    <div v-for="error in v$.start_date.$errors" :key="error">
                      {{ $t("START_DATE") + " " + $t(error.$validator) }}
                    </div>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="nameWithTitle" class="form-label">{{
                    $t("END_DATE")
                  }}</label>
                  <input v-model="v$.end_date.$model" :class="{
                    'is-invalid': v$.end_date.$error,
                  }" type="datetime-local" id="nameWithTitle" class="form-control" />
                  <div class="invalid-feedback">
                    <div v-for="error in v$.end_date.$errors" :key="error">
                      {{ $t("END_DATE") + " " + $t(error.$validator) }}
                    </div>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="nameWithTitle" class="form-label">{{
                    $t("SELECTIONS_NUMBER")
                  }}</label>
                  <input @blur="changeSelectionsSize" v-model="v$.selections_size.$model" :class="{
                    'is-invalid': v$.selections_size.$error,
                  }" type="number" id="nameWithTitle" class="form-control" />
                  <div class="invalid-feedback">
                    <div v-for="error in v$.selections_size.$errors" :key="error">
                      {{ $t("SELECTIONS_NUMBER") + " " + $t(error.$validator) }}
                    </div>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <div class="question-actions">
                    <button @click="addQuestion" type="button" class="btn btn-primary">
                      <i class="bx bx-plus-circle"></i>
                    </button>
                    <button :disabled="questions.length == 1" @click="removeQuestion" style="margin: 0 5px" type="button"
                      class="btn btn-primary">
                      <i class="bx bx-minus-circle"></i>
                    </button>
                  </div>
                </div>
                <template v-for="(question, index) in questions">
                  <div v-if="index + 1 == currentQuestionIndex">
                    <div class="col-12 mb-3">
                      <div class="d-flex justify-content-between">
                        <label for="exampleFormControlTextarea1" class="form-label">
                          {{ $t("QUESTION") }} ({{ currentQuestionIndex }})
                        </label>
                      </div>
                      <textarea v-model="question.context" class="form-control" id="exampleFormControlTextarea1"
                        rows="3"></textarea>
                    </div>
                    <div v-for="(selection, i) in question.selections" class="col-md-12 mb-3">
                      <div class="input-group">
                        <div class="input-group-text">
                          <input v-model="selection.selected" :name="`selection-${index}-${i}`" :value="true"
                            class="form-check-input mt-0" type="checkbox"
                            aria-label="Checkbox for following text input" />
                        </div>
                        <input v-model="selection.context" type="text" class="form-control"
                          aria-label="Text input with checkbox" />
                      </div>
                    </div>
                  </div>
                </template>
                <div class="col-12 my-3">
                  <paginate v-model="currentQuestionIndex" :pageCount="questions.length" :prevText="$t('PREV')"
                    :nextText="$t('NEXT')">
                  </paginate>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
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
import examClient from "../../../shared/http-clients/exam-client";
import { computed, inject, reactive, toRefs, watch, onMounted } from "vue-demi";
import { useI18n } from "vue-i18n";
import Paginate from "vuejs-paginate-next";

export default {
  components: { Paginate },
  setup(props, context) {
    const { t, locale } = useI18n({ useScope: "global" });
    const exam_store = inject("exam_store");
    const toast = inject("toast");
    const swal = inject("swal");
    const data = reactive({
      uploadedImage: null,
      previewImage: "",
      currentQuestionIndex: 1,
    });
    const form = reactive({
      title: "",
      start_date: "",
      end_date: "",
      exercise: false,
      selections_size: 4,
      questions: [],
    });
    const rules = {
      title: { required },
      start_date: {
        required,
        beforeEndDate: (value) => {
          return new Date(form.start_date) < new Date(form.end_date);
        },
        afterOrEqualNow: (value) => {
          return new Date(form.start_date) >= new Date();
        },
      },
      end_date: {
        required, afterStartDate: (value) => {
          return new Date(form.end_date) > new Date(form.start_date);
        },
      },
      selections_size: { required },
    };
    onMounted(() => {
      form.questions = getQuestion();
    });
    const v$ = useVuelidate(rules, form);
    //Methods
    function changeSelectionsSize() {
      form.selections_size = form.selections_size < 2 ? 2 : form.selections_size;
      form.selections_size = form.selections_size > 6 ? 6 : form.selections_size;
      let currentSelectionSize = form.questions[0].selections.length;
      if (form.selections_size < currentSelectionSize) {
        form.questions.forEach((question) => {
          for (let i = 0; i < currentSelectionSize - form.selections_size; i++) {
            question.selections.pop();
          }
        });
      } else if (form.selections_size > currentSelectionSize) {
        form.questions.forEach((question) => {
          for (let i = 0; i < form.selections_size - currentSelectionSize; i++) {
            question.selections.push({
              selection: true,
              context: locale == "ar" ? "اختيار" : "selection",
            });
          }
        });
      }
    }

    function addQuestion() {
      form.questions.push(getQuestion());
      data.currentQuestionIndex = form.questions.length;
    }

    function removeQuestion() {
      form.questions.splice(data.currentQuestionIndex - 1, 1);
      if (data.currentQuestionIndex != 1) {
        data.currentQuestionIndex--;
      }
    }
    function save() {
      if (v$.value.$invalid) {
        v$.value.$touch();
        return;
      }
      form.questions.forEach((question) => {
        question.context = question.context
          ? question.context
          : locale == "ar"
            ? "سؤال"
            : "Question";
        question.selections.forEach((selection) => {
          selection.context = selection.context
            ? selection.context
            : locale == "ar"
              ? "اختيار"
              : "Selection";
        });
      });
      if (!props.selectedExam) {
        store();
      } else {
        update();
      }
    }
    //Commons
    function getQuestion() {
      let question = {
        context: locale == "ar" ? "سؤال" : "Question",
        selections: [],
      };
      for (let i = 0; i < form.selections_size; i++) {
        question.selections.push({
          context: locale == "ar" ? "اختيار" : "selection",
          selected: i == 0 ? true : false,
        });
      }
      return question;
    }
    function store() {
      examClient
        .store(getForm())
        .then((response) => {
          swal({
            icon: "success",
            title: t("SUCCESS"),
            text: t("CREATED_SUCCESSFULLY"),
          });
          context.emit("created");
          $("#modalExam").modal("hide");
        })
        .catch((error) => { });
    }
    function update() {
      examClient
        .update(getForm())
        .then((response) => {
          swal({
            icon: "success",
            title: t("SUCCESS"),
            text: t("UPDATED_SUCCESSFULLY"),
          });
          context.emit("updated");
          $("#modalExam").modal("hide");
        })
        .catch((error) => { });
    }
    function getForm() {
      return {
        id: props.selectedExam ? props.selectedExam.id : null,
        title: form.title,
        start_date: form.start_date,
        end_date: form.end_date,
        selections_size: form.selections_size,
        folder_id: props.parent ? props.parent.id : null,
        questions: form.questions,
      };
    }
    function setForm() {
      data.currentQuestionIndex = 1;
      v$.value.$reset();
      form.title = props.selectedExam ? props.selectedExam.title : "";
      form.start_date = props.selectedExam ? props.selectedExam.start_date : "";
      form.end_date = props.selectedExam ? props.selectedExam.end_date : "";
      form.selections_size = props.selectedExam ? props.selectedExam.selections_size : 4;
      form.questions = props.selectedExam
        ? [...props.selectedExam.questions]
        : [getQuestion()];
    }
    //Watchers
    watch(
      () => {
        exam_store.onFormShow;
      },
      (value) => {
        setForm();
      },
      { deep: true }
    );
    return {
      ...toRefs(data),
      ...toRefs(form),
      changeSelectionsSize,
      addQuestion,
      removeQuestion,
      v$,
      save,
    };
  },
  props: ["selectedExam", "parent"],
};
</script>

<style lang="scss">
.exam-managing-form {
  .page-item:first-child {
    display: none;
  }
  .question-actions{
    position: relative;
    top: 29px;
  }
  .page-item:last-child {
    display: none;
  }
  .exam-checks {
    .form-check {
      position: relative;
      display: inline-block !important;
      top: 20px;
    }
  }
}

@media (max-width:767px) {
  .exam-managing-form .question-actions {
    position: unset !important;
    top: unset !important;
  }
}
</style>
