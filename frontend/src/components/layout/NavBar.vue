<template>
  <nav class="bg-white shadow-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo e menu principal -->
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <router-link to="/" class="text-2xl font-bold text-primary-600">
              EscolaOnline
            </router-link>
          </div>
          
          <!-- Menu desktop -->
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <router-link 
              to="/" 
              class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
              :class="{ 'border-primary-500 text-gray-900': $route.name === 'home' }"
            >
              Início
            </router-link>
            
            <router-link 
              to="/courses" 
              class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
              :class="{ 'border-primary-500 text-gray-900': $route.name === 'courses' }"
            >
              Cursos
            </router-link>
          </div>
        </div>
        
        <!-- Menu do usuário -->
        <div class="hidden sm:ml-6 sm:flex sm:items-center">
          <template v-if="isAuthenticated">
            <!-- Menu dropdown do usuário autenticado -->
            <div class="ml-3 relative">
              <div>
                <button 
                  @click="showUserMenu = !showUserMenu"
                  class="bg-white rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  aria-expanded="false"
                  aria-haspopup="true"
                >
                  <span class="sr-only">Abrir menu do usuário</span>
                  <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center">
                    <span class="text-sm font-medium text-white">
                      {{ userName.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </button>
              </div>
              
              <!-- Dropdown menu -->
              <div 
                v-show="showUserMenu"
                @click.away="showUserMenu = false"
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                role="menu"
                aria-orientation="vertical"
              >
                <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-200">
                  <p class="font-medium">{{ userName }}</p>
                  <p class="text-gray-500">{{ userEmail }}</p>
                </div>
                
                <router-link 
                  to="/dashboard" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                  @click="showUserMenu = false"
                >
                  Dashboard
                </router-link>
                
                <router-link 
                  to="/profile" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                  @click="showUserMenu = false"
                >
                  Perfil
                </router-link>
                
                <router-link 
                  to="/enrollments" 
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                  @click="showUserMenu = false"
                >
                  Minhas Matrículas
                </router-link>
                
                <template v-if="isAdmin">
                  <div class="border-t border-gray-200"></div>
                  <router-link 
                    to="/admin" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                    @click="showUserMenu = false"
                  >
                    Admin
                  </router-link>
                </template>
                
                <div class="border-t border-gray-200"></div>
                <button 
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors"
                >
                  Sair
                </button>
              </div>
            </div>
          </template>
          
          <!-- Botões para usuários não autenticados -->
          <template v-else>
            <router-link 
              to="/login" 
              class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Entrar
            </router-link>
            <router-link 
              to="/register" 
              class="btn-primary ml-3"
            >
              Cadastrar
            </router-link>
          </template>
        </div>
        
        <!-- Menu mobile -->
        <div class="sm:hidden flex items-center">
          <button 
            @click="showMobileMenu = !showMobileMenu"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
          >
            <span class="sr-only">Abrir menu principal</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Menu mobile dropdown -->
    <div v-show="showMobileMenu" class="sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <router-link 
          to="/" 
          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors"
          :class="$route.name === 'home' ? 'bg-primary-50 border-primary-500 text-primary-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
          @click="showMobileMenu = false"
        >
          Início
        </router-link>
        
        <router-link 
          to="/courses" 
          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors"
          :class="$route.name === 'courses' ? 'bg-primary-50 border-primary-500 text-primary-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
          @click="showMobileMenu = false"
        >
          Cursos
        </router-link>
      </div>
      
      <div class="pt-4 pb-3 border-t border-gray-200">
        <template v-if="isAuthenticated">
          <div class="flex items-center px-4">
            <div class="h-10 w-10 rounded-full bg-primary-600 flex items-center justify-center">
              <span class="text-sm font-medium text-white">
                {{ userName.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-gray-800">{{ userName }}</div>
              <div class="text-sm font-medium text-gray-500">{{ userEmail }}</div>
            </div>
          </div>
          
          <div class="mt-3 space-y-1">
            <router-link 
              to="/dashboard" 
              class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors"
              @click="showMobileMenu = false"
            >
              Dashboard
            </router-link>
            
            <router-link 
              to="/profile" 
              class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors"
              @click="showMobileMenu = false"
            >
              Perfil
            </router-link>
            
            <router-link 
              to="/enrollments" 
              class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors"
              @click="showMobileMenu = false"
            >
              Minhas Matrículas
            </router-link>
            
            <template v-if="isAdmin">
              <router-link 
                to="/admin" 
                class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors"
                @click="showMobileMenu = false"
              >
                Admin
              </router-link>
            </template>
            
            <button 
              @click="handleLogout"
              class="block w-full text-left px-4 py-2 text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50 transition-colors"
            >
              Sair
            </button>
          </div>
        </template>
        
        <template v-else>
          <div class="space-y-1">
            <router-link 
              to="/login" 
              class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors"
              @click="showMobileMenu = false"
            >
              Entrar
            </router-link>
            
            <router-link 
              to="/register" 
              class="block px-4 py-2 text-base font-medium text-primary-600 hover:text-primary-800 hover:bg-primary-50 transition-colors"
              @click="showMobileMenu = false"
            >
              Cadastrar
            </router-link>
          </div>
        </template>
      </div>
    </div>
  </nav>
</template>

<script>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'NavBar',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const showUserMenu = ref(false)
    const showMobileMenu = ref(false)
    
    const isAuthenticated = computed(() => authStore.isAuthenticated)
    const isAdmin = computed(() => authStore.isAdmin)
    const userName = computed(() => authStore.userName)
    const userEmail = computed(() => authStore.userEmail)
    
    const handleLogout = async () => {
      try {
        await authStore.logout()
        showUserMenu.value = false
        showMobileMenu.value = false
        router.push('/')
      } catch (error) {
        console.error('Erro ao fazer logout:', error)
      }
    }
    
    return {
      showUserMenu,
      showMobileMenu,
      isAuthenticated,
      isAdmin,
      userName,
      userEmail,
      handleLogout
    }
  }
}
</script>
