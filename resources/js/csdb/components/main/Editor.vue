<script>
import EditorXML from './EditorXML.vue';
import EditorDML from './EditorDML.vue';
import EditorICN from './EditorICN.vue';
import ContextMenu from '../subComponents/ContextMenu.vue';

export default {
  data(){
    return {
      // editorComponent: 'EditorXML',
      editorComponent: 'EditorXML',
      contextMenuId: 'cmEditorVue',
      text: '',
    }
  },
  provide(){
    return {
      'getTextReadFromReadFile': () => this.text,
    }
  },
  components: {EditorXML, EditorDML, EditorICN, ContextMenu},
  computed:{
    type(){
      return this.$route.params.filename.substring(0,3);
    }
  },
  methods:{
    readTextFileFromUploadICN(text){
      this.text = text;
      this.editorComponent = 'EditorXML';
      // this.text = '';
    },
  },
  mounted(){
    // if(this.ContextMenu.register(this.contextMenuId)) this.ContextMenu.toggle(false,this.contextMenuId);

    switch (this.type) {
      case 'ICN':
        this.editorComponent = 'EditorICN';
        break;
      case 'DML':
        this.editorComponent = 'EditorDML';
        break;    
      default:
        this.editorComponent = 'EditorXML';
        // this.editorComponent = 'EditorDML';
        break;
    }
  }
}
</script>
<template>
  <div class="editor">
    <component :is="editorComponent" v-if="editorComponent"/>
  
    <ContextMenu :id="contextMenuId">
      <div v-if="editorComponent !== 'EditorDML'"  @click="editorComponent = 'EditorDML'" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to DML Editor</div>
      </div>
      <div v-else-if="editorComponent !== 'EditorICN'"  @click="editorComponent = 'EditorICN'" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to ICN Editor</div>
      </div>
      <div v-else-if="editorComponent !== 'EditorXML'" @click="editorComponent = 'EditorXML'" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to XML Editor</div>
      </div>
    </ContextMenu>
  </div>
</template>