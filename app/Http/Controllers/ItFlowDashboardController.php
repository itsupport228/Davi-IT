<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItFlowDashboardController extends Controller
{
    private const ZONE = 'Asia/Jakarta';

    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'atl', 403);

        return app(ItFlowBoardController::class)($request);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'atl', 403);
        $input = $request->validate([
            'period' => ['sometimes', Rule::in(['today', '7d', 'mtd', 'ytd', 'all'])],
            'list' => ['sometimes', Rule::in(['new', 'done', 'overdue', 'critical'])],
            'pic' => ['required_with:workload', 'integer', 'exists:users,id'],
            'workload' => ['sometimes', Rule::in(['1'])],
        ]);
        $period = $input['period'] ?? '7d';
        $now = CarbonImmutable::now(self::ZONE);
        $today = $now->startOfDay();
        $start = match ($period) {
            'today' => $today,
            '7d' => $today->subDays(6),
            'mtd' => $today->startOfMonth(),
            'ytd' => $today->startOfYear(),
            default => null,
        };
        $from = $start?->utc()->toDateTimeString();
        $until = $now->utc()->toDateTimeString();
        $dueFrom = $start?->toDateString();
        $dueUntil = $now->subDay()->toDateString();

        $queries = [
            'new' => Task::query()->where('tasks.created_at', '<=', $until)->when($from, fn (Builder $q) => $q->where('tasks.created_at', '>=', $from)),
            'done' => Task::query()->where('tasks.status', 'done')->whereNotNull('tasks.completed_at')->where('tasks.completed_at', '<=', $until)->when($from, fn (Builder $q) => $q->where('tasks.completed_at', '>=', $from)),
            'overdue' => Task::query()->where('tasks.status', '!=', 'done')->whereNotNull('tasks.due_date')->where('tasks.due_date', '<=', $dueUntil)->when($dueFrom, fn (Builder $q) => $q->where('tasks.due_date', '>=', $dueFrom)),
            'critical' => Task::query()->where('tasks.status', '!=', 'done')->where('tasks.priority', 'critical')->where('tasks.created_at', '<=', $until)->when($from, fn (Builder $q) => $q->where('tasks.created_at', '>=', $from)),
        ];
        $counts = array_map(fn (Builder $query) => (clone $query)->count(), $queries);
        $taskColumns = ['tasks.id', 'tasks.assignee_id', 'tasks.code', 'tasks.title', 'tasks.status', 'tasks.priority', 'tasks.due_date', 'tasks.created_at', 'tasks.completed_at'];
        $items = function (Builder $query) use ($taskColumns, $dueFrom, $dueUntil, $from, $until) {
            return $query->with(['assignee:id,name', 'reporter:id,name'])
                ->join('projects', 'projects.id', '=', 'tasks.project_id')
                ->orderByDesc('tasks.id')->get([...$taskColumns, 'projects.name as project_name'])
                ->map(fn (Task $task) => [
                    'id' => $task->id, 'code' => $task->code, 'title' => $task->title,
                    'pic' => $task->assignee?->name ?? '-', 'project' => $task->project_name,
                    'due_date' => $task->due_date?->toDateString(),
                    'overdue' => $task->status !== 'done' && $task->due_date && $task->due_date->toDateString() <= $dueUntil && (! $dueFrom || $task->due_date->toDateString() >= $dueFrom),
                    'critical' => $task->status !== 'done' && $task->priority === 'critical' && $task->created_at->toDateTimeString() <= $until && (! $from || $task->created_at->toDateTimeString() >= $from),
                ])->values();
        };

        if (isset($input['workload'])) {
            $query = (clone $queries['new'])->where('tasks.assignee_id', $input['pic'])->whereIn('tasks.status', ['todo', 'in_progress']);
            return response()->json(['period' => $period, 'count' => (clone $query)->count(), 'tasks' => $items($query)]);
        }

        if (isset($input['list'])) {
            return response()->json(['period' => $period, 'count' => $counts[$input['list']], 'tasks' => $items(clone $queries[$input['list']])]);
        }

        $followUp = Task::query()->where(function (Builder $query) use ($queries): void {
            $query->whereIn('tasks.id', (clone $queries['overdue'])->select('tasks.id'))
                ->orWhereIn('tasks.id', (clone $queries['critical'])->select('tasks.id'));
        });
        $interval = match ($period) { 'today' => 'hour', '7d', 'mtd' => 'day', default => 'month' };
        $first = $start ?? collect([Task::query()->min('created_at'), Task::query()->whereNotNull('completed_at')->min('completed_at')])->filter()->min();
        $firstDate = $first instanceof CarbonImmutable ? $first : ($first ? CarbonImmutable::parse($first, 'UTC')->setTimezone(self::ZONE) : $today);
        $cursor = match ($interval) { 'hour' => $today, 'day' => $firstDate->startOfDay(), default => $firstDate->startOfMonth() };
        $last = match ($interval) { 'hour' => $now->startOfHour(), 'day' => $today, default => $now->startOfMonth() };
        $buckets = [];
        for (; $cursor <= $last; $cursor = match ($interval) { 'hour' => $cursor->addHour(), 'day' => $cursor->addDay(), default => $cursor->addMonth() }) {
            $buckets[$cursor->format(match ($interval) { 'hour' => 'Y-m-d H', 'day' => 'Y-m-d', default => 'Y-m' })] = [
                'label' => $cursor->format(match ($interval) { 'hour' => 'H:00', 'day' => 'd M', default => 'M Y' }),
                'date' => $cursor->format(match ($interval) { 'hour' => 'd M Y, H:00', 'day' => 'd M Y', default => 'M Y' }).' WIB',
                'new' => 0, 'done' => 0,
            ];
        }
        foreach (['new' => 'created_at', 'done' => 'completed_at'] as $series => $column) {
            foreach ((clone $queries[$series])->pluck($column) as $value) {
                $key = CarbonImmutable::parse($value, 'UTC')->setTimezone(self::ZONE)->format(match ($interval) { 'hour' => 'Y-m-d H', 'day' => 'Y-m-d', default => 'Y-m' });
                if (isset($buckets[$key])) $buckets[$key][$series]++;
            }
        }
        $workload = (clone $queries['new'])->whereIn('tasks.status', ['todo', 'in_progress'])
            ->join('users', 'users.id', '=', 'tasks.assignee_id')
            ->selectRaw('tasks.assignee_id as pic_id, users.name as pic, tasks.status, count(*) as total')
            ->groupBy('tasks.assignee_id', 'users.name', 'tasks.status')->get()
            ->groupBy('pic_id')->map(function ($group, $picId) {
                return [
                    'pic_id' => (int) $picId, 'pic' => $group->first()->pic,
                    'todo' => (int) $group->firstWhere('status', 'todo')?->total,
                    'in_progress' => (int) $group->firstWhere('status', 'in_progress')?->total,
                ];
            })->sortByDesc(fn ($row) => $row['todo'] + $row['in_progress'])->values();

        return response()->json([
            'period' => $period,
            'range' => ($start?->format('d M Y') ?? 'Awal data').' - '.$now->format('d M Y, H:i').' WIB',
            'counts' => $counts,
            'series' => array_values($buckets),
            'workload' => $workload,
            'follow_up' => $items($followUp),
        ]);
    }
}
