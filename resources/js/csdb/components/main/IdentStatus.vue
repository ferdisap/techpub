<!-- 
  VUE ini DEPRECIATED karena IdentStatus tidak dipakai lagi di frontend (sebelumnya di Explorer.vue)
  props.filename is depreciated
 -->
<script>
export default {
  data(){
    return {
      data: {},
    }
  },
  props:{
    dataProps: {
      type: Object,
      required: true
    }
  },
  computed:{
    async requestTransformed(){
      if(!this.$route.params.filename){
        return '';
      }
      let response = await axios({
        route: {
          name: 'api.get_transformed_identstatus',
          data: {filename: this.$route.params.filename}
        }
      })
      // if(!this.$props.dataProps.filename){
      //   return '';
      // }
      // let response = await axios({
      //   route: {
      //     name: 'api.get_transformed_identstatus',
      //     data: {filename: this.$props.dataProps.filename}
      //   }
      // })
      this.storingResponse(response);
    },
    transformed(){
      return {
        template: this.data.transformed,
      }
    },
  },
  methods:{
    storingResponse(response){
      if(response.statusText === 'OK'){
        this.data.transformed = response.data.transformed;
      }
    }
  },
  mounted(){
    this.emitter.on('IdentStatus-refresh', async (data) => {
      let response = await axios({
        route: {
          name: 'api.get_transformed_identstatus',
          data: {filename: data.filename}
        }
      });
      this.storingResponse(response);
    });

    if(this.$route.params.filename){
      this.emitter.emit('IdentStatus-refresh', {
        filename: this.$route.params.filename,
      })
    }
  },
}
</script>
<template>
  <div class="IdentStatus overflow-auto h-[93%] w-full">
    <div class="h-[5%] flex mb-3">
      <h1 class="text-blue-500 w-full text-center">IdentStatus</h1>
    </div>
    <div class="">
      <component v-if="data.transformed" :is="transformed"/>
      <div v-if="$route.params.filename && $route.params.filename.slice(0,3) === 'ICN'">ICN Meta File coming soon</div>
    </div>
  </div>
</template>