<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-black leading-tight text-black">Task Manager</h2>
    </x-slot>

    <div class="min-h-screen bg-white py-6 sm:py-10">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6">

            <section class="border-2 border-black bg-white p-4 shadow-[6px_6px_0_0_#000] sm:p-6">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-lg font-black text-black sm:text-xl">{{ auth()->user()->name }}'s tasks</h3>
                    <span id="task-count" class="border-2 border-black px-2 py-0.5 font-mono text-sm font-bold"></span>
                </div>

                <div id="project-bar" class="mt-4 flex flex-wrap gap-2"></div>

                <form id="project-form" class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <input id="new-project-name" type="text" maxlength="100" placeholder="New project name"
                        class="min-w-0 flex-1 rounded-none border-2 border-black bg-white px-3 py-2 focus:border-black focus:shadow-[4px_4px_0_0_#000] focus:outline-none focus:ring-0">
                    <button type="submit"
                        class="border-2 border-black bg-white px-4 py-2 font-bold text-black shadow-[4px_4px_0_0_#000] transition hover:bg-black hover:text-white active:translate-x-1 active:translate-y-1 active:shadow-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                        Add project
                    </button>
                </form>
            </section>

            <section class="border-2 border-black bg-white p-4 shadow-[6px_6px_0_0_#000] sm:p-6">
                <form id="task-form" class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                    <input id="new-task-name" type="text" maxlength="255" placeholder="What needs to be done?" required
                        class="rounded-none border-2 border-black bg-white px-3 py-2 focus:border-black focus:shadow-[4px_4px_0_0_#000] focus:outline-none focus:ring-0 sm:col-span-6">
                    <select id="new-task-project" required
                        class="rounded-none border-2 border-black bg-white px-3 py-2 focus:border-black focus:shadow-[4px_4px_0_0_#000] focus:outline-none focus:ring-0 sm:col-span-3"></select>
                    <button type="submit"
                        class="border-2 border-black bg-black px-4 py-2 font-bold text-white shadow-[4px_4px_0_0_#000] transition hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[6px_6px_0_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black sm:col-span-3">
                        Add task
                    </button>
                </form>
                <p id="form-error" class="mt-3 hidden border-2 border-black px-3 py-2 text-sm font-bold" role="alert"></p>
            </section>

            <section class="border-2 border-black bg-white shadow-[6px_6px_0_0_#000]">
                <p class="border-b-2 border-black px-4 py-3 text-sm font-bold sm:px-6">
                    Drag the handle to change priority. #1 stays on top.
                </p>
                <ul id="task-list" class="space-y-3 p-3 sm:p-5"></ul>
            </section>
        </div>
    </div>

    <div id="toast" role="status"
        class="pointer-events-none fixed bottom-4 left-1/2 z-50 max-w-[90vw] -translate-x-1/2 border-2 border-black bg-black px-4 py-2 font-bold text-white opacity-0 shadow-[4px_4px_0_0_#000] transition-opacity"></div>

    <dialog id="edit-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-none border-4 border-black p-0 shadow-[8px_8px_0_0_#000] backdrop:bg-black/70">
        <div class="p-5 sm:p-6">
            <h3 class="text-xl font-black text-black">Edit task</h3>
            <input type="hidden" id="edit-task-id">

            <label for="edit-task-name" class="mt-4 block text-sm font-bold text-black">Task name</label>
            <input id="edit-task-name" type="text" maxlength="255"
                class="mt-1 block w-full rounded-none border-2 border-black bg-white px-3 py-2 focus:border-black focus:shadow-[4px_4px_0_0_#000] focus:outline-none focus:ring-0">

            <label for="edit-task-project" class="mt-4 block text-sm font-bold text-black">Project</label>
            <select id="edit-task-project"
                class="mt-1 block w-full rounded-none border-2 border-black bg-white px-3 py-2 focus:border-black focus:shadow-[4px_4px_0_0_#000] focus:outline-none focus:ring-0"></select>

            <p id="edit-error" class="mt-3 hidden border-2 border-black px-3 py-2 text-sm font-bold" role="alert"></p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button id="edit-cancel" type="button"
                    class="border-2 border-black bg-white px-4 py-2 font-bold text-black hover:bg-black hover:text-white">Cancel</button>
                <button id="edit-save" type="button"
                    class="border-2 border-black bg-black px-4 py-2 font-bold text-white shadow-[4px_4px_0_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none">Save changes</button>
            </div>
        </div>
    </dialog>

    <dialog id="delete-dialog" class="m-auto w-[calc(100%-2rem)] max-w-sm rounded-none border-4 border-black p-0 shadow-[8px_8px_0_0_#000] backdrop:bg-black/70">
        <div class="p-5 sm:p-6">
            <h3 class="text-xl font-black text-black">Delete this task?</h3>
            <p class="mt-2 text-sm text-black">
                <span id="delete-task-name" class="break-words font-bold underline"></span> will be removed for good.
            </p>
            <input type="hidden" id="delete-task-id">
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button id="delete-cancel" type="button"
                    class="border-2 border-black bg-white px-4 py-2 font-bold text-black hover:bg-black hover:text-white">Keep task</button>
                <button id="delete-confirm" type="button"
                    class="border-2 border-black bg-black px-4 py-2 font-bold text-white shadow-[4px_4px_0_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none">Delete task</button>
            </div>
        </div>
    </dialog>

@php
    $appData = [
        'projects' => $projects->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values(),
        'tasks' => $tasks,
        'filter' => $selectedProjectId ? (string) $selectedProjectId : '',
    ];
@endphp

<script id="app-data" type="application/json">@json($appData)</script>

@vite('resources/js/tasks.js')

    @vite('resources/js/tasks.js')
</x-app-layout>