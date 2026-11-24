<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ItFlowExportController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->user()->role === 'atl', 403);
        $data = $request->validate([
            'period' => ['nullable', Rule::in(['1d', '7d', '30d', 'all'])],
            'project' => ['nullable', Rule::exists('projects', 'prefix')],
            'preview' => ['nullable', 'boolean'],
        ]);
        $period = $data['period'] ?? '7d';
        $project = $data['project'] ?? null;
        $projects = DB::table('projects')->orderBy('id')->get()->keyBy('id');
        $subProjects = DB::table('sub_projects')->orderBy('id')->get()->keyBy('id');
        $users = DB::table('users')->orderBy('id')->get()->keyBy('id');
        $tasks = Task::query()->visibleTo($request->user())
            ->when($project, fn ($query) => $query->whereIn('tasks.project_id', $projects->where('prefix', $project)->pluck('id')))
            ->when($period !== 'all', fn ($query) => $query->where('tasks.created_at', '>=', now()->subDays((int) $period)))
            ->orderBy('tasks.created_at')->get();

        if ($request->boolean('preview')) return response()->json(['count' => $tasks->count()]);
        abort_unless(extension_loaded('zip'), 503, 'Ekstensi PHP zip diperlukan untuk mengunduh Excel.');

        $subtasks = DB::table('subtasks')->whereIn('task_id', $tasks->pluck('id'))->orderBy('id')->get()->groupBy('task_id');
        $done = $tasks->where('status', 'done')->count();
        $progress = $tasks->where('status', 'in_progress')->count();
        $todo = $tasks->where('status', 'todo')->count();
        $critical = $tasks->where('priority', 'critical')->where('status', '!=', 'done')->count();
        $periodText = ['1d' => '1 Hari Terakhir', '7d' => '7 Hari Terakhir', '30d' => '30 Hari Terakhir', 'all' => 'Semua Periode'][$period];
        $scopeText = $project ? $project.' — '.$projects->firstWhere('prefix', $project)->name : 'Semua Project / Board IT';
        $subtitle = 'Periode Laporan: '.$periodText.'   |   Cakupan: '.$scopeText.'   |   Dicetak: '.now()->format('d M Y, H:i');

        $detail = [
            ['LAPORAN REKAPITULASI TASK DIVISI IT — ITFLOW'], [$subtitle], [],
            ['Dicetak Oleh:', $request->user()->name.' (NIP: '.$request->user()->nip.')', '', 'Total Task:', $tasks->count(), '', 'Done / Resolved:', $done, '', 'In Progress:', $progress, '', 'To Do:', $todo], [],
            ['No', 'Kode Task', 'Project / Board', 'Sub-Project', 'Kategori', 'Level / Prioritas', 'Status', 'Judul Pekerjaan', 'PIC (Assignee)', 'NIP PIC', 'Progres Sub-Task', 'Detail Checklist Sub-Task', 'Tanggal Dibuat', 'Target Selesai'],
        ];
        foreach ($tasks as $index => $task) {
            $subs = $subtasks->get($task->id, collect());
            $complete = $subs->where('is_done', 1)->count();
            $detail[] = [$index + 1, $task->code, $projects[$task->project_id]->prefix.' - '.$projects[$task->project_id]->name,
                $task->sub_project_id === null ? 'Umum / Tanpa Sub-Project' : ($subProjects[$task->sub_project_id]->name ?? '-'), $task->category === 'maintenance' ? 'Maintenance / Support' : 'Development',
                ucfirst($task->priority), ['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done / Resolved'][$task->status],
                $task->title, $users[$task->assignee_id]->name ?? '-', $users[$task->assignee_id]->nip ?? '-',
                $subs->count() ? $complete.'/'.$subs->count().' ('.round($complete * 100 / $subs->count()).'%)' : '-',
                $subs->count() ? $subs->map(fn ($sub) => ($sub->is_done ? '☑' : '☐').' '.$sub->title)->implode("\n") : '-',
                $task->created_at?->format('d M Y, H:i'), $task->due_date?->format('Y-m-d') ?? '-'];
        }
        if ($tasks->isEmpty()) $detail[] = ['-', '-', 'Tidak ada task pada periode waktu yang dipilih.', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'];

        $pic = [['REKAPITULASI BEBAN KERJA & KINERJA TIM IT'], [$subtitle], [],
            ['No', 'NIP', 'Nama Personel IT', 'Jabatan / Spesialisasi', 'Total Task', 'To Do', 'In Progress', 'Done / Resolved', 'Critical Aktif', 'Rasio Selesai']];
        foreach ($users->values() as $index => $member) {
            $items = $tasks->where('assignee_id', $member->id);
            $memberDone = $items->where('status', 'done')->count();
            $pic[] = [$index + 1, $member->nip, $member->name, match ($member->role) {'atl' => 'Area Team Leader (ATL)', 'tl' => 'Team Leader (TL)', default => 'IT Staff'},
                $items->count(), $items->where('status', 'todo')->count(), $items->where('status', 'in_progress')->count(), $memberDone,
                $items->where('priority', 'critical')->where('status', '!=', 'done')->count(), $items->count() ? round($memberDone * 100 / $items->count()).'%' : '0%'];
        }
        $pic[] = ['', '', 'TOTAL KESELURUHAN', '', $tasks->count(), $todo, $progress, $done, $critical, $tasks->count() ? round($done * 100 / $tasks->count()).'%' : '0%'];

        $targetProjects = $project ? $projects->where('prefix', $project) : $projects;
        $boards = [['REKAPITULASI PROGRES PER PROJECT (BOARD)'], [$subtitle], [],
            ['Prefix', 'Nama Project / Board', 'Jumlah Sub-Project', 'Total Task', 'To Do', 'In Progress', 'Done / Resolved', 'Progres Selesai']];
        foreach ($targetProjects as $item) {
            $items = $tasks->where('project_id', $item->id);
            $itemDone = $items->where('status', 'done')->count();
            $boards[] = [$item->prefix, $item->name, $subProjects->where('project_id', $item->id)->count(), $items->count(),
                $items->where('status', 'todo')->count(), $items->where('status', 'in_progress')->count(), $itemDone,
                $items->count() ? round($itemDone * 100 / $items->count()).'%' : '0%'];
        }
        $boards[] = ['TOTAL', $targetProjects->count().' Project', $subProjects->whereIn('project_id', $targetProjects->keys())->count(),
            $tasks->count(), $todo, $progress, $done, $tasks->count() ? round($done * 100 / $tasks->count()).'%' : '0%'];

        $sheets = [
            new ItFlowSheet('Rekap Detail Task', $detail, 14, 6, ['E' => 20, 'H' => 38, 'L' => 42], 10),
            new ItFlowSheet('Rekap Kinerja PIC', $pic, 10, 4, ['C' => 24, 'D' => 22], 2),
            new ItFlowSheet('Rekap Per Project', $boards, 8, 4, ['B' => 32], null),
        ];
        return Excel::download(new class($sheets) implements Export, WithMultipleSheets {
            public function __construct(private array $items) {}
            public function sheets(): array { return $this->items; }
        }, 'Rekap_ITFlow_'.($period === 'all' ? 'Semua' : strtoupper($period)).'_'.now()->format('Y-m-d').'.xlsx');
    }
}

