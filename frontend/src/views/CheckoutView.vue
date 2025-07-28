<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        
        <!-- Header -->
        <div class="bg-blue-600 text-white p-6">
          <h1 class="text-2xl font-bold">Finalizar Compra</h1>
          <p class="text-blue-100 mt-1">Complete sua compra em poucos passos</p>
        </div>

        <div class="p-6">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-gray-600">Carregando...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="text-center py-12">
            <div class="text-red-500 mb-4">
              <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Ops! Algo deu errado</h3>
            <p class="text-gray-600 mb-4">{{ error }}</p>
            <button 
              @click="loadCartSummary"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
            >
              Tentar Novamente
            </button>
          </div>

          <!-- Checkout Form -->
          <div v-else-if="cartSummary">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              
              <!-- Form Section -->
              <div class="lg:col-span-2">
                <form @submit.prevent="processPayment">
                  
                  <!-- Customer Information -->
                  <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Dados Pessoais</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                        <input 
                          v-model="form.customer.name"
                          type="text" 
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input 
                          v-model="form.customer.email"
                          type="email" 
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input 
                          v-model="form.customer.phone"
                          type="tel"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                        <input 
                          v-model="form.customer.cpf"
                          type="text"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                      </div>
                    </div>

                    <!-- Create Account Option -->
                    <div class="mt-4">
                      <label class="flex items-center">
                        <input 
                          v-model="form.customer.create_account"
                          type="checkbox"
                          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Criar conta para acessar os cursos</span>
                      </label>
                      
                      <div v-if="form.customer.create_account" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                        <input 
                          v-model="form.customer.password"
                          type="password"
                          :required="form.customer.create_account"
                          minlength="8"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <p class="text-xs text-gray-500 mt-1">Mínimo de 8 caracteres</p>
                      </div>
                    </div>
                  </div>

                  <!-- Coupon Section -->
                  <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Cupom de Desconto</h2>
                    <div class="flex gap-2">
                      <input 
                        v-model="couponCode"
                        type="text"
                        placeholder="Digite o código do cupom"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                      <button 
                        type="button"
                        @click="applyCoupon"
                        :disabled="!couponCode || applyingCoupon"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        {{ applyingCoupon ? 'Aplicando...' : 'Aplicar' }}
                      </button>
                    </div>

                    <!-- Applied Coupon -->
                    <div v-if="appliedCoupon" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                      <div class="flex items-center justify-between">
                        <div>
                          <p class="font-medium text-green-800">{{ appliedCoupon.coupon.name }}</p>
                          <p class="text-sm text-green-600">Desconto: {{ formatCurrency(appliedCoupon.discount) }}</p>
                        </div>
                        <button 
                          type="button"
                          @click="removeCoupon"
                          class="text-red-500 hover:text-red-700"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Payment Method -->
                  <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Forma de Pagamento</h2>
                    
                    <div class="space-y-3 mb-4">
                      <label class="flex items-center">
                        <input 
                          v-model="form.payment_method"
                          type="radio"
                          value="credit_card"
                          class="text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2">Cartão de Crédito</span>
                      </label>
                      <label class="flex items-center">
                        <input 
                          v-model="form.payment_method"
                          type="radio"
                          value="pix"
                          class="text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2">PIX</span>
                      </label>
                      <label class="flex items-center">
                        <input 
                          v-model="form.payment_method"
                          type="radio"
                          value="boleto"
                          class="text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2">Boleto Bancário</span>
                      </label>
                    </div>

                    <!-- Credit Card Fields -->
                    <div v-if="form.payment_method === 'credit_card'" class="space-y-4">
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Número do Cartão *</label>
                          <input 
                            v-model="form.card.number"
                            type="text"
                            required
                            placeholder="1234 5678 9012 3456"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          >
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Nome no Cartão *</label>
                          <input 
                            v-model="form.card.holder_name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          >
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Validade *</label>
                          <div class="grid grid-cols-2 gap-2">
                            <input 
                              v-model="form.card.expiry_month"
                              type="text"
                              required
                              placeholder="MM"
                              maxlength="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <input 
                              v-model="form.card.expiry_year"
                              type="text"
                              required
                              placeholder="AAAA"
                              maxlength="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                          </div>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">CVV *</label>
                          <input 
                            v-model="form.card.cvv"
                            type="text"
                            required
                            maxlength="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          >
                        </div>
                      </div>

                      <!-- Installments -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parcelas</label>
                        <select 
                          v-model="form.installments"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                          <option value="1">1x de {{ formatCurrency(finalTotal) }}</option>
                          <option value="2">2x de {{ formatCurrency(finalTotal / 2) }}</option>
                          <option value="3">3x de {{ formatCurrency(finalTotal / 3) }}</option>
                          <option value="6">6x de {{ formatCurrency(finalTotal / 6) }}</option>
                          <option value="12">12x de {{ formatCurrency(finalTotal / 12) }}</option>
                        </select>
                      </div>
                    </div>

                    <!-- Boleto Due Date -->
                    <div v-if="form.payment_method === 'boleto'">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Vencimento</label>
                      <input 
                        v-model="form.due_date"
                        type="date"
                        :min="tomorrow"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                    </div>
                  </div>

                  <!-- Submit Button -->
                  <div class="text-center">
                    <button 
                      type="submit"
                      :disabled="processing"
                      class="w-full md:w-auto px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ processing ? 'Processando...' : `Finalizar Compra - ${formatCurrency(finalTotal)}` }}
                    </button>
                  </div>
                </form>
              </div>

              <!-- Order Summary -->
              <div class="lg:col-span-1">
                <div class="bg-gray-50 p-6 rounded-lg sticky top-6">
                  <h2 class="text-xl font-semibold mb-4">Resumo do Pedido</h2>
                  
                  <!-- Items -->
                  <div class="space-y-3 mb-4">
                    <div 
                      v-for="item in cartSummary.items" 
                      :key="item.id"
                      class="flex justify-between items-start"
                    >
                      <div class="flex-1">
                        <h3 class="font-medium text-sm">{{ item.course.name }}</h3>
                        <p class="text-xs text-gray-600">{{ item.course.workload_hours }}h</p>
                      </div>
                      <span class="text-sm font-medium">{{ formatCurrency(item.price) }}</span>
                    </div>
                  </div>

                  <!-- Totals -->
                  <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                      <span>Subtotal:</span>
                      <span>{{ formatCurrency(cartSummary.subtotal) }}</span>
                    </div>
                    <div v-if="appliedCoupon" class="flex justify-between text-sm text-green-600">
                      <span>Desconto:</span>
                      <span>-{{ formatCurrency(appliedCoupon.discount) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-lg border-t pt-2">
                      <span>Total:</span>
                      <span>{{ formatCurrency(finalTotal) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import api from '@/services/api'

const router = useRouter()
const cartStore = useCartStore()

const loading = ref(true)
const error = ref(null)
const processing = ref(false)
const cartSummary = ref(null)
const couponCode = ref('')
const appliedCoupon = ref(null)
const applyingCoupon = ref(false)

const form = ref({
  customer: {
    name: '',
    email: '',
    phone: '',
    cpf: '',
    create_account: false,
    password: ''
  },
  payment_method: 'credit_card',
  gateway: 'asaas',
  card: {
    number: '',
    holder_name: '',
    expiry_month: '',
    expiry_year: '',
    cvv: ''
  },
  installments: 1,
  due_date: '',
  coupon_code: ''
})

const finalTotal = computed(() => {
  if (!cartSummary.value) return 0
  const subtotal = cartSummary.value.subtotal
  const discount = appliedCoupon.value ? appliedCoupon.value.discount : 0
  return Math.max(0, subtotal - discount)
})

const tomorrow = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 1)
  return date.toISOString().split('T')[0]
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const loadCartSummary = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await api.get('/guest/cart/summary', {
      headers: cartStore.getHeaders()
    })
    
    if (response.data.success) {
      cartSummary.value = response.data.data
    } else {
      error.value = response.data.message
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao carregar carrinho'
  } finally {
    loading.value = false
  }
}

const applyCoupon = async () => {
  if (!couponCode.value.trim()) return
  
  try {
    applyingCoupon.value = true
    const result = await cartStore.applyCoupon(couponCode.value)
    
    if (result.success) {
      appliedCoupon.value = result.data
      form.value.coupon_code = couponCode.value
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert('Erro ao aplicar cupom')
  } finally {
    applyingCoupon.value = false
  }
}

const removeCoupon = () => {
  appliedCoupon.value = null
  couponCode.value = ''
  form.value.coupon_code = ''
}

const processPayment = async () => {
  try {
    processing.value = true
    
    // Prepare cart items for payment
    const cartItems = cartSummary.value.items.map(item => ({
      course_id: item.course_id,
      price: item.price
    }))
    
    const paymentData = {
      ...form.value,
      cart_items: cartItems
    }
    
    if (appliedCoupon.value) {
      paymentData.coupon_code = appliedCoupon.value.coupon.code
    }
    
    const response = await api.post('/guest/payment/process', paymentData, {
      headers: cartStore.getHeaders()
    })
    
    if (response.data.success) {
      // Clear cart after successful payment
      await cartStore.clearCart()
      
      // Redirect to success page or show success message
      alert('Pagamento processado com sucesso!')
      router.push('/')
    } else {
      alert(response.data.message)
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Erro ao processar pagamento'
    alert(message)
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  // Set default due date for boleto
  form.value.due_date = tomorrow.value
  loadCartSummary()
})
</script>

<style scoped>
/* Additional styles if needed */
</style>