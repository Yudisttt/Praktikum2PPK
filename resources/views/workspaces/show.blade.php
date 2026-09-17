<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ $workspace->name }}
                </h2>
                @if($userRole === 'owner')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        Owner
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                        Member
                    </span>
                @endif
            </div>

            @can('update', $workspace)
                <div class="flex items-center gap-2">
                    <a href="{{ route('workspaces.edit', $workspace) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md font-medium text-xs hover:bg-gray-200 transition">
                        Edit Workspace
                    </a>
                    @can('delete', $workspace)
                        <form action="{{ route('workspaces.destroy', $workspace) }}" method="POST" onsubmit="return confirm('Hapus workspace ini beserta seluruh project dan task di dalamnya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md font-medium text-xs hover:bg-red-200 transition">
                                Hapus Workspace
                            </button>
                        </form>
                    @endcan
                </div>
            @endcan
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

            <!-- Section 1: Dashboard Projects -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Project dalam Workspace</h3>
                        <p class="text-sm text-gray-500">Pantau progress pengerjaan tugas di setiap project.</p>
                    </div>

                    <a href="{{ route('workspaces.projects.create', $workspace) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        + Buat Project Baru
                    </a>
                </div>

                @if($workspace->projects->isEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-center text-gray-500">
                        Belum ada project di workspace ini. Klik "+ Buat Project Baru" untuk memulai.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($workspace->projects as $project)
                            @php
                                $progress = $project->progressPercentage();
                                $totalTasks = $project->tasks->count();
                                $doneTasks = $project->tasks->where('status.value', 'done')->count() + $project->tasks->where('status', 'done')->count();
                            @endphp
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6 flex flex-col justify-between hover:shadow-md transition">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-gray-900 text-lg">
                                            <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}" class="hover:text-indigo-600">
                                                {{ $project->name }}
                                            </a>
                                        </h4>
                                    </div>
                                    @if($project->description)
                                        <p class="text-xs text-gray-600 line-clamp-2">{{ $project->description }}</p>
                                    @endif

                                    @if($project->deadline)
                                        <p class="text-xs text-gray-400">
                                            📅 Deadline: <span class="font-medium text-gray-600">{{ $project->deadline->format('d M Y') }}</span>
                                        </p>
                                    @endif

                                    <!-- Progress Bar -->
                                    <div class="space-y-1.5 pt-2">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-medium text-gray-600">Progress</span>
                                            <span class="font-bold text-indigo-600">{{ $progress }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 mt-4 border-t border-gray-100 flex justify-between items-center text-xs">
                                    <span class="text-gray-500">Task: <strong class="text-gray-800">{{ $doneTasks }}/{{ $totalTasks }}</strong> selesai</span>
                                    <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}" class="font-semibold text-indigo-600 hover:text-indigo-900">
                                        Lihat Task &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Section 2: Anggota Workspace -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Anggota Workspace</h3>
                        <p class="text-sm text-gray-500">Anggota yang dapat mengakses dan mengerjakan project di sini.</p>
                    </div>

                    @can('inviteMember', $workspace)
                        <form action="{{ route('workspaces.members.invite', $workspace) }}" method="POST" class="flex gap-2">
                            @csrf
                            <x-text-input name="email" type="email" placeholder="Email anggota baru..." required class="text-sm px-3 py-1.5" />
                            <x-primary-button class="text-xs">+ Undang</x-primary-button>
                        </form>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role Workspace</th>
                                @can('removeMember', $workspace)
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($workspace->members as $member)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ $member->name }}
                                        @if($member->id === auth()->id())
                                            <span class="text-xs text-indigo-600 font-semibold">(Anda)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-sm">
                                        {{ $member->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($member->pivot->role === 'owner')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                Owner
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                                Member
                                            </span>
                                        @endif
                                    </td>
                                    @can('removeMember', $workspace)
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            @if($member->pivot->role !== 'owner')
                                                <form action="{{ route('workspaces.members.remove', [$workspace, $member]) }}" method="POST" class="inline" onsubmit="return confirm('Keluarkan anggota ini dari workspace?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Keluarkan</button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-xs italic">-</span>
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
