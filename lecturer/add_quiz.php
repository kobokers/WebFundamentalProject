<?php
session_start();
include("../connection.php");

// --- Authentication ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied. Only lecturers can create quizzes.";
    header("Location: ../auth/login.php");
    exit;
}

$lecturer_id = $_SESSION['user_id'];
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

if ($module_id <= 0) {
    $_SESSION['error'] = "Invalid module selected.";
    header("Location: dashboard.php");
    exit;
}

// Verify this module belongs to a course owned by this lecturer
$verify_query = "SELECT m.id, m.title as module_title, c.title as course_title, c.id as course_id 
                 FROM modules m 
                 JOIN courses c ON m.course_id = c.id 
                 WHERE m.id = '$module_id' AND c.lecturer_id = '$lecturer_id'";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Module not found or access denied.";
    header("Location: dashboard.php");
    exit;
}

$module_data = mysqli_fetch_assoc($verify_result);

// Check if quiz already exists for this module
$existing_quiz = "SELECT id FROM quizzes WHERE module_id = '$module_id' LIMIT 1";
$existing_result = mysqli_query($conn, $existing_quiz);
if (mysqli_num_rows($existing_result) > 0) {
    $quiz = mysqli_fetch_assoc($existing_result);
    header("Location: edit_quiz.php?quiz_id=" . $quiz['id']);
    exit;
}

// --- Handle Quiz Creation ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quiz_title = mysqli_real_escape_string($conn, $_POST['quiz_title']);
    $passing_score = (int)$_POST['passing_score'];
    $questions = $_POST['questions'] ?? [];
    
    if (empty($quiz_title)) {
        $_SESSION['error'] = "Quiz title is required.";
    } elseif (count($questions) < 1) {
        $_SESSION['error'] = "At least one question is required.";
    } else {
        // Create quiz
        $create_quiz = "INSERT INTO quizzes (module_id, title, passing_score) VALUES ('$module_id', '$quiz_title', '$passing_score')";
        
        if (mysqli_query($conn, $create_quiz)) {
            $quiz_id = mysqli_insert_id($conn);
            
            // Add questions
            $order = 1;
            foreach ($questions as $q) {
                $question_text = mysqli_real_escape_string($conn, $q['question']);
                $option_a = mysqli_real_escape_string($conn, $q['option_a']);
                $option_b = mysqli_real_escape_string($conn, $q['option_b']);
                $option_c = mysqli_real_escape_string($conn, $q['option_c']);
                $option_d = mysqli_real_escape_string($conn, $q['option_d']);
                $correct = mysqli_real_escape_string($conn, $q['correct_answer']);
                
                $insert_q = "INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, question_order) 
                             VALUES ('$quiz_id', '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct', '$order')";
                mysqli_query($conn, $insert_q);
                $order++;
            }
            
            $_SESSION['success'] = "Quiz created successfully with " . count($questions) . " questions!";
            header("Location: module_setup.php?course_id=" . $module_data['course_id']);
            exit;
        } else {
            $_SESSION['error'] = "Failed to create quiz: " . mysqli_error($conn);
        }
    }
}

include("../header.php");
?>

    <div class="container mx-auto p-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-purple-800 dark:text-purple-300">Create Quiz</h1>
            <p class="text-gray-600 dark:text-gray-400">
                For Module: <strong><?php echo htmlspecialchars($module_data['module_title']); ?></strong><br>
                Course: <strong><?php echo htmlspecialchars($module_data['course_title']); ?></strong>
            </p>
            <a href="module_setup.php?course_id=<?php echo $module_data['course_id']; ?>" 
               class="text-purple-500 hover:text-purple-700 dark:text-purple-400 mt-2 inline-block">← Back to Module Setup</a>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="mb-6">
                <label for="quiz_title" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Quiz Title:</label>
                <input type="text" id="quiz_title" name="quiz_title" required
                       placeholder="e.g., Module 1 Assessment"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div class="mb-6">
                <label for="passing_score" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Passing Score (%):</label>
                <input type="number" id="passing_score" name="passing_score" min="0" max="100" value="60" required
                       class="w-32 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <hr class="my-6 border-gray-300 dark:border-gray-700">
            
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-question-circle mr-2"></i>Questions
            </h3>
            
            <div id="questions-container" class="space-y-6">
                <!-- Question 1 (default) -->
                <div class="question-block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600" data-question="1">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-semibold text-purple-600 dark:text-purple-400">Question 1</span>
                    </div>
                    <div class="mb-3">
                        <textarea name="questions[0][question]" rows="2" required placeholder="Enter your question..."
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="questions[0][correct_answer]" value="A" required class="text-green-500">
                            <input type="text" name="questions[0][option_a]" placeholder="Option A" required
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="radio" name="questions[0][correct_answer]" value="B" class="text-green-500">
                            <input type="text" name="questions[0][option_b]" placeholder="Option B" required
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="radio" name="questions[0][correct_answer]" value="C" class="text-green-500">
                            <input type="text" name="questions[0][option_c]" placeholder="Option C" required
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="radio" name="questions[0][correct_answer]" value="D" class="text-green-500">
                            <input type="text" name="questions[0][option_d]" placeholder="Option D" required
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><i class="fas fa-info-circle mr-1"></i>Select the radio button next to the correct answer</p>
                </div>
            </div>
            
            <button type="button" onclick="addQuestion()" 
                    class="mt-4 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                <i class="fas fa-plus mr-2"></i>Add Question
            </button>
            
            <hr class="my-6 border-gray-300 dark:border-gray-700">
            
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition text-lg">
                <i class="fas fa-save mr-2"></i>Create Quiz
            </button>
        </form>
    </div>

<script>
let questionCount = 1;

function addQuestion() {
    questionCount++;
    const container = document.getElementById('questions-container');
    const questionHtml = `
        <div class="question-block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600" data-question="${questionCount}">
            <div class="flex justify-between items-center mb-3">
                <span class="font-semibold text-purple-600 dark:text-purple-400">Question ${questionCount}</span>
                <button type="button" onclick="removeQuestion(this)" class="text-red-500 hover:text-red-700 text-sm">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="mb-3">
                <textarea name="questions[${questionCount-1}][question]" rows="2" required placeholder="Enter your question..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <input type="radio" name="questions[${questionCount-1}][correct_answer]" value="A" required class="text-green-500">
                    <input type="text" name="questions[${questionCount-1}][option_a]" placeholder="Option A" required
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="radio" name="questions[${questionCount-1}][correct_answer]" value="B" class="text-green-500">
                    <input type="text" name="questions[${questionCount-1}][option_b]" placeholder="Option B" required
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="radio" name="questions[${questionCount-1}][correct_answer]" value="C" class="text-green-500">
                    <input type="text" name="questions[${questionCount-1}][option_c]" placeholder="Option C" required
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="radio" name="questions[${questionCount-1}][correct_answer]" value="D" class="text-green-500">
                    <input type="text" name="questions[${questionCount-1}][option_d]" placeholder="Option D" required
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400"><i class="fas fa-info-circle mr-1"></i>Select the radio button next to the correct answer</p>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', questionHtml);
}

function removeQuestion(btn) {
    if (document.querySelectorAll('.question-block').length > 1) {
        btn.closest('.question-block').remove();
        // Renumber questions
        document.querySelectorAll('.question-block').forEach((block, index) => {
            block.querySelector('span').textContent = `Question ${index + 1}`;
        });
    } else {
        alert('You must have at least one question.');
    }
}
</script>
</main>
<?php include("../footer.php"); ?>
