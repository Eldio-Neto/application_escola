<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Configurações de Pagamento</h1>
        <p class="text-gray-600 mt-2">Configure as opções de parcelamento e taxas de juros</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <div v-else class="space-y-8">
        <!-- Configurações de Parcelamento -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Configurações de Parcelamento</h2>
          
          <form @submit.prevent="saveInstallmentSettings" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Parcelas Máximas:
                </label>
                <select v-model.number="installmentSettings.max_installments" 
                        class="w-full p-2 border border-gray-300 rounded-md">
                  <option v-for="n in 12" :key="n" :value="n">{{ n }}x</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Parcelas sem Juros:
                </label>
                <select v-model.number="installmentSettings.free_installments" 
                        class="w-full p-2 border border-gray-300 rounded-md">
                  <option v-for="n in installmentSettings.max_installments" :key="n" :value="n">{{ n }}x</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Valor Mínimo da Parcela (R$):
                </label>
                <input v-model.number="installmentSettings.min_installment_value" 
                       type="number" 
                       step="0.01" 
                       min="0" 
                       class="w-full p-2 border border-gray-300 rounded-md">
              </div>
            </div>

            <div class="flex justify-end">
              <button type="submit" 
                      :disabled="savingInstallments"
                      :class="['px-6 py-2 rounded-md font-semibold', 
                              savingInstallments 
                                ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                                : 'bg-primary-600 text-white hover:bg-primary-700']">
                <span v-if="savingInstallments">Salvando...</span>
                <span v-else>Salvar Configurações</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Configurações de Juros -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Configurações de Juros</h2>
          
          <form @submit.prevent="saveInterestSettings" class="space-y-6">
            <div class="space-y-4">
              <div v-for="installment in interestRates" :key="installment.installments" 
                   class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center">
                  <span class="font-medium text-gray-900">
                    {{ installment.installments }}x parcelas
                  </span>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Taxa de Juros (% ao mês):
                  </label>
                  <input v-model.number="installment.rate" 
                         type="number" 
                         step="0.01" 
                         min="0" 
                         max="20"
                         class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="flex items-center">
                  <span class="text-sm text-gray-600">
                    Juros total: {{ calculateTotalInterest(installment.rate, installment.installments) }}%
                  </span>
                </div>
              </div>
            </div>

            <div class="flex justify-end">
              <button type="submit" 
                      :disabled="savingInterest"
                      :class="['px-6 py-2 rounded-md font-semibold', 
                              savingInterest 
                                ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                                : 'bg-primary-600 text-white hover:bg-primary-700']">
                <span v-if="savingInterest">Salvando...</span>
                <span v-else>Salvar Juros</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Simulador de Parcelas -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Simulador de Parcelas</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Valor do Produto (R$):
              </label>
              <input v-model.number="simulator.productValue" 
                     type="number" 
                     step="0.01" 
                     min="0" 
                     @input="simulateInstallments"
                     class="w-full p-2 border border-gray-300 rounded-md">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Número de Parcelas:
              </label>
              <select v-model.number="simulator.installments" 
                      @change="simulateInstallments"
                      class="w-full p-2 border border-gray-300 rounded-md">
                <option v-for="n in installmentSettings.max_installments" :key="n" :value="n">{{ n }}x</option>
              </select>
            </div>
          </div>

          <div v-if="simulator.result" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 mb-3">Resultado da Simulação</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <span class="text-blue-700 font-medium">Valor da Parcela:</span>
                <div class="text-lg font-bold text-blue-900">
                  {{ formatCurrency(simulator.result.installment_value) }}
                </div>
              </div>
              
              <div>
                <span class="text-blue-700 font-medium">Total a Pagar:</span>
                <div class="text-lg font-bold text-blue-900">
                  {{ formatCurrency(simulator.result.total_amount) }}
                </div>
              </div>
              
              <div>
                <span class="text-blue-700 font-medium">Juros Total:</span>
                <div class="text-lg font-bold" 
                     :class="simulator.result.has_interest ? 'text-red-600' : 'text-green-600'">
                  <span v-if="simulator.result.has_interest">
                    {{ formatCurrency(simulator.result.total_amount - simulator.productValue) }}
                    ({{ simulator.result.interest_rate }}%)
                  </span>
                  <span v-else>Sem Juros</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabela de Configuração Atual -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuração Atual</h2>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Parcelas
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Taxa de Juros
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Exemplo (R$ 100,00)
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="n in installmentSettings.max_installments" :key="n">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ n }}x
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ getInstallmentRate(n) }}% a.m.
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', 
                                  n <= installmentSettings.free_installments 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-yellow-100 text-yellow-800']">
                      {{ n <= installmentSettings.free_installments ? 'Sem Juros' : 'Com Juros' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(calculateExampleInstallment(100, n)) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Histórico de Alterações -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Histórico de Alterações</h2>
          
          <div v-if="history.length === 0" class="text-center py-8 text-gray-500">
            Nenhuma alteração registrada
          </div>
          
          <div v-else class="space-y-4">
            <div v-for="entry in history" :key="entry.id" 
                 class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <div class="font-medium text-gray-900">{{ entry.description }}</div>
                <div class="text-sm text-gray-500">{{ formatDate(entry.created_at) }}</div>
              </div>
              <div class="text-sm text-gray-600">
                por {{ entry.user_name }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'

export default {
  name: 'PaymentSettingsView',
  setup() {
    // Estado
    const loading = ref(true)
    const savingInstallments = ref(false)
    const savingInterest = ref(false)
    
    // Configurações
    const installmentSettings = ref({
      max_installments: 12,
      free_installments: 3,
      min_installment_value: 25.00
    })

    const interestRates = ref([])
    const history = ref([])

    // Simulador
    const simulator = ref({
      productValue: 100,
      installments: 1,
      result: null
    })

    // Métodos
    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value || 0)
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('pt-BR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const loadSettings = async () => {
      try {
        const response = await api.get('/payment-settings')
        const settings = response.data.data

        if (settings.installment_config) {
          installmentSettings.value = {
            max_installments: settings.installment_config.max_installments || 12,
            free_installments: settings.installment_config.free_installments || 3,
            min_installment_value: settings.installment_config.min_installment_value || 25.00
          }
        }

        if (settings.interest_rates) {
          interestRates.value = Object.entries(settings.interest_rates).map(([installments, rate]) => ({
            installments: parseInt(installments),
            rate: parseFloat(rate)
          })).sort((a, b) => a.installments - b.installments)
        } else {
          generateDefaultInterestRates()
        }
      } catch (error) {
        console.error('Erro ao carregar configurações:', error)
        generateDefaultInterestRates()
      }
    }

    const generateDefaultInterestRates = () => {
      const rates = []
      for (let i = 4; i <= installmentSettings.value.max_installments; i++) {
        rates.push({
          installments: i,
          rate: 2.99 + (i - 4) * 0.5
        })
      }
      interestRates.value = rates
    }

    const saveInstallmentSettings = async () => {
      savingInstallments.value = true
      
      try {
        await api.put('/payment-settings/installments', {
          installment_config: installmentSettings.value
        })
        
        console.log('Configurações de parcelamento salvas com sucesso!')
        generateDefaultInterestRates()
      } catch (error) {
        console.error('Erro ao salvar configurações:', error)
      } finally {
        savingInstallments.value = false
      }
    }

    const saveInterestSettings = async () => {
      savingInterest.value = true
      
      try {
        const rates = {}
        interestRates.value.forEach(item => {
          rates[item.installments] = item.rate
        })

        await api.put('/payment-settings/interest-rates', {
          interest_rates: rates
        })
        
        console.log('Configurações de juros salvas com sucesso!')
      } catch (error) {
        console.error('Erro ao salvar juros:', error)
      } finally {
        savingInterest.value = false
      }
    }

    const simulateInstallments = async () => {
      if (!simulator.value.productValue || simulator.value.productValue <= 0) {
        simulator.value.result = null
        return
      }

      try {
        const response = await api.post('/payment-settings/calculate-installments', {
          amount: simulator.value.productValue,
          installments: simulator.value.installments
        })

        simulator.value.result = response.data.data
      } catch (error) {
        console.error('Erro ao simular parcelas:', error)
      }
    }

    const calculateTotalInterest = (monthlyRate, installments) => {
      if (!monthlyRate || installments <= installmentSettings.value.free_installments) return 0
      return (Math.pow(1 + monthlyRate / 100, installments) - 1) * 100
    }

    const getInstallmentRate = (installments) => {
      if (installments <= installmentSettings.value.free_installments) return 0
      const rate = interestRates.value.find(r => r.installments === installments)
      return rate ? rate.rate : 0
    }

    const calculateExampleInstallment = (amount, installments) => {
      const rate = getInstallmentRate(installments)
      if (rate === 0) return amount / installments
      
      const monthlyRate = rate / 100
      const factor = Math.pow(1 + monthlyRate, installments)
      const totalAmount = amount * factor
      return totalAmount / installments
    }

    const loadHistory = async () => {
      try {
        const response = await api.get('/payment-settings/history')
        history.value = response.data.data || []
      } catch (error) {
        console.error('Erro ao carregar histórico:', error)
      }
    }

    // Watchers
    watch(() => installmentSettings.value.max_installments, (newValue) => {
      if (installmentSettings.value.free_installments > newValue) {
        installmentSettings.value.free_installments = newValue
      }
      generateDefaultInterestRates()
    })

    // Lifecycle
    onMounted(async () => {
      loading.value = true
      await Promise.all([
        loadSettings(),
        loadHistory()
      ])
      simulateInstallments()
      loading.value = false
    })

    return {
      // Estado
      loading,
      savingInstallments,
      savingInterest,
      
      // Configurações
      installmentSettings,
      interestRates,
      history,
      
      // Simulador
      simulator,
      
      // Métodos
      formatCurrency,
      formatDate,
      saveInstallmentSettings,
      saveInterestSettings,
      simulateInstallments,
      calculateTotalInterest,
      getInstallmentRate,
      calculateExampleInstallment
    }
  }
}
</script>
