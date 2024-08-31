<script>
import Randomstring from 'randomstring';
import { useTechpubStore } from '../../techpub/techpubStore';
export default {
  data(){
    return {
      techpubStore: useTechpubStore(),
    }
  },
  beforeMount() {
    if (!this.$parent.componentId) {
      this.$parent.componentId = Randomstring.generate({charset:'alphabetic'});
    }
    useTechpubStore().componentLoadingProgress[this.$parent.componentId] = false;

  },
  mounted(){
    this.$el.parentElement.style.position = 'relative';
  }
}
</script>
<template>
  <div v-if="techpubStore.componentLoadingProgress[$parent.componentId]" class="continuousloadingcircle">
    <div class="loading_buffer" />
  </div>
</template>