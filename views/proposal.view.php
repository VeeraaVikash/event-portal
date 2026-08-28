<?php 
$page_title = 'Submit Proposal - SRM Event Connect';
$body_class = 'bg-gray-50 dark:bg-gray-900 flex flex-col min-h-screen text-gray-900 dark:text-gray-100 transition-colors duration-300';
require 'partials/head.php'; 
require 'partials/nav.php'; 

// Fetch department
$dept = $_SESSION['department'] ?? 'Computing Technologies';
?>

<main class="flex-grow max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
<?php
$is_editing = isset($_GET['id']) && is_numeric($_GET['id']);
$prop_id_str = $is_editing ? sprintf('%04d', $_GET['id']) : '';
?>
    <div class="mb-8 block text-center sm:text-left text-gray-800 dark:text-gray-100 block border-b border-gray-200 dark:border-gray-700 pb-4">
        <h1 class="text-3xl sm:text-4xl font-bold">
            <?= $is_editing ? "EDITING PROPOSAL: PRO-" . $prop_id_str : 'Submit Event Proposal' ?>
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm sm:text-base">
            <?= $is_editing ? "Modify and resubmit the requested details." : 'Fill in the formal requirements for HOD review.' ?>
        </p>
    </div>

    <form id="proposalForm" action="proposal.php<?= $is_editing ? '?id=' . (int)$_GET['id'] : '' ?>" method="POST" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ec_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <!-- 1. Convener Information (PREFILLED) -->
        <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-blue-100 dark:border-gray-700 p-6 sm:p-8 relative overflow-hidden transition-colors">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Convener Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Convener Name</label>
                    <input type="text" value="<?= htmlspecialchars($_SESSION['full_name']) ?>" disabled
                           class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-500 dark:text-gray-400 cursor-not-allowed font-medium focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Convener Email</label>
                    <input type="email" value="<?= htmlspecialchars($_SESSION['email']) ?>" disabled
                           class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-500 dark:text-gray-400 cursor-not-allowed font-medium focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Department</label>
                    <input type="text" value="<?= htmlspecialchars($dept) ?>" disabled
                           class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-500 dark:text-gray-400 cursor-not-allowed font-medium focus:outline-none">
                </div>
            </div>
        </section>

        <!-- 2. Event Details -->
        <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8 transition-colors">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Event Details <span class="text-red-500">*</span>
            </h2>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Event Title</label>
                    <input type="text" name="title" required placeholder="Enter Event Title"
                           class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 shadow-sm outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Event Description</label>
                    <textarea name="description" required rows="3" placeholder="Provide a detailed description..."
                              class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 shadow-sm outline-none"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Event Category</label>
                        <select name="category" required class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-sm outline-none">
                            <option value="">Select Category</option>
                            <?php 
                            $cats = ["conference_national", "conference_international", "fdp", "workshop", "winter_summer_school", "mdp_pdp", "student_programme", "alumni_programme", "outreach_programme", "value_added_course", "association_activity", "counselling_activity", "commemoration_day", "upskilling_non_teaching", "industrial_conclave", "patent_commercialisation", "lecture_series_industry_expert"];
                            foreach($cats as $cat): 
                                $name = ucwords(str_replace('_', ' ', $cat));
                            ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                        <input type="date" name="start_date" id="start_date" required onchange="calculateDuration()"
                               class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 overflow-hidden shadow-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                        <input type="date" name="end_date" id="end_date" required onchange="calculateDuration()"
                               class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 shadow-sm outline-none">
                    </div>
                </div>
                <div id="duration_box" class="hidden text-center text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 py-2 rounded-lg"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Past Relevant Events (Optional)</label>
                        <textarea name="past_events" rows="2" class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 shadow-sm outline-none text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Other Relevant Details (Optional)</label>
                        <textarea name="other_details" rows="2" class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 shadow-sm outline-none text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Participants -->
        <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8 transition-colors">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Participants
            </h2>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Total Expected Count</label>
                    <input type="number" name="total_participants" min="1" placeholder="e.g. 150"
                           class="w-full md:w-1/3 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Participant Categories</label>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $pcats = ["FA's", "Faculties Only", "International Students", "International Participants", "Students"];
                        foreach($pcats as $cat): 
                        ?>
                        <label class="cursor-pointer relative group">
                            <input type="checkbox" name="part_categories[]" value="<?= htmlspecialchars($cat) ?>" class="peer sr-only" <?= $cat==='Students' ? 'onchange="document.getElementById(\'studentCats\').classList.toggle(\'hidden\', !this.checked)"' : '' ?>>
                            <span class="inline-block px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full text-sm font-medium text-gray-700 dark:text-gray-300 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all shadow-sm">
                                <?= $cat ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="studentCats" class="hidden p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-600 mt-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Targeted Student Departments (Optional)</label>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $scats = ["Aerospace Engineering", "Automobile Engineering", "Biomedical Engineering", "Biotechnology", "Chemical Engineering", "Civil Engineering", "Computer Science and Engineering", "Ctech", "Cintel", "Electrical and Electronics Engineering", "Electronics and Communication Engineering", "Electronics and Instrumentation Engineering", "Food Process Engineering", "Genetic Engineering", "Information Technology", "Mechanical Engineering", "Mechatronics Engineering", "Software Engineering"];
                        foreach($scats as $scat): 
                        ?>
                        <label class="cursor-pointer flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-1.5 border border-gray-200 dark:border-gray-600 rounded-md shadow-sm">
                            <input type="checkbox" name="student_categories[]" value="<?= $scat ?>" class="rounded bg-white text-blue-600">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300"><?= $scat ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Chief Guest Details -->
        <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8 transition-colors">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                <svg class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                Chief Guest Details
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Details for the primary Chief Guest.</p>

            <div class="space-y-6">
                <!-- Core info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="cg_name" required placeholder="Full Name" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Designation <span class="text-red-500">*</span></label>
                        <input type="text" name="cg_designation" required placeholder="e.g. CEO, Example Corp" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address <span class="text-red-500">*</span></label>
                    <input type="text" name="cg_address" required placeholder="Full Address" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="tel" name="cg_phone" required placeholder="Contact Number" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PAN <span class="text-red-500">*</span></label>
                        <input type="text" name="cg_pan" required placeholder="PAN Number" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason for Inviting <span class="text-red-500">*</span></label>
                        <input type="text" name="cg_reason" required placeholder="Brief reason" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <!-- Accommodation block -->
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h3 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Accommodation
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Hotel Name</label>
                            <input type="text" name="hotel_name" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Hotel Address</label>
                            <input type="text" name="hotel_address" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Duration (days)</label>
                            <input type="number" name="hotel_duration" min="1" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Hotel Type</label>
                            <select name="hotel_type" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                                <option value="srm">SRM Arranged</option>
                                <option value="others">External/Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Travel block -->
                <div class="mt-4">
                    <h3 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Travel Arranged
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Travel Mode/Name</label>
                            <input type="text" name="travel_name" placeholder="Flight / Car" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Travel To/From Address</label>
                            <input type="text" name="travel_address" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Duration (trips)</label>
                            <input type="number" name="travel_duration" min="1" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Travel Type</label>
                            <select name="travel_type" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm outline-none">
                                <option value="srm">SRM Provided</option>
                                <option value="others">External/Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Financial Overview -->
        <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8 transition-colors">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-6 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Financial & Budget Estimations
            </h2>

            <!-- Summary Block -->
            <div class="mb-8">
                <h4 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-4">Initial Funding Sources (₹)</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">University Fund</label>
                        <input type="number" name="fund_uni" min="0" placeholder="0" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Registration Fund</label>
                        <input type="number" name="fund_reg" min="0" placeholder="0" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sponsorship Fund</label>
                        <input type="number" id="fund_sponsor" name="fund_sponsor" value="0" readonly class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-md outline-none cursor-not-allowed font-medium text-blue-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Other Sources</label>
                        <input type="number" name="fund_other" min="0" placeholder="0" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md outline-none">
                    </div>
                </div>
            </div>

            <!-- Detailed Budget Engine -->
            <div class="mb-8 overflow-hidden">
                <h4 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-4">Detailed Expense Budget Items</h4>
                <div class="overflow-x-auto pb-2 custom-scrollbar">
                    <table class="w-full text-sm text-left align-middle border border-gray-200 dark:border-gray-700 rounded-lg min-w-[800px] overflow-hidden">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr class="text-gray-600 dark:text-gray-300">
                                <th class="p-3 w-10 text-center font-medium">#</th>
                                <th class="p-3 font-medium">Category & Subcategory</th>
                                <th class="p-3 font-medium text-center">Location Type</th>
                                <th class="p-3 font-medium text-center">Quantity</th>
                                <th class="p-3 font-medium text-center">Cost/Unit (₹)</th>
                                <th class="p-3 font-medium text-right">Total (₹)</th>
                                <th class="p-3 font-medium text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="budgetTableBody" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <!-- JS Injected budget rows -->
                        </tbody>
                        <tfoot class="bg-gray-100/50 dark:bg-gray-700/30 font-semibold text-gray-800 dark:text-gray-200">
                            <tr>
                                <td colspan="5" class="p-3 text-right">Total Estimated Budget (₹):</td>
                                <td class="p-3 text-right text-lg text-blue-600 dark:text-blue-400" id="totalBudgetText">0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" onclick="addBudgetRow()" class="mt-3 px-3 py-1.5 border border-blue-500 text-blue-600 dark:text-blue-400 rounded-full hover:bg-blue-50 dark:hover:bg-blue-900/30 transition text-sm font-medium inline-flex items-center gap-1">
                    + Add Expense Item
                </button>
            </div>

            <!-- Sponsorship Engine -->
            <div class="overflow-hidden">
                <h4 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-4">Sponsorship Breakdown</h4>
                <div class="overflow-x-auto pb-2 custom-scrollbar">
                    <table class="w-full text-sm text-left align-middle border border-gray-200 dark:border-gray-700 rounded-lg min-w-[800px] overflow-hidden">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <tr class="text-gray-600 dark:text-gray-300">
                                <th class="p-3 w-10 text-center font-medium">#</th>
                                <th class="p-3 font-medium">Category <span class="text-red-500">*</span></th>
                                <th class="p-3 font-medium text-right">Amount (₹)</th>
                                <th class="p-3 font-medium">Reward</th>
                                <th class="p-3 font-medium">Mode</th>
                                <th class="p-3 font-medium">Benefit/Output</th>
                                <th class="p-3 font-medium text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="sponsorTableBody" class="bg-white dark:bg-gray-800">
                            <!-- JS Injected sponsorship rows -->
                        </tbody>
                    </table>
                </div>
                <button type="button" onclick="addSponsorRow()" class="mt-3 px-3 py-1.5 border border-amber-500 text-amber-600 dark:text-amber-400 rounded-full hover:bg-amber-50 dark:hover:bg-amber-900/30 transition text-sm font-medium inline-flex items-center gap-1">
                    + Add Sponsor
                </button>
            </div>
            
            <input type="hidden" name="budget_json" id="budgetJson">
            <input type="hidden" name="sponsor_json" id="sponsorJson">
        </section>

        <!-- Submit Panel -->
        <div class="flex justify-center pt-8 pb-16">
            <button type="button" onclick="submitForm()" class="px-10 py-3.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-bold text-lg rounded-full shadow-lg transition-transform transform hover:scale-[1.01] active:scale-[0.98]">
                <?= $is_editing ? "Update Proposal / Resubmit" : "Submit Proposal for Review" ?>
            </button>
        </div>

    </form>
