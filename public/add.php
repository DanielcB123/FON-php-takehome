<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/layout.php';

$errors = [];
$input  = [
    'company_name'      => '',
    'contact_email'     => '',
    'subscription_plan' => 'starter',
    'status'            => 'active',
    'monthly_revenue'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($input as $key => $_) {
        $input[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if ($input['company_name'] === '') {
        $errors[] = 'Company name is required.';
    }
    if (!filter_var($input['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid contact email is required.';
    }
    if (!in_array($input['subscription_plan'], PLANS, true)) {
        $errors[] = 'Unknown plan.';
    }
    if (!in_array($input['status'], STATUSES, true)) {
        $errors[] = 'Unknown status.';
    }
    if (!is_numeric($input['monthly_revenue']) || (float) $input['monthly_revenue'] < 0) {
        $errors[] = 'Monthly revenue must be a number of 0 or more.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO customers (company_name, contact_email, subscription_plan, status, monthly_revenue, created_date)
             VALUES (:company_name, :contact_email, :subscription_plan, :status, :monthly_revenue, CURDATE())'
        );
        try {
            $stmt->execute([
                ':company_name'      => $input['company_name'],
                ':contact_email'     => $input['contact_email'],
                ':subscription_plan' => $input['subscription_plan'],
                ':status'            => $input['status'],
                ':monthly_revenue'   => (float) $input['monthly_revenue'],
            ]);
            header('Location: index.php?msg=added');
            exit;
        } catch (PDOException $e) {
            // 23000 = integrity constraint violation (duplicate email)
            $errors[] = $e->getCode() === '23000'
                ? 'A customer with that email already exists.'
                : 'Could not save the customer.';
        }
    }
}

render_header('Add customer');
?>

<?php if ($errors): ?>
    <ul class="errors">
        <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" class="card">
    <label>Company name
        <input type="text" name="company_name" value="<?= e($input['company_name']) ?>" required>
    </label>
    <label>Contact email
        <input type="email" name="contact_email" value="<?= e($input['contact_email']) ?>" required>
    </label>
    <label>Plan
        <select name="subscription_plan">
            <?php foreach (PLANS as $p): ?>
                <option value="<?= e($p) ?>" <?= $input['subscription_plan'] === $p ? 'selected' : '' ?>><?= e(ucfirst($p)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Status
        <select name="status">
            <?php foreach (STATUSES as $status): ?>
                <option value="<?= e($status) ?>" <?= $input['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Monthly revenue (USD)
        <input type="number" step="0.01" min="0" name="monthly_revenue" value="<?= e($input['monthly_revenue']) ?>" required>
    </label>
    <div class="actions">
        <button type="submit">Save customer</button>
        <a href="index.php">Cancel</a>
    </div>
</form>

<?php render_footer(); ?>
