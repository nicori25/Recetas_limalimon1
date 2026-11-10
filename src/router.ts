import { createRouter, createWebHashHistory } from 'vue-router'
export const router = createRouter({
  history: createWebHashHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    {
      path: '/',
      component: () => import('./views/ExploreView.vue'),
      meta: { title: 'Descubrí recetas' },
    },
    {
      path: '/receta/:id',
      component: () => import('./views/RecipeView.vue'),
      meta: { title: 'A cocinar' },
    },
    {
      path: '/publicar',
      component: () => import('./views/EditorView.vue'),
      meta: { title: 'Compartí tu receta' },
    },
    {
      path: '/editar/:id',
      component: () => import('./views/EditorView.vue'),
      meta: { title: 'Editá tu receta' },
    },
    {
      path: '/favoritos',
      component: () => import('./views/CollectionView.vue'),
      meta: { title: 'Tus favoritos' },
    },
    {
      path: '/mis-recetas',
      component: () => import('./views/CollectionView.vue'),
      meta: { title: 'Mis recetas' },
    },
    {
      path: '/ruleta',
      component: () => import('./views/RouletteView.vue'),
      meta: { title: 'Ruleta de temporada' },
    },
    {
      path: '/compras',
      component: () => import('./views/ShoppingView.vue'),
      meta: { title: 'Lista de compras' },
    },
    {
      path: '/cuenta',
      component: () => import('./views/AccountView.vue'),
      meta: { title: 'Tu cuenta' },
    },
    {
      path: '/admin',
      component: () => import('./views/AdminView.vue'),
      meta: { title: 'Administración' },
    },
    {
      path: '/:pathMatch(.*)*',
      component: () => import('./views/NotFoundView.vue'),
      meta: { title: 'Página no encontrada' },
    },
  ],
})
router.afterEach((to) => {
  document.title = `${to.meta.title} · LimaLimón`
})
