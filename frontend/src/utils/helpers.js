export const currencyUtils = {
  formatBRL(amount) {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(amount / 100)
  },

  formatCurrency(amount) {
    return this.formatBRL(amount)
  },

  parseCurrency(value) {
    if (typeof value === 'number') return value
    
    // Remove tudo que não é dígito ou vírgula/ponto
    const cleaned = value.toString().replace(/[^\d,.-]/g, '')
    
    // Substitui vírgula por ponto
    const normalized = cleaned.replace(',', '.')
    
    // Converte para número e multiplica por 100 (centavos)
    return Math.round(parseFloat(normalized || 0) * 100)
  },

  formatInstallmentValue(amount, installments, interestRate = 0) {
    const totalAmount = amount * (1 + (interestRate / 100))
    const installmentValue = totalAmount / installments
    
    return {
      installmentValue: Math.round(installmentValue),
      totalAmount: Math.round(totalAmount),
      totalInterest: Math.round(totalAmount - amount),
      formatted: {
        installmentValue: this.formatBRL(Math.round(installmentValue)),
        totalAmount: this.formatBRL(Math.round(totalAmount)),
        totalInterest: this.formatBRL(Math.round(totalAmount - amount))
      }
    }
  },

  calculateInstallments(amount, maxInstallments = 12, interestRates = {}) {
    const installments = []
    
    for (let i = 1; i <= maxInstallments; i++) {
      const interestRate = interestRates[i] || 0
      const calculation = this.formatInstallmentValue(amount, i, interestRate)
      
      installments.push({
        installments: i,
        interestRate,
        ...calculation
      })
    }
    
    return installments
  }
}

export const validationUtils = {
  isValidCPF(cpf) {
    if (!cpf) return false
    
    // Remove caracteres especiais
    cpf = cpf.replace(/[^\d]/g, '')
    
    // Verifica se tem 11 dígitos
    if (cpf.length !== 11) return false
    
    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1{10}$/.test(cpf)) return false
    
    // Validação dos dígitos verificadores
    let sum = 0
    let weight = 10
    
    for (let i = 0; i < 9; i++) {
      sum += parseInt(cpf.charAt(i)) * weight--
    }
    
    let digit = 11 - (sum % 11)
    if (digit >= 10) digit = 0
    
    if (parseInt(cpf.charAt(9)) !== digit) return false
    
    sum = 0
    weight = 11
    
    for (let i = 0; i < 10; i++) {
      sum += parseInt(cpf.charAt(i)) * weight--
    }
    
    digit = 11 - (sum % 11)
    if (digit >= 10) digit = 0
    
    return parseInt(cpf.charAt(10)) === digit
  },

  isValidCNPJ(cnpj) {
    if (!cnpj) return false
    
    // Remove caracteres especiais
    cnpj = cnpj.replace(/[^\d]/g, '')
    
    // Verifica se tem 14 dígitos
    if (cnpj.length !== 14) return false
    
    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1{13}$/.test(cnpj)) return false
    
    // Validação do primeiro dígito verificador
    let sum = 0
    let weight = 5
    
    for (let i = 0; i < 12; i++) {
      sum += parseInt(cnpj.charAt(i)) * weight
      weight = weight === 2 ? 9 : weight - 1
    }
    
    let digit = sum % 11 < 2 ? 0 : 11 - (sum % 11)
    
    if (parseInt(cnpj.charAt(12)) !== digit) return false
    
    // Validação do segundo dígito verificador
    sum = 0
    weight = 6
    
    for (let i = 0; i < 13; i++) {
      sum += parseInt(cnpj.charAt(i)) * weight
      weight = weight === 2 ? 9 : weight - 1
    }
    
    digit = sum % 11 < 2 ? 0 : 11 - (sum % 11)
    
    return parseInt(cnpj.charAt(13)) === digit
  },

  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  },

  isValidPhone(phone) {
    if (!phone) return false
    
    // Remove caracteres especiais
    const cleaned = phone.replace(/[^\d]/g, '')
    
    // Verifica se tem 10 ou 11 dígitos (celular/fixo)
    return cleaned.length >= 10 && cleaned.length <= 11
  },

  isValidCreditCard(number) {
    if (!number) return false
    
    // Remove espaços e caracteres especiais
    const cleaned = number.replace(/[^\d]/g, '')
    
    // Verifica o comprimento
    if (cleaned.length < 13 || cleaned.length > 19) return false
    
    // Algoritmo de Luhn
    let sum = 0
    let isEven = false
    
    for (let i = cleaned.length - 1; i >= 0; i--) {
      let digit = parseInt(cleaned.charAt(i))
      
      if (isEven) {
        digit *= 2
        if (digit > 9) {
          digit -= 9
        }
      }
      
      sum += digit
      isEven = !isEven
    }
    
    return sum % 10 === 0
  },

  isValidCVV(cvv) {
    return /^\d{3,4}$/.test(cvv)
  },

  isValidExpiryDate(month, year) {
    const currentDate = new Date()
    const currentYear = currentDate.getFullYear()
    const currentMonth = currentDate.getMonth() + 1
    
    const expMonth = parseInt(month)
    const expYear = parseInt(year)
    
    if (expMonth < 1 || expMonth > 12) return false
    if (expYear < currentYear) return false
    if (expYear === currentYear && expMonth < currentMonth) return false
    
    return true
  }
}

