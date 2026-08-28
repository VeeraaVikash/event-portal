<!-- Modals Container -->
<style>
    .hr-text {
        display: flex;
        align-items: center;
        text-align: center;
        color: #9ca3af;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 1.5rem 0;
    }
    .hr-text::before, .hr-text::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e5e7eb;
    }
    .hr-text::before { margin-right: .5em; }
    .hr-text::after { margin-left: .5em; }
    
    /* Dark Mode border adjustments for hr-text */
    .dark .hr-text::before, .dark .hr-text::after { border-bottom-color: #374151; }
</style>

<!-- Login Modal Overlay -->
<div id="loginModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300 p-4">
    <!-- Modal Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md overflow-hidden relative transform transition-all border border-transparent dark:border-gray-700">
        <!-- Close Button -->
        <button onclick="closeModal('loginModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="px-8 py-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Sign In</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Use your SRMIST credentials</p>
            </div>

            <div class="hr-text">Email Login</div>

            <?php if (isset($_SESSION['error_login'])): ?>
                <div class="mb-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-3 rounded text-sm text-red-700 dark:text-red-400">
                    <?= htmlspecialchars($_SESSION['error_login']) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_signup'])): ?>
                <div class="mb-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-3 rounded text-sm text-green-700 dark:text-green-400">
                    <?= htmlspecialchars($_SESSION['success_signup']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="space-y-5">
                <div>
                    <label for="login_email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email Address</label>
                    <input id="login_email" name="email" type="email" required placeholder="your.email@srmist.edu.in" 
                           class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                </div>
                <div>
                    <label for="login_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Password</label>
                    <input id="login_password" name="password" type="password" required placeholder="••••••••" 
                           class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 bg-[#1d4ed8] hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors cursor-pointer text-sm">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Signup Modal Overlay -->
<div id="signupModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300 p-4">
    <!-- Modal Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md overflow-hidden relative transform transition-all border border-transparent dark:border-gray-700 max-h-[90vh] overflow-y-auto hidden-scrollbar">
        <!-- Close Button -->
        <button onclick="closeModal('signupModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="px-8 py-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create New User</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Register your SRMIST account</p>
            </div>

            <?php if (isset($_SESSION['error_signup'])): ?>
                <div class="mb-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-3 rounded text-sm text-red-700 dark:text-red-400">
                    <?= htmlspecialchars($_SESSION['error_signup']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="signup.php" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Full Name</label>
                    <input name="full_name" type="text" required placeholder="Dr. Jane Doe" 
                           class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email Address</label>
                    <input name="email" type="email" pattern="^[a-zA-Z0-9._%+-]+@srmist\.edu\.in$" title="Use your @srmist.edu.in email" required placeholder="your.email@srmist.edu.in" 
                           class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Password</label>
                    <input name="password" type="password" required placeholder="••••••••" 
                           class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Phone Number (Optional)</label>
                    <input name="phone_number" type="text" placeholder="e.g., +91-XXXXXXXXXX" 
                           class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Role</label>
                    <select name="role" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg shadow-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm pointer-events-none transition-colors" readonly>
                        <option value="Convener" selected>Convener</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Department</label>
                    <select name="department" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-colors">
                        <option value="Computing Technologies" selected>Computing Technologies</option>
                        <option value="Networking and Communications">Networking and Communications</option>
                        <option value="Computational Intelligence">Computational Intelligence</option>
                        <option value="Computer Science and Engineering">Computer Science and Engineering</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Designation</label>
                    <select name="designation" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-colors">
                        <option value="">Select Designation</option>
                        <option value="Prof">Professor</option>
                        <option value="assistant prof">Assistant Professor</option>
                        <option value="associate prof">Associate Professor</option>
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-2.5 px-4 bg-[#22c55e] hover:bg-green-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-semibold rounded-lg shadow-sm transition-colors cursor-pointer text-sm">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden'; 
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const modalToOpen = urlParams.get('modal');
        if (modalToOpen === 'login') {
            openModal('loginModal');
        } else if (modalToOpen === 'signup') {
            openModal('signupModal');
        }
    });
</script>
