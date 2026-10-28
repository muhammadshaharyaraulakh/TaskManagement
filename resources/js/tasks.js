import Sortable from 'sortablejs';

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const initial = JSON.parse(document.getElementById('app-data').textContent);

const state = {
    projects: initial.projects,
    tasks: initial.tasks,
    filter: initial.filter,
};

const $ = (id) => document.getElementById(id);
const taskList = $('task-list');
let toastTimer = null;

const CLASSES = {
    chipBase: 'border-2 border-black px-3 py-1.5 text-sm font-bold transition',
    chipOn: 'bg-black text-white',
    chipOff: 'bg-white text-black shadow-[2px_2px_0_0_#000] hover:-translate-x-0.5 hover:-translate-y-0.5',
    row: 'task-item flex items-center gap-2 border-2 border-black bg-white p-2 shadow-[4px_4px_0_0_#000] sm:gap-3 sm:p-3',
    handle: 'drag-handle shrink-0 cursor-grab touch-none p-1 text-black active:cursor-grabbing',
    badge: 'priority-badge flex h-9 min-w-9 shrink-0 items-center justify-center border-2 border-black bg-black px-2 font-mono text-sm font-bold text-white',
    name: 'task-name break-words font-bold text-black',
    tag: 'mt-1 inline-block border border-black px-1.5 font-mono text-xs text-black',
    action: 'border-2 border-black bg-white p-2 text-black hover:bg-black hover:text-white',
    empty: 'border-2 border-dashed border-black py-10 text-center font-bold text-black',
};

const ICONS = {
    grip: '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true"><circle cx="9" cy="6" r="1.7"/><circle cx="15" cy="6" r="1.7"/><circle cx="9" cy="12" r="1.7"/><circle cx="15" cy="12" r="1.7"/><circle cx="9" cy="18" r="1.7"/><circle cx="15" cy="18" r="1.7"/></svg>',
    edit: '<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5l4 4L7 21l-4 1 1-4z"/></svg>',
    trash: '<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V3h8v3"/><path d="M19 6l-1 15H6L5 6"/></svg>',
};

function toast(text, isError = false) {
    const el = $('toast');
    el.textContent = isError ? 'Error: ' + text : text;
    el.classList.toggle('bg-black', !isError);
    el.classList.toggle('text-white', !isError);
    el.classList.toggle('bg-white', isError);
    el.classList.toggle('text-black', isError);
    el.classList.remove('opacity-0');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.add('opacity-0'), 2500);
}

function showError(el, text) {
    el.textContent = text;
    el.classList.remove('hidden');
}

async function api(url, method, data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    };

    if (data !== null) {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    const result = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = result.errors
            ? Object.values(result.errors)[0][0]
            : result.message || 'Something went wrong.';
        throw new Error(message);
    }

    return result;
}

function projectName(id) {
    const project = state.projects.find((p) => String(p.id) === String(id));
    return project ? project.name : '';
}

function renderProjectBar() {
    const bar = $('project-bar');
    bar.innerHTML = '';

    const addChip = (label, id) => {
        const active = String(state.filter) === String(id);
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.projectId = id;
        button.textContent = label;
        button.className = `${CLASSES.chipBase} ${active ? CLASSES.chipOn : CLASSES.chipOff}`;
        button.setAttribute('aria-pressed', String(active));
        bar.appendChild(button);
    };

    addChip('All projects', '');
    state.projects.forEach((p) => addChip(p.name, p.id));
}

function renderProjectSelects() {
    ['new-task-project', 'edit-task-project'].forEach((id) => {
        const select = $(id);
        const previous = select.value;
        select.innerHTML = '';

        if (state.projects.length === 0) {
            const option = new Option('Add a project first', '', true, true);
            option.disabled = true;
            select.appendChild(option);
            return;
        }

        state.projects.forEach((p) => select.appendChild(new Option(p.name, p.id)));
        select.value = previous || state.filter || state.projects[0].id;
    });
}

function buildRow(task) {
    const li = document.createElement('li');
    li.className = CLASSES.row;
    li.dataset.id = task.id;
    li.dataset.projectId = task.project_id;

    const handle = document.createElement('button');
    handle.type = 'button';
    handle.className = CLASSES.handle;
    handle.title = 'Drag to reorder';
    handle.setAttribute('aria-label', 'Drag to reorder');
    handle.innerHTML = ICONS.grip;

    const badge = document.createElement('span');
    badge.className = CLASSES.badge;
    badge.textContent = '#' + task.priority;

    const body = document.createElement('div');
    body.className = 'min-w-0 flex-1';

    const name = document.createElement('p');
    name.className = CLASSES.name;
    name.textContent = task.name;

    const tag = document.createElement('span');
    tag.className = CLASSES.tag;
    tag.textContent = task.project ? task.project.name : projectName(task.project_id);

    body.append(name, tag);

    const actions = document.createElement('div');
    actions.className = 'flex shrink-0 gap-2';

    const edit = document.createElement('button');
    edit.type = 'button';
    edit.className = `edit-task ${CLASSES.action}`;
    edit.setAttribute('aria-label', 'Edit task');
    edit.innerHTML = ICONS.edit;

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = `delete-task ${CLASSES.action}`;
    remove.setAttribute('aria-label', 'Delete task');
    remove.innerHTML = ICONS.trash;

    actions.append(edit, remove);
    li.append(handle, badge, body, actions);

    return li;
}