</main>

<script>
<?php if($is_editing && !empty($edit_data)): ?>
    document.addEventListener("DOMContentLoaded", () => {
        const d = <?= json_encode($edit_data) ?>;
        const setVal = (sel, val) => { const el = document.querySelector(sel); if(el && val) el.value = val; };

        // Core
        setVal('input[name="title"]', d.title);
        setVal('textarea[name="description"]', d.description);
        setVal('input[name="start_date"]', d.start_date);
        setVal('input[name="end_date"]', d.end_date);
        setVal('select[name="category"]', d.category);
        setVal('input[name="total_participants"]', d.total_expected_participants);
        setVal('textarea[name="past_events"]', d.past_events);
        setVal('textarea[name="other_details"]', d.other_details);

        if(d.participant_categories) {
            const arr = d.participant_categories.split(',').map(x=>x.trim());
            document.querySelectorAll('input[name="part_categories[]"]').forEach(chk => {
                if(arr.includes(chk.value)) { 
                    chk.checked = true;
                    if(chk.value === 'Students') document.getElementById('studentCats').classList.remove('hidden');
                }
            });
        }
        if(d.student_categories) {
            const arr = d.student_categories.split(',').map(x=>x.trim());
            document.querySelectorAll('input[name="student_categories[]"]').forEach(chk => {
                if(arr.includes(chk.value)) chk.checked = true;
            });
        }

        // Financials
        setVal('input[name="fund_uni"]', d.university_fund);
        setVal('input[name="fund_reg"]', d.registration_fund);
        setVal('input[name="fund_other"]', d.other_sources);

        // Guest
        if(d.guest) {
            setVal('input[name="cg_name"]', d.guest.name);
            setVal('input[name="cg_designation"]', d.guest.designation);
            setVal('input[name="cg_address"]', d.guest.address);
            setVal('input[name="cg_phone"]', d.guest.contact_number);
            setVal('input[name="cg_pan"]', d.guest.pan_number);
            setVal('input[name="cg_reason"]', d.guest.reason_for_inviting);
        }

        // Travel 
        if(d.travel && d.travel.length > 0) {
            d.travel.forEach(t => {
                if(t.hotel_name_address) {
                    const parts = t.hotel_name_address.split(' - ');
                    setVal('input[name="hotel_name"]', parts[0]);
                    if(parts[1]) setVal('input[name="hotel_address"]', parts.slice(1).join(' - '));
                    setVal('input[name="hotel_duration"]', t.accommodation_days);
                    setVal('select[name="hotel_type"]', t.who_arranges === 'SRM' ? 'srm' : 'others');
                }
                if(t.mode) {
                    setVal('input[name="travel_name"]', t.mode);
                    setVal('input[name="travel_duration"]', t.number_of_trips);
                    setVal('select[name="travel_type"]', t.who_provides === 'SRM' ? 'srm' : 'others');
                }
            });
        }
        
        // Render arrays properly after timeout to let global bindings setup logic catch up
        setTimeout(() => {
            if(d.budgets && d.budgets.length > 0) {
                budgetItems = d.budgets.map(b => {
                    const m = b.category.match(/(.*?)\s*\-\s*(.*)/);
                    return {
                        id: budgetIdCounter++,
                        category: m ? m[1].trim() : b.category,
                        sub_category: m ? m[2].trim() : '',
                        type: b.type || 'Domestic',
                        quantity: parseInt(b.quantity) || 1,
                        cost: parseFloat(b.cost_per_unit) || 0,
                        amount: parseFloat(b.total) || 0
                    };
                });
                renderBudgetTable();
            }
            if(d.sponsors && d.sponsors.length > 0) {
                sponsorItems = d.sponsors.map(s => {
                    return {
                        id: sponsorIdCounter++,
                        category: s.sponsor_category || '',
                        amount: parseFloat(s.amount_contributed) || 0,
                        reward: s.reward_perk || '',
                        mode: s.mode || '',
                        benefit: s.benefits || '',
                        about: s.about || ''
                    };
                });
                renderSponsorTable();
            }
        }, 150);
    });
