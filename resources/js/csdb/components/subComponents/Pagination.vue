<script>
export default {
  props: {
    data: {
      type: Object
    },
    callback: {
      type: Function
    }
  },
  methods: {
    async goto(url, page) {
      if (page) {
        url = new URL(this.pagination['path']);
        url.searchParams.set('page', page)
        return;
      }
      else if (url) {
        let response = await axios.get(url);
        if (response.statusText === 'OK') {
          this.callback(response);
        }
        return 
      }
    }
  },
}
</script>
<template>
  <div class="pagination my-2 w-full text-black bottom-[10px] h-[30px] px-3 flex justify-center">
    <div v-if="$props.data" class="flex justify-center items-center bg-gray-100 rounded-lg px-2 w-[300px]">
      <button @click="goto($props.data['prev_page_url'])" class="material-symbols-outlined">navigate_before</button>
      <form @submit.prevent="goto('', $props.data['current_page'])" class="flex">
        <input v-model="$props.data['current_page']"
          class="w-2 text-sm border-none text-center bg-transparent font-bold" />
        <span class="font-bold text-sm"> of {{ $props.data['last_page'] }} </span>
      </form>
      <button @click="goto($props.data['next_page_url'])" class="material-symbols-outlined">navigate_next</button>
    </div>
  </div>
</template>