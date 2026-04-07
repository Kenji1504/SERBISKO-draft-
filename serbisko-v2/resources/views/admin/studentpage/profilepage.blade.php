@extends('admin.layout')

@section('page_title')
    Student Profile
@endsection

@section('content')
<div class="p-6 font-['Inter'] tracking-normal space-y-4">

    <div class="bg-[#F7FBF9]/40 rounded-xl shadow-md border border-gray-100 p-5">
        <h2 class="text-[#005288] text-2xl font-extrabold uppercase tracking-tight">
            {{ $student->first_name }} {{ $student->middle_name ? substr($student->middle_name, 0, 1) . '.' : '' }} {{ $student->last_name }} {{ $student->extension_name ? $student->extension_name : '' }}
        </h2>
        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-tight">{{ $student->lrn }}</h3>
        
        <div class="mt-6 pt-4 border-t border-gray-100">
            <div class="mb-3">
                <label for="sectionSelect" class="block text-xs font-semibold text-gray-600 mb-1">Select LIS Section to enroll:</label>
                <select id="sectionSelect" class="w-full sm:w-72 px-3 py-2 rounded-lg border border-gray-300 focus:border-[#005288] focus:outline-none">
                    <option value="">Choose section...</option>
                    <option value="Grade 11 - Bezos (SY 2025–2026)">Grade 11 - Bezos (SY 2025–2026)</option>
                    <option value="Grade 11 - Gates (SY 2025–2026)">Grade 11 - Gates (SY 2025–2026)</option>
                    <option value="Grade 11 - Buffett (SY 2025–2026)">Grade 11 - Buffett (SY 2025–2026)</option>
                    <option value="Grade 11 - Arnault (SY 2025–2026)">Grade 11 - Arnault (SY 2025–2026)</option>
                    <option value="Grade 11 - Ellison (SY 2025–2026)">Grade 11 - Ellison (SY 2025–2026)</option>
                    <option value="Grade 11 - Page (SY 2025–2026)">Grade 11 - Page (SY 2025–2026)</option>
                    <option value="Grade 11 - Musk (SY 2025–2026)">Grade 11 - Musk (SY 2025–2026)</option>
                    <option value="Grade 11 - Ambani (SY 2025–2026)">Grade 11 - Ambani (SY 2025–2026)</option>
                    <option value="Grade 11 - Bang Si-Hyuk (SY 2025–2026)">Grade 11 - Bang Si-Hyuk (SY 2025–2026)</option>
                    <option value="Grade 11 - Husserl (SY 2025–2026)">Grade 11 - Husserl (SY 2025–2026)</option>
                    <option value="Grade 11 - Comte (SY 2025–2026)">Grade 11 - Comte (SY 2025–2026)</option>
                    <option value="Grade 11 - Aquinas (SY 2025–2026)">Grade 11 - Aquinas (SY 2025–2026)</option>
                    <option value="Grade 11 - Herodotus (SY 2025–2026)">Grade 11 - Herodotus (SY 2025–2026)</option>
                    <option value="Grade 11 - Durkheim (SY 2025–2026)">Grade 11 - Durkheim (SY 2025–2026)</option>
                    <option value="Grade 11 - Enriquez (SY 2025–2026)">Grade 11 - Enriquez (SY 2025–2026)</option>
                    <option value="Grade 11 - Freud (SY 2025–2026)">Grade 11 - Freud (SY 2025–2026)</option>
                    <option value="Grade 11 - Heidegger (SY 2025–2026)">Grade 11 - Heidegger (SY 2025–2026)</option>
                    <option value="Grade 11 - Confucius (SY 2025–2026)">Grade 11 - Confucius (SY 2025–2026)</option>
                    <option value="Grade 11 - Mercado (SY 2025–2026)">Grade 11 - Mercado (SY 2025–2026)</option>
                    <option value="Grade 11 - Plato (SY 2025–2026)">Grade 11 - Plato (SY 2025–2026)</option>
                    <option value="Grade 11 - Socrates (SY 2025–2026)">Grade 11 - Socrates (SY 2025–2026)</option>
                    <option value="Grade 11 - Adler (SY 2025–2026)">Grade 11 - Adler (SY 2025–2026)</option>
                    <option value="Grade 11 - Agoncillo (SY 2025–2026)">Grade 11 - Agoncillo (SY 2025–2026)</option>
                    <option value="Grade 11 - Salazar (SY 2025–2026)">Grade 11 - Salazar (SY 2025–2026)</option>
                    <option value="Grade 11 - Descartes (SY 2025–2026)">Grade 11 - Descartes (SY 2025–2026)</option>
                    <option value="Grade 11 - Euclid (SY 2025–2026)">Grade 11 - Euclid (SY 2025–2026)</option>
                    <option value="Grade 11 - Pythagoras (SY 2025–2026)">Grade 11 - Pythagoras (SY 2025–2026)</option>
                    <option value="Grade 11 - Archimedes (SY 2025–2026)">Grade 11 - Archimedes (SY 2025–2026)</option>
                    <option value="Grade 11 - Fibonacci (SY 2025–2026)">Grade 11 - Fibonacci (SY 2025–2026)</option>
                    <option value="Grade 11 - Diophantus (SY 2025–2026)">Grade 11 - Diophantus (SY 2025–2026)</option>
                    <option value="Grade 11 - Lovelace (SY 2025–2026)">Grade 11 - Lovelace (SY 2025–2026)</option>
                </select>
            </div>
            {{-- REVERTED: Added onclick directly back --}}
            <button type="button" id="useProfileBtn" class="inline-flex items-center bg-[#005288] hover:bg-[#003f66] text-white font-semibold px-5 py-2.5 rounded-lg shadow-sm transition focus:ring-2 focus:ring-offset-2 focus:ring-[#005288] outline-none" onclick="triggerEnrollmentFormFiller()">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span id="useProfileBtnText">Use Profile</span>
            </button>
            <button type="button" id="confirmEnrollmentBtn" class="hidden ml-3 inline-flex items-center bg-[#008000] hover:bg-[#006600] text-white font-semibold px-5 py-2.5 rounded-lg shadow-sm transition focus:ring-2 focus:ring-offset-2 focus:ring-[#008000] outline-none" onclick="confirmEnrollment()">
                Confirm Enrollment Completed
            </button>
            <p class="text-xs text-gray-500 italic mt-2 ml-1">
                This will open the LIS enrollment form with the student's saved information ready to be verified and submitted by you.
            </p>
            <div id="enrollmentStatus" class="mt-3 hidden p-3 rounded-lg text-sm">
                <p id="enrollmentStatusText"></p>
            </div>
        </div>
    </div>

    {{-- Rest of student info sections... --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- [SAME AS BEFORE] --}}
        @php
        $age = '—';
        if (!empty($student->birthday)) {
            try { $age = \Carbon\Carbon::parse($student->birthday)->age; } catch (\Exception $e) { $age = 'Invalid Date'; }
        }
        @endphp

        @php
            $renderFields = function($fields, $cols = 'md:grid-cols-2') {
                $html = "<div class='grid grid-cols-1 $cols gap-x-12 gap-y-6'>";
                foreach ($fields as $label => $value) {
                    $val = $value ?: '—';
                    $html .= "
                    <div class='relative border-b-2 border-gray-200 pb-1 group hover:border-[#005288] transition-colors duration-200'>
                        <label class='block text-[10px] font-bold text-gray-400 mb-1'>$label</label>
                        <p class='text-[13px] uppercase text-[#003918] min-h-6'>$val</p>
                    </div>";
                }
                $html .= "</div>";
                return $html;
            };
        @endphp

        <div class="lg:col-span-2 bg-[#F7FBF9]/40 rounded-xl shadow-md border border-gray-100 p-7">
            <h2 class="text-[#005288] text-sm font-extrabold mb-4 uppercase">Learner’s Information</h2>
            {!! $renderFields([
                'LRN:' => $student->lrn, 'Birthdate:' => $student->birthday??'—',
                'Last Name:' => $student->last_name??'—', 'Birthplace:' => $student->place_of_birth??'—',
                'First Name:' => $student->first_name??'—', 'Age:' => $age,
                'Middle Name:' => $student->middle_name??'—', 'Mother Tongue:' => $student->mother_tongue??'—',
                'Extension:' => $student->extension_name ?? '—', 'Gender:' => $student->sex??'—',
            ]) !!}
        </div>

        <div class="bg-[#F7FBF9]/40 rounded-xl shadow-md border border-gray-100 p-7">
            <h2 class="text-[#005288] text-sm font-extrabold mb-4 uppercase">Enrolment</h2>
            {!! $renderFields([
                'School Year:' => $details['School Year'] ?? '—',
                'Grade Level:' => $finalGrade,    
                'Track:'       => $finalTrack,   
                'Cluster of Electives:'    => $finalCluster,  
                'Status:'      => $finalStatus    
            ], 'grid-cols-1') !!}
        </div>

        <div class="lg:col-span-3 bg-[#F7FBF9]/40 rounded-xl shadow-md border border-gray-100 p-8">
            <h2 class="text-[#005288] text-sm font-extrabold mb-4 uppercase">Current Address</h2>
            {!! $renderFields([
                'House #:' => $student->curr_house_number??'—', 'Street:' => $student->curr_street??'—',
                'Barangay:' => $student->curr_barangay??'—', 'City:' => $student->curr_city??'—',
                'Province:' => $student->curr_province??'—', 'Zip:' => $student->curr_zip_code??'—'
            ], 'lg:grid-cols-6') !!}

            <div class="flex items-center gap-3 mt-8 mb-4">
                <h2 class="text-[#005288] text-sm font-extrabold uppercase">Permanent Address</h2>
                @if($student->is_perm_same_as_curr)
                    <span class="text-[9px] bg-[#f1f5fd] text-[#005288] px-2 py-0.5 rounded-full border border-[#00923F]/20 font-bold">SAME AS CURRENT</span>
                @endif
            </div>
            {!! $renderFields([
                'House #:' => $student->perm_house_number??'—', 'Street:' => $student->perm_street??'—',
                'Barangay:' => $student->perm_barangay??'—', 'City:' => $student->perm_city??'—',
                'Province:' => $student->perm_province??'—', 'Zip:' => $student->perm_zip_code??'—'
            ], 'lg:grid-cols-6') !!}
        </div>

        <div class="lg:col-span-3 bg-[#F7FBF9]/40 rounded-xl shadow-md border border-gray-100 p-7 space-y-8">
            @foreach(['Father\'s Name' => 'father', 'Mother\'s Maiden Name' => 'mother', 'Guardian\'s Name' => 'guardian'] as $title => $key)
                <div>
                    <h2 class="text-[#005288] text-sm font-extrabold mb-4 uppercase">{{ $title }}</h2>
                    {!! $renderFields([
                        'Last Name:' => $student->{$key.'_last_name'}??'—', 'First Name:' => $student->{$key.'_first_name'}??'—',
                        'Middle Name:' => $student->{$key.'_middle_name'}??'—', 'Contact:' => $student->{$key.'_contact_number'}??'—'
                    ], 'lg:grid-cols-4') !!}
                </div>
            @endforeach
        </div>

        @if(!empty($dynamicDetails))
            <div class="lg:col-span-3 bg-[#F7FBF9]/40 rounded-xl shadow-md border border-gray-100 p-7">
                <h2 class="text-[#005288] text-sm font-extrabold mb-4 uppercase">Additional Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-6">
                    @foreach($dynamicDetails as $question => $answer)
                        @php
                            $answerText = '—';
                            if (is_array($answer)) {
                                $answerText = json_encode($answer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            } elseif (!empty($answer) || $answer === '0' || $answer === 0) {
                                $answerText = $answer;
                            }
                        @endphp
                        <div class="relative border-b-2 border-gray-200 pb-1 group hover:border-[#005288] transition-colors duration-200">
                            <label class="block text-[10px] font-bold text-gray-400 mb-1">{{ $question }}</label>
                            <p class="text-[13px] uppercase text-[#003918] min-h-6">{{ $answerText }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $status = trim($details['Academic Status'] ?? '');
            $isSpecialStatus = str_contains(strtolower($status), 'feree') || str_contains(strtolower($status), 'balik');
        @endphp

        @if($isSpecialStatus)
            <div class="lg:col-span-3 bg-[#F7FBF9]/40 rounded-xl shadow-md border border-gray-100 p-7">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-[#005288] text-sm font-extrabold uppercase">Transferee / Balik-Aral Information</h2>
                </div>
                {!! $renderFields([
                    'Last School Year Completed:' => $details['Last School Year Completed'] ?? '—',
                    'Last Grade Level Completed:' => $details['Last Grade Level Completed'] ?? '—',
                    'Last School Attended:'       => $details['Last School Attended'] ?? '—',
                    'School ID:'                  => $details['School ID'] ?? '—',
                ], 'lg:grid-cols-4') !!}
            </div>
        @endif
    </div>

    <div class="flex justify-end pt-6 pb-10">
        <button type="button" class="inline-flex items-center bg-[#00923F] hover:bg-[#007a34] text-white font-semibold px-8 py-3 rounded-lg shadow-sm transition focus:ring-2 focus:ring-offset-2 focus:ring-[#00923F] outline-none">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            Save Changes
        </button>
    </div>

</div>

{{-- Enhanced script with better error handling and debugging --}}
<script>
    const SERVICE_URL = 'http://127.0.0.1:5002';
    
    async function triggerEnrollmentFormFiller() {
        console.log('%c[DEBUG] Button clicked - Starting enrollment process', 'color: blue; font-weight: bold;');
        
        const btn = document.getElementById('useProfileBtn');
        const btnText = document.getElementById('useProfileBtnText');
        const statusDiv = document.getElementById('enrollmentStatus');
        const statusText = document.getElementById('enrollmentStatusText');
        const confirmBtn = document.getElementById('confirmEnrollmentBtn');
        const sectionSelect = document.getElementById('sectionSelect');
        const section = sectionSelect.value;
        
        // Validate section selection
        if (!section) {
            console.warn('[VALIDATION] No section selected');
            statusDiv.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border', 'border-green-200');
            statusDiv.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
            statusText.innerHTML = '<strong>Error:</strong> Please select a section from the dropdown above.';
            return;
        }
        
        console.log(`[INFO] Section selected: ${section}`);
        localStorage.setItem('serbiskoSelectedSection', section);
        
        // Disable button and show loading state
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btnText.textContent = 'Checking service...';
        
        // Step 1: Check if service is running
        console.log(`[NETWORK] Checking service at ${SERVICE_URL}/status`);
        try {
            const statusResp = await fetch(`${SERVICE_URL}/status`, {
                method: 'GET',
                signal: AbortSignal.timeout(5000) // 5 second timeout
            });
            if (!statusResp.ok) {
                throw new Error(`HTTP ${statusResp.status}`);
            }
            const statusData = await statusResp.json();
            console.log('[SERVICE] Service is online:', statusData);
        } catch (err) {
            console.error('%c[ERROR] Service unavailable', 'color: red; font-weight: bold;', err.message);
            statusDiv.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border', 'border-green-200');
            statusDiv.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
            statusText.innerHTML = `
                <strong>⚠️ Service Error:</strong> Cannot connect to enrollment service.<br>
                <small style="display: block; margin-top: 8px;">
                  Please ensure:<br>
                  • The enrollment filler service is running on port 5002<br>
                  • Run: <code style="background: #f0f0f0; padding: 2px 4px;">python3 enrollment_form_filler.py</code> in the python_services folder
                </small>
            `;
            resetButton(btn, btnText);
            return;
        }
        
        // Step 2: Prepare student data
        console.log('[DATA] Preparing student data...');
        const studentData = {
            lrn: '{{ $student->lrn }}',
            first_name: '{{ $student->first_name }}',
            last_name: '{{ $student->last_name }}',
            middle_name: '{{ $student->middle_name ?? "" }}',
            extension_name: '{{ $student->extension_name ?? "" }}',
            birthday: '{{ $student->birthday ?? "" }}',
            sex: '{{ $student->sex ?? "" }}',
            mother_tongue: '{{ $student->mother_tongue ?? "" }}',
            place_of_birth: '{{ $student->place_of_birth ?? "" }}',
            contact_number: '{{ $student->contact_number ?? "" }}',
            curr_house_number: '{{ $student->curr_house_number ?? "" }}',
            curr_street: '{{ $student->curr_street ?? "" }}',
            curr_barangay: '{{ $student->curr_barangay ?? "" }}',
            curr_city: '{{ $student->curr_city ?? "" }}',
            curr_province: '{{ $student->curr_province ?? "" }}',
            curr_zip_code: '{{ $student->curr_zip_code ?? "" }}',
            grade_level: '{{ $finalGrade ?? "" }}',
            track: '{{ $finalTrack ?? "" }}',
            cluster: '{{ $finalCluster ?? "" }}',
            academic_status: '{{ $finalStatus ?? "" }}',
            section: section
        };
        console.log('[DATA] Student:', studentData.first_name, studentData.last_name, 'LRN:', studentData.lrn);
        
        // Step 3: Send request to start enrollment automation
        btnText.textContent = 'Starting automation...';
        console.log(`[NETWORK] Sending POST to ${SERVICE_URL}/fill-enrollment`);
        try {
            const response = await fetch(`${SERVICE_URL}/fill-enrollment`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(studentData),
                signal: AbortSignal.timeout(10000) // 10 second timeout
            });
            
            console.log(`[RESPONSE] Got status ${response.status} from server`);
            const result = await response.json();
            console.log('[RESPONSE] Server response:', result);
            
            if (response.ok && result.status === 'started') {
                console.log('%c[SUCCESS] Automation started successfully!', 'color: green; font-weight: bold;');
                statusDiv.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border', 'border-red-200');
                statusDiv.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
                statusText.innerHTML = `
                    <strong>✓ Success!</strong> Form automation is running.<br>
                    <small style="display: block; margin-top: 8px;">
                      A new Chrome window should open automatically. The form will be filled with ${studentData.first_name}'s information.<br>
                      Once you verify everything is correct, click <strong>Confirm Enrollment Completed</strong>.
                    </small>
                `;
                btnText.textContent = 'Form Filler Active';
                confirmBtn.classList.remove('hidden');
            } else {
                console.error('[ERROR] Server returned failure:', result);
                throw new Error(result.message || 'Server returned an error');
            }
        } catch (error) {
            console.error('%c[ERROR] Failed to start automation', 'color: red; font-weight: bold;', error);
            statusDiv.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border', 'border-green-200');
            statusDiv.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
            
            let errorMsg = error.message;
            if (error.name === 'AbortError') {
                errorMsg = 'Request timeout - service took too long to respond';
            } else if (error instanceof TypeError && error.message.includes('Failed to fetch')) {
                errorMsg = 'Network error - cannot connect to service (CORS or connection issue)';
            }
            
            statusText.innerHTML = `<strong>⚠️ Error:</strong> ${errorMsg}<br><small style="display: block; margin-top: 8px;">Check browser console (F12) for more details.</small>`;
            resetButton(btn, btnText);
        }
    }
    
    function resetButton(btn, btnText) {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
        btnText.textContent = 'Use Profile';
    }

    async function confirmEnrollment() {
        console.log('[DEBUG] Confirming enrollment...');
        const confirmBtn = document.getElementById('confirmEnrollmentBtn');
        const statusDiv = document.getElementById('enrollmentStatus');
        const statusText = document.getElementById('enrollmentStatusText');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Confirming...';

        try {
            // Close the LIS session
            console.log('[NETWORK] Sending confirm request to service');
            await fetch(`${SERVICE_URL}/confirm-enrollment`, {method: 'POST'});
            
            // Update database
            console.log('[NETWORK] Updating database');
            await fetch('{{ url("/admin/students/profile/" . $student->lrn . "/confirm-enrollment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            console.log('%c[SUCCESS] Enrollment confirmed!', 'color: green; font-weight: bold;');
            statusDiv.classList.remove('bg-red-50');
            statusDiv.classList.add('bg-green-50', 'text-green-700');
            statusText.innerHTML = `<strong>✓ Success!</strong> Enrollment has been confirmed and saved.`;
            confirmBtn.classList.add('hidden');
        } catch (error) {
            console.error('[ERROR] Confirmation failed:', error);
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Enrollment Completed';
            statusText.innerHTML = `<strong>⚠️ Error confirming enrollment:</strong> ${error.message}`;
        }
    }
    
    // Log that scripts are loaded
    console.log('%c[READY] Enrollment form controller loaded and ready', 'color: green; font-weight: bold;');

    // Restore section on load
    window.onload = () => {
        const saved = localStorage.getItem('serbiskoSelectedSection');
        if (saved) document.getElementById('sectionSelect').value = saved;
    };
</script>
@endsection
