<script>
import { useTechpubStore } from '../../../techpub/techpubStore';
import ContinuousLoadingCircle from '../../loadingProgress/Continuousloadingcircle.vue';
import ContextMenu from '../subComponents/ContextMenu.vue';
import {submit, readEntity} from './EditorICNVue.js';
import { ref } from 'vue';
export default {
  setup(){
    const inputPath = ref(null);
    const inputFilename = ref(null);
    return {inputPath, inputFilename}
  },
  data() {
    return {
      techpubStore: useTechpubStore(),
      isUpdate: false,

      // helper
      pathForInputUploadFile: '',
      // pathForInputXMLFile: '',
      filenameForInputUploadFile: '',

      contextMenuId: 'cmEditorICNVue'
    }
  },
  components: { ContinuousLoadingCircle, ContextMenu },
  computed: {
  },
  methods: {
    submit: submit,
    readEntity: readEntity,
    refresh(){
      // define later if needed
      // const ext = path.extname(this.$route.params.filename);
      // if( ext === '.xml' || ext === '.pdf') return;
    },
  },
  mounted() {
    this.emitter.on('Editor-refresh', this.refresh);

    if(this.techpubStore.currentObjectModel.csdb) this.inputFilename.value = this.techpubStore.currentObjectModel.csdb.filename;
  }
}
</script>

<template>
  <div class="editoricn px-3">
    <form @submit.prevent="submit" enctype="multipart/form-data">
      <h1 class="text-center">File Upload</h1>
      <div>
        <label for="icn-filename" class="text-sm font-bold">Filename</label><br />
        <input type="text" id="icn-filename" name="filename"
          ref="inputFilename"
          class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
      </div>
      <div class="error text-sm text-red-600" v-html="techpubStore.error('filename')"></div>
      <div class="w-2/3 inline-block">
        <label for="icn-path" class="text-sm font-bold">Path</label><br />
        <input id="icn-path" name="path" ref="inputPath" placeholder="type the fullpath eg. CSDB/N219/AMM" value="CSDB" type="text"
          class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
      </div>
      <div class="w-1/3 inline-block">
        <label for="icn-path" class="text-sm font-bold">Browse</label><br />
        <input type="file" id="entity" name="entity" @change="readEntity($event)"
          class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
      </div>
      <div class="error text-sm text-red-600" v-html="techpubStore.error('path')"></div>
      <div class="error text-sm text-red-600" v-html="techpubStore.error('entity')"></div>
  
      <div class="italic text-sm mt-8 h-60">
        <span class="text-sm bg-yellow-200">this is for ICN meta file that provided soon</span>
      </div>
      <button type="submit" name="button" class="button text-sm bg-blue-400 text-white hover:bg-blue-600">Upload</button>
    </form>

    <ContinuousLoadingCircle/>    

    <ContextMenu :id="contextMenuId">
      <div @click.stop.prevent="$parent.editorComponent = 'EditorXML'"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">XML Editor</div>
      </div>
      <div @click.stop.prevent="$parent.editorComponent = 'EditorDML'"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">DML Editor</div>
      </div>
    </ContextMenu>
  </div>
</template>