<button id="theme-toggle" class="inline-flex items-center justify-center p-2 text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition-all duration-200">
    <!-- Sun Icon -->
    <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 2a1 1 0 011 1v2a1 1 0 11-2 0V3a1 1 0 011-1zM4.343 4.343a1 1 0 011.414 0l1.414 1.414a1 1 0 00 1.414-1.414L5.757 4.343a1 1 0 010-1.414zM2 10a1 1 0 011 1v2a1 1 0 11-2 0v-2a1 1 0 011-1zm13.657-5.657a1 1 0 00-1.414 1.414l1.414 1.414a1 1 0 001.414-1.414l-1.414-1.414zM18 10a1 1 0 011 1v2a1 1 0 11-2 0v-2a1 1 0 011-1zM9 18a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1zm5.657-4.343a1 1 0 001.414-1.414l-1.414-1.414a1 1 0 10-1.414 1.414l1.414 1.414zM5.757 15.657a1 1 0 00-1.414 1.414l1.414 1.414a1 1 0 001.414-1.414l-1.414-1.414z"></path>
    </svg>

    <!-- Moon Icon -->
    <svg id="theme-toggle-dark-icon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
    </svg>
</button>

<script>
    const themeToggle = document.getElementById('theme-toggle');
    const lightIcon = document.getElementById('theme-toggle-light-icon');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const htmlElement = document.documentElement;

    // Initialiser le thème au chargement
    function initTheme() {
        const isDark = localStorage.getItem('theme') === 'dark' || 
                      (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        if (isDark) {
            htmlElement.classList.add('dark');
            lightIcon.classList.remove('hidden');
            darkIcon.classList.add('hidden');
        } else {
            htmlElement.classList.remove('dark');
            lightIcon.classList.add('hidden');
            darkIcon.classList.remove('hidden');
        }
    }

    // Initialiser au chargement
    initTheme();

    // Toggle du thème
    themeToggle.addEventListener('click', function() {
        if (htmlElement.classList.contains('dark')) {
            // Passer au mode clair
            htmlElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
            lightIcon.classList.add('hidden');
            darkIcon.classList.remove('hidden');
        } else {
            // Passer au mode sombre
            htmlElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
            lightIcon.classList.remove('hidden');
            darkIcon.classList.add('hidden');
        }
    });
</script>