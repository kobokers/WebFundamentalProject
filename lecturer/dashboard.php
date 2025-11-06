<?php
session_start();
include("../header.php");
include("../connection.php");

// --- 1. Authentication and Authorization Check ---
// Redirect if user is not logged in or is not a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
  $_SESSION['error'] = "Access denied. Please log in as a lecturer.";
  header("Location: ../auth/login.php");
  exit;
}

// Set lecturer variables from session
$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['user_name'];

/* This query retrieves all courses linked to the current lecturer. */
$sql_courses = "
  SELECT
    c.id AS course_id,
    c.title AS course_title,
    c.level,
    c.language,
    c.fee
  FROM
    courses c
  WHERE
    c.lecturer_id = '{$lecturer_id}'
  ORDER BY
    c.title ASC";

$result = mysqli_query($conn, $sql_courses);
?>

<body>
  <div class="container mx-auto p-8">
    <header class="mb-8">
      <h1 class="text-4xl font-extrabold text-purple-800">Lecturer Dashboard</h1>
      <p class="text-lg text-gray-600">Welcome, <b><?php echo htmlspecialchars($lecturer_name); ?>! </b> Manage your content here.</p>
    </header>

    <hr class="mb-8">

    <section id="actions" class="mb-10 p-6 bg-purple-50 border-l-4 border-purple-500 rounded-lg shadow-md flex justify-between items-center">
      <h2 class="text-xl font-semibold text-purple-700">Ready to create new content?</h2>
      <a href="add_course_form.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
        <i class="fas fa-plus-circle mr-2"></i> Register New Course
      </a>
    </section>

    <section id="courses-taught">
      <h2 class="text-2xl font-semibold mb-4 flex items-center"><i class="fas fa-chalkboard-teacher mr-2"></i> My Registered Courses</h2>

      <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead>
              <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Course Title</th>
                <th class="py-3 px-6 text-left">Level</th>
                <th class="py-3 px-6 text-center">Fee</th>
                <th class="py-3 px-6 text-center">Actions</th>
                <th class="py-3 px-6 text-center">Enrollments</th>
              </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="py-3 px-6 text-left whitespace-nowrap font-medium">
                    <?php echo htmlspecialchars($row['course_title']); ?>
                  </td>
                  <td class="py-3 px-6 text-left">
                    <?php echo htmlspecialchars($row['level']); ?>
                  </td>
                  <td class="py-3 px-6 text-center">
                    $<?php echo number_format($row['fee'], 2); ?>
                  </td>
                  <td class="py-3 px-6 text-center space-x-2">
                    <a href="course_discussion.php?course_id=<?php echo $row['course_id']; ?>" class="text-purple-500 hover:text-purple-700 font-medium text-xs">Discussion</a>
                    <a href="module_setup.php?course_id=<?php echo $row['course_id']; ?>" class="text-blue-500 hover:text-blue-700 font-medium text-xs">Manage Modules</a>
                    <a href="edit_course.php?course_id=<?php echo $row['course_id']; ?>" class="text-gray-500 hover:text-gray-700 font-medium text-xs">Edit Details</a>
                  </td>
                  <td class="py-3 px-10 text-center">
                    <a href="view_enrollment.php?course_id=<?php echo $row['course_id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded text-xs transition duration-200 ml-2">View Students</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700">
          <p class="font-bold">No Courses Found!</p>
          <p>You have not registered any courses yet. Click the **Register New Course** button above to get started.</p>
        </div>
      <?php endif; ?>
    </section>

    <section id="account-actions">
      <h2 class="text-2xl font-semibold mb-4 flex items-center"><i class="fas fa-user-cog mr-2"></i> Account Actions</h2>
      <div class="flex space-x-4">
        <a href="edit_profile.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition duration-200">Edit Profile</a>
      </div>
    </section>

    <?php mysqli_close($conn); ?>

  </div>
</body>
<?php include("../footer.php"); ?>

</html>