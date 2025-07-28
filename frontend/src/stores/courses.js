import { defineStore } from 'pinia'
import { courseService } from '@/services'

export const useCoursesStore = defineStore('courses', {
  state: () => ({
    courses: [],
    currentCourse: null,
    loading: false,
    error: null
  }),

  getters: {
    getCourseById: (state) => (id) => {
      return state.courses.find(course => course.id === parseInt(id))
    },
    
    publishedCourses: (state) => {
      return Array.isArray(state.courses) ? state.courses.filter(course => course.active === true) : []
    },

    draftCourses: (state) => {
      return Array.isArray(state.courses) ? state.courses.filter(course => course.active === false) : []
    }
  },

  actions: {
    async fetchCourses() {
      this.loading = true
      this.error = null
      
      try {
        const response = await courseService.getCourses()
        // Extrair dados da estrutura paginada da API
        this.courses = response.data.data || response.data || []
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao carregar cursos'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchCourse(id) {
      this.loading = true
      this.error = null
      
      try {
        const data = await courseService.getCourse(id)
        this.currentCourse = data.data || data
        return this.currentCourse
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao carregar curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createCourse(courseData) {
      this.loading = true
      this.error = null
      
      try {
        const data = await courseService.createCourse(courseData)
        const newCourse = data.data || data
        this.courses.push(newCourse)
        return newCourse
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao criar curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateCourse(id, courseData) {
      this.loading = true
      this.error = null
      
      try {
        const data = await courseService.updateCourse(id, courseData)
        const updatedCourse = data.data || data
        
        const index = this.courses.findIndex(course => course.id === parseInt(id))
        if (index !== -1) {
          this.courses[index] = updatedCourse
        }
        
        if (this.currentCourse?.id === parseInt(id)) {
          this.currentCourse = updatedCourse
        }
        
        return updatedCourse
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao atualizar curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteCourse(id) {
      this.loading = true
      this.error = null
      
      try {
        await courseService.deleteCourse(id)
        this.courses = this.courses.filter(course => course.id !== parseInt(id))
        
        if (this.currentCourse?.id === parseInt(id)) {
          this.currentCourse = null
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao excluir curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    },

    clearCurrentCourse() {
      this.currentCourse = null
    }
  }
})
