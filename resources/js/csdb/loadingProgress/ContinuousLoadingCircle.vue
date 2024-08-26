<script>
  export default {
    props:{
      show:false
    },
  }
</script>
<template>
  <div v-if="$props.show" class="continuousloadingcircle">
    <div class="loading_buffer"/>
  </div>
</template>