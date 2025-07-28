<template>
  <div class="relative">
    <!-- Cart Icon -->
    <button 
      @click="toggleCart"
      class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5.2A1 1 0 006.9 19H19"/>
      </svg>
      <span 
        v-if="cartStore.count > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
      >
        {{ cartStore.count }}
      </span>
    </button>

    <!-- Cart Dropdown -->
    <div 
      v-if="showCart"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 border"
    >
      <div class="p-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Carrinho</h3>
          <button 
            @click="showCart = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Cart Items -->
        <div v-if="cartStore.items.length > 0">
          <div 
            v-for="item in cartStore.items" 
            :key="item.id"
            class="flex items-center gap-3 p-3 border rounded-lg mb-3"
          >
            <div class="flex-1">
              <h4 class="font-medium text-sm">{{ item.course.name }}</h4>
              <p class="text-gray-600 text-xs">{{ formatCurrency(item.price) }}</p>
            </div>
            <button 
              @click="removeItem(item.id)"
              class="text-red-500 hover:text-red-700 p-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>

          <!-- Cart Total -->
          <div class="border-t pt-3 mt-4">
            <div class="flex justify-between items-center font-semibold">
              <span>Total:</span>
              <span>{{ formatCurrency(cartStore.total) }}</span>
            </div>
          </div>

          <!-- Cart Actions -->
          <div class="mt-4 space-y-2">
            <button 
              @click="goToCheckout"
              class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 font-medium"
            >
              Finalizar Compra
            </button>
            <button 
              @click="clearCart"
              class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 font-medium"
            >
              Limpar Carrinho
            </button>
          </div>
        </div>

        <!-- Empty Cart -->
        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5.2A1 1 0 006.9 19H19"/>
          </svg>
          <p class="text-gray-500">Seu carrinho está vazio</p>
        </div>
      </div>
    </div>

    <!-- Backdrop -->
    <div 
      v-if="showCart"
      @click="showCart = false"
      class="fixed inset-0 z-40"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'

const router = useRouter()
const cartStore = useCartStore()
const showCart = ref(false)

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const toggleCart = () => {
  showCart.value = !showCart.value
  if (showCart.value) {
    cartStore.loadCart()
  }
}

const removeItem = async (itemId) => {
  await cartStore.removeItem(itemId)
}

const clearCart = async () => {
  await cartStore.clearCart()
  showCart.value = false
}

const goToCheckout = () => {
  showCart.value = false
  router.push('/checkout')
}

onMounted(() => {
  cartStore.loadCart()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>