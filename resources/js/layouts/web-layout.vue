<template>
  <div>
    <div class="simple-nav">
    </div>
    <router-view />
  </div>
</template>
<script>
import TokenUtil from "../shared/utils/token-util";
export default {
  components: {
  },
  data() {
    return {
      token: TokenUtil.get(),
    };
  },
};
</script>
<style scoped>
.simple-nav {
  padding: 10px;
  display: flex;
  justify-content: flex-end;
}
</style>