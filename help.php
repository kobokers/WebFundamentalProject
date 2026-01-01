<?php
include("header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-4">Help Center</h1>
            <p class="text-blue-100 text-lg max-w-2xl mx-auto">Find answers to common questions and get the support you
                need</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12 max-w-5xl">

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <a href="#getting-started"
                class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow group">
                <div
                    class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors">
                    <i class="fas fa-rocket text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Getting Started</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">New to OLMS? Learn the basics</p>
            </a>

            <a href="#account"
                class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow group">
                <div
                    class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-colors">
                    <i class="fas fa-user-circle text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Account & Profile</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage your account settings</p>
            </a>

            <a href="#courses"
                class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow group">
                <div
                    class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors">
                    <i class="fas fa-book text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Courses & Learning</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Course enrollment and access</p>
            </a>
        </div>

        <!-- Getting Started Section -->
        <section id="getting-started" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                <i class="fas fa-rocket text-blue-600"></i>
                Getting Started
            </h2>
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">How do I create an account?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Click on "Join for Free" button on the homepage. Fill in
                        your details including name, email, and password. Verify your email address to activate your
                        account.</p>
                </div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">How do I enroll in a course?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Browse the course catalog, click on a course you're
                        interested in, and click "Enroll Now". Complete the payment process if it's a paid course.</p>
                </div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Are there free courses available?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Yes! We offer many free courses. Look for courses with
                        $0.00 price in the catalog or filter by "Free" in the search options.</p>
                </div>
            </div>
        </section>

        <!-- Account Section -->
        <section id="account" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                <i class="fas fa-user-circle text-green-600"></i>
                Account & Profile
            </h2>
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">How do I reset my password?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Click "Forgot Password" on the login page. Enter your
                        email address and we'll send you a reset link. Check your spam folder if you don't see it.</p>
                </div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">How do I update my profile picture?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">Go to your Dashboard → Edit Profile. Click on your
                        current avatar and upload a new image. Supported formats: JPG, PNG (max 2MB).</p>
                </div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Can I change my email address?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Yes, go to Edit Profile and update your email. You'll
                        need to verify the new email address before the change takes effect.</p>
                </div>
            </div>
        </section>

        <!-- Courses Section -->
        <section id="courses" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                <i class="fas fa-book text-purple-600"></i>
                Courses & Learning
            </h2>
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">How long do I have access to a course?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">Once enrolled, you have lifetime access to the course.
                        You can learn at your own pace and revisit materials anytime.</p>
                </div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Do I get a certificate after completing
                        a course?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Yes! After completing all modules and passing the
                        required quizzes, you can download your certificate from the course page.</p>
                </div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Can I download course materials?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Some materials may be available for download. Look for
                        the download icon next to downloadable resources in your course modules.</p>
                </div>
            </div>
        </section>

        <!-- Contact Support -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-8 text-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Still need help?</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Our support team is here to assist you</p>
            <a href="contact.php"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                <i class="fas fa-envelope"></i>
                Contact Support
            </a>
        </div>

    </div>
</div>

<?php
include("footer.php");
?>