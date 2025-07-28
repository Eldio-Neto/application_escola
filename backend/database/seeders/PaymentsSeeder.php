<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Enrollment;

class PaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::where('user_type', 'student')->first();
        $courses = Course::all();

        if (!$student || $courses->isEmpty()) {
            $this->command->info('Nenhum estudante ou curso encontrado. Execute os seeders principais primeiro.');
            return;
        }

        $this->command->info('Criando pagamentos e matrículas de teste...');

        // Criar pagamentos aprovados para demonstração
        foreach ($courses->take(3) as $course) {
            $payment = Payment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'credit_card',
                'status' => 'paid',
                'paid_at' => now()->subDays(rand(1, 30)),
                'getnet_payment_id' => 'demo_cc_' . uniqid(),
                'getnet_order_id' => 'order_' . uniqid(),
            ]);

            // Criar matrícula ativa
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'payment_id' => $payment->id,
                'status' => 'active',
                'enrolled_at' => $payment->paid_at,
            ]);

            $this->command->info("Pagamento aprovado criado para o curso: {$course->title}");
        }

        // Criar alguns pagamentos pendentes para demonstração
        if ($courses->count() > 3) {
            $pendingCourse = $courses->skip(3)->first();
            
            Payment::create([
                'user_id' => $student->id,
                'course_id' => $pendingCourse->id,
                'amount' => $pendingCourse->price,
                'payment_method' => 'boleto',
                'status' => 'pending',
                'boleto_url' => 'https://exemplo.com/boleto/demo123',
                'boleto_barcode' => '23793.38128 60000.000000 00000.000000 1 88880000015000',
                'due_date' => now()->addDays(3),
            ]);

            $this->command->info("Pagamento via boleto (pendente) criado para o curso: {$pendingCourse->title}");

            // Criar um pagamento cancelado
            if ($courses->count() > 4) {
                $cancelledCourse = $courses->skip(4)->first();
                
                Payment::create([
                    'user_id' => $student->id,
                    'course_id' => $cancelledCourse->id,
                    'amount' => $cancelledCourse->price,
                    'payment_method' => 'credit_card',
                    'status' => 'cancelled',
                    'getnet_payment_id' => 'demo_cancelled_' . uniqid(),
                    'getnet_order_id' => 'order_cancelled_' . uniqid(),
                ]);

                $this->command->info("Pagamento cancelado criado para o curso: {$cancelledCourse->title}");
            }
        }

        $this->command->info('Seeders de pagamentos executados com sucesso!');
    }
}
