<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/layout.php';

const PER_PAGE = 10;

$pdo  = db();
// Normalize query params once so all later logic can rely on typed values.
$page = max(1, (int) ($_GET['page'] ?? 1));
$plan = $_GET['plan'] ?? '';
// Optional free-text filter used to search by company name.
$q    = trim((string) ($_GET['q'] ?? ''));

// Build the WHERE clause for the optional plan filter.
// BUG (Part 1.4 Security): String interpolation in SQL allowed SQL injection via `plan`.
// $where = '';
// if ($plan !== '') {
//     $where = "WHERE subscription_plan = '{$plan}'";
// }
//
// FIX: Build SQL with placeholders and parameter arrays.
// `$whereParts` stores SQL fragments that are safe to join with AND.
// `$params` stores bound values for those fragments in the same request.
$whereParts = [];
$params = [];

// Plan filter is optional, but if present must be from our allow-list.
// Invalid plan values are ignored so they cannot affect query semantics.
if ($plan !== '') {
    if (in_array($plan, PLANS, true)) {
        $whereParts[] = 'subscription_plan = :plan';
        $params[':plan'] = $plan;
    } else {
        $plan = '';
    }
}

// Company search is also optional. We use LIKE with surrounding wildcards
// so partial matches work (e.g. "acme" matches "Acme Logistics").
if ($q !== '') {
    $whereParts[] = 'company_name LIKE :q';
    $params[':q'] = '%' . $q . '%';
}

// Assemble one WHERE clause that works for:
// - no filters
// - only plan
// - only search
// - both filters together
$where = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// Total row count for pagination.
// BUG (Part 1.1): `query()` + interpolated WHERE reused unsafe SQL from above.
// $total = (int) $pdo->query("SELECT COUNT(*) FROM customers {$where}")->fetchColumn();
//
// FIX: Prepared statement for the count query.
// We run count and row queries separately so pagination is based on all
// matching rows, not just the current page payload.
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM customers {$where}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

// BUG (Part 1.1): intdiv() dropped remainder pages; e.g. 23 rows became 2 pages, hiding rows 21-23.
// $totalPages = intdiv($total, PER_PAGE);
//
// FIX: ceil() retains partial last page.
$totalPages = max(1, (int) ceil($total / PER_PAGE));
// Clamp requested page into valid range to avoid negative/empty offsets.
$page = min($page, $totalPages);

// Fetch the current page.
// BUG (Part 1.1): Page 1 started at row 11 because offset used `$page * PER_PAGE`.
// $offset = $page * PER_PAGE;
//
// FIX: First page must start at zero offset.
$offset = ($page - 1) * PER_PAGE;

// BUG (Part 1.4 Security): Raw SQL string was still assembled for row fetch.
// $rows = $pdo->query(
//     "SELECT id, company_name, contact_email, subscription_plan, monthly_revenue, created_date
//      FROM customers {$where}
//      ORDER BY company_name ASC
//      LIMIT " . PER_PAGE . " OFFSET {$offset}"
// )->fetchAll();
//
// FIX: Prepare/execute with placeholders; keep LIMIT/OFFSET as safe ints.
$rowsSql = "SELECT id, company_name, contact_email, subscription_plan, status, monthly_revenue, created_date
            FROM customers {$where}
            ORDER BY company_name ASC
            LIMIT :limit OFFSET :offset";
$rowsStmt = $pdo->prepare($rowsSql);
// Bind dynamic filter params first (all strings in this query).
foreach ($params as $key => $value) {
    $rowsStmt->bindValue($key, $value, PDO::PARAM_STR);
}
// Bind pagination params as ints to keep LIMIT/OFFSET strictly numeric.
$rowsStmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$rowsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$rowsStmt->execute();
$rows = $rowsStmt->fetchAll();

// Monthly recurring revenue.
// BUG (Part 1.2): MRR was calculated from only the current page, so it changed per page/filter.
// $mrr = 0.0;
// foreach ($rows as $row) {
//     $mrr += (float) $row['monthly_revenue'];
// }
//
// FIX: Calculate MRR from the full customer base, excluding cancelled.
// This KPI is intentionally independent from list filters/pagination.
$mrr = (float) $pdo->query(
    "SELECT COALESCE(SUM(monthly_revenue), 0)
     FROM customers
     WHERE status <> 'cancelled'"
)->fetchColumn();

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
    <label>Company
        <input class="filter-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Search company name">
    </label>
    <label>Plan
        <select class="filter-control" name="plan">
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
            <th>Status</th>
            <th class="num">Monthly</th>
            <th>Since</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
        <tr><td colspan="7" class="empty">No customers found.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
        <tr>
            <!-- Escape company name to prevent stored XSS from DB-sourced text. -->
            <td><?= e($row['company_name']) ?></td>
            <td><?= e($row['contact_email']) ?></td>
            <td><span class="pill pill-<?= e($row['subscription_plan']) ?>"><?= e(ucfirst($row['subscription_plan'])) ?></span></td>
            <td><?= e(ucfirst((string) $row['status'])) ?></td>
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
            <a href="?page=<?= $i ?>&plan=<?= e($plan) ?>&q=<?= urlencode($q) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</nav>

<?php render_footer(); ?>