function renderTasks() {
    taskList.innerHTML = '';

    if (state.tasks.length === 0) {
        const empty = document.createElement('li');
        empty.className = CLASSES.empty;
        empty.textContent = state.projects.length === 0
            ? 'Add a project, then create your first task.'
            : 'No tasks here yet. Add one above.';
        taskList.appendChild(empty);
    } else {
        state.tasks.forEach((task) => taskList.appendChild(buildRow(task)));
    }

    const count = state.tasks.length;
    $('task-count').textContent = count + (count === 1 ? ' task' : ' tasks');
}

async function loadTasks() {
    const url = state.filter ? '/tasks?project_id=' + encodeURIComponent(state.filter) : '/tasks';
    const result = await api(url, 'GET');
    state.tasks = result.tasks;
    renderTasks();
}

function renumberBadges() {
    const counters = {};

    taskList.querySelectorAll('.task-item').forEach((li) => {
        const projectId = li.dataset.projectId;
        counters[projectId] = (counters[projectId] || 0) + 1;
        li.querySelector('.priority-badge').textContent = '#' + counters[projectId];
    });
}

function initSortable() {
    Sortable.create(taskList, {
        handle: '.drag-handle',
        draggable: '.task-item',
        animation: 150,
        ghostClass: 'opacity-40',

        onMove: (event) => {
            const related = event.related;

            if (!related || !related.classList.contains('task-item')) {
                return false;
            }

            return event.dragged.dataset.projectId === related.dataset.projectId;
        },

        onEnd: async (event) => {
            if (event.oldIndex === event.newIndex) {
                return;
            }

            renumberBadges();

            const payload = {
                task_ids: Array.from(taskList.querySelectorAll('.task-item')).map((li) => Number(li.dataset.id)),
            };

            if (state.filter) {
                payload.project_id = Number(state.filter);
            }

            try {
                const result = await api('/tasks/reorder', 'POST', payload);
                toast(result.message || 'Order saved.');
            } catch (error) {
                toast(error.message, true);
            }

            try {
                await loadTasks();
            } catch (error) {
                toast(error.message, true);
            }
        },
    });
}

$('project-bar').addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-project-id]');

    if (!button) {
        return;
    }

    state.filter = button.dataset.projectId;
    renderProjectBar();

    if (state.filter) {
        $('new-task-project').value = state.filter;
    }

    try {
        await loadTasks();
    } catch (error) {
        toast(error.message, true);
    }
});

$('project-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    const input = $('new-project-name');
    const name = input.value.trim();

    if (!name) {
        toast('Enter a project name.', true);
        return;
    }

    try {
        const result = await api('/projects', 'POST', { name });
        state.projects.push(result.project);
        state.filter = String(result.project.id);
        input.value = '';
        renderProjectBar();
        renderProjectSelects();
        $('new-task-project').value = state.filter;
        toast(result.message || 'Project added.');
        await loadTasks();
    } catch (error) {
        toast(error.message, true);
    }
});

$('task-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    const errorBox = $('form-error');
    errorBox.classList.add('hidden');

    const name = $('new-task-name').value.trim();
    const projectId = $('new-task-project').value;

    if (!name) {
        showError(errorBox, 'Enter a task name.');
        return;
    }

    if (!projectId) {
        showError(errorBox, 'Add or select a project first.');
        return;
    }

    try {
        const result = await api('/tasks', 'POST', { name, project_id: Number(projectId) });
        $('new-task-name').value = '';
        toast(result.message || 'Task created.');
        await loadTasks();
    } catch (error) {
        showError(errorBox, error.message);
    }
});

taskList.addEventListener('click', (event) => {
    const row = event.target.closest('.task-item');

    if (!row) {
        return;
    }

    const taskName = row.querySelector('.task-name').textContent.trim();

    if (event.target.closest('.edit-task')) {
        $('edit-task-id').value = row.dataset.id;
        $('edit-task-name').value = taskName;
        $('edit-task-project').value = row.dataset.projectId;
        $('edit-error').classList.add('hidden');
        $('edit-dialog').showModal();
    }

    if (event.target.closest('.delete-task')) {
        $('delete-task-id').value = row.dataset.id;
        $('delete-task-name').textContent = taskName;
        $('delete-dialog').showModal();
    }
});

$('edit-cancel').addEventListener('click', () => $('edit-dialog').close());
$('delete-cancel').addEventListener('click', () => $('delete-dialog').close());

['edit-dialog', 'delete-dialog'].forEach((id) => {
    $(id).addEventListener('click', (event) => {
        if (event.target === event.currentTarget) {
            event.currentTarget.close();
        }
    });
});

$('edit-save').addEventListener('click', async () => {
    const errorBox = $('edit-error');
    errorBox.classList.add('hidden');

    const name = $('edit-task-name').value.trim();
    const projectId = $('edit-task-project').value;

    if (!name) {
        showError(errorBox, 'Enter a task name.');
        return;
    }

    if (!projectId) {
        showError(errorBox, 'Select a project.');
        return;
    }

    try {
        const result = await api('/tasks/' + $('edit-task-id').value, 'PUT', {
            name,
            project_id: Number(projectId),
        });
        $('edit-dialog').close();
        toast(result.message || 'Task updated.');
        await loadTasks();
    } catch (error) {
        showError(errorBox, error.message);
    }
});

$('delete-confirm').addEventListener('click', async () => {
    try {
        const result = await api('/tasks/' + $('delete-task-id').value, 'DELETE');
        $('delete-dialog').close();
        toast(result.message || 'Task deleted.');
        await loadTasks();
    } catch (error) {
        toast(error.message, true);
    }
});

renderProjectBar();
renderProjectSelects();
renderTasks();
initSortable();