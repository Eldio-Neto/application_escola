<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Desenvolvimento Web',
                'slug' => 'desenvolvimento-web',
                'description' => 'Cursos focados em desenvolvimento de aplicações web modernas',
                'color' => '#3B82F6',
                'icon' => 'fas fa-code',
                'active' => true
            ],
            [
                'name' => 'Ciência de Dados',
                'slug' => 'ciencia-de-dados',
                'description' => 'Aprenda análise de dados, machine learning e inteligência artificial',
                'color' => '#10B981',
                'icon' => 'fas fa-chart-line',
                'active' => true
            ],
            [
                'name' => 'Mobile',
                'slug' => 'mobile',
                'description' => 'Desenvolvimento de aplicações móveis para iOS e Android',
                'color' => '#8B5CF6',
                'icon' => 'fas fa-mobile-alt',
                'active' => true
            ],
            [
                'name' => 'DevOps',
                'slug' => 'devops',
                'description' => 'Práticas de desenvolvimento e operações para entrega contínua',
                'color' => '#F59E0B',
                'icon' => 'fas fa-server',
                'active' => true
            ],
            [
                'name' => 'UI/UX Design',
                'slug' => 'ui-ux-design',
                'description' => 'Design de interfaces e experiência do usuário',
                'color' => '#EF4444',
                'icon' => 'fas fa-paint-brush',
                'active' => true
            ],
            [
                'name' => 'Marketing Digital',
                'slug' => 'marketing-digital',
                'description' => 'Estratégias de marketing online e growth hacking',
                'color' => '#EC4899',
                'icon' => 'fas fa-bullhorn',
                'active' => true
            ]
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
