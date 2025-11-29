<?php
session_start();
include("../connection.php"); // Database connection only (no HTML output)

// --- 1. Authentication and Validation ---
// Ensure user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Please log in as a student to proceed with payment.";
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
// Use $_REQUEST to safely get course_id from either GET (link click) or POST (form submit)
$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course for payment.";
    header("Location: dashboard.php");
    exit;
}

// --- 2. Fetch Course Fee and Enrollment Status  ---
$check_query = "SELECT c.title, c.fee, e.payment_status 
                FROM courses c 
                JOIN enrollment e ON c.id = e.course_id 
                WHERE e.user_id = '{$user_id}' AND e.course_id = '{$course_id}'";
                
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = "Enrollment record not found. Please enroll first.";
    header("Location: ../catalog.php");
    exit;
}

$course_data = mysqli_fetch_assoc($check_result);
$course_title = htmlspecialchars($course_data['title']);
$course_fee = $course_data['fee'];

// If already paid, redirect immediately (Redundancy check)
if ($course_data['payment_status'] == 'paid') {
    $_SESSION['success'] = "Payment already completed. You can start the course!";
    header("Location: course_view.php?id={$course_id}");
    exit;
}

// --- 3. Process Payment Simulation ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_submit'])) {
    
    // ************************************************
    // In a real application, a secure payment gateway (e.g., Stripe, PayPal and Toyyibpay) 
    // API call would be placed here.
    // For this simulation, we assume payment is successful.
    // ************************************************

    // --- Update Payment Status ---
    $update_query = "UPDATE enrollment 
                     SET payment_status = 'paid', payment_date = NOW() 
                     WHERE user_id = '{$user_id}' AND course_id = '{$course_id}'";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Payment successful! You now have full access to the course.";
        header("Location: course_view.php?id={$course_id}");
        exit;
    } else {
        $_SESSION['error'] = "Payment update failed: " . mysqli_error($conn);
        header("Location: payment.php?course_id={$course_id}");
        exit;
    }
}

include("../header.php");

mysqli_close($conn);
?>

<body>
    <div class="container mx-auto p-8 max-w-lg">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-extrabold text-red-700 dark:text-red-400">Finalize Payment</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Complete your transaction for <strong><?php echo $course_title; ?></strong>.</p>
        </header>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <div class="p-8 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-200">

            <div class="text-center mb-6">
                <p class="text-xl text-gray-700 dark:text-gray-300 font-semibold mb-2">Amount Due:</p>
                <p class="text-5xl font-bold text-green-600 dark:text-green-400">$<?php echo number_format($course_fee, 2); ?></p>
            </div>

            <form action="payment.php" method="POST">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                <input type="hidden" name="pay_submit" value="1">

                <p class="text-sm text-center text-gray-500 dark:text-gray-400 mb-4">
                    By clicking below, you simulate a successful payment transaction.
                </p>

                <button type="submit"
                    class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 text-xl shadow-lg">
                    <i class="fas fa-credit-card mr-2"></i> Confirm & Pay Now
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">← Back to Dashboard</a>
        </div>
    </div>
</body>
<?php include("../footer.php"); ?>