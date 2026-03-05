<div x-data="{ activeTab: '{{ request('status', 'All') }}' }" class="flex justify-between items-end border-b border-gray-400">
    <div class="flex gap-3">
        @foreach(['All', 'Registered', 'Document Verified', 'Officially Enrolled'] as $tab)
            <button 
                @click="activeTab = '{{ $tab }}'; switchTab('{{ $tab }}')"
                :class="activeTab === '{{ $tab }}' ? 'text-[#005288] border-[#005288] font-bold' : 'text-gray-500 border-transparent font-medium'"
                class="pb-3 px-2 transition-all duration-200 text-sm border-b-4 -mb-[1px]">
                {{ $tab }}
            </button>
        @endforeach
    </div>

    <div class="flex items-center gap-4 pb-3"> {{-- Matches the pb-3 of the tabs for perfect baseline alignment --}}
        
        @php
            $lastSync = DB::table('sync_histories')->where('status', 'Success')->latest()->first();
        @endphp
        
        @if($lastSync)
            <span class="text-[10px] text-gray-400 mb-0.5">
                Last updated: {{ \Carbon\Carbon::parse($lastSync->created_at)->diffForHumans() }}
            </span>
        @endif

        <div class="flex items-center gap-2">
            {{-- Sync Button --}}
            <form action="{{ route('admin.sync.perform') }}" method="POST" x-data="{ syncing: false }" class="m-0">
                @csrf
                <button type="submit" 
                        @click="syncing = true"
                        :class="syncing ? 'opacity-75 cursor-not-allowed' : ''"
                        class="flex items-center gap-2 px-3 py-1.5 bg-[#F1F3F2] text-[#005288] font-semibold text-xs rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" :class="syncing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="syncing ? 'Syncing...' : 'Sync Data'"></span>
                </button>
            </form>

            {{-- Export Button --}}
            <button class="flex items-center gap-2 px-4 py-1.5 bg-[#005288] text-white font-semibold text-xs rounded-lg hover:bg-[#003d66] transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm1 8V3.5L18.5 10H15z"/>
                </svg>
                Export as Excel
            </button>
        </div>
    </div>
</div>