<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'atl' => $query,
            'tl' => $query->where(fn (Builder $tasks) => $tasks->where('tasks.assignee_id', $user->id)
                ->orWhereIn('tasks.assignee_id', DB::table('tl_staff')->where('tl_id', $user->id)->select('staff_id'))),
            'it_staff' => $query->where('tasks.assignee_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public static function allowedAssignees(User $user): Builder
    {
        $query = User::query()->where('is_active', true);

        return match ($user->role) {
            'atl' => $query,
            'tl' => $query->where(function (Builder $members) use ($user): void {
                $staffIds = DB::table('tl_staff')
                    ->where('tl_id', $user->id)
                    ->select('staff_id');

                $members->where('users.id', $user->id)
                    ->orWhereIn('users.id', $staffIds);
            }),
            'it_staff' => $query->whereKey($user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }
}
