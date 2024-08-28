<script>
import { showDMLContent, showCSLContent, addEntry, removeEntry, editEntry, submitUpdate, submitMerge, getAllValues } from './DMLVue.js';
import ContinuousLoadingCircle from '../../loadingProgress/ContinuousLoadingCircle.vue';
import { CheckboxSelector } from '../../CheckboxSelector';
// import Randomstring from 'randomstring';
import DMLVueCb from './DMLVueCb.js';
import ContextMenu from './ContextMenu.vue';
import Remarks from './Remarks.vue';
import Modal from './Modal.vue';

export default {
  data() {
    return {
      showLoadingProgress: false,

      DMLType: '',
      DMLObject: {},
      CbSelector: new CheckboxSelector(),
      // rand: Randomstring,

      dmlEntryData: {}, // depreciated
      dmlEntryVueTemplate: '',

      contextMenuId: 'cmDMLVue',
      cbId: 'cbDMLVue',
      CB: {},
      dmlEntryModalId: 'modal_dmlEntry_form',

      contextMenuIdMerge: 'cmDMLVueMerge',
      mergeId: 'mergedml'
    }
  },
  components: { ContextMenu, Modal, Remarks, ContinuousLoadingCircle },
  props: ['filename'], // tidak mengambil langsung dari $route 
  methods: {
    showDMLContent: showDMLContent,
    showCSLContent: showCSLContent,
    edit: editEntry,
    add: addEntry,
    remove: removeEntry,
    getAllValues: getAllValues,
    submit: submitUpdate,
    openMerge(){
      document.getElementById(this.mergeId).style.display = 'block';
    },
    cancelMerge(){
      document.getElementById(this.mergeId).style.display = 'none';
    },
    submitMerge: submitMerge,
    refresh(){
      this.DMLType === 's' ? this.showCSLContent($route.params.filename) : this.showDMLContent(this.$props.filename);
    }
  },
  computed: {
    entries() {
      return {
        components: { Remarks },
        template: this.dmlEntryVueTemplate,
      }
    }
  },
  mounted() {
    // window.dml = this;
    if (this.$props.filename && (this.$props.filename.substring(0, 3) === 'DML')) {
      this.refresh();
    }

    if(this.ContextMenu.register(this.contextMenuId)) this.ContextMenu.toggle(false, this.contextMenuId);

    this.CB = new DMLVueCb(this.cbId)
    this.CB.cbRoomAdditionalAttribute = { "use-in-modal": this.dmlEntryModalId };

    this.Dropdown.register(this.mergeId);

    this.emitter.on('DML-refresh', this.refresh);
  }
}
</script>
<template>
  <div class="dml">
    <h1 class="mt-2 mb-4 text-center underline text-blue-500">{{ DMLType === 'p' ? 'Partial DML' : (DMLType === 'c' ? 'Complete DML' : (DMLType === 's' ? 'Status DML' : '') ) }}</h1>
  
    <form @submit.prevent>
      <!-- dmlIdentAndStatus -->
      <div class="dmlIdentAndStatusSection mb-3">
        <div class="mb-2 flex">
          <div class="mr-2">
            <div class=" font-bold italic">Code</div>
            <div>{{ DMLObject.code }}</div>
          </div>
          <div class="mr-2">
            <div class=" font-bold italic">InWork</div>
            <div class="text-center">{{ DMLObject.inWork }}</div>
          </div>
          <div class="mr-2">
            <div class=" font-bold italic">Issue</div>
            <div class="text-center">{{ DMLObject.issueNumber }}</div>
          </div>
          <div class="mr-2">
            <div class=" font-bold italic">Date</div>
            <div>{{ DMLObject.issueDate }}</div>
          </div>
        </div>
        <div class="mb-2 flex">
          <div class="mr-2">
            <div class=" font-bold italic">Security</div>
            <input type="number" name="ident-securityClassification" :value="DMLObject.securityClassification"
              class="w-20 bg-white border-none p-1" />
          </div>
          <div class="mr-2">
            <div class=" font-bold italic">BREX</div>
            <input name="ident-brexDmRef" :value="DMLObject.BREX" class=" w-96 bg-white border-none p-1" />
          </div>
        </div>
        <div>
          <Remarks :para="DMLObject.remarks" class_label="text-sm font-bold italic" nameAttr="ident-remarks[]" />
        </div>
      </div>
      <!-- dmlContent -->
      <div class="mt-3">
        <table :id="cbId">
          <thead>
            <tr>
              <th v-show="CB.selectionMode"></th>
              <th>No</th>
              <th>Ident</th>
              <th>Type</th>
              <th>Security</th>
              <th>Company</th>
              <th>Answer</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <component v-if="dmlEntryVueTemplate" :is="entries" />
          </tbody>
        </table>
      </div>
      <!-- submit button -->
      <div v-if="DMLType !== 's'" class="text-center mt-3">
        <button @click.stop.prevent="submit()" type="button"
          class="button bg-violet-400 text-white hover:bg-violet-600">Submit</button>
      </div>
      <!-- modal to fill dml Entry -->
      <Modal :id="dmlEntryModalId">
        <template #title>
          <h1 class="text-center font-bold mb-2 text-lg">DML Entry</h1>
        </template>
        <div class="w-full text-center mb-2 relative">
          <!-- entry ident -->
          <div class="relative text-left mb-2">
            <label class="italic font-semibold ml-1">No.</label>
            <input modal-input-name="no" type="number" class="p-2 ml-1 text-sm rounded-lg w-14">
            <label class="italic font-semibold ml-1">Entry Ident:</label>
            <input placeholder="DMC-..." type="text" class="p-2 w-96 ml-1 inline text-sm rounded-lg border"
              modal-input-name="entryIdent"
              dd-input="filename,path" 
              dd-type="csdbs" 
              dd-route="api.get_object_csdbs"
              dd-target="self">
          </div>
          <div class="relative text-left mb-2">
            <label class="italic font-semibold ml-1">Entry Type:</label>
            <select modal-input-name="dmlEntryType" class="ml-2 p-2 rounded-lg">
              <option class="text-sm" value="">---</option>
              <option class="text-sm" value="new">new</option>
              <option class="text-sm" value="changed">changed</option>
              <option class="text-sm" value="deleted">deleted</option>
            </select>
            <label class="italic font-semibold ml-1">Issue Type:</label>
            <select modal-input-name="issueType" class="ml-2 p-2 rounded-lg">
              <option class="text-sm" value="">---</option>
              <option class="text-sm" value="new">new</option>
              <option class="text-sm" value="changed">changed</option>
              <option class="text-sm" value="deleted">deleted</option>
              <option class="text-sm" value="revised">revised</option>
              <option class="text-sm" value="status">status</option>
              <option class="text-sm" value="rinstate-status">rinstate-status</option>
              <option class="text-sm" value="rinstate-changed">rinstate-changed</option>
              <option class="text-sm" value="rinstate-revised">rinstate-revised</option>
            </select>
          </div>
          <div class="relative text-left mb-2">
            <label class="italic font-semibold ml-1">Security Classification:</label>
            <select modal-input-name="securityClassification" class="ml-2 p-2 rounded-lg">
              <option class="text-sm" value="">---</option>
              <option class="text-sm" value="01">Unclassified</option>
              <option class="text-sm" value="02">Classified</option>
              <option class="text-sm" value="03">Restricted</option>
              <option class="text-sm" value="04">Secret</option>
              <option class="text-sm" value="05">Top Secret</option>
            </select>
          </div>
          <div class="relative text-left mb-2">
            <label class="italic font-semibold ml-1">Enterprise:</label>
            <!-- <input dd-input="enterprise,path" dd-type="csdbs" dd-route="api.get_object_csdbs" dd-target="self,modal_enterpriseCode" -->
            <input modal-input-name="enterpriseName" placeholder="find name" type="text"
              class="ml-1 w-80 p-2 inline bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
              dd-input="name,code.name" 
              dd-type="enterprises" 
              dd-route="api.get_enterprises"
              dd-target="self,modal_enterpriseCode">
            <input modal-input-name="enterpriseCode" id="modal_enterpriseCode" placeholder="find code" type="text"
              class="ml-1 w-32 p-2 inline bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <div class="relative text-left mb-2">
            <label class="italic font-semibold ml-1">Answer to entry:</label>
            <select modal-input-name="answerToEntry" class="ml-2 p-2 rounded-lg">
              <option class="text-sm" value="">---</option>
              <option class="text-sm" value="y">yes</option>
              <option class="text-sm" value="n">no</option>
            </select>
            <text-editor v-pre modal-input-name="answer[]" class="w-full block mt-1"/>
          </div>
          <div class="relative text-left mb-2">
            <Remarks modalInputName="remarks[]" class_label="text-sm font-semibold italic" />
          </div>
        </div>
      </Modal>
  
      <ContextMenu :id="contextMenuId">
        <div v-if="DMLType !== 's'" @click="CB.push()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Select</div>
        </div>
        <div v-if="DMLType !== 's'" @click="CB.pushAll(true)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Select All</div>
        </div>
        <div v-if="DMLType !== 's'" @click="CB.pushAll(false)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Deselect All</div>
        </div>
        <hr v-if="DMLType !== 's'" class="border border-gray-300 block mt-1 my-1 border-solid" />
        <div v-if="DMLType !== 's'" @click="add()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Add</div>
        </div>
        <div v-if="DMLType !== 's'" @click="add(true, true)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Duplicate</div>
        </div>
        <div v-if="DMLType !== 's'" @click="edit()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Edit</div>
        </div>
        <div v-if="DMLType !== 's'" @click="remove()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Remove</div>
        </div>
        <hr v-if="DMLType !== 's'" class="border border-gray-300 block mt-1 my-1 border-solid" />
        <div @click="$parent.isUpdate = false" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Create New</div>
        </div>
        <div @click="openMerge" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Merge</div>
        </div>
        <div v-if="DMLType !== 's'" @click.prevent="showCSLContent($route.params.filename)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">See CSL</div>
        </div>
        <div v-else @click.prevent="showDMLContent($route.params.filename)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">See DML</div>
        </div>
        <div @click.prevent="CB.cancel()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Cancel</div>
        </div>
      </ContextMenu>
    </form>
  
    <div :id="mergeId" class="my-2 border p-2 rounded-md shadow-md text-center" style="display:none">
      <div class="text-center">
        <label class="font-semibold mb-2 block">Merge DML</label>
        <input placeholder="search filename" type="text" class="inline p-2 w-96 ml-1text-sm rounded-lg border"
                dd-input="filename,path" 
                dd-type="csdbs" 
                dd-route="api.get_object_csdbs"
                dd-target="mergesourcecontainer-append">
      </div>
      <textarea id="mergesourcecontainer" merge-source-container class="w-full block p-2 my-2"></textarea>
      <button @click.stop.prevent="submitMerge()" type="button" class="button text-center bg-violet-400 text-white hover:bg-violet-600">Submit</button>
      <ContextMenu :id="contextMenuIdMerge">
        <div @click.prevent="cancelMerge()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
          <div class="text-sm">Cancel</div>
        </div>
      </ContextMenu>
    </div>
  
    <ContinuousLoadingCircle :show="showLoadingProgress" />
  </div>
</template>