<script>
import { useTechpubStore } from '../../../techpub/techpubStore';
import path from 'path';
import ContinuousLoadingCircle from '../../loadingProgress/ContinuousLoadingCircle.vue';
import { refresh, renderFromBlob, render, blobRequestTransformed, switchView } from './PreviewVue.js';
import ContextMenu from '../subComponents/ContextMenu.vue';
import Comment from './Comment.vue';

export default {
  data() {
    return {
      techpubStore: useTechpubStore(),
      pathHelper: path,
      inIframe: undefined,
      mime: undefined, // ini bisa PDF, HTML, IMG, VIDEO, mungkin FLASH, etc
      src: undefined,

      contextMenuId: 'cmPreviewVue',
    }
  },
  computed:{
    whatNextView(){
      return (this.$route.params.viewType === 'html') ? 'pdf' : 'html';
    }
  },
  components: { ContinuousLoadingCircle, ContextMenu, Comment },
  methods: {
    render: render,
    renderFromBlob, renderFromBlob,
    /**
     * aat ini belum dipakai karena halaman untuk ietm(html) belum difungsikan
     */
    switchView: switchView,
    /**
     * nanti ini diganti oleh worker
     * kalo worker cuma untuk fetch saja, tidak perlu pakai worker, kecuali ada proses pengolahan data response nya. Tapi karna blobURL yang dibuat di worker tidak bisa ditampilkan di window maka worker tidak perlu
    */
    blobRequestTransformed: blobRequestTransformed,
    refresh: refresh,
  },
  mounted() {
    this.render(this.$route.params.filename, this.$route.params.viewType);

    // dari Listtree via Explorer/Management data data berisi path doang,
    this.emitter.on('Preview-refresh', this.refresh)

    // this.ContextMenu.register(this.contextMenuId);
    // this.ContextMenu.toggle(false, this.contextMenuId);
  }
}
</script>
<template>
  <div class="preview h-[93%] w-[98%] relative">
    <div class="h-[5%] flex mb-3">
      <h1 class="text-blue-500 w-full text-center">Preview</h1>
    </div>
    <div class="flex justify-center w-full px-3 h-[95%]">
      <!-- untuk HTML dan PDF-->
      <div v-if="inIframe" id="datamodule-container" class="w-full h-full">
        <iframe id="datamodule-frame" class="w-full h-full" :src="src" loading="lazy" referrerpolicy="same-origin"/>
      </div>
      <!-- untuk non HTML dan non PDF-->
      <div v-else id="icn-container">
        <embed class="w-full h-full" :src="src" :type="mime" />
      </div>
    </div>
    
    <Comment :csdbFilename="$route.params.filename"/>

    <ContinuousLoadingCircle/>
    <ContextMenu :id="contextMenuId">
      <div @click.stop.prevent="copy()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Copy</div>
      </div>
      <div class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <a class="text-sm" :href="src" target="_blank">Open in new tab</a>
      </div>
      <hr class="border border-gray-300 block mt-1 my-1 border-solid"/>
      <div @click.stop.prevent="switchView(whatNextView)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to {{ whatNextView }} </div>
      </div>
    </ContextMenu>
  </div>
</template>