<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <NavBar />
    <main>
      <RouterView />
    </main>
    <FooterComponent />
  </div>
</template>

<script>
import NavBar from '@/components/layout/NavBar.vue'
import FooterComponent from '@/components/layout/FooterComponent.vue'

export default {
  name: 'App',
  components: {
    NavBar,
    FooterComponent
  }
}
</script>
