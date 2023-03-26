<template>
  <div class="lang-container">
    <a href="" v-if="$i18n.locale == 'ar'" @click.prevent="changeLang('en')">
       <span style="top:1px;position: relative;">English</span>
    </a>
    <a href="" v-if="$i18n.locale == 'en'" @click.prevent="changeLang('ar')">
     عربى
    </a>
  </div>
</template>
<script>
import LangUtil from "../utils/lang-util";
export default {
  setup() {
    const lang = LangUtil.setup();
    function changeLang(locale) {
      lang.changeLang(locale);
    }
    return { changeLang };
  },
};
</script>
<style lang="scss" scoped>
.lang-container {
  img {
    width: 20px;
    height: 15px;
  }
  a{
    text-decoration: none;
    color:#212529;
  }
}
</style>
