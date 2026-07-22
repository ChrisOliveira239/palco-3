<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = DB::table('groups')->get(['id', 'user_id']);
        $allUserIds = DB::table('users')->pluck('id')->all();

        foreach ($groups as $group) {
            DB::table('group_members')->insert([
                'group_id' => $group->id,
                'user_id' => $group->user_id,
                'grm_papel' => 'admin',
                'created_at' => now(),
            ]);

            $membros = collect($allUserIds)
                ->reject(fn ($id) => $id === $group->user_id)
                ->random(rand(2, 4));

            foreach ($membros as $userId) {
                DB::table('group_members')->insert([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                    'grm_papel' => 'membro',
                    'created_at' => now(),
                ]);
            }
        }
    }
}
