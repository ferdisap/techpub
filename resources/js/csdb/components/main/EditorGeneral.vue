<script>
import axios from 'axios';
import { useTechpubStore } from '../../../techpub/techpubStore';
import XMLEditor from '../../XMLEditor';
import ContinuousLoadingCircle from '../../loadingProgress/Continuousloadingcircle.vue';
import {setUpdate, setCreate, switchEditor, readEntity, submit, submitUploadFile, submitCreateXml, submitUpdateXml} from './EditorGeneralVue';
// import EditorDML from './EditorDML.vue';
export default {
  data(){
    return {
      techpubStore: useTechpubStore(),
      isUpdate: false,
      isFile: false,
      route: {},
      XMLEditor: new XMLEditor('xml-editor-container'),
      showLoadingProgress: false,

      // helper
      pathForInputUploadFile: '',
      pathForInputXMLFile: '',
      filenameForInputUploadFile: '',
    }
  },
  components:{ContinuousLoadingCircle},
  computed:{
    pathInputUploadFile(){
      return this.isUpdate && this.isFile ? (this.techpubStore.currentObjectModel.csdb ? this.techpubStore.currentObjectModel.csdb.path : this.pathForInputUploadFile) : this.pathForInputUploadFile;
    },
    pathInputXMLFile(){
      return this.isUpdate ? (this.techpubStore.currentObjectModel.csdb ? this.techpubStore.currentObjectModel.csdb.path : '') : '';
    },
    filenameInputUploadFile(){
      return this.isUpdate && this.isFile ? (this.techpubStore.currentObjectModel.csdb ? this.techpubStore.currentObjectModel.csdb.filename : this.filenameForInputUploadFile) : this.filenameForInputUploadFile;
    },
    // isDML(){
    //   return (this.$route.params.filename && this.$route.params.filename.slice(0,3) === 'DML');
    // }
  },
  methods: {
    setUpdate: setUpdate,
    setCreate: setCreate,
    switchEditor: switchEditor,
    readEntity: readEntity,
    submit: submit,
    submitUploadFile: submitUploadFile,
    submitCreateXml: submitCreateXml,
    submitUpdateXml: submitUpdateXml,
  },
  mounted(){
    // window.XMLEditor = XMLEditor;
    // window.ed = this.XMLEditor;
    this.XMLEditor.attachEditor()
    if(this.$route.params.filename) this.setUpdate(this.$route.params.filename);
  }
}
</script>
<style>
#xml-editor-container > div{
  height: 350px;
}
</style>
<template>
  <div class="editor px-3 relative">

    <div class="h-[5%] mb-3">
      <h1 class="text-blue-500 w-full text-center">Editor</h1>
      <a href="#" @click.prevent="isUpdate? setCreate() : setUpdate($route.params.filename)" class="block text-center text-sm underline text-blue-600">Switch to {{ isUpdate ? 'Create' : 'Update' }}</a>
    </div>

    <form @submit.prevent="submit($event)">
      <div v-if="isFile">
        <div>
          <h2>File Upload</h2>
        <a href="#" @click.prevent="switchEditor('XMLEditor')" class="text-sm underline text-blue-500">Switch to text editor</a>
        </div>
        <div>
          <label for="icn-filename" class="text-sm font-bold">Filename</label><br/>
          <input type="text" id="icn-filename" name="filename" :value="filenameInputUploadFile" @change.prevent="filenameForInputUploadFile = $event.target.value" placeholder="filename without extension" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        </div>
        <div class="error text-sm text-red-600" v-html="techpubStore.error('filename')"></div>
        <div class="w-2/3 inline-block">
          <label for="icn-path" class="text-sm font-bold">Path</label><br/>
          <input id="icn-path" name="path" :value="pathInputUploadFile" @change.prevent="pathForInputUploadFile = $event.target.value" placeholder="type the fullpath eg. csdb/n219/amm" value="csdb" type="text" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        </div>
        <div class="w-1/3 inline-block">
          <label for="icn-path" class="text-sm font-bold">Browse</label><br/>
          <input type="file" id="entity" name="entity" @change="readEntity($event)" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
        </div>
        <div class="error text-sm text-red-600" v-html="techpubStore.error('path')"></div>
        <div class="error text-sm text-red-600" v-html="techpubStore.error('entity')"></div>
      </div>
      <div v-else>
        <h2>Text Editor</h2>
        <a href="#" @click.prevent="switchEditor('FILEUpload')" class="text-sm underline text-blue-500">Switch to file upload</a>
        <div class="mb-1" v-if="$route.params.filename && isUpdate">
          <label for="object-filename" class="text-sm font-bold mr-2">Filename:</label>
          <input id="object-filename" name="filename" type="text" readonly="true" :value="$route.params.filename" class="w-96 py-0 px-1 rounded-sm"/>
        </div>
        <div class="mb-1">
          <label for="object-path" class="text-sm font-bold mr-2">Path:</label>
          <input id="object-path" name="path" :value="pathInputXMLFile" placeholder="type the fullpath eg. csdb/n219/amm" type="text" class="py-0 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        </div>
        <div :id="XMLEditor.id" class="text-xl mb-2"></div>
        <div class="error text-sm text-red-600" v-html="techpubStore.error('xmleditor')"></div>
      </div>
      <button v-if="!isUpdate && !isFile" type="submit" name="button" class="button bg-green-400 text-white hover:bg-green-600">Create</button>
      <button v-else-if="isFile" type="submit" name="button" class="button bg-blue-400 text-white hover:bg-blue-600">Upload</button>
      <button v-else type="submit" name="button" class="button bg-violet-400 text-white hover:bg-violet-600">Update</button>
    </form>
    <ContinuousLoadingCircle :show="showLoadingProgress"/>
  </div>

  <!-- <EditorDML v-else /> -->
  
</template>