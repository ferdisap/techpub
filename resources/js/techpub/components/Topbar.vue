<script>
import axios from 'axios';
import { useTechpubStore } from '../../techpub/techpubStore'
import { mount } from '@vue/test-utils';

export default {
  data() {
    return {
      techpubStore: useTechpubStore(),
      openDropdown: false
    }
  },
  methods: {
    // logout(params = {}){
    //   let route = this.techpubStore.getWebRoute('logout', params);
    //   window.router = this.$router;
    //   axios({
    //     url: route.url,
    //     method: route.method[0],
    //     data: route.params,
    //   })
    //   .then( response => window.location.href ="/")
    //   .catch( e => this.$root.error(e));
    // }
  },
}
</script>

<template>
  <nav class="bg-blue-500 text-white h-full relative flex justify-between items-center">
    <div class="pl-3 text-sm flex">
      <a href="/" class="text-sm">Technical Publication</a>

      <div class="ml-3 dropdown text-sm relative" v-if="techpubStore.Auth.first_name">
        <button @click="openDropdown = !openDropdown" class="text-sm">
          <span class="text-xs">{{ techpubStore.Auth.first_name }}</span> <span class="material-symbols-outlined align-middle text-sm ">keyboard_arrow_down</span>
        </button>
        <div v-show="openDropdown" class="dropdown-content text-black absolute z-[100] bg-white p-2 rounded-sm shadow-md">
          <a class="block w-full text-sm" href="/csdb4">CSDB</a>
          <hr />
          <a class="block w-full text-sm" href="/logout">Logout</a>
        </div>
      </div>
      <!-- <a class="ml-3" v-else :href="techpubStore.getWebRoute('login')['path']"> login here..</a> -->
    </div>

    <div class="flex text-sm">
      <a href="#" class="text-sm mx-2">Home</a>
      <a href="#" class="text-sm mx-2">About</a>
      <a href="#" class="text-sm mx-2">Contact</a>
    </div>

    <form action="" method="GET">
      <div class="flex text-sm h-[90%]">
        <input type="text" id="search" name="search"
          class="block w-full p-0 px-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
          placeholder="search..">
        <button class="shadow-lg text-xs text-white font-semibold rounded-lg ml-2 px-5 py-1 bg-slate-500 hover:bg-slate-600 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 ">
          Search
        </button>
        <!-- <input type="token" id="token" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required placeholder="search..">
        <button type="submit" @click="getRepo" class="shadow-lg text-white font-semibold rounded-lg ml-2 px-5 py-1 bg-slate-500 hover:bg-slate-600 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 ">Search</button> -->
      </div>
    </form>
  </nav>
</template>