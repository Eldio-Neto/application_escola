<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard do Estudante</h1>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card">
          <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900">Cursos Matriculados</h3>
            <p class="text-3xl font-bold text-primary-600">0</p>
          </div>
        </div>
        
        <div class="card">
          <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900">Progresso Médio</h3>
            <p class="text-3xl font-bold text-success-600">0%</p>
          </div>
        </div>
        
        <div class="card">
          <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900">Certificados</h3>
            <p class="text-3xl font-bold text-warning-600">0</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DashboardView'
}
</script>
