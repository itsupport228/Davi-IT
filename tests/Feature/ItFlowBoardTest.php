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
            'password' => bcrypt('unused'), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return User::findOrFail($id);
    }

    public function test_only_atl_can_manage_users_and_history_blocks_permanent_delete(): void
    {
        $atl = $this->user('atl', '0000000101');
        $tl = $this->user('tl', '0000000102');
        $staff = $this->user('it_staff', '0000000103');

        $this->actingAs($tl)->getJson('/board/api/users')->assertForbidden();
        $created = $this->actingAs($atl)->postJson('/board/api/users', [
            'name' => 'New Staff', 'nip' => '0000000104', 'role' => 'it_staff', 'password' => 'password123', 'assignment_mode' => 'tl', 'tl_ids' => [$tl->id],
        ])->assertCreated()->json();
        $this->assertDatabaseHas('tl_staff', ['tl_id' => $tl->id, 'staff_id' => $created['id']]);

        $project = $this->actingAs($atl)->postJson('/board/api/projects', ['name' => 'History', 'prefix' => 'HIS'])->json();
        $task = $this->postJson('/board/api/tasks', ['project_id' => $project['id'], 'sub_project_id' => null, 'title' => 'Historical', 'status' => 'todo', 'category' => 'maintenance', 'priority' => 'high', 'assignee_id' => $created['id']])->json();
        $this->actingAs($atl)->deleteJson('/board/api/users/'.$created['id'])->assertJsonPath('deactivated', true);
        $this->assertDatabaseHas('users', ['id' => $created['id'], 'is_active' => false]);

        DB::table('users')->where('id', $created['id'])->update(['is_active' => false]);
        auth()->logout();
        $this->post('/login', ['nip' => '0000000104', 'password' => 'password123'])->assertSessionHasErrors('nip');
        $this->actingAs($atl)->getJson('/board/api/meta')->assertJsonMissing(['name' => 'New Staff']);
        $this->assertDatabaseHas('tasks', ['id' => $task['id'], 'assignee_id' => $created['id']]);
        DB::table('tasks')->where('id', $task['id'])->delete();
    }

    public function test_staff_assignment_modes_and_leading_zero_nip_are_validated(): void
    {
        $atl = $this->user('atl', '0000000111');
        $tlOne = $this->user('tl', '0000000112');
        $tlTwo = $this->user('tl', '0000000113');

        $direct = $this->actingAs($atl)->postJson('/board/api/users', ['name' => 'Direct Staff', 'nip' => '0012345678', 'role' => 'it_staff', 'password' => 'password123', 'assignment_mode' => 'atl', 'tl_ids' => []])->assertCreated()->json();
        $this->assertSame('0012345678', $direct['nip']);
        $this->assertDatabaseMissing('tl_staff', ['staff_id' => $direct['id']]);

        $multi = $this->postJson('/board/api/users', ['name' => 'Multi Staff', 'nip' => '0012345679', 'role' => 'it_staff', 'password' => 'password123', 'assignment_mode' => 'tl', 'tl_ids' => [$tlOne->id, $tlTwo->id]])->assertCreated()->json();
        $this->assertDatabaseCount('tl_staff', 2);
        $this->patchJson('/board/api/users/'.$multi['id'], ['name' => 'Multi Staff', 'nip' => '0012345679', 'role' => 'it_staff', 'password' => '', 'assignment_mode' => 'atl', 'tl_ids' => []])->assertOk();
        $this->assertDatabaseMissing('tl_staff', ['staff_id' => $multi['id']]);
        $this->postJson('/board/api/users', ['name' => 'Invalid NIP', 'nip' => '123', 'role' => 'it_staff', 'password' => 'password123', 'assignment_mode' => 'atl'])->assertStatus(422);
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

    public function test_general_task_survives_board_reload_edit_and_excel(): void
    {
        $atl = $this->user('atl', '0000000005');
        $project = $this->actingAs($atl)->postJson('/board/api/projects', ['name' => 'General Board', 'prefix' => 'GEN'])->assertCreated()->json();
        $task = $this->postJson('/board/api/tasks', ['project_id' => $project['id'], 'sub_project_id' => null,
            'title' => 'General task', 'status' => 'todo', 'category' => 'maintenance', 'priority' => 'high',
            'assignee_id' => $atl->id])->assertCreated()->assertJsonPath('sub_project_id', null)->json();

        $this->get('/board')->assertOk()->assertSee('General task');
        $this->patchJson('/board/api/tasks/'.$task['id'], ['title' => 'Edited general task', 'status' => 'in_progress',
            'sub_project_id' => null, 'category' => 'maintenance', 'priority' => 'high', 'assignee_id' => $atl->id,
            'subtasks' => []])->assertOk()->assertJsonPath('sub_project_id', null);
        $this->get('/board')->assertOk()->assertSee('Edited general task');

        $response = $this->get('/board/api/export?period=all');
        $response->assertOk()->assertDownload();
        $workbook = \PhpOffice\PhpSpreadsheet\IOFactory::load($response->baseResponse->getFile()->getPathname());
        $rows = $workbook->getSheetByName('Rekap Detail Task')->rangeToArray('A1:N10');
        $this->deleteJson('/board/api/tasks/'.$task['id'])->assertOk();
        $this->assertContains('Umum / Tanpa Sub-Project', array_merge(...$rows), json_encode($rows));
    }

    public function test_deletion_checks_all_tasks_including_invisible_tasks(): void
    {
        $atl = $this->user('atl', '0000000006');
        $staff = $this->user('it_staff', '0000000007');
        $project = $this->actingAs($atl)->postJson('/board/api/projects', ['name' => 'Shared', 'prefix' => 'SHR',
            'sub_projects' => ['Used', 'Empty']])->assertCreated()->json();
        $used = DB::table('sub_projects')->where('project_id', $project['id'])->where('name', 'Used')->first();
        $empty = DB::table('sub_projects')->where('project_id', $project['id'])->where('name', 'Empty')->first();
        $task = $this->postJson('/board/api/tasks', ['project_id' => $project['id'], 'sub_project_id' => $used->id,
            'title' => 'Hidden from staff', 'status' => 'todo', 'category' => 'maintenance', 'priority' => 'high',
            'assignee_id' => $atl->id])->assertCreated()->json();

        $this->actingAs($staff)->deleteJson('/board/api/sub-projects/'.$used->id)->assertStatus(409)
            ->assertJsonPath('message', 'Sub-project tidak dapat dihapus karena masih memiliki task.');
        $this->deleteJson('/board/api/projects/'.$project['id'])->assertStatus(409);
        $this->deleteJson('/board/api/sub-projects/'.$empty->id)->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $task['id'], 'sub_project_id' => $used->id]);

        $this->actingAs($atl)->deleteJson('/board/api/tasks/'.$task['id'])->assertOk();
        $this->actingAs($staff)->deleteJson('/board/api/sub-projects/'.$used->id)->assertOk();
        $this->deleteJson('/board/api/projects/'.$project['id'])->assertOk();
        $this->assertDatabaseMissing('projects', ['id' => $project['id']]);
    }

    public function test_general_task_blocks_project_deletion_after_sub_project_is_removed(): void
    {
        $atl = $this->user('atl', '0000000008');
        $tl = $this->user('tl', '0000000009');
        $project = $this->actingAs($atl)->postJson('/board/api/projects', ['name' => 'Shared General', 'prefix' => 'SGN',
            'sub_projects' => ['Empty']])->assertCreated()->json();
        $sub = DB::table('sub_projects')->where('project_id', $project['id'])->first();
        $task = $this->postJson('/board/api/tasks', ['project_id' => $project['id'], 'sub_project_id' => null,
            'title' => 'Invisible general task', 'status' => 'todo', 'category' => 'maintenance',
            'priority' => 'high', 'assignee_id' => $atl->id])->assertCreated()->json();

        $this->actingAs($tl)->deleteJson('/board/api/sub-projects/'.$sub->id)->assertOk();
        $this->deleteJson('/board/api/projects/'.$project['id'])->assertStatus(409)
            ->assertJsonPath('message', 'Project tidak dapat dihapus karena masih memiliki task.');
        $this->actingAs($atl)->deleteJson('/board/api/tasks/'.$task['id'])->assertOk();
    }
}
