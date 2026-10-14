<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function render_header(string $title): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= e($title) ?> · Subscription Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a href="index.php" class="brand">Subscription Admin</a>
    <nav>
        <a href="index.php">Customers</a>
        <a href="add.php">Add customer</a>
    </nav>
</header>
<main>
    <h1><?= e($title) ?></h1>
    <?php
}

function render_footer(): void
{
    ?>
</main>
</body>
</html>
    <?php
}

function flash(): ?string
{
    if (!isset($_GET['msg'])) {
        return null;
    }
    return match ($_GET['msg']) {
        'added'   => 'Customer added.',
        'updated' => 'Customer updated.',
        default   => null,
    };
}
