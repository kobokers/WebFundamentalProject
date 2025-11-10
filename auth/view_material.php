<?php
session_start();
include("../header.php");
include("../connection.php"); 

// --- 1. Authentication and Authorization ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['student', 'lecturer'])) {
    $_SESSION['error'] = "Access denied.";
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$material_id = isset($_GET['material_id']) ? (int)$_GET['material_id'] : null;

if (!$material_id) {
    $_SESSION['error'] = "Invalid material selected.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Fetch Material Details and Course/Module IDs ---
// Join materials -> modules -> courses to get all necessary info for security checks
$fetch_query = "
    SELECT 
        T1.title AS material_title, T1.content_type, T1.content_url,
        T2.module_order, T2.title AS module_title,
        T3.id AS course_id, T3.title AS course_title, T3.lecturer_id
    FROM 
        learning_materials AS T1
    INNER JOIN 
        modules AS T2 ON T1.module_id = T2.id
    INNER JOIN
        courses AS T3 ON T2.course_id = T3.id
    WHERE 
        T1.id = '$material_id'";
        
$material_result = mysqli_query($conn, $fetch_query);

if (mysqli_num_rows($material_result) == 0) {
    $_SESSION['error'] = "Material not found.";
    header("Location: dashboard.php");
    exit;
}

$material = mysqli_fetch_assoc($material_result);
$course_id = $material['course_id'];
$content_url = htmlspecialchars($material['content_url']);
$content_type = $material['content_type'];


// --- 3. Security Check: Payment Verification (Bypass for Lecturer) ---
$is_lecturer = ($user_role === 'lecturer' && $user_id == $material['lecturer_id']);
$has_paid = false;

if (!$is_lecturer) {
    $enroll_query = "SELECT payment_status FROM enrollment 
                     WHERE user_id = '$user_id' AND course_id = '$course_id' LIMIT 1";
    $enroll_result = mysqli_query($conn, $enroll_query);

    if (mysqli_num_rows($enroll_result) > 0) {
        $enroll_data = mysqli_fetch_assoc($enroll_result);
        if ($enroll_data['payment_status'] === 'paid') {
            $has_paid = true;
        }
    }

    // DENY ACCESS if student hasn't paid AND is not the lecturer
    if (!$has_paid) {
        $_SESSION['error'] = "You must complete payment to access this material.";
        header("Location: payment.php?course_id={$course_id}");
        exit;
    }
}

// --- 4. Determine Content Display ---

$display_content = '';
$link_text = 'Access Content';

switch ($content_type) {
    case 'video':
        $link_text = 'Watch Video Lesson';
        // Basic check for YouTube URL format (for embedding)
        if (strpos($content_url, 'youtube.com') !== false || strpos($content_url, 'youtu.be') !== false) {
            // Simple logic to extract video ID for embedding
            $video_id = '';
            if (preg_match('/v=([a-zA-Z0-9_-]+)/', $content_url, $matches)) {
                $video_id = $matches[1];
            } elseif (preg_match('/youtu.be\/([a-zA-Z0-9_-]+)/', $content_url, $matches)) {
                $video_id = $matches[1];
            }

            if ($video_id) {
                 // Use iframe to embed YouTube video
                $display_content = '<iframe width="100%" height="450" 
                    src="https://www.youtube.com/embed/'.$video_id.'?rel=0" 
                    frameborder="0" allowfullscreen class="rounded-lg shadow-xl"></iframe>';
                $link_text = 'Open in New Tab (if embedded fails)';
                
            }
        }
        break;
        
    case 'reading':
        $link_text = 'View Reading/Document (Opens in New Tab)';
        $display_content = '<p class="text-gray-700">This is a reading assignment. Click the link below to access the document (e.g., PDF or article).</p>';
        break;

    case 'quiz':
        $link_text = 'Start Quiz/Assessment';
        $display_content = '<p class="text-gray-700">This link will take you to the external quiz system or the designated quiz page.</p>';
        break;

    case 'link':
        $link_text = 'Go to External Link';
        $display_content = '<p class="text-gray-700">This is an external resource link.</p>';
        break;
}

// Fallback for types that don't embed or if embedding failed
if ($display_content == '') {
    $display_content = '<p class="text-gray-700">Click the link below to access the content.</p>';
}

?>

<body>
    <div class="container mx-auto p-8 max-w-4xl">

        <header class="mb-8 border-b pb-4">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($material['material_title']); ?></h1>
            <p class="text-md text-gray-600">
                Course: **<?php echo htmlspecialchars($material['course_title']); ?>** | 
                Module <?php echo $material['module_order']; ?>: **<?php echo $material['module_title']; ?>**
            </p>
            <a href="course_view.php?id=<?php echo $course_id; ?>" class="text-sm text-blue-600 hover:underline mt-2 inline-block">
                ← Back to Course Curriculum
            </a>
        </header>

        <section class="bg-white p-6 rounded-lg shadow-xl">
            <h2 class="text-2xl font-semibold mb-4 text-indigo-700">
                Content Type: <?php echo ucfirst($content_type); ?>
            </h2>

            <?php echo $display_content; ?>
            
            <div class="mt-8 pt-4 border-t-2 border-dashed border-gray-200">
                <p class="text-lg font-semibold mb-4">Direct Link Access:</p>
                
                <a href="<?php echo $content_url; ?>" target="_blank"
                    class="inline-flex items-center bg-green-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition shadow-lg">
                    <i class="fas fa-play-circle mr-2"></i> 
                    <?php echo $link_text; ?>
                </a>

                <p class="text-sm text-gray-500 mt-3">
                    URL: <code><?php echo $content_url; ?></code>
                </p>
            </div>
        </section>

    </div>
</body>

<?php mysqli_close($conn); ?>
<?php include("../footer.php"); ?>