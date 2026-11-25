<main id="atlDashboard" class="dashboard-main flex-1 min-h-0 overflow-y-auto kanban-scroll" aria-label="Dashboard ATL">
    <div class="dashboard-header"><div><p class="eyebrow">OVERVIEW / ATL</p><h1>Dashboard</h1><p id="dashboardRange" aria-live="polite">Memuat rentang...</p></div></div>
    <nav id="dashboardPeriods" class="dashboard-periods" aria-label="Filter periode">
        <button type="button" data-period="today">Hari Ini</button><button type="button" data-period="7d" class="active">7 Hari</button><button type="button" data-period="mtd">MTD</button><button type="button" data-period="ytd">YTD</button><button type="button" data-period="all">Semua</button>
    </nav>
    <p id="dashboardError" role="alert" hidden></p>
    <section id="dashboardCards" class="dashboard-cards" aria-label="Ringkasan task">
        <button type="button" data-list="new"><span>Task Baru</span><strong>...</strong><small>Tanggal dibuat dalam rentang</small></button>
        <button type="button" data-list="done"><span>Task Selesai</span><strong>...</strong><small>Tanggal selesai dalam rentang</small></button>
        <button type="button" data-list="overdue"><span>Overdue</span><strong>...</strong><small>Target terlewat, masih terbuka</small></button>
        <button type="button" data-list="critical"><span>Critical Terbuka</span><strong>...</strong><small>Critical dibuat dalam rentang, belum Done</small></button>
    </section>
    <div class="dashboard-charts">
        <section class="dashboard-panel"><div class="panel-heading"><h2>Arus Task</h2><p>Task baru vs selesai</p></div><div class="chart-legend"><span>● Task Baru</span><span>● Task Selesai</span></div><div id="dashboardLine" class="line-chart" role="img" aria-label="Grafik garis task baru dan selesai"></div></section>
        <section class="dashboard-panel"><div class="panel-heading"><h2>Beban per PIC</h2><p>Task terbuka yang dibuat dalam rentang</p></div><div class="chart-legend"><span>● To Do</span><span>● In Progress</span></div><div id="dashboardWorkload" class="workload-chart"></div></section>
    </div>
    <section class="dashboard-panel dashboard-followup"><div class="panel-heading"><h2>Perlu Ditindaklanjuti</h2><p>Overdue atau Critical Terbuka</p></div><div class="table-scroll"><table><thead><tr><th>Kode</th><th>Judul</th><th>PIC</th><th>Project</th><th>Target</th><th>Label</th></tr></thead><tbody id="dashboardFollowup"><tr><td colspan="6">Memuat...</td></tr></tbody></table></div></section>
</main>
<dialog id="dashboardListDialog" aria-labelledby="dashboardListTitle"><header><h2 id="dashboardListTitle">Daftar Task</h2><button type="button" id="dashboardListClose" aria-label="Tutup">×</button></header><p id="dashboardListCount"></p><div class="table-scroll"><table><thead><tr><th>Kode</th><th>Judul</th><th>PIC</th><th>Project</th><th>Target</th></tr></thead><tbody id="dashboardListBody"></tbody></table></div></dialog>
