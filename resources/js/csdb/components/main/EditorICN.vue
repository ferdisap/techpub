<script>
import { useTechpubStore } from '../../../techpub/techpubStore';
import ContinuousLoadingCircle from '../../loadingProgress/Continuousloadingcircle.vue';
import ContextMenu from '../subComponents/ContextMenu.vue';
import {submit, readEntity} from './EditorICNVue.js';
export default {
  data() {
    return {
      techpubStore: useTechpubStore(),
      isUpdate: false,
      route: {},
      showLoadingProgress: false,

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
  },
  mounted() {
    // this.ContextMenu.register(this.contextMenuId);
    // this.ContextMenu.toggle(false, this.contextMenuId);
  }
}
</script>

<template>
  <div class="editoricn px-3">
    <h1 class="text-center">File Upload</h1>
    <div>
      <label for="icn-filename" class="text-sm font-bold">Filename</label><br />
      <input type="text" id="icn-filename" name="filename"
        @change.prevent="filenameForInputUploadFile = $event.target.value" placeholder="filename without extension"
        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    </div>
    <div class="error text-sm text-red-600" v-html="techpubStore.error('filename')"></div>
    <div class="w-2/3 inline-block">
      <label for="icn-path" class="text-sm font-bold">Path</label><br />
      <input id="icn-path" name="path" placeholder="type the fullpath eg. csdb/n219/amm" value="csdb" type="text"
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

    <ContinuousLoadingCircle :show="showLoadingProgress"/>

    
    <button type="submit" name="button" class="button bg-blue-400 text-white hover:bg-blue-600">Upload</button>

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
  <!-- <div>
    <div>
      <h2>File Upload</h2>
      <a href="#" @click.prevent="switchEditor('XMLEditor')" class="text-sm underline text-blue-500">Switch to text
        editor</a>
    </div>
    <div>
      <label for="icn-filename" class="text-sm font-bold">Filename</label><br />
      <input type="text" id="icn-filename" name="filename" :value="filenameInputUploadFile"
        @change.prevent="filenameForInputUploadFile = $event.target.value" placeholder="filename without extension"
        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    </div>
    <div class="error text-sm text-red-600" v-html="techpubStore.error('filename')"></div>
    <div class="w-2/3 inline-block">
      <label for="icn-path" class="text-sm font-bold">Path</label><br />
      <input id="icn-path" name="path" :value="pathInputUploadFile"
        @change.prevent="pathForInputUploadFile = $event.target.value"
        placeholder="type the fullpath eg. csdb/n219/amm" value="csdb" type="text"
        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    </div>
    <div class="w-1/3 inline-block">
      <label for="icn-path" class="text-sm font-bold">Browse</label><br />
      <input type="file" id="entity" name="entity" @change="readEntity($event)"
        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
    </div>
    <div class="error text-sm text-red-600" v-html="techpubStore.error('path')"></div>
    <div class="error text-sm text-red-600" v-html="techpubStore.error('entity')"></div>
  </div> -->
</template>