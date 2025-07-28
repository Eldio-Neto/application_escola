import api from './api'

export const authService = {
  async login(credentials) {
    const response = await api.post('/auth/login', credentials)
    if (response.data.data.token) {
      localStorage.setItem('token', response.data.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.data.user))
    }
    return response.data.data
  },

  async register(userData) {
    const response = await api.post('/auth/register', userData)
    if (response.data.data.token) {
      localStorage.setItem('token', response.data.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.data.user))
    }
    return response.data.data
  },

  async logout() {
    try {
      await api.post('/auth/logout')
    } catch (error) {
      console.error('Erro ao fazer logout:', error)
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  },

  async getCurrentUser() {
    const response = await api.get('/auth/me')
    return response.data
  },

  getStoredUser() {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  },

  getToken() {
    return localStorage.getItem('token')
  },

  isAuthenticated() {
    return !!this.getToken()
  }
}

export const courseService = {
  async getCourses() {
    const response = await api.get('/courses')
    return response.data.data
  },

  async getCourse(id) {
    const response = await api.get(`/courses/${id}`)
    return response.data.data
  },

  async createCourse(courseData) {
    const config = {}
    if (courseData instanceof FormData) {
      config.headers = {
        'Content-Type': 'multipart/form-data'
      }
    }
    const response = await api.post('/courses', courseData, config)
    return response.data.data
  },

  async updateCourse(id, courseData) {
    const config = {}
    if (courseData instanceof FormData) {
      config.headers = {
        'Content-Type': 'multipart/form-data'
      }
    }
    const response = await api.put(`/courses/${id}`, courseData, config)
    return response.data.data
  },

  async deleteCourse(id) {
    const response = await api.delete(`/courses/${id}`)
    return response.data
  }
}

export const enrollmentService = {
  async enroll(courseId) {
    const response = await api.post('/enrollments', { course_id: courseId })
    return response.data.data
  },

  async getMyEnrollments() {
    const response = await api.get('/enrollments/my')
    return response.data.data
  },

  async getAllEnrollments() {
    const response = await api.get('/enrollments')
    return response.data.data
  },

  async deleteEnrollment(id) {
    const response = await api.delete(`/enrollments/${id}`)
    return response.data
  }
}

export const paymentService = {
  async processPayment(paymentData) {
    const response = await api.post('/payments/process', paymentData)
    return response.data
  },

  async getPaymentStatus(paymentId) {
    const response = await api.get(`/payments/${paymentId}/status`)
    return response.data
  },

  async processPix(paymentData) {
    const response = await api.post('/payments/pix', paymentData)
    return response.data
  },

  async processCreditCard(paymentData) {
    const response = await api.post('/payments/credit-card', paymentData)
    return response.data
  },

  async processBoleto(paymentData) {
    const response = await api.post('/payments/boleto', paymentData)
    return response.data
  },

  async getPaymentSettings() {
    const response = await api.get('/payment-settings')
    return response.data
  },

  async updateInstallmentConfig(config) {
    const response = await api.put('/payment-settings/installments', config)
    return response.data
  },

  async updateInterestRates(rates) {
    const response = await api.put('/payment-settings/interest-rates', rates)
    return response.data
  },

  async simulateInstallments(amount, installments) {
    const response = await api.post('/payment-settings/simulate', {
      amount,
      installments
    })
    return response.data
  }
}

export const sessionService = {
  async getSessions(courseId) {
    const response = await api.get(`/courses/${courseId}/sessions`)
    return response.data.data
  },

  async createSession(courseId, sessionData) {
    const response = await api.post(`/courses/${courseId}/sessions`, sessionData)
    return response.data.data
  },

  async updateSession(sessionId, sessionData) {
    const response = await api.put(`/sessions/${sessionId}`, sessionData)
    return response.data.data
  },

  async deleteSession(sessionId) {
    const response = await api.delete(`/sessions/${sessionId}`)
    return response.data
  }
}

export const userService = {
  async getUsers() {
    const response = await api.get('/users')
    return response.data.data
  },

  async createUser(userData) {
    const response = await api.post('/users', userData)
    return response.data.data
  },

  async updateUser(id, userData) {
    const response = await api.put(`/users/${id}`, userData)
    return response.data.data
  },

  async deleteUser(id) {
    const response = await api.delete(`/users/${id}`)
    return response.data
  },

  async updateProfile(userData) {
    const response = await api.put('/profile', userData)
    return response.data.data
  }
}

export const adminService = {
  async getUsers() {
    const response = await api.get('/admin/users')
    return response.data.data
  },

  async createUser(userData) {
    const response = await api.post('/admin/users', userData)
    return response.data.data
  },

  async updateUser(id, userData) {
    const response = await api.put(`/admin/users/${id}`, userData)
    return response.data.data
  },

  async deleteUser(id) {
    const response = await api.delete(`/admin/users/${id}`)
    return response.data
  },

  async getPayments(filters = {}) {
    const params = new URLSearchParams(filters)
    const response = await api.get(`/admin/payments?${params}`)
    return response.data.data
  },

  async updatePaymentStatus(paymentId, status) {
    const response = await api.put(`/admin/payments/${paymentId}/status`, { status })
    return response.data.data
  },

  async getCourses() {
    const response = await api.get('/admin/courses')
    return response.data.data
  },

  async createCourse(courseData) {
    const config = {}
    if (courseData instanceof FormData) {
      config.headers = {
        'Content-Type': 'multipart/form-data'
      }
    }
    const response = await api.post('/admin/courses', courseData, config)
    return response.data.data
  },

  async updateCourse(id, courseData) {
    const config = {}
    if (courseData instanceof FormData) {
      config.headers = {
        'Content-Type': 'multipart/form-data'
      }
    }
    const response = await api.put(`/admin/courses/${id}`, courseData, config)
    return response.data.data
  },

  async deleteCourse(id) {
    const response = await api.delete(`/admin/courses/${id}`)
    return response.data
  },

  async getEnrollments() {
    const response = await api.get('/admin/enrollments')
    return response.data.data
  },

  async updateEnrollmentStatus(enrollmentId, status) {
    const response = await api.put(`/admin/enrollments/${enrollmentId}/status`, { status })
    return response.data.data
  },

  async getStatistics() {
    const response = await api.get('/admin/statistics')
    return response.data.data
  }
}
