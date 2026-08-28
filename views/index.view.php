<?php 
$page_title = 'SRM Sustainable Event Connect';
require 'partials/head.php'; 
require 'partials/nav.php'; 
?>

<!-- Main Content Area with Background Image -->
<div class="bg-campus flex-grow flex items-center">
    <div class="content-layer max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <!-- Left Side Content -->
            <div class="text-white space-y-6">
                <div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight text-shadow">
                        SRM Institute of <br>
                        Science and <br>
                        <span class="text-[#60a5fa]">Technology</span>
                    </h1>
                </div>
                
                <div>
                    <h2 class="text-2xl md:text-3xl font-semibold text-shadow">
                        Faculty of Engineering and <br>
                        Technology
                    </h2>
                </div>
                
                <p class="text-sm md:text-base text-gray-200 max-w-md leading-relaxed text-shadow">
                    A powerful platform developed by Department of Computing Technology to revolutionize event management. Streamline creation, submission, and review processes for enhanced efficiency and collaboration.
                </p>
            </div>

            <!-- Right Side Content -->
            <div class="text-white text-right space-y-2 mt-12 md:mt-0">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-[0.2em] leading-tight text-shadow">
                    SUSTAINABLE <br>
                    EVENT CONNECT
                </h1>
                <p class="text-lg md:text-xl text-gray-200 mt-4 text-shadow">
                    A Sustainable event management portal
                </p>
            </div>

        </div>
    </div>
</div>

<?php require 'partials/about.php'; ?>

<?php require 'partials/modals.php'; ?>
<?php require 'partials/footer.php'; ?>
