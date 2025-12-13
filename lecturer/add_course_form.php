<?php
session_start();
include("../connection.php");

// --- Authentication and Authorization Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied. Please log in as a lecturer.";
    header("Location: ../auth/login.php");
    exit;
}

include("../header.php");
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4 lg:px-8 py-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="dashboard.php" class="hover:text-purple-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900 dark:text-white">Create Course</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-plus-circle text-purple-600"></i>
                Create New Course
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Start building your new course by entering the basic details below.</p>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="max-w-3xl mx-auto">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span class="text-green-700 dark:text-green-300"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="text-red-700 dark:text-red-300"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-8">
                <form action="add_course.php" method="POST" class="space-y-6" enctype="multipart/form-data">
                    
                    <!-- Basic Info Section -->
                    <div class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2">Basic Information</h2>
                        
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Title</label>
                            <input type="text" id="title" name="title" required
                                placeholder="e.g., Introduction to Python Programming"
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Description</label>
                            <textarea id="description" name="description" rows="5" required
                                placeholder="Provide a comprehensive overview of what students will learn..."
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors"></textarea>
                        </div>
                        
                        <div>
                            <label for="course_image" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Image (Optional)</label>
                            <input type="file" id="course_image" name="course_image" accept="image/png, image/jpeg, image/gif"
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Recommended size: 800x600px. Max size: 5MB.</p>
                        </div>
                    </div>
                    </div>

                    <!-- Details Section -->
                    <div class="space-y-6 pt-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2">Course Details</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Category</label>
                                <select id="category" name="category" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors appearance-none">
                                    <option value="" disabled selected>Select a category</option>
                                    <option value="General">General</option>
                                    <option value="Programming">Programming</option>
                                    <option value="Design">Design</option>
                                    <option value="Business">Business</option>
                                    <option value="Language">Language</option>
                                    <option value="Science">Science</option>
                                    <option value="Mathematics">Mathematics</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="level" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Difficulty Level</label>
                                <select id="level" name="level" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors appearance-none">
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>

                            <div>
                                <label for="language" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Language</label>
                                <input type="text" id="language" name="language" value="English" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                            </div>

                            <div>
                                <label for="duration" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Duration (Hours)</label>
                                <input type="number" id="duration" name="duration" min="1" placeholder="e.g., 10"
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="space-y-6 pt-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-2">Pricing</h2>
                        
                        <div>
                            <label for="fee" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Course Fee ($)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 text-lg">$</span>
                                <input type="number" id="fee" name="fee" step="0.01" min="0" value="0.00" required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:border-purple-500 transition-colors font-mono text-lg">
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Enter 0.00 to make this course free for students.</p>
                        </div>
                    </div>

                    <div class="pt-6 flex gap-4">
                        <a href="dashboard.php" class="flex-1 py-3 px-6 rounded-xl border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold text-center hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="flex-[2] bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                            Create Course <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>