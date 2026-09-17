<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Workspace Saya') }}
            </h2>
            <a href="{{ route('workspaces.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                + Buat Workspace
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            @if($workspaces->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada workspace</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai kolaborasi dengan membuat workspace baru.</p>
                    <div class="mt-6">
                        <a href="{{ route('workspaces.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Buat Workspace Sekarang
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($workspaces as $workspace)
                        @php
                            $role = $workspace->pivot->role ?? 'member';
                        @endphp
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 hover:shadow-md transition">
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-start">
                                    <h3 class="text-lg font-bold text-gray-900 truncate">
                                        <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-indigo-600">
                                            {{ $workspace->name }}
                                        </a>
                                    </h3>
                                    @if($role === 'owner')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                            Owner
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            Member
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center text-xs text-gray-500 space-x-4">
                                    <div>
                                        📁 <span class="font-medium text-gray-700">{{ $workspace->projects->count() }}</span> Project
                                    </div>
                                    <div>
                                        👥 <span class="font-medium text-gray-700">{{ $workspace->members->count() }}</span> Anggota
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <a href="{{ route('workspaces.show', $workspace) }}" class="block w-full text-center px-4 py-2 bg-gray-50 hover:bg-gray-100 text-indigo-600 font-semibold rounded text-sm transition">
                                        Buka Workspace &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
