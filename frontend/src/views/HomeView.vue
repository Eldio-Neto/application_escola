<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary-600 to-primary-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
            Transforme sua Carreira com 
            <span class="text-yellow-300">Educação de Qualidade</span>
          </h1>
          <p class="text-xl text-blue-100 max-w-3xl mx-auto mb-8">
            Cursos online desenvolvidos por especialistas do mercado. 
            Metodologia comprovada para acelerar seu crescimento profissional 
            e conquistar melhores oportunidades.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
            <router-link 
              to="/courses" 
              class="btn-primary bg-white text-primary-600 hover:bg-gray-100 px-8 py-3 text-lg font-semibold"
            >
              Ver Cursos Disponíveis
            </router-link>
            <router-link 
              to="/register" 
              class="btn-outline border-white text-white hover:bg-white hover:text-primary-600 px-8 py-3 text-lg font-semibold"
            >
              Comece Agora
            </router-link>
          </div>
          <div class="text-yellow-300 font-semibold text-lg">
            🎯 Mais de 5.000 profissionais já transformaram suas carreiras
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Por que nossa metodologia funciona?
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Uma abordagem completa e estruturada para o seu desenvolvimento profissional
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="bg-primary-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Conteúdo Prático</h3>
            <p class="text-gray-600">
              Cursos focados em resultados reais, com projetos práticos e cases do mercado desenvolvidos por especialistas
            </p>
          </div>

          <div class="text-center">
            <div class="bg-success-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Flexibilidade Total</h3>
            <p class="text-gray-600">
              Estude no seu ritmo, com acesso vitalício ao conteúdo e suporte personalizado para suas dúvidas
            </p>
          </div>

          <div class="text-center">
            <div class="bg-yellow-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Resultados Comprovados</h3>
            <p class="text-gray-600">
              Metodologia testada e aprovada por milhares de profissionais que já alcançaram seus objetivos
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Courses Section -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Cursos Mais Procurados
          </h2>
          <p class="text-xl text-gray-600">
            Escolha entre os cursos mais populares e comece sua transformação hoje mesmo
          </p>
        </div>

        <div v-if="loading" class="flex justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="error" class="text-center">
          <div class="alert-danger">
            {{ error }}
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <CourseCard 
            v-for="course in featuredCourses" 
            :key="course.id" 
            :course="course" 
          />
        </div>

        <div class="text-center mt-12">
          <router-link 
            to="/courses" 
            class="btn-primary inline-flex items-center px-6 py-3 text-lg"
          >
            Explorar Todos os Cursos
            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-primary-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-white mb-4">
            Resultados que Comprovam Nossa Excelência
          </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
          <div>
            <div class="text-4xl font-bold text-white mb-2">5.000+</div>
            <div class="text-blue-100">Profissionais Transformados</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-white mb-2">15+</div>
            <div class="text-blue-100">Cursos Especializados</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-white mb-2">97%</div>
            <div class="text-blue-100">Taxa de Sucesso</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-white mb-2">24h</div>
            <div class="text-blue-100">Suporte Especializado</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 bg-gradient-to-r from-gray-50 to-blue-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            O que você vai conquistar
          </h2>
          <p class="text-xl text-gray-600">
            Benefícios reais que farão diferença na sua carreira
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Aumento Salarial</h3>
              <p class="text-gray-600">Profissionais com nossas certificações têm aumento médio de 40% no salário</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Novas Oportunidades</h3>
              <p class="text-gray-600">Acesso a vagas exclusivas em empresas parceiras e networking especializado</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Conhecimento Atualizado</h3>
              <p class="text-gray-600">Conteúdo sempre atualizado com as últimas tendências e tecnologias do mercado</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Mentoria Especializada</h3>
              <p class="text-gray-600">Suporte direto com profissionais experientes do mercado</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Portfolio Profissional</h3>
              <p class="text-gray-600">Desenvolva projetos reais para impressionar recrutadores</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Certificação Reconhecida</h3>
              <p class="text-gray-600">Certificados válidos no mercado que comprovam sua expertise</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            O que nossos alunos dizem
          </h2>
          <p class="text-xl text-gray-600">
            Histórias reais de profissionais que transformaram suas carreiras
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-gray-50 p-6 rounded-lg">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                <span class="text-primary-600 font-bold">MR</span>
              </div>
              <div class="ml-4">
                <h4 class="font-semibold text-gray-900">Maria Rosa</h4>
                <p class="text-sm text-gray-600">Desenvolvedora Full Stack</p>
              </div>
            </div>
            <p class="text-gray-700">
              "Em 6 meses consegui uma promoção para Sênior. O conteúdo é extremamente prático e me deu todas as ferramentas necessárias para evoluir na minha carreira."
            </p>
            <div class="flex text-yellow-400 mt-4">
              ⭐⭐⭐⭐⭐
            </div>
          </div>

          <div class="bg-gray-50 p-6 rounded-lg">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-success-100 rounded-full flex items-center justify-center">
                <span class="text-success-600 font-bold">JS</span>
              </div>
              <div class="ml-4">
                <h4 class="font-semibold text-gray-900">João Silva</h4>
                <p class="text-sm text-gray-600">Cientista de Dados</p>
              </div>
            </div>
            <p class="text-gray-700">
              "Mudei completamente de área aos 35 anos. Os projetos práticos me deram confiança para trabalhar com dados reais e hoje atuo em uma multinacional."
            </p>
            <div class="flex text-yellow-400 mt-4">
              ⭐⭐⭐⭐⭐
            </div>
          </div>

          <div class="bg-gray-50 p-6 rounded-lg">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <span class="text-yellow-600 font-bold">AC</span>
              </div>
              <div class="ml-4">
                <h4 class="font-semibold text-gray-900">Ana Costa</h4>
                <p class="text-sm text-gray-600">Tech Lead</p>
              </div>
            </div>
            <p class="text-gray-700">
              "O curso de Laravel me ajudou a liderar projetos complexos. A metodologia focada em boas práticas fez toda diferença na qualidade do meu trabalho."
            </p>
            <div class="flex text-yellow-400 mt-4">
              ⭐⭐⭐⭐⭐
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
          Pronto para dar o próximo passo na sua carreira?
        </h2>
        <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
          Não perca mais tempo. Junte-se aos milhares de profissionais que já transformaram 
          suas vidas com nossa metodologia comprovada.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <router-link 
            to="/register" 
            class="btn-primary bg-primary-600 hover:bg-primary-700 px-8 py-3 text-lg font-semibold"
          >
            Começar Minha Transformação
          </router-link>
          <router-link 
            to="/courses" 
            class="btn-outline border-gray-300 text-gray-300 hover:bg-gray-300 hover:text-gray-900 px-8 py-3 text-lg font-semibold"
          >
            Ver Cursos Disponíveis
          </router-link>
        </div>
        <div class="text-yellow-300 font-semibold">
          ⚡ Garantia de 30 dias ou seu dinheiro de volta
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useCoursesStore } from '@/stores/courses'
import CourseCard from '@/components/courses/CourseCard.vue'

export default {
  name: 'HomeView',
  components: {
    CourseCard
  },
  setup() {
    const coursesStore = useCoursesStore()
    
    const loading = computed(() => coursesStore.loading)
    const error = computed(() => coursesStore.error)
    const courses = computed(() => coursesStore.publishedCourses)
    
    // Mostrar apenas os primeiros 6 cursos na home
    const featuredCourses = computed(() => courses.value.slice(0, 6))
    
    onMounted(async () => {
      try {
        await coursesStore.fetchCourses()
      } catch (error) {
        console.error('Erro ao carregar cursos:', error)
      }
    })
    
    return {
      loading,
      error,
      featuredCourses
    }
  }
}
</script>
