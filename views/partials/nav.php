<!-- Navigation Bar -->
<nav
    class="bg-[#1e40af] dark:bg-gray-900 border-b dark:border-gray-800 text-white shadow-md sticky top-0 z-50 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo and Title -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <img src="images/logo.jpg" alt="SRM Logo"
                    class="h-8 w-8 sm:h-10 sm:w-10 p-0.5 sm:p-1 bg-white rounded-full">
                <span class="font-bold text-base sm:text-lg md:text-xl tracking-wide truncate">SRM Event Connect</span>
            </div>

            <!-- Utilities & User Navigation -->
            <div class="flex items-center space-x-3 sm:space-x-5 relative">

                <!-- Dark Mode Toggle Button -->
                <button onclick="toggleTheme()"
                    class="p-2 rounded-full hover:bg-white/10 transition-colors focus:outline-none"
                    aria-label="Toggle Dark Mode">
                    <!-- Sun Icon (visible in dark mode) -->
                    <svg class="h-5 w-5 hidden dark:block text-yellow-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon Icon (visible in light mode) -->
                    <svg class="h-5 w-5 block dark:hidden text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                </button>

                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>

                    <!-- Dropdown Menu Trigger -->
                    <div class="relative group">
                        <button
                            class="flex items-center justify-center p-2 rounded-full hover:bg-white/10 transition focus:outline-none"
                            onclick="toggleDropdown()">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>

                        <!-- Dropdown content -->
                        <div id="userDropdown"
                            class="hidden absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-4 z-50 transition-colors">
                            <div
                                class="px-4 pb-4 border-b border-gray-200 dark:border-gray-700 text-center flex flex-col items-center">
                                <div
                                    class="h-12 w-12 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xl mb-2">
                                    <?= strtoupper(substr(htmlspecialchars($_SESSION["full_name"]), 0, 1)) ?>
                                </div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    <?= htmlspecialchars($_SESSION["full_name"]) ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($_SESSION["email"]) ?></p>
                            </div>
                            <div class="px-2 pt-2">
                                <a href="dashboard.php"
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition mb-1 flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    Dashboard
                                </a>
                                <!-- Proposals feature link placeholder -->
                                <a href="proposal.php"
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition mb-1 flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    New Proposal
                                </a>
                                <a href="logout.php"
                                    class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="flex items-center space-x-2">
                        <a href="#about"
                            class="px-2.5 sm:px-4 py-1.5 sm:py-2 text-white/90 hover:text-white hover:bg-white/10 text-xs sm:text-sm font-semibold rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 whitespace-nowrap">
                            About
                        </a>
                        <button onclick="openModal('loginModal')"
                            class="px-3 sm:px-5 py-1.5 sm:py-2 bg-white/10 hover:bg-white/20 dark:bg-gray-800 dark:hover:bg-gray-700 text-white text-xs sm:text-sm font-semibold rounded-md transition border border-white/20 dark:border-gray-600 shadow-sm whitespace-nowrap">
                            Login
                        </button>
                        <button onclick="openModal('signupModal')"
                            class="px-3 sm:px-5 py-1.5 sm:py-2 bg-[#22c55e] dark:bg-emerald-600 hover:bg-green-600 dark:hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold rounded-md transition shadow-sm whitespace-nowrap">
                            Sign Up
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    // Toggle theme logic
    function toggleTheme() {
        var html = document.documentElement;
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }

    // Dropdown Logic
    function toggleDropdown() {
        var el = document.getElementById('userDropdown');
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }

    // Close dropdown when clicking outside
    window.onclick = function (event) {
        // Dropdown checks...
        if (!event.target.closest('.group')) {
            var el = document.getElementById('userDropdown');
            if (el && !el.classList.contains('hidden')) {
                el.classList.add('hidden');
            }
        }

        // Modal overlapping checks
        const loginModal = document.getElementById('loginModal');
        const signupModal = document.getElementById('signupModal');
        if (loginModal && event.target === loginModal) {
            closeModal('loginModal');
        }
        if (signupModal && event.target === signupModal) {
            closeModal('signupModal');
        }
    }
</script>