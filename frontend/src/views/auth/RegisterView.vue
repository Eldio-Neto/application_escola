<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Registro -->
      <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
          <h2 class="text-3xl font-extrabold text-gray-900">
            Crie sua conta
          </h2>
          <p class="mt-2 text-sm text-gray-600">
            Ou
            <router-link 
              to="/login" 
              class="font-medium text-primary-600 hover:text-primary-500"
            >
              faça login na sua conta existente
            </router-link>
          </p>
        </div>
        
        <form class="space-y-6" @submit.prevent="handleRegister">
          <div v-if="error" class="alert-danger">
            {{ error }}
          </div>
          
          <div>
            <label for="name" class="form-label">Nome completo</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              required
              class="form-input"
              placeholder="Seu nome completo"
            />
          </div>

          <div>
            <label for="email" class="form-label">E-mail</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              required
              class="form-input"
              placeholder="seu@email.com"
            />
          </div>

          <div>
            <label for="password" class="form-label">Senha</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              required
              class="form-input"
              placeholder="Mínimo 8 caracteres"
            />
          </div>

          <div>
            <label for="password_confirmation" class="form-label">Confirmar senha</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              required
              class="form-input"
              placeholder="Digite a senha novamente"
            />
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary w-full"
            >
              {{ loading ? 'Criando conta...' : 'Criar conta' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'RegisterView',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const form = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    })
    
    const loading = computed(() => authStore.loading)
    const error = computed(() => authStore.error)
    
    const handleRegister = async () => {
      if (form.value.password !== form.value.password_confirmation) {
        authStore.error = 'As senhas não coincidem'
        return
      }
      
      try {
        authStore.clearError()
        await authStore.register(form.value)
        router.push('/dashboard')
      } catch (error) {
        console.error('Erro no registro:', error)
      }
    }
    
    return {
      form,
      loading,
      error,
      handleRegister
    }
  }
}
</script>
