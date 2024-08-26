<script>
import { useTechpubStore } from '../../../techpub/techpubStore';
// import { isString, isArray } from '../../helper';
import Randomstring from "randomstring";
// import TextEditor from '../../TextEditor';
// import TextEditorElement from "../../element/TextEditorElement.js";


// class SomeClass extends HTMLInputElement {
//   value = 'foo';
//   constructor() {
//     super();
//   }
// }


export default {
  data() {
    return {
      techpubStore: useTechpubStore(),
      // remarkElementName: 'rm-el',
      remarkId: Randomstring.generate({ charset: 'alphabetic' }),
      // remarksEditor: {},
      // remarksEditorId: Randomstring.generate({ charset: 'alphabetic' }),
    }
  },
  props: {
    class_label: {
      type: String,
      default: "text-lg font-bold"
    },
    class_textarea: { // not applicable
      type: String,
    },
    class: { // not applicable
      type: String,
    },
    placeholder: {
      type: String, // not applicable
      default: 'eg.: this document is intended for...',
    },
    nameAttr: {
      type: String,
      default: 'remarks',
    },
    modalInputName: {
      type: String
    },
    lineType: '',
    para: '',
  },
  computed: {
    remarksPara() {
      if (this.$props.para) {
        const rm = document.querySelector('#' + this.remarkId);
        if (rm) rm.value = this.$props.para;
      }
      return;
    }
  },
  methods: {
  },
  mounted() {},
}
</script>


<template>
  <div class="RemarksVue">
    {{ remarksPara }}
    <div :class="['block mb-2 text-gray-900 dark:text-white', $props.class_label]">Remarks:</div>
    <text-editor v-pre :line-type="$props.lineType" :name="$props.nameAttr" :class="[$props.class,'w-full block']" :id="remarkId" :modal-input-name="$props.modalInputName"/>
    <div class="text-red-600" v-html="techpubStore.error('remarks')"></div>
  </div>
</template>