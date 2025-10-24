<?php
include("../connection.php");
include("../header.php");
?>

<body>
    <div>
        <h2 class="text-3xl font-bold text-center mt-10">Login to Your Account</h2>
        <form action="authenticate.php" method="POST" class="max-w-md mx-auto mt-6 bg-white p-6 rounded shadow-md">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 mb-2">Email:</label>
                <input type="email" id="email" name="email" required class="w-full px-3 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 mb-2">Password:</label>
                <input type="password" id="password" name="password" required class="w-full px-3 py-2 border rounded">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Login</button>
        </form>
    </div>
</body>

<?php include("../footer.php"); ?>