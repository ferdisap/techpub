<script>
import { join } from 'path';
import { bottomBarItems, col2, col3 } from '../route/ExplorerVue';

export default {
  data() {
    return {}
  },
  props: {
    items: {
      type: Object,
      defaults: {
        componentName: {
          iconName: '',
          tooltipName: '',
          isShow: '',
        }
      },
    },
    class: String,
  },
  methods: {
    click(componentName) {
      const show = this.$props.items[componentName]['isShow'];

      let bbi = this.$route.query.bbi; // ?bbi=History,Folder,Editor
      if (bbi) bbi = bbi.split(",");
      else bbi = [];
      (!show ? bbi.unshift(componentName) : bbi.splice(bbi.indexOf(componentName), 1));

      const query = Object.assign({},this.$route.query);
      query.bbi = bbi.join(",");
      // sengaja pakai timeout, karena jika tidak, misal saat clickFilename, route push url dan saat push maka ada kemungkinan sript ini dilakukan duluan atau bersamaan sehingga path/paramsnya tidak ada
      setTimeout(()=>{
        this.$router.replace({
          path: this.$route.path,
          query: query,
        });
      })

      // arrange column width
      this.hide(bbi);

      // sort view;
      this.sort(bbi, componentName);
      
      this.$props.items[componentName]['isShow'] = !this.$props.items[componentName]['isShow'];
    },
    sort(bbi, componentName) {
      setTimeout(() => {
        bbi = bbi.map(v => v = v.toLowerCase());
        const target = document.querySelector("." + componentName.toLowerCase());
        if(!target) return;
        const CSSSelector = Object.keys(this.$props.items).map(componentName => "." + componentName.toLowerCase()).join(", "); // ".folder .explorer .preview ...etc"
        const collection = [...document.querySelectorAll(CSSSelector)];
        collection.sort(function (a, b) {
          return bbi.indexOf(a.classList[0]) - bbi.indexOf(b.classList[0])
        }).forEach(function (item) {
          // if (item && (item.parentElement == target.parentElement))target.parentElement.appendChild(item);
          // sengaja setiap component di kasi parent div agar bisa di sort
          if (item && (item.parentElement.parentElement == target.parentElement.parentElement)){
            target.parentElement.parentElement.appendChild(item.parentElement);
          }
        })
      },1000);
    },
    hide(bbi){
      let hideCol2 = true;
      let hideCol3 = true;
      col2.forEach(componentName => {
        if (bbi.indexOf(componentName) >= 0) hideCol2 = false;
      })
      col3.forEach(componentName => {
        if (bbi.indexOf(componentName) >= 0) hideCol3 = false;
      })
      if(hideCol2 !== hideCol3){
        if (hideCol2) this.emitter.emit('Explorer-column-size', {colnum: 'tiga', 'size': 1});
        else if (hideCol3) this.emitter.emit('Explorer-column-size', {colnum: 'dua', 'size': 1});
      } else {
        this.emitter.emit('Explorer-column-size', {colnum: 'tiga', 'size': 0.5});
        this.emitter.emit('Explorer-column-size', {colnum: 'dua', 'size': 0.5});
      }      
    }
  },
  activated() {
    let bbi = this.$route.query.bbi;
    if (!bbi) return;
    bbi = bbi.split(",");
    for (let i = 0; i < bbi.length; i++) {
      if (this.$props.items[bbi[i]]) this.$props.items[bbi[i]]['isShow'] = true;
    }
    this.hide(bbi);
  },
  mounted() {

    window.bb = this;
    let emitters = this.emitter.all.get('bottom-bar-switch'); // 'emitter.length < 2' artinya emitter max. hanya dua kali di instance atau baru sekali di emit, check Explorer.vue
    if (emitters) {
      let indexEmitter = emitters.indexOf(emitters.find((v) => v.name === 'bound click')) // 'bound addObjects' adalah fungsi, lihat scrit dibawah ini. Jika fungsi anonymous, maka output = ''
      if (emitters.length < 1 && indexEmitter < 0) this.emitter.on('bottom-bar-switch', this.click.bind(this));
    } else this.emitter.on('bottom-bar-switch', this.click.bind(this));
  }

}
</script>

<template>
  <div
    :class="['mt-2 absolute flex h-max w-max  space-x-2 px-2 py-1 border-4 border-blue-500 bg-white rounded-xl', $props.class]">
    <div v-for="(item, componentName) in $props.items"
      :class="['relative h-max w-max flex items-center rounded-lg', item.isShow ? 'bg-blue-500' : 'bg-white']">
      <a @click.stop.prevent="click(componentName)" href="#"
        :class="['material-symbols-outlined bg-transparent  p-2 rounded-md has-tooltip-arrow', item.isShow ? 'text-white' : 'text-blue-500']"
        :data-tooltip="item.tooltipName">{{ item.iconName }}</a>
    </div>
  </div>
</template>