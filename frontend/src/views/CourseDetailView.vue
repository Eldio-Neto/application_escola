<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Carregando curso...</p>
      </div>

      <!-- Course Details -->
      <div v-else-if="course" class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Course Header -->
        <div class="relative h-64 bg-gradient-to-r from-blue-600 to-purple-600">
          <div v-if="course.image" class="absolute inset-0">
            <img 
              :src="getImageUrl(course.image)" 
              :alt="course.name"
              class="w-full h-full object-cover opacity-50"
            />
          </div>
          <div class="absolute inset-0 bg-black bg-opacity-40"></div>
          <div class="relative z-10 h-full flex items-center justify-center text-white text-center p-6">
            <div>
              <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ course.name }}</h1>
              <p class="text-lg md:text-xl opacity-90">{{ course.description }}</p>
            </div>
          </div>
        </div>

        <!-- Course Info -->
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-gray-50 p-4 rounded-lg text-center">
              <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(course.price) }}</div>
              <div class="text-sm text-gray-600">Preço</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
              <div class="text-2xl font-bold text-green-600">
                {{ course.workload_hours ? `${course.workload_hours}h` : '-' }}
              </div>
              <div class="text-sm text-gray-600">Carga Horária</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
              <div class="text-2xl font-bold text-purple-600">
                {{ course.modules ? course.modules.length : 0 }}
              </div>
              <div class="text-sm text-gray-600">Módulos</div>
            </div>
          </div>

          <!-- Course Modules -->
          <div v-if="course.modules && course.modules.length > 0" class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Módulos do Curso</h2>
            <div class="space-y-4">
              <div 
                v-for="(module, index) in course.modules" 
                :key="index"
                class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
              >
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <span class="text-blue-600 font-semibold text-sm">{{ index + 1 }}</span>
                    </div>
                  </div>
                  <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ module.title }}</h3>
                    <p v-if="module.description" class="text-gray-600 leading-relaxed">
                      {{ module.description }}
                    </p>
                    <div v-else class="text-gray-400 italic">
                      Sem descrição disponível
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Modules Message -->
          <div v-else class="text-center py-8 mb-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Nenhum módulo cadastrado</h3>
            <p class="mt-2 text-gray-500">Este curso ainda não possui módulos definidos.</p>
          </div>

          <!-- Course Sessions -->
          <div v-if="course.sessions && course.sessions.length > 0" class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Sessões do Curso</h2>
            <div class="grid gap-4">
              <div 
                v-for="(session, index) in course.sessions" 
                :key="index"
                class="bg-gray-50 border border-gray-200 rounded-lg p-4"
              >
                <h3 class="font-semibold text-gray-900 mb-2">{{ session.title }}</h3>
                <p v-if="session.description" class="text-gray-600 mb-3">{{ session.description }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Início:</span>
                    <span class="ml-2 text-gray-600">{{ formatDateTime(session.start_datetime) }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Fim:</span>
                    <span class="ml-2 text-gray-600">{{ formatDateTime(session.end_datetime) }}</span>
                  </div>
                  <div v-if="session.max_participants">
                    <span class="font-medium text-gray-700">Máx. Participantes:</span>
                    <span class="ml-2 text-gray-600">{{ session.max_participants }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              @click="$router.go(-1)"
              class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium"
            >
              Voltar
            </button>
            <button
              @click="addToCart"
              class="px-8 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium"
              :disabled="!course.active || addingToCart"
            >
              {{ addingToCart ? 'Adicionando...' : 'Adicionar ao Carrinho' }}
            </button>
            <button
              @click="enrollInCourse"
              class="px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium"
              :disabled="!course.active"
            >
              {{ course.active ? 'Comprar Agora' : 'Curso Inativo' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900">Curso não encontrado</h3>
        <p class="mt-2 text-gray-500">O curso que você está procurando não existe ou foi removido.</p>
        <div class="mt-6">
          <button
            @click="$router.push('/')"
            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium"
          >
            Voltar à Página Inicial
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()

const loading = ref(true)
const course = ref(null)
const addingToCart = ref(false)

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const getImageUrl = (imagePath) => {
  if (!imagePath) return '/placeholder-course.jpg'
  if (imagePath.startsWith('http')) return imagePath
  return `http://localhost:8000/storage/${imagePath}`
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  const date = new Date(dateTime)
  return date.toLocaleString('pt-BR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadCourse = async () => {
  try {
    const response = await api.get(`/courses/${route.params.id}`)
    if (response.data.success) {
      course.value = response.data.data
    }
  } catch (error) {
    console.error('Erro ao carregar curso:', error)
  } finally {
    loading.value = false
  }
}

const addToCart = async () => {
  if (!course.value || addingToCart.value) return
  
  try {
    addingToCart.value = true
    const result = await cartStore.addItem(course.value.id)
    
    if (result.success) {
      alert('Curso adicionado ao carrinho!')
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert('Erro ao adicionar curso ao carrinho')
  } finally {
    addingToCart.value = false
  }
}

const enrollInCourse = () => {
  // Add to cart first, then go to checkout
  addToCart().then(() => {
    router.push('/checkout')
  })
}

onMounted(() => {
  loadCourse()
})
</script>

<style scoped>
/* Estilos personalizados se necessário */
</style>
