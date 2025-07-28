import { defineStore } from 'pinia'
import api from '@/services/api'

export const usePaymentStore = defineStore('payment', {
  state: () => ({
    payments: [],
    currentPayment: null,
    loading: false,
    error: null
  }),

  getters: {
    isLoading: (state) => state.loading,
    hasError: (state) => !!state.error,
    getPaymentById: (state) => (id) => {
      return state.payments.find(payment => payment.id === id)
    },
    getPaidPayments: (state) => {
      return state.payments.filter(payment => payment.status === 'paid')
    },
    getPendingPayments: (state) => {
      return state.payments.filter(payment => payment.status === 'pending')
    }
  },

  actions: {
    async fetchPayments() {
      this.loading = true
      this.error = null

      try {
        const response = await api.get('/payments')
        this.payments = response.data.data || []
        return response.data
      } catch (error) {
        console.error('Erro ao buscar pagamentos:', error)
        this.error = error.response?.data?.message || 'Erro ao buscar pagamentos'
        throw error
      } finally {
        this.loading = false
      }
    },

    async getPayment(id) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/payments/${id}`)
        this.currentPayment = response.data.data
        return response.data
      } catch (error) {
        console.error('Erro ao buscar pagamento:', error)
        this.error = error.response?.data?.message || 'Erro ao buscar pagamento'
        throw error
      } finally {
        this.loading = false
      }
    },

    async processCreditCardPayment(paymentData) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post('/payments/credit-card', paymentData)
        
        // Adicionar pagamento à lista se bem-sucedido
        if (response.data.payment) {
          this.payments.unshift(response.data.payment)
          this.currentPayment = response.data.payment
        }

        return response.data
      } catch (error) {
        console.error('Erro no pagamento com cartão:', error)
        this.error = error.response?.data?.message || 'Erro no processamento do pagamento'
        throw error
      } finally {
        this.loading = false
      }
    },

    async processPixPayment(paymentData) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post('/payments/pix', paymentData)
        
        // Adicionar pagamento à lista se bem-sucedido
        if (response.data.payment) {
          this.payments.unshift(response.data.payment)
          this.currentPayment = response.data.payment
        }

        return response.data
      } catch (error) {
        console.error('Erro no pagamento PIX:', error)
        this.error = error.response?.data?.message || 'Erro na geração do PIX'
        throw error
      } finally {
        this.loading = false
      }
    },

    async processBoletoPayment(paymentData) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post('/payments/boleto', paymentData)
        
        // Adicionar pagamento à lista se bem-sucedido
        if (response.data.payment) {
          this.payments.unshift(response.data.payment)
          this.currentPayment = response.data.payment
        }

        return response.data
      } catch (error) {
        console.error('Erro no pagamento boleto:', error)
        this.error = error.response?.data?.message || 'Erro na geração do boleto'
        throw error
      } finally {
        this.loading = false
      }
    },

    async checkPaymentStatus(paymentId) {
      try {
        const response = await api.get(`/payments/${paymentId}`)
        
        // Atualizar pagamento na lista se existe
        const index = this.payments.findIndex(p => p.id === paymentId)
        if (index !== -1) {
          this.payments[index] = response.data.data
        }

        return response.data.data
      } catch (error) {
        console.error('Erro ao verificar status do pagamento:', error)
        throw error
      }
    },

    async updatePaymentStatus(paymentId, status) {
      this.loading = true
      this.error = null

      try {
        const response = await api.put(`/admin/payments/${paymentId}/status`, { status })
        
        // Atualizar pagamento na lista
        const index = this.payments.findIndex(p => p.id === paymentId)
        if (index !== -1) {
          this.payments[index] = response.data.data
        }

        return response.data
      } catch (error) {
        console.error('Erro ao atualizar status do pagamento:', error)
        this.error = error.response?.data?.message || 'Erro ao atualizar status'
        throw error
      } finally {
        this.loading = false
      }
    },

    async testGetnetConnection() {
      try {
        const response = await api.get('/payments/test-getnet')
        return response.data
      } catch (error) {
        console.error('Erro ao testar conexão Getnet:', error)
        throw error
      }
    },

    // Métodos administrativos
    async fetchAllPayments(filters = {}) {
      this.loading = true
      this.error = null

      try {
        const params = new URLSearchParams()
        
        if (filters.status) params.append('status', filters.status)
        if (filters.payment_method) params.append('payment_method', filters.payment_method)
        if (filters.course_id) params.append('course_id', filters.course_id)
        if (filters.per_page) params.append('per_page', filters.per_page)

        const response = await api.get(`/admin/payments?${params.toString()}`)
        this.payments = response.data.data || []
        return response.data
      } catch (error) {
        console.error('Erro ao buscar todos os pagamentos:', error)
        this.error = error.response?.data?.message || 'Erro ao buscar pagamentos'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Utilitários
    clearError() {
      this.error = null
    },

    resetCurrentPayment() {
      this.currentPayment = null
    },

    formatPaymentMethod(method) {
      const methods = {
        'credit_card': 'Cartão de Crédito',
        'pix': 'PIX',
        'boleto': 'Boleto Bancário'
      }
      return methods[method] || method
    },

    formatPaymentStatus(status) {
      const statuses = {
        'pending': 'Pendente',
        'processing': 'Processando',
        'paid': 'Pago',
        'failed': 'Falhou',
        'cancelled': 'Cancelado',
        'refunded': 'Estornado'
      }
      return statuses[status] || status
    },

    getStatusColor(status) {
      const colors = {
        'pending': 'text-yellow-600 bg-yellow-100',
        'processing': 'text-blue-600 bg-blue-100',
        'paid': 'text-green-600 bg-green-100',
        'failed': 'text-red-600 bg-red-100',
        'cancelled': 'text-gray-600 bg-gray-100',
        'refunded': 'text-purple-600 bg-purple-100'
      }
      return colors[status] || 'text-gray-600 bg-gray-100'
    }
  }
})
