<?php
session_start();
include("../header.php");
include("../connection.php");

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'lecturer')) {
    $_SESSION['error'] = "Access denied. Please log in to view discussions.";
    header("Location: ../auth/login.php"); 
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course ID.";
    header("Location: dashboard.php"); 
    exit;
}

$course_query = "SELECT title FROM courses WHERE id = '$course_id'";
$course_result = mysqli_query($conn, $course_query);

if (mysqli_num_rows($course_result) == 0) {
    $_SESSION['error'] = "Course not found.";
    header("Location: dashboard.php");
    exit;
}
$course_title = mysqli_fetch_assoc($course_result)['title'];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_topic_title']) && isset($_POST['new_topic_content'])) {
    
    $title = $_POST['new_topic_title'];
    $content = $_POST['new_topic_content'];

    $insert_query = "INSERT INTO discussion_threads (course_id, user_id, title, content, created_at) 
                     VALUES ('$course_id', '$user_id', '$title', '$content', NOW())";

    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "New discussion topic posted!";
    } else {
        $_SESSION['error'] = "Failed to post discussion: " . mysqli_error($conn);
    }
    
    header("Location: course_discussion.php?course_id={$course_id}");
    exit;
}


$topics_query = "
    SELECT 
        d.id, d.title, d.content, d.created_at, u.name AS poster_name, u.role AS poster_role
    FROM 
        discussion_threads d
    JOIN 
        users u ON d.user_id = u.id
    WHERE 
        d.course_id = '$course_id'
    ORDER BY 
        d.created_at DESC";

$topics_result = mysqli_query($conn, $topics_query);
?>

<body>
    <div class="container mx-auto p-8 max-w-5xl">
        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-purple-800">Course Discussion Board</h1>
            <p class="text-lg text-gray-600">Course: <b><?php echo htmlspecialchars($course_title); ?></b></p>
            <a href="dashboard.php" class="text-purple-500 hover:text-purple-700 mt-2 inline-block">← Back to Dashboard</a>
        </header>

        <hr class="mb-8">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-3 mb-4 bg-green-100 border border-green-400 text-green-700 rounded"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="p-6 bg-purple-50 border-l-4 border-purple-500 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold text-purple-700 mb-4">Start a New Topic</h2>
            <form action="course_discussion.php?course_id=<?php echo $course_id; ?>" method="POST">
                <div class="mb-4">
                    <label for="new_topic_title" class="block text-gray-700 font-semibold mb-2">Topic Title:</label>
                    <input type="text" id="new_topic_title" name="new_topic_title" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="mb-6">
                    <label for="new_topic_content" class="block text-gray-700 font-semibold mb-2">Initial Post Content:</label>
                    <textarea id="new_topic_content" name="new_topic_content" rows="4" required class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-plus-circle mr-2"></i> Post New Topic
                </button>
            </form>
        </div>


        <section id="discussion-topics">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2 flex items-center"><i class="fas fa-comments mr-2"></i> Existing Discussions</h2>
            
            <?php if (mysqli_num_rows($topics_result) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white shadow-md rounded-lg">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Topic Title</th>
                                <th class="py-3 px-6 text-left">Started By</th>
                                <th class="py-3 px-6 text-center">Date Posted</th>
                                <th class="py-3 px-6 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php while ($topic = mysqli_fetch_assoc($topics_result)): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6 text-left whitespace-nowrap font-medium">
                                        <?php echo htmlspecialchars($topic['title']); ?>
                                    </td>
                                    <td class="py-3 px-6 text-left">
                                        <span class="font-semibold text-<?php echo ($topic['poster_role'] == 'lecturer') ? 'red-600' : 'blue-600'; ?>">
                                            <?php echo htmlspecialchars($topic['poster_name']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <a href="view_thread.php?thread_id=<?php echo $topic['id']; ?>" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-1 px-3 rounded text-xs transition duration-200">
                                            View Thread
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700">
                    <p class="font-bold">No Discussion Threads Yet!</p>
                    <p>Use the section above to post the first discussion topic.</p>
                </div>
            <?php endif; ?>
        </section>

    </div>
</body>
<?php include("../footer.php"); ?>