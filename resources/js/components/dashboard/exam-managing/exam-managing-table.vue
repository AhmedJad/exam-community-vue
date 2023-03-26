<template>
  <div class="folder-container">
    <div class="buy-now">
      <button @click="actionClicked = !actionClicked" class="btn btn-danger btn-buy-now">
        <i class="bx bx-menu"></i> {{ $t("ACTIONS") }}
      </button>
      <div v-if="actionClicked">
        <button style="position: fixed; bottom: 103px;" @click="onAddClicked" data-bs-toggle="modal"
          data-bs-target="#modalFolder" type="button" class="btn btn-danger btn-buy-now">
          <i class='bx bx-add-to-queue'></i>
          {{ $t("FOLDER") }}
        </button>
        <button style="position: fixed; bottom: 160px;" @click="onExamClicked" data-bs-toggle="modal"
          data-bs-target="#modalExam" type="button" class="btn btn-danger btn-buy-now">
          <i class='bx bx-add-to-queue'></i>
          {{ $t("EXAM") }}
        </button>
        <button style="position: fixed; bottom: 217px;" v-if="parentFolder" @click="back" type="button"
          class="btn btn-danger btn-buy-now">
          <i :class="{ 'bx bx-left-arrow': $i18n.locale == 'en', 'bx bx-right-arrow': $i18n.locale == 'ar' }"></i>
          {{ $t("BACK") }}
        </button>
      </div>
    </div>
    <h4 class="fw-bold py-3 mb-4">
      <router-link to="/home" class="text-muted fw-light">{{ $t("DASHBOARD") }} /</router-link>
      {{ $t("Exam managing") }}
    </h4>
    <small class="text-light fw-semibold">
      <template v-if="folders.length">
        <i @click="folderExpanded = true; examExpanded = false" v-if="!folderExpanded"
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
                    <li>
                      <a href="" @click.prevent="onEditClicked(folder, index)" data-bs-toggle="modal"
                        data-bs-target="#modalFolder" type="button" class="dropdown-item">
                        {{ $t("EDIT") }}
                      </a>
                    </li>

                    <li>
                      <a href="" @click.prevent="onDeleteClicked(folder, index)" type="button" class="dropdown-item">
                        {{ $t("DELETE") }}
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
            :prevText="null" :nextText="null">
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
                <h5 class="card-title">{{ exam.title }}</h5>
                <div class="btn-group">
                  <button type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a href="" @click.prevent="onExamEditClicked(exam, index)" data-bs-toggle="modal"
                        data-bs-target="#modalExam" type="button" class="dropdown-item">
                        {{ $t("EDIT") }}
                      </a>
                    </li>
                    <li>
                      <a href="#" @click.prevent="onExamDeleteClicked(exam, index)" type="button" class="dropdown-item">
                        {{ $t("DELETE") }}
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <!--/ Icon Dropdown -->
              <p class="card-text">
              <div>
                <i class='bx bx-time'></i>
                {{ exam.start_date.replace("T"," ( ") }} )
              </div>
              <div>
                <i class='bx bx-time'></i>
                {{ exam.end_date.replace("T"," ( ") }} )
              </div>
              <div>
                <i class='bx bx-check'></i>
                <template v-if="new Date() < new Date(exam.start_date)">
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
            :prevText="null" :nextText="null">
          </paginate>
        </div>
      </template>
    </div>

  </div>
  <FolderForm :parent="parentFolder" @created="onCreated" @updated="onUpdated" :selectedFolder="selectedFolder" />
  <ExamForm :parent="parentFolder" @created="onExamCreated" @updated="onExamUpdated" :selectedExam="selectedExam" />