<?php endif; ?>
    // Initialize Flatpickr 
    document.addEventListener("DOMContentLoaded", () => {
        const isDark = document.documentElement.classList.contains('dark');
        if(isDark) document.getElementById('flatpickr-dark-theme').removeAttribute('disabled');
        flatpickr("input[type='date']", { 
            dateFormat: "Y-m-d", 
            minDate: "today" 
        });
    });

    function calculateDuration(){
        const s = document.getElementById("start_date").value;
        const e = document.getElementById("end_date").value;
        const b = document.getElementById("duration_box");
        if(s && e){
            const d1 = new Date(s);
            const d2 = new Date(e);
            if(d2 < d1){
                b.innerText = "End date cannot be before start date";
                b.className = "text-center text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 py-2 rounded-lg mt-4";
                b.classList.remove('hidden');
            } else {
                const diffTime = Math.abs(d2 - d1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                b.innerText = "Total Duration: " + diffDays + " Day" + (diffDays>1?"s":"");
                b.className = "text-center text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 py-2 rounded-lg mt-4";
                b.classList.remove('hidden');
            }
        }
    }

    // JSON Arrays
    let budgetItems = [];
    let budgetIdCounter = 0;
    
    let sponsorItems = [];
    let sponsorIdCounter = 0;

    const subcategoryMap = {
        "Budgetary Expenditures": ["Number of Sessions Planned", "Number of Keynote Speakers", "Number of Session Judges", "Number of Celebrities / Chief Guests"],
        "Publicity": ["Invitation", "Press Coverage", "Brochures/Flyers", "Website/Social Media"],
        "General": ["Conference Kits", "Printing and Stationery", "Secretarial Expenses", "Mementos", "Certificates"],
        "Honorarium": ["Keynote Speakers", "Session Judges", "Chief Guests", "Invited Speakers"],
        "Hospitality": ["Train / Flight for Chief Guest / Keynote Speakers", "Accommodation for Chief Guest / Keynote Speakers", "Food and Beverages for Chief Guest / Keynote Speakers", "Local Travel Expenses", "Food for Participants", "Food & Snacks for Volunteers / Organizers", "Hostel Accommodation"],
        "Inaugural and Valedictory": ["Banners, Pandal etc", "Lighting and Decoration", "Flower Bouquet", "Cultural Events", "Field Visits / Sightseeing"],
        "Resource Materials": ["Preparation, Printing, Binding", "Software/Licenses"],
        "Conference Paper Publication": ["Extended Abstract", "Full Paper", "Journal Publication Fees", "Proceedings"],
        "Miscellaneous": ["Contingency", "Bank Charges", "Other Unforeseen"]
    };

    // ----- BUDGET LOGIC -----
    function addBudgetRow() {
        budgetItems.push({ id: budgetIdCounter++, category: "", sub_category: "", type: "Domestic", quantity: 1, cost: 0, amount: 0 });
        renderBudgetTable();
    }

    function renderBudgetTable() {
        const tbody = document.getElementById("budgetTableBody");
        tbody.innerHTML = "";
        let total = 0;
        budgetItems.forEach((item, index) => {
            item.amount = item.quantity * item.cost;
            total += item.amount;
            
            let subOptions = `<option value="" disabled selected hidden>Subcategory</option>`;
            if(item.category && subcategoryMap[item.category]) {
                subcategoryMap[item.category].forEach(sub => {
                    subOptions += `<option value="${sub}" ${item.sub_category === sub ? 'selected' : ''}>${sub}</option>`;
                });
            }

            const tr = document.createElement("tr");
            tr.className = "border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors";
            tr.innerHTML = `
                <td class="p-3 text-center align-top text-gray-500">${index + 1}</td>
                <td class="p-3 align-top space-y-2">
                    <select onchange="updateBudget(${item.id}, 'category', this.value)" class="w-full text-xs p-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded text-gray-800 dark:text-gray-200">
                        <option value="" disabled selected hidden>Category</option>
                        ${Object.keys(subcategoryMap).map(k => `<option value="${k}" ${item.category === k ? 'selected' : ''}>${k}</option>`).join('')}
                    </select>
                    <select onchange="updateBudget(${item.id}, 'sub_category', this.value)" class="w-full text-xs p-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded text-gray-800 dark:text-gray-200" ${item.category ? '' : 'disabled'}>
                        ${subOptions}
                    </select>
                </td>
                <td class="p-3 align-middle text-center">
                    <div class="flex flex-col items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="loc_${item.id}" value="Domestic" ${item.type==='Domestic'?'checked':''} onchange="updateBudget(${item.id}, 'type', 'Domestic')"> Domestic</label>
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="loc_${item.id}" value="International" ${item.type==='International'?'checked':''} onchange="updateBudget(${item.id}, 'type', 'International')"> International</label>
                    </div>
                </td>
                <td class="p-3 align-middle text-center">
                    <input type="number" min="1" value="${item.quantity}" oninput="updateBudget(${item.id}, 'quantity', this.value)" class="w-16 p-1.5 text-center text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded">
                </td>
                <td class="p-3 align-middle text-center">
                    <input type="number" min="0" step="0.01" value="${item.cost}" oninput="updateBudget(${item.id}, 'cost', this.value)" class="w-full p-1.5 text-right text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded">
                </td>
                <td id="budget_amount_${item.id}" class="p-3 align-middle text-right font-medium text-gray-800 dark:text-gray-200">
                    ${item.amount.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                </td>
                <td class="p-3 align-middle text-center">
                    <button type="button" onclick="removeBudget(${item.id})" class="text-red-500 hover:text-red-700 bg-red-50 dark:bg-red-900/20 p-1.5 rounded transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        document.getElementById("totalBudgetText").innerText = total.toLocaleString('en-IN', {minimumFractionDigits: 2});
        syncJSON();
    }
    
    function updateBudgetTotals() {
        let total = 0;
        budgetItems.forEach(i => total += (i.amount || 0));
        document.getElementById("totalBudgetText").innerText = total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    }

    function updateBudget(id, field, value) {
        let item = budgetItems.find(i => i.id === id);
        if(!item) return;

        if(field === 'category') {
            item.category = value; item.sub_category = "";
            renderBudgetTable();
        } else if(field === 'quantity' || field === 'cost') {
            item[field] = parseFloat(value) || 0;
            item.amount = (item.quantity || 0) * (item.cost || 0);
            const tNode = document.getElementById(`budget_amount_${item.id}`);
            if (tNode) tNode.innerText = item.amount.toLocaleString('en-IN', {minimumFractionDigits: 2});
            updateBudgetTotals();
        } else {
            item[field] = value;
        }
        syncJSON();
    }
    function removeBudget(id) { budgetItems = budgetItems.filter(i => i.id !== id); renderBudgetTable(); }

    // ----- SPONSOR LOGIC -----
    function addSponsorRow() {
        sponsorItems.push({ id: sponsorIdCounter++, category: "", amount: 0, reward: "", mode: "", benefit: "", about: "" });
        renderSponsorTable();
    }

    function renderSponsorTable() {
        const tbody = document.getElementById("sponsorTableBody");
        tbody.innerHTML = "";
        let total = 0;
        sponsorItems.forEach((item, index) => {
            total += parseFloat(item.amount) || 0;
            // Native fragment style for UI
            const tr1 = document.createElement("tr");
            tr1.className = "border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors text-sm text-gray-800 dark:text-gray-200";
            tr1.innerHTML = `
                <td class="p-2 text-center align-middle font-medium text-gray-500">${index + 1}</td>
                <td class="p-2 align-middle"><input type="text" placeholder="Title/Co-sponsor" oninput="updateSponsor(${item.id}, 'category', this.value)" value="${item.category}" class="w-full p-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded outline-none"></td>
                <td class="p-2 align-middle text-right"><input type="number" min="0" placeholder="Amount" oninput="updateSponsor(${item.id}, 'amount', this.value)" value="${item.amount}" class="w-24 p-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded text-right outline-none"></td>
                <td class="p-2 align-middle"><input type="text" placeholder="Perks" oninput="updateSponsor(${item.id}, 'reward', this.value)" value="${item.reward}" class="w-full p-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded outline-none"></td>
                <td class="p-2 align-middle"><input type="text" placeholder="Cash/Kind" oninput="updateSponsor(${item.id}, 'mode', this.value)" value="${item.mode}" class="w-full p-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded outline-none"></td>
                <td class="p-2 align-middle"><input type="text" placeholder="Output expected" oninput="updateSponsor(${item.id}, 'benefit', this.value)" value="${item.benefit}" class="w-full p-1.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded outline-none"></td>
                <td class="p-2 align-middle text-center">
                    <button type="button" onclick="removeSponsor(${item.id})" class="text-red-500 hover:text-red-700 bg-red-50 dark:bg-red-900/20 p-1 rounded transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr1);
            
            const tr2 = document.createElement("tr");
            tr2.className = "border-b border-gray-200 dark:border-gray-700 bg-amber-50/30 dark:bg-amber-900/10";
            tr2.innerHTML = `
                <td colspan="7" class="px-4 py-2">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">About Sponsor ${index + 1}</label>
                    <textarea rows="1" placeholder="Brief details about the sponsor..." oninput="updateSponsor(${item.id}, 'about', this.value)" class="w-full p-1.5 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded outline-none">${item.about}</textarea>
                </td>
            `;
            tbody.appendChild(tr2);
        });

        // Set total natively mapped to fund_sponsor field
        document.getElementById("fund_sponsor").value = total;
        syncJSON();
    }

    function updateSponsorTotals() {
        let total = 0;
        sponsorItems.forEach(i => total += (parseFloat(i.amount) || 0));
        document.getElementById("fund_sponsor").value = total;
    }

    function updateSponsor(id, field, value) {
        let item = sponsorItems.find(i => i.id === id);
        if(!item) return;
        item[field] = field === 'amount' ? (parseFloat(value) || 0) : value;
        if (field === 'amount') updateSponsorTotals();
        syncJSON();
    }
    function removeSponsor(id) { sponsorItems = sponsorItems.filter(i => i.id !== id); renderSponsorTable(); }

    function syncJSON() {
        document.getElementById("budgetJson").value = JSON.stringify(budgetItems);
        document.getElementById("sponsorJson").value = JSON.stringify(sponsorItems);
    }

    // Init Arrays
    document.addEventListener("DOMContentLoaded", () => {
        addBudgetRow();
        addSponsorRow();
    });

    function submitForm() {
        if(!document.getElementById('proposalForm').checkValidity()) {
            document.getElementById('proposalForm').reportValidity();
            return;
        }
        syncJSON();
        document.getElementById('proposalForm').submit();
    }
</script>

<?php require 'partials/footer.php'; ?>
