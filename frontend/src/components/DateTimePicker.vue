<template>
  <div class="date-time-picker mb-4">
    <div class="flex justify-between items-center mb-3">
      <h4 class="text-lg font-medium text-gray-900">{{ title }}</h4>
      <button
        type="button"
        @click="addDateTime"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Adicionar Data
      </button>
    </div>

    <div class="space-y-4">
      <div
        v-for="(datetime, index) in dateTimeList"
        :key="index"
        class="bg-gray-50 p-4 rounded-lg border border-gray-200"
      >
        <div class="flex justify-between items-start mb-3">
          <h5 class="text-sm font-medium text-gray-700">{{ dateTitle }} {{ index + 1 }}</h5>
          <button
            v-if="dateTimeList.length > 1"
            type="button"
            @click="removeDateTime(index)"
            class="text-red-600 hover:text-red-800"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ startLabel }}
            </label>
            <input
              type="datetime-local"
              v-model="datetime.start_datetime"
              @change="updateDateTime(index)"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ endLabel }}
            </label>
            <input
              type="datetime-local"
              v-model="datetime.end_datetime"
              @change="updateDateTime(index)"
              :min="datetime.start_datetime"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              required
            />
          </div>
        </div>

        <div v-if="showTitle" class="mt-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Título
          </label>
          <input
            type="text"
            v-model="datetime.title"
            @input="updateDateTime(index)"
            placeholder="Título da sessão"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            required
          />
        </div>

        <div v-if="showDescription" class="mt-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Descrição
          </label>
          <textarea
            v-model="datetime.description"
            @input="updateDateTime(index)"
            rows="2"
            placeholder="Descrição da sessão"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          ></textarea>
        </div>

        <div v-if="showMaxParticipants" class="mt-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Máximo de Participantes
          </label>
          <input
            type="number"
            v-model="datetime.max_participants"
            @input="updateDateTime(index)"
            min="1"
            placeholder="Número máximo de participantes"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'Datas e Horários'
  },
  dateTitle: {
    type: String,
    default: 'Data'
  },
  startLabel: {
    type: String,
    default: 'Data e Hora de Início'
  },
  endLabel: {
    type: String,
    default: 'Data e Hora de Fim'
  },
  showTitle: {
    type: Boolean,
    default: false
  },
  showDescription: {
    type: Boolean,
    default: false
  },
  showMaxParticipants: {
    type: Boolean,
    default: false
  },
  minDates: {
    type: Number,
    default: 1
  }
})

const emit = defineEmits(['update:modelValue'])

const dateTimeList = ref([])

// Inicializar com valores do prop ou uma data vazia
const initializeDateTimeList = () => {
  if (props.modelValue && props.modelValue.length > 0) {
    dateTimeList.value = [...props.modelValue]
  } else {
    dateTimeList.value = [createEmptyDateTime()]
  }
}

const createEmptyDateTime = () => {
  const now = new Date()
  const endTime = new Date(now.getTime() + 2 * 60 * 60 * 1000) // 2 horas depois
  
  return {
    title: props.showTitle ? '' : undefined,
    description: props.showDescription ? '' : undefined,
    start_datetime: now.toISOString().slice(0, 16),
    end_datetime: endTime.toISOString().slice(0, 16),
    max_participants: props.showMaxParticipants ? null : undefined
  }
}

const addDateTime = () => {
  dateTimeList.value.push(createEmptyDateTime())
  updateParent()
}

const removeDateTime = (index) => {
  if (dateTimeList.value.length > props.minDates) {
    dateTimeList.value.splice(index, 1)
    updateParent()
  }
}

const updateDateTime = (index) => {
  updateParent()
}

const updateParent = () => {
  // Filtrar campos undefined antes de emitir
  const cleanData = dateTimeList.value.map(item => {
    const cleanItem = {}
    Object.keys(item).forEach(key => {
      if (item[key] !== undefined) {
        cleanItem[key] = item[key]
      }
    })
    return cleanItem
  })
  
  emit('update:modelValue', cleanData)
}

// Watch para mudanças no modelValue
watch(() => props.modelValue, () => {
  initializeDateTimeList()
}, { immediate: true, deep: true })

// Inicializar
initializeDateTimeList()
</script>

<style scoped>
/* Estilos personalizados se necessário */
</style>
