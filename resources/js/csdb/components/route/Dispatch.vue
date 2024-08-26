<script>
import { bottomBarItems, colWidth, col1Width, col2Width } from './DispatchVue';
import DispatchList from '../main/DispatchList.vue'
import { turnOnSizing, turnOffSizing } from './ExplorerVue';
import DDN from '../subComponents/DDN.vue';
export default {
  name: 'Dispatch',
  components:{ DispatchList, DDN },
  data(){
    return {
      bottomBarItems: bottomBarItems,
      colWidth: colWidth,
    }
  },
  computed:{
    col1Width: col1Width,
    col2Width: col2Width,
  },
  methods:{
    turnOnSizing: turnOnSizing,
    turnOffSizing: turnOffSizing,
  },
  mounted(){
    this.emitter.on('clickFilenameFromDispatchList', (data) => {
      this.emitter.emit('DDN-refresh', data);
    });
  }
}
</script>
<template>
  <div class="dispatch overflow-auto h-full">
    <div class="bg-white px-3 py-3 2xl:h-[92%] xl:h-[90%] lg:h-[88%] md:h-[90%] sm:h-[90%] h-full">
      <div class="2xl:h-[5%] xl:h-[6%] lg:h-[8%] md:h-[9%] sm:h-[11%] border-b-4 border-blue-500 grid items-center">
        <h1>DISPATCH</h1>
      </div>

      <div class="explorer-content flex 2xl:h-[95%] xl:h-[94%] lg:h-[92%] md:h-[91%] sm:h-[89%]">
        <div :style="[col1Width]" class="overflow-auto text-nowrap relative h-full">
          <DispatchList/>
        </div>
        <div id="v-line-2" class="v-line h-full border-l-[4px] border-blue-500 w-0 cursor-ew-resize"
            @mousedown.prevent="turnOnSizing($event, 'satu', 'colWidthDispatch')"></div>
        <div :style="[col2Width]" class="overflow-auto text-wrap relative h-full w-full">
          <DDN/>
        </div>
      </div>
    </div>
  </div>
</template>