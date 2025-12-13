<?php
session_start();
include("../connection.php"); 
include("../header.php");

// --- 1. Authentication and Validation ---
// Ensure user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    $_SESSION['error'] = "Please log in as a student to proceed with payment.";
    echo "<script>window.location.href = '../auth/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : null;

if (!$course_id || !is_numeric($course_id)) {
    $_SESSION['error'] = "Invalid course for payment.";
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

// --- 2. Fetch Course Fee and Enrollment Status  ---
$check_query = "SELECT c.title, c.fee, e.payment_status, u.name as lecturer 
                FROM courses c 
                JOIN enrollment e ON c.id = e.course_id 
                JOIN users u ON c.lecturer_id = u.id
                WHERE e.user_id = '{$user_id}' AND e.course_id = '{$course_id}'";
                
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = "Enrollment record not found. Please enroll first.";
    echo "<script>window.location.href = 'catalog.php';</script>";
    exit;
}

$course_data = mysqli_fetch_assoc($check_result);
$course_title = htmlspecialchars($course_data['title']);
$course_fee = $course_data['fee'];

// If already paid, redirect immediately
if ($course_data['payment_status'] == 'paid') {
    $_SESSION['success'] = "Payment already completed. You can start the course!";
    echo "<script>window.location.href = 'course_view.php?id={$course_id}';</script>";
    exit;
}

// --- 3. Process Payment Simulation ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Handle Cancellation
    if (isset($_POST['cancel_payment'])) {
        $delete_query = "DELETE FROM enrollment WHERE user_id = '{$user_id}' AND course_id = '{$course_id}' AND payment_status = 'pending'";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success'] = "Transaction cancelled. Enrollment removed.";
            echo "<script>window.location.href = 'catalog.php';</script>";
            exit;
        } else {
            $_SESSION['error'] = "Cancellation failed: " . mysqli_error($conn);
        }
    }

    // Handle Payment
    if (isset($_POST['pay_submit'])) {
        // Simulate payment processing delay if desired, or database update
        $update_query = "UPDATE enrollment 
                        SET payment_status = 'paid', payment_date = NOW() 
                        WHERE user_id = '{$user_id}' AND course_id = '{$course_id}'";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = "Payment successful! You now have full access to the course.";
            echo "<script>window.location.href = 'course_view.php?id={$course_id}';</script>";
            exit;
        } else {
            $_SESSION['error'] = "Payment update failed: " . mysqli_error($conn);
        }
    }
}
?>

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-4xl mx-auto">
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Checkout</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Left Column: Payment Details -->
                <div class="md:col-span-2 space-y-6">
                    
                    <!-- Secure Payment Banner -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center gap-3">
                        <i class="fas fa-lock text-green-600 dark:text-green-400 text-xl"></i>
                        <div>
                            <h3 class="font-semibold text-green-800 dark:text-green-300">Secure Checkout</h3>
                            <p class="text-sm text-green-700 dark:text-green-400">All transactions are secure and encrypted.</p>
                        </div>
                    </div>

                    <!-- Payment Method Simulation -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Payment Method</h2>
                        
                        <div class="space-y-4">
                            <!-- Card Option (Selected) -->
                            <div class="border-2 border-coursera-blue bg-blue-50 dark:bg-blue-900/30 dark:border-blue-500 rounded-xl p-4 relative cursor-pointer">
                                <div class="absolute top-4 right-4 text-coursera-blue dark:text-blue-400">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                                <div class="flex items-center gap-4 mb-2">
                                    <i class="far fa-credit-card text-2xl text-gray-700 dark:text-gray-300"></i>
                                    <span class="font-semibold text-gray-900 dark:text-white">Credit/Debit Card</span>
                                </div>
                                <div class="flex gap-2">
                                    <i class="fab fa-cc-visa text-3xl text-blue-900 dark:text-blue-100"></i>
                                    <i class="fab fa-cc-mastercard text-3xl text-red-600 dark:text-red-400"></i>
                                    <i class="fab fa-cc-amex text-3xl text-blue-500"></i>
                                </div>
                            </div>

                            <!-- Mock Card Form -->
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Card Number</label>
                                    <input type="text" placeholder="0000 0000 0000 0000" disabled
                                        class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-500 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiration</label>
                                    <input type="text" placeholder="MM/YY" disabled
                                        class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-500 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CVC</label>
                                    <input type="text" placeholder="123" disabled
                                        class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-500 cursor-not-allowed">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                * This is a simulation. No real card details are required.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Order Summary -->
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Order Summary</h2>
                        
                        <div class="flex gap-4 mb-6">
                            <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-book text-gray-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-2"><?php echo $course_title; ?></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Instr. <?php echo htmlspecialchars($course_data['lecturer']); ?></p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 my-4 pt-4 space-y-2">
                            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                <span>Original Price</span>
                                <span>$<?php echo number_format($course_fee, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                <span>Discounts</span>
                                <span class="text-green-600 dark:text-green-400">-$0.00</span>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-2 flex justify-between items-center">
                                <span class="font-bold text-gray-900 dark:text-white text-lg">Total</span>
                                <span class="font-bold text-gray-900 dark:text-white text-2xl">$<?php echo number_format($course_fee, 2); ?></span>
                            </div>
                        </div>

                        <form action="payment.php" method="POST" class="mt-8">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                            <button type="submit" name="pay_submit" value="1"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                                <span>Pay $<?php echo number_format($course_fee, 2); ?></span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            
                            <button type="submit" name="cancel_payment" value="1"
                                class="w-full text-red-500 hover:text-red-700 font-semibold py-3 px-6 rounded-xl mt-3 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all border border-transparent hover:border-red-200 dark:hover:border-red-800">
                                Cancel Transaction
                            </button>

                            <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-4">
                                By confirming, you agree to our Terms of Service.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
include("../footer.php"); 
?>