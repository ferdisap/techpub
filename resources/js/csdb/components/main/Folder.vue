<script>
import { copy } from "../../helper";
import { useTechpubStore } from "../../../techpub/techpubStore";
import Sort from "../../../techpub/components/Sort.vue";
import ContinuousLoadingCircle from "../../loadingProgress/ContinuousLoadingCircle.vue";
import {
  getObjs, storingResponse, goto, back, clickFolder, clickFilename, download,
  sortTable, search, removeList, remove, pushFolder, dispatch, changePath, deleteObject, refresh
} from './FolderVue'
import FolderVueCb from "./FolderVueCb";
import ContextMenu from "../subComponents/ContextMenu.vue";

export default {
  components: { Sort, ContinuousLoadingCircle, ContextMenu },
  data() {
    return {
      techpubStore: useTechpubStore(),
      path: '',
      data: {},
      open: {},

      // type: '', // kayaknya ga perlu
      showLoadingProgress: false,

      // CbSelector: new CsdbObjectCheckboxSelector(),

      // selection view (becasuse clicked by user)
      // selectedRow: undefined,

      contextMenuId: 'cmFolderVue',
      cbId: 'cbFolderVue',
      CB: {},
    }
  },
  props: {
    dataProps: {
      type: Object,
      default: {},
    },
    routeName: {
      type: String
    }
  },
  computed: {
    // setObject() {
    //   if (this.$props.dataProps.path && (this.$props.dataProps.path !== this.data.current_path)) {
    //     this.getObjs({ path: this.$props.dataProps.path });
    //   }
    // },
    models() {
      return this.data.csdb;
    },
    folders() {
      return this.data.folders;
    },
    sc() {
      return this.data.sc;
    },
    currentPath() {
      return this.data.current_path ? this.data.current_path : '';
    },
    pagination() {
      return this.data.paginationInfo;
    },
    pageless() {
      return this.data.paginationInfo['prev_page_url'];
    },
    pagemore() {
      return this.data.paginationInfo['next_page_url'];
    },
  },
  methods: {
    getObjs: getObjs,
    storingResponse: storingResponse,
    goto: goto,
    back: back,
    clickFolder: clickFolder,
    clickFilename: clickFilename,
    sortTable: sortTable,
    search: search,
    removeList: removeList, // tidak perlu ditaruh disini
    dispatch: dispatch,
    // select: select, // DEPRECIATED
    changePath: changePath,
    deleteObject: deleteObject,
    pushFolder: pushFolder,
    download: download,

    // emit
    refresh: refresh,
    remove: remove,

    copy: copy,
  },
  mounted() {
    // dari Listtree via Explorer/Management data data berisi path doang,
    let emitters = this.emitter.all.get('Folder-refresh'); // 'emitter.length < 2' artinya emitter max. hanya dua kali di instance atau baru sekali di emit, check ManagementData.vue
    if (emitters) {
      let indexEmitter = emitters.indexOf(emitters.find((v) => v.name === 'bound refresh')) // 'bound addObjects' adalah fungsi, lihat scrit dibawah ini. Jika fungsi anonymous, maka output = ''
      if (emitters.length < 1 && indexEmitter < 0) this.emitter.on('Folder-refresh', this.refresh);
    } else this.emitter.on('Folder-refresh', this.refresh);

    emitters = this.emitter.all.get('Folder-delete'); // 'emitter.length < 2' artinya emitter max. hanya dua kali di instance atau baru sekali di emit, check ManagementData.vue
    if (emitters) {
      let indexEmitter = emitters.indexOf(emitters.find((v) => v.name === 'bound remove')) // 'bound addObjects' adalah fungsi, lihat scrit dibawah ini. Jika fungsi anonymous, maka output = ''
      if (emitters.length < 1 && indexEmitter < 0) this.emitter.on('Folder-remove', this.remove);
    } else this.emitter.on('Folder-remove', this.remove);



    if (this.$route.params.filename && this.techpubStore.currentObjectModel.csdb) {
      this.getObjs({ path: this.techpubStore.currentObjectModel.csdb.path });
      this.data.current_path = this.techpubStore.currentObjectModel.csdb.path;
    } else {
      this.getObjs({ path: 'CSDB' });
      this.data.current_path = 'CSDB';
    }

    // if (this.$props.dataProps.path && (this.$props.dataProps.path !== this.data.current_path)) {
    //   this.getObjs({ path: this.$props.dataProps.path });
    // }

    // if (this.ContextMenu.register(this.contextMenuId)) this.ContextMenu.toggle(false, this.contextMenuId);

    this.CB = new FolderVueCb(this.cbId)
  },
}
</script>
<style>
.folder th,
.folder td {
  white-space: nowrap;
}
</style>
<template>
  <div class="folder">
    <div class="h-[100%] w-full relative">
      <div class="h-[50px] relative text-center">
        <div class="absolute top-0">
          <button @click="back()" class="material-symbols-outlined has-tooltip-right"
            data-tooltip="back">keyboard_backspace</button>
        </div>
        <h1>Folder:/<span>{{ currentPath }}</span></h1>
      </div>

      <div class="text-center mb-3">
        <input @change="search()" v-model="this.data.sc" placeholder="find filename" type="text"
          class="w-48 inline bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <button class="material-icons mx-3 text-gray-500 text-sm has-tooltip-arrow inline" data-tooltip="info"
          @click="$root.info({ name: 'searchCsdbObject' })">info</button>
      </div>

      <div class="h-[75%] block relative overflow-scroll">
        <table class="table" :id="cbId">
          <thead class="text-sm text-left">
            <tr class="leading-3 text-sm">
              <th v-show="CB.selectionMode"></th>
              <th class="text-sm">Name <Sort :function="sortTable"></Sort>
              </th>
              <th class="text-sm">Path <Sort :function="sortTable"></Sort>
              </th>
              <th class="text-sm">Last History <Sort :function="sortTable"></Sort>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="path in folders" cb-room="folder" @dblclick="clickFolder($event, path)"
              class="folder-row text-sm hover:bg-blue-300 cursor-pointer">
              <td cb-window>
                <input type="checkbox" :value="path">
              </td>
              <td class="leading-3 text-sm" colspan="6">
                <span class="material-symbols-outlined text-sm mr-1">folder</span>
                <span class="text-sm">{{ path.split("/").at(-1) }} </span>
              </td>
            </tr>
            <tr v-for="obj in models" cb-room @dblclick.prevent="clickFilename($event, obj.filename)"
              class="file-row text-sm hover:bg-blue-300 cursor-pointer">
              <td cb-window>
                <input file type="checkbox" :value="obj.filename">
              </td>
              <td class="leading-3 text-sm">
                <span class="material-symbols-outlined text-sm mr-1">description</span>
                <span class="text-sm"> {{ obj.filename }} </span>
              </td>
              <td class="leading-3 text-sm"> {{ obj.path }} </td>
              <td class="leading-3 text-sm"> {{ (obj.last_history.description) }}, {{
                techpubStore.date(obj.last_history.created_at) }} </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- pagination -->
      <div class="w-full text-black bottom-[10px] h-[30px] px-3 flex justify-center">
        <div v-if="pagination" class="flex justify-center items-center bg-gray-100 rounded-lg px-2 w-[300px]">
          <button @click="goto(pageless)" class="material-symbols-outlined">navigate_before</button>
          <form @submit.prevent="goto('', pagination['current_page'])" class="flex">
            <input v-model="pagination['current_page']"
              class="w-2 text-sm border-none text-center bg-transparent font-bold" />
            <span class="font-bold text-sm"> of {{ pagination['last_page'] }} </span>
          </form>
          <button @click="goto(pagemore)" class="material-symbols-outlined">navigate_next</button>
        </div>
      </div>
    </div>

    <ContinuousLoadingCircle :show="showLoadingProgress" />
    <!-- RCMenu -->
    <ContextMenu :id="contextMenuId">
      <div @click.stop.prevent="CB.push" class="list">
        <div class="text-sm">Select</div>
      </div>
      <div @click.stop.prevent="CB.pushAll(true)" class="list">
        <div class="text-sm">Select All</div>
      </div>
      <div @click.stop.prevent="CB.pushAll(false)" class="list">
        <div class="text-sm">Deselect All</div>
      </div>
      <hr class="border border-gray-300 block mt-1 my-1 border-solid" />
      <div @click.stop.prevent="dispatch(1)" class="list">
        <div class="text-sm">Add Dispatch</div>
      </div>
      <div @click="dispatch(0)" class="list">
        <div class="text-sm">Dispatch</div>
      </div>
      <hr class="border border-gray-300 block mt-1 my-1 border-solid" />
      <div @click.stop.prevent="copy()" class="list">
        <div class="text-sm">Copy</div>
      </div>
      <div class="flex flex-col hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <form class="text-sm" @submit.prevent="changePath($event)">
          <label class="text-sm">Move </label>
          <input type="text" class="w-[65%] rounded-sm border px-1" placeholder="CSDB/..." name="path"
            @keydown.enter.prevent />
          <button type="submit"
            class="material-icons text-sm ml-2 hover:bg-blue-300 hover:border rounded-full px-1">send</button>
        </form>
      </div>
      <div @click="deleteObject()" class="list">
        <div class="text-sm">Delete</div>
      </div>
      <div @click="download()" class="list">
        <div class="text-sm">Download</div>
      </div>
      <hr class="border border-gray-300 block mt-1 my-1 border-solid" />
      <div @click.prevent="CB.cancel()" class="list">
        <div class="text-sm">Cancel</div>
      </div>
    </ContextMenu>
  </div>
</template>