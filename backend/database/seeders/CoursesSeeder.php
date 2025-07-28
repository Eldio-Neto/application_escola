<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'name' => 'Laravel Avançado',
                'description' => 'Aprenda Laravel do básico ao avançado com projetos práticos e as melhores práticas de desenvolvimento.',
                'price' => 299.90,
                'active' => true,
                'sessions' => [
                    [
                        'title' => 'Introdução ao Laravel',
                        'description' => 'Conceitos básicos e configuração do ambiente',
                        'start_datetime' => now()->addDays(7)->setHour(19)->setMinute(0),
                        'end_datetime' => now()->addDays(7)->setHour(21)->setMinute(0),
                        'max_participants' => 50,
                    ],
                    [
                        'title' => 'Migrations e Models',
                        'description' => 'Trabalhando com banco de dados no Laravel',
                        'start_datetime' => now()->addDays(14)->setHour(19)->setMinute(0),
                        'end_datetime' => now()->addDays(14)->setHour(21)->setMinute(0),
                        'max_participants' => 50,
                    ],
                    [
                        'title' => 'API REST com Laravel',
                        'description' => 'Criando APIs robustas e escaláveis',
                        'start_datetime' => now()->addDays(21)->setHour(19)->setMinute(0),
                        'end_datetime' => now()->addDays(21)->setHour(21)->setMinute(0),
                        'max_participants' => 50,
                    ]
                ]
            ],
            [
                'name' => 'Vue.js 3 Completo',
                'description' => 'Domine o Vue.js 3 com Composition API, Pinia, e desenvolvimento de SPAs modernas.',
                'price' => 249.90,
                'active' => true,
                'sessions' => [
                    [
                        'title' => 'Fundamentos do Vue.js 3',
                        'description' => 'Conceitos básicos e Composition API',
                        'start_datetime' => now()->addDays(10)->setHour(20)->setMinute(0),
                        'end_datetime' => now()->addDays(10)->setHour(22)->setMinute(0),
                        'max_participants' => 40,
                    ],
                    [
                        'title' => 'Gerenciamento de Estado com Pinia',
                        'description' => 'Estado global e reatividade avançada',
                        'start_datetime' => now()->addDays(17)->setHour(20)->setMinute(0),
                        'end_datetime' => now()->addDays(17)->setHour(22)->setMinute(0),
                        'max_participants' => 40,
                    ]
                ]
            ],
            [
                'name' => 'Python para Data Science',
                'description' => 'Aprenda análise de dados e machine learning com Python, pandas, NumPy e scikit-learn.',
                'price' => 399.90,
                'active' => true,
                'sessions' => [
                    [
                        'title' => 'Introdução ao Python',
                        'description' => 'Sintaxe básica e estruturas de dados',
                        'start_datetime' => now()->addDays(5)->setHour(18)->setMinute(0),
                        'end_datetime' => now()->addDays(5)->setHour(20)->setMinute(0),
                        'max_participants' => 60,
                    ],
                    [
                        'title' => 'Pandas e NumPy',
                        'description' => 'Manipulação e análise de dados',
                        'start_datetime' => now()->addDays(12)->setHour(18)->setMinute(0),
                        'end_datetime' => now()->addDays(12)->setHour(20)->setMinute(0),
                        'max_participants' => 60,
                    ],
                    [
                        'title' => 'Machine Learning Básico',
                        'description' => 'Algoritmos de aprendizado de máquina',
                        'start_datetime' => now()->addDays(19)->setHour(18)->setMinute(0),
                        'end_datetime' => now()->addDays(19)->setHour(20)->setMinute(0),
                        'max_participants' => 60,
                    ]
                ]
            ]
        ];

        foreach ($courses as $courseData) {
            $sessions = $courseData['sessions'];
            unset($courseData['sessions']);

            $course = \App\Models\Course::create($courseData);

            foreach ($sessions as $sessionData) {
                $course->sessions()->create($sessionData);
            }
        }
    }
}
