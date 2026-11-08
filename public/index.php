<?php

require_once __DIR__ . '/includes/db.php';

// Retrieve total number of employees
$totalEmployees = $pdo->query(
    "SELECT COUNT(*) FROM employees"
)->fetchColumn();

// Retrieve total number of departments
$totalDepartments = $pdo->query(
    "SELECT COUNT(*) FROM departments"
)->fetchColumn();

// Retrieve employee distribution by department
$employeeDistribution = $pdo->query(
    "SELECT
        d.department_name,
        COUNT(e.id) AS employee_count
     FROM departments d
     LEFT JOIN employees e
        ON d.id = e.department_id
     GROUP BY d.id, d.department_name
     ORDER BY d.department_name"
)->fetchAll();

// Read application version from environment
$appVersion = getenv('APP_VERSION') ?: 'v1.0.0';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>IQA Office Management System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header>
        <h1>IQA Office Management System</h1>
        <p>Welcome to the IQA Office Management System</p>
    </header>

    <main>

        <h2>Dashboard</h2>

        <p>Overview of office information and resources.</p>

        <!-- Dashboard Statistics -->
        <div class="stats-grid">

            <div class="stat-card">
                <h3>Total Employees</h3>

                <p class="stat-number">
                    <?= htmlspecialchars((string)$totalEmployees) ?>
                </p>
            </div>

            <div class="stat-card">
                <h3>Total Departments</h3>

                <p class="stat-number">
                    <?= htmlspecialchars((string)$totalDepartments) ?>
                </p>
            </div>

        </div>

        <!-- Employee Distribution by Department -->
        <section class="distribution-section">

            <h2>Employee Distribution by Department</h2>

            <p>
                Overview of employees assigned to each department.
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Total Employees</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($employeeDistribution as $department): ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                    $department['department_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string)$department['employee_count'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <?php if (empty($employeeDistribution)): ?>

                        <tr>
                            <td colspan="2">
                                No department records found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>

        </section>

        <!-- Navigation Menu -->
        <div class="menu">

            <a href="employees.php">Manage Employees</a>

            <a href="departments.php">Manage Departments</a>

        </div>

    </main>

    <footer>

        <p>&copy; VIBOL 2026 - IQA Office Management System</p>

        <p class="version">
            Application Version:
            <?= htmlspecialchars($appVersion, ENT_QUOTES, 'UTF-8') ?>
        </p>

    </footer>

</body>

</html>
