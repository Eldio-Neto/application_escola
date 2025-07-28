<template>
  <div class="relative">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <span class="text-gray-500 sm:text-sm">{{ currencySymbol }}</span>
      </div>
      <input
        :value="displayValue"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        :class="inputClass"
        type="text"
        inputmode="decimal"
      />
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="help" class="mt-1 text-sm text-gray-500">{{ help }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '0,00'
  },
  currencySymbol: {
    type: String,
    default: 'R$'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  help: {
    type: String,
    default: ''
  },
  inputClass: {
    type: String,
    default: ''
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const displayValue = ref('')
const isFocused = ref(false)

// Formatar número para exibição
const formatCurrency = (value) => {
  if (!value && value !== 0) return ''
  
  const number = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(number)) return ''
  
  return number.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

// Converter string formatada para número
const parseCurrency = (value) => {
  if (!value) return null
  
  // Remove todos os caracteres que não são dígitos ou vírgula
  const cleaned = value.replace(/[^\d,]/g, '')
  
  // Substitui vírgula por ponto para conversão
  const withDot = cleaned.replace(',', '.')
  
  const number = parseFloat(withDot)
  return isNaN(number) ? null : number
}

// Valor computado para exibição
const computedDisplayValue = computed(() => {
  if (isFocused.value) {
    // Durante edição, mostra valor sem formatação para facilitar digitação
    return displayValue.value
  } else {
    // Fora de foco, mostra valor formatado
    return formatCurrency(props.modelValue)
  }
})

const handleInput = (event) => {
  const value = event.target.value
  displayValue.value = value
  
  // Parse do valor e emissão
  const numericValue = parseCurrency(value)
  
  // Validação de limites
  if (numericValue !== null) {
    if (numericValue < props.min) {
      return // Não permite valores abaixo do mínimo
    }
    if (props.max !== null && numericValue > props.max) {
      return // Não permite valores acima do máximo
    }
  }
  
  emit('update:modelValue', numericValue)
}

const handleFocus = (event) => {
  isFocused.value = true
  // Ao focar, mostra o valor sem formatação
  if (props.modelValue) {
    displayValue.value = props.modelValue.toString().replace('.', ',')
  } else {
    displayValue.value = ''
  }
  event.target.select() // Seleciona todo o texto
}

const handleBlur = (event) => {
  isFocused.value = false
  // Ao perder foco, formata o valor
  const numericValue = parseCurrency(displayValue.value)
  if (numericValue !== null) {
    emit('update:modelValue', numericValue)
  }
}

// Watch para mudanças no modelValue
watch(() => props.modelValue, (newValue) => {
  if (!isFocused.value) {
    displayValue.value = formatCurrency(newValue)
  }
}, { immediate: true })

// Inicializar displayValue
displayValue.value = formatCurrency(props.modelValue)
</script>

<style scoped>
/* Remove spinners do input number no Chrome/Safari */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Remove spinners do input number no Firefox */
input[type="number"] {
  -moz-appearance: textfield;
}
</style>
