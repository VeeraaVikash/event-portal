<?php
$page_title = 'Coordinator Dashboard - SRM Event Connect';
$body_class = 'bg-gray-50 dark:bg-gray-900 flex flex-col min-h-screen text-gray-900 dark:text-gray-100 transition-colors duration-300';
require 'partials/head.php';
require 'partials/nav.php';
?>
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<!-- Main Content -->
<main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-gray-100 transition-colors duration-300">Coordinator Dashboard</h1>
        <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
            <a href="api_export_proposals.php" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200">
                Export Department Data (CSV)
            </a>
            <form action="api_upload_preloaded.php" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <input type="file" name="csv_file" accept=".csv" required class="text-sm text-gray-600 dark:text-gray-300" />
                <button type="submit" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition-colors duration-200">
                    Upload Pre-Approved CSV
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <!-- Total Applied (Department) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-blue-200 dark:hover:border-blue-600">
            <div>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                    <?= htmlspecialchars($stats['total'] ?? 0) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Total Department Proposals</p>
            </div>
            <div class="text-blue-500 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-green-200 dark:hover:border-green-600 border-t-4 border-t-green-400 dark:border-t-green-500">
            <div>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                    <?= htmlspecialchars($stats['approved'] ?? 0) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Approved</p>
            </div>
            <div class="text-green-500 dark:text-green-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-red-200 dark:hover:border-red-600 border-t-4 border-t-red-400 dark:border-t-red-500">
            <div>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                    <?= htmlspecialchars($stats['rejected'] ?? 0) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Rejected</p>
            </div>
            <div class="text-red-500 dark:text-red-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Review -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-yellow-200 dark:hover:border-yellow-600 border-t-4 border-t-yellow-400 dark:border-t-yellow-500">
            <div>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                    <?= htmlspecialchars($stats['review'] ?? 0) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Review</p>
            </div>
            <div class="text-yellow-500 dark:text-yellow-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- HOD Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Proposals for the Year (Bar Chart) -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl p-6 transition-colors duration-300 h-96">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Proposals for the Year</h2>
            <div class="relative h-72 w-full">
                <canvas id="yearlyChart"></canvas>
            </div>
        </div>

        <!-- Proposal Statuses (Pie Chart) -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl p-6 transition-colors duration-300 h-96 flex flex-col">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 w-full">Proposal Statuses</h2>
            <div class="relative h-72 w-full flex justify-center pb-4">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Proposals List Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden transition-colors duration-300">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Department Proposals</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm cursor-default" id="proposalsTable">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="sortTable(0)">Proposal ID ↕</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="sortTable(1)">Event Title ↕</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="sortTable(2)">Convener ↕</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="sortTable(3)">Start Date ↕</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="sortTable(4)">Status ↕</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (empty($proposals)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No proposals found for your department.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($proposals as $prop): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer" onclick="openViewer(<?= $prop['id'] ?>, '<?= $prop['status'] ?>', <?= $prop['user_id'] ?? 0 ?>)">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-500 dark:text-gray-400">PRO-<?= sprintf('%04d', $prop['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-600 dark:text-blue-400"><?= htmlspecialchars($prop['title']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300"><?= htmlspecialchars($prop['convener_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300"><?= date('M d, Y', strtotime($prop['start_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $displayStatus = htmlspecialchars($prop['status']);
                                    $statusClass = 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
                                    $isCompleted = (strtotime($prop['end_date'] ?? date('Y-m-d')) < time()) && ($prop['status'] === 'Approved');
                                    
                                    if ($prop['status'] == 'Approved') {
                                        if ($isCompleted) {
                                            $displayStatus = 'Completed';
                                            $statusClass = 'bg-teal-100 dark:bg-teal-900/30 text-teal-800 dark:text-teal-400 font-bold border border-teal-200 dark:border-teal-800 shadow-sm';
                                        } else {
                                            $statusClass = 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400';
                                        }
                                    }
                                    if ($prop['status'] == 'Rejected') $statusClass = 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400';
                                    if ($prop['status'] == 'Pending') $statusClass = 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400';
                                    if ($prop['status'] == 'Rescheduled') $statusClass = 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400';
                                    if ($prop['status'] == 'Cancelled') $statusClass = 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 opacity-75';
                                    if ($prop['status'] == 'Review') $statusClass = 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400';
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>"><?= $displayStatus ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Approval Calendar -->
    <div
        class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden p-6 transition-colors duration-300 mt-8" style="height: 700px;">
        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Event Calendar</h2>

        <style>
            /* Inject subtle dark mode overrides for fullcalendar natively rendering light text */
            .dark {
                --fc-page-bg-color: transparent;
                --fc-neutral-bg-color: #374151;
                --fc-neutral-text-color: #9ca3af;
                --fc-border-color: #374151;
                --fc-button-text-color: #f3f4f6;
                --fc-button-bg-color: #374151;
                --fc-button-border-color: #4b5563;
                --fc-button-hover-bg-color: #4b5563;
                --fc-button-hover-border-color: #6b7280;
                --fc-button-active-bg-color: #1f2937;
                --fc-button-active-border-color: #111827;
                --fc-today-bg-color: rgba(255, 255, 255, 0.05);
            }

            .dark .fc {
                color: #e5e7eb;
            }

            .dark .fc a {
                color: #e5e7eb;
            }

            .dark .fc a:hover {
                color: #ffffff;
                text-decoration: none;
            }
        </style>

        <div id="calendar" class="h-96 text-gray-800 dark:text-gray-300"></div>
    </div>

    <div class="mt-8 bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden transition-colors duration-300">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Pre-Approved Events</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm cursor-default">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider">Sl. No.</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider">Month</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider">Activity</th>
                        <th scope="col" class="px-6 py-3 text-right font-medium tracking-wider">Budget (INR)</th>
                        <th scope="col" class="px-6 py-3 text-right font-medium tracking-wider">University Contribution (INR)</th>
                        <th scope="col" class="px-6 py-3 text-left font-medium tracking-wider">Convener</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (empty($preloaded_events)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No pre-approved events found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($preloaded_events as $pe): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($pe['sl_no']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300"><?= htmlspecialchars($pe['event_date']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300"><?= htmlspecialchars($pe['event_month']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300"><?= htmlspecialchars($pe['activity']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-600 dark:text-gray-300">₹<?= htmlspecialchars($pe['budget']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-600 dark:text-gray-300">₹<?= htmlspecialchars($pe['university_contribution']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300"><?= htmlspecialchars($pe['convener']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Yearly Bar Chart
        const ctxYear = document.getElementById('yearlyChart');
        if (ctxYear) {
            new Chart(ctxYear.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Proposals',
                        data: <?= $monthly_data_json ?? '[]' ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: '#9ca3af' }, grid: { color: 'rgba(156, 163, 175, 0.1)' } },
                        x: { ticks: { color: '#9ca3af' }, grid: { display: false } }
                    }
                }
            });
        }

        // Status Pie Chart
        const ctxStatus = document.getElementById('statusChart');
        if (ctxStatus) {
            new Chart(ctxStatus.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Approved', 'Rejected', 'Review', 'Pending', 'Rescheduled', 'Cancelled'],
                    datasets: [{
                        data: <?= $status_data_json ?? '[]' ?>,
                        backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#6b7280', '#6366f1', '#9ca3af'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#9ca3af', padding: 20 } }
                    }
                }
            });
        }

        // FullCalendar Initialization
        var calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            var approvedEvents = <?= json_encode($approved_events ?? []) ?>;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: approvedEvents,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'standard',
                height: 'auto',
                eventClick: function(info) {
                    if(info.event.extendedProps.proposal_id) {
                        openViewer(info.event.extendedProps.proposal_id, 'Approved');
                    }
                }
            });
            calendar.render();
        }
    });

    let currentSortColumn = -1;
    let asc = true;

    function sortTable(n) {
        const table = document.getElementById("proposalsTable");
        let rows, switching, i, x, y, shouldSwitch, switchcount = 0;
        switching = true;

        asc = (currentSortColumn === n) ? !asc : true;
        currentSortColumn = n;

        while (switching) {
            switching = false;
            rows = table.rows;

            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];

                if (x === undefined || y === undefined) break;

                let valX = x.innerHTML.toLowerCase().trim();
                let valY = y.innerHTML.toLowerCase().trim();

                if (n === 3) { // Date column shifted
                    valX = new Date(valX).getTime() || 0;
                    valY = new Date(valY).getTime() || 0;
                }

                if (asc) {
                    if (valX > valY) {
                        shouldSwitch = true;
                        break;
                    }
                } else {
                    if (valX < valY) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                switchcount++;
            }
        }
    }
