<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center mb-4">
      <svg class="w-8 h-8 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
      </svg>
      <h3 class="text-xl font-semibold text-gray-900">Pagamento via PIX</h3>
    </div>

    <form @submit.prevent="processPixPayment" v-if="!pixGenerated">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h4 class="text-sm font-medium text-blue-800">PIX - Pagamento Instantâneo</h4>
            <p class="text-sm text-blue-700 mt-1">
              O PIX é gratuito, instantâneo e disponível 24 horas por dia, 7 dias por semana.
              Após o pagamento, sua matrícula será ativada automaticamente.
            </p>
          </div>
        </div>
      </div>

      <div class="mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Curso:</span>
            <span class="font-semibold">{{ course.title }}</span>
          </div>
          <div class="flex justify-between items-center mt-2">
            <span class="text-gray-600">Valor:</span>
            <span class="text-2xl font-bold text-green-600">
              R$ {{ formatPrice(course.price) }}
            </span>
          </div>
        </div>
      </div>

      <button
        type="submit"
        :disabled="processing"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        <div v-if="processing" class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Gerando PIX...
        </div>
        <div v-else class="flex items-center justify-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
          </svg>
          Gerar PIX
        </div>
      </button>
    </form>

    <!-- PIX Gerado -->
    <div v-if="pixGenerated" class="space-y-6">
      <div class="text-center">
        <div class="bg-green-100 rounded-full p-3 w-16 h-16 mx-auto mb-4">
          <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">PIX gerado com sucesso!</h3>
        <p class="text-gray-600 text-sm">
          Use o QR Code ou o código PIX Copia e Cola para realizar o pagamento
        </p>
      </div>

      <!-- QR Code -->
      <div v-if="pixData.pix_qr_code_image" class="text-center">
        <h4 class="font-semibold text-gray-900 mb-3">QR Code PIX</h4>
        <div class="bg-white p-4 rounded-lg border inline-block">
          <img 
            :src="pixData.pix_qr_code_image" 
            alt="QR Code PIX"
            class="w-48 h-48 mx-auto"
          />
        </div>
        <p class="text-sm text-gray-600 mt-2">
          Abra o app do seu banco e escaneie o QR Code
        </p>
      </div>

      <!-- PIX Copia e Cola -->
      <div v-if="pixData.pix_copy_paste" class="bg-gray-50 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-3">PIX Copia e Cola</h4>
        <div class="flex items-center space-x-2">
          <input
            type="text"
            :value="pixData.pix_copy_paste"
            readonly
            class="flex-1 bg-white border border-gray-300 rounded-md px-3 py-2 text-sm font-mono"
          />
          <button
            @click="copyPixCode"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
              <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
            </svg>
          </button>
        </div>
        <p class="text-sm text-gray-600 mt-2">
          Cole este código no seu aplicativo bancário
        </p>
      </div>

      <!-- Status do Pagamento -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h4 class="text-sm font-medium text-yellow-800">Aguardando Pagamento</h4>
            <p class="text-sm text-yellow-700 mt-1">
              Assim que o pagamento for confirmado, você receberá um email de confirmação
              e terá acesso imediato ao curso.
            </p>
          </div>
        </div>
      </div>

      <!-- Ações -->
      <div class="flex space-x-4">
        <button
          @click="checkPaymentStatus"
          :disabled="checkingStatus"
          class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <div v-if="checkingStatus" class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Verificando...
          </div>
          <span v-else>Verificar Pagamento</span>
        </button>
        
        <button
          @click="generateNewPix"
          class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
        >
          Gerar Novo PIX
        </button>
      </div>
    </div>

    <!-- Mensagens de Erro -->
    <div v-if="errorMessage" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="ml-3">
          <h4 class="text-sm font-medium text-red-800">Erro no Pagamento PIX</h4>
          <p class="text-sm text-red-700 mt-1">{{ errorMessage }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { usePaymentStore } from '@/stores/payment'

export default {
  name: 'PixPayment',
  props: {
    course: {
      type: Object,
      required: true
    }
  },
  emits: ['payment-success', 'payment-error'],
  setup(props, { emit }) {
    const paymentStore = usePaymentStore()
    
    const processing = ref(false)
    const checkingStatus = ref(false)
    const pixGenerated = ref(false)
    const errorMessage = ref('')
    
    const pixData = reactive({
      payment_id: null,
      pix_qr_code: null,
      pix_qr_code_image: null,
      pix_copy_paste: null
    })

    const formatPrice = (price) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(price)
    }

    const processPixPayment = async () => {
      processing.value = true
      errorMessage.value = ''

      try {
        const response = await paymentStore.processPixPayment({
          course_id: props.course.id
        })

        if (response.success !== false) {
          // PIX gerado com sucesso
          pixGenerated.value = true
          pixData.payment_id = response.payment?.id
          pixData.pix_qr_code = response.pix_qr_code
          pixData.pix_qr_code_image = response.pix_qr_code_image
          pixData.pix_copy_paste = response.pix_copy_paste
          
          emit('payment-success', response)
        } else {
          throw new Error(response.message || 'Erro na geração do PIX')
        }
      } catch (error) {
        console.error('Erro no pagamento PIX:', error)
        errorMessage.value = error.message || 'Erro interno na geração do PIX'
        emit('payment-error', error)
      } finally {
        processing.value = false
      }
    }

    const copyPixCode = async () => {
      try {
        await navigator.clipboard.writeText(pixData.pix_copy_paste)
        // Mostrar feedback visual
        const button = event.target.closest('button')
        const originalContent = button.innerHTML
        button.innerHTML = `
          <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>
        `
        setTimeout(() => {
          button.innerHTML = originalContent
        }, 2000)
      } catch (error) {
        console.error('Erro ao copiar código PIX:', error)
        errorMessage.value = 'Erro ao copiar código PIX'
      }
    }

    const checkPaymentStatus = async () => {
      if (!pixData.payment_id) return

      checkingStatus.value = true
      
      try {
        const response = await paymentStore.checkPaymentStatus(pixData.payment_id)
        
        if (response.status === 'paid') {
          emit('payment-success', response)
        } else if (response.status === 'cancelled' || response.status === 'failed') {
          emit('payment-error', { message: 'Pagamento não foi aprovado' })
        }
      } catch (error) {
        console.error('Erro ao verificar status:', error)
        errorMessage.value = 'Erro ao verificar status do pagamento'
      } finally {
        checkingStatus.value = false
      }
    }

    const generateNewPix = () => {
      pixGenerated.value = false
      Object.keys(pixData).forEach(key => pixData[key] = null)
      errorMessage.value = ''
    }

    return {
      processing,
      checkingStatus,
      pixGenerated,
      errorMessage,
      pixData,
      formatPrice,
      processPixPayment,
      copyPixCode,
      checkPaymentStatus,
      generateNewPix
    }
  }
}
</script>

<style scoped>
/* Animações personalizadas se necessário */
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>
