<script>

export default {
  data() {
    return {}
  },
  props: ['function', 'emitname'],
  methods: {
    sortDefault() {
      const getCellValue = function (row, index) {
        return $(row).children('td').eq(index).text();
      };
      const comparer = function (index) {
        return function (a, b) {
          let valA = getCellValue(a, index), valB = getCellValue(b, index);
          return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
        }
      };
      let table = $(event.target).parents('table').eq(0);
      let th = $(event.target).parents('th').eq(0);
      let rows = table.find('tr:gt(0)').toArray().sort(comparer(th.index()));
      this.asc = !this.asc;
      if (!this.asc) {
        rows = rows.reverse();
      }
      for (let i = 0; i < rows.length; i++) {
        table.append(rows[i]);
      }
      if(this.$props.emitname){
        this.emitter.emit(this.$props.emitname);
      }
    },
  },
}
</script>
<template>
  <!-- <a href="javascript:void(0)" @click="$props.function ? $props.function : sort()"><span class="material-icons text-sm ">swap_vert</span></a> -->
  <button v-if="$props.function" @click="$props.function" type="button" class="has-tooltip-arrow" data-tooltip="Sort"><span class="material-icons text-sm">swap_vert</span></button>
  <button v-else @click="sortDefault" type="button" class="has-tooltip-arrow" data-tooltip="Sort"><span class="material-icons text-sm ">swap_vert</span></button>
</template>