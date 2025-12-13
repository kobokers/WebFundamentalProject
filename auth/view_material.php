<?php
session_start();
include("../connection.php"); 
include("../header.php");

// --- 1. Authentication and Authorization ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['student', 'lecturer'])) {
    $_SESSION['error'] = "Access denied.";
    echo "<script>window.location.href = '../auth/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$material_id = isset($_GET['material_id']) ? (int)$_GET['material_id'] : null;

if (!$material_id) {
    $_SESSION['error'] = "Invalid material selected.";
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

// --- 2. Fetch Material Details and Course/Module IDs ---
// Join materials -> modules -> courses
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
    echo "<script>window.location.href = 'dashboard.php';</script>";
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
        echo "<script>window.location.href = 'payment.php?course_id={$course_id}';</script>";
        exit;
    }
}

// --- 4. Determine Content Display ---
$display_content = '';
$icon_type = 'fa-file-alt'; // Default icon
$link_text = 'Access Content';

switch ($content_type) {
    case 'video':
        $icon_type = 'fa-play-circle';
        $link_text = 'Watch Video Lesson';
        // Basic check for YouTube URL format (for embedding)
        if (strpos($content_url, 'youtube.com') !== false || strpos($content_url, 'youtu.be') !== false) {
            $video_id = '';
            if (preg_match('/v=([a-zA-Z0-9_-]+)/', $content_url, $matches)) {
                $video_id = $matches[1];
            } elseif (preg_match('/youtu.be\/([a-zA-Z0-9_-]+)/', $content_url, $matches)) {
                $video_id = $matches[1];
            }

            if ($video_id) {
                 // Use iframe to embed YouTube video
                $display_content = '<div class="aspect-w-16 aspect-h-9 w-full">
                    <iframe src="https://www.youtube.com/embed/'.$video_id.'?rel=0" 
                    frameborder="0" allowfullscreen class="w-full h-[500px] rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700"></iframe>
                </div>';
                $link_text = 'Open in YouTube';
            }
        } else {
             $display_content = '<div class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-200 dark:border-gray-700">
                <i class="fas fa-video text-6xl text-gray-400 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-600 dark:text-gray-400 text-lg">This video cannot be embedded. Please click the button below to watch it.</p>
            </div>';
        }
        break;
        
    case 'reading':
        $icon_type = 'fa-book-reader';
        $link_text = 'View Document';
        $display_content = '<div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-200 dark:border-gray-700">
            <i class="fas fa-file-pdf text-6xl text-red-500 mb-4 opacity-80"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Reading Assignment</h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">This reading material is hosted externally. Click the button below to open the document.</p>
        </div>';
        break;

    case 'quiz':
        $icon_type = 'fa-tasks';
        $link_text = 'Start Quiz';
        $display_content = '<div class="bg-purple-50 dark:bg-purple-900/20 rounded-2xl p-12 text-center border border-purple-100 dark:border-purple-800/50">
            <i class="fas fa-clipboard-check text-6xl text-purple-500 mb-4 opacity-80"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Quiz Assessment</h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">Ready to test your knowledge? Click the button below to start the assessment.</p>
        </div>';
        break;

    case 'link':
        $icon_type = 'fa-link';
        $link_text = 'Visit Link';
        $display_content = '<div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-12 text-center border border-blue-100 dark:border-blue-800/50">
            <i class="fas fa-globe text-6xl text-blue-500 mb-4 opacity-80"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">External Resource</h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">This resource is located on an external website. Click below to visit.</p>
        </div>';
        break;
}

// Fallback
if ($display_content == '') {
    $display_content = '<div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-12 text-center">
            <i class="fas fa-external-link-alt text-6xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400">Click the link below to access this content.</p>
        </div>';
}
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <!-- Header/Breadcrumb -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-[72px] z-40">
        <div class="container mx-auto px-4 lg:px-8 py-4">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                <a href="course_view.php?id=<?php echo $course_id; ?>" class="hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Course
                </a>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="text-gray-900 dark:text-white font-medium"><?php echo $material['module_title']; ?></span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas <?php echo $icon_type; ?> text-blue-600 dark:text-blue-400"></i>
                <?php echo htmlspecialchars($material['material_title']); ?>
            </h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 lg:px-8 py-8">
        <div class="max-w-5xl mx-auto">
            
            <!-- Content Area -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                <div class="p-1">
                    <?php echo $display_content; ?>
                </div>
            </div>

            <!-- Access Button Area -->
            <div class="flex items-center justify-between flex-wrap gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white mb-1">Source URL</h3>
                    <code class="text-sm text-blue-600 dark:text-blue-400 break-all bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded"><?php echo $content_url; ?></code>
                </div>
                
                <a href="<?php echo $content_url; ?>" target="_blank"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md transform hover:-translate-y-0.5">
                    <?php echo $link_text; ?>
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
include("../footer.php"); 
?>