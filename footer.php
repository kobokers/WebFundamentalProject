</main>



</main>



<footer class="bg-slate-800 dark:bg-gray-900 text-white py-6 mt-auto transition-colors duration-200">
    <div class="container mx-auto px-4 text-center">
        <p>&copy; <?php echo date('Y'); ?> OLMS. All rights reserved.</p>
        <p>Developed by <a href="https://github.com/kobokers" target="_blank" class="text-blue-500 hover:text-blue-600">Kobokers</a></p>
    </div>
</footer>

<script>
// Mobile menu toggle 
const navToggle = document.getElementById('navToggle');
const mobileMenu = document.getElementById('mobileMenu');

if (navToggle && mobileMenu) {
    // Add transition class
    mobileMenu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    mobileMenu.style.transform = 'translateY(-10px)';
    mobileMenu.style.opacity = '0';

    navToggle.addEventListener('click', (e) => {
        e.stopPropagation();

        if (mobileMenu.classList.contains('hidden')) {
            // Show menu
            mobileMenu.classList.remove('hidden');
            setTimeout(() => {
                mobileMenu.style.transform = 'translateY(0)';
                mobileMenu.style.opacity = '1';
            }, 10);
        } else {
            // Hide menu
            mobileMenu.style.transform = 'translateY(-10px)';
            mobileMenu.style.opacity = '0';
            setTimeout(() => {
                mobileMenu.classList.add('hidden');
            }, 300);
        }
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
            if (!mobileMenu.classList.contains('hidden')) {
                mobileMenu.style.transform = 'translateY(-10px)';
                mobileMenu.style.opacity = '0';
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 300);
            }
        }
    });
}

// Dark mode toggle logic
var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
var themeToggleDarkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
var themeToggleLightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');

// Change the icons inside the button based on previous settings
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    if(themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
    if(themeToggleLightIconMobile) themeToggleLightIconMobile.classList.remove('hidden');
} else {
    if(themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
    if(themeToggleDarkIconMobile) themeToggleDarkIconMobile.classList.remove('hidden');
}

var themeToggleBtn = document.getElementById('theme-toggle');
var themeToggleBtnMobile = document.getElementById('theme-toggle-mobile');

function toggleDarkMode() {
    console.log('Toggling dark mode');
    // toggle icons inside button
    if(themeToggleDarkIcon) themeToggleDarkIcon.classList.toggle('hidden');
    if(themeToggleLightIcon) themeToggleLightIcon.classList.toggle('hidden');
    if(themeToggleDarkIconMobile) themeToggleDarkIconMobile.classList.toggle('hidden');
    if(themeToggleLightIconMobile) themeToggleLightIconMobile.classList.toggle('hidden');

    // if set via local storage previously
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
            console.log('Set to dark mode (from local storage)');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
            console.log('Set to light mode (from local storage)');
        }

    // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
            console.log('Set to light mode (default)');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
            console.log('Set to dark mode (default)');
        }
    }
}

if(themeToggleBtn) {
    themeToggleBtn.addEventListener('click', toggleDarkMode);
}

if(themeToggleBtnMobile) {
    themeToggleBtnMobile.addEventListener('click', toggleDarkMode);
}
</script>
</body>

</html>