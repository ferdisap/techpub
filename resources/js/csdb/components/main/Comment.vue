<script>
import Modal from '../subComponents/Modal.vue';
import { useTechpubStore } from '../../../techpub/techpubStore';
import { fetch, submit } from './CommentVue';
// import Randomstring from 'randomstring';
import CommentVueCb from './CommentsVueCb';
import ContextMenu from '../subComponents/ContextMenu.vue';

export default {
  data() {
    return {
      techpubStore: useTechpubStore(),
      comments: {
        csdbFilename: '',
        template: '<div></div>',
        modalId: "modalCommentVue",
        cbHomeId: "cbCommentId",
        cmId: "cmCommentId",
        CB: {},
      },
    }
  },
  props: ['csdbFilename'],
  components: { Modal, ContextMenu },
  computed: {
    listComment() {
      this.fetch(this.$props.csdbFilename, false);
      return {
        template: this.comments.template,
      }
    }
  },
  methods: {
    fetch: fetch,
    submit: submit,
  },
  mounted() {
    this.comments.CB = new CommentVueCb(this.comments.cbHomeId);
    // this.ContextMenu.register(this.comments.cmId);
  }
}
</script>

<!-- template diletakkan didalam Preview.vue -->
<template>
  <div class="comment mt-2">
    <!-- Associated Comment List -->
    <div class="comment-list">
      <h5>Comments</h5>
      <form :id="comments.cbHomeId" @submit.prevent="submit">
        <component :is="listComment" v-if="$props.csdbFilename && comments.template" />
      </form>
    </div>
    <!-- modal preferences -->
    <Modal :id="comments.modalId">
      <template #title>
        <h1 class="text-center font-bold mb-2 text-lg">Submit Preferences</h1>
      </template>
      <input class="text-sm hidden" modal-input-name="parentCommentFilename" name="parentCommentFilename" value="" />

      <!-- <input class="text-sm hidden" modal-input-name="previousCommentFilename" name="previousCommentFilename" value="" /> -->
      <input class="text-sm hidden" modal-input-name="position" name="position" value="" />
      <input class="text-sm hidden" modal-input-name="commentType" name="commentType" value="" />

      <div class="w-full text-center mb-2 relative">
        <!-- comment title -->
        <div class="relative text-left mb-2">
          <label class="italic font-semibold ml-1">Title:</label>
          <input placeholder="comment title" type="text" class="p-2 w-full ml-1 inline text-sm rounded-md border">
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('commentTitle')"></div>
        <div class="flex items-center mt-1 text-left mb-2">
          <div class="w-1/2 mr-1">
            <label for="languageIsoCode" class="text-sm mr-2 font-semibold italic">Lang:</label>
            <input modal-input-name="languageIsoCode" name="languageIsoCode" id="languageIsoCode"
              class="mr-2 p-2 rounded-md w-full" value="en" />
          </div>
          <div class="w-1/2 ml-1">
            <label for="countryIsoCode" class="text-sm mr-2 font-semibold italic">Country:</label>
            <input modal-input-name="countryIsoCode" name="countryIsoCode" id="countryIsoCode"
              class="p-2 rounded-md w-full" value="US" />
          </div>
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('languageIsoCode')"></div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('countryIsoCode')"></div>
        <!-- comment BREX -->
        <div class="relative text-left mb-2">
          <label class="italic font-semibold ml-1">BREX:</label>
          <input placeholder="DMC..." type="text" class="p-2 w-full ml-1 inline text-sm rounded-md border"
            :value="techpubStore.currentObjectModel.brexDmRef" modal-input-name="brexDmRef" name="brexDmRef"
            dd-input="filename,path" dd-type="csdbs" dd-route="api.get_object_csdbs" dd-target="self">
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('brexDmRef')"></div>
        <!-- comment security class  -->
        <div class="flex items-center mb-2">
          <label for="securityClassification" class="italic font-semibold ml-1 mr-2">Security Classification:</label>
          <input modal-input-name="securityClassification" name="securityClassification" id="securityClassification"
            placeholder="eg:. 05" value="01" class="w-[50px] p-2" type="number" min="1" max="5" step="1"
            onchange="if(parseInt(this.value,10)<10)this.value='0'+this.value;" />
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('securityClassification')"></div>
        <!-- comment type -->
        <div class="flex items-center mb-2">
          <label for="commentPriorityCode" class="italic text-sm font-semibold ml-1 mr-2">Priority:</label>
          <select id="commentPriorityCode" modal-input-name="commentPriorityCode" name="commentPriorityCode"
            class="p-2 rounded-md">
            <option class="text-sm" value="cp01">Routine</option>
            <option class="text-sm" value="cp02">Emergency</option>
            <option class="text-sm" value="cp03">Safety critical</option>
          </select>
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('commentPriorityCode')"></div>
        <!-- response type -->
        <div class="flex items-center mb-2">
          <label for="responseType" class="italic text-sm font-semibold ml-1 mr-2">Response:</label>
          <select id="responseType" modal-input-name="responseType" name="responseType" class="p-2 rounded-md">
            <option class="text-sm" value="rt01">Accepted</option>
            <option class="text-sm" value="rt02">Pending</option>
            <option class="text-sm" value="rt03">Partly rejected</option>
            <option class="text-sm" value="rt04">Rejected</option>
          </select>
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('responseType')"></div>
        <!-- commentRefs, nanti ini pakai filename sesuai route atau sesuai preview -->
        <div class="relative text-left mb-2">
          <label for="commentRefs" class="italic text-sm font-semibold ml-1 mr-2">Comment Refs:</label>
          <input class="block p-2 w-full ml-1 rounded-md" :value="$route.params.filename" dd-input="filename,path"
            dd-type="csdbs" dd-route="api.get_object_csdbs" dd-target="self-append" modal-input-name="commentRefs"
            name="commentRefs" />
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('commentRefs')"></div>
        <!-- commentRemarks -->
        <div class="relative text-left mb-2">
          <label class="italic text-sm font-semibold ml-1 mr-2">Remarks:</label>
          <text-editor v-pre modal-input-name="commentRemarks[]" name="commentRemarks[]" class="w-full" />
        </div>
        <div class="error text-sm text-red-600 text-left" v-html="techpubStore.error('commentRemarks')"></div>
      </div>
    </Modal>
    <!-- </form> -->

    <ContextMenu :id="comments.cmId">
      <div @click.stop.prevent="comments.CB.createNewComment(comments.modalId)"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">create comment</div>
      </div>
      <div @click.stop.prevent="comments.CB.reply(comments.modalId)"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">reply</div>
      </div>
      <div v-if="comments.CB.containerEditorId"
        @click.stop.prevent="emitter.emit('Modal-show', { modalId: comments.modalId })"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">preferences</div>
      </div>
      <div v-if="comments.CB.containerEditorId" @click.stop.prevent="comments.CB.cancel"
        class="flex hover:bg-gray-100 py-1 px-2 rounded cursor-pointer text-gray-900">
        <div class="text-sm">cancel</div>
      </div>
    </ContextMenu>
  </div>
</template>