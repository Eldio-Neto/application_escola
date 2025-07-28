<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Todos os Cursos</h1>
        <p class="mt-2 text-gray-600">Descubra nossa seleção completa de cursos online</p>
      </div>

      <!-- Filtros -->
      <div class="mb-8 bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Buscar cursos</label>
            <input
              v-model="searchQuery"
              type="text"
              class="form-input"
              placeholder="Digite o nome do curso..."
            />
          </div>
          <div>
            <label class="form-label">Categoria</label>
            <select v-model="selectedCategory" class="form-input">
              <option value="">Todas as categorias</option>
              <option value="programacao">Programação</option>
              <option value="design">Design</option>
              <option value="marketing">Marketing</option>
              <option value="negocios">Negócios</option>
            </select>
          </div>
          <div>
            <label class="form-label">Ordenar por</label>
            <select v-model="sortBy" class="form-input">
              <option value="title">Nome</option>
              <option value="price">Preço</option>
              <option value="created_at">Mais recente</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center py-12">
        <div class="alert-danger">
          {{ error }}
        </div>
      </div>

      <!-- Courses Grid -->
      <div v-else-if="filteredCourses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <CourseCard 
          v-for="course in filteredCourses" 
          :key="course.id" 
          :course="course" 
        />
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 20a7.962 7.962 0 01-6-2.709M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum curso encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useCoursesStore } from '@/stores/courses'
import CourseCard from '@/components/courses/CourseCard.vue'

export default {
  name: 'CoursesView',
  components: {
    CourseCard
  },
  setup() {
    const coursesStore = useCoursesStore()
    
    const searchQuery = ref('')
    const selectedCategory = ref('')
    const sortBy = ref('title')
    
    const loading = computed(() => coursesStore.loading)
    const error = computed(() => coursesStore.error)
    const courses = computed(() => coursesStore.publishedCourses)
    
    const filteredCourses = computed(() => {
      let filtered = courses.value
      
      // Filter by search query
      if (searchQuery.value) {
        filtered = filtered.filter(course =>
          course.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          course.description.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
      }
      
      // Filter by category
      if (selectedCategory.value) {
        filtered = filtered.filter(course =>
          course.category === selectedCategory.value
        )
      }
      
      // Sort
      return filtered.sort((a, b) => {
        switch (sortBy.value) {
          case 'price':
            return parseFloat(a.price) - parseFloat(b.price)
          case 'created_at':
            return new Date(b.created_at) - new Date(a.created_at)
          default:
            return a.name.localeCompare(b.name)
        }
      })
    })
    
    onMounted(async () => {
      try {
        await coursesStore.fetchCourses()
      } catch (error) {
        console.error('Erro ao carregar cursos:', error)
      }
    })
    
    return {
      searchQuery,
      selectedCategory,
      sortBy,
      loading,
      error,
      filteredCourses
    }
  }
}
</script>
