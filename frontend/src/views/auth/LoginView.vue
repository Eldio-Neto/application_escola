<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Entre na sua conta
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Ou
          <router-link 
            to="/register" 
            class="font-medium text-primary-600 hover:text-primary-500"
          >
            cadastre-se gratuitamente
          </router-link>
        </p>
      </div>

      <!-- Credenciais de Teste -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-3">🧪 Credenciais de Teste</h3>
        <div class="space-y-2">
          <div class="flex justify-between items-center">
            <div class="text-xs text-blue-800">
              <strong>Admin:</strong> admin@teste.com / 123456
            </div>
            <button 
              @click="fillCredentials('admin@teste.com', '123456')"
              class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Usar
            </button>
          </div>
          <div class="flex justify-between items-center">
            <div class="text-xs text-blue-800">
              <strong>Aluno:</strong> aluno@teste.com / 123456
            </div>
            <button 
              @click="fillCredentials('aluno@teste.com', '123456')"
              class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Usar
            </button>
          </div>
        </div>
        <div class="mt-2 text-xs text-blue-700">
          <strong>Dica:</strong> Clique em "Usar" para preencher automaticamente os campos
        </div>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div v-if="error" class="alert-danger">
          {{ error }}
        </div>
        
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">E-mail</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="Endereço de e-mail"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Senha</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
              placeholder="Senha"
            />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="form.remember"
              name="remember-me"
              type="checkbox"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Lembrar de mim
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
              Esqueceu sua senha?
            </a>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg
                v-if="loading"
                class="animate-spin h-5 w-5 text-primary-500"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg
                v-else
                class="h-5 w-5 text-primary-500 group-hover:text-primary-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
            </span>
            {{ loading ? 'Entrando...' : 'Entrar' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'LoginView',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const form = ref({
      email: '',
      password: '',
      remember: false
    })
    
    const loading = computed(() => authStore.loading)
    const error = computed(() => authStore.error)
    
    const handleLogin = async () => {
      try {
        authStore.clearError()
        await authStore.login({
          email: form.value.email,
          password: form.value.password
        })
        
        // Redirecionar baseado no papel do usuário
        const redirectTo = authStore.isAdmin ? '/admin' : '/dashboard'
        router.push(redirectTo)
      } catch (error) {
        console.error('Erro no login:', error)
      }
    }

    const fillCredentials = (email, password) => {
      form.value.email = email
      form.value.password = password
    }
    
    return {
      form,
      loading,
      error,
      handleLogin,
      fillCredentials
    }
  }
}
</script>
