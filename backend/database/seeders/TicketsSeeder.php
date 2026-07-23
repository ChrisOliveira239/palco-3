<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ticketTypes = DB::table('ticket_types')->get(['id', 'tit_quantidade_total']);
        $userIds = DB::table('users')->pluck('id')->all();

        foreach ($ticketTypes as $ticketType) {
            $vendidos = rand(0, min(5, $ticketType->tit_quantidade_total));

            for ($i = 0; $i < $vendidos; $i++) {
                $status = fake()->randomElement(['VALIDO', 'VALIDO', 'VALIDO', 'VALIDO', 'USADO', 'CANCELADO']);

                DB::table('tickets')->insert([
                    'ticket_type_id' => $ticketType->id,
                    'user_id' => fake()->randomElement($userIds),
                    'tic_codigo_qr' => (string) Str::uuid(),
                    'tic_status' => $status,
                    'tic_comprado_em' => now()->subDays(rand(1, 30)),
                    'tic_usado_em' => $status === 'USADO' ? now()->subDays(rand(0, 29)) : null,
                    'tic_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('ticket_types')
                ->where('id', $ticketType->id)
                ->update(['tit_quantidade_vendida' => $vendidos]);
        }
    }
}
