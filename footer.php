</main>
    
    <footer class="bg-slate-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date('Y'); ?> OLMS. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle with smooth animation
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
    </script>
</body>
</html>