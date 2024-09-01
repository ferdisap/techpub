<script>
import { useTechpubStore } from '../../../techpub/techpubStore';
import XMLEditor from '../../XMLEditor';
import ContinuousLoadingCircle from '../../loadingProgress/Continuousloadingcircle.vue';
import { setUpdate, setCreate, switchEditor, readEntity, submit, submitUploadFile, submitCreateXml, submitUpdateXml } from './EditorXMLVue.js';
import { copy } from '../../helper';
import ContextMenu from '../subComponents/ContextMenu.vue';
import { ref } from 'vue';
import path from 'path';
export default {
  inject:['getTextReadFromReadFile'],
  setup(){
    const inputPath = ref(null);
    return {inputPath}
  },
  data() {
    return {
      techpubStore: useTechpubStore(),
      isUpdate: false,
      isFile: false,
      route: {},
      XMLEditor: new XMLEditor('xml-editor-container'),
      contextMenuId: 'cmEditorXMLVue'
    }
  },
  components: { ContinuousLoadingCircle, ContextMenu },
  computed: {
  },
  methods: {
    setUpdate: setUpdate,
    setCreate: setCreate,
    submit: submit,
    submitCreateXml: submitCreateXml,
    submitUpdateXml: submitUpdateXml,
    copy: copy,
    switchTo(){
      if(this.isUpdate) this.XMLEditor.changeText('');
      else this.XMLEditor.fetchRaw();
      this.isUpdate = !this.isUpdate;
    },
    refresh() {
      if(path.extname(this.$route.params.filename) !== '.xml') return;
      if(this.$route.params.filename) this.setUpdate(this.$route.params.filename);
      else if(this.getTextReadFromReadFile()) this.XMLEditor.changeText(this.getTextReadFromReadFile());

      // set path
      if(this.techpubStore.currentObjectModel.csdb) this.inputPath.value = this.techpubStore.currentObjectModel.csdb.path;
    },
  },
  mounted() {
    this.XMLEditor.attachEditor();
    this.refresh();
    
    this.emitter.on('EditorXML-refresh', this.refresh);
  },
}
</script>
<style>
#xml-editor-container > div {
  height: 350px;
}
</style>
<template>
  <div class="editorxml px-3 relative h-full">
    <h1 class="text-blue-500 w-full text-center">Editor</h1>

    <form @submit.prevent="submit($event)">
      <input v-if="isUpdate" name="filename" type="text" class="hidden" :value="$route.params.filename"/>
      <div>
        <div class="mb-1">
          <label for="object-path" class="text-sm font-bold mr-2">Path:</label>
          <input id="object-path" name="path" ref="inputPath" placeholder="type the fullpath eg. CSDB/N219/AMM"
            type="text" class="py-0 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        </div>
        <div :id="XMLEditor.id" class="text-xl mb-2"></div>
        <div class="error text-sm text-red-600" v-html="techpubStore.error('xmleditor')"></div>
      </div>
      <button type="submit" name="button" 
        :class="['button text-white text-sm', !isUpdate ? 'bg-green-400 hover:bg-green-600' : 'bg-violet-400 hover:bg-violet-600']">{{ !isUpdate ? 'Create' : 'Update' }}</button>
    </form>
    <ContinuousLoadingCircle/>

    <ContextMenu :id="contextMenuId">
      <div @click.stop.prevent="$parent.editorComponent = 'EditorDML'"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Create DML</div>
      </div>
      <div @click.stop.prevent="$parent.editorComponent = 'EditorICN'"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Upload ICN</div>
      </div>
      <hr class="border border-gray-300 block mt-1 my-1 border-solid" />
      <div @click.stop.prevent="switchTo"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to {{ isUpdate ? 'create' : 'update' }}</div>
      </div>
      <div @click.stop.prevent="copy(null, '')"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Copy</div>
      </div>
    </ContextMenu>
  </div>
</template>