<?php
session_start();
include("../connection.php");

// ALLOWS both 'lecturer' and 'student' to access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Access denied. Please log in to view the thread.";
    header("Location: ../auth/login.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];
$thread_id = isset($_GET['thread_id']) ? $_GET['thread_id'] : null;

if (!$thread_id || !is_numeric($thread_id)) {
    $_SESSION['error'] = "Invalid thread ID.";
    // Link back to the student's dashboard
    header("Location: dashboard.php"); 
    exit;
}

// --- Handle Reply Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_content'])) {
    
    $content = mysqli_real_escape_string($conn, $_POST['reply_content']);

    $insert_query = "INSERT INTO discussion_replies (thread_id, user_id, content, created_at) 
                     VALUES ('$thread_id', '$user_id', '$content', NOW())";

    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "Reply posted successfully!";
    } else {
        $_SESSION['error'] = "Failed to post reply: " . mysqli_error($conn);
    }
    
    header("Location: view_thread.php?thread_id={$thread_id}");
    exit;
}

// --- Handle Upvote Toggle ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_upvote'])) {
    $reply_id = (int)$_POST['reply_id'];
    
    // Check if user already upvoted
    $check_upvote = "SELECT id FROM discussion_upvotes WHERE reply_id = '$reply_id' AND user_id = '$user_id' LIMIT 1";
    $check_result = mysqli_query($conn, $check_upvote);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Remove upvote
        $remove_query = "DELETE FROM discussion_upvotes WHERE reply_id = '$reply_id' AND user_id = '$user_id'";
        mysqli_query($conn, $remove_query);
    } else {
        // Add upvote
        $add_query = "INSERT INTO discussion_upvotes (reply_id, user_id, created_at) VALUES ('$reply_id', '$user_id', NOW())";
        mysqli_query($conn, $add_query);
    }
    
    header("Location: view_thread.php?thread_id={$thread_id}");
    exit;
}

// --- Fetch Thread Details ---
$thread_query = "
    SELECT 
        t.title, t.content, t.created_at, t.course_id, 
        u.name AS author_name, u.role AS author_role, c.title AS course_title
    FROM 
        discussion_threads t
    JOIN 
        users u ON t.user_id = u.id
    JOIN 
        courses c ON t.course_id = c.id
    WHERE 
        t.id = '$thread_id'";

$thread_result = mysqli_query($conn, $thread_query);

if (mysqli_num_rows($thread_result) === 0) {
    $_SESSION['error'] = "Discussion thread not found.";
    // Link back to the student's course list
    header("Location: course_discussion.php");
    exit;
}
$thread = mysqli_fetch_assoc($thread_result);

// --- Fetch Replies with Upvote Count and User's Upvote Status ---
$replies_query = "
    SELECT 
        r.id AS reply_id,
        r.content, 
        r.created_at, 
        u.name AS replier_name, 
        u.role AS replier_role,
        u.profile_picture AS replier_picture,
        (SELECT COUNT(*) FROM discussion_upvotes WHERE reply_id = r.id) AS upvote_count,
        (SELECT COUNT(*) FROM discussion_upvotes WHERE reply_id = r.id AND user_id = '$user_id') AS user_upvoted
    FROM 
        discussion_replies r
    JOIN 
        users u ON r.user_id = u.id
    WHERE 
        r.thread_id = '$thread_id'
    ORDER BY 
        upvote_count DESC, r.created_at ASC";

