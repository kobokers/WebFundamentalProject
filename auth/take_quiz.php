<?php
session_start();
include("../connection.php");

// --- Authentication ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to take quizzes.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id <= 0) {
    $_SESSION['error'] = "Invalid quiz selected.";
    header("Location: dashboard.php");
    exit;
}

// Get quiz details
$quiz_query = "SELECT q.*, m.title as module_title, m.course_id, c.title as course_title 
               FROM quizzes q 
               JOIN modules m ON q.module_id = m.id 
               JOIN courses c ON m.course_id = c.id 
               WHERE q.id = '$quiz_id'";
$quiz_result = mysqli_query($conn, $quiz_query);

if (mysqli_num_rows($quiz_result) == 0) {
    $_SESSION['error'] = "Quiz not found.";
    header("Location: dashboard.php");
    exit;
}

$quiz = mysqli_fetch_assoc($quiz_result);

// Check if student is enrolled in this course
$enroll_check = "SELECT * FROM enrollment WHERE user_id = '$user_id' AND course_id = '{$quiz['course_id']}' AND payment_status = 'paid'";
$enroll_result = mysqli_query($conn, $enroll_check);

if (mysqli_num_rows($enroll_result) == 0 && $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "You must be enrolled in this course to take the quiz.";
    header("Location: dashboard.php");
    exit;
}

// Get questions
$questions_query = "SELECT * FROM quiz_questions WHERE quiz_id = '$quiz_id' ORDER BY question_order ASC";
$questions_result = mysqli_query($conn, $questions_query);
$questions = [];
while ($q = mysqli_fetch_assoc($questions_result)) {
    $questions[] = $q;
}

$total_questions = count($questions);

// Check previous attempts
$attempts_query = "SELECT * FROM quiz_attempts WHERE quiz_id = '$quiz_id' AND user_id = '$user_id' ORDER BY attempted_at DESC";
$attempts_result = mysqli_query($conn, $attempts_query);
$previous_attempts = [];
while ($a = mysqli_fetch_assoc($attempts_result)) {
    $previous_attempts[] = $a;
}

// --- Handle Quiz Submission ---
$show_results = false;
$score = 0;
$passed = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_quiz'])) {
    $correct_count = 0;
    
    foreach ($questions as $q) {
        $answer_key = 'answer_' . $q['id'];
        if (isset($_POST[$answer_key]) && strtoupper($_POST[$answer_key]) === strtoupper($q['correct_answer'])) {
            $correct_count++;
        }
    }
    
    $score = ($total_questions > 0) ? round(($correct_count / $total_questions) * 100) : 0;
    $passed = ($score >= $quiz['passing_score']) ? 1 : 0;
    
    // Record attempt
    $record_query = "INSERT INTO quiz_attempts (quiz_id, user_id, score, passed) VALUES ('$quiz_id', '$user_id', '$score', '$passed')";
    mysqli_query($conn, $record_query);
    
    $show_results = true;
}

include("../header.php");
?>

    <div class="container mx-auto p-8 max-w-3xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-purple-800 dark:text-purple-300"><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p class="text-gray-600 dark:text-gray-400">
                Module: <strong><?php echo htmlspecialchars($quiz['module_title']); ?></strong><br>
                Course: <strong><?php echo htmlspecialchars($quiz['course_title']); ?></strong>
            </p>
            <a href="course_view.php?id=<?php echo $quiz['course_id']; ?>" 
               class="text-purple-500 hover:text-purple-700 dark:text-purple-400 mt-2 inline-block">← Back to Course</a>
        </header>

        <?php if ($show_results): ?>
        <!-- Results Section -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 text-center">
            <div class="text-6xl mb-4 <?php echo $passed ? 'text-green-500' : 'text-red-500'; ?>">
                <?php echo $passed ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>'; ?>
            </div>
            <h2 class="text-3xl font-bold <?php echo $passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?> mb-2">
                <?php echo $passed ? 'Congratulations!' : 'Keep Trying!'; ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                <?php echo $passed ? 'You passed the quiz!' : 'You did not pass this time.'; ?>
            </p>
            
            <div class="text-5xl font-extrabold text-gray-800 dark:text-white my-6"><?php echo $score; ?>%</div>
            
            <p class="text-gray-500 dark:text-gray-400">
                Passing Score: <?php echo $quiz['passing_score']; ?>%
            </p>
            
            <div class="mt-8 flex gap-4 justify-center">
                <?php if (!$passed): ?>
                <a href="take_quiz.php?quiz_id=<?php echo $quiz_id; ?>" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-redo mr-2"></i>Try Again
                </a>
                <?php endif; ?>
                <a href="course_view.php?id=<?php echo $quiz['course_id']; ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                    Back to Course
                </a>
            </div>
        </div>
        
        <?php else: ?>
        
        <!-- Previous Attempts -->
        <?php if (count($previous_attempts) > 0): ?>
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
            <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">
                <i class="fas fa-history mr-2"></i>Previous Attempts
            </h3>
            <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                <?php foreach (array_slice($previous_attempts, 0, 3) as $attempt): ?>
                <li>
                    Score: <strong><?php echo $attempt['score']; ?>%</strong> 
                    (<?php echo $attempt['passed'] ? '✅ Passed' : '❌ Failed'; ?>)
                    - <?php echo date('M j, Y g:i A', strtotime($attempt['attempted_at'])); ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Quiz Form -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-6">
                <span class="text-gray-600 dark:text-gray-400"><?php echo $total_questions; ?> Questions</span>
                <span class="text-purple-600 dark:text-purple-400 font-semibold">Passing: <?php echo $quiz['passing_score']; ?>%</span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="submit_quiz" value="1">
                
                <div class="space-y-8">
                    <?php foreach ($questions as $index => $q): ?>
                    <div class="question-item">
                        <p class="font-semibold text-gray-800 dark:text-white mb-3">
                            <span class="text-purple-600 dark:text-purple-400">Q<?php echo $index + 1; ?>.</span>
                            <?php echo htmlspecialchars($q['question']); ?>
                        </p>
                        <div class="space-y-2 ml-4">
                            <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <input type="radio" name="answer_<?php echo $q['id']; ?>" value="A" required class="mr-3 text-purple-600">
                                <span class="text-gray-700 dark:text-gray-300"><strong>A.</strong> <?php echo htmlspecialchars($q['option_a']); ?></span>
                            </label>
                            <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <input type="radio" name="answer_<?php echo $q['id']; ?>" value="B" class="mr-3 text-purple-600">
                                <span class="text-gray-700 dark:text-gray-300"><strong>B.</strong> <?php echo htmlspecialchars($q['option_b']); ?></span>
                            </label>
                            <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <input type="radio" name="answer_<?php echo $q['id']; ?>" value="C" class="mr-3 text-purple-600">
                                <span class="text-gray-700 dark:text-gray-300"><strong>C.</strong> <?php echo htmlspecialchars($q['option_c']); ?></span>
                            </label>
                            <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <input type="radio" name="answer_<?php echo $q['id']; ?>" value="D" class="mr-3 text-purple-600">
                                <span class="text-gray-700 dark:text-gray-300"><strong>D.</strong> <?php echo htmlspecialchars($q['option_d']); ?></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <hr class="my-6 border-gray-300 dark:border-gray-700">
                
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition text-lg">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Quiz
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php include("../footer.php"); ?>
