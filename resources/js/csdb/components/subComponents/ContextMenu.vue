<script>
import { copy } from "../../helper";
export default {
  props: {
    id: String,
    level: {
      type: Number,
      default: 0
    },
    trigger:Array
  },
  methods: {
    copy: copy
  },
  mounted(){
    if(this.$props.trigger){
      this.ContextMenu.register(this.$props.id, this.$props.level, this.$props.trigger);
    }
    else{
      if (this.ContextMenu.register(this.$props.id, this.$props.level)) this.ContextMenu.toggle(false, [this.$props.id]);
    }
  }
}
</script>
<template>
  <div context-menu :id="id" style="display:none" class="contextmenu">
    <div class="container">
      <slot>
        <div @click.stop.prevent="copy()" class="list">
          <div class="text-sm">Copy</div>
        </div>
      </slot>
    </div>
  </div>
</template>