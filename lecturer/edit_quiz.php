<?php
session_start();
include("../connection.php");

// --- Authentication ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    $_SESSION['error'] = "Access denied. Only lecturers can edit quizzes.";
    header("Location: ../auth/login.php");
    exit;
}

$lecturer_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id <= 0) {
    $_SESSION['error'] = "Invalid quiz selected.";
    header("Location: dashboard.php");
    exit;
}

// Verify this quiz belongs to a course owned by this lecturer
$verify_query = "SELECT q.id, q.title as quiz_title, q.passing_score, m.id as module_id, m.title as module_title, c.title as course_title, c.id as course_id 
                 FROM quizzes q 
                 JOIN modules m ON q.module_id = m.id 
                 JOIN courses c ON m.course_id = c.id 
                 WHERE q.id = '$quiz_id' AND c.lecturer_id = '$lecturer_id'";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    $_SESSION['error'] = "Quiz not found or access denied.";
    header("Location: dashboard.php");
    exit;
}

$quiz_data = mysqli_fetch_assoc($verify_result);

// Fetch existing questions
$questions_query = "SELECT * FROM quiz_questions WHERE quiz_id = '$quiz_id' ORDER BY question_order ASC";
$questions_result = mysqli_query($conn, $questions_query);
$questions = [];
while ($q = mysqli_fetch_assoc($questions_result)) {
    $questions[] = $q;
}

// --- Handle Quiz Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_quiz'])) {
        // Delete quiz and its questions
        mysqli_query($conn, "DELETE FROM quiz_questions WHERE quiz_id = '$quiz_id'");
        mysqli_query($conn, "DELETE FROM quiz_attempts WHERE quiz_id = '$quiz_id'");
        mysqli_query($conn, "DELETE FROM quizzes WHERE id = '$quiz_id'");
        
        $_SESSION['success'] = "Quiz deleted successfully!";
        header("Location: module_setup.php?course_id=" . $quiz_data['course_id']);
        exit;
    }
    
    $quiz_title = mysqli_real_escape_string($conn, $_POST['quiz_title']);
    $passing_score = (int)$_POST['passing_score'];
    $new_questions = $_POST['questions'] ?? [];
    
    if (empty($quiz_title)) {
        $_SESSION['error'] = "Quiz title is required.";
    } elseif (count($new_questions) < 1) {
        $_SESSION['error'] = "At least one question is required.";
    } else {
        // Update quiz
        $update_quiz = "UPDATE quizzes SET title = '$quiz_title', passing_score = '$passing_score' WHERE id = '$quiz_id'";
        
        if (mysqli_query($conn, $update_quiz)) {
            // Delete old questions and add new ones
            mysqli_query($conn, "DELETE FROM quiz_questions WHERE quiz_id = '$quiz_id'");
            
            $order = 1;
            foreach ($new_questions as $q) {
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
            
            $_SESSION['success'] = "Quiz updated successfully with " . count($new_questions) . " questions!";
            header("Location: module_setup.php?course_id=" . $quiz_data['course_id']);
            exit;
        } else {
            $_SESSION['error'] = "Failed to update quiz: " . mysqli_error($conn);
        }
    }
}

include("../header.php");
?>

    <div class="container mx-auto p-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-purple-800 dark:text-purple-300">Edit Quiz</h1>
            <p class="text-gray-600 dark:text-gray-400">
                For Module: <strong><?php echo htmlspecialchars($quiz_data['module_title']); ?></strong><br>
                Course: <strong><?php echo htmlspecialchars($quiz_data['course_title']); ?></strong>
            </p>
            <a href="module_setup.php?course_id=<?php echo $quiz_data['course_id']; ?>" 
               class="text-purple-500 hover:text-purple-700 dark:text-purple-400 mt-2 inline-block">← Back to Module Setup</a>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="mb-6">
                <label for="quiz_title" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Quiz Title:</label>
                <input type="text" id="quiz_title" name="quiz_title" required
                       value="<?php echo htmlspecialchars($quiz_data['quiz_title']); ?>"
                       placeholder="e.g., Module 1 Assessment"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div class="mb-6">
                <label for="passing_score" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Passing Score (%):</label>
                <input type="number" id="passing_score" name="passing_score" min="0" max="100" 
                       value="<?php echo (int)$quiz_data['passing_score']; ?>" required
                       class="w-32 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <hr class="my-6 border-gray-300 dark:border-gray-700">
            
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-question-circle mr-2"></i>Questions
            </h3>
            
            <div id="questions-container" class="space-y-6">
                <?php if (count($questions) > 0): ?>
                    <?php foreach ($questions as $index => $q): ?>
                    <div class="question-block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600" data-question="<?php echo $index + 1; ?>">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold text-purple-600 dark:text-purple-400">Question <?php echo $index + 1; ?></span>
                            <?php if ($index > 0): ?>
                            <button type="button" onclick="removeQuestion(this)" class="text-red-500 hover:text-red-700 text-sm">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <textarea name="questions[<?php echo $index; ?>][question]" rows="2" required placeholder="Enter your question..."
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white"><?php echo htmlspecialchars($q['question']); ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="questions[<?php echo $index; ?>][correct_answer]" value="A" required 
                                       class="text-green-500" <?php echo ($q['correct_answer'] === 'A') ? 'checked' : ''; ?>>
                                <input type="text" name="questions[<?php echo $index; ?>][option_a]" placeholder="Option A" required
                                       value="<?php echo htmlspecialchars($q['option_a']); ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="questions[<?php echo $index; ?>][correct_answer]" value="B" 
                                       class="text-green-500" <?php echo ($q['correct_answer'] === 'B') ? 'checked' : ''; ?>>
                                <input type="text" name="questions[<?php echo $index; ?>][option_b]" placeholder="Option B" required
                                       value="<?php echo htmlspecialchars($q['option_b']); ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="questions[<?php echo $index; ?>][correct_answer]" value="C" 
                                       class="text-green-500" <?php echo ($q['correct_answer'] === 'C') ? 'checked' : ''; ?>>
                                <input type="text" name="questions[<?php echo $index; ?>][option_c]" placeholder="Option C" required
                                       value="<?php echo htmlspecialchars($q['option_c']); ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="questions[<?php echo $index; ?>][correct_answer]" value="D" 
                                       class="text-green-500" <?php echo ($q['correct_answer'] === 'D') ? 'checked' : ''; ?>>
                                <input type="text" name="questions[<?php echo $index; ?>][option_d]" placeholder="Option D" required
                                       value="<?php echo htmlspecialchars($q['option_d']); ?>"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><i class="fas fa-info-circle mr-1"></i>Select the radio button next to the correct answer</p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default empty question if no questions exist -->
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
                <?php endif; ?>
            </div>
            
            <button type="button" onclick="addQuestion()" 
                    class="mt-4 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                <i class="fas fa-plus mr-2"></i>Add Question
            </button>
            
            <hr class="my-6 border-gray-300 dark:border-gray-700">
            
            <div class="flex justify-between items-center">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition text-lg">
                    <i class="fas fa-save mr-2"></i>Update Quiz
                </button>
                
                <button type="submit" name="delete_quiz" value="1" 
                        onclick="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.');"
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg transition">
                    <i class="fas fa-trash mr-2"></i>Delete Quiz
                </button>
            </div>
        </form>
    </div>

<script>
let questionCount = <?php echo max(count($questions), 1); ?>;

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
