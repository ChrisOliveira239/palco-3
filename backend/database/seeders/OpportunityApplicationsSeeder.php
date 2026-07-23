<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunityApplicationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $opportunityIds = DB::table('opportunities')->pluck('id');
        $userIds = DB::table('users')->pluck('id')->all();
        $statusOpcoes = ['PENDENTE', 'ACEITO', 'RECUSADO'];

        foreach ($opportunityIds as $opportunityId) {
            $candidatos = collect($userIds)->random(min(rand(1, 3), count($userIds)));

            foreach ($candidatos as $userId) {
                DB::table('opportunity_applications')->insert([
                    'opportunity_id' => $opportunityId,
                    'user_id' => $userId,
                    'opa_mensagem' => fake()->sentence(10),
                    'opa_status' => fake()->randomElement($statusOpcoes),
                    'opa_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
