<?php
$page_title = 'Convener Dashboard - SRM Event Connect';
$body_class = 'bg-gray-50 dark:bg-gray-900 flex flex-col min-h-screen text-gray-900 dark:text-gray-100 transition-colors duration-300';
require 'partials/head.php';
require 'partials/nav.php';
?>
<!-- FullCalendar integration -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<!-- Main Content -->
<main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-gray-100 transition-colors duration-300">
            Convener Dashboard
        </h1>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">

        <!-- Total Applied -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-blue-200 dark:hover:border-blue-600">
            <div>
                <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                    <?= htmlspecialchars($stats['total'] ?? 0) ?>
                </h3>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Total Applied</p>
            </div>
            <div class="text-blue-500 dark:text-blue-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        <!-- Approved -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-green-200 dark:hover:border-green-600 border-t-4 border-t-green-400 dark:border-t-green-500">
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
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-red-200 dark:hover:border-red-600 border-t-4 border-t-red-400 dark:border-t-red-500">
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
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex justify-between items-start transition hover:border-yellow-200 dark:hover:border-yellow-600 border-t-4 border-t-yellow-400 dark:border-t-yellow-500">
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

    <!-- Main Grid layout for Lists & Calendar -->
    <div class="space-y-8">

        <!-- Proposals List Table -->
        <div
            class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden transition-colors duration-300">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">My Proposals</h2>
                <a href="proposal.php"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-semibold rounded-md transition-colors shadow-sm cursor-pointer inline-block">
                    + New Proposal
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm cursor-default"
                    id="proposalsTable">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(0)">Proposal ID ↕</th>
                            <th scope="col"
                                class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(1)">Event Title ↕</th>
                            <th scope="col"
                                class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(2)">Category ↕</th>
                            <th scope="col"
                                class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(3)">Start Date ↕</th>
                            <th scope="col"
                                class="px-6 py-3 text-left font-medium tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(4)">Status ↕</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if (empty($proposals)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No proposals
                                    found. Start by creating a new one.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proposals as $prop): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer"
                                    onclick="openViewer(<?= $prop['id'] ?>, '<?= $prop['status'] ?>')">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-500 dark:text-gray-400">
                                        PRO-<?= sprintf('%04d', $prop['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-600 dark:text-blue-400">
                                        <?= htmlspecialchars($prop['title']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                        <?= ucwords(str_replace('_', ' ', htmlspecialchars($prop['category']))) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                        <?= date('M d, Y', strtotime($prop['start_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        // Dynamic status colors accommodating both modes
                                        $isCompleted = (strtotime($prop['end_date'] ?? date('Y-m-d')) < time()) && ($prop['status'] !== 'Cancelled');
                                        $displayStatus = htmlspecialchars($prop['status']);
                                        $statusClass = 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';

                                        if ($prop['status'] == 'Approved') {
                                            if ($isCompleted) {
                                                $displayStatus = 'Completed';
                                                $statusClass = 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400 font-bold';
                                            } else {
                                                $statusClass = 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400';
                                            }
                                        }
                                        if ($prop['status'] == 'Rejected')
                                            $statusClass = 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400';
                                        if ($prop['status'] == 'Pending')
                                            $statusClass = 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400';
                                        if ($prop['status'] == 'Review')
                                            $statusClass = 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400';
                                        if ($prop['status'] == 'Cancelled')
                                            $statusClass = 'bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-500 border border-red-200 dark:border-red-800';
                                        if ($prop['status'] == 'Rescheduled')
                                            $statusClass = 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 font-bold';
                                        ?>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                            <?= $displayStatus ?>
                                        </span>
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
            class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden p-6 transition-colors duration-300" style="height: 700px;">
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

    </div>

</main>

<script>
    // Sort logic for HTML Table
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

                if (n === 2) {
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

    // FullCalendar Initialization
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;
        
        var approvedEvents = <?= json_encode($approved_events ?? []) ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'title',
                right: 'today prev,next dayGridMonth,timeGridWeek'
            },
            height: 'auto',
            events: approvedEvents,
            eventColor: '#10B981', // Tailwind Emerald-500 natively mapped visually marking Approved tags globally
            eventClick: function (info) {
                // Prevent browser navigation dynamically bypassing routing issues 
                info.jsEvent.preventDefault();
                const props = info.event.extendedProps;
                if (props && props.proposal_id) {
                    openViewer(props.proposal_id, 'Approved');
                }
            }
        });
        calendar.render();
    });
    let activeProposalId = null;
    let activeProposalStatus = null;

    function openViewer(id, status) {
        activeProposalId = id;
        activeProposalStatus = status;

        document.getElementById('viewerModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Reset everything
        document.getElementById('viewerTitle').innerText = "Loading...";
        document.getElementById('viewerContent').innerHTML = "<div class='p-8 text-center text-gray-500'>Fetching reliable data...</div>";
        document.getElementById('viewerChatLog').innerHTML = "";
        document.getElementById('viewerChatInput').value = "";

        document.getElementById('viewerCommunicationCol').classList.remove('hidden');
        document.getElementById('viewerEditBtn').classList.add('hidden');

        document.getElementById('viewerCancelBtn').classList.add('hidden');
        document.getElementById('viewerRescheduleBtn').classList.add('hidden');

        // Allow editing only if Pending or Review
        if (status === 'Pending' || status === 'Review') {
            const ebtn = document.getElementById('viewerEditBtn');
            ebtn.href = 'proposal.php?id=' + id;
            ebtn.classList.remove('hidden');
        }

        document.getElementById('viewerGenerateReportBtn')?.classList.add('hidden');
        document.getElementById('viewerViewReportBtn')?.classList.add('hidden');

        // Setup Chat strictly on Review Status
        if (status === 'Review') {
            document.getElementById('viewerChatInput').disabled = false;
            document.getElementById('viewerChatSendBtn').disabled = false;
            document.getElementById('viewerChatInput').placeholder = "Type your message...";
        } else {
            document.getElementById('viewerChatInput').disabled = true;
            document.getElementById('viewerChatSendBtn').disabled = true;
            document.getElementById('viewerChatInput').placeholder = "Communication disabled.";
        }

        // Fetch Data
        fetch('api_proposal_details.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                let badgeHTML = '';
                let isCompleted = data.end_date && (new Date(data.end_date) < new Date()) && data.status === 'Approved';

                // Mount Action Matrix securely matching explicit state bounds recursively
                if (isCompleted) {
                    if (data.report_path) {
                        const vrt = document.getElementById('viewerViewReportBtn');
                        if (vrt) { vrt.href = data.report_path; vrt.classList.remove('hidden'); }
                    } else {
                        const grt = document.getElementById('viewerGenerateReportBtn');
                        if (grt) { grt.classList.remove('hidden'); window.generatorPayload = data; }
                    }
                } else {
                    if (data.status !== 'Cancelled') {
                        const cBtn = document.getElementById('viewerCancelBtn');
                        if (cBtn) { cBtn.classList.remove('hidden'); cBtn.onclick = () => openActionModal(id, 'cancel'); }
                    }
                    if (data.status === 'Approved') {
                        const rBtn = document.getElementById('viewerRescheduleBtn');
                        if (rBtn) { rBtn.classList.remove('hidden'); rBtn.onclick = () => openActionModal(id, 'reschedule'); }
                    }
                }

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

                // Render Messages
                const chatLog = document.getElementById('viewerChatLog');
                chatLog.innerHTML = "";
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(m => {
                        const isMe = m.sender_id == data.my_id;
                        const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                        let wrap = document.createElement('div');
                        wrap.className = isMe ? "flex justify-end" : "flex justify-start";

                        wrap.innerHTML = `
                            <div class="${isMe ? 'bg-blue-600 text-white rounded-bl-xl' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-br-xl'} p-3 rounded-t-xl shadow-sm max-w-[90%]">
                                ${!isMe ? `<p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 mb-1 tracking-wide">${m.full_name} (${m.role})</p>` : ''}
                                <p class="text-sm leading-snug break-words">${m.message}</p>
                                <p class="text-[10px] ${isMe ? 'text-blue-200' : 'text-gray-400'} text-right mt-1">${time}</p>
                            </div>
                        `;
                        chatLog.appendChild(wrap);
                    });
                    chatLog.scrollTop = chatLog.scrollHeight;
                } else {
                    chatLog.innerHTML = `<p class="text-xs text-gray-400 text-center italic mt-4">No communication logs recorded yet.</p>`;
                }
            });
    }

    function sendChatMessage() {
        if (activeProposalStatus !== 'Review') return;
        const msg = document.getElementById('viewerChatInput').value.trim();
        if (!msg) return;

        document.getElementById('viewerChatSendBtn').disabled = true;
        fetch('api_proposal_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ proposal_id: activeProposalId, message: msg })
        })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    openViewer(activeProposalId, activeProposalStatus);
                }
            });
    }

    let actionTargetId = null;
    let actionType = null;

    function openActionModal(id, action) {
        actionTargetId = id;
        actionType = action;
        document.getElementById('actionModalTitle').innerText = action === 'cancel' ? 'Cancel Proposal' : 'Reschedule Event';
        document.getElementById('actionRescheduleDates').style.display = action === 'reschedule' ? 'grid' : 'none';
        document.getElementById('actionReason').value = '';
        document.getElementById('actionModal').classList.remove('hidden');
    }

    function closeActionModal() {
        document.getElementById('actionModal').classList.add('hidden');
    }

    function confirmAction() {
        const reason = document.getElementById('actionReason').value.trim();
        const start = document.getElementById('actionNewStart').value;
        const end = document.getElementById('actionNewEnd').value;

        if (!reason) { alert("Please provide a valid reason."); return; }
        if (actionType === 'reschedule' && (!start || !end)) { alert("Please provide both new dates."); return; }

        const payload = { id: actionTargetId, action: actionType, reason: reason };
        if (actionType === 'reschedule') {
            payload.new_start = start;
            payload.new_end = end;
        }

        document.getElementById('actionConfirmBtn').disabled = true;
        fetch('api_proposal_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'dashboard.php?alert=Action%20Processed';
                } else {
                    alert("Error: " + data.message);
                    document.getElementById('actionConfirmBtn').disabled = false;
                }
            });
    }

    function closeViewer() {
        document.getElementById('viewerModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>

<!-- Viewer Modal Overlay -->
<div id="viewerModal"
    class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300 p-4 pt-16">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-6xl max-h-[85vh] flex flex-col relative overflow-hidden flex transition-all">

        <!-- Header -->
        <div
            class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
            <h2 id="viewerTitle" class="text-xl font-bold text-gray-900 dark:text-gray-100">Proposal Details</h2>
            <div class="flex items-center gap-3">
                <button id="viewerRescheduleBtn"
                    class="hidden px-4 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    Reschedule
                </button>
                <button id="viewerCancelBtn"
                    class="hidden px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    Cancel Event
                </button>
                <button id="viewerGenerateReportBtn" onclick="openReportModal()"
                    class="hidden px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Generate Report
                </button>
                <a id="viewerViewReportBtn" target="_blank" href="#"
                    class="hidden px-4 py-1.5 bg-indigo-100 hover:bg-indigo-200 border border-indigo-300 text-indigo-800 text-sm font-bold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Report
                </a>
                <a id="viewerEditBtn" href="#"
                    class="hidden px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-block">
                    Edit Proposal
                </a>
                <button onclick="closeViewer()"
                    class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition bg-white dark:bg-gray-700 p-1.5 rounded-full border border-gray-200 dark:border-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Body split -->
        <div
            class="flex-grow overflow-hidden w-full flex flex-col md:grid md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">

            <div id="viewerContentCol" class="md:col-span-2 p-6 text-sm overflow-y-auto custom-scrollbar">
                <div id="viewerContent">Loading data...</div>
            </div>

            <!-- Communication Sidebar -->
            <div id="viewerCommunicationCol"
                class="md:col-span-1 bg-yellow-50/50 dark:bg-yellow-900/10 flex flex-col relative h-[50vh] md:h-auto overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Communication Logs
                    </h3>
                </div>

                <!-- Chat View -->
                <div id="viewerChatLog"
                    class="flex-grow p-4 overflow-y-auto space-y-4 custom-scrollbar text-sm flex flex-col pb-6">
                    <!-- Javascript populates chat here via AJAX DB Ping -->
                </div>

                <!-- Chat Input Frame -->
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800 mt-auto">
                    <div class="flex items-center gap-2">
                        <input type="text" id="viewerChatInput" onkeypress="if(event.key === 'Enter') sendChatMessage()"
                            placeholder="Type your message..."
                            class="w-full text-sm rounded-full bg-gray-100 dark:bg-gray-700 border-none focus:ring-2 focus:ring-blue-500 px-4 py-2 text-gray-900 dark:text-gray-100 outline-none transition disabled:opacity-50">
                        <button type="button" onclick="sendChatMessage()" id="viewerChatSendBtn"
                            class="bg-blue-600 text-white rounded-full p-2.5 hover:bg-blue-700 shadow-sm transition transform hover:scale-105 disabled:opacity-50 disabled:hover:scale-100 flex-shrink-0">
                            <svg class="w-4 h-4 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="actionModal"
    class="fixed inset-0 z-[200] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-6">
        <h3 id="actionModalTitle" class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Action Proposal</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Please provide detailed reasoning required to formally
            process this change constraint.</p>

        <div id="actionRescheduleDates" class="grid grid-cols-2 gap-4 mb-4 hidden">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">New Start Date</label>
                <input type="date" id="actionNewStart"
                    class="w-full text-sm p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">New End Date</label>
                <input type="date" id="actionNewEnd"
                    class="w-full text-sm p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Detailed Reason *</label>
        <textarea id="actionReason" rows="3"
            class="w-full text-sm p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 outline-none focus:ring-2 focus:ring-blue-500 resize-none transition"
            placeholder="Explain the rationale behind this workflow shift..."></textarea>

        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeActionModal()"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-semibold bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Go
                Back</button>
            <button id="actionConfirmBtn" onclick="confirmAction()"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed">Confirm
                Change</button>
        </div>
    </div>
</div>

<script>
    const params = new URLSearchParams(window.location.search);
    if (params.has('alert')) {
        alert(params.get('alert'));
        window.history.replaceState(null, '', window.location.pathname);
    }

    // --- Report Generator Logic ---
    function openReportModal() {
        document.getElementById('reportErrorBox').classList.add('hidden');
        document.getElementById('reportImageUpload').value = '';
        document.getElementById('reportBillUpload').value = '';
        document.getElementById('reportModal').classList.remove('hidden');
    }

    function closeReportModal() {
        document.getElementById('reportModal').classList.add('hidden');
    }

    async function processReportGeneration() {
        console.log("=== processReportGeneration START ===");

        if (!window.generatorPayload) {
            console.error("ERROR: window.generatorPayload is null/undefined");
            return;
        }
        console.log("generatorPayload exists:", window.generatorPayload);

        const btn = document.getElementById('reportGeneratePerformBtn');
        btn.disabled = true;
        btn.innerText = "Building PDF Base...";
        console.log("Button disabled, text set to 'Building PDF Base...'");

        const images = document.getElementById('reportImageUpload').files;
        const bills = document.getElementById('reportBillUpload').files;

        // Helper: Converts ANY image (including SVG) to a safe JPEG base64 and gets dimensions
        const getSafeImageData = (file) => new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    // Cap resolution to avoid massive memory spikes while keeping A4 quality
                    const MAX_DIM = 2000;
                    let w = img.width;
                    let h = img.height;

                    if (w > MAX_DIM || h > MAX_DIM) {
                        const ratio = w / h;
                        if (w > h) { w = MAX_DIM; h = MAX_DIM / ratio; }
                        else { h = MAX_DIM; w = MAX_DIM * ratio; }
                    }

                    canvas.width = w || 800; // fallback width
                    canvas.height = h || 800; // fallback height
                    const ctx = canvas.getContext('2d');

                    // Fill white background (prevents transparent PNGs/SVGs turning black)
                    ctx.fillStyle = "#ffffff";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                    resolve({
                        base64: canvas.toDataURL('image/jpeg', 0.8),
                        width: canvas.width,
                        height: canvas.height
                    });
                };
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
        });

        const pData = window.generatorPayload;

        // Build HTML for text and tables ONLY. No images here.
        let htmlBlock = `
        <div style="font-family: Arial, sans-serif; font-size: 11px; padding: 20px; color: #333; margin: 0;">
            <div style="text-align: center; border-bottom: 2px solid #004289; padding-bottom: 10px; margin-bottom: 15px;">
                <h1 style="color: #004289; margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px;">SRM Institute of Science and Technology</h1>
                <h2 style="color: #444; margin: 3px 0 0; font-size: 14px;">Faculty of Engineering and Technology</h2>
                <h3 style="color: #666; margin: 3px 0 0; font-size: 12px;">Department of Computing Technologies</h3>
                <h4 style="color: #333; margin: 10px 0 0; font-size: 16px; text-decoration: underline;">EVENT REPORT</h4>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; width: 15%; border: 1px solid #ccc; background: #f9f9f9;">Event Title</td>
                    <td style="padding: 4px 6px; width: 35%; border: 1px solid #ccc;">${pData.title}</td>
                    <td style="padding: 4px 6px; font-weight: bold; width: 15%; border: 1px solid #ccc; background: #f9f9f9;">Reference ID</td>
                    <td style="padding: 4px 6px; width: 35%; border: 1px solid #ccc;">PRO-${String(pData.id).padStart(4, '0')}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Dates</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc;">${new Date(pData.start_date).toLocaleDateString()} to ${new Date(pData.end_date).toLocaleDateString()}</td>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Category</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc; text-transform: capitalize;">${pData.category.replace(/_/g, ' ')}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Participants</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc;" colspan="3"><b>Count:</b> ${pData.total_expected_participants} | <b>Target Audience:</b> ${pData.participant_categories}</td>
                </tr>
                <tr>
                    <td style="padding: 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Event Description</td>
                    <td style="padding: 6px; border: 1px solid #ccc; text-align: justify;" colspan="3">${pData.description}</td>
                </tr>
            </table>
        `;

        if (pData.guests && pData.guests.length > 0) {
            htmlBlock += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Chief Guests / Experts</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Name</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Designation & Address</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Phone</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Reason for Inviting</th>
            </tr>`;
            pData.guests.forEach(g => {
                htmlBlock += `<tr>
                <td style="border: 1px solid #ccc; padding: 4px;"><b>${g.name}</b></td>
                <td style="border: 1px solid #ccc; padding: 4px;">${g.designation}<br/>${g.address}</td>
                <td style="border: 1px solid #ccc; padding: 4px;">${g.contact_number}</td>
                <td style="border: 1px solid #ccc; padding: 4px;">${g.reason}</td>
            </tr>`;
            });
            htmlBlock += `</table>`;
        }

        if (pData.travel && pData.travel.length > 0) {
            htmlBlock += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Travel & Accommodation</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Logistics Type</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Details</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Provided By</th>
            </tr>`;
            pData.travel.forEach(t => {
                if (t.hotel_name_address) {
                    htmlBlock += `<tr>
                    <td style="border: 1px solid #ccc; padding: 4px;"><b>Accommodation</b> (${t.accommodation_days} Days)</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">Hotel: ${t.hotel_name_address}</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">${t.who_arranges}</td>
                </tr>`;
                } else if (t.mode) {
                    htmlBlock += `<tr>
                    <td style="border: 1px solid #ccc; padding: 4px;"><b>Travel</b> (${t.mode}) - ${t.number_of_trips} Trips</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">Locations: ${t.travel_address}</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">${t.who_provides}</td>
                </tr>`;
                }
            });
            htmlBlock += `</table>`;
        }

        if (pData.budgets && pData.budgets.length > 0) {
            let bTotal = 0;
            htmlBlock += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Proposed Budget Breakdown</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Category</th>
                <th style="border: 1px solid #ccc; padding: 4px; text-align: center; background: #eee;">Type</th>
                <th style="border: 1px solid #ccc; padding: 4px; text-align: center; background: #eee;">Qty</th>
                <th style="border: 1px solid #ccc; padding: 4px; text-align: right; background: #eee;">Unit Cost (₹)</th>
                <th style="border: 1px solid #ccc; padding: 4px; text-align: right; background: #eee;">Total (₹)</th>
            </tr>`;
            pData.budgets.forEach(b => {
                bTotal += parseFloat(b.total || 0);
                htmlBlock += `<tr>
                <td style="border: 1px solid #ccc; padding: 4px;"><b>${b.category}</b></td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${b.type}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${b.quantity}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: right;">${parseFloat(b.cost_per_unit).toLocaleString('en-IN')}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: right; font-weight: bold;">${parseFloat(b.total).toLocaleString('en-IN')}</td>
            </tr>`;
            });
            htmlBlock += `<tr>
            <td colspan="4" style="border: 1px solid #ccc; padding: 4px; text-align: right; background: #f0f8ff;"><b>GRAND TOTAL</b></td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: right; background: #f0f8ff; font-weight: bold; color: #004289;">₹${bTotal.toLocaleString('en-IN')}</td>
        </tr></table>`;
        }

        htmlBlock += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Funding Sources</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
        <tr>
            <td style="border: 1px solid #ccc; padding: 4px; background: #f9f9f9;"><b>University Fund</b></td>
            <td style="border: 1px solid #ccc; padding: 4px;">₹${parseFloat(pData.university_fund || 0).toLocaleString('en-IN')}</td>
            <td style="border: 1px solid #ccc; padding: 4px; background: #f9f9f9;"><b>Registration Fees</b></td>
            <td style="border: 1px solid #ccc; padding: 4px;">₹${parseFloat(pData.registration_fund || 0).toLocaleString('en-IN')}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ccc; padding: 4px; background: #f9f9f9;"><b>Sponsorship Fund</b></td>
            <td style="border: 1px solid #ccc; padding: 4px;">₹${parseFloat(pData.sponsorship_fund || 0).toLocaleString('en-IN')}</td>
            <td style="border: 1px solid #ccc; padding: 4px; background: #f9f9f9;"><b>Other Sources</b></td>
            <td style="border: 1px solid #ccc; padding: 4px;">₹${parseFloat(pData.other_sources || 0).toLocaleString('en-IN')}</td>
        </tr>
        </table>
        
        <div style="margin-top: 50px; display: flex; justify-content: space-between; page-break-inside: avoid; padding: 0 40px;">
            <div style="text-align: center; width: 220px;">
                <hr style="border: 0; border-bottom: 1.5px solid #000; margin-bottom: 10px;" />
                <span style="font-weight: bold; font-size: 13px;">Convener Signature</span>
            </div>
            <div style="text-align: center; width: 220px;">
                <hr style="border: 0; border-bottom: 1.5px solid #000; margin-bottom: 10px;" />
                <span style="font-weight: bold; font-size: 13px;">HOD Signature</span>
            </div>
        </div>
        </div>`;

        const opt = {
            margin: 0.3,
            filename: 'report.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0 }, // Set to 2 for sharper text
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        };

        try {
            // Step 1: Render the text/tables via html2pdf, but intercept before saving
            let worker = html2pdf().set(opt).from(htmlBlock).toPdf();

            // Step 2: Hook into the jsPDF instance and manually draw images on new pages
            worker = worker.get('pdf').then(async function (pdf) {
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const margin = 0.3;
                const maxImgWidth = pageWidth - (margin * 2);
                const maxImgHeight = pageHeight - (margin * 2) - 0.5; // Leave space for headers

                const addImagesToPDF = async (fileList, titlePrefix) => {
                    for (let i = 0; i < fileList.length; i++) {
                        btn.innerText = `Attaching ${titlePrefix} ${i + 1}/${fileList.length}...`;
                        console.log(`Processing ${titlePrefix} ${i + 1}: ${fileList[i].name}`);

                        try {
                            const imgData = await getSafeImageData(fileList[i]);

                            // Calculate aspect ratio to fit the page natively
                            const ratio = imgData.width / imgData.height;
                            let renderWidth = maxImgWidth;
                            let renderHeight = maxImgWidth / ratio;

                            if (renderHeight > maxImgHeight) {
                                renderHeight = maxImgHeight;
                                renderWidth = maxImgHeight * ratio;
                            }

                            // Center horizontally
                            const xPos = (pageWidth - renderWidth) / 2;

                            // Create a new page and inject
                            pdf.addPage();
                            pdf.setFontSize(11);
                            pdf.setTextColor(80);
                            pdf.text(`${titlePrefix} ${i + 1} of ${fileList.length}`, margin, margin + 0.2);

                            // Natively stamp the image
                            pdf.addImage(imgData.base64, 'JPEG', xPos, margin + 0.5, renderWidth, renderHeight);
                        } catch (err) {
                            console.error(`Failed to process image ${fileList[i].name}`, err);
                        }
                    }
                };

                if (bills.length > 0) await addImagesToPDF(bills, "Bill / Receipt");
                if (images.length > 0) await addImagesToPDF(images, "Event Photograph");

                return pdf;
            });

            // Step 3: Finalize and send to server
            worker.output('blob').then(function (pdfBlob) {
                console.log("Final combined PDF blob size:", pdfBlob.size, "bytes");

                const formData = new FormData();
                formData.append('proposal_id', pData.id);
                formData.append('report_pdf', pdfBlob, 'report.pdf');

                btn.innerText = "Saving securely natively...";
                console.log("Sending to server...");

                fetch('api_save_report.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(res => {
                        console.log("Server response:", res);
                        if (res.status === 'success') {
                            window.location.href = 'dashboard.php?alert=Report%20Generated%20and%20Linked%20Successfully';
                        } else {
                            document.getElementById('reportErrorBox').innerText = res.message;
                            document.getElementById('reportErrorBox').classList.remove('hidden');
                            btn.innerText = "Generate Report";
                            btn.disabled = false;
                        }
                    })
                    .catch(err => {
                        console.error("Fetch error:", err);
                        document.getElementById('reportErrorBox').innerText = "Network transmission fault storing PDF Blob natively.";
                        document.getElementById('reportErrorBox').classList.remove('hidden');
                        btn.innerText = "Generate Report";
                        btn.disabled = false;
                    });
            });

        } catch (err) {
            console.error("PDF engine ERROR:", err);
            document.getElementById('reportErrorBox').innerText = "PDF rendering failed: " + err.message;
            document.getElementById('reportErrorBox').classList.remove('hidden');
            btn.innerText = "Generate Report";
            btn.disabled = false;
        }

        console.log("=== processReportGeneration END ===");
    }

</script>

<!-- Report Generator Modal Overlay -->
<div id="reportModal"
    class="fixed inset-0 z-[200] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg p-6 relative">
        <h3
            class="text-xl font-bold text-gray-800 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-3 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Generate Official Post-Event Report
        </h3>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">Upload photographic proof and physical expense bills
            verifying completion. Note that once generated, it cannot be edited
        </p>

        <div id="reportErrorBox"
            class="hidden mb-4 p-3 bg-red-50 text-red-700 text-xs rounded border border-red-200 font-bold"></div>

        <div class="space-y-4 mb-6">
            <div class="bg-blue-50/50 dark:bg-blue-900/10 p-3 rounded-md border border-blue-100 dark:border-blue-800">
                <label class="block text-xs font-bold text-blue-800 dark:text-blue-300 mb-2">Attach Event Photographs
                    (JPG/PNG)</label>
                <input type="file" id="reportImageUpload" multiple accept="image/*"
                    class="w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400 cursor-pointer">
            </div>

            <div
                class="bg-amber-50/50 dark:bg-amber-900/10 p-3 rounded-md border border-amber-100 dark:border-amber-800">
                <label class="block text-xs font-bold text-amber-800 dark:text-amber-300 mb-2">Attach Scanned Bills &
                    Expense Receipts</label>
                <input type="file" id="reportBillUpload" multiple accept="image/*"
                    class="w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-amber-900/30 dark:file:text-amber-400 cursor-pointer">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closeReportModal()"
                class="px-4 py-2 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
            <button id="reportGeneratePerformBtn" onclick="processReportGeneration()"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition disabled:opacity-50 flex items-center gap-2">
                Generate Report
            </button>
        </div>
    </div>
</div>

<!-- Invisible HTML2PDF Container -->
<div id="pdfTemplateFrame" style="position: absolute; top: -9999px; left: -9999px; width: 800px; background: white;">
</div>

<!-- Modals placeholder -->
<?php require 'partials/footer.php'; ?>