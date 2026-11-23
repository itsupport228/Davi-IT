<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedBoards($this->seedTeam());
        });
    }

    private function seedTeam(): array
    {
        $accounts = [
            ['2202350215', 'FARHAN HAIDAR ROSADI', 'atl'],
            ['2302540426', 'JUHDI ROSADI', 'tl'],
            ['2303550431', 'ZULFIAN', 'tl'],
            ['0226111611', 'ELDONI MOSUL', 'it_staff'],
            ['2509981167', 'DHIAURRAHMAN RAZIQ RAMADHAN', 'it_staff'],
            ['0226111591', 'MUHAMMAD RANGGA PRAMUDYA', 'it_staff'],
            ['2407830881', 'ARIS ANGGARA PUTRA', 'it_staff'],
            ['0126071433', 'AHMAD DAVI RAMADANI', 'it_staff'],
            ['2307640531', 'DIMAS TRI SAPUTRA', 'it_staff'],
            ['2403780745', 'MOCHAMMAD ATHAR RIZALDI', 'it_staff'],
            ['0226101538', 'DESY NURSALSABILA', 'it_staff'],
        ];

        $userIds = [];
        foreach ($accounts as [$nip, $name, $role]) {
            $existingId = DB::table('users')->where('nip', $nip)->value('id');
            $userIds[$nip] = $existingId ?? DB::table('users')->insertGetId([
                'nip' => $nip,
                'name' => $name,
                'role' => $role,
                'password' => Hash::make('it123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $assignments = [
            ['2302540426', '0226111611'],
            ['2302540426', '2509981167'],
            ['2303550431', '0226111591'],
            ['2303550431', '2407830881'],
            ['2303550431', '2509981167'],
        ];

        foreach ($assignments as [$tlNip, $staffNip]) {
            DB::table('tl_staff')->updateOrInsert([
                'tl_id' => $userIds[$tlNip],
                'staff_id' => $userIds[$staffNip],
            ]);
        }

        return $userIds;
    }

    private function seedBoards(array $userIds): void
    {
        $boards = [
            'INF' => ['Infrastructure & Network', [
                'Core Network & Failover',
                'CCTV & Physical Security',
                'Wi-Fi & Access Point',
            ]],
            'DEV' => ['Internal Web & Apps', [
                'Portal Ticketing & SSO',
                'REST API & Integrasi ERP',
                'Dashboard Monitoring',
            ]],
            'OPS' => ['Daily IT Operations', [
                'Onboarding & Asset Setup',
                'Backup & Patch Rutin',
            ]],
        ];

        $examples = [
            'INF' => [
                [0, 'Uji failover koneksi ISP', 'maintenance', 'critical', 'in_progress', '0126071433'],
                [2, 'Validasi cakupan Wi-Fi ruang rapat', 'maintenance', 'low', 'todo', '2509981167'],
                [1, 'Periksa rekaman kamera area server', 'maintenance', 'high', 'done', '2407830881'],
            ],
            'DEV' => [
                [2, 'Buat dashboard monitoring pemakaian jaringan', 'development', 'great', 'in_progress', '0226111591'],
                [1, 'Tambahkan pencatatan sinkronisasi ERP', 'development', 'basic', 'done', '0226111611'],
                [0, 'Rancang alur integrasi SSO', 'development', 'epic', 'todo', '2509981167'],
            ],
            'OPS' => [
                [1, 'Verifikasi hasil backup SAP', 'maintenance', 'high', 'done', '2307640531'],
                [0, 'Siapkan checklist onboarding staf', 'maintenance', 'low', 'todo', '0226101538'],
            ],
        ];

        foreach ($boards as $prefix => [$name, $subProjectNames]) {
            if (DB::table('projects')->where('prefix', $prefix)->exists()) {
                continue;
            }

            $timestamp = now();
            $projectId = DB::table('projects')->insertGetId([
                'prefix' => $prefix,
                'name' => $name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $subProjectIds = [];
            foreach ($subProjectNames as $subProjectName) {
                $subProjectIds[] = DB::table('sub_projects')->insertGetId([
                    'project_id' => $projectId,
                    'name' => $subProjectName,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            $nextNumber = 101;
            foreach ($examples[$prefix] as [$subIndex, $title, $category, $priority, $status, $nip]) {
                $taskId = DB::table('tasks')->insertGetId([
                    'project_id' => $projectId,
                    'sub_project_id' => $subProjectIds[$subIndex],
                    'code' => $prefix . '-' . $nextNumber,
                    'title' => $title,
                    'category' => $category,
                    'priority' => $priority,
                    'status' => $status,
                    'assignee_id' => $userIds[$nip],
                    'reporter_id' => $userIds['2202350215'],
                    'created_at' => $timestamp->copy()->subDays(2),
                    'updated_at' => $timestamp,
                    'completed_at' => $status === 'done' ? $timestamp : null,
                ]);
                $nextNumber++;

                if ($prefix === 'INF' && $subIndex === 0) {
                    $this->seedFailoverDetails($taskId, $userIds[$nip]);
                }
            }

            DB::table('projects')->where('id', $projectId)
                ->update(['next_task_number' => $nextNumber]);
        }
    }

    private function seedFailoverDetails(int $taskId, int $authorId): void
    {
        $timestamp = now();

        DB::table('subtasks')->insert([
            [
                'task_id' => $taskId,
                'title' => 'Catat kondisi koneksi ISP utama',
                'is_done' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'task_id' => $taskId,
                'title' => 'Ukur waktu perpindahan ke jalur cadangan',
                'is_done' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);

        DB::table('task_comments')->insert([
            'task_id' => $taskId,
            'user_id' => $authorId,
            'body' => 'Kondisi awal sudah dicatat. Pengujian perpindahan jalur dijadwalkan berikutnya.',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
