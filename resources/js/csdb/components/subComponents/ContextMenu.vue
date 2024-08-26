<script>
import { copy } from "../../helper";
export default {
  props: ['id'],
  methods: {
    copy: copy
  },
  mounted(){
    if (this.ContextMenu.register(this.$props.id)) this.ContextMenu.toggle(false, this.$props.id);
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