</script>

<!-- Viewer Modal Overlay -->
<div id="viewerModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300 p-4 pt-16">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-6xl max-h-[85vh] flex flex-col relative overflow-hidden transition-all">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
            <h2 id="viewerTitle" class="text-xl font-bold text-gray-900 dark:text-gray-100">Proposal Details</h2>
            <div class="flex items-center gap-3">
                <a id="viewerViewReportBtn" target="_blank" href="#"
                    class="hidden px-4 py-1.5 bg-indigo-100 hover:bg-indigo-200 border border-indigo-300 text-indigo-800 text-sm font-bold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Report
                </a>
                <button onclick="closeViewer()" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition bg-white dark:bg-gray-700 p-1.5 rounded-full border border-gray-200 dark:border-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Body split -->
        <div class="flex-grow overflow-hidden w-full flex flex-col md:grid md:grid-cols-1 divide-y md:divide-y-0 divide-gray-200 dark:divide-gray-700">
            <div id="viewerContentCol" class="md:col-span-1 p-6 text-sm overflow-y-auto custom-scrollbar">
                <div id="viewerContent">Loading data...</div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeProposalId = null;
    function openViewer(id, status) {
        activeProposalId = id;
        document.getElementById('viewerModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('viewerContent').innerHTML = '<div class="text-center py-10 text-gray-500">Loading data...</div>';
        document.getElementById('viewerViewReportBtn').classList.add('hidden');
        document.getElementById('viewerViewReportBtn').href = "#";

        fetch('api_proposal_details.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('viewerContent').innerHTML = `<div class="p-4 bg-red-100 text-red-600 rounded">${data.error}</div>`;
                    return;
                }
                activeOwnerId = data.user_id;
                
                let isCompleted = data.end_date && (new Date(data.end_date) < new Date()) && data.status === 'Approved';
                if (isCompleted && data.report_path) {
                    const vrt = document.getElementById('viewerViewReportBtn');
                    if (vrt) {
                        vrt.href = data.report_path;
                        vrt.classList.remove('hidden');
                    }
                }

                let badgeHTML = '';
                if (data.status === 'Approved') badgeHTML = `<span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold dark:bg-green-900/50 dark:text-green-300 align-text-top shadow-sm border border-green-200 dark:border-green-800">Approved</span>`;
                else if (data.status === 'Rejected') badgeHTML = `<span class="ml-2 bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full font-bold dark:bg-red-900/50 dark:text-red-300 align-text-top shadow-sm border border-red-200 dark:border-red-800">Rejected</span>`;
                else if (data.status === 'Review') badgeHTML = `<span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full font-bold dark:bg-yellow-900/50 dark:text-yellow-300 align-text-top shadow-sm border border-yellow-200 dark:border-yellow-800">Review</span>`;
                else if (data.status === 'Rescheduled') badgeHTML = `<span class="ml-2 bg-purple-100 text-purple-800 text-xs px-2 py-0.5 rounded-full font-bold dark:bg-purple-900/50 dark:text-purple-300 align-text-top shadow-sm border border-purple-200 dark:border-purple-800">Rescheduled</span>`;
                else if (data.status === 'Cancelled') badgeHTML = `<span class="ml-2 bg-gray-100 text-gray-800 text-xs px-2 py-0.5 rounded-full font-bold dark:bg-gray-900/50 dark:text-gray-300 align-text-top shadow-sm border border-gray-200 dark:border-gray-800">Cancelled</span>`;
                else badgeHTML = `<span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full font-bold dark:bg-blue-900/50 dark:text-blue-300 align-text-top shadow-sm border border-blue-200 dark:border-blue-800">Pending</span>`;
                
                if (isCompleted) {
                    badgeHTML = `<span class="ml-2 bg-indigo-100 text-indigo-800 text-xs px-2 py-0.5 rounded-full font-bold dark:bg-indigo-900/50 dark:text-indigo-300 align-text-top shadow-sm border border-indigo-200 dark:border-indigo-800">Completed</span>`;
                }

                document.getElementById('viewerTitle').innerHTML = "View PRO-" + ("0000" + data.id).slice(-4) + " | " + data.title + badgeHTML;

                let fCat = data.category ? data.category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A';
                let sd = data.start_date ? new Date(data.start_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '';
                let ed = data.end_date ? new Date(data.end_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '';

                let pCats = data.participant_categories || 'N/A';
                if (data.student_categories) {
                    pCats += `<br/><span class="text-[10px] text-gray-500 dark:text-gray-400 block mt-1 border-t border-gray-200 dark:border-gray-600 pt-1 leading-snug whitespace-normal break-words" title="${data.student_categories}">${data.student_categories}</span>`;
                }

                let vhtml = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 border border-gray-200 dark:border-gray-700 rounded-md">
                                <span class="block text-[9px] uppercase tracking-wider text-gray-500 mb-1">Category</span>
                                <span class="font-bold text-xs text-gray-800 dark:text-gray-200 leading-tight">${fCat}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 border border-gray-200 dark:border-gray-700 rounded-md">
                                <span class="block text-[9px] uppercase tracking-wider text-gray-500 mb-1">Dates</span>
                                <span class="font-bold text-xs text-gray-800 dark:text-gray-200 leading-tight">${sd} <br/> to ${ed}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 border border-gray-200 dark:border-gray-700 rounded-md">
                                <span class="block text-[9px] uppercase tracking-wider text-gray-500 mb-1">Total Expected</span>
                                <span class="font-bold text-xs text-gray-800 dark:text-gray-200 leading-tight">${data.total_expected_participants}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 p-2 border border-gray-200 dark:border-gray-700 rounded-md whitespace-normal break-words">
                                <span class="block text-[9px] uppercase tracking-wider text-gray-500 mb-1">Particip. Targets</span>
                                <span class="font-bold text-xs text-gray-800 dark:text-gray-200 leading-tight block whitespace-normal break-words">${pCats}</span>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-800 p-3 border border-gray-200 dark:border-gray-700 rounded-md text-xs text-gray-800 dark:text-gray-200">
                            <span class="block text-[9px] uppercase tracking-wider text-gray-500 mb-1">Description</span>
                            <p>${data.description}</p>
                            ${data.past_events ? `<span class="block text-[9px] uppercase tracking-wider text-gray-500 mt-2 mb-1">Past Events</span><p>${data.past_events}</p>` : ''}
                            ${data.other_details ? `<span class="block text-[9px] uppercase tracking-wider text-gray-500 mt-2 mb-1">Other Details</span><p>${data.other_details}</p>` : ''}
                        </div>
                `;


                vhtml += `<h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-1 mt-4">Chief Guests</h4>`;
                if (data.guests && data.guests.length > 0) {
                    vhtml += `<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs mt-2">`;
                    data.guests.forEach((g) => {
                        vhtml += `<div class="bg-orange-50/50 dark:bg-orange-900/10 p-2 border border-orange-100 dark:border-orange-800 rounded-md">
                            <p class="font-bold text-orange-700 dark:text-orange-400">${g.name} - ${g.designation}</p>
                            <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">Address: ${g.address || 'N/A'} | PAN: ${g.pan_number || 'N/A'} | Ph: ${g.contact_number}</p>
                        </div>`;
                    });
                    vhtml += `</div>`;
                } else {
                    vhtml += `<p class="text-[10px] italic text-gray-400 mt-1">No Chief Guests recorded.</p>`;
                }

                vhtml += `<h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-1 mt-4">Travel & Accommodation</h4>`;
                if (data.travel && data.travel.length > 0) {
                    vhtml += `<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs mt-2">`;
                    data.travel.forEach((t) => {
                        if (t.hotel_name_address) {
                            vhtml += `<div class="bg-blue-50/50 dark:bg-blue-900/10 p-2 border border-blue-100 dark:border-blue-800 rounded-md">
                                <p class="font-bold text-blue-700 dark:text-blue-400">Hotel: ${t.hotel_name_address} (${t.accommodation_days || 0} days)</p>
                                <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">Arranged by: ${t.who_arranges || 'N/A'}</p>
                            </div>`;
                        } else if (t.mode) {
                            vhtml += `<div class="bg-blue-50/50 dark:bg-blue-900/10 p-2 border border-blue-100 dark:border-blue-800 rounded-md">
                                <p class="font-bold text-blue-700 dark:text-blue-400">Travel Mode: ${t.mode} (${t.number_of_trips || 0} trips)</p>
                                <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">From/To: ${t.travel_address || 'N/A'} | Provided by: ${t.who_provides || 'N/A'}</p>
                            </div>`;
                        }
                    });
                    vhtml += `</div>`;
                } else {
                    vhtml += `<p class="text-[10px] italic text-gray-400 mt-1">No Travel/Accommodation logistics recorded.</p>`;
                }

                let bTotal = 0;
                vhtml += `<h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-1 mt-4">Budget Details</h4>`;
                if (data.budgets && data.budgets.length > 0) {
                    vhtml += '<div class="overflow-x-auto"><table class="w-full text-[11px] text-left mt-2 mb-2 border border-gray-200 dark:border-gray-700"><thead class="bg-gray-100 dark:bg-gray-800"><tr><th class="p-1">Category</th><th class="p-1 text-center">Qty</th><th class="p-1 text-right">Cost</th></tr></thead><tbody>';
                    data.budgets.forEach((b) => {
                        vhtml += `<tr class="border-b border-gray-100 dark:border-gray-700"><td class="p-1 text-gray-800 dark:text-gray-200">${b.category}</td><td class="p-1 text-center text-gray-600 dark:text-gray-400">${b.quantity}</td><td class="p-1 text-right text-gray-800 dark:text-gray-200 font-medium">₹${parseFloat(b.total).toLocaleString('en-IN')}</td></tr>`;
                        bTotal += parseFloat(b.total) || 0;
                    });
                    vhtml += '</tbody></table></div>';
                } else {
                    vhtml += `<p class="text-[10px] italic text-gray-400 mt-1">No Expense targets proposed.</p>`;
                }

                vhtml += `<h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-1 mt-4">Sponsors</h4>`;
                if (data.sponsors && data.sponsors.length > 0) {
                    vhtml += `<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs mt-2">`;
                    data.sponsors.forEach((s) => {
                        vhtml += `<div class="bg-amber-50/50 dark:bg-amber-900/10 p-2 border border-amber-100 dark:border-amber-800 rounded-md">
                            <p class="font-bold text-amber-700 dark:text-amber-400">${s.sponsor_category} (₹${parseFloat(s.amount_contributed).toLocaleString('en-IN')})</p>
                            <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">Reward: ${s.reward_perk} | Mode: ${s.mode}</p>
                        </div>`;
                    });
                    vhtml += `</div>`;
                } else {
                    vhtml += `<p class="text-[10px] italic text-gray-400 mt-1">No third-party Sponsors listed.</p>`;
                }

                vhtml += `
                    <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md">
                        <span class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-3 border-b border-gray-200 dark:border-gray-700 pb-1">Initial Funding Sources</span>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-left text-xs pb-1">
                            <div><span class="text-[10px] uppercase font-semibold text-gray-400 block mb-1">University Fund</span> <span class="font-mono text-sm font-medium text-gray-800 dark:text-gray-100">₹${parseFloat(data.university_fund || 0).toLocaleString('en-IN')}</span></div>
                            <div><span class="text-[10px] uppercase font-semibold text-gray-400 block mb-1">Registration Fund</span> <span class="font-mono text-sm font-medium text-gray-800 dark:text-gray-100">₹${parseFloat(data.registration_fund || 0).toLocaleString('en-IN')}</span></div>
                            <div><span class="text-[10px] uppercase font-semibold text-gray-400 block mb-1">Sponsorship Fund</span> <span class="font-mono text-sm font-medium text-gray-800 dark:text-gray-100">₹${parseFloat(data.sponsorship_fund || 0).toLocaleString('en-IN')}</span></div>
                            <div><span class="text-[10px] uppercase font-semibold text-gray-400 block mb-1">Other Sources</span> <span class="font-mono text-sm font-medium text-gray-800 dark:text-gray-100">₹${parseFloat(data.other_sources || 0).toLocaleString('en-IN')}</span></div>
                            <div class="md:border-l border-gray-300 dark:border-gray-600 md:pl-3"><span class="text-[10px] uppercase font-bold text-blue-500 block mb-1">Total Requested Budget</span> <span class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">₹${bTotal.toLocaleString('en-IN')}</span></div>
                        </div>
                    </div>
                </div>`;

                document.getElementById('viewerContent').innerHTML = vhtml;
            });
    }

    function closeViewer() {
        document.getElementById('viewerModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Coordinator dashboard is intentionally read-only: no actions and no communication.
</script>
<?php require 'partials/footer.php'; ?>