export const formatUtils = {
  formatCPF(value) {
    if (!value) return ''
    
    const cleaned = value.replace(/[^\d]/g, '')
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{3})(\d{2})$/)
    
    if (match) {
      return `${match[1]}.${match[2]}.${match[3]}-${match[4]}`
    }
    
    return cleaned
  },

  formatCNPJ(value) {
    if (!value) return ''
    
    const cleaned = value.replace(/[^\d]/g, '')
    const match = cleaned.match(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/)
    
    if (match) {
      return `${match[1]}.${match[2]}.${match[3]}/${match[4]}-${match[5]}`
    }
    
    return cleaned
  },

  formatPhone(value) {
    if (!value) return ''
    
    const cleaned = value.replace(/[^\d]/g, '')
    
    if (cleaned.length === 11) {
      const match = cleaned.match(/^(\d{2})(\d{5})(\d{4})$/)
      if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`
      }
    } else if (cleaned.length === 10) {
      const match = cleaned.match(/^(\d{2})(\d{4})(\d{4})$/)
      if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`
      }
    }
    
    return cleaned
  },

  formatCreditCard(value) {
    if (!value) return ''
    
    const cleaned = value.replace(/[^\d]/g, '')
    const match = cleaned.match(/.{1,4}/g)
    
    if (match) {
      return match.join(' ').substr(0, 19)
    }
    
    return cleaned
  },

  formatCEP(value) {
    if (!value) return ''
    
    const cleaned = value.replace(/[^\d]/g, '')
    const match = cleaned.match(/^(\d{5})(\d{3})$/)
    
    if (match) {
      return `${match[1]}-${match[2]}`
    }
    
    return cleaned
  }
}

export const dateUtils = {
  formatDate(date, format = 'dd/MM/yyyy') {
    if (!date) return ''
    
    const d = new Date(date)
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const year = d.getFullYear()
    
    return format
      .replace('dd', day)
      .replace('MM', month)
      .replace('yyyy', year)
  },

  formatDateTime(date, format = 'dd/MM/yyyy HH:mm') {
    if (!date) return ''
    
    const d = new Date(date)
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const year = d.getFullYear()
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    
    return format
      .replace('dd', day)
      .replace('MM', month)
      .replace('yyyy', year)
      .replace('HH', hours)
      .replace('mm', minutes)
  },

  isValidDate(dateString) {
    const date = new Date(dateString)
    return date instanceof Date && !isNaN(date)
  },

  addDays(date, days) {
    const result = new Date(date)
    result.setDate(result.getDate() + days)
    return result
  },

  diffInDays(date1, date2) {
    const oneDay = 24 * 60 * 60 * 1000
    const firstDate = new Date(date1)
    const secondDate = new Date(date2)
    
    return Math.round(Math.abs((firstDate - secondDate) / oneDay))
  }
}