$replies_result = mysqli_query($conn, $replies_query);
include("../header.php");
?>

    <div class="container mx-auto p-8 max-w-5xl">
        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-purple-800 dark:text-purple-300"><?php echo htmlspecialchars($thread['title']); ?></h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Course: <b><?php echo htmlspecialchars($thread['course_title']); ?></b></p>
            <a href="course_discussion.php?course_id=<?php echo $thread['course_id']; ?>"
                class="text-purple-500 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 mt-2 inline-block">← Back to
                Discussions</a>
        </header>

        <hr class="mb-8 border-gray-300 dark:border-gray-700">

        <?php if (isset($_SESSION['success'])): ?>
        <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <section id="main-post" class="mb-8 p-6 bg-purple-50 dark:bg-purple-900 border-l-4 border-purple-500 dark:border-purple-400 rounded-lg shadow-xl transition-colors duration-200">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                Posted by
                <span
                    class="font-semibold text-<?php echo ($thread['author_role'] == 'lecturer') ? 'red-600' : 'blue-600'; ?>">
                    <?php echo htmlspecialchars($thread['author_name']); ?>
                    <?php if ($thread['author_role'] == 'lecturer'): ?>
                        <span class="ml-1 px-1.5 py-0.5 text-xs bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 rounded">Instructor</span>
                    <?php endif; ?>
                </span>
                on <?php echo date('M j, Y g:i A', strtotime($thread['created_at'])); ?>
            </p>
            <div class="text-gray-800 dark:text-gray-200 leading-relaxed">
                <?php echo nl2br(htmlspecialchars($thread['content'])); ?>
            </div>
        </section>

        <section id="replies" class="mb-8">
            <h2 class="text-2xl font-semibold mb-4 border-b dark:border-gray-700 pb-2 flex items-center text-gray-800 dark:text-white"><i class="fas fa-reply mr-2"></i>
                Replies (<?php echo mysqli_num_rows($replies_result); ?>)</h2>

            <div class="space-y-6">
                <?php if (mysqli_num_rows($replies_result) > 0): ?>
                <?php while ($reply = mysqli_fetch_assoc($replies_result)): ?>
                <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm transition-colors duration-200">
                    <div class="flex justify-between items-start">
                        <div class="flex gap-3 flex-1">
                            <!-- Avatar -->
                            <?php if (!empty($reply['replier_picture'])): ?>
                                <img src="../uploads/avatars/<?php echo htmlspecialchars($reply['replier_picture']); ?>" 
                                     alt="Avatar" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-gray-400 dark:text-gray-500"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                    <span
                                        class="font-semibold text-<?php echo ($reply['replier_role'] == 'lecturer') ? 'red-600' : 'blue-600'; ?>">
                                        <?php echo htmlspecialchars($reply['replier_name']); ?>
                                        <?php if ($reply['replier_role'] == 'lecturer'): ?>
                                            <span class="ml-1 px-1.5 py-0.5 text-xs bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 rounded">Instructor</span>
                                        <?php endif; ?>
                                    </span>
                                    on <?php echo date('M j, Y g:i A', strtotime($reply['created_at'])); ?>
                                </p>
                                <div class="text-gray-700 dark:text-gray-300">
                                    <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upvote Button -->
                        <form method="POST" class="ml-4 flex-shrink-0">
                            <input type="hidden" name="toggle_upvote" value="1">
                            <input type="hidden" name="reply_id" value="<?php echo $reply['reply_id']; ?>">
                            <button type="submit" 
                                    class="flex flex-col items-center p-2 rounded-lg transition-all duration-200
                                           <?php echo $reply['user_upvoted'] ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-green-50 dark:hover:bg-green-900/50 hover:text-green-600'; ?>">
                                <svg class="w-5 h-5" fill="<?php echo $reply['user_upvoted'] ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                                <span class="text-sm font-semibold"><?php echo $reply['upvote_count']; ?></span>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-500 dark:border-yellow-400 text-yellow-700 dark:text-yellow-200 rounded-lg transition-colors duration-200">
                    There are no replies yet. Be the first to respond!
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="reply-form">
            <h2 class="text-2xl font-semibold mb-4 border-b dark:border-gray-700 pb-2 text-gray-800 dark:text-white"><i class="fas fa-feather-alt mr-2"></i> Post a Reply
            </h2>
            <form action="view_thread.php?thread_id=<?php echo $thread_id; ?>" method="POST"
                class="p-6 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-md transition-colors duration-200">
                <div class="mb-6">
                    <label for="reply_content" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Your Reply:</label>
                    <textarea id="reply_content" name="reply_content" rows="5" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
                <button type="submit"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Submit Reply
                </button>
            </form>
        </section>

    </div>
</main>
<?php include("../footer.php"); ?>