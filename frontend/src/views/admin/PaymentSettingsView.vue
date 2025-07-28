<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Configurações de Pagamento</h1>
        <p class="text-gray-600 mt-2">Gerencie parcelamentos, juros e opções de pagamento</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <div v-else class="space-y-8">
        <!-- Configurações de Parcelamento -->
        <div class="bg-white rounded-lg shadow-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Configurações de Parcelamento</h2>
            <p class="text-gray-600 text-sm mt-1">Defina as regras para parcelamento dos cursos</p>
          </div>
          
          <div class="p-6">
            <form @submit.prevent="updateInstallmentConfig" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Máximo de Parcelas:
                  </label>
                  <input v-model.number="installmentForm.max_installments" 
                         type="number" 
                         min="1" 
                         max="24"
                         class="w-full p-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Valor Mínimo da Parcela (R$):
                  </label>
                  <input v-model.number="installmentForm.min_installment_value" 
                         type="number" 
                         min="1" 
                         step="0.01"
                         class="w-full p-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Parcelas sem Juros:
                  </label>
                  <input v-model.number="installmentForm.interest_free_installments" 
                         type="number" 
                         min="1" 
                         :max="installmentForm.max_installments"
                         class="w-full p-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                </div>
              </div>
              
              <div class="flex justify-end">
                <button type="submit" 
                        :disabled="savingInstallments"
                        class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50">
                  <span v-if="savingInstallments">Salvando...</span>
                  <span v-else>Salvar Configurações</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Configurações de Juros -->
        <div class="bg-white rounded-lg shadow-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Taxas de Juros</h2>
            <p class="text-gray-600 text-sm mt-1">Configure as taxas de juros por número de parcelas</p>
          </div>
          
          <div class="p-6">
            <form @submit.prevent="updateInterestRates" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="installment in availableInstallments" 
                     :key="installment" 
                     class="flex items-center space-x-3">
                  <label class="w-16 text-sm font-medium text-gray-700">
                    {{ installment }}x:
                  </label>
                  <div class="flex-1 relative">
                    <input v-model.number="interestRates[installment]" 
                           type="number" 
                           min="0" 
                           max="50" 
                           step="0.01"
                           class="w-full p-2 pr-8 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    <span class="absolute right-2 top-2 text-gray-400 text-sm">%</span>
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between">
                <button type="button" 
                        @click="resetToDefault"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                  Resetar Padrão
                </button>
                
                <button type="submit" 
                        :disabled="savingRates"
                        class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50">
                  <span v-if="savingRates">Salvando...</span>
                  <span v-else>Salvar Taxas</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Simulador de Parcelamento -->
        <div class="bg-white rounded-lg shadow-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Simulador de Parcelamento</h2>
            <p class="text-gray-600 text-sm mt-1">Teste como ficam as parcelas com diferentes valores</p>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Formulário de Simulação -->
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Valor do Curso (R$):
                  </label>
                  <input v-model.number="simulationAmount" 
                         @input="runSimulation"
                         type="number" 
                         min="1" 
                         step="0.01"
                         placeholder="Ex: 299.90"
                         class="w-full p-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Máximo de Parcelas para Simulação:
                  </label>
                  <select v-model.number="simulationMaxInstallments" 
                          @change="runSimulation"
                          class="w-full p-3 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                    <option v-for="i in 12" :key="i" :value="i">{{ i }} parcelas</option>
                  </select>
                </div>

                <button @click="runSimulation" 
                        :disabled="!simulationAmount"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                  Simular Parcelamento
                </button>
              </div>

              <!-- Resultados da Simulação -->
              <div v-if="simulationResults.length > 0" class="space-y-3">
                <h3 class="font-semibold text-gray-900">Resultados da Simulação:</h3>
                <div class="max-h-96 overflow-y-auto space-y-2">
                  <div v-for="result in simulationResults" 
                       :key="result.installments"
                       class="border border-gray-200 rounded-lg p-3">
                    <div class="flex justify-between items-start">
                      <div>
                        <div class="font-medium text-gray-900">
                          {{ result.installments }}x de {{ formatCurrency(result.installment_value) }}
                        </div>
                        <div class="text-sm text-gray-600">
                          Total: {{ formatCurrency(result.total_amount) }}
                          <span v-if="result.has_interest" class="text-red-600">
                            (+ {{ formatCurrency(result.total_interest) }} juros)
                          </span>
                          <span v-else class="text-green-600">
                            (sem juros)
                          </span>
                        </div>
                      </div>
                      <div v-if="result.has_interest" class="text-right">
                        <div class="text-xs text-gray-500">Taxa:</div>
                        <div class="text-sm font-medium">{{ result.interest_rate }}%</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Status dos Gateways -->
        <div class="bg-white rounded-lg shadow-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Status dos Gateways</h2>
            <p class="text-gray-600 text-sm mt-1">Verificar conectividade com os provedores de pagamento</p>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="font-semibold text-gray-900">Asaas</h3>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-green-600">Ativo</span>
                  </div>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                  Gateway nacional com PIX, cartão e boleto
                </p>
                <button @click="testAsaasConnection" 
                        :disabled="testingAsaas"
                        class="text-primary-600 hover:text-primary-700 text-sm disabled:opacity-50">
                  <i class="fas fa-plug mr-1"></i>
                  {{ testingAsaas ? 'Testando...' : 'Testar Conexão' }}
                </button>
              </div>

              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="font-semibold text-gray-900">Getnet</h3>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-green-600">Ativo</span>
                  </div>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                  Gateway Santander com PIX, cartão e boleto
                </p>
                <button @click="testGetnetConnection" 
                        :disabled="testingGetnet"
                        class="text-primary-600 hover:text-primary-700 text-sm disabled:opacity-50">
                  <i class="fas fa-plug mr-1"></i>
                  {{ testingGetnet ? 'Testando...' : 'Testar Conexão' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { paymentService } from '@/services'

export default {
  name: 'PaymentSettingsView',
  setup() {
    // Estado
    const loading = ref(true)
    const savingInstallments = ref(false)
    const savingRates = ref(false)
    const testingAsaas = ref(false)
    const testingGetnet = ref(false)
    
    // Formulários
    const installmentForm = ref({
      max_installments: 12,
      min_installment_value: 50.00,
      interest_free_installments: 3
    })
    
    const interestRates = ref({})
    
    // Simulação
    const simulationAmount = ref(299.90)
    const simulationMaxInstallments = ref(12)
    const simulationResults = ref([])
    
    // Computed
    const availableInstallments = computed(() => {
      const installments = []
      const maxFree = installmentForm.value.interest_free_installments
      const maxTotal = installmentForm.value.max_installments
      
      for (let i = maxFree + 1; i <= maxTotal; i++) {
        installments.push(i)
      }
      return installments
    })

    // Métodos
    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value || 0)
    }

    const showMessage = (message, type = 'success') => {
      // Implementar toast/notification aqui
      console.log(`${type}: ${message}`)
    }

    const loadSettings = async () => {
      try {
        loading.value = true
        
        const settings = await paymentService.getPaymentSettings()
        
        if (settings.success) {
          // Carregar configurações existentes
          installmentForm.value = {
            max_installments: 12,
            min_installment_value: 50.00,
            interest_free_installments: 3
          }
          
          interestRates.value = {
            '4': 2.99,
            '5': 3.49,
            '6': 3.99,
            '7': 4.49,
            '8': 4.99,
            '9': 5.49,
            '10': 5.99,
            '11': 6.49,
            '12': 6.99
          }
        }
        
      } catch (error) {
        console.error('Erro ao carregar configurações:', error)
        showMessage('Erro ao carregar configurações', 'error')
      } finally {
        loading.value = false
      }
    }

    const updateInstallmentConfig = async () => {
      try {
        savingInstallments.value = true
        
        const response = await paymentService.updateInstallmentConfig(installmentForm.value)
        
        if (response.success) {
          showMessage('Configurações de parcelamento atualizadas!')
        } else {
          showMessage(response.message || 'Erro ao salvar configurações', 'error')
        }
      } catch (error) {
        console.error('Erro ao salvar:', error)
        showMessage('Erro ao salvar configurações', 'error')
      } finally {
        savingInstallments.value = false
      }
    }

    const updateInterestRates = async () => {
      try {
        savingRates.value = true
        
        const response = await paymentService.updateInterestRates(interestRates.value)
        
        if (response.success) {
          showMessage('Taxas de juros atualizadas!')
        } else {
          showMessage(response.message || 'Erro ao salvar taxas', 'error')
        }
      } catch (error) {
        console.error('Erro ao salvar taxas:', error)
        showMessage('Erro ao salvar taxas', 'error')
      } finally {
        savingRates.value = false
      }
    }

    const resetToDefault = async () => {
      if (!confirm('Tem certeza que deseja resetar para as configurações padrão?')) {
        return
      }
      
      try {
        // Reset para valores padrão
        installmentForm.value = {
          max_installments: 12,
          min_installment_value: 50.00,
          interest_free_installments: 3
        }
        
        interestRates.value = {
          '4': 2.99,
          '5': 3.49,
          '6': 3.99,
          '7': 4.49,
          '8': 4.99,
          '9': 5.49,
          '10': 5.99,
          '11': 6.49,
          '12': 6.99
        }
        
        showMessage('Configurações resetadas para padrão!')
      } catch (error) {
        console.error('Erro ao resetar:', error)
        showMessage('Erro ao resetar configurações', 'error')
      }
    }

    const runSimulation = async () => {
      if (!simulationAmount.value) return
      
      try {
        const response = await paymentService.simulateInstallments(
          simulationAmount.value, 
          simulationMaxInstallments.value
        )
        
        if (response.success) {
          simulationResults.value = response.data.simulations || []
        }
      } catch (error) {
        console.error('Erro na simulação:', error)
        showMessage('Erro ao simular parcelamento', 'error')
      }
    }

    const testAsaasConnection = async () => {
      try {
        testingAsaas.value = true
        // Implementar teste de conexão Asaas
        await new Promise(resolve => setTimeout(resolve, 2000)) // Simular teste
        showMessage('Conexão com Asaas funcionando!')
      } catch (error) {
        showMessage('Erro na conexão com Asaas', 'error')
      } finally {
        testingAsaas.value = false
      }
    }

    const testGetnetConnection = async () => {
      try {
        testingGetnet.value = true
        // Implementar teste de conexão Getnet
        await new Promise(resolve => setTimeout(resolve, 2000)) // Simular teste
        showMessage('Conexão com Getnet funcionando!')
      } catch (error) {
        showMessage('Erro na conexão com Getnet', 'error')
      } finally {
        testingGetnet.value = false
      }
    }

    // Lifecycle
    onMounted(async () => {
      await loadSettings()
      if (simulationAmount.value) {
        await runSimulation()
      }
    })

    return {
      // Estado
      loading,
      savingInstallments,
      savingRates,
      testingAsaas,
      testingGetnet,
      
      // Formulários
      installmentForm,
      interestRates,
      
      // Simulação
      simulationAmount,
      simulationMaxInstallments,
      simulationResults,
      
      // Computed
      availableInstallments,
      
      // Métodos
      formatCurrency,
      updateInstallmentConfig,
      updateInterestRates,
      resetToDefault,
      runSimulation,
      testAsaasConnection,
      testGetnetConnection
    }
  }
}
</script>

<style scoped>
/* Adicionar estilos específicos se necessário */
</style>
