<script>
// import {useTechpubStore} from '../../techpub/techpubStore';
// import ManagementData from './route/ManagementData.vue';
import Explorer from './route/Explorer.vue';
import {joinFilename, deleteCSDBs, getCSDBObjectModel} from './MainVue';

export default {
  data() {
    return {
      // techpubStore: useTechpubStore(),
      showRightAside: false,
      TO: 0,
    };
  },
  components: {Explorer},
  methods:{
    joinFilename: joinFilename,
    deleteCSDBs: deleteCSDBs,
    getCSDBObjectModel: getCSDBObjectModel,
  },
  mounted() {
    this.emitter.on('openfile', () =>  this.showRightAside = true)
    
    this.emitter.on('DeleteCSDBObjectFromEveryWhere', (data) => {
      this.deleteCSDBs(data)
    })

    this.emitter.on('RequireCSDBObjectModel', (data) => this.getCSDBObjectModel(data));
    
    setTimeout(() => {
      if(this.$route.params.filename){
        this.getCSDBObjectModel({filename: this.$route.params.filename})
      }
    }, 10);

    if(this.$route.params.filename) this.getCSDBObjectModel({filename: this.$route.params.filename});
    // window.rt = this.$route;
    // window.rtr = this.$router;

  },
}
</script>
<template>
  <div class="pt-5 pl-2 h-full w-full overflow-auto">
    <router-view v-slot="{ Component }">
      <!-- <keep-alive exclude="Explorer,ManagementData"> -->
      <keep-alive>
        <component :is="Component" />
      </keep-alive>
    </router-view>
  </div>
</template>