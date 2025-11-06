<?php
session_start();
include("../connection.php");
include("../header.php");

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape all user inputs before use
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'student');

    // validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
    } else {
        // --- 1. Check if email already exists ---
        $check_query = "SELECT id FROM users WHERE email='$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Email already registered.";
        } else {
            // --- 2. Hash the password ---
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $status = 'pending';
            
            // --- 3. Insert new user ---
            $insert_query = "INSERT INTO users (name, email, password, role, status) 
                             VALUES ('$username', '$email', '$hashed_password', '$role', '$status')";
                             
            if (mysqli_query($conn, $insert_query)) {
                $_SESSION['success'] = "Registration successful. Please log in.";
                header("Location: login.php");
                exit;
            } else {
                $_SESSION['error'] = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<div class="flex w-full items-center justify-center p-4 bg-gray-100" style="min-height: calc(100vh - 64px);">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <h2 class="text-3xl font-extrabold mb-6 text-gray-900 text-center border-b pb-3">Student Registration</h2>

        <form action="register.php" method="POST" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Name:</label>
                <input type="text" id="username" name="username"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    required>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" id="email" name="email"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" id="password" name="password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    required>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">I am registering as:</label>
                <select id="role" name="role"
                    class="mt-1 block w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="student">Student</option>
                    <option value="lecturer">Lecturer</option>
                </select>
            </div>

            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out mt-6">
                Register
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Already registered?
            <a href="./login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign in here</a>
        </p>
    </div>
</div>
<?php include("../footer.php"); ?>