<?php
require_once __DIR__ . '/includes/db.php';

// Retrieve live dashboard statistics
$totalEmployees = $pdo->query(
    "SELECT COUNT(*) FROM employees"
)->fetchColumn();

$totalDepartments = $pdo->query(
    "SELECT COUNT(*) FROM departments"
)->fetchColumn();

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
