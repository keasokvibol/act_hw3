<?php
ob_start();
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Manage Departments';
$errorMessage = '';
$successMessage = '';
$editingDepartment = null;

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $errorMessage = 'Invalid department ID.';
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
                $stmt->execute([$id]);

                if ($stmt->rowCount() === 0) {
                    $errorMessage = 'Department not found.';
                } else {
                    header('Location: departments.php?deleted=1');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Failed to delete department: ' . $e->getMessage());

                if ($e->getCode() === '23000') {
                    $errorMessage = 'This department cannot be deleted because employees are assigned to it.';
                } else {
                    $errorMessage = 'Unable to delete department. Please try again.';
                }
            }
        }
    } elseif ($action === 'save') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $departmentName = trim($_POST['department_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($departmentName === '') {
            $errorMessage = 'Department name is required.';
        } elseif (strlen($departmentName) > 100) {
            $errorMessage = 'Department name must not exceed 100 characters.';
        } elseif (strlen($description) > 255) {
            $errorMessage = 'Description must not exceed 255 characters.';
        } else {
            try {
                if ($id !== false && $id !== null && $id > 0) {
                    $stmt = $pdo->prepare(
                        'UPDATE departments
                         SET department_name = ?, description = ?
                         WHERE id = ?'
                    );
                    $stmt->execute([
                        $departmentName,
                        $description !== '' ? $description : null,
                        $id
                    ]);

                    header('Location: departments.php?updated=1');
                    exit;
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO departments (department_name, description)
                         VALUES (?, ?)'
                    );
                    $stmt->execute([
                        $departmentName,
                        $description !== '' ? $description : null
                    ]);

                    header('Location: departments.php?added=1');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Failed to save department: ' . $e->getMessage());
                $errorMessage = 'Unable to save department. Please try again.';
            }
        }

        if ($id !== false && $id !== null && $id > 0) {
            $editingDepartment = [
                'id' => $id,
                'department_name' => $departmentName,
                'description' => $description
            ];
        }
    }
}

if (isset($_GET['added'])) {
    $successMessage = 'Department added successfully.';
} elseif (isset($_GET['updated'])) {
    $successMessage = 'Department updated successfully.';
} elseif (isset($_GET['deleted'])) {
    $successMessage = 'Department deleted successfully.';
}

try {
    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $stmt = $pdo->prepare(
            'SELECT id, department_name, description
             FROM departments WHERE id = ?'
        );
        $stmt->execute([(int) $_GET['edit']]);
        $editingDepartment = $stmt->fetch() ?: null;

        if (!$editingDepartment) {
            $errorMessage = 'Department not found.';
        }
    }

    $departments = $pdo->query(
        'SELECT id, department_name, description
         FROM departments
         ORDER BY id'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load departments: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load departments. Please try again later.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?> - IQA Office</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>IQA Office Management System</h1>
    <p>Department Management</p>
</header>

<main>
    <p><a href="index.php">← Back to Dashboard</a></p>
    <h2>Manage Departments</h2>
    <p>View and manage office departments.</p>

    <?php if ($errorMessage !== ''): ?>
        <p role="alert"><?php echo h($errorMessage); ?></p>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <p role="status"><?php echo h($successMessage); ?></p>
    <?php endif; ?>

    <h3><?php echo $editingDepartment ? 'Edit Department' : 'Add Department'; ?></h3>

    <form method="post" action="departments.php<?php echo $editingDepartment ? '?edit=' . (int) $editingDepartment['id'] : ''; ?>">
        <input type="hidden" name="action" value="save">
        <?php if ($editingDepartment): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editingDepartment['id']; ?>">
        <?php endif; ?>

        <p>
            <label for="department_name">Department Name:</label><br>
            <input type="text" id="department_name" name="department_name"
                   maxlength="100" required
                   value="<?php echo h($editingDepartment['department_name'] ?? ''); ?>">
        </p>

        <p>
            <label for="description">Description:</label><br>
            <textarea id="description" name="description" maxlength="255" rows="3"><?php echo h($editingDepartment['description'] ?? ''); ?></textarea>
        </p>

        <button type="submit">
            <?php echo $editingDepartment ? 'Update Department' : 'Add Department'; ?>
        </button>

        <?php if ($editingDepartment): ?>
            <a href="departments.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h3>Department List</h3>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Department Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($departments)): ?>
            <tr><td colspan="4">No departments found.</td></tr>
        <?php else: ?>
            <?php foreach ($departments as $department): ?>
                <tr>
                    <td><?php echo (int) $department['id']; ?></td>
                    <td><?php echo h($department['department_name']); ?></td>
                    <td><?php echo h($department['description'] ?? ''); ?></td>
                    <td>
                        <a href="departments.php?edit=<?php echo (int) $department['id']; ?>">Edit</a>

                        <form method="post" action="departments.php"
                              style="display:inline"
                              onsubmit="return confirm('Are you sure you want to delete this department?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int) $department['id']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</main>

<footer>
    <p>© VIBOL 2026 - IQA Office Management System</p>
</footer>
</body>
</html>
