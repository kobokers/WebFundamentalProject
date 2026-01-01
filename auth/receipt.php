<?php
session_start();
include("../connection.php");
include("../header.php");

// --- 1. Authentication and Validation ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Please log in as a student to view receipt.";
    echo "<script>window.location.href = '../auth/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course for receipt.";
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

// --- 2. Fetch Payment and Course Details ---
$query = "SELECT c.title, c.fee, c.description, c.duration, e.payment_status, e.payment_date, 
                 u.name as lecturer, u.email as lecturer_email,
                 s.name as student_name, s.email as student_email
          FROM courses c 
          JOIN enrollment e ON c.id = e.course_id 
          JOIN users u ON c.lecturer_id = u.id
          JOIN users s ON e.user_id = s.id
          WHERE e.user_id = '{$user_id}' AND e.course_id = '{$course_id}'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Enrollment record not found.";
    echo "<script>window.location.href = 'catalog.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($result);

if ($data['payment_status'] !== 'paid') {
    $_SESSION['error'] = "Payment not completed. Please complete payment first.";
    echo "<script>window.location.href = 'payment.php?course_id={$course_id}';</script>";
    exit;
}

$receipt_number = 'RCP-' . strtoupper(substr(md5($user_id . $course_id . $data['payment_date']), 0, 8));
$transaction_id = 'TXN-' . strtoupper(substr(md5(time() . $receipt_number), 0, 10));
$payment_date = date('F j, Y', strtotime($data['payment_date']));
$payment_time = date('g:i A', strtotime($data['payment_date']));
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen py-10">
    <div class="max-w-lg mx-auto px-4">

        <!-- Success Message -->
        <div class="text-center mb-6">
            <div
                class="inline-flex items-center justify-center w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                <i class="fas fa-check text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Successful</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Your receipt is ready</p>
        </div>

        <!-- Receipt Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">

            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-white">OLMS</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Receipt</p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-semibold rounded-full">
                        <i class="fas fa-check-circle text-[10px]"></i>
                        Paid
                    </span>
                </div>
            </div>

            <!-- Receipt Details -->
            <div class="px-6 py-5 space-y-4">

                <!-- Receipt Info -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Receipt Number</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo $receipt_number; ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Date</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo $payment_date; ?></p>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                <!-- Customer -->
                <div class="text-sm">
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Billed to</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($data['student_name']); ?></p>
                    <p class="text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($data['student_email']); ?>
                    </p>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                <!-- Course Item -->
                <div class="flex gap-4">
                    <div
                        class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-book text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">
                            <?php echo htmlspecialchars($data['title']); ?></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            By <?php echo htmlspecialchars($data['lecturer']); ?> ·
                            <?php echo $data['duration'] ? $data['duration'] . 'h' : 'Self-paced'; ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">
                            $<?php echo number_format($data['fee'], 2); ?></p>
                    </div>
                </div>

            </div>

            <!-- Summary -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($data['fee'], 2); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Discount</span>
                        <span class="text-green-600 dark:text-green-400">-$0.00</span>
                    </div>
                </div>
                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <span class="font-semibold text-gray-900 dark:text-white">Total</span>
                    <span
                        class="text-xl font-bold text-gray-900 dark:text-white">$<?php echo number_format($data['fee'], 2); ?></span>
                </div>
            </div>

            <!-- Footer Info -->
            <div
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex justify-between">
                    <span>Transaction ID</span>
                    <span class="font-mono"><?php echo $transaction_id; ?></span>
                </div>
                <div class="flex justify-between mt-2">
                    <span>Payment Method</span>
                    <span>Credit Card •••• 4242</span>
                </div>
            </div>

        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-6">
            <button onclick="window.print()"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-download text-sm"></i>
                Download
            </button>
            <a href="course_view.php?id=<?php echo $course_id; ?>"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-white font-medium transition-colors">
                <i class="fas fa-play text-sm"></i>
                Start Course
            </a>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="dashboard.php"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                ← Back to Dashboard
            </a>
        </div>

    </div>
</div>

<!-- Print Styles -->
<style>
    @media print {

        header,
        footer,
        nav,
        .flex.gap-3.mt-6,
        .text-center.mt-6 {
            display: none !important;
        }

        .bg-gray-50,
        .dark\:bg-gray-900 {
            background: white !important;
        }

        .shadow-lg {
            box-shadow: none !important;
            border: 1px solid #e5e7eb !important;
        }
    }
</style>

<?php
mysqli_close($conn);
include("../footer.php");
?>