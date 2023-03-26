<template>
  <Loader />
  <router-view />
</template>
<script>
import authClient from "./shared/http-clients/auth-client";
import TokenUtil from "./shared/utils/token-util";
import Loader from "./shared/components/loader.vue";
import LangUtil from "./shared/utils/lang-util";
import { inject } from "vue-demi";
export default {
  components: {
    Loader,
  },
  setup() {
    const lang = LangUtil.setup();
    const store = inject("store");
    onCreated();
    //Commons
    function onCreated() {
      lang.changeLang(LangUtil.get());
      if (TokenUtil.get()) {
        authClient
          .getCurrentUser()
          .then((response) => {
            store.currentUser = response.data;
          })  
          .catch((error) => {
            console.log("err", error.response);
          });
      }
    }
  },
};
</script>
<style lang="scss">
</style>