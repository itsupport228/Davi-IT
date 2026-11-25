const api = '/dashboard/api';
const periods = {today:'Hari Ini', '7d':'7 Hari', mtd:'MTD', ytd:'YTD', all:'Semua'};
const labels = {new:'Task Baru', done:'Task Selesai', overdue:'Overdue', critical:'Critical Terbuka'};
let activePeriod = '7d';
let requestVersion = 0;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[char]));
}
function taskRows(items, followUp = false) {
    if (!items.length) return `<tr><td colspan="${followUp ? 6 : 5}">Tidak ada task pada rentang ini.</td></tr>`;
    return items.map(task => `<tr data-task-id="${task.id}" tabindex="0" title="Buka detail ${escapeHtml(task.code)}"><td>${escapeHtml(task.code)}</td><td>${escapeHtml(task.title)}</td><td>${escapeHtml(task.pic)}</td><td>${escapeHtml(task.project)}</td><td>${escapeHtml(task.due_date || '-')}</td>${followUp ? `<td>${task.overdue ? '<span class="tag">Overdue</span>' : ''}${task.critical ? '<span class="tag critical">Critical</span>' : ''}</td>` : ''}</tr>`).join('');
}
function enableTaskLinks(container) {
    container.querySelectorAll('[data-task-id]').forEach(row => {
        const open = () => { location.href = `/board?task=${encodeURIComponent(row.dataset.taskId)}`; };
        row.addEventListener('click', open);
        row.addEventListener('keydown', event => { if (event.key === 'Enter') open(); });
    });
}
function renderLine(series) {
    const element = document.getElementById('dashboardLine');
    if (!series.length || series.every(point => !point.new && !point.done)) { element.textContent = 'Belum ada task baru atau selesai pada periode ini.'; return; }
    const width = Math.max(440, series.length * 38 + 52), height = 230;
    const max = Math.max(1, ...series.flatMap(point => [point.new, point.done]));
    const x = index => 36 + index * (width - 56) / Math.max(1, series.length - 1);
    const y = count => 180 - count / max * 145;
    const colors = {new:'var(--dashboard-new)', done:'var(--dashboard-done)'};
    const chart = ['new', 'done'].map(key => `<polyline stroke="${colors[key]}" points="${series.map((point, index) => `${x(index)},${y(point[key])}`).join(' ')}"/>${series.map((point, index) => `<circle cx="${x(index)}" cy="${y(point[key])}" r="4" fill="${colors[key]}"><title>${escapeHtml(point.date)} · ${labels[key]}: ${point[key]}</title></circle>`).join('')}`).join('');
    const step = Math.max(1, Math.ceil(series.length / 8));
    const ticks = Array.from({length: Math.min(max, 4) + 1}, (_, index) => Math.round(index * max / Math.min(max, 4))).filter((value, index, all) => all.indexOf(value) === index);
    element.innerHTML = `<svg viewBox="0 0 ${width} ${height}" aria-hidden="true">${ticks.map(value => `<line x1="33" x2="${width - 12}" y1="${y(value)}" y2="${y(value)}"/><text x="27" y="${y(value) + 3}" text-anchor="end">${value}</text>`).join('')}${chart}${series.map((point, index) => index % step ? '' : `<text x="${x(index)}" y="207" text-anchor="middle">${escapeHtml(point.label)}</text>`).join('')}</svg>`;
}
function renderWorkload(rows) {
    const element = document.getElementById('dashboardWorkload');
    if (!rows.length) { element.textContent = 'Tidak ada task To Do atau In Progress yang dibuat pada periode ini.'; return; }
    const max = Math.max(...rows.map(row => row.todo + row.in_progress), 1);
    element.innerHTML = rows.map(row => `<button type="button" class="workload-row" data-pic-id="${row.pic_id}" aria-label="Daftar task ${escapeHtml(row.pic)}: ${row.todo} To Do, ${row.in_progress} In Progress"><span class="workload-label"><span>${escapeHtml(row.pic)}</span><strong>${row.todo + row.in_progress}</strong></span><span class="workload-track"><span class="workload-todo" style="width:${row.todo / max * 100}%" title="To Do: ${row.todo}"></span><span class="workload-progress" style="width:${row.in_progress / max * 100}%" title="In Progress: ${row.in_progress}"></span></span></button>`).join('');
    element.querySelectorAll('[data-pic-id]').forEach(button => button.addEventListener('click', () => openTaskList({picId: button.dataset.picId, title: button.querySelector('.workload-label span').textContent})));
}
async function loadDashboard() {
    const version = ++requestVersion;
    const error = document.getElementById('dashboardError'); error.hidden = true;
    document.querySelectorAll('#dashboardCards strong').forEach(count => count.textContent = '...');
    try {
        const response = await fetch(`${api}?period=${activePeriod}`, {headers:{Accept:'application/json'}});
        if (!response.ok) throw new Error('Dashboard tidak dapat dimuat. Silakan coba lagi.');
        const data = await response.json();
        if (version !== requestVersion) return;
        document.getElementById('dashboardRange').textContent = data.range;
        document.querySelectorAll('#dashboardCards button').forEach(card => card.querySelector('strong').textContent = data.counts[card.dataset.list]);
        renderLine(data.series); renderWorkload(data.workload);
        const followUp = document.getElementById('dashboardFollowup'); followUp.innerHTML = taskRows(data.follow_up, true); enableTaskLinks(followUp);
    } catch (cause) { if (version === requestVersion) { error.textContent = cause.message; error.hidden = false; } }
}
document.querySelectorAll('#dashboardPeriods button').forEach(button => button.addEventListener('click', () => {
    activePeriod = button.dataset.period;
    document.querySelectorAll('#dashboardPeriods button').forEach(item => item.classList.toggle('active', item === button));
    loadDashboard();
}));
async function openTaskList({key, picId, title}) {
    const period = activePeriod;
    const dialog = document.getElementById('dashboardListDialog');
    document.getElementById('dashboardListTitle').textContent = title || labels[key];
    document.getElementById('dashboardListCount').textContent = `${periods[period]} · Memuat...`;
    document.getElementById('dashboardListBody').innerHTML = '';
    dialog.showModal();
    try {
        const response = await fetch(`${api}?period=${period}&${picId ? `workload=1&pic=${encodeURIComponent(picId)}` : `list=${key}`}`, {headers:{Accept:'application/json'}});
        if (!response.ok) throw new Error('Daftar tidak dapat dimuat.');
        const data = await response.json();
        if (!dialog.open) return;
        document.getElementById('dashboardListCount').textContent = `${periods[period]} · ${data.count} task`;
        const body = document.getElementById('dashboardListBody'); body.innerHTML = taskRows(data.tasks); enableTaskLinks(body);
    } catch (cause) { document.getElementById('dashboardListCount').textContent = cause.message; }
}
document.querySelectorAll('#dashboardCards button').forEach(button => button.addEventListener('click', () => openTaskList({key: button.dataset.list})));
document.getElementById('dashboardListClose').addEventListener('click', () => document.getElementById('dashboardListDialog').close());
