<script>
import {setBR} from './BrVue';

export default {
  data(){  
    return {
      BRObject:{},
      BRModel: {},
    }
  },
  mounted(){
    if(this.$route.filename){
      this.setBR(this.$route.filename);
    }
  }
}

</script>
<template>
  <div class="br bg-white px-3 py-3 2xl:h-[92%] xl:h-[90%] lg:h-[88%] md:h-[90%] sm:h-[90%] h-full">
    <div class="2xl:h-[5%] xl:h-[6%] lg:h-[8%] md:h-[9%] sm:h-[11%] border-b-4 border-blue-500 grid items-center">
      <h1>BUSINESS RULE</h1>
    </div>

    <div class="mt-20 w-full text-center text-lg">
      will be available soon.
    </div>

  </div>
</template>