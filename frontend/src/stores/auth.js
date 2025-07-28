import { defineStore } from 'pinia'
import { authService } from '@/services'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.user_type === 'admin',
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || ''
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      
      try {
        const data = await authService.login(credentials)
        this.user = data.user
        this.token = data.token
        return data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao fazer login'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null
      
      try {
        const data = await authService.register(userData)
        this.user = data.user
        this.token = data.token
        return data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao registrar usuário'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      
      try {
        await authService.logout()
      } catch (error) {
        console.error('Erro ao fazer logout:', error)
      } finally {
        this.user = null
        this.token = null
        this.loading = false
      }
    },

    async refreshUser() {
      if (!this.token) return
      
      try {
        const user = await authService.getCurrentUser()
        this.user = user
      } catch (error) {
        console.error('Erro ao atualizar dados do usuário:', error)
        this.logout()
      }
    },

    initializeAuth() {
      const token = authService.getToken()
      const user = authService.getStoredUser()
      
      if (token && user) {
        this.token = token
        this.user = user
      }
    },

    clearError() {
      this.error = null
    }
  }
})
