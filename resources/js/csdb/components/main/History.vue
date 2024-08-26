<script>
import axios from 'axios';
import { useTechpubStore } from '../../../techpub/techpubStore';
import Pagination from '../subComponents/Pagination.vue';
import ContinuousLoadingCircle from '../../loadingProgress/ContinuousLoadingCircle.vue';

export default {
  // props: ["filename", "history"],
  props: {
    filename: {
      type: String
    },
    history: {
      type: Object
    }
  },
  data() {
    return {
      techpubStore: useTechpubStore(),
      histories: {},
      pagination: {},
      showLoadingProgress: false,
    }
  },
  components:{ Pagination, ContinuousLoadingCircle },
  methods: {
    async fetchHistories(filename) {
      this.showLoadingProgress = true;
      let response = await axios({
        route: {
          name: 'api.get_csdb_history',
          data: { filename: filename }
        }
      })
      if (response.statusText === 'OK') {
        this.storingResponse(response);
      }
      this.showLoadingProgress = false;
    },
    storingResponse(response){
      this.histories = response.data.csdb.histories.data;
      delete response.data.csdb.histories.data;
      this.pagination = response.data.csdb.histories;
    }
  },
  mounted() {
    if (this.$route.params.filename) this.fetchHistories(this.$route.params.filename);
  },
}
</script>
<template>
  <div data-sort="3" class="history">
    <div class="h-[5%] flex mb-3">
      <h1>History Log</h1>
    </div>
    <table class="text-left">
      <thead>
        <tr>
          <th class="text-sm w-[5%]">No</th>
          <th class="text-sm w-[20%]">Code</th>
          <th class="text-sm">Message</th>
          <th class="text-sm w-[20%]">Date/Time</th>
        </tr>
      </thead>
      <tr v-for="(history, no) in histories">
        <td class="text-sm">{{ no + 1 }}</td>
        <td class="text-sm">{{ history.code }}</td>
        <td class="text-sm">{{ history.description }}</td>
        <td class="text-sm">{{ techpubStore.date(history.created_at) }}</td>
      </tr>
    </table>

    <Pagination :callback="storingResponse" :data="pagination"/>

    <ContinuousLoadingCircle :show="showLoadingProgress" />
  </div>
</template>