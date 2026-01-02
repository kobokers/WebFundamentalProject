<?php
session_start();
include("header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-4">Frequently Asked Questions</h1>
            <p class="text-blue-100 text-lg max-w-2xl mx-auto">Find quick answers to the most common questions</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12 max-w-4xl">

        <!-- FAQ Categories -->
        <div class="flex flex-wrap gap-3 justify-center mb-10">
            <button onclick="filterFAQ('all')"
                class="faq-filter active px-4 py-2 rounded-full text-sm font-medium bg-blue-600 text-white transition-colors">All</button>
            <button onclick="filterFAQ('general')"
                class="faq-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">General</button>
            <button onclick="filterFAQ('payment')"
                class="faq-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Payment</button>
            <button onclick="filterFAQ('courses')"
                class="faq-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Courses</button>
            <button onclick="filterFAQ('technical')"
                class="faq-filter px-4 py-2 rounded-full text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Technical</button>
        </div>

        <!-- FAQ Accordion -->
        <div class="space-y-4" id="faq-list">

            <!-- General FAQs -->
            <div class="faq-item" data-category="general">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">What is OLMS?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">OLMS (Online Learning Management System) is a
                            comprehensive platform for online education. Students can enroll in courses, watch lectures,
                            complete quizzes, and earn certificates. Lecturers can create and manage courses with ease.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item" data-category="general">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">Is OLMS free to use?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">Creating an account is completely free. We offer
                            both free and paid courses. Browse our catalog to find courses that fit your budget and
                            learning goals.</p>
                    </div>
                </div>
            </div>

            <!-- Payment FAQs -->
            <div class="faq-item" data-category="payment">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">What payment methods are
                            accepted?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">We accept major credit and debit cards including
                            Visa, MasterCard, and American Express. All payments are processed securely through our
                            payment gateway.</p>
                    </div>
                </div>
            </div>

            <div class="faq-item" data-category="payment">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">Can I get a refund?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">Yes, we offer a 30-day money-back guarantee. If
                            you're not satisfied with a course, contact our support team within 30 days of purchase for
                            a full refund.</p>
                    </div>
                </div>
            </div>

            <div class="faq-item" data-category="payment">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">Where can I find my payment
                            receipt?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">After completing a payment, you'll be redirected to
                            a receipt page where you can download or print your receipt. You can also access receipts
                            from your Dashboard.</p>
                    </div>
                </div>
            </div>

            <!-- Courses FAQs -->
            <div class="faq-item" data-category="courses">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">How long do I have access to a
                            course?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">Once you enroll in a course, you have lifetime
                            access. Learn at your own pace and revisit the materials whenever you want.</p>
                    </div>
                </div>
            </div>

            <div class="faq-item" data-category="courses">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">Do I get a certificate?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">Yes! Upon completing all course modules and required
                            quizzes, you'll receive a certificate of completion that you can download and share.</p>
                    </div>
                </div>
            </div>

            <!-- Technical FAQs -->
            <div class="faq-item" data-category="technical">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">I forgot my password. How do I reset
                            it?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">Click "Forgot Password" on the login page and enter
                            your email. You'll receive a password reset link. If you don't see the email, check your
                            spam folder.</p>
                    </div>
                </div>
            </div>

            <div class="faq-item" data-category="technical">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">Video not playing. What should I
                            do?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">Try refreshing the page, clearing your browser
                            cache, or using a different browser. Ensure you have a stable internet connection. If issues
                            persist, contact support.</p>
                    </div>
                </div>
            </div>

            <div class="faq-item" data-category="technical">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 text-left flex items-center justify-between"
                        onclick="toggleFAQ(this)">
                        <span class="font-semibold text-gray-900 dark:text-white">Which browsers are supported?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 dark:text-gray-400">OLMS works best on the latest versions of Chrome,
                            Firefox, Safari, and Edge. We recommend keeping your browser updated for the best
                            experience.</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Contact CTA -->
        <div class="mt-12 text-center">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Can't find what you're looking for?</p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="help.php"
                    class="inline-flex items-center gap-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium px-5 py-2.5 rounded-lg transition-colors">
                    <i class="fas fa-book"></i>
                    Help Center
                </a>
                <a href="contact.php"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors">
                    <i class="fas fa-envelope"></i>
                    Contact Us
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    function toggleFAQ(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('i');

        content.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }

    function filterFAQ(category) {
        const items = document.querySelectorAll('.faq-item');
        const buttons = document.querySelectorAll('.faq-filter');

        buttons.forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'active');
            btn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        });

        event.target.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
        event.target.classList.add('bg-blue-600', 'text-white', 'active');

        items.forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>

<?php
include("footer.php");
?>