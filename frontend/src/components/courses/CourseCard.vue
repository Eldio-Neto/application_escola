<template>
  <div class="card hover:shadow-lg transition-shadow duration-300">
      <div class="relative">
        <img 
          :src="course.image || '/default-course.jpg'" 
          :alt="course.name"
          class="w-full h-48 object-cover"
        />
        <div class="absolute top-2 right-2">
          <span 
            v-if="course.active" 
            class="bg-success-500 text-white px-2 py-1 rounded text-xs font-medium"
          >
            Disponível
          </span>
          <span 
            v-else 
            class="bg-warning-500 text-white px-2 py-1 rounded text-xs font-medium"
          >
            Em breve
          </span>
        </div>
      </div>    <div class="card-body">
        <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
          {{ course.name }}
        </h3>
        
        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
          {{ course.description }}
        </p>
      
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          <span class="text-sm text-gray-600">4.8</span>
        </div>
        
        <div class="flex items-center text-sm text-gray-500">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ course.sessions ? course.sessions.length : 0 }} aulas
        </div>
      </div>
      
      <div class="flex items-center justify-between">
        <div class="text-2xl font-bold text-primary-600">
          {{ formatPrice(course.price) }}
        </div>
        
        <router-link 
          :to="{ name: 'course-detail', params: { id: course.id } }"
          class="btn-primary text-sm px-4 py-2"
        >
          Ver Detalhes
        </router-link>
      </div>
      
      <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="flex items-center justify-between text-sm text-gray-500">
          <span>{{ course.sessions ? course.sessions.length : 0 }} aulas</span>
          <span>Participantes: -</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CourseCard',
  props: {
    course: {
      type: Object,
      required: true
    }
  },
  setup() {
    const formatPrice = (price) => {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(price)
    }
    
    const formatDuration = (minutes) => {
      if (!minutes) return '0h'
      
      const hours = Math.floor(minutes / 60)
      const remainingMinutes = minutes % 60
      
      if (hours === 0) {
        return `${remainingMinutes}min`
      } else if (remainingMinutes === 0) {
        return `${hours}h`
      } else {
        return `${hours}h ${remainingMinutes}min`
      }
    }
    
    return {
      formatPrice,
      formatDuration
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
