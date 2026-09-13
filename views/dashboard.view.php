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
        document.getElementById('viewerDraftBtn')?.classList.add('hidden');

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

                // Everything the report workspace and the draft printer read.
                window.generatorPayload = data;

                // Before the event: an approved proposal can be printed for the
                // HOD. That draft is never uploaded, so there is no server gate
                // behind this button - can_draft is the whole rule.
                const draftBtn = document.getElementById('viewerDraftBtn');
                if (draftBtn && data.can_draft) { draftBtn.classList.remove('hidden'); }

                // Mount Action Matrix securely matching explicit state bounds recursively
                if (isCompleted) {
                    if (data.report_path) {
                        const vrt = document.getElementById('viewerViewReportBtn');
                        if (vrt) { vrt.href = 'download_report.php?id=' + id; vrt.classList.remove('hidden'); }
                    } else if (data.can_attach) {
                        const grt = document.getElementById('viewerGenerateReportBtn');
                        if (grt) { grt.classList.remove('hidden'); }
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

        // Send the status this page was showing so the server can reject the
        // action if the proposal has since moved on.
        const payload = { id: actionTargetId, action: actionType, reason: reason };
        if (activeProposalStatus) {
            payload.expected_status = activeProposalStatus;
        }
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
                <button id="viewerDraftBtn" onclick="printDraftReport(this)"
                    class="hidden px-4 py-1.5 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5"
                    title="Printable copy for the HOD. Not saved on the server.">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Draft
                </button>
                <button id="viewerGenerateReportBtn" onclick="openReportModal()"
                    class="hidden px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Create Event Report
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

    // --- Event Report ---
    //
    // One builder, two modes:
    //
    //   draft  the event has not happened yet. The convener prints the approved
    //          proposal for the HOD. Nothing is uploaded and nothing is stored.
    //   final  the event is over. Photographs are embedded as pages, every
    //          attachment is listed in an annexure, and the finished PDF is
    //          stored on the server as the official report.
    //
    // A PDF cannot play a video and this generator cannot merge foreign
    // documents, so bills and attendance proof stay on the server: the annexure
    // carries a QR code and a link to download_media.php for each one.

    const reportState = { media: [], busy: false };

    const REPORT_KINDS = {
        photo:    { label: 'Photograph', plural: 'Photographs', limit: '10 MB each' },
        document: { label: 'Document',   plural: 'Documents',   limit: '25 MB each' },
        // Video was accepted briefly. Rows from that window still list and still
        // open, so the label has to survive even though nothing new can be added.
        video:    { label: 'Video',      plural: 'Videos',      limit: 'no longer accepted' }
    };

    /** The four attendance boxes, as numbers. Blank counts as none. */
    const ATTENDANCE_FIELDS = {
        att_internal_students: 'Internal students',
        att_external_students: 'External students',
        att_internal_faculty:  'Internal faculty',
        att_external_faculty:  'External faculty'
    };

    function reportAttendance() {
        const out = { total: 0, any: false };
        Object.keys(ATTENDANCE_FIELDS).forEach(field => {
            const el = document.getElementById(field);
            const raw = el ? el.value.trim() : '';
            const n = raw === '' ? 0 : parseInt(raw, 10);
            out[field] = (isNaN(n) || n < 0) ? 0 : n;
            if (raw !== '') out.any = true;
            out.total += out[field];
        });
        return out;
    }

    /** Keeps the running total under the attendance boxes honest. */
    function updateAttendanceTotal() {
        const a = reportAttendance();
        const el = document.getElementById('attendanceTotal');
        if (el) el.innerText = a.any ? a.total.toLocaleString('en-IN') : '—';
    }

    /** The convener's account of the event, and how long it is. */
    function reportSummary() {
        const el = document.getElementById('reportSummary');
        return el ? el.value.trim() : '';
    }

    function summaryWordCount() {
        const text = reportSummary();
        return text === '' ? 0 : text.split(/\s+/).length;
    }

    function updateSummaryCount() {
        const el = document.getElementById('summaryCount');
        if (!el) return;
        const words = summaryWordCount();
        const min = (window.generatorPayload && window.generatorPayload.summary_min_words) || 50;
        el.innerText = words + ' / ' + min + ' words';
        el.classList.toggle('text-emerald-600', words >= min);
        el.classList.toggle('dark:text-emerald-400', words >= min);
        el.classList.toggle('text-gray-500', words < min);
    }

    /** Escapes a value before it goes into the generated HTML. */
    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /** A link that still works when it is scanned from a printed page. */
    function absoluteUrl(path) {
        return new URL(path, window.location.href).href;
    }

    function openReportModal() {
        const data = window.generatorPayload;
        if (!data) return;

        reportState.media = data.media || [];
        Object.keys(ATTENDANCE_FIELDS).forEach(field => {
            const el = document.getElementById(field);
            if (el) el.value = data[field] ?? '';
        });
        updateAttendanceTotal();

        const summaryEl = document.getElementById('reportSummary');
        if (summaryEl) summaryEl.value = data.report_summary || '';
        updateSummaryCount();

        const allowance = document.getElementById('photoAllowance');
        if (allowance) {
            const days = data.event_days || 1;
            allowance.innerText = 'Up to ' + data.photo_allowance + ' for this event - 2 per day over '
                + days + (days === 1 ? ' day' : ' days') + '. ' + (data.photos_used || 0) + ' attached so far.';
        }
        document.getElementById('reportErrorBox').classList.add('hidden');
        document.getElementById('reportProgress').classList.add('hidden');
        document.getElementById('reportModalTitle').innerText =
            'Event Report - PRO-' + String(data.id).padStart(4, '0');
        renderMediaList();
        document.getElementById('reportModal').classList.remove('hidden');
    }

    function closeReportModal() {
        if (reportState.busy) return;
        document.getElementById('reportModal').classList.add('hidden');
    }

    function showReportError(message) {
        const box = document.getElementById('reportErrorBox');
        box.innerText = message;
        box.classList.remove('hidden');
    }

    function setReportBusy(busy, message) {
        reportState.busy = busy;
        const progress = document.getElementById('reportProgress');
        progress.innerText = message || '';
        progress.classList.toggle('hidden', !busy);
        document.querySelectorAll('.report-action').forEach(el => { el.disabled = busy; });
    }

    /** Redraws the attached-files list from reportState.media. */
    function renderMediaList() {
        const box = document.getElementById('reportMediaList');
        const media = reportState.media || [];

        if (media.length === 0) {
            box.innerHTML = '<p class="text-xs text-gray-500 dark:text-gray-400 italic py-3 text-center">'
                + 'No files attached yet. Photographs are printed inside the report; bills and attendance proof '
                + 'are stored and linked from its annexure.</p>';
            return;
        }

        const icons = {
            photo: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            video: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
        };

        let html = '';
        ['photo', 'video', 'document'].forEach(kind => {
            const items = media.filter(m => m.kind === kind);
            if (items.length === 0) return;

            const allowance = (kind === 'photo' && window.generatorPayload)
                ? ' of ' + window.generatorPayload.photo_allowance : '';
            html += '<p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mt-3 mb-1">'
                + esc(REPORT_KINDS[kind].plural) + ' (' + items.length + allowance + ')</p>';

            items.forEach(m => {
                html += '<div class="flex items-center gap-2 py-1.5 px-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50 group">'
                    + '<svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">'
                    + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + icons[kind] + '"/></svg>'
                    + '<a href="' + esc(m.url) + '" target="_blank" class="flex-grow text-xs text-gray-700 dark:text-gray-200 truncate hover:underline" title="' + esc(m.original_name) + '">'
                    + esc(m.original_name) + '</a>'
                    + '<span class="text-[10px] text-gray-400 flex-shrink-0">' + esc(m.size_human) + '</span>'
                    + '<button type="button" onclick="deleteMedia(' + m.id + ')" title="Remove"'
                    + ' class="report-action opacity-0 group-hover:opacity-100 focus:opacity-100 text-red-500 hover:text-red-700 transition p-1 disabled:opacity-30">'
                    + '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">'
                    + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                    + '</button></div>';
            });
        });

        box.innerHTML = html;
    }

    /**
     * Uploads the chosen files straight away.
     *
     * Attachments live on the server rather than only in the browser, so the
     * convener can collect them over several sittings and a large scan is
     * only ever sent once. XMLHttpRequest rather than fetch, for the progress
     * readout - which means setting the CSRF header by hand.
     */
    function uploadMedia(kind, input) {
        const data = window.generatorPayload;
        if (!data || input.files.length === 0) return;

        const files = Array.from(input.files);
        const form = new FormData();
        form.append('proposal_id', data.id);
        form.append('kind', kind);
        files.forEach(f => form.append('files[]', f));

        document.getElementById('reportErrorBox').classList.add('hidden');
        setReportBusy(true, 'Uploading ' + files.length + ' ' + (files.length === 1 ? 'file' : 'files') + '...');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'api_upload_media.php');
        xhr.setRequestHeader('X-CSRF-Token', window.EC_CSRF);

        xhr.upload.onprogress = (e) => {
            if (!e.lengthComputable) return;
            const pct = Math.round((e.loaded / e.total) * 100);
            setReportBusy(true, 'Uploading... ' + pct + '%');
        };

        xhr.onload = () => {
            input.value = '';
            setReportBusy(false, '');
            let res = {};
            try { res = JSON.parse(xhr.responseText); } catch (e) { /* handled below */ }

            if (res.media) {
                reportState.media = res.media;
                window.generatorPayload.media = res.media;
                window.generatorPayload.photos_used = res.media.filter(m => m.kind === 'photo').length;
                renderMediaList();
            }
            if (res.status !== 'success') {
                showReportError(res.message || 'The upload was rejected by the server.');
            } else if (res.message) {
                // Some files landed, some did not.
                showReportError(res.message);
            }
        };

        xhr.onerror = () => {
            input.value = '';
            setReportBusy(false, '');
            showReportError('The upload could not reach the server. Check your connection and try again.');
        };

        xhr.send(form);
    }

    async function deleteMedia(mediaId) {
        if (!confirm('Remove this attachment from the report?')) return;

        setReportBusy(true, 'Removing...');
        try {
            const res = await fetch('api_delete_media.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ media_id: mediaId })
            }).then(r => r.json());

            if (res.media) {
                reportState.media = res.media;
                window.generatorPayload.media = res.media;
                window.generatorPayload.photos_used = res.media.filter(m => m.kind === 'photo').length;
                renderMediaList();
            }
            if (res.status !== 'success') {
                showReportError(res.message || 'The attachment could not be removed.');
            }
        } catch (err) {
            showReportError('The attachment could not be removed: ' + err.message);
        } finally {
            setReportBusy(false, '');
        }
    }

    // Converts any image - a File, or a blob pulled back from the server - into
    // a JPEG data URL the PDF can take, capping the resolution so a phone photo
    // does not blow up memory.
    const getSafeImageData = (blob) => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const MAX_DIM = 2000;
                let w = img.width;
                let h = img.height;

                if (w > MAX_DIM || h > MAX_DIM) {
                    const ratio = w / h;
                    if (w > h) { w = MAX_DIM; h = MAX_DIM / ratio; }
                    else { h = MAX_DIM; w = MAX_DIM * ratio; }
                }

                canvas.width = w || 800;
                canvas.height = h || 800;
                const ctx = canvas.getContext('2d');

                // White ground, so transparent PNGs do not print black.
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                resolve({ base64: canvas.toDataURL('image/jpeg', 0.8), width: canvas.width, height: canvas.height });
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
    });

    /** A QR code for the annexure, or '' when the library did not load. */
    function qrDataUrl(text) {
        if (typeof qrcode === 'undefined') return '';
        try {
            // 0 = pick the smallest symbol that fits, 'M' = medium correction,
            // which survives a printed page being scanned off a phone camera.
            const qr = qrcode(0, 'M');
            qr.addData(text);
            qr.make();
            return qr.createDataURL(4, 1);
        } catch (err) {
            return '';
        }
    }

    /** The text and tables of the report. Images are added afterwards. */
    function buildReportHtml(pData, opts) {
        const draft = opts.draft;
        const annexure = opts.annexure || [];

        let html = `
        <div style="font-family: Arial, sans-serif; font-size: 11px; padding: 20px; color: #333; margin: 0;">
            <div style="text-align: center; border-bottom: 2px solid #004289; padding-bottom: 10px; margin-bottom: 15px;">
                <h1 style="color: #004289; margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px;">SRM Institute of Science and Technology</h1>
                <h2 style="color: #444; margin: 3px 0 0; font-size: 14px;">Faculty of Engineering and Technology</h2>
                <h3 style="color: #666; margin: 3px 0 0; font-size: 12px;">${esc(pData.convener_department ? 'Department of ' + pData.convener_department : 'Department of Computing Technologies')}</h3>
                <h4 style="color: #333; margin: 10px 0 0; font-size: 16px; text-decoration: underline;">${draft ? 'EVENT REPORT (DRAFT)' : 'EVENT REPORT'}</h4>
            </div>`;

        if (draft) {
            html += `
            <div style="border: 1px solid #b45309; background: #fffbeb; color: #92400e; padding: 6px 10px; margin-bottom: 15px; font-size: 10px;">
                <b>DRAFT - for approval circulation only.</b> Prepared on ${esc(new Date().toLocaleDateString('en-GB'))}, before the event took place.
                It is not stored on the server. The official post-event report is generated after ${esc(new Date(pData.end_date).toLocaleDateString('en-GB'))}.
            </div>`;
        }

        html += `
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; width: 15%; border: 1px solid #ccc; background: #f9f9f9;">Event Title</td>
                    <td style="padding: 4px 6px; width: 35%; border: 1px solid #ccc;">${esc(pData.title)}</td>
                    <td style="padding: 4px 6px; font-weight: bold; width: 15%; border: 1px solid #ccc; background: #f9f9f9;">Reference ID</td>
                    <td style="padding: 4px 6px; width: 35%; border: 1px solid #ccc;">PRO-${String(pData.id).padStart(4, '0')}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Dates</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc;">${esc(new Date(pData.start_date).toLocaleDateString())} to ${esc(new Date(pData.end_date).toLocaleDateString())}</td>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Category</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc; text-transform: capitalize;">${esc(String(pData.category || '').replace(/_/g, ' '))}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Convener</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc;">${esc(pData.convener_name || 'Not recorded')}${pData.convener_designation ? '<br/><span style="font-size: 9px; color: #555;">' + esc(pData.convener_designation) + '</span>' : ''}</td>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Contact</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc;">${esc(pData.convener_email || '-')}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Participants</td>
                    <td style="padding: 4px 6px; border: 1px solid #ccc;" colspan="3"><b>Expected:</b> ${esc(pData.total_expected_participants)}${(opts.attendance && opts.attendance.any) ? ' | <b>Attended:</b> ' + esc(opts.attendance.total) : ''} | <b>Target Audience:</b> ${esc(pData.participant_categories)}</td>
                </tr>
                <tr>
                    <td style="padding: 6px; font-weight: bold; border: 1px solid #ccc; background: #f9f9f9;">Event Description</td>
                    <td style="padding: 6px; border: 1px solid #ccc; text-align: justify;" colspan="3">${esc(pData.description)}</td>
                </tr>
            </table>
        `;

        // What happened, in the convener's words. First thing after the facts.
        if (!draft && opts.summary) {
            html += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Summary of the Event</h3>
            <p style="text-align: justify; margin: 0 0 12px; font-size: 11px; line-height: 1.5;">${esc(opts.summary)}</p>`;
        }

        // Who came, by group. A total alone does not answer that question.
        if (!draft && opts.attendance && opts.attendance.any) {
            const a = opts.attendance;
            html += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Attendance</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Internal students</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">External students</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Internal faculty</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">External faculty</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #f0f8ff;">Total attended</th>
            </tr>
            <tr>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${esc(a.att_internal_students)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${esc(a.att_external_students)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${esc(a.att_internal_faculty)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${esc(a.att_external_faculty)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center; font-weight: bold; background: #f0f8ff;">${esc(a.total)}</td>
            </tr>
            </table>`;
        }

        // The evidence this report carries, and the rule it was collected under.
        if (!draft) {
            const days = pData.event_days || 1;
            html += `<p style="font-size: 9px; color: #555; margin: 0 0 12px;">
                Evidence attached: ${esc(opts.photoCount || 0)} photograph${(opts.photoCount === 1) ? '' : 's'}
                (allowance ${esc(pData.photo_allowance || 0)} - two per day over ${esc(days)} day${days === 1 ? '' : 's'})
                and ${esc(opts.documentCount || 0)} supporting document${(opts.documentCount === 1) ? '' : 's'}.
            </p>`;
        }

        if (pData.guests && pData.guests.length > 0) {
            html += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Chief Guests / Experts</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Name</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Designation &amp; Address</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Phone</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Reason for Inviting</th>
            </tr>`;
            pData.guests.forEach(g => {
                html += `<tr>
                <td style="border: 1px solid #ccc; padding: 4px;"><b>${esc(g.name)}</b></td>
                <td style="border: 1px solid #ccc; padding: 4px;">${esc(g.designation)}<br/>${esc(g.address)}</td>
                <td style="border: 1px solid #ccc; padding: 4px;">${esc(g.contact_number)}</td>
                <td style="border: 1px solid #ccc; padding: 4px;">${esc(g.reason)}</td>
            </tr>`;
            });
            html += `</table>`;
        }

        if (pData.travel && pData.travel.length > 0) {
            html += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Travel &amp; Accommodation</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Logistics Type</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Details</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">Provided By</th>
            </tr>`;
            pData.travel.forEach(t => {
                if (t.hotel_name_address) {
                    html += `<tr>
                    <td style="border: 1px solid #ccc; padding: 4px;"><b>Accommodation</b> (${esc(t.accommodation_days)} Days)</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">Hotel: ${esc(t.hotel_name_address)}</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">${esc(t.who_arranges)}</td>
                </tr>`;
                } else if (t.mode) {
                    html += `<tr>
                    <td style="border: 1px solid #ccc; padding: 4px;"><b>Travel</b> (${esc(t.mode)}) - ${esc(t.number_of_trips)} Trips</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">Locations: ${esc(t.travel_address)}</td>
                    <td style="border: 1px solid #ccc; padding: 4px;">${esc(t.who_provides)}</td>
                </tr>`;
                }
            });
            html += `</table>`;
        }

        if (pData.budgets && pData.budgets.length > 0) {
            let bTotal = 0;
            html += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Proposed Budget Breakdown</h3>
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
                html += `<tr>
                <td style="border: 1px solid #ccc; padding: 4px;"><b>${esc(b.category)}</b></td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${esc(b.type)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${esc(b.quantity)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: right;">${parseFloat(b.cost_per_unit).toLocaleString('en-IN')}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: right; font-weight: bold;">${parseFloat(b.total).toLocaleString('en-IN')}</td>
            </tr>`;
            });
            html += `<tr>
            <td colspan="4" style="border: 1px solid #ccc; padding: 4px; text-align: right; background: #f0f8ff;"><b>GRAND TOTAL</b></td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: right; background: #f0f8ff; font-weight: bold; color: #004289;">₹${bTotal.toLocaleString('en-IN')}</td>
        </tr></table>`;
        }

        html += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Funding Sources</h3>
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
        </table>`;

        // Annexure: what the PDF itself cannot carry.
        if (annexure.length > 0) {
            html += `<h3 style="background:#004289; color:white; padding: 4px 8px; margin: 15px 0 5px; font-size: 13px;">Annexure - Bills, Attendance Proof and Documents</h3>
            <p style="font-size: 9px; color: #666; margin: 0 0 6px;">Held with the event record - the browser cannot merge them into this PDF. Scan the code or open the link while signed in to SRM Event Connect.</p>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee; width: 5%;">#</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee; width: 13%;">Type</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee;">File</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee; width: 12%;">Size</th>
                <th style="border: 1px solid #ccc; padding: 4px; background: #eee; width: 20%; text-align: center;">Scan to open</th>
            </tr>`;
            annexure.forEach((m, i) => {
                const link = absoluteUrl(m.url);
                const qr = m.qr
                    ? `<img src="${m.qr}" style="width: 70px; height: 70px;" />`
                    : '<span style="font-size: 8px; color: #888;">see link</span>';
                html += `<tr>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${i + 1}</td>
                <td style="border: 1px solid #ccc; padding: 4px;">${esc(REPORT_KINDS[m.kind] ? REPORT_KINDS[m.kind].label : m.kind)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; word-break: break-all;"><b>${esc(m.original_name)}</b><br/>
                    <span style="font-size: 8px; color: #555;">${esc(link)}</span></td>
                <td style="border: 1px solid #ccc; padding: 4px;">${esc(m.size_human)}</td>
                <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">${qr}</td>
            </tr>`;
            });
            html += `</table>`;
        }

        html += `
        <div style="margin-top: 50px; display: flex; justify-content: space-between; page-break-inside: avoid; padding: 0 40px;">
            <div style="text-align: center; width: 220px;">
                <hr style="border: 0; border-bottom: 1.5px solid #000; margin-bottom: 10px;" />
                <span style="font-weight: bold; font-size: 13px;">Convener Signature</span>
                ${pData.convener_name ? `<br/><span style="font-size: 10px; color: #555;">${esc(pData.convener_name)}</span>` : ''}
            </div>
            <div style="text-align: center; width: 220px;">
                <hr style="border: 0; border-bottom: 1.5px solid #000; margin-bottom: 10px;" />
                <span style="font-weight: bold; font-size: 13px;">HOD Signature</span>
            </div>
        </div>
        </div>`;

        return html;
    }

    /**
     * Builds the PDF and hands back the jsPDF instance, so the caller can print
     * it, save it, or upload it.
     *
     * @param opts.draft    pre-event copy: no attachments, no annexure
     * @param opts.onStatus progress callback for the button label
     */
    async function buildReportPdf(opts) {
        const pData = window.generatorPayload;
        const status = opts.onStatus || function () {};
        const media = opts.draft ? [] : (reportState.media || []);
        const photos = media.filter(m => m.kind === 'photo');
        const others = media.filter(m => m.kind !== 'photo');

        // QR codes have to exist before the HTML that embeds them is built.
        if (others.length > 0) {
            status('Preparing annexure...');
            others.forEach(m => { m.qr = qrDataUrl(absoluteUrl(m.url)); });
        }

        status('Building PDF...');
        const html = buildReportHtml(pData, {
            draft: opts.draft,
            annexure: others,
            // A draft predates the event, so it carries none of this.
            attendance: opts.draft ? null : reportAttendance(),
            summary: opts.draft ? '' : reportSummary(),
            photoCount: photos.length,
            documentCount: others.length
        });

        const opt = {
            margin: 0.3,
            filename: 'report.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        };

        const pdf = await html2pdf().set(opt).from(html).toPdf().get('pdf');

        // Photographs go on their own pages, drawn straight onto the document -
        // html2canvas would rasterise them a second time and lose quality.
        if (photos.length > 0) {
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 0.3;
            const maxImgWidth = pageWidth - (margin * 2);
            const maxImgHeight = pageHeight - (margin * 2) - 0.5;

            for (let i = 0; i < photos.length; i++) {
                status('Attaching photograph ' + (i + 1) + ' of ' + photos.length + '...');
                try {
                    const response = await fetch(photos[i].url);
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const imgData = await getSafeImageData(await response.blob());

                    const ratio = imgData.width / imgData.height;
                    let renderWidth = maxImgWidth;
                    let renderHeight = maxImgWidth / ratio;
                    if (renderHeight > maxImgHeight) {
                        renderHeight = maxImgHeight;
                        renderWidth = maxImgHeight * ratio;
                    }

                    pdf.addPage();
                    pdf.setFontSize(11);
                    pdf.setTextColor(80);
                    pdf.text('Photograph ' + (i + 1) + ' of ' + photos.length + ' - ' + photos[i].original_name,
                        margin, margin + 0.2);
                    pdf.addImage(imgData.base64, 'JPEG', (pageWidth - renderWidth) / 2, margin + 0.5, renderWidth, renderHeight);
                } catch (err) {
                    // One unreadable photograph must not cost the whole report.
                    console.error('Could not attach ' + photos[i].original_name, err);
                    pdf.addPage();
                    pdf.setFontSize(11);
                    pdf.setTextColor(150);
                    pdf.text('Photograph ' + (i + 1) + ' (' + photos[i].original_name + ') could not be rendered.',
                        margin, margin + 0.5);
                }
            }
        }

        return pdf;
    }

    /**
     * Shows a finished PDF in a new tab, where the browser's own viewer offers
     * printing and saving. The tab is opened on the click itself - opening it
     * after the await would be caught by the popup blocker.
     */
    function showPdfInTab(tab, pdf) {
        const url = URL.createObjectURL(pdf.output('blob'));
        if (tab) {
            tab.location.href = url;
        } else {
            window.open(url, '_blank');
        }
        // Give the viewer time to load before the blob is released.
        setTimeout(() => URL.revokeObjectURL(url), 60000);
    }

    /** Pre-event copy for the HOD. Generated in the browser, never uploaded. */
    async function printDraftReport(btn) {
        if (!window.generatorPayload) return;

        const tab = window.open('', '_blank');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerText = 'Building...';

        try {
            const pdf = await buildReportPdf({ draft: true, onStatus: (m) => { btn.innerText = m; } });
            showPdfInTab(tab, pdf);
        } catch (err) {
            console.error('Draft generation failed', err);
            if (tab) tab.close();
            alert('The draft could not be generated: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }

    /** Post-event report, with attachments, shown for checking and printing. */
    async function previewReport() {
        const tab = window.open('', '_blank');
        document.getElementById('reportErrorBox').classList.add('hidden');
        setReportBusy(true, 'Building preview...');
        try {
            const pdf = await buildReportPdf({ draft: false, onStatus: (m) => setReportBusy(true, m) });
            showPdfInTab(tab, pdf);
        } catch (err) {
            console.error('Preview failed', err);
            if (tab) tab.close();
            showReportError('The preview could not be generated: ' + err.message);
        } finally {
            setReportBusy(false, '');
        }
    }

    /** Builds the final report and stores it against the proposal. */
    async function processReportGeneration() {
        if (!window.generatorPayload) return;

        const media = reportState.media || [];
        const minWords = window.generatorPayload.summary_min_words || 50;
        if (summaryWordCount() < minWords) {
            showReportError('Write the summary of what happened first - at least ' + minWords
                + ' words. It currently has ' + summaryWordCount() + '.');
            document.getElementById('reportSummary').focus();
            return;
        }
        if (media.length === 0 &&
            !confirm('No photographs or documents are attached. Generate the report anyway?')) {
            return;
        }
        if (!confirm('Generate the final report? Once generated it cannot be edited, and no further attachments can be added.')) {
            return;
        }

        document.getElementById('reportErrorBox').classList.add('hidden');
        setReportBusy(true, 'Building PDF...');

        try {
            const pdf = await buildReportPdf({ draft: false, onStatus: (m) => setReportBusy(true, m) });

            setReportBusy(true, 'Saving to the server...');
            const form = new FormData();
            form.append('proposal_id', window.generatorPayload.id);
            form.append('report_pdf', pdf.output('blob'), 'report.pdf');
            form.append('report_summary', reportSummary());
            const attendance = reportAttendance();
            if (attendance.any) {
                Object.keys(ATTENDANCE_FIELDS).forEach(field => form.append(field, attendance[field]));
            }

            const res = await fetch('api_save_report.php', { method: 'POST', body: form }).then(r => r.json());

            if (res.status === 'success') {
                window.location.href = 'dashboard.php?alert=Report%20Generated%20and%20Linked%20Successfully';
                return;
            }
            showReportError(res.message || 'The report could not be saved.');
        } catch (err) {
            console.error('Report generation failed', err);
            showReportError('The report could not be generated: ' + err.message);
        } finally {
            setReportBusy(false, '');
        }
    }

</script>

<!-- Event Report workspace. Opened from "Create Event Report" once the event
     has ended; attachments are uploaded as they are chosen, so the convener can
     come back to this over several sittings. -->
<div id="reportModal"
    class="fixed inset-0 z-[200] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl p-6 relative my-8">
        <h3
            class="text-xl font-bold text-gray-800 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-3 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span id="reportModalTitle">Event Report</span>
        </h3>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Write what happened, record who attended, and attach the evidence: photographs of the event, and the bills
            and attendance proof that go with it. Photographs are printed inside the report; documents are kept with the
            event record and reachable from the report's annexure by link and QR code. Everything you entered in the
            proposal is included automatically. Once the final report is generated it cannot be edited.
        </p>

        <div id="reportErrorBox"
            class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs rounded border border-red-200 dark:border-red-800 font-semibold"></div>

        <div id="reportProgress"
            class="hidden mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 text-xs rounded border border-blue-200 dark:border-blue-800 font-semibold flex items-center gap-2">
        </div>

        <div class="mb-5 p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20">
            <div class="flex items-baseline justify-between mb-1">
                <label for="reportSummary" class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Summary of what happened <span class="text-red-500">*</span>
                </label>
                <span id="summaryCount" class="text-[11px] font-semibold text-gray-500">0 / 50 words</span>
            </div>
            <textarea id="reportSummary" rows="4" oninput="updateSummaryCount()"
                placeholder="What was conducted, who spoke, what the participants took away, and how it concluded."
                class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                At least 50 words. Printed at the top of the report, before the tables.
            </p>
        </div>

        <div class="mb-5 p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Who attended?</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label for="att_internal_students" class="block text-[11px] text-gray-600 dark:text-gray-400 mb-1">Internal students</label>
                    <input type="number" id="att_internal_students" min="0" max="1000000" oninput="updateAttendanceTotal()"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label for="att_external_students" class="block text-[11px] text-gray-600 dark:text-gray-400 mb-1">External students</label>
                    <input type="number" id="att_external_students" min="0" max="1000000" oninput="updateAttendanceTotal()"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label for="att_internal_faculty" class="block text-[11px] text-gray-600 dark:text-gray-400 mb-1">Internal faculty</label>
                    <input type="number" id="att_internal_faculty" min="0" max="1000000" oninput="updateAttendanceTotal()"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label for="att_external_faculty" class="block text-[11px] text-gray-600 dark:text-gray-400 mb-1">External faculty</label>
                    <input type="number" id="att_external_faculty" min="0" max="1000000" oninput="updateAttendanceTotal()"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2">
                Total attended: <span id="attendanceTotal" class="font-bold text-gray-700 dark:text-gray-200">—</span>.
                Printed in the report and counted in the department analysis. Leave all four blank if no headcount was taken.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
            <label
                class="cursor-pointer bg-blue-50/50 dark:bg-blue-900/10 p-3 rounded-md border border-blue-100 dark:border-blue-800 hover:border-blue-400 transition text-center">
                <svg class="w-6 h-6 mx-auto text-blue-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="block text-xs font-bold text-blue-800 dark:text-blue-300">Photographs</span>
                <span class="block text-[10px] text-blue-600/70 dark:text-blue-400/70">JPG, PNG - 10 MB each</span>
                <span id="photoAllowance" class="block text-[10px] font-semibold text-blue-700 dark:text-blue-300 mt-1">Two per day of the event</span>
                <input type="file" class="hidden report-action" multiple accept="image/*"
                    onchange="uploadMedia('photo', this)">
            </label>

            <label
                class="cursor-pointer bg-amber-50/50 dark:bg-amber-900/10 p-3 rounded-md border border-amber-100 dark:border-amber-800 hover:border-amber-400 transition text-center">
                <svg class="w-6 h-6 mx-auto text-amber-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="block text-xs font-bold text-amber-800 dark:text-amber-300">Bills &amp; Attendance Proof</span>
                <span class="block text-[10px] text-amber-600/70 dark:text-amber-400/70">PDF, Word, Excel - 25 MB
                    each</span>
                <span class="block text-[10px] font-semibold text-amber-700 dark:text-amber-300 mt-1">No limit</span>
                <input type="file" class="hidden report-action" multiple
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv" onchange="uploadMedia('document', this)">
            </label>
        </div>

        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-5 max-h-64 overflow-y-auto">
            <div id="reportMediaList"></div>
        </div>

        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closeReportModal()"
                class="px-4 py-2 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-100 dark:hover:bg-gray-700 transition">Close</button>
            <button onclick="previewReport()"
                class="report-action px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition disabled:opacity-50 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Preview &amp; Print
            </button>
            <button id="reportGeneratePerformBtn" onclick="processReportGeneration()"
                class="report-action px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition disabled:opacity-50 flex items-center gap-2">
                Generate &amp; Save Final Report
            </button>
        </div>
    </div>
</div>

<!-- Invisible HTML2PDF Container -->
<div id="pdfTemplateFrame" style="position: absolute; top: -9999px; left: -9999px; width: 800px; background: white;">
</div>

<!-- Modals placeholder -->
<?php require 'partials/footer.php'; ?>