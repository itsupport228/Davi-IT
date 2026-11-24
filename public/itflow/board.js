const boardApi = '/board/api';
const boardHeaders = {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content};

let activeBoardModal = null;
let modalRestoreFocus = null;
let modalAction = null;
let modalCloseTimer = null;

function boardModal({title, name, impact, onConfirm}) {
    const dialog = document.getElementById('boardConfirmDialog');
    if (!dialog) throw new Error('Modal konfirmasi tidak tersedia.');
    if (modalCloseTimer) { clearTimeout(modalCloseTimer); modalCloseTimer = null; }
    if (dialog.open) dialog.close();
    dialog.querySelector('#boardConfirmTitle').textContent = title;
    dialog.querySelector('[data-confirm-name]').textContent = name;
    dialog.querySelector('[data-confirm-impact]').textContent = impact;
    const confirm = dialog.querySelector('[data-modal-confirm]');
    confirm.disabled = false; confirm.classList.remove('is-busy'); confirm.textContent = 'Hapus';
    modalAction = onConfirm; modalRestoreFocus = document.activeElement; activeBoardModal = dialog;
    dialog.classList.remove('is-closing'); dialog.showModal();
    requestAnimationFrame(() => dialog.classList.add('is-open'));
}

function closeBoardModal() {
    const dialog = document.getElementById('boardConfirmDialog');
    if (!dialog?.open) return;
    dialog.classList.remove('is-open'); dialog.classList.add('is-closing');
    modalCloseTimer = setTimeout(() => { if (dialog.open) dialog.close(); dialog.classList.remove('is-closing'); activeBoardModal = null; modalAction = null; modalRestoreFocus?.focus?.(); modalCloseTimer = null; }, 160);
}

document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('boardConfirmDialog');
    if (!dialog) return;
    dialog.querySelectorAll('[data-modal-close], [data-modal-cancel]').forEach(button => button.addEventListener('click', closeBoardModal));
    dialog.addEventListener('cancel', event => { event.preventDefault(); closeBoardModal(); });
    dialog.addEventListener('click', event => { if (event.target === dialog) closeBoardModal(); });
    dialog.querySelector('[data-modal-confirm]').addEventListener('click', async event => {
        const confirm = event.currentTarget;
        if (confirm.disabled || !modalAction) return;
        const action = modalAction; confirm.disabled = true; confirm.classList.add('is-busy'); confirm.textContent = 'Memproses...'; modalAction = null;
        try { await action(); closeBoardModal(); } catch (error) { modalAction = action; confirm.disabled = false; confirm.classList.remove('is-busy'); confirm.textContent = 'Hapus'; showToast(error.message, 'error'); }
    });
});

function confirmBoardDelete({type, name, impact, onConfirm}) {
    return boardModal({title: `Hapus ${type}?`, name, impact, onConfirm});
}

function setFormBusy(form, busy) { form?.querySelectorAll('button, input, textarea, select').forEach(el => { el.disabled = busy; el.classList.toggle('is-busy', busy); }); }
function showFieldError(input, message) {
    input?.parentElement.querySelector('.field-error')?.remove();
    if (!input || !message) return;
    const error = document.createElement('div'); error.className = 'field-error'; error.textContent = message; input.parentElement.append(error); input.setAttribute('aria-invalid', 'true');
}

async function boardRequest(path, method = 'GET', data) {
    const response = await fetch(`${boardApi}${path}`, {method, headers: boardHeaders, body: data ? JSON.stringify(data) : undefined});
    if (response.status === 401) { location.href = '/login'; throw new Error('Sesi berakhir. Silakan masuk lagi.'); }
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || result.message || 'Permintaan gagal.');
    return result;
}

