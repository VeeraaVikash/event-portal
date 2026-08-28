<?php
require_once __DIR__ . '/../../includes/workflow.php';

// Standardize the body class if not provided by page
if(!isset($body_class)) {
    $body_class = "bg-gray-50 dark:bg-gray-900 flex flex-col min-h-screen text-gray-800 dark:text-gray-100 transition-colors duration-300";
} else {
    // Inject dark mode equivalents recursively if overridden
    if (strpos($body_class, 'bg-gray-50') !== false) {
        $body_class .= ' dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(ec_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'SRM Sustainable Event Connect' ?></title>

    <!-- Attaches the CSRF token to same-origin state-changing requests. -->
    <script>
        window.EC_CSRF = document.querySelector('meta[name="csrf-token"]').content;
        (function () {
            var nativeFetch = window.fetch;
            window.fetch = function (input, init) {
                init = init || {};
                var method = (init.method || 'GET').toUpperCase();
                var url = typeof input === 'string' ? input : (input && input.url) || '';
                var sameOrigin = !/^https?:\/\//i.test(url) || url.indexOf(window.location.origin) === 0;
                if (sameOrigin && method !== 'GET' && method !== 'HEAD') {
                    var headers = new Headers(init.headers || {});
                    if (!headers.has('X-CSRF-Token')) {
                        headers.set('X-CSRF-Token', window.EC_CSRF);
                    }
                    init.headers = headers;
                }
                return nativeFetch.call(this, input, init);
            };
        })();
    </script>
    
    <!-- Tailwind config for explicit Dark Mode -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- PDF Generation Runtime -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Instant Dark Mode Detection Script to prevent flashing -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Flatpickr Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css" id="flatpickr-dark-theme" disabled>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        html {
            scroll-behavior: smooth;
        }
        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .bg-campus {
            background-image: url('images/background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: calc(100vh - 64px);
            position: relative;
        }
        .bg-campus::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.4) 50%, rgba(0, 0, 0, 0.65) 100%);
            z-index: 1;
        }
        .dark .bg-campus::before {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.7) 50%, rgba(0, 0, 0, 0.8) 100%);
        }
        .content-layer {
            position: relative;
            z-index: 2;
        }
        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body class="<?= htmlspecialchars($body_class) ?>">
