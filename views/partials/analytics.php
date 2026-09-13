<?php
/**
 * Department analysis panel.
 *
 * Included by the HOD and coordinator dashboards, which both already see their
 * whole department. Every figure comes from api_analytics.php, and the archive
 * button hands the same period to download_report_archive.php, so the numbers
 * on screen and the numbers in the ZIP are produced by the same code.
 */
require_once __DIR__ . '/../../includes/analytics.php';
$ec_period_list = ec_periods();
?>

<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl p-6 mb-8 transition-colors duration-300">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Department Analysis</h2>
            <p id="analyticsRange" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Loading...</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <?php foreach ($ec_period_list as $key => $meta): ?>
                <button type="button" data-period="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                    class="ec-period-btn px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="analyticsError"
        class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs rounded border border-red-200 dark:border-red-800 font-semibold"></div>

    <!-- Headline figures -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/30 p-4">
            <span class="block text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400">Events held</span>
            <span id="statEvents" class="block text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">—</span>
            <span id="statEventsSub" class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5"></span>
        </div>
        <div class="rounded-xl border border-blue-100 dark:border-blue-900 bg-blue-50/60 dark:bg-blue-900/20 p-4">
            <span class="block text-[10px] uppercase tracking-wider text-blue-700 dark:text-blue-300">Participants attended</span>
            <span id="statAttended" class="block text-2xl font-bold text-blue-900 dark:text-blue-200 mt-1">—</span>
            <span id="statAttendedSub" class="block text-[11px] text-blue-700/70 dark:text-blue-300/70 mt-0.5"></span>
        </div>
        <div class="rounded-xl border border-emerald-100 dark:border-emerald-900 bg-emerald-50/60 dark:bg-emerald-900/20 p-4">
            <span class="block text-[10px] uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Budget</span>
            <span id="statBudget" class="block text-2xl font-bold text-emerald-900 dark:text-emerald-200 mt-1">—</span>
            <span class="block text-[11px] text-emerald-700/70 dark:text-emerald-300/70 mt-0.5">across events held</span>
        </div>
        <div class="rounded-xl border border-indigo-100 dark:border-indigo-900 bg-indigo-50/60 dark:bg-indigo-900/20 p-4">
            <span class="block text-[10px] uppercase tracking-wider text-indigo-700 dark:text-indigo-300">Reports filed</span>
            <span id="statReports" class="block text-2xl font-bold text-indigo-900 dark:text-indigo-200 mt-1">—</span>
            <span id="statReportsSub" class="block text-[11px] text-indigo-700/70 dark:text-indigo-300/70 mt-0.5"></span>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-3">Events by month</h3>
            <div class="relative h-56 w-full"><canvas id="analyticsTrendChart"></canvas></div>
        </div>
        <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-3">Events by category</h3>
            <div class="relative h-56 w-full"><canvas id="analyticsCategoryChart"></canvas></div>
        </div>
    </div>

    <!-- Per-faculty breakdown -->
    <div class="rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">By faculty</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium">Convener</th>
                        <th class="px-4 py-2 text-right font-medium">Events</th>
                        <th class="px-4 py-2 text-right font-medium">Attended</th>
                        <th class="px-4 py-2 text-right font-medium">Budget</th>
                        <th class="px-4 py-2 text-right font-medium">Reports</th>
                    </tr>
                </thead>
                <tbody id="facultyTableBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
            </table>
        </div>
    </div>

    <!-- Archive download -->
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/30 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Download the reports for this period</h3>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                A ZIP of every filed report PDF, with <code>index.csv</code>, <code>analysis.csv</code> and a summary.
                Videos stay on the server and are linked from the index.
            </p>
            <label class="inline-flex items-center gap-2 mt-2 text-[11px] text-gray-600 dark:text-gray-300 cursor-pointer">
                <input type="checkbox" id="archiveIncludeMedia"
                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                Include photographs and documents (larger file)
            </label>
        </div>
        <button type="button" id="archiveDownloadBtn"
            class="flex-shrink-0 inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download ZIP
        </button>
    </div>
</div>