const originalTheme = toggleDarkMode;
toggleDarkMode = function () {
    originalTheme();
    localStorage.setItem('itflow-board-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
};
handleLogout = function () { document.getElementById('laravel-logout').submit(); };

const originalOpenCreate = openCreateModal;
openCreateModal = function (status = 'todo') {
    originalOpenCreate(status);
    const board = document.getElementById('createBoardSelect');
    if (Object.keys(boards).length === 0) { closeCreateModal(); showToast('Buat project terlebih dahulu.', 'warning'); return; }
    const subs = document.getElementById('createSubProjectSelect');
    if (selectedSubProjectFilter !== 'ALL') subs.value = selectedSubProjectFilter;
};

const originalOpenBoard = openNewBoardModal;
openNewBoardModal = function () {
    originalOpenBoard();
    document.getElementById('newBoardPrefix').maxLength = 12;
};

const originalOpenExport = openExportModal;
openExportModal = function () {
    originalOpenExport();
    updateExportPreviewCount();
};

const originalRenderSidebar = renderSidebarProjectTree;
renderSidebarProjectTree = function () {
    originalRenderSidebar();
    const rows = document.querySelectorAll('#sidebarProjectTree > div');
    Object.entries(boards).forEach(([prefix, name], index) => {
        const row = rows[index];
        if (!row) return;
        const actions = row.firstElementChild?.querySelector('button[onclick^="toggleBoardDropdown"]')?.parentElement;
        if (actions) actions.insertBefore(deleteButton(`Hapus project ${prefix} — ${name}`, async () => {
            await boardRequest(`/projects/${encodeURIComponent((await boardRequest('/meta')).projects.find(p => p.prefix === prefix).id)}`, 'DELETE');
            delete boards[prefix];
            subProjects = subProjects.filter(sub => sub.board !== prefix);
            delete expandedBoards[prefix];
            if (selectedBoardFilter === prefix) selectSidebarScope('ALL', 'ALL');
            else renderSidebarProjectTree();
            showToast(`Project ${name} berhasil dihapus.`, 'success');
        }), actions.querySelector('button[onclick^="toggleBoardDropdown"]'));

        row.querySelectorAll('button[onclick^="selectSidebarScope"]').forEach(button => {
            const match = button.getAttribute('onclick')?.match(/'([^']+)'\s*\)$/);
            const sub = subProjects.find(item => item.board === prefix && item.id === match?.[1]);
            if (!sub) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'sns-subproject-row';
            button.parentNode.insertBefore(wrapper, button);
            wrapper.append(button, deleteButton(`Hapus sub-project ${sub.name} dari ${name}`, async () => {
                await boardRequest(`/sub-projects/${encodeURIComponent(sub.id)}`, 'DELETE');
                subProjects = subProjects.filter(item => item.id !== sub.id);
                if (selectedBoardFilter === prefix && selectedSubProjectFilter === sub.id) selectSidebarScope(prefix, 'ALL');
                else renderSidebarProjectTree();
                showToast(`Sub-Project ${sub.name} berhasil dihapus.`, 'success');
            }));
        });
    });
};

function deleteButton(label, remove) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'sns-sidebar-delete';
    button.title = label;
    button.setAttribute('aria-label', label);
    button.innerHTML = '<span aria-hidden="true">×</span>';
    button.addEventListener('click', async event => {
        event.stopPropagation();
        const isProject = label.toLowerCase().includes('project') && !label.toLowerCase().includes('sub-project');
        const isSubProject = label.toLowerCase().includes('sub-project');
        confirmBoardDelete({type: isProject ? 'project' : isSubProject ? 'sub-project' : 'item', name: label.replace(/^Hapus\s+/i, '').replace(/\?$/, ''), impact: isProject ? 'Project hanya dapat dihapus jika tidak memiliki task atau sub-project.' : isSubProject ? 'Sub-project hanya dapat dihapus jika tidak memiliki task.' : 'Data task dan seluruh detailnya akan dihapus.', onConfirm: remove});
    });
    return button;
}

handleCreateBoard = async function (event) {
    event.preventDefault();
    const form = event.currentTarget; setFormBusy(form, true);
    const name = document.getElementById('newBoardName').value.trim();
    const prefix = document.getElementById('newBoardPrefix').value.trim().toUpperCase();
    const sub_projects = document.getElementById('newBoardSubProjectsInput').value.split('\n').map(s => s.trim()).filter(Boolean);
    if (!name) { showFieldError(document.getElementById('newBoardName'), 'Nama project wajib diisi.'); setFormBusy(form, false); return; }
    if (!prefix) { showFieldError(document.getElementById('newBoardPrefix'), 'Prefix project wajib diisi.'); setFormBusy(form, false); return; }
    try {
        const project = await boardRequest('/projects', 'POST', {name, prefix, sub_projects});
        boards[project.prefix] = project.name;
        const meta = await boardRequest('/meta');
        subProjects = meta.subProjects.map(s => ({id: String(s.id), board: meta.projects.find(p => p.id === s.project_id)?.prefix, name: s.name}));
        closeNewBoardModal();
        selectSidebarScope(project.prefix, 'ALL');
        showToast(`Project ${project.prefix} (${project.name}) berhasil dibuat!`, 'success');
    } catch (error) { showToast(error.message, 'error'); } finally { setFormBusy(form, false); }
};

