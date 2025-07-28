<template>
  <div class="modules-manager mb-4">
    <div class="flex justify-between items-center mb-3">
      <h4 class="text-lg font-medium text-gray-900">{{ title }}</h4>
      <button
        type="button"
        @click="addModule"
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Adicionar Módulo
      </button>
    </div>

    <div class="space-y-4">
      <div
        v-for="(module, index) in modulesList"
        :key="index"
        class="bg-gray-50 p-4 rounded-lg border border-gray-200"
      >
        <div class="flex justify-between items-start mb-3">
          <h5 class="text-sm font-medium text-gray-700">Módulo {{ index + 1 }}</h5>
          <div class="flex items-center space-x-2">
            <button
              v-if="index > 0"
              type="button"
              @click="moveModule(index, 'up')"
              class="text-gray-500 hover:text-gray-700"
              title="Mover para cima"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
              </svg>
            </button>
            <button
              v-if="index < modulesList.length - 1"
              type="button"
              @click="moveModule(index, 'down')"
              class="text-gray-500 hover:text-gray-700"
              title="Mover para baixo"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>
            <button
              v-if="modulesList.length > 1"
              type="button"
              @click="removeModule(index)"
              class="text-red-600 hover:text-red-800"
              title="Remover módulo"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Título do Módulo *
            </label>
            <input
              type="text"
              v-model="module.title"
              @input="updateModule(index)"
              placeholder="Ex: Introdução ao curso"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Descrição do Módulo
            </label>
            <textarea
              v-model="module.description"
              @input="updateModule(index)"
              rows="3"
              placeholder="Descrição detalhada do que será abordado neste módulo"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            ></textarea>
          </div>

          <div class="text-xs text-gray-500">
            Ordem: {{ index + 1 }}
          </div>
        </div>
      </div>

      <div v-if="modulesList.length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <p class="mt-2">Nenhum módulo adicionado</p>
        <p class="text-sm">Clique em "Adicionar Módulo" para começar</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'Módulos do Curso'
  },
  minModules: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['update:modelValue'])

const modulesList = ref([])

// Inicializar com valores do prop ou módulo vazio
const initializeModulesList = () => {
  if (props.modelValue && props.modelValue.length > 0) {
    modulesList.value = [...props.modelValue]
  } else if (props.minModules > 0) {
    modulesList.value = Array(props.minModules).fill().map(() => createEmptyModule())
  } else {
    modulesList.value = []
  }
}

const createEmptyModule = () => {
  return {
    title: '',
    description: '',
    order: modulesList.value.length + 1
  }
}

const addModule = () => {
  const newModule = createEmptyModule()
  newModule.order = modulesList.value.length + 1
  modulesList.value.push(newModule)
  updateParent()
}

const removeModule = (index) => {
  if (modulesList.value.length > props.minModules) {
    modulesList.value.splice(index, 1)
    // Reordenar os módulos
    reorderModules()
    updateParent()
  }
}

const moveModule = (index, direction) => {
  const newIndex = direction === 'up' ? index - 1 : index + 1
  
  if (newIndex >= 0 && newIndex < modulesList.value.length) {
    const temp = modulesList.value[index]
    modulesList.value[index] = modulesList.value[newIndex]
    modulesList.value[newIndex] = temp
    
    // Reordenar os módulos
    reorderModules()
    updateParent()
  }
}

const reorderModules = () => {
  modulesList.value.forEach((module, index) => {
    module.order = index + 1
  })
}

const updateModule = (index) => {
  updateParent()
}

const updateParent = () => {
  // Filtrar módulos vazios (sem título)
  const validModules = modulesList.value.filter(module => module.title.trim() !== '')
  emit('update:modelValue', validModules)
}

// Watch para mudanças no modelValue
watch(() => props.modelValue, () => {
  initializeModulesList()
}, { immediate: true, deep: true })

// Inicializar
initializeModulesList()
</script>

<style scoped>
/* Estilos personalizados se necessário */
</style>