</template>
<script>
import folderClient from "../../../shared/http-clients/folder-client";
import examClient from "../../../shared/http-clients/exam-client";
import Paginate from "vuejs-paginate-next";
import exportFromJSON from "export-from-json";
import FolderForm from "./folder-form.vue";
import ExamForm from "./exam-form.vue";
import folderStore from "./folder-store";
import examStore from "./exam-store";
import { inject, provide, reactive, ref, toRefs, watch } from "vue-demi";
import { useI18n } from "vue-i18n";
export default {
  components: {
    Paginate,
    FolderForm,
    ExamForm,
  },
  setup() {
    const swal = inject("swal");
    const data = reactive({
      pageSize: 6,
      actionClicked: false,
      folderExpanded: true,
      examExpanded: false,
      examPageSize: 6,
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
    provide("folder_store", folderStore);
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
      data.page = 1;
      data.parentFolder = data.parentFolder.parent;
      getFolders();
      getExams();
    }
    function onAddClicked() {
      data.selectedFolder = null;
      //Make little delay to ensure that watcher that found in folder form component
      // catch selectedFolder input prop
      setTimeout(() => {
        folderStore.onFormShow = !folderStore.onFormShow;
      }, 1);
    }
    function onEditClicked(folder, index) {
      data.selectedFolder = folder;
      data.selectedFolderIndex = index;
      //Make little delay to ensure that watcher catch selectedFolder input prop
      //in folder form component
      setTimeout(() => {
        folderStore.onFormShow = !folderStore.onFormShow;
      }, 1);
    }
    function onDeleteClicked(folder, index) {
      data.selectedFolder = folder;
      data.selectedFolderIndex = index;
      swal
        .fire({
          title: t("Are you sure"),
          text: t("You will not be able to revert this"),
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: t("YES"),
          cancelButtonText: t("NO"),
        })
        .then((res) => {
          if (res.value) {
            deleteFolder();
          }
        });
    }
    function getFolders() {
      folderClient
        .getFolders(data.page, data.pageSize, data.text, data.parentFolder)
        .then((response) => {
          data.folders = response.data.data;
          data.pageCounts = Math.ceil(response.data.total / data.pageSize);
        })
        .catch((error) => {
          console.log(error.response);
        });
    }
    function onCreated(event) {
      data.page = 1;
      getFolders();
      data.folderExpanded = true;
      data.examExpanded = false;
    }
    function onUpdated(event) {
      data.selectedFolder = null;
      getFolders();
    }
    function deleteFolder() {
      folderClient
        .delete(data.selectedFolder.id)
        .then((response) => {
          swal({
            icon: "success",
            title: t("SUCCESS"),
            text: t("DELETED_SUCCESSFULLY"),
          });
          if (data.page > 1 && data.folders.length == 1) {
            data.page--;
          }
          getFolders();
          data.selectedFolder = null;
        })
        .catch((error) => { });
    }
    //////////Exam/////////
    function onExamClicked() {
      data.selectedExam = null;
      //Make little delay to ensure that watcher that found in folder form component
      // catch selectedFolder input prop
      setTimeout(() => {
        examStore.onFormShow = !examStore.onFormShow;
      }, 1);
    }
    function onExamEditClicked(folder, index) {
      data.selectedExam = folder;
      //Make little delay to ensure that watcher catch selectedFolder input prop
      //in folder form component
      setTimeout(() => {
        examStore.onFormShow = !examStore.onFormShow;
      }, 1);
    }
    function onExamDeleteClicked(folder, index) {
      data.selectedExam = folder;
      swal
        .fire({
          title: t("Are you sure"),
          text: t("You will not be able to revert this"),
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: t("YES"),
          cancelButtonText: t("NO"),
        })
        .then((res) => {
          if (res.value) {
            deleteExam();
          }
        });
    }
    function getExams() {
      examClient
        .getExams(data.examPage, data.examPageSize, data.examText, data.parentFolder)
        .then((response) => {
          data.exams = response.data.data;
          data.examPageCounts = Math.ceil(response.data.total / data.examPageSize);
        })
        .catch((error) => {
          console.log(error.response);
        });
    }
    function onExamCreated(event) {
      data.examPage = 1;
      getExams();
      data.folderExpanded = false;
      data.examExpanded = true;
    }
    function onExamUpdated(event) {
      data.selectedExam = null;
      getExams();
    }
    function deleteExam() {
      examClient
        .delete(data.selectedExam.id)
        .then((response) => {
          swal({
            icon: "success",
            title: t("SUCCESS"),
            text: t("DELETED_SUCCESSFULLY"),
          });
          if (data.examPage > 1 && data.exams.length == 1) {
            data.examPage--;
          }
          getExams();
          data.selectedExam = null;
        })
        .catch((error) => { });
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
      onAddClicked,
      onExamClicked,
      onEditClicked,
      onExamEditClicked,
      onDeleteClicked,
      onExamDeleteClicked,
      getFolders,
      getExams,
      onCreated,
      onExamCreated,
      onUpdated,
      onExamUpdated,
      deleteFolder,
      deleteExam,
      search,
      print,
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

  .buy-now {
    button {
      width: 120px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  }

  .folders {
    margin-bottom: 11px;
    display: inline-block;
    font-size: 13px;
  }

  .page-container {
    padding: 25px 0;
    display: flex;
    justify-content: center;
  }

  .actions {
    display: flex;
    justify-content: space-between;

    button.exam {
      margin: 0 5px;
    }
  }
}
</style>