handleCreateSubProject = async function (event) {
    event.preventDefault();
    const form = event.currentTarget; setFormBusy(form, true);
    const prefix = document.getElementById('newSubProjectBoardSelect').value;
    const name = document.getElementById('newSubProjectName').value.trim();
    if (!name) { showFieldError(document.getElementById('newSubProjectName'), 'Nama sub-project wajib diisi.'); setFormBusy(form, false); return; }
    try {
        const meta = await boardRequest('/meta');
        const project = meta.projects.find(p => p.prefix === prefix);
        const sub = await boardRequest('/sub-projects', 'POST', {project_id: project.id, name});
        subProjects.push({id: String(sub.id), board: prefix, name: sub.name});
        expandedBoards[prefix] = true;
        closeNewBoardModal(); renderSidebarProjectTree(); renderBoard();
        showToast(`Sub-Project "${name}" ditambahkan ke ${prefix}!`, 'success');
    } catch (error) { showToast(error.message, 'error'); } finally { setFormBusy(form, false); }
};

handleCreateTask = async function (event) {
    event.preventDefault();
    const form = event.currentTarget; setFormBusy(form, true);
    const prefix = document.getElementById('createBoardSelect').value;
    const subProjectId = document.getElementById('createSubProjectSelect').value;
    const titleInput = document.getElementById('createTitleInput');
    if (!titleInput.value.trim()) { showFieldError(titleInput, 'Judul task wajib diisi.'); setFormBusy(form, false); return; }
    try {
        const meta = await boardRequest('/meta');
        const created = await boardRequest('/tasks', 'POST', {
            project_id: meta.projects.find(p => p.prefix === prefix).id, sub_project_id: subProjectId ? Number(subProjectId) : null,
            status: document.getElementById('createStatusSelect').value,
            assignee_id: Number(document.getElementById('createAssigneeSelect').value),
            title: document.getElementById('createTitleInput').value.trim(),
            description: document.getElementById('createDescInput').value.trim(),
            category: document.getElementById('createCategorySelect').value,
            priority: document.getElementById('createPrioritySelect').value,
            subtasks: document.getElementById('createSubtasksInput').value.split('\n').map(s => s.trim()).filter(Boolean),
        });
        tasks.unshift(toPrototypeTask(created));
        closeCreateModal(); renderSidebarProjectTree(); renderBoard();
        showToast(`Task ${created.code} berhasil dibuat!`, 'success');
    } catch (error) { showToast(error.message, 'error'); } finally { setFormBusy(form, false); }
};

