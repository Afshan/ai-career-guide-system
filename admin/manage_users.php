<?php
session_start();
include '../backend/db.php';
include 'admin_header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// Search filter (optional)
$search = $_GET['search'] ?? '';

// Get users
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, a.degree_program, a.current_semester, a.cgpa
    FROM users u
    LEFT JOIN academic_info a ON u.id = a.user_id
    WHERE u.name LIKE CONCAT('%', ?, '%')
    ORDER BY u.id DESC
");
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();
?>




    <div class="container mt-4">
        <h3 class="mb-3">Manage Students</h3>

        <form class="mb-3" method="get">
            <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($search) ?>" class="form-control w-50 d-inline">
            <button class="btn btn-primary ms-2">Search</button>
        </form>

        <table class="table table-striped table-hover table-bordered shadow-sm table-responsive">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Degree</th>
                    <th>Semester</th>
                    <th>CGPA</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <a href="student_detail.php?id=<?= $row['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($row['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['degree_program'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($row['current_semester'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($row['cgpa'] ?? '—') ?></td>
                            <td>
                                <form method="post" action="delete_user.php" onsubmit="return confirm('Are you sure?');">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger ms-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php include 'admin_footer.php'; ?>

