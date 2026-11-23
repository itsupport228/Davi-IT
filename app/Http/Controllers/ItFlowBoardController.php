<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ItFlowBoardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $categories = ['maintenance' => 'Maint/Support', 'development' => 'Development'];
        $priorities = [
            'maintenance' => ['low' => 'Low', 'high' => 'High', 'critical' => 'Critical'],
            'development' => ['basic' => 'Basic', 'great' => 'Great', 'epic' => 'Epic'],
        ];
        $periods = ['all' => 'Semua', '1d' => '1 Hari', '7d' => '7 Hari', '30d' => '30 Hari'];
        $statuses = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done / Resolved'];

        $visible = Task::query()->visibleTo($user);
        $projects = DB::table('projects')->orderBy('id')->get()->keyBy('id');
        $subProjects = DB::table('sub_projects')->orderBy('id')->get()->keyBy('id');
        $assignees = Task::allowedAssignees($user)->orderBy('name')->get(['id', 'name', 'nip', 'role'])->keyBy('id');
        $project = $projects->get($request->integer('project'));
        $subProject = $subProjects->get($request->integer('sub_project'));
        if ($subProject && (int) $subProject->project_id !== (int) ($project->id ?? 0)) {
            $subProject = null;
        }

        $category = $request->string('category')->toString();
        $category = isset($categories[$category]) ? $category : null;
        $priorityOptions = $category ? $priorities[$category] : array_merge(...array_values($priorities));
        $priority = $request->string('priority')->toString();
        $priority = isset($priorityOptions[$priority]) ? $priority : null;
        $period = $request->string('period', 'all')->toString();
        $period = isset($periods[$period]) ? $period : 'all';
        $sort = $request->string('sort')->toString() === 'asc' ? 'asc' : 'desc';
        $my = $request->boolean('my');
        $pic = $my ? $user->id : $assignees->get($request->integer('pic'))?->id;
        $search = mb_substr(trim($request->string('q')->toString()), 0, 120);

        $projectCounts = (clone $visible)->selectRaw('tasks.project_id, count(*) as total')
            ->groupBy('tasks.project_id')->pluck('total', 'project_id');
        $subtasks = DB::table('subtasks')->whereColumn('subtasks.task_id', 'tasks.id');

        $query = $visible->select('tasks.*', 'sub_projects.name as sub_project_name')
            ->join('sub_projects', 'sub_projects.id', '=', 'tasks.sub_project_id')
            ->addSelect([
                'subtasks_total' => (clone $subtasks)->selectRaw('count(*)'),
                'subtasks_done' => (clone $subtasks)->where('is_done', true)->selectRaw('count(*)'),
            ])
            ->when($project, fn ($query) => $query->where('tasks.project_id', $project->id))
            ->when($subProject, fn ($query) => $query->where('tasks.sub_project_id', $subProject->id))
            ->when($category, fn ($query) => $query->where('tasks.category', $category))
            ->when($priority, fn ($query) => $query->where('tasks.priority', $priority))
            ->when($pic, fn ($query) => $query->where('tasks.assignee_id', $pic))
            ->when($period !== 'all', fn ($query) => $query->where(
                'tasks.created_at', '>=', now()->subDays((int) $period)
            ));

        if ($search !== '') {
            $query->where(fn ($query) => $query
                ->whereLike('tasks.code', '%' . $search . '%')
                ->orWhereLike('tasks.title', '%' . $search . '%'));
        }

        $tasks = $query->orderBy('tasks.created_at', $sort)->orderBy('tasks.id', $sort)->get();

        $allTasks = Task::query()->visibleTo($user)->orderBy('tasks.id')->get();
        $subtasksByTask = DB::table('subtasks')->whereIn('task_id', $allTasks->pluck('id'))->orderBy('id')->get()->groupBy('task_id');
        $commentsByTask = DB::table('task_comments')->whereIn('task_id', $allTasks->pluck('id'))
            ->orderBy('id')->get()->groupBy('task_id');
        $members = Task::allowedAssignees($user)->orderBy('name')->get(['id', 'name', 'nip', 'role'])->map(fn ($member) => [
            'id' => (string) $member->id,
            'name' => $member->name,
            'nip' => $member->nip,
            'role' => $member->role,
            'title' => match ($member->role) {
                'atl' => 'Area Team Leader (ATL)',
                'tl' => 'Team Leader (TL)',
                default => 'IT Staff',
            },
            'avatar' => 'https://ui-avatars.com/api/?name='.rawurlencode($member->name).'&background=2563eb&color=fff&bold=true',
            'subordinateIds' => $member->role === 'tl' ? DB::table('tl_staff')->where('tl_id', $member->id)
                ->pluck('staff_id')->map(fn ($id) => (string) $id)->all() : [],
        ])->values();

        $prototypeData = [
            'userId' => (string) $user->id,
            'role' => $user->role,
            'teamMembers' => $members,
            'boards' => $projects->pluck('name', 'prefix'),
            'subProjects' => $subProjects->map(fn ($item) => [
                'id' => (string) $item->id,
                'board' => $projects->get($item->project_id)?->prefix,
                'name' => $item->name,
            ])->values(),
            'tasks' => $allTasks->map(fn ($task) => [
                'id' => (string) $task->id,
                'code' => $task->code,
                'board' => $projects->get($task->project_id)?->prefix,
                'subProjectId' => (string) $task->sub_project_id,
                'category' => $task->category,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'assigneeId' => (string) $task->assignee_id,
                'reporterId' => (string) $task->reporter_id,
                'dueDate' => $task->due_date?->format('Y-m-d'),
                'createdAt' => $task->created_at?->format('d M Y, H:i'),
                'subtasks' => ($subtasksByTask->get($task->id) ?? collect())->map(fn ($sub) => [
                    'id' => (string) $sub->id, 'title' => $sub->title, 'done' => (bool) $sub->is_done,
                ])->values(),
                'comments' => ($commentsByTask->get($task->id) ?? collect())->map(fn ($comment) => [
                    'id' => (string) $comment->id, 'userId' => (string) $comment->user_id,
                    'text' => $comment->body, 'time' => \Carbon\Carbon::parse($comment->created_at)->format('d M Y, H:i'),
                ])->values(),
            ])->values(),
        ];

        return view('itflow.board', [
            'user' => $user,
            'roleLabel' => match ($user->role) {
                'atl' => 'ATL',
                'tl' => 'Team Leader',
                'it_staff' => 'IT Staff',
                default => 'Akun',
            },
            'projects' => $projects,
            'subProjectsByProject' => $subProjects->groupBy('project_id'),
            'projectCounts' => $projectCounts,
            'assignees' => $assignees,
            'categories' => $categories,
            'priorities' => array_merge(...array_values($priorities)),
            'priorityOptions' => $priorityOptions,
            'periods' => $periods,
            'statuses' => $statuses,
            'tasksByStatus' => $tasks->groupBy('status'),
            'total' => $tasks->count(),
            'done' => $tasks->where('status', 'done')->count(),
            'critical' => $tasks->where('priority', 'critical')->where('status', '!=', 'done')->count(),
            'project' => $project,
            'subProject' => $subProject,
            'filters' => [
                'project' => $project?->id,
                'sub_project' => $subProject?->id,
                'category' => $category,
                'priority' => $priority,
                'period' => $period,
                'sort' => $sort,
                'my' => $my ? 1 : null,
                'pic' => $my ? null : $pic,
                'q' => $search,
            ],
            'prototypeData' => $prototypeData,
        ]);
    }
}
