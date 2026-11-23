<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ItFlowBoardTest extends TestCase
{
    use DatabaseMigrations;

    private function user(string $role, string $nip): User
    {
        $id = DB::table('users')->insertGetId(['nip' => $nip, 'name' => $role, 'role' => $role,
            'password' => bcrypt('unused'), 'created_at' => now(), 'updated_at' => now()]);
        return User::findOrFail($id);
    }

    public function test_project_task_detail_and_role_access_persist(): void
    {
        $atl = $this->user('atl', '0000000001');
        $staff = $this->user('it_staff', '0000000002');
        $project = $this->actingAs($atl)->postJson('/board/api/projects', ['name' => 'Test Project', 'prefix' => 'TST', 'sub_projects' => ['Initial']])
            ->assertCreated()->json();
        $sub = DB::table('sub_projects')->where('project_id', $project['id'])->first();
        $task = $this->postJson('/board/api/tasks', ['project_id' => $project['id'], 'sub_project_id' => $sub->id,
            'title' => 'Server setup', 'status' => 'todo', 'category' => 'maintenance', 'priority' => 'high',
            'assignee_id' => $atl->id, 'subtasks' => ['Prepare config']])->assertCreated()->json();
        $this->assertSame('TST-101', $task['code']);
        $this->patchJson('/board/api/tasks/'.$task['id'], ['title' => 'Server setup updated', 'description' => 'Network config',
            'status' => 'in_progress', 'sub_project_id' => $sub->id, 'category' => 'maintenance', 'priority' => 'high',
            'assignee_id' => $atl->id, 'subtasks' => [['id' => $task['subtasks'][0]['id'], 'title' => 'Prepare config', 'done' => true]],
            'comment' => 'Ready for test'])->assertOk();
        $this->patchJson('/board/api/tasks/'.$task['id'].'/status', ['status' => 'done'])->assertOk();
        $this->getJson('/board/api/tasks/'.$task['id'])->assertOk()->assertJsonPath('status', 'done')
            ->assertJsonPath('subtasks.0.done', 1)->assertJsonPath('comments.0.text', 'Ready for test');
        $this->actingAs($staff)->getJson('/board/api/tasks/'.$task['id'])->assertNotFound();
        $this->patchJson('/board/api/tasks/'.$task['id'].'/status', ['status' => 'todo'])->assertNotFound();
        $this->get('/board/api/export')->assertForbidden();
    }

    public function test_board_renders_reference_and_rejects_invalid_task_pairs(): void
    {
        $staff = $this->user('it_staff', '0000000003');
        $project = $this->actingAs($staff)->postJson('/board/api/projects', ['name' => 'Network', 'prefix' => 'NET',
            'sub_projects' => ['Routing']])->assertCreated()->json();
        $other = $this->postJson('/board/api/projects', ['name' => 'Devices', 'prefix' => 'DEV',
            'sub_projects' => ['Hardware']])->assertCreated()->json();
        $sub = DB::table('sub_projects')->where('project_id', $project['id'])->first();
        $this->postJson('/board/api/tasks', ['project_id' => $other['id'], 'sub_project_id' => $sub->id, 'title' => 'Invalid',
            'status' => 'todo', 'category' => 'maintenance', 'priority' => 'high', 'assignee_id' => $staff->id])->assertStatus(422);
        $this->postJson('/board/api/tasks', ['project_id' => $project['id'], 'sub_project_id' => $sub->id, 'title' => 'Invalid',
            'status' => 'todo', 'category' => 'development', 'priority' => 'high', 'assignee_id' => $staff->id])->assertStatus(422);
        $this->get('/board')->assertOk()->assertSee('mainAppContainer')->assertSee('detailModal')->assertSee('newBoardModal');
    }

    public function test_atl_can_download_three_sheet_excel(): void
    {
        $atl = $this->user('atl', '0000000004');
        $response = $this->actingAs($atl)->get('/board/api/export?period=1d');
        $response->assertOk()->assertDownload('Rekap_ITFlow_1D_'.now()->format('Y-m-d').'.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();
        $this->assertSame('PK', file_get_contents($path, false, null, 0, 2));
        $workbook = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $this->assertSame(['Rekap Detail Task', 'Rekap Kinerja PIC', 'Rekap Per Project'], $workbook->getSheetNames());
    }
}
