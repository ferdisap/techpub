<script>
import { useTechpubStore } from '../../../techpub/techpubStore';
import {getObjs, storingResponse, goto, removeList, restore, permanentDelete, refresh, select, preview, clickFilename} from './DeletionVue.js';
// import {CsdbObjectCheckboxSelector} from '../../CheckboxSelector';
import Checkbox from '../../Checkbox';
import ContinuousLoadingCircle from "../../loadingProgress/ContinuousLoadingCircle.vue";
import Sort from "../subComponents/Sort.vue";
import { copy } from "../../helper";
import ContextMenu from "../subComponents/ContextMenu.vue";
import DeletionVueCb from "./DeletionVueCb.js"
import Pagination from '../subComponents/Pagination.vue';
import { download } from '../main/FolderVue.js';

export default {
  name: 'Deletion',
  components: {ContinuousLoadingCircle, Sort, ContextMenu, Pagination},
  data() {
    return {
      techpubStore: useTechpubStore(),
      data: {},

      // selection view (becasuse clicked by user)
      // selectedRow: undefined,

      contextMenuId: 'cmDeletionVue',
      cbId: 'cbDeletionVue',
      CB: {},
    }
  },
  computed: {
    list() {
      return this.data.csdb ?? [];
    },
    filenameSearch() {
      return this.data.filenameSearch;
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
    removeList: removeList,
    restore: restore,
    permanentDelete: permanentDelete,
    download: download,
    clickFilename: clickFilename,
    refresh: refresh,
    select: select,
    preview: preview,

    copy:copy,
  },
  mounted() {
    this.getObjs({ filenameSearch: this.filenameSearch });
    this.emitter.on('Deletion-refresh', this.refresh);

    this.CB = new Checkbox(this.cbId);
  }
}
</script>
<template>
  <div class="deletion overflow-auto h-full">

    <div class="bg-white px-3 py-3 2xl:h-[92%] xl:h-[90%] lg:h-[88%] md:h-[90%] sm:h-[90%] h-full fix">

      <div class="2xl:h-[5%] xl:h-[6%] lg:h-[8%] md:h-[9%] sm:h-[11%]">
        <h1 class="text-blue-500">DELETION</h1>
        <hr class="border-2 border-blue-500" />
      </div>

      <div class="2xl:h-[95%] xl:h-[94%] lg:h-[92%] md:h-[91%] sm:h-[89%]">

        <div class="block relative oveflow-auto max-h-[80%] text-left">
          <table class="table" :id="cbId">
            <thead class="text-sm">
              <tr>
                <th v-show="CB.selectionMode"></th>
                <th class="w-[53%] text-sm">Filename <Sort/></th>
                <th class="w-[15%] text-sm">Path</th>
                <th class="w-[15%] text-sm">Last Update</th>
                <th class="w-[15%] text-sm">Deleted At</th>
              </tr>
            </thead>
            <tbody>
              <!-- <tr v-for="object in list" @click.stop.prevent="select($event)" @dblclick.prevent="clickFilename($event, path)" @mousemove="CbSelector.setCbHovered('cbdell'+object.filename)" class="text-sm hover:cursor-pointer" > -->
              <tr v-for="object in list" cb-room @dblclick="clickFilename($event, path)" class="text-sm hover:cursor-pointer">
                <td cb-window class="flex p-2">
                  <input type="checkbox" :value="object.filename">
                </td>
                <td class="text-sm">
                  <span class="material-symbols-outlined text-sm mr-1">description</span>
                  <span class="text-sm"> {{ object.filename }} </span>
                </td>
                <td class="text-sm">{{ object.path }}</td>
                <td class="text-sm">{{ techpubStore.date(object.updated_at) }}</td>
                <td class="text-sm">{{ object.last_history ? techpubStore.date(object.last_history.created_at) : '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination :data="data.paginationInfo"/>
      </div>
      <ContinuousLoadingCircle/>
    </div>

    <ContextMenu  :id="contextMenuId">
      <div @click="CB.push" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Select</div>
      </div>
      <div @click="CB.pushAll(true)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Select All</div>
      </div>
      <div @click="CB.pushAll(false)" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Deselect All</div>
      </div>
      <hr class="border border-gray-300 block mt-1 my-1 border-solid"/>
      <div @click="copy()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Copy</div>
      </div>
      <div @click="download()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Download</div>
      </div>
      <!-- <div @click="preview()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Preview</div>
      </div> -->
      <hr class="border border-gray-300 block mt-1 my-1 border-solid"/>
      <div @click="restore()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Restore</div>
      </div> 
      <!-- <div @click="permanentDelete()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Permanent Delete</div>
      </div> -->
      <hr class="border border-gray-300 block mt-1 my-1 border-solid"/>
      <div @click.prevent="CB.cancel()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Cancel</div>
      </div>
    </ContextMenu>
  </div>
</template>