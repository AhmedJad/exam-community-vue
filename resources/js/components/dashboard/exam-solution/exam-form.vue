<template>
  <div class="exam-solution-form">
    <form @submit.prevent="solve">
      <!-- Modal -->
      <div class="modal fade" id="modalExam" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalCenterTitle">{{ $t("EXAM") }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <template v-for="(question, index) in questions">
                  <div v-if="index + 1 == currentQuestionIndex" class="row">
                    <div class="col-12 mb-3">
                      <div class="d-flex justify-content-between">
                        <label for="exampleFormControlTextarea1" class="form-label">
                          {{ $t("QUESTION") }} ({{ currentQuestionIndex }})
                        </label>
                      </div>
                      <textarea readonly v-model="question.context" class="form-control" id="exampleFormControlTextarea1"
                        rows="3"></textarea>
                    </div>
                    <div v-for="(selection, i) in question.selections" class="col-md-12 mb-3">
                      <div class="input-group">
                        <div class="input-group-text" :class="{
                          success: examSolutions.length && isSelectionCorrect(index, i),
                          wrong: examSolutions.length && !isSelectionCorrect(index, i) && isSelectedSelection(index, i)
                        }">
                          <input v-if="examSolutions.length" readonly :name="`selection-${index}-${i}`"
                            :checked="isSelectedSelection(index, i)" class="form-check-input mt-0" type="checkbox"
                            aria-label="Checkbox for following text input" />
                          <input v-else v-model="selection.selected" :name="`selection-${index}-${i}`" :value="true"
                            class="form-check-input mt-0" type="checkbox"
                            aria-label="Checkbox for following text input" />
                        </div>
                        <input readonly v-model="selection.context" type="text" class="form-control"
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
              <button v-if="selectedExam && !selectedExam.exam_solutions" class="btn btn-primary">
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
import examSolutionClient from "../../../shared/http-clients/exam-solution-client";
import { inject, reactive, toRefs, watch, onMounted } from "vue-demi";
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
      currentQuestionIndex: 1,
      examSolutions: []
    });
    const form = reactive({
      questions: [],
    });
    //Methods
    function solve() {
      examSolutionClient
        .solve(getForm())
        .then((response) => {
          swal({
            icon: "success",
            title: t("SUCCESS"),
            text: t("SOLVED_SUCCESSFULLY_AND_RESULT", { result: response.data.result }),
          });
          context.emit("updated");
          $("#modalExam").modal("hide");
        })
        .catch((error) => { });
    }
    //Commons
    function getForm() {
      return {
        exam_id: props.selectedExam.id,
        solutions: getSolutions(),
      };
    }
    function getSolutions() {
      let solutions = [];
      form.questions.forEach((question, questionIndex) => {
        let solution = { questionIndex: questionIndex, selectedSelectionIndex: 0 };
        question.selections.forEach((selection, selectionIndex) => {
          if (selection.selected == true) {
            solution.selectedSelectionIndex = selectionIndex;
          }
        });
        solutions.push(solution);
      });
      return solutions;
    }
    function isSelectedSelection(questionIndex, selectionIndex) {
      let check = false;
      data.examSolutions.forEach(solution => {
        if (solution.questionIndex == questionIndex && solution.selectedSelectionIndex == selectionIndex) {
          check = true;
          return;
        }
      })
      return check;
    }
    function isSelectionCorrect(questionIndex, selectionIndex) {
      let check = false;
      data.examSolutions.forEach(solution => {
        if (solution.questionIndex == questionIndex && solution.correctSelectionIndex == selectionIndex) {
          check = true;
          return;
        }
      })
      return check;
    }

    function setForm() {
      data.examSolutions = props.selectedExam.exam_solutions ? props.selectedExam.exam_solutions.solutions : [];
      data.currentQuestionIndex = 1;
      form.questions = props.selectedExam.questions;
      if (!props.selectedExam.exam_solutions) {
        form.questions.forEach(question => {
          question.selections.forEach(selection => {
            selection.selected = false;
          })
        })
      }
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
      isSelectedSelection,
      isSelectionCorrect,
      solve,
    };
  },
  props: ["selectedExam"],
};
</script>

<style lang="scss">
.exam-solution-form {
  .form-control {
    background: none !important;
  }

  .success {
    border: 1px solid #28a745 !important;
  }

  .wrong {
    border: 1px solid #dc3545 !important;
  }

  .page-item:first-child {
    display: none;
  }

  .page-item:last-child {
    display: none;
  }

  .exam-checks {
    .form-check {
      position: relative;
      margin: 0 4px;
      display: inline-block !important;
      top: 20px;
    }
  }
}
</style>
