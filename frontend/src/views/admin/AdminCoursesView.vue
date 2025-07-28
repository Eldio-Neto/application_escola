<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gerenciar Cursos</h1>
          <p class="text-gray-600">Crie, edite e organize seus cursos<    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value)
    }

    const getImageUrl = (imagePath) => {
      if (!imagePath) return '/placeholder-course.jpg'
      // Se a imagem já tem o domínio completo, retorna como está
      if (imagePath.startsWith('http')) return imagePath
      // Senão, constrói a URL completa
      return `http://localhost:8000/storage/${imagePath}`
    }      </div>
        <button
          @click="openCreateModal"
          class="btn-primary flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          <span>Novo Curso</span>
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nome do curso..."
              class="input"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="input">
              <option value="">Todos</option>
              <option value="active">Ativos</option>
              <option value="inactive">Inativos</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="loadCourses"
              class="btn-secondary"
            >
              Filtrar
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Courses Table -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Curso
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Preço
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Carga Horária
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Alunos
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Sessões
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="course in courses" :key="course.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <img 
                        :src="getImageUrl(course.image)" 
                        :alt="course.name"
                        class="h-12 w-12 rounded-lg object-cover"
                      />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ course.name }}</div>
                      <div class="text-sm text-gray-500">{{ course.description?.substring(0, 50) }}...</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ formatCurrency(course.price) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ course.workload_hours ? `${course.workload_hours}h` : '-' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="course.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ course.active ? 'Ativo' : 'Inativo' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ course.enrollments_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ course.sessions_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button
                    @click="editCourse(course)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    Editar
                  </button>
                  <button
                    @click="manageSessions(course)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Sessões
                  </button>
                  <button
                    @click="toggleCourseStatus(course)"
                    :class="course.active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                  >
                    {{ course.active ? 'Desativar' : 'Ativar' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="courses.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum curso encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">Comece criando um novo curso.</p>
          <div class="mt-6">
            <button
              @click="openCreateModal"
              class="btn-primary"
            >
              Criar Primeiro Curso
            </button>
          </div>
        </div>
      </div>

      <!-- Course Form Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-lg rounded-md bg-white max-w-6xl max-h-[90vh] overflow-y-auto">
          <div class="mt-3">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-medium text-gray-900">
                {{ editingCourse ? 'Editar Curso' : 'Novo Curso' }}
              </h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveCourse" class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Curso</label>
                <input
                  v-model="courseForm.name"
                  type="text"
                  required
                  class="input"
                  placeholder="Ex: Vue.js Avançado"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <textarea
                  v-model="courseForm.description"
                  rows="4"
                  required
                  class="input"
                  placeholder="Descreva o conteúdo do curso..."
                ></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <CurrencyInput
                  v-model="courseForm.price"
                  label="Preço do Curso"
                  placeholder="0,00"
                  required
                  :min="0"
                  help="Valor em reais (R$)"
                />

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Carga Horária
                  </label>
                  <input
                    v-model="courseForm.workload_hours"
                    type="number"
                    min="1"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Ex: 40"
                  />
                  <p class="mt-1 text-sm text-gray-500">Carga horária em horas</p>
                </div>
              </div>

              <!-- Módulos do Curso -->
              <ModulesManager
                v-model="courseForm.modules"
                title="Módulos do Curso"
              />

              <div>
                <DateTimePicker
                  v-model="courseForm.sessions"
                  title="Sessões do Curso"
                  date-title="Sessão"
                  start-label="Data e Hora de Início"
                  end-label="Data e Hora de Fim"
                  :show-title="true"
                  :show-description="true"
                  :show-max-participants="true"
                  :min-dates="1"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem do Curso</label>
                <input
                  @change="handleImageUpload"
                  type="file"
                  accept="image/*"
                  class="input"
                />
                <p class="text-sm text-gray-500 mt-1">Formatos aceitos: JPG, PNG, GIF (máx. 2MB)</p>
              </div>

              <div class="flex items-center">
                <input
                  v-model="courseForm.active"
                  type="checkbox"
                  id="course-active"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label for="course-active" class="ml-2 block text-sm text-gray-900">
                  Curso ativo (visível para alunos)
                </label>
              </div>

              <div class="flex justify-end space-x-3 pt-4 border-t">
                <button
                  type="button"
                  @click="closeModal"
                  class="btn-secondary"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="btn-primary"
                >
                  {{ saving ? 'Salvando...' : 'Salvar' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { courseService } from '@/services'
import CurrencyInput from '../../components/CurrencyInput.vue'
import DateTimePicker from '../../components/DateTimePicker.vue'
import ModulesManager from '../../components/ModulesManager.vue'

export default {
  name: 'AdminCoursesView',
  components: {
    CurrencyInput,
    DateTimePicker,
    ModulesManager
  },
  setup() {
    const loading = ref(true)
    const saving = ref(false)
    const courses = ref([])
    const showModal = ref(false)
    const editingCourse = ref(null)
    
    const filters = ref({
      search: '',
      status: ''
    })

    const courseForm = ref({
      name: '',
      description: '',
      price: 0,
      workload_hours: null,
      modules: [],
      active: true,
      image: null,
      sessions: []
    })

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value || 0)
    }

    const loadCourses = async () => {
      try {
        loading.value = true
        const data = await courseService.getCourses()
        courses.value = data.data || data
      } catch (error) {
        console.error('Erro ao carregar cursos:', error)
      } finally {
        loading.value = false
      }
    }

    const openCreateModal = () => {
      editingCourse.value = null
      courseForm.value = {
        name: '',
        description: '',
        price: 0,
        workload_hours: null,
        modules: [],
        active: true,
        image: null,
        sessions: [{
          title: '',
          description: '',
          start_datetime: '',
          end_datetime: '',
          max_participants: null
        }]
      }
      showModal.value = true
    }

    const editCourse = (course) => {
      editingCourse.value = course
      courseForm.value = {
        name: course.name,
        description: course.description,
        price: course.price,
        workload_hours: course.workload_hours,
        modules: course.modules || [],
        active: course.active,
        image: null,
        sessions: course.sessions && course.sessions.length > 0 
          ? course.sessions.map(session => ({
              title: session.title,
              description: session.description,
              start_datetime: session.start_datetime,
              end_datetime: session.end_datetime,
              max_participants: session.max_participants
            }))
          : [{
              title: '',
              description: '',
              start_datetime: '',
              end_datetime: '',
              max_participants: null
            }]
      }
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      editingCourse.value = null
    }

    const handleImageUpload = (event) => {
      const file = event.target.files[0]
      if (file) {
        courseForm.value.image = file
      }
    }

    const saveCourse = async () => {
      try {
        saving.value = true
        
        const formData = new FormData()
        formData.append('name', courseForm.value.name)
        formData.append('description', courseForm.value.description)
        formData.append('price', courseForm.value.price)
        if (courseForm.value.workload_hours) {
          formData.append('workload_hours', courseForm.value.workload_hours)
        }
        formData.append('active', courseForm.value.active ? '1' : '0')
        if (courseForm.value.image) {
          formData.append('image', courseForm.value.image)
        }

        // Adicionar módulos
        if (courseForm.value.modules && courseForm.value.modules.length > 0) {
          courseForm.value.modules.forEach((module, index) => {
            if (module.title) {
              formData.append(`modules[${index}][title]`, module.title)
              formData.append(`modules[${index}][description]`, module.description || '')
              formData.append(`modules[${index}][order]`, module.order || index + 1)
            }
          })
        }

        // Adicionar sessões
        if (courseForm.value.sessions && courseForm.value.sessions.length > 0) {
          courseForm.value.sessions.forEach((session, index) => {
            if (session.title && session.start_datetime && session.end_datetime) {
              formData.append(`sessions[${index}][title]`, session.title)
              formData.append(`sessions[${index}][description]`, session.description || '')
              formData.append(`sessions[${index}][start_datetime]`, session.start_datetime)
              formData.append(`sessions[${index}][end_datetime]`, session.end_datetime)
              if (session.max_participants) {
                formData.append(`sessions[${index}][max_participants]`, session.max_participants)
              }
            }
          })
        }

        if (editingCourse.value) {
          await courseService.updateCourse(editingCourse.value.id, formData)
        } else {
          await courseService.createCourse(formData)
        }

        closeModal()
        await loadCourses()
      } catch (error) {
        console.error('Erro ao salvar curso:', error)
        
        // Mostrar erro específico para o usuário
        let errorMessage = 'Erro ao salvar curso'
        if (error.response?.data?.message) {
          errorMessage = error.response.data.message
        } else if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          errorMessage = errors.join(', ')
        }
        
        alert(errorMessage) // Ou use um sistema de notificação mais sofisticado
      } finally {
        saving.value = false
      }
    }

    const toggleCourseStatus = async (course) => {
      try {
        await courseService.updateCourse(course.id, {
          active: !course.active
        })
        await loadCourses()
      } catch (error) {
        console.error('Erro ao alterar status do curso:', error)
      }
    }

    const manageSessions = (course) => {
      // TODO: Implementar gerenciamento de sessões
      console.log('Gerenciar sessões do curso:', course.name)
    }

    onMounted(() => {
      loadCourses()
    })

    return {
      loading,
      saving,
      courses,
      showModal,
      editingCourse,
      filters,
      courseForm,
      formatCurrency,
      getImageUrl,
      loadCourses,
      openCreateModal,
      editCourse,
      closeModal,
      handleImageUpload,
      saveCourse,
      toggleCourseStatus,
      manageSessions
    }
  }
}
</script>
