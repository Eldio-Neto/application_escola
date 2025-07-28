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
    return response.data
  },

  async getCourse(id) {
    const response = await api.get(`/courses/${id}`)
    return response.data
  },

  async createCourse(courseData) {
    const response = await api.post('/courses', courseData)
    return response.data
  },

  async updateCourse(id, courseData) {
    const response = await api.put(`/courses/${id}`, courseData)
    return response.data
  },

  async deleteCourse(id) {
    const response = await api.delete(`/courses/${id}`)
    return response.data
  },

  async uploadCourseImage(id, formData) {
    const response = await api.post(`/courses/${id}/image`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  }
}

export const enrollmentService = {
  async getEnrollments() {
    const response = await api.get('/enrollments')
    return response.data
  },

  async enrollInCourse(courseId) {
    const response = await api.post('/enrollments', { course_id: courseId })
    return response.data
  },

  async getEnrollment(id) {
    const response = await api.get(`/enrollments/${id}`)
    return response.data
  }
}

export const paymentService = {
  async createPayment(paymentData) {
    const response = await api.post('/payments', paymentData)
    return response.data
  },

  async getPayments() {
    const response = await api.get('/payments')
    return response.data
  },

  async getPayment(id) {
    const response = await api.get(`/payments/${id}`)
    return response.data
  }
}

export const dashboardService = {
  async getStats() {
    const response = await api.get('/dashboard/stats')
    return response.data
  },

  async getRecentActivities() {
    const response = await api.get('/dashboard/activities')
    return response.data
  }
}

export const adminService = {
  async getDashboardStats() {
    const response = await api.get('/dashboard/admin')
    return response.data.data
  },

  async getUsers() {
    const response = await api.get('/admin/users')
    return response.data
  },

  async updateUserStatus(userId, status) {
    const response = await api.put(`/admin/users/${userId}/status`, { status })
    return response.data
  },

  async getPayments(filters = {}) {
    const response = await api.get('/admin/payments', { params: filters })
    return response.data
  },

  async updatePaymentStatus(paymentId, status) {
    const response = await api.put(`/admin/payments/${paymentId}/status`, { status })
    return response.data
  },

  async getCourseStudents(courseId) {
    const response = await api.get(`/admin/courses/${courseId}/students`)
    return response.data
  }
}
