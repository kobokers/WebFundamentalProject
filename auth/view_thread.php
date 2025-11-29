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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_content'])) {
    
    $content = $_POST['reply_content'];

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

$replies_query = "
    SELECT 
        r.content, r.created_at, u.name AS replier_name, u.role AS replier_role
    FROM 
        discussion_replies r
    JOIN 
        users u ON r.user_id = u.id
    WHERE 
        r.thread_id = '$thread_id'
    ORDER BY 
        r.created_at ASC";

$replies_result = mysqli_query($conn, $replies_query);
include("../header.php");
?>

<body>
    <div class="container mx-auto p-8 max-w-5xl">
        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-purple-800 dark:text-purple-300"><?php echo htmlspecialchars($thread['title']); ?></h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Course: <b><?php echo htmlspecialchars($thread['course_title']); ?></b></p>
            <a href="course_discussion.php?course_id=<?php echo $thread['course_id']; ?>"
                class="text-purple-500 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 mt-2 inline-block">← Back to Discussions</a>
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
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Replied by
                        <span
                            class="font-semibold text-<?php echo ($reply['replier_role'] == 'lecturer') ? 'red-600' : 'blue-600'; ?>">
                            <?php echo htmlspecialchars($reply['replier_name']); ?>
                        </span>
                        on <?php echo date('M j, Y g:i A', strtotime($reply['created_at'])); ?>
                    </p>
                    <div class="text-gray-700 dark:text-gray-300">
                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
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
</body>
<?php include("../footer.php"); ?>