@extends('admin.layout')

@section('page_title', 'Manual Verification Queue')

@section('content')
<div class="px-6 py-8 w-full max-w-7xl mx-auto">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" id="success-message">
        {{ session('success') }}
    </div>
    @endif

    <div id="verification-table-container" class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
        @include('admin.partials.verification-table')
    </div>

    <!-- NEW: Rejected Paper List (Physical Bin Rejections) -->
    <div class="mt-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-red-700 flex items-center gap-2">
                <span class="text-3xl">🗑️</span> Rejected Paper List (Physical Bin)
            </h2>
            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-red-200">
                Live Monitoring
            </span>
        </div>
        
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <div id="rejected-papers-container">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Grade Level</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Document Type</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rejected At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($rejectedPapers as $rej)
                        <tr class="hover:bg-red-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ $rej->last_name }}, {{ $rej->first_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $rej->display_grade }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 border border-orange-200">
                                    {{ $rej->document_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                {{ \Carbon\Carbon::parse($rej->rejected_at)->format('M d, g:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">
                                No physical paper rejections recorded.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Document Preview</h3>
                        <div class="mt-4 flex justify-center bg-gray-100 rounded p-2">
                            <img id="modalImage" src="" alt="Document Scan" class="max-h-[70vh] object-contain rounded border border-gray-300">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('imageModal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.getElementById('modalImage').src = '';
    }

    // REAL-TIME REFRESH LOGIC
    function refreshVerificationTable() {
        // Only refresh if the modal is hidden (don't interrupt the admin viewing an image)
        const modal = document.getElementById('imageModal');
        if (modal && modal.classList.contains('hidden')) {
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const container = document.getElementById('verification-table-container');
                if (container) {
                    container.innerHTML = html;
                }
            })
            .catch(error => console.error('Error refreshing table:', error));
        }
    }

    // Poll every 3 seconds
    setInterval(refreshVerificationTable, 3000);

    // Auto-hide success message after 5 seconds
    setTimeout(() => {
        const successMsg = document.getElementById('success-message');
        if (successMsg) {
            successMsg.style.display = 'none';
        }
    }, 5000);
</script>
@endsection
