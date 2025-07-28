import { defineStore } from 'pinia'
import api from '@/services/api'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    loading: false,
    sessionId: null
  }),

  getters: {
    count: (state) => state.items.length,
    total: (state) => state.items.reduce((sum, item) => sum + parseFloat(item.price) * item.quantity, 0),
    isEmpty: (state) => state.items.length === 0
  },

  actions: {
    initSession() {
      if (!this.sessionId) {
        this.sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
      }
      return this.sessionId
    },

    getHeaders() {
      return {
        'X-Session-ID': this.initSession()
      }
    },

    async loadCart() {
      try {
        this.loading = true
        const response = await api.get('/cart', {
          headers: this.getHeaders()
        })
        
        if (response.data.success) {
          this.items = response.data.data.items
        }
      } catch (error) {
        console.error('Error loading cart:', error)
      } finally {
        this.loading = false
      }
    },

    async addItem(courseId) {
      try {
        const response = await api.post('/cart', {
          course_id: courseId
        }, {
          headers: this.getHeaders()
        })

        if (response.data.success) {
          await this.loadCart()
          return { success: true, message: response.data.message }
        }

        return { success: false, message: response.data.message }
      } catch (error) {
        const message = error.response?.data?.message || 'Erro ao adicionar item ao carrinho'
        return { success: false, message }
      }
    },

    async removeItem(itemId) {
      try {
        const response = await api.delete(`/cart/${itemId}`, {
          headers: this.getHeaders()
        })

        if (response.data.success) {
          await this.loadCart()
          return { success: true, message: response.data.message }
        }

        return { success: false, message: response.data.message }
      } catch (error) {
        const message = error.response?.data?.message || 'Erro ao remover item do carrinho'
        return { success: false, message }
      }
    },

    async clearCart() {
      try {
        const response = await api.delete('/cart', {
          headers: this.getHeaders()
        })

        if (response.data.success) {
          this.items = []
          return { success: true, message: response.data.message }
        }

        return { success: false, message: response.data.message }
      } catch (error) {
        const message = error.response?.data?.message || 'Erro ao limpar carrinho'
        return { success: false, message }
      }
    },

    async applyCoupon(couponCode) {
      try {
        const response = await api.post('/cart/apply-coupon', {
          coupon_code: couponCode
        }, {
          headers: this.getHeaders()
        })

        if (response.data.success) {
          return { 
            success: true, 
            data: response.data.data,
            message: 'Cupom aplicado com sucesso'
          }
        }

        return { success: false, message: response.data.message }
      } catch (error) {
        const message = error.response?.data?.message || 'Erro ao aplicar cupom'
        return { success: false, message }
      }
    },

    async validateCoupon(couponCode) {
      try {
        const response = await api.post('/coupons/validate', {
          code: couponCode,
          amount: this.total
        })

        if (response.data.success) {
          return { 
            success: true, 
            data: response.data.data
          }
        }

        return { success: false, message: response.data.message }
      } catch (error) {
        const message = error.response?.data?.message || 'Cupom inválido'
        return { success: false, message }
      }
    },

    async transferToUser() {
      try {
        const response = await api.post('/cart/transfer', {}, {
          headers: this.getHeaders()
        })

        if (response.data.success) {
          await this.loadCart()
          return { success: true, message: response.data.message }
        }

        return { success: false, message: response.data.message }
      } catch (error) {
        const message = error.response?.data?.message || 'Erro ao transferir carrinho'
        return { success: false, message }
      }
    }
  }
})