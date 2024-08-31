<script>
import ContextMenu from '../subComponents/ContextMenu.vue'
import ContinuousLoadingCircle from '../../loadingProgress/continuousLoadingCircle.vue';
// import {CheckboxSelector} from '../../CheckboxSelector';
// import DropdownInputSearch from '../../DropdownInputSearch';
import { useTechpubStore } from '../../../techpub/techpubStore';
import Remarks from '../subComponents/Remarks.vue';
import {isObject} from '../../helper'
import RoutesWeb from '../../RoutesWeb';
import axios from 'axios';
import Checkbox from '../../Checkbox';
export default {
  data() {
    return {
      techpubStore: useTechpubStore(),
      objects: [], // berisi filenames
      
      cbId: 'cbDispatchToVue',
      contextMenuId: 'cmDispatchToVue',
      CB: {},
    }
  },
  components:{ContinuousLoadingCircle, Remarks, ContextMenu},
  props:{
    objectsToDispatch:{
      type: Array,
      default: [],
      // type: Array,
      // default: [],
    }
  },
  methods: {
    async submit(event){
      let fd = new FormData(event.target);

      fd.set('dispatchFromPersonEmail', this.techpubStore.Auth.email);

      for (var i = 0; i < this.objects.length; i++) {
        fd.append('deliveryListItemsFilename[]', this.objects[i]);
      }
      
      let response = await axios({
        route: {
          name: 'api.create_ddn',
          data: fd,
        },
      });

      if (response.statusText === 'OK') {
        // do something here
        // this.model = response.data.model;
        this.emitter.emit('createDDNFromDispatchTo', response.data.csdb);

      }
      // this.showLoadingProgress = false;
    },
    clickFilename(event){
      if(this.CB.selectionMode){
        this.CB.push();
      }
    },
    remove(values){
      if(!values) values = this.CB.value();
      values.forEach((value)=>{
        let i = this.objects.indexOf(value);
        if(i > -1) this.objects.splice(i,1);
      });
      return this.CB.selectionMode ? this.CB.closeSelectionMode() : undefined;
    },
    // bisa langsung pakai DropdownBrexSearch@keypress di tag htmlnya, tanpa pakai fungsi searchUser ini
    async searchUser(event){
      if(event.target.id === this.DropdownUserSearch.idInputText){
        // let route = this.techpubStore.getWebRoute('api.user_search_model', {sc: event.target.value, limit:5});
        const route = RoutesWeb.get('api.user_search_model', {sc: event.target.value, limit:5});
        this.DropdownUserSearch.keypress(event, route);
      }
    },
    // bisa langsung pakai DropdownBrexSearch@keypress di tag htmlnya, tanpa pakai fungsi searchBrexIni
    async searchBrex(event){
      if(event.target.id === this.DropdownBrexSearch.idInputText){
        // let route = this.techpubStore.getWebRoute('api.dmc_search_model', {sc: "filename::" + event.target.value, limit:5});
        const route = RoutesWeb.get('api.dmc_search_model', {sc: "filename::" + event.target.value, limit:5});
        this.DropdownBrexSearch.keypress(event, route);
      }
    },
    setObject(data){
      if(Array.isArray(data)){
        this.objects = [];
        data.forEach((v) => {
          if(isObject(v)) this.objects.push(v.filename);
          else this.objects.push(v);
        });
      }
    },
    addObject(data){
      if(Array.isArray(data)){
        data.forEach((v) => {
          if(isObject(v)) this.objects.push(v.filename);
          else this.objects.push(v);
        });
      }
    },
  },
  mounted() {
    this.emitter.on('dispatchTo', this.setObject); 
    this.emitter.on('AddDispatchTo', this.addObject); 

    this.CB = new Checkbox(this.cbId);
    this.CB.register();
    this.Dropdown.register('dropdownDispatchToPersonEmail');
    this.Dropdown.register('dropdownBrexDmRef');

  }
}
</script>
<template>
  <div class="dispatchto overflow-auto h-[93%] w-full relative px-3">

    <form @submit.prevent="submit($event)">
      <!-- List of filename to dispatch -->
      <h1 class="text-blue-500 w-full text-center my-2">Dispatched Object</h1>
      <div class="flex items-center mt-1">
        <label for="securityClassification" class="text-sm mr-3 font-bold">Security Classification:</label>
        <input name="securityClassification" id="securityClassification" placeholder="eg:. 05" value="01" class="w-[50px]"/>
      </div>
      <div class="mb-3">
        <table class="text-left" :id="cbId">
          <thead>
            <tr>
              <th v-show="CB.selectionMode" class="w-10"></th>
              <th class="w-10">No</th>
              <th>Filename</th>
            </tr>
          </thead>
          <tbody>
            <tr cb-room @click.prevent="clickFilename($event)" v-for="(filename, i) in objects" class="hover:bg-blue-300">
              <td cb-window v-show="CB.selectionMode">
                <input type="checkbox" :value="filename" :id="'dt'+filename">
              </td>
              <td class="p-0 text-sm text-center">{{ i+1 }}</td>
              <td class="p-0 text-sm">{{ filename }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <hr/>
      <!-- Dropdown User Search -->
      <div class="mb-2 mt-2 flex" id="dropdownDispatchToPersonEmail">
        <div class="mr-2">
          <label class="font-bold text-sm">Send To: </label>
        </div>
        <div class="w-80 relative">
          <input name="dispatchToPersonEmail" class="block mb-0 w-full p-1" autocomplete="off" aria-autocomplete="none"
            dd-input="email"
            dd-target="self"
            dd-route="api.user_search_model"
            dd-type="users"
            />
        </div>
      </div>
      <!-- Dropdown BREX Search -->
      <div class="mb-2 mt-2 flex" id="dropdownBrexDmRef">
        <div class="mr-2">
          <label class="font-bold text-sm">Brex: </label>
        </div>
        <div class="w-80 relative">
          <input name="brexDmRef" class="block mb-0 w-full p-1" autocomplete="off" aria-autocomplete="none"
            dd-input="filename,path" 
            dd-target="self"
            dd-route="api.get_object_csdbs"
            dd-type="csdbs"
          />
        </div>
      </div>
      <div class="blok items-center mt-1 mb-2">
        <Remarks class_label="text-sm font-bold" placeholder="eg.: this comment answer the COM-...xml"/>
      </div>
      <div class="w-full text-center">
        <button type="submit" class="button bg-violet-400 text-white hover:bg-violet-600">Submit</button>
      </div>
    </form>
    <ContinuousLoadingCircle/>
    <ContextMenu :id="contextMenuId">
      <div @click.stop.prevent="CB.push" class="list">
        <div class="text-sm">select</div>
      </div>
      <div @click.stop.prevent="remove()" class="list">
        <div class="text-sm">remove</div>
      </div>
    </ContextMenu>
    <!-- <RCMenu v-if="CbSelector.isShowTriggerPanel" id="DispatchTo">
      <div @click="CbSelector.select()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Select</div>
      </div>
      <div @click="remove()" class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">Remove</div>
      </div>
    </RCMenu> -->
  </div>
</template>