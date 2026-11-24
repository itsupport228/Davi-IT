<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItFlowBoardApiController extends Controller
{
    private function allowedTask(Request $request, int $id): Task
    {
        return Task::query()->visibleTo($request->user())->whereKey($id)->firstOrFail();
    }

    private function taskPayload(Task $task): array
    {
        $task->load(['assignee:id,name,nip', 'reporter:id,name,nip']);
        return [
            'id' => $task->id, 'code' => $task->code, 'title' => $task->title,
            'description' => $task->description, 'status' => $task->status,
            'category' => $task->category, 'priority' => $task->priority,
            'project_id' => $task->project_id, 'sub_project_id' => $task->sub_project_id,
            'assignee_id' => $task->assignee_id, 'reporter_id' => $task->reporter_id,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'created_at' => $task->created_at?->toIso8601String(),
            'project' => DB::table('projects')->where('id', $task->project_id)->first(['id','prefix','name']),
            'sub_project' => DB::table('sub_projects')->where('id', $task->sub_project_id)->first(['id','name','project_id']),
            'assignee' => $task->assignee, 'reporter' => $task->reporter,
            'subtasks' => DB::table('subtasks')->where('task_id', $task->id)->orderBy('id')->get(['id','title','is_done as done']),
            'comments' => DB::table('task_comments')->join('users','users.id','=','task_comments.user_id')
                ->where('task_id', $task->id)->orderBy('task_comments.created_at')
                ->get(['task_comments.id','task_comments.body as text','task_comments.created_at as time','users.id as user_id','users.name as author_name']),
        ];
    }

    public function meta(Request $request): JsonResponse
    {
        $user = $request->user();
        $projects = DB::table('projects')->orderBy('id')->get(['id','prefix','name']);
        $subProjects = DB::table('sub_projects')->orderBy('id')->get(['id','project_id','name']);
        $assignees = Task::allowedAssignees($user)->orderBy('name')->get(['id','name','nip','role']);
        return response()->json(compact('projects','subProjects','assignees'));
    }

    public function project(Request $request): JsonResponse
    {
        $request->merge(['prefix' => strtoupper((string) $request->input('prefix'))]);
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'prefix' => ['required','string','max:12','regex:/^[A-Za-z0-9]+$/','unique:projects,prefix'],
            'sub_projects' => ['nullable','array'], 'sub_projects.*' => ['string','max:150'],
        ]);
        $project = DB::transaction(function () use ($data) {
            $id = DB::table('projects')->insertGetId(['name' => $data['name'], 'prefix' => strtoupper($data['prefix']), 'next_task_number' => 101, 'created_at' => now(), 'updated_at' => now()]);
            foreach (array_unique(array_filter(array_map('trim', $data['sub_projects'] ?? []))) as $name) {
                DB::table('sub_projects')->insert(['project_id' => $id, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            }
            return DB::table('projects')->find($id);
        });
        return response()->json($project, 201);
    }

    public function subProject(Request $request): JsonResponse
    {
        $data = $request->validate(['project_id' => ['required','integer','exists:projects,id'], 'name' => ['required','string','max:150', Rule::unique('sub_projects')->where('project_id', $request->input('project_id'))]]);
        $id = DB::table('sub_projects')->insertGetId(['project_id'=>$data['project_id'],'name'=>trim($data['name']),'created_at'=>now(),'updated_at'=>now()]);
        return response()->json(DB::table('sub_projects')->find($id), 201);
    }

    public function destroyProject(int $project): JsonResponse
    {
        return DB::transaction(function () use ($project): JsonResponse {
            $item = DB::table('projects')->where('id', $project)->lockForUpdate()->first();
            abort_unless($item, 404);
            abort_if(DB::table('tasks')->where('project_id', $project)->exists(), 409, 'Project tidak dapat dihapus karena masih memiliki task.');
            abort_if(DB::table('sub_projects')->where('project_id', $project)->exists(), 409, 'Project tidak dapat dihapus karena masih memiliki sub-project.');
            DB::table('projects')->where('id', $project)->delete();

            return response()->json(['deleted' => true]);
        });
    }

    public function destroySubProject(int $subProject): JsonResponse
    {
        return DB::transaction(function () use ($subProject): JsonResponse {
            $item = DB::table('sub_projects')->where('id', $subProject)->lockForUpdate()->first();
            abort_unless($item, 404);
            abort_if(DB::table('tasks')->where('sub_project_id', $subProject)->exists(), 409, 'Sub-project tidak dapat dihapus karena masih memiliki task.');
            DB::table('sub_projects')->where('id', $subProject)->delete();

            return response()->json(['deleted' => true]);
        });
    }

    public function storeTask(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'project_id' => ['required','integer','exists:projects,id'], 'sub_project_id' => ['nullable','integer','exists:sub_projects,id'],
            'title' => ['required','string','max:255'], 'description' => ['nullable','string'],
            'status' => ['required', Rule::in(['todo','in_progress','done'])], 'category' => ['required', Rule::in(['maintenance','development'])],
            'priority' => ['required', Rule::in(['low','high','critical','basic','great','epic'])],
            'assignee_id' => ['required','integer'], 'due_date' => ['nullable','date'], 'subtasks' => ['nullable','array'], 'subtasks.*' => ['string','max:255'],
        ]);
        $this->validatePair($data['project_id'], $data['sub_project_id'] ?? null, $data['category'], $data['priority']);
        abort_unless(Task::allowedAssignees($user)->whereKey($data['assignee_id'])->exists(), 403);
        if ($user->role === 'it_staff') $data['assignee_id'] = $user->id;
        $task = DB::transaction(function () use ($data, $user) {
            $project = DB::table('projects')->where('id', $data['project_id'])->lockForUpdate()->first();
            $number = $project->next_task_number;
            DB::table('projects')->where('id', $project->id)->update(['next_task_number' => $number + 1, 'updated_at' => now()]);
            $taskId = DB::table('tasks')->insertGetId([
                'project_id'=>$project->id,'sub_project_id'=>$data['sub_project_id'] ?? null,'assignee_id'=>$data['assignee_id'],'reporter_id'=>$user->id,
                'code'=>$project->prefix.'-'.$number,'title'=>$data['title'],'description'=>$data['description'] ?? null,'status'=>$data['status'],
                'category'=>$data['category'],'priority'=>$data['priority'],'due_date'=>$data['due_date'] ?? null,'created_at'=>now(),'updated_at'=>now(),
            ]);
            foreach (array_filter(array_map('trim', $data['subtasks'] ?? [])) as $title) DB::table('subtasks')->insert(['task_id'=>$taskId,'title'=>$title,'is_done'=>false,'created_at'=>now(),'updated_at'=>now()]);
            return Task::findOrFail($taskId);
        });
        return response()->json($this->taskPayload($task), 201);
    }

    public function showTask(Request $request, int $task): JsonResponse { return response()->json($this->taskPayload($this->allowedTask($request, $task))); }

    public function updateTask(Request $request, int $task): JsonResponse
    {
        $model = $this->allowedTask($request, $task); $user = $request->user();
        $data = $request->validate(['title'=>['required','string','max:255'],'description'=>['nullable','string'],'status'=>[Rule::in(['todo','in_progress','done'])], 'sub_project_id'=>['present','nullable','integer','exists:sub_projects,id'],'category'=>[Rule::in(['maintenance','development'])], 'priority'=>[Rule::in(['low','high','critical','basic','great','epic'])], 'assignee_id'=>['required','integer'],'due_date'=>['nullable','date'],'subtasks'=>['nullable','array'],'subtasks.*.id'=>['nullable','integer'],'subtasks.*.title'=>['required','string','max:255'],'subtasks.*.done'=>['boolean'],'comment'=>['nullable','string','max:5000']]);
        $this->validatePair($model->project_id, $data['sub_project_id'], $data['category'], $data['priority']);
        abort_unless(Task::allowedAssignees($user)->whereKey($data['assignee_id'])->exists(), 403);
        if ($user->role === 'it_staff') $data['assignee_id'] = $user->id;
        DB::transaction(function () use ($model, $data, $user) {
            $model->update(collect($data)->only(['title','description','status','sub_project_id','category','priority','assignee_id','due_date'])->all());
            $existingIds = [];
            foreach ($data['subtasks'] ?? [] as $sub) {
                if (! empty($sub['id'])) {
                    $existing = DB::table('subtasks')->where('task_id', $model->id)->where('id', $sub['id']);
                    abort_unless((clone $existing)->exists(), 422, 'Sub-task tidak sesuai task.');
                    $existing->update(['title' => $sub['title'], 'is_done' => (bool) ($sub['done'] ?? false), 'updated_at' => now()]);
                    $existingIds[] = $sub['id'];
                } else {
                    $existingIds[] = DB::table('subtasks')->insertGetId(['task_id' => $model->id, 'title' => $sub['title'],
                        'is_done' => (bool) ($sub['done'] ?? false), 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            DB::table('subtasks')->where('task_id', $model->id)->whereNotIn('id', $existingIds)->delete();
            if (trim((string)($data['comment'] ?? '')) !== '') DB::table('task_comments')->insert(['task_id'=>$model->id,'user_id'=>$user->id,'body'=>trim($data['comment']),'created_at'=>now(),'updated_at'=>now()]);
        });
        return response()->json($this->taskPayload($model->fresh()));
    }

    public function updateStatus(Request $request, int $task): JsonResponse
    {
        $model = $this->allowedTask($request, $task); $data = $request->validate(['status'=>[Rule::in(['todo','in_progress','done'])]]); $model->update(['status'=>$data['status']]); return response()->json(['status'=>$model->status]);
    }

    public function destroyTask(Request $request, int $task): JsonResponse { $model = $this->allowedTask($request, $task); $model->delete(); return response()->json(['deleted'=>true]); }

    private function validatePair(int $projectId, ?int $subProjectId, string $category, string $priority): void
    {
        if ($subProjectId !== null) {
            abort_unless(DB::table('sub_projects')->where('id',$subProjectId)->where('project_id',$projectId)->exists(), 422, 'Sub-project tidak sesuai project.');
        }
        $valid = $category === 'maintenance' ? ['low','high','critical'] : ['basic','great','epic']; abort_unless(in_array($priority, $valid, true), 422, 'Level tidak sesuai kategori.');
    }
}
