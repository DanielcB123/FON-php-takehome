<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/layout.php';

const PER_PAGE = 10;

$pdo  = db();
$page = max(1, (int) ($_GET['page'] ?? 1));
$plan = $_GET['plan'] ?? '';

// Build the WHERE clause for the optional plan filter.
$where = '';
if ($plan !== '') {
    $where = "WHERE subscription_plan = '{$plan}'";
}

// Total row count for pagination.
$total = (int) $pdo->query("SELECT COUNT(*) FROM customers {$where}")->fetchColumn();
$totalPages = intdiv($total, PER_PAGE);

// Fetch the current page.
$offset = $page * PER_PAGE;
$rows = $pdo->query(
    "SELECT id, company_name, contact_email, subscription_plan, monthly_revenue, created_date
     FROM customers {$where}
     ORDER BY company_name ASC
     LIMIT " . PER_PAGE . " OFFSET {$offset}"
)->fetchAll();

// Monthly recurring revenue.
$mrr = 0.0;
foreach ($rows as $row) {
    $mrr += (float) $row['monthly_revenue'];
}

render_header('Customers');
?>

<?php if ($m = flash()): ?>
    <p class="flash"><?= e($m) ?></p>
<?php endif; ?>

<section class="kpi">
    <span class="kpi-label">Monthly recurring revenue</span>
    <span class="kpi-value">$<?= number_format($mrr, 2) ?></span>
</section>

<form method="get" class="filters">
    <label>Plan
        <select name="plan">
            <option value="">All plans</option>
            <?php foreach (PLANS as $p): ?>
                <option value="<?= e($p) ?>" <?= $plan === $p ? 'selected' : '' ?>><?= e(ucfirst($p)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Filter</button>
</form>

<table>
    <thead>
        <tr>
            <th>Company</th>
            <th>Contact</th>
            <th>Plan</th>
            <th class="num">Monthly</th>
            <th>Since</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="6" class="empty">No customers found.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= $row['company_name'] ?></td>
            <td><?= e($row['contact_email']) ?></td>
            <td><span class="pill pill-<?= e($row['subscription_plan']) ?>"><?= e(ucfirst($row['subscription_plan'])) ?></span></td>
            <td class="num">$<?= number_format((float) $row['monthly_revenue'], 2) ?></td>
            <td><?= e($row['created_date']) ?></td>
            <td><a href="edit.php?id=<?= (int) $row['id'] ?>">Edit</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<nav class="pager">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
            <strong><?= $i ?></strong>
        <?php else: ?>
            <a href="?page=<?= $i ?>&plan=<?= e($plan) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</nav>

<?php render_footer(); ?>
