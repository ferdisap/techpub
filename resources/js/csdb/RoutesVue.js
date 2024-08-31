import Welcome from './components/route/Welcome.vue';
import Explorer from './components/route/Explorer.vue';
import Deletion from './components/route/Deletion.vue';
import Dispatch from './components/route/Dispatch.vue';
import Br from './components/route/Br.vue'

export default [
  {
    name: 'Welcome',
    path: '/csdb',
    component: Welcome
  },
  {
    name: 'Explorer',
    path: '/csdb/explorer/:filename?/:viewType?',
    component: Explorer
  },
  {
    name: 'Deletion',
    path: '/csdb/deletion',
    component: Deletion
  },
  {
    name: 'Dispatch',
    path: '/csdb/dispatch/:filename?',
    component: Dispatch
  },
  {
    name: 'Br',
    path: '/csdb/br/:filename?',
    component: Br
  }
];