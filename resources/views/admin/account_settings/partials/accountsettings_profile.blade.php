<div class="bg-[#F7FBF9]/50 rounded-2xl shadow-lg overflow-hidden border-l-8 border-[#1a8a44] mb-10">
    <div class="bg-white px-6 py-4 flex justify-between items-center border-b border-gray-100">
        <h2 class="text-xl font-black text-[#003918] uppercase tracking-tighter">
            User Identification
        </h2>
        <span class="bg-[#e8f5ed] text-[#1a8a44] text-[10px] font-bold px-2 py-1 rounded-full uppercase">
            {{ str_replace('_', ' ', auth()->user()->role) }}
        </span>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
            <div class="md:col-span-2 flex items-center gap-4 p-4 bg-white rounded-xl border border-gray-100">
                <div class="w-12 h-12 rounded-full bg-[#1a8a44] flex items-center justify-center text-white font-bold text-xl">
                    {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Official Name</p>
                    <p class="text-lg font-bold text-[#003918]">
                        {{ auth()->user()->first_name }} {{ auth()->user()->middle_name }} {{ auth()->user()->last_name }} {{ auth()->user()->extension_name }}
                    </p>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Registered Birthday</p>
                <p class="text-[#003918] font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round"/></svg>
                    {{ \Carbon\Carbon::parse(auth()->user()->birthday)->format('F d, Y') }}
                </p>
            </div>

            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Account Status</p>
                <p class="text-[#1a8a44] font-semibold flex items-center gap-2">
                    <span class="w-2 h-2 bg-[#1a8a44] rounded-full animate-pulse"></span>
                    Active System Access
                </p>
            </div>
        </div>

        <div class="mt-8 p-4 bg-amber-50 rounded-lg border border-amber-100">
            <p class="text-[11px] text-amber-700 leading-relaxed">
                <span class="font-bold">NOTE:</span> Identity information is managed by the Registrar. If your name or birthday is incorrect, please contact the <strong>Super Admin</strong> for a formal correction.
            </p>
        </div>
    </div>
</div>