<script>
    // Department analysis. Chart.js is loaded further down the page, so all of
    // this waits for DOMContentLoaded.
    (function () {
        let analyticsPeriod = '3m';
        let trendChart = null;
        let categoryChart = null;

        const num = (n) => Number(n || 0).toLocaleString('en-IN');
        const money = (n) => '₹' + Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });

        function markActivePeriod() {
            document.querySelectorAll('.ec-period-btn').forEach(btn => {
                const on = btn.dataset.period === analyticsPeriod;
                btn.classList.toggle('bg-indigo-600', on);
                btn.classList.toggle('text-white', on);
                btn.classList.toggle('border-indigo-600', on);
                btn.classList.toggle('text-gray-600', !on);
                btn.classList.toggle('dark:text-gray-300', !on);
            });
        }

        function renderFaculty(rows) {
            const body = document.getElementById('facultyTableBody');
            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">'
                    + 'No events were held in this period.</td></tr>';
                return;
            }
            body.innerHTML = rows.map(f => {
                const outstanding = f.reports_pending > 0
                    ? ' <span class="text-[10px] text-amber-600 dark:text-amber-400">(' + f.reports_pending + ' due)</span>'
                    : '';
                return '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">'
                    + '<td class="px-4 py-2 text-gray-800 dark:text-gray-100">' + escapeHtml(f.name)
                    + '<br/><span class="text-[10px] text-gray-400">' + escapeHtml(f.email) + '</span></td>'
                    + '<td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-100">' + num(f.events) + '</td>'
                    + '<td class="px-4 py-2 text-right text-gray-600 dark:text-gray-300">' + num(f.attended) + '</td>'
                    + '<td class="px-4 py-2 text-right text-gray-600 dark:text-gray-300">' + money(f.budget) + '</td>'
                    + '<td class="px-4 py-2 text-right text-gray-600 dark:text-gray-300">'
                    + f.reports_filed + '/' + f.events + outstanding + '</td></tr>';
            }).join('');
        }

        function escapeHtml(v) {
            return String(v === null || v === undefined ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function renderCharts(data) {
            const dark = document.documentElement.classList.contains('dark');
            const grid = dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
            const tick = dark ? '#9CA3AF' : '#6B7280';
            const axes = {
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: tick }, grid: { color: grid } },
                    x: { ticks: { color: tick }, grid: { display: false } }
                },
                plugins: { legend: { display: false } },
                responsive: true,
                maintainAspectRatio: false
            };

            if (trendChart) trendChart.destroy();
            trendChart = new Chart(document.getElementById('analyticsTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.timeline.map(m => m.label),
                    datasets: [{
                        data: data.timeline.map(m => m.events),
                        borderColor: '#4F46E5',
                        backgroundColor: 'rgba(79,70,229,0.12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3
                    }]
                },
                options: axes
            });

            const categories = Object.entries(data.categories || {});
            if (categoryChart) categoryChart.destroy();
            categoryChart = new Chart(document.getElementById('analyticsCategoryChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: categories.map(([key]) => key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())),
                    datasets: [{ data: categories.map(([, count]) => count), backgroundColor: '#0EA5E9', borderRadius: 4 }]
                },
                options: Object.assign({}, axes, { indexAxis: 'y' })
            });
        }

        async function loadAnalytics() {
            markActivePeriod();
            document.getElementById('analyticsError').classList.add('hidden');
            try {
                const data = await fetch('api_analytics.php?period=' + analyticsPeriod).then(r => r.json());
                if (data.status !== 'success') {
                    throw new Error(data.message || 'The analysis could not be loaded.');
                }

                const t = data.totals;
                document.getElementById('analyticsRange').innerText =
                    data.department + ' · ' + data.period.label + ' (' + data.period.from + ' to ' + data.period.to + ')';

                document.getElementById('statEvents').innerText = num(t.events_held);
                document.getElementById('statEventsSub').innerText =
                    t.events_cancelled > 0 ? t.events_cancelled + ' cancelled in this period' : num(t.events) + ' in the window';

                document.getElementById('statAttended').innerText = num(t.attended);
                document.getElementById('statAttendedSub').innerText =
                    t.expected > 0 ? t.attendance_percent + '% of the ' + num(t.expected) + ' expected' : '';

                document.getElementById('statBudget').innerText = money(t.budget);

                document.getElementById('statReports').innerText = t.reports_filed + ' / ' + t.events_held;
                document.getElementById('statReportsSub').innerText =
                    t.reports_pending > 0 ? t.reports_pending + ' still outstanding' : 'all filed';

                renderFaculty(data.faculty || []);
                renderCharts(data);
            } catch (err) {
                const box = document.getElementById('analyticsError');
                box.innerText = err.message;
                box.classList.remove('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.ec-period-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    analyticsPeriod = btn.dataset.period;
                    loadAnalytics();
                });
            });

            document.getElementById('archiveDownloadBtn').addEventListener('click', () => {
                const media = document.getElementById('archiveIncludeMedia').checked ? '1' : '0';
                // A plain navigation: the response is an attachment, so the page stays put.
                window.location = 'download_report_archive.php?period=' + encodeURIComponent(analyticsPeriod)
                    + '&include_media=' + media;
            });

            loadAnalytics();
        });
    })();
</script>