class ItFlowSheet implements Export, FromArray, WithTitle, WithEvents
{
    public function __construct(private string $name, private array $rows, private int $columns, private int $headerRow, private array $widths, private ?int $nipColumn) {}
    public function title(): string { return $this->name; }
    public function array(): array { return $this->rows; }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $last = $sheet->getHighestRow();
            $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->columns);
            $sheet->mergeCells("A1:{$end}1"); $sheet->mergeCells("A2:{$end}2");
            $sheet->getStyle("A1:{$end}1")->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']]]);
            $sheet->getStyle("A2:{$end}2")->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => 'E2E8F0']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']]]);
            $sheet->getStyle("A{$this->headerRow}:{$end}{$this->headerRow}")->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]]);
            $sheet->getRowDimension(1)->setRowHeight(30); $sheet->getRowDimension($this->headerRow)->setRowHeight(28);
            foreach ($this->widths as $column => $width) $sheet->getColumnDimension($column)->setWidth($width);
            for ($index = 1; $index <= $this->columns; $index++) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
                if (! isset($this->widths[$column])) $sheet->getColumnDimension($column)->setWidth(16);
            }
            if ($this->nipColumn) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->nipColumn);
                for ($row = $this->headerRow + 1; $row <= $last; $row++) {
                    $value = $sheet->getCell("{$column}{$row}")->getValue();
                    if ($value !== null && $value !== '') $sheet->setCellValueExplicit("{$column}{$row}", (string) $value, DataType::TYPE_STRING);
                }
            }
            if ($this->name !== 'Rekap Detail Task') $sheet->getStyle("A{$last}:{$end}{$last}")->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']]]);
        }];
    }
}
