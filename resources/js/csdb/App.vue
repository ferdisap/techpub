<script>
import Topbar from '../techpub/components/Topbar.vue';
import Flash from '../techpub/components/Flash.vue';
import Aside from './components/Aside.vue';
import Main from './components/Main.vue';
import { useTechpubStore } from '../techpub/techpubStore';
import {info, alert} from './AppVue'


export default {
  data() {
    return {
      techpubStore: useTechpubStore(),
      messages: undefined,
      isSuccess: true,

      showMessages: false,
      errors: undefined, // {}
      message: undefined, // ""

      infoData: {}, // requiredAttribute: 'show:Boolean', 'message:String', 'name:String'
      alertData: {}
    }
  },
  components: { Topbar, Flash, Aside, Main },
  methods: {
    info: info,
    alert: alert,
        /**
     * DEPRECATED
     * diganti langsung pakai this.$router.push
    */
    gotoExplorer(filename, viewType = undefined){
      this.$router.push({
        name: 'Explorer',
        params: {
          filename: filename,
          viewType:  viewType||this.$route.params.viewType||'html',
        }
      });
    },   
  },
  mounted(){
    // window.techpubStore = this.techpubStore;
    window.app = this;
  }
}
</script>

<style>
body {
  background-color: rgb(192, 209, 220);
  height: 100vh;
}
</style>

<template>
  <Flash />

  <div class="topbar w-full 2xl:h-[5%] xl:h-[6%] lg:h-[5%] md:h-[5%] sm:h-[5%]">
    <Topbar />
  </div>

  <div class="content w-full relative flex mx-auto 2xl:h-[95%] xl:h-[94%] lg:h-[95%] md:h-[95%] sm:h-[95%]">
    <div class="aside relative h-full 2xl:w-[4%] xl:w-[5%] lg:w-[6%] md:w-[8%] sm:w-[10%] w-[16%]">
      <Aside />
    </div>
    <div class="main relative h-full 2xl:w-[96%] xl:w-[95%] lg:w-[94%] md:w-[92%] sm:w-[90%] w-[84%]">
      <Main />
    </div>
  </div>

  <!-- loading sign -->
  <div v-if="techpubStore.showLoadingBar" class="fixed top-0 left-0 h-[100vh] w-[100%] z-50">
    <div style="
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    background:rgba(0,0,0,0.5);
    ">
      <div class="loading_buffer"></div>
    </div>
  </div>

  <!-- 
    alert modal 
  -->
  <div v-if="alertData.show" class="alert fixed top-0 left-0 h-[100vh] w-[100%] z-50 bg-[rgba(255,0,0,00)] font-mono">
    <div style="
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    background:rgba(0,0,0,0.5);
    ">
      <div
        class="w-1/2 h-1/2 bg-white dark:bg-neutral-900 opacity-100 absolute top-1/4 left-1/4 rounded-xl border-[15px] border-red-500 border-dashed">
        <h1 class="text-center mt-3 mb-3">ALERT !</h1>
        <hr class="border-2" />
        <div class="max-h-[65%] overflow-auto px-10" message v-html="alertData.message"></div>
        <div class="w-full text-center bottom-3 absolute">
          <button autofocus class="button-danger shadow-md" alert-not-ok @click="alertData.button(0)">X</button>
          <button class="button-safe shadow-md" alert-ok @click="alertData.button(1)">O</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 
    info modal 
  -->
  <div v-if="infoData.show" id="info" class="info fixed top-0 left-0 h-[100vh] w-[100%] z-50 bg-[rgba(255,0,0,00)] font-mono">
    <div style="
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    background:rgba(0,0,0,0.5);
    ">
      <div
        class="w-1/2 h-1/2 bg-white dark:bg-neutral-900 opacity-100 absolute top-1/4 left-1/4 rounded-xl border-[15px] border-cyan-200">
        <h1 class="text-center mt-3 mb-3">INFORMATION</h1>
        <hr class="border-2" />
        <div class="max-h-[65%] overflow-auto px-10" v-html="infoData.message"></div>
        <div class="w-full text-center bottom-3 absolute">
          <button autofocus class="button-danger shadow-md" @click="infoData.show = false">X</button>
        </div>
      </div>
    </div>
  </div>
</template>