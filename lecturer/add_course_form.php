<?php
session_start();
include("../connection.php");

// --- Authentication and Authorization Check ---
// Ensures only logged-in lecturers can access this form.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied. Please log in as a lecturer to register a course.";
    header("Location: ../auth/login.php");
    exit;
}

// NOW include header after authentication check
include("../header.php");
?>

<body>
    <div class="container mx-auto p-8">
        <header class="mb-8 text-center">
            <h1 class="text-4xl font-extrabold text-blue-800 dark:text-blue-300">New Course Registration</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Enter the details for your course below.</p>
        </header>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="max-w-xl mx-auto p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="max-w-xl mx-auto p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <div class="max-w-xl mx-auto mt-6 p-8 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-200">
            <form action="add_course.php" method="POST">

                <div class="mb-5">
                    <label for="title" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Course Title:</label>
                    <input type="text" id="title" name="title" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Advanced JavaScript Frameworks">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="level" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Difficulty Level:</label>
                        <select id="level" name="level" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label for="category" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Category:</label>
                        <select id="category" name="category" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="General">General</option>
                            <option value="Programming">Programming</option>
                            <option value="Design">Design</option>
                            <option value="Business">Business</option>
                            <option value="Language">Language</option>
                            <option value="Science">Science</option>
                            <option value="Mathematics">Mathematics</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label for="language" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Language:</label>
                    <input type="text" id="language" name="language" value="English" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., English">
                </div>

                <div class="mb-5">
                    <label for="description" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Course Description:</label>
                    <textarea id="description" name="description" rows="4" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white"
                        placeholder="Provide a brief overview of the course content and objectives."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="fee" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Course Fee ($):</label>
                        <input type="number" id="fee" name="fee" step="0.01" min="0" value="0.00" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter 0.00 for a free course.</p>
                    </div>
                    <div>
                        <label for="duration" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Duration (hours):</label>
                        <input type="number" id="duration" name="duration" min="1" placeholder="e.g., 10"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg dark:bg-gray-700 dark:text-white">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Estimated time to complete.</p>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200 text-xl shadow-lg">
                    <i class="fas fa-save mr-2"></i> Register and Continue to Modules
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>

<?php include("../footer.php"); ?>