<template>
  <div class="folder-container">
    <div class="buy-now">
      <button @click="back" type="button" class="btn btn-danger btn-buy-now">
        <i :class="{ 'bx bx-left-arrow': $i18n.locale == 'en', 'bx bx-right-arrow': $i18n.locale == 'ar' }"></i>
        {{ $t("BACK") }}
      </button>
    </div>
    <h4 class="fw-bold py-3 mb-4">
      <router-link to="/home" class="text-muted fw-light">{{ $t("DASHBOARD") }} /</router-link>
      {{ $t("Exam solution") }}
    </h4>
    <small class="text-light fw-semibold">
      <template v-if="folders.length">
        <i @click="folderExpanded = true; examExpanded = false;" v-if="!folderExpanded"
          style="font-size:17px;position: relative;bottom: 1px;" class='bx bx-plus-circle'></i>
        <i @click="folderExpanded = false; examExpanded = true" v-if="folderExpanded"
          style="font-size:17px;position: relative;bottom: 1px;" class='bx bx-minus-circle'></i>
        {{ $t("FOLDERS") }}
      </template>
    </small>
    <div class="row mt-3">
      <template v-if="folderExpanded">
        <div v-for="(folder, index) in folders" class="col-md-6 col-lg-4">
          <div class="card mb-3">
            <div class="card-body">
              <div class="folder-header">
                <h5 class="card-title">{{ folder.name }}</h5>
                <div class="btn-group">
                  <button type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a @click.prevent="onFolderClicked(folder, index)" type="button" class="dropdown-item">
                        {{ $t("ENTER_FOLDER") }}
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <!--/ Icon Dropdown -->
              <p class="card-text">
                {{ folder.description }}
              </p>
            </div>
          </div>
        </div>
        <div class="mt-1 page-container">
          <paginate v-if="folders.length" v-model="page" :pageCount="pageCounts" :clickHandler="getFolders"
            :prevText="$t('PREV')" :nextText="$t('NEXT')">
          </paginate>
        </div>
      </template>
    </div>
    <small class="text-light fw-semibold">
      <template v-if="exams.length">
        <i @click="examExpanded = true; folderExpanded = false" v-if="!examExpanded"
          style="font-size:17px;position: relative;bottom: 1px;" class='bx bx-plus-circle'></i>
        <i @click="examExpanded = false; folderExpanded = true" v-if="examExpanded"
          style="font-size:17px;position: relative;bottom: 1px;" class='bx bx-minus-circle'></i>
        {{ $t("EXAMS") }}
      </template>
    </small>
    <div class="row mt-3">
      <template v-if="examExpanded">
        <div v-for="(exam, index) in exams" class="col-md-6 col-lg-4">
          <div class="card mb-3">
            <div class="card-body">
              <div class="exam-header">

                <h5 class="card-title">
                  {{ exam.title }}
                </h5>
                <div class="btn-group">
                  <button type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul
                    v-if="(new Date >= new Date(exam.start_date) && new Date() < new Date(exam.end_date)) || exam.exam_solutions"
                    class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a href="" @click.prevent="onExamClicked(exam)" data-bs-toggle="modal" data-bs-target="#modalExam"
                        type="button" class="dropdown-item">
                        {{ exam.exam_solutions ? $t("Exam revision") : $t("Solve Exam") }}
                      </a>
                    </li>
                  </ul>
                </div>

              </div>
              <!--/ Icon Dropdown -->
              <p class="card-text">
              <div>
                <i class='bx bx-time'></i>
                {{ exam.start_date.replace("T", ` ${$t("at")} `) }}
              </div>
              <div>
                <i class='bx bx-time'></i>
                {{ exam.end_date.replace("T", ` ${$t("at")} `) }}
              </div>
              <div>
                <i class='bx bx-check'></i>
                <template v-if="exam.exam_solutions">
                  {{ $t("solved") }} ( {{ exam.exam_solutions.result }} )
                </template>
                <template v-else-if="new Date() < new Date(exam.start_date)">
                  {{ $t("not_started") }}
                </template>
                <template v-else-if="new Date() >= new Date(exam.start_date)">
                  {{ $t("started") }}
                </template>
                <template v-else-if="new Date() < new Date(exam.start_date)">
                  {{ $t("continue") }}
                </template>
                <template v-else-if="new Date() >= new Date(exam.start_date)">
                  {{ $t("finished") }}
                </template>

              </div>
              </p>
            </div>
          </div>
        </div>
        <div class="mt-1 page-container">
          <paginate v-if="exams.length" v-model="examPage" :pageCount="examPageCounts" :clickHandler="getExams"
            :prevText="$t('PREV')" :nextText="$t('NEXT')">
          </paginate>
        </div>
      </template>
    </div>
  </div>
  <ExamForm :parent="parentFolder" @updated="onExamUpdated" :selectedExam="selectedExam" />
</template>
<script>
import examSolutionClient from "../../../shared/http-clients/exam-solution-client";
import Paginate from "vuejs-paginate-next";
import exportFromJSON from "export-from-json";
import ExamForm from "./exam-form.vue";
import examStore from "./exam-store";
import { inject, provide, reactive, ref, toRefs, watch } from "vue-demi";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";
import { useRouter } from "vue-router";
export default {
  components: {
    Paginate,
    ExamForm,
  },
  setup() {
    const swal = inject("swal");
    const route = useRoute();
    const router = useRouter();
    const data = reactive({
      pageSize: 6,
      examPageSize: 6,
      folderExpanded: true,
      examExpanded: false,
      actionClicked: false,
      page: 1,
      examPage: 1,
      folders: [],
      exams: [],
      text: "",
      examText: "",
      pageCounts: 0,
      examPageCounts: 0,
      timeout: null,
      selectedFolder: null,
      selectedExam: null,
      selectedFolderIndex: 0,
      parentFolder: null,
    });
    const toast = inject("toast");
    const { t, locale } = useI18n({ useScope: "global" });
    provide("exam_store", examStore);
    created();
    //Methods
    function onFolderClicked(folder) {
      data.page = 1;
      data.parentFolder = folder;
      getFolders();
      getExams();
    }
    function back() {
      if (!data.parentFolder) {
        router.push("/exam-solutions");
        return;
      }
      data.page = 1;
      data.parentFolder = data.parentFolder.parent;
      getFolders();
      getExams();
    }
    function getFolders() {
      examSolutionClient
        .getFolders(
          data.page,
          data.pageSize,
          data.text,
          data.parentFolder,
          route.params.userId
        )
        .then((response) => {
          data.folders = response.data.data;
          data.pageCounts = Math.ceil(response.data.total / data.pageSize);
        })
        .catch((error) => {
          console.log(error.response);
        });
    }
    //////////Exam/////////
    function onExamClicked(exam) {
      data.selectedExam = exam;
      //Make little delay to ensure that watcher that found in folder form component
      // catch selectedFolder input prop
      setTimeout(() => {
        examStore.onFormShow = !examStore.onFormShow;
      }, 1);
    }
    function getExams() {
      examSolutionClient
        .getExams(
          data.examPage,
          data.examPageSize,
          data.examText,
          data.parentFolder,
          route.params.userId
        )
        .then((response) => {
          data.exams = response.data.data;
          data.examPageCounts = Math.ceil(response.data.total / data.examPageSize);
        })
        .catch((error) => {
          console.log(error.response);
        });
    }
    function onExamUpdated(event) {
      data.selectedExam = null;
      getExams();
    }
    function search() {
      data.page = 1;
      // clear timeout variable
      clearTimeout(data.timeout);
      data.timeout = setTimeout(() => {
        getFolders();
      }, 500);
    }
    watch(
      () => {
        data.text;
      },
      (value) => {
        search();
      },
      { deep: true }
    );
    //Commons
    function created() {
      getFolders();
      getExams();
    }
    return {
      ...toRefs(data),
      back,
      onFolderClicked,
      onExamClicked,
      getFolders,
      getExams,
      onExamUpdated,
      search,
    };
  },
};
</script>

<style lang="scss">
.folder-container {
  .card-text {
    font-size: 12px;
    color: #b4bdc6 !important;
  }

  .page-item:first-child {
    display: none;
  }

  .page-item:last-child {
    display: none;
  }

  .folder-header,
  .exam-header {
    display: flex;
    justify-content: space-between;

    button {
      background: none;
      border: none;
      position: relative;
      bottom: 7px;
    }
  }

  .folders {
    margin-bottom: 11px;
    display: inline-block;
    font-size: 13px;
  }

  .page-container {
    display: flex;
    justify-content: center;
  }
}
</style>