function toPrototypeTask(data) {
    const date = new Date(data.created_at);
    return {id: String(data.id), code: data.code, board: data.project.prefix,
        subProjectId: data.sub_project_id === null ? '' : String(data.sub_project_id), category: data.category, title: data.title,
        description: data.description, status: data.status, priority: data.priority,
        assigneeId: String(data.assignee_id), reporterId: String(data.reporter_id), dueDate: data.due_date || '',
        createdAt: `${String(date.getDate()).padStart(2, '0')} ${['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][date.getMonth()]} ${date.getFullYear()}, ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`,
        subtasks: data.subtasks.map(s => ({id: String(s.id), title: s.title, done: Boolean(s.done)})),
        comments: data.comments.map(c => ({id: String(c.id), userId: String(c.user_id), text: c.text,
            time: new Date(c.time).toLocaleString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'})}))};
}

const originalOpenDetail = openDetailModal;
openDetailModal = async function (id) {
    try {
        const data = await boardRequest(`/tasks/${id}`);
        const index = tasks.findIndex(t => t.id === String(id));
        if (index < 0) return;
        tasks[index] = toPrototypeTask(data);
        originalOpenDetail(String(id));
        document.getElementById('detailReporterName').textContent = data.reporter?.name || 'Sistem IT';
    } catch (error) { showToast(error.message, 'error'); }
};

updateExportPreviewCount = async function () {
    const badge = document.getElementById('exportMatchedCountBadge');
    const scope = document.getElementById('exportBoardScope').value;
    const query = new URLSearchParams({period: selectedExportPeriod, preview: '1'});
    if (scope !== 'ALL') query.set('project', scope);
    try {
        const result = await boardRequest(`/export?${query}`);
        badge.textContent = `${result.count} Task`;
    } catch (error) { badge.textContent = '—'; showToast(error.message, 'error'); }
};

saveDetailChanges = async function () {
    if (!activeTaskId || !draftTask) return;
    if (!draftTask.title.trim()) { showToast('Judul task tidak boleh kosong.', 'error'); return; }
    const saveButton = document.querySelector('#detailModal button[onclick="saveDetailChanges()"]');
    if (saveButton?.disabled) return;
    if (saveButton) { saveButton.disabled = true; saveButton.classList.add('is-busy'); }
    try {
        const updated = await boardRequest(`/tasks/${activeTaskId}`, 'PATCH', {
            title: draftTask.title, description: draftTask.description || '', status: draftTask.status,
            sub_project_id: draftTask.subProjectId ? Number(draftTask.subProjectId) : null, category: draftTask.category,
            priority: draftTask.priority, assignee_id: Number(draftTask.assigneeId), due_date: draftTask.dueDate || null,
            subtasks: draftTask.subtasks.map(s => ({id: /^\d+$/.test(s.id) ? Number(s.id) : null, title: s.title, done: s.done})),
            comment: draftTask.comments.filter(c => String(c.id).startsWith('c-')).map(c => c.text).join('\n'),
        });
        tasks[tasks.findIndex(t => t.id === activeTaskId)] = toPrototypeTask(updated);
        closeDetailModal(); renderSidebarProjectTree(); renderBoard();
        showToast(`Perubahan pada ${updated.code} berhasil disimpan!`, 'success');
    } catch (error) { showToast(error.message, 'error'); } finally { if (saveButton) { saveButton.disabled = false; saveButton.classList.remove('is-busy'); } }
};

deleteCurrentTask = async function () {
    if (!activeTaskId) return;
    confirmBoardDelete({type: 'task', name: `${draftTask.code} — ${draftTask.title}`, impact: 'Task, checklist, dan catatan terkait akan dihapus permanen.', onConfirm: async () => {
        await boardRequest(`/tasks/${activeTaskId}`, 'DELETE');
        const code = draftTask.code;
        tasks = tasks.filter(t => t.id !== activeTaskId);
        closeDetailModal(); renderSidebarProjectTree(); renderBoard();
        showToast(`Task ${code} telah dihapus.`, 'info');
    }});
};

initSortable = function () {
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];
    document.querySelectorAll('.kanban-column').forEach(column => {
        const instance = new Sortable(column, {group: 'itflow-kanban', animation: 160,
            ghostClass: 'sortable-ghost', dragClass: 'sortable-drag', onEnd: async event => {
                const task = tasks.find(t => t.id === event.item.dataset.id);
                const status = event.to.dataset.status;
                if (!task || task.status === status) return;
                try {
                    await boardRequest(`/tasks/${task.id}/status`, 'PATCH', {status});
                    if (status === 'done') {
                        const unfinished = task.subtasks.filter(s => !s.done).length;
                        if (unfinished) showToast(`Perhatian: Masih ada ${unfinished} sub-task yang belum selesai pada ${task.code}.`, 'warning');
                    }
                    task.status = status; renderBoard(); renderSidebarProjectTree();
                } catch (error) { renderBoard(); showToast(error.message, 'error'); }
            }});
        sortableInstances.push(instance);
    });
};

confirmExportExcel = function () {
    const scope = document.getElementById('exportBoardScope').value;
    const query = new URLSearchParams({period: selectedExportPeriod});
    if (scope !== 'ALL') query.set('project', scope);
    closeExportModal();
    window.location.href = `/board/api/export?${query}`;
};

document.addEventListener('DOMContentLoaded', () => {
    const user = teamMembers.find(member => member.id === currentUserId);
    document.getElementById('mainAppContainer').classList.remove('hidden');
    if (localStorage.getItem('itflow-board-theme') === 'dark') document.documentElement.classList.add('dark');
    applyAuthenticatedUserUI(user);
    renderSidebarProjectTree(); renderAvatarFilters(); renderPriorityFilterPills(); renderBoard(); initSortable();
});
