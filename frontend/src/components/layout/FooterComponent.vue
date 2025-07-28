<template>
  <footer class="bg-gray-800 text-white mt-auto">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Sobre -->
        <div class="col-span-1 md:col-span-2">
          <div class="flex items-center">
            <h3 class="text-2xl font-bold text-white">EscolaOnline</h3>
          </div>
          <p class="mt-4 text-gray-300 text-sm">
            Plataforma moderna para venda e gestão de cursos online. 
            Aprenda com os melhores profissionais e desenvolva suas habilidades.
          </p>
          <div class="mt-6 flex space-x-6">
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <span class="sr-only">Facebook</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <span class="sr-only">Instagram</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.621 5.367 11.988 11.988 11.988c6.62 0 11.987-5.367 11.987-11.988C24.014 5.367 18.647.001 12.017.001zM8.449 16.988c-1.297 0-2.348-1.051-2.348-2.348c0-1.297 1.051-2.348 2.348-2.348c1.297 0 2.348 1.051 2.348 2.348c0 1.297-1.051 2.348-2.348 2.348zM8.449 11.239c-1.297 0-2.348-1.051-2.348-2.348c0-1.297 1.051-2.348 2.348-2.348c1.297 0 2.348 1.051 2.348 2.348c0 1.297-1.051 2.348-2.348 2.348z" clip-rule="evenodd" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition-colors">
              <span class="sr-only">Twitter</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
              </svg>
            </a>
          </div>
        </div>
        
        <!-- Links rápidos -->
        <div>
          <h3 class="text-sm font-semibold text-white tracking-wider uppercase">
            Links Rápidos
          </h3>
          <ul class="mt-4 space-y-4">
            <li>
              <router-link to="/" class="text-base text-gray-300 hover:text-white transition-colors">
                Início
              </router-link>
            </li>
            <li>
              <router-link to="/courses" class="text-base text-gray-300 hover:text-white transition-colors">
                Cursos
              </router-link>
            </li>
            <li>
              <a href="#" class="text-base text-gray-300 hover:text-white transition-colors">
                Sobre Nós
              </a>
            </li>
            <li>
              <a href="#" class="text-base text-gray-300 hover:text-white transition-colors">
                Contato
              </a>
            </li>
          </ul>
        </div>
        
        <!-- Suporte -->
        <div>
          <h3 class="text-sm font-semibold text-white tracking-wider uppercase">
            Suporte
          </h3>
          <ul class="mt-4 space-y-4">
            <li>
              <a href="#" class="text-base text-gray-300 hover:text-white transition-colors">
                Central de Ajuda
              </a>
            </li>
            <li>
              <a href="#" class="text-base text-gray-300 hover:text-white transition-colors">
                FAQ
              </a>
            </li>
            <li>
              <a href="#" class="text-base text-gray-300 hover:text-white transition-colors">
                Termos de Uso
              </a>
            </li>
            <li>
              <a href="#" class="text-base text-gray-300 hover:text-white transition-colors">
                Política de Privacidade
              </a>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="mt-12 border-t border-gray-700 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="text-base text-gray-400">
            &copy; {{ currentYear }} EscolaOnline. Todos os direitos reservados.
          </p>
          <div class="mt-4 md:mt-0 flex space-x-6">
            <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">
              Política de Privacidade
            </a>
            <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">
              Termos de Serviço
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'FooterComponent',
  setup() {
    const currentYear = computed(() => new Date().getFullYear())
    
    return {
      currentYear
    }
  }
}
</script>
