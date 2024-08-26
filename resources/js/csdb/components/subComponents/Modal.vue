<script>
import Modal from '../../Modal.js';

export default {
  data() {
    return {
      
    }
  },
  props: {
    id: {type: String},
  },
  methods: {
    // async start(){
    //   this.Modal = new Modal('modal_dmlEntry_form',document.querySelector(".dmlEntry"));
    // },
    ok(){
      this.Modal.button(true);
    },
    cancel(){
      this.Modal.button(false);
    },
    show(data = {}){
      this.Modal.start(
      (data.referer ? data.referer : undefined), 
      (data.modalId ? data.modalId : this.$props.id)
      );
    }
  },
  mounted() {
    this.Dropdown.register(this.$props.id);
    this.Modal.register(this.$props.id);

    // dari Listtree via Explorer/Management data data berisi path doang,
    // seharusnya kalau pakai emitter, tidak boleh pakai v-if karena 'this' pada calback merujuk pada context yang berbeda dengan context ini
    // let emitters = this.emitter.all.get('Modal-show'); // 'emitter.length < 2' artinya emitter max. hanya dua kali di instance atau baru sekali di emit, check ManagementData.vue
    // if (emitters) {
    //   let indexEmitter = emitters.indexOf(emitters.find((v) => v.name === 'bound show')) // 'bound addObjects' adalah fungsi, lihat scrit dibawah ini. Jika fungsi anonymous, maka output = ''
    //   if (emitters.length < 1 && indexEmitter < 0) this.emitter.on('Modal-show', this.show);
    // } else 
    this.emitter.on('Modal-show', this.show)
  },
}
</script>
<template>
  <div :id="$props.id" v-show="Modal.getShow($props.id)" class="fixed top-0 left-0 w-full h-full z-[100]">
    <div class="fixed top-0 left-0 w-full h-full bg-black opacity-50"></div>
    <div class="absolute top-[20%] w-[80%] h-max-[70%] left-[10%] border-8 border-black rounded-lg p-8 bg-slate-200">      
      <slot name="title">
        <h1 class="text-center font-bold mb-2">Modal</h1>
      </slot>
      
      <slot/>
  
  
      <slot name="button_ok">
        <button modal-button="ok" type="button" class="button bg-violet-400 text-white hover:bg-violet-600">OK</button>
      </slot>
      <slot name="button_not_ok">
        <button modal-button="not_ok" type="button" class="button bg-red-400 text-white hover:bg-red-600">Cancel</button>
      </slot>
    </div>
  </div>
</template>