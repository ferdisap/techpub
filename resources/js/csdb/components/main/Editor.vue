<script>
import EditorXML from './EditorXML.vue';
import EditorDML from './EditorDML.vue';
import EditorICN from './EditorICN.vue';
import ContextMenu from '../subComponents/ContextMenu.vue';

export default {
  data() {
    return {
      // editorComponent: 'EditorXML',
      editorComponent: 'EditorXML',
      contextMenuId: 'cmEditorVue',
      text: '',
    }
  },
  provide() {
    return {
      'getTextReadFromReadFile': () => this.text,
    }
  },
  components: { EditorXML, EditorDML, EditorICN, ContextMenu },
  computed: {
    type() {
      return this.$route.params.filename.substring(0, 3);
    }
  },
  methods: {
    readTextFileFromUploadICN(text) {
      this.text = text;
      this.editorComponent = 'EditorXML';
    },
    getEditorName(type) {
      switch (this.type) {
        case 'ICN': return 'EditorICN';
        case 'DML': return 'EditorDML';
        default: return 'EditorXML';
      }
    },
    refresh() {
      this.emitter.emit(this.getEditorName(this.type) + '-refresh');
    }
  },
  mounted() {
    this.editorComponent = this.getEditorName(this.type);

    this.emitter.on('Editor-refresh', this.refresh)
  }
}
</script>
<template>
  <div class="editor">
    <component :is="editorComponent" v-if="editorComponent" />

    <ContextMenu :id="contextMenuId">
      <div v-if="editorComponent !== 'EditorDML'" @click="editorComponent = 'EditorDML'"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to DML Editor</div>
      </div>
      <div v-else-if="editorComponent !== 'EditorICN'" @click="editorComponent = 'EditorICN'"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to ICN Editor</div>
      </div>
      <div v-else-if="editorComponent !== 'EditorXML'" @click="editorComponent = 'EditorXML'"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Switch to XML Editor</div>
      </div>
    </ContextMenu>
</div></template>