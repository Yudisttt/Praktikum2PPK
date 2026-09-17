<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                    <a href="{{ route('workspaces.show', $workspace) }}" class="hover:underline text-indigo-600 font-medium">
                        &larr; {{ $workspace->name }}
                    </a>
                    <span>/</span>
                    <span>Project Detail</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ $project->name }}
                </h2>
            </div>

            <div class="flex items-center gap-2">
                @can('update', $project)
                    <a href="{{ route('workspaces.projects.edit', [$workspace, $project]) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md font-medium text-xs hover:bg-gray-200 transition">
                        Edit Project
                    </a>
                @endcan

                @can('delete', $project)
                    <form action="{{ route('workspaces.projects.destroy', [$workspace, $project]) }}" method="POST" onsubmit="return confirm('Hapus project ini beserta seluruh task di dalamnya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md font-medium text-xs hover:bg-red-200 transition">
                            Hapus Project
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Progress & Overview Header Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-2">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Deskripsi Project</h3>
                        <p class="text-gray-700 text-sm">
                            {{ $project->description ?: 'Tidak ada deskripsi.' }}
                        </p>

                        @if($project->deadline)
                            <p class="text-xs text-gray-500 pt-2">
                                📅 Deadline: <strong class="text-gray-800">{{ $project->deadline->format('d M Y') }}</strong>
                            </p>
                        @endif
                    </div>

                    @php
                        $progress = $project->progressPercentage();
                        $totalTasks = $project->tasks->count();
                        $doneTasks = $project->tasks->where('status.value', 'done')->count() + $project->tasks->where('status', 'done')->count();
                    @endphp
                    <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 space-y-3 flex flex-col justify-center">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-indigo-900 uppercase">Progress Project</span>
                            <span class="text-2xl font-black text-indigo-600">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-indigo-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="text-xs text-indigo-700 text-right">
                            <strong>{{ $doneTasks }}</strong> dari <strong>{{ $totalTasks }}</strong> task selesai
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Management Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Daftar Tugas (Tasks)</h3>
                        <p class="text-sm text-gray-500">Kelola dan tugaskan task ke anggota workspace.</p>
                    </div>

                    <a href="{{ route('workspaces.projects.tasks.create', [$workspace, $project]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        + Tambah Task
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ditugaskan Ke</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioritas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($project->tasks as $task)
                                @php
                                    $statusVal = $task->status->value ?? $task->status;
                                    $priorityVal = $task->priority->value ?? $task->priority;
                                @endphp
                                <tr class="{{ $statusVal === 'done' ? 'bg-gray-50/50' : '' }}">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 {{ $statusVal === 'done' ? 'line-through text-gray-400' : '' }}">
                                            {{ $task->title }}
                                        </div>
                                        @if($task->description)
                                            <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $task->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($task->assignee)
                                            <span class="inline-flex items-center gap-1">
                                                👤 {{ $task->assignee->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic text-xs">Belum di-assign</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($priorityVal === 'high')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">High</span>
                                        @elseif($priorityVal === 'medium')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Medium</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Low</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $task->deadline ? $task->deadline->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($statusVal === 'done')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Done</span>
                                        @elseif($statusVal === 'in_progress')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">In Progress</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">To Do</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @if($statusVal !== 'done')
                                            <form action="{{ route('workspaces.projects.tasks.markDone', [$workspace, $project, $task]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white font-semibold rounded shadow-sm transition">
                                                    ✓ Tandai Selesai
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('workspaces.projects.tasks.edit', [$workspace, $project, $task]) }}" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">Edit</a>

                                        <form action="{{ route('workspaces.projects.tasks.destroy', [$workspace, $project, $task]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus task ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                        Belum ada task di project ini. Klik "+ Tambah Task" untuk membuat task pertama.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
