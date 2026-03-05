<div class="grid grid-cols-1 md:grid-cols-2 gap-6 justify-items-center bg-[#F7FBF9]/50 rounded-2xl shadow-lg border-t-8 border-[#1a8a44] p-4">
    
    <div class="flex flex-col h-full">
        <h2 class="text-xl font-black text-[#003918] uppercase tracking-tighter mb-8 items-center md:text-center">
            Enrollment Progress Overview
        </h2>
        
        <div class="space-y-5">
            <div class="flex items-center gap-4">
                <span class="w-32 text-gray-500 font-medium text-[12px] text-right leading-tight">Total Registrations</span>
                <div class="flex-1 flex items-center gap-3">
                    <div class="bg-[#048F81] h-8 rounded-md" style="width: 100%"></div>
                    <span class="text-gray-500 font-bold shrink-0 text-[12px]">{{ number_format($totalRegistrations) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="w-32 text-gray-500 font-medium text-[12px] text-right leading-tight">Document Verified</span>
                <div class="flex-1 flex items-center gap-3">
                    <div class="bg-[#00923F] h-8 rounded-md" style="width: {{ $percVerified }}%"></div>
                    <span class="text-[#00923F] font-bold shrink-0 text-[12px]">{{ number_format($totalSubmissions) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="w-32 text-gray-500 font-medium text-[12px] text-right leading-tight">Officially Enrolled</span>
                <div class="flex-1 flex items-center gap-3">
                    <div class="bg-[#00568d] h-8 rounded-md" style="width: {{ $percEnrolled }}%"></div>
                    <span class="text-gray-500 font-bold shrink-0 text-[12px]">{{ number_format($totalEnrolled) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col items-center">
        <h2 class="text-xl font-black text-[#003918] uppercase tracking-tighter mb-4 text-center">
            Student Elective Preference
        </h2>

        <div class="relative w-full max-w-[150px] h-[150px]">
            <canvas id="electiveChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('electiveChart').getContext('2d');
        
        // Data passed from the loop above
        const counts = [
            {{ $electiveCounts['STEM'] ?? 0 }}, 
            {{ $electiveCounts['ASSH'] ?? 0 }}, 
            {{ $electiveCounts['BE'] ?? 0 }}, 
            {{ $electiveCounts['TechPro'] ?? 0 }}
        ];

        const total = counts.reduce((a, b) => a + b, 0);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['STEM', 'ASSH', 'BE', 'TechPro'],
                datasets: [{
                    data: counts,
                    backgroundColor: ['#00568d', '#00897b', '#1a8a44', '#facc15'],
                    // 1. FILL THE GAP: Remove border and spacing
                    borderWidth: 0, 
                    spacing: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // 2. SMALLER HOLE: Lower percentage = thicker ring/smaller hole
                cutout: '50%', 
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                // 3. INTERNAL PADDING: Adds a little "breathing room" inside the canvas
                layout: {
                    padding: 10
                }
            }
        });
    });
</script>