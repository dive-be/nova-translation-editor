Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'nova-translation-editor',
      path: '/translation-editor',
      component: require('./components/TranslationList'),
    },
  ])
})
