<template>
  <div class="folder-container">
    <h4 class="fw-bold py-3 mb-4">
      <router-link to="/home" class="text-muted fw-light"
        >{{ $t("DASHBOARD") }} /</router-link
      >
      {{ $t("Users") }}
    </h4>
    <div class="row mt-3">
      <div class="col-md-4" v-for="(user, index) in users">
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-4">
              <img
                class="card-img card-img-left"
                src="/assets/img/elements/12.jpg"
                alt="Card image"
              />
            </div>
            <div class="col-md-8">
              <div class="card-body">
                <h5 class="card-title">{{ user.name }}</h5>
                <p class="card-text">
                  {{ user.email }}
                </p>
                <div class="btn-group" role="group" aria-label="Basic example">
                  <button
                    @click="onUserClicked(user, index)"
                    type="button"
                    class="btn btn-primary"
                  >
                    {{ $t("ENTER_USER") }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-1 page-container">
      <paginate
        v-if="users.length"
        v-model="page"
        :pageCount="pageCounts"
        :clickHandler="getUsers"
        :prevText="$t('PREV')"
        :nextText="$t('NEXT')"
      >
      </paginate>
    </div>
  </div>
</template>
<script>
import userClient from "../../../shared/http-clients/exam-solution-client";
import Paginate from "vuejs-paginate-next";
import exportFromJSON from "export-from-json";
import { inject, provide, reactive, ref, toRefs, watch } from "vue-demi";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
export default {
  components: {
    Paginate,
  },
  setup() {
    const swal = inject("swal");
    const data = reactive({
      pageSize: 6,
      page: 1,
      users: [],
      text: "",
      pageCounts: 0,
      timeout: null,
      selectedFolder: null,
      selectedFolderIndex: 0,
      parentFolder: null,
    });
    const toast = inject("toast");
    const { t, locale } = useI18n({ useScope: "global" });
    const router = useRouter();
    created();
    //Methods
    function onUserClicked(user) {
      router.push(`/user-exam/${user.id}`);
    }
    function getUsers() {
      userClient
        .getUsers(data.page, data.pageSize, data.text)
        .then((response) => {
          data.users = response.data.data;
          data.pageCounts = Math.ceil(response.data.total / data.pageSize);
        })
        .catch((error) => {
          console.log(error.response);
        });
    }
    function search() {
      data.page = 1;
      // clear timeout variable
      clearTimeout(data.timeout);
      data.timeout = setTimeout(() => {
        getUsers();
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
      getUsers();
    }
    return {
      ...toRefs(data),
      onUserClicked,
      getUsers,
      search,
    };
  },
};
</script>

<style lang="scss">
.folder-container {
  .page-item:first-child{
    display: none;
  }
  .page-item:last-child{
    display: none;
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
