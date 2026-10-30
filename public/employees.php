<?php
ob_start();
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Manage Employees';
$errorMessage = '';
$successMessage = '';
$editingEmployee = null;

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $errorMessage = 'Invalid employee ID.';
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM employees WHERE id = ?');
                $stmt->execute([$id]);

                header('Location: employees.php?deleted=1');
                exit;
            } catch (PDOException $e) {
                error_log('Failed to delete employee: ' . $e->getMessage());
                $errorMessage = 'Unable to delete employee. Please try again.';
            }
        }
    } elseif ($action === 'save') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $employeeName = trim($_POST['employee_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $departmentId = $_POST['department_id'] ?? '';

        if ($employeeName === '') {
            $errorMessage = 'Employee name is required.';
        } elseif (strlen($employeeName) > 100) {
            $errorMessage = 'Employee name must not exceed 100 characters.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        } elseif (strlen($email) > 150) {
            $errorMessage = 'Email must not exceed 150 characters.';
        } elseif (!ctype_digit((string) $departmentId) || (int) $departmentId < 1) {
            $errorMessage = 'Please select a department.';
        } else {
            try {
                $departmentCheck = $pdo->prepare(
                    'SELECT id FROM departments WHERE id = ?'
                );
                $departmentCheck->execute([(int) $departmentId]);

                if (!$departmentCheck->fetch()) {
                    $errorMessage = 'The selected department does not exist.';
                } elseif ($id !== false && $id !== null && $id > 0) {
                    $stmt = $pdo->prepare(
                        'UPDATE employees
                         SET employee_name = ?, email = ?, department_id = ?
                         WHERE id = ?'
                    );
                    $stmt->execute([
                        $employeeName,
                        $email !== '' ? $email : null,
                        (int) $departmentId,
                        $id
                    ]);

                    header('Location: employees.php?updated=1');
                    exit;
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO employees
                         (employee_name, email, department_id)
                         VALUES (?, ?, ?)'
                    );
                    $stmt->execute([
                        $employeeName,
                        $email !== '' ? $email : null,
                        (int) $departmentId
                    ]);

                    header('Location: employees.php?added=1');
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Failed to save employee: ' . $e->getMessage());

                if ($e->getCode() === '23000') {
                    $errorMessage = 'This email may already be in use.';
                } else {
                    $errorMessage = 'Unable to save employee. Please try again.';
                }
            }
        }

        if ($id !== false && $id !== null && $id > 0) {
            $editingEmployee = [
                'id' => $id,
                'employee_name' => $employeeName,
                'email' => $email,
                'department_id' => (int) $departmentId
            ];
        }
    }
}

if (isset($_GET['added'])) {
    $successMessage = 'Employee added successfully.';
} elseif (isset($_GET['updated'])) {
    $successMessage = 'Employee updated successfully.';
} elseif (isset($_GET['deleted'])) {
    $successMessage = 'Employee deleted successfully.';
}

try {
    $departments = $pdo->query(
        'SELECT id, department_name FROM departments ORDER BY department_name'
    )->fetchAll();

    if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
        $stmt = $pdo->prepare(
            'SELECT id, employee_name, email, department_id
             FROM employees WHERE id = ?'
        );
        $stmt->execute([(int) $_GET['edit']]);
        $editingEmployee = $stmt->fetch() ?: null;

        if (!$editingEmployee) {
            $errorMessage = 'Employee not found.';
        }
    }

    $employees = $pdo->query(
        'SELECT e.id, e.employee_name, e.email, e.department_id,
                d.department_name
         FROM employees e
         LEFT JOIN departments d ON e.department_id = d.id
         ORDER BY e.id'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load employee data: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load employee data. Please try again later.');
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
    <p>Employee Management</p>
</header>

<main>
    <p><a href="index.php">← Back to Dashboard</a></p>
    <h2>Manage Employees</h2>
    <p>View and manage office employees.</p>

    <?php if ($errorMessage !== ''): ?>
        <p role="alert"><?php echo h($errorMessage); ?></p>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <p role="status"><?php echo h($successMessage); ?></p>
    <?php endif; ?>

    <h3><?php echo $editingEmployee ? 'Edit Employee' : 'Add Employee'; ?></h3>

    <form method="post" action="employees.php<?php echo $editingEmployee ? '?edit=' . (int) $editingEmployee['id'] : ''; ?>">
        <input type="hidden" name="action" value="save">
        <?php if ($editingEmployee): ?>
            <input type="hidden" name="id" value="<?php echo (int) $editingEmployee['id']; ?>">
        <?php endif; ?>

        <p>
            <label for="employee_name">Employee Name:</label><br>
            <input type="text" id="employee_name" name="employee_name"
                   maxlength="100" required
                   value="<?php echo h($editingEmployee['employee_name'] ?? ''); ?>">
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" maxlength="150"
                   value="<?php echo h($editingEmployee['email'] ?? ''); ?>">
        </p>

        <p>
            <label for="department_id">Department:</label><br>
            <select id="department_id" name="department_id" required>
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo (int) $department['id']; ?>"
                        <?php echo (string) ($editingEmployee['department_id'] ?? '') === (string) $department['id'] ? 'selected' : ''; ?>>
                        <?php echo h($department['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <button type="submit">
            <?php echo $editingEmployee ? 'Update Employee' : 'Add Employee'; ?>
        </button>

        <?php if ($editingEmployee): ?>
            <a href="employees.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h3>Employee List</h3>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Employee Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($employees)): ?>
            <tr><td colspan="5">No employee records found.</td></tr>
        <?php else: ?>
            <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?php echo (int) $employee['id']; ?></td>
                    <td><?php echo h($employee['employee_name']); ?></td>
                    <td><?php echo h($employee['email'] ?? ''); ?></td>
                    <td><?php echo h($employee['department_name'] ?? 'Unassigned'); ?></td>
                    <td>
                        <a href="employees.php?edit=<?php echo (int) $employee['id']; ?>">Edit</a>

                        <form method="post" action="employees.php"
                              style="display:inline"
                              onsubmit="return confirm('Are you sure you want to delete this employee?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int) $employee['id']; ?>">
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
