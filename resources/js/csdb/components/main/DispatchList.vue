<script>
import Sort from '../subComponents/Sort.vue';
import { getObjs } from './FolderVue';
import { storingResponse, clickFilename } from './DispatchListVue';
import DispatchListVueCb from './DispatchListVueCb.js';
import ContinuousLoadingCircle from '../../loadingProgress/ContinuousLoadingCircle.vue';
import ContextMenu from '../subComponents/ContextMenu.vue';
import Pagination from '../subComponents/Pagination.vue';

export default {
  data(){
    return {
      data: {},
      showLoadingProgress: false,

      contextMenuId: 'cmDispatchListVue',

      cbId: 'cbDispatchListVue',
      CB: {}
    }
  },
  components:{ Sort, ContextMenu, ContinuousLoadingCircle, Pagination },
  methods:{
    getObjs: getObjs,
    storingResponse: storingResponse,
    clickFilename: clickFilename
  },
  mounted(){
    this.getObjs({routeName: 'api.get_ddn_list'})

    this.CB = new DispatchListVueCb(this.cbId)
    this.CB.register();

    if(this.$route.params.filename) setTimeout(()=>{
      this.emitter.emit('DDN-refresh', {filename: this.$route.params.filename})
    },0);
  }
}
</script>
<template>
  <!-- <div class="dispatchlist h-[100%] w-full relative border border-gray-400"> -->
  <div class="dispatchlist">

    <h1 class="text-blue-500 w-full text-center my-2">List</h1>

    <div class="h-[75%] block relative overflow-scroll">
      <table class="table" :id="cbId">
        <thead class="text-sm text-left">
          <tr class="leading-3 text-sm">
            <th v-show="CB.selectionMode"></th>
            <th class="text-sm">Filename <Sort/></th>
            <th class="text-sm">Date <Sort/></th>
            <th class="text-sm">From <Sort/></th>
            <th class="text-sm">Email <Sort/></th>
            <th class="text-sm">Enterprise <Sort/></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="obj in data.list" cb-room @dblclick.prevent="clickFilename($event, obj.csdb.filename)"
            class="file-row text-sm hover:bg-blue-300 cursor-pointer">
            <td cb-window>
              <input file type="checkbox" :value="obj.filename">
            </td>
            <td class="leading-3 text-sm">
              <span class="material-symbols-outlined text-sm mr-1">description</span>
              <span class="text-sm"> {{ obj.csdb.filename }} </span>
            </td>
            <td class="leading-3 text-sm">
              {{ obj.year }}-{{ obj.month }}-{{ obj.day }}
            </td>
            <td class="leading-3 text-sm">
              {{ obj.csdb.owner.first_name }} {{ obj.csdb.owner.middle_name }} {{ obj.csdb.owner.last_name }}
            </td>
            <td class="leading-3 text-sm">
              {{ obj.csdb.owner.email }}
            </td>
            <td class="leading-3 text-sm">
              {{ obj.csdb.owner.work_enterprise.name }}
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :data="data.paginationInfo"/>
    </div>

    <ContinuousLoadingCircle :show="showLoadingProgress" />

    <ContextMenu :id="contextMenuId">
      <div @click.stop.prevent="CB.push" class="list">
        <div class="text-sm">Select</div>
      </div>
      <hr/>
    </ContextMenu>
  </div>
</template>