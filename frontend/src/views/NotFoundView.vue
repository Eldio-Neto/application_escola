<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto text-center">
      <!-- 404 Icon -->
      <div class="mb-8">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
      </div>

      <!-- 404 Title -->
      <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
      
      <!-- Error Message -->
      <h2 class="text-2xl font-semibold text-gray-700 mb-2">Página não encontrada</h2>
      <p class="text-gray-600 mb-8">
        Desculpe, a página que você está procurando não existe ou foi movida.
      </p>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <router-link 
          to="/" 
          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Voltar ao Início
        </router-link>
        
        <router-link 
          to="/courses" 
          class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
          Ver Cursos
        </router-link>
      </div>

      <!-- Helpful Links -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-sm text-gray-500 mb-4">Pode estar procurando por:</p>
        <div class="flex flex-wrap justify-center gap-2">
          <router-link 
            to="/login" 
            class="text-sm text-primary-600 hover:text-primary-800 underline"
          >
            Login
          </router-link>
          <span class="text-gray-300">•</span>
          <router-link 
            to="/register" 
            class="text-sm text-primary-600 hover:text-primary-800 underline"
          >
            Cadastro
          </router-link>
          <span class="text-gray-300">•</span>
          <router-link 
            to="/courses" 
            class="text-sm text-primary-600 hover:text-primary-800 underline"
          >
            Cursos
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFoundView',
  mounted() {
    // Log the 404 for analytics purposes (optional)
    console.log('404 Page accessed:', this.$route.fullPath)
  }
}
</script>