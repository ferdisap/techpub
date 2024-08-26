<script>
// import RoutesWeb from '../../RoutesWeb';
// import jp from 'jsonpath';
import Remarks from './Remarks.vue';
import ContextMenu from './ContextMenu.vue';
import {showDDNContent, explorerConfig, importObject, refresh} from './DDNVue';
import Checkbox from '../../Checkbox';

export default {
  data(){
    return {
      showLoadingProgress: false,
      DDNObject: {},
      cbId: "cbDdnVue",
      cmId: 'cmDdnVue',
      CB: {},
    }
  },
  components:{Remarks, ContextMenu},
  methods:{
    showDDNContent: showDDNContent,
    explorerConfig: explorerConfig,
    importObject: importObject,
    refresh: refresh,
  },
  mounted(){
    // window.ddn = this;
    // window.jp = jp;
    if (this.$props.filename && (this.$props.filename.substring(0, 3) === 'DDN')) {
      this.showDDNContent(this.$props.filename);
    }

    let emitters = this.emitter.all.get('DDN-refresh'); // 'emitter.length < 2' artinya emitter max. hanya dua kali di instance atau baru sekali di emit, check ManagementData.vue
    if (emitters) {
      const indexEmitter = emitters.indexOf(emitters.find((v) => v.name === 'bound refresh')) // 'bound addObjects' adalah fungsi, lihat scrit dibawah ini. Jika fungsi anonymous, maka output = ''
      if (emitters.length < 1 && indexEmitter < 0) this.emitter.on('DDN-refresh', this.refresh);
    } else this.emitter.on('DDN-refresh', this.refresh);

    this.CB = new Checkbox(this.cbId);
    this.CB.cbRoomDisplay = 'block';
    this.CB.register();
  }
}
</script>
<template>
  <div class="ddn">
    <h1 class="text-blue-500 w-full text-center my-2">DDN View</h1>
    <div class="mb-2 flex">
      <div class="mr-2">
        <div class=" font-bold italic">Code: </div>
        <div>{{ DDNObject.code }}</div>
      </div>
      <div class="mr-2">
        <div class=" font-bold italic">InWork: </div>
        <div>{{ DDNObject.inWork }}</div>
      </div>
      <div class="mr-2">
        <div class=" font-bold italic">Date: </div>
        <div>{{ DDNObject.issueDate }}</div>
      </div>
    </div>
    <div class="mb-2 flex">
      <div class="mr-2">
        <div class=" font-bold italic">Security</div>
        <div>{{ DDNObject.securityClassification }}</div>
      </div>
      <div class="mr-2">
        <div class=" font-bold italic">BREX</div>
        <div>{{ DDNObject.brex }}</div>
      </div>
    </div>
    <div>
      <Remarks :para="DDNObject.remarks" class_label="text-sm font-bold italic"/>
    </div>
    
    <table :id="cbId">
      <thead>
        <tr>
          <th v-show="CB.selectionMode"></th>
          <th>Filename</th>
        </tr>
      </thead>
      <tbody v-if="DDNObject.dispatchFileNames && DDNObject.dispatchFileNames.length">
        <tr cb-room v-for="(filename) in DDNObject.dispatchFileNames">
          <td cb-window>
            <input type="checkbox" :value="filename">
          </td>
          <td>
            <a class="text-sm" :href="$router.resolve(explorerConfig(filename))['fullPath']">{{ filename }}</a>
          </td>
        </tr>
      </tbody>
    </table>

    <ContextMenu :id="cmId">
      <div class="list" @click.prevent="$router.push(explorerConfig(CB.value()[0]))">
        <div class="text-sm">preview</div>
      </div>
      <div class="list" @click.prevent="importObject">
        <div class="text-sm">import</div>
      </div>
    </ContextMenu>
    
  </div>
</template>