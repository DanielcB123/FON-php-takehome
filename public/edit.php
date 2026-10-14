<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/layout.php';

$pdo    = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int) ($_POST['id'] ?? 0);
    $input = [
        'company_name'      => trim((string) ($_POST['company_name'] ?? '')),
        'contact_email'     => trim((string) ($_POST['contact_email'] ?? '')),
        'subscription_plan' => (string) ($_POST['subscription_plan'] ?? 'starter'),
        'monthly_revenue'   => trim((string) ($_POST['monthly_revenue'] ?? '')),
    ];

    if ($input['company_name'] === '') {
        $errors[] = 'Company name is required.';
    }
    if (!filter_var($input['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid contact email is required.';
    }
    if (!in_array($input['subscription_plan'], PLANS, true)) {
        $errors[] = 'Unknown plan.';
    }
    if (!is_numeric($input['monthly_revenue']) || (float) $input['monthly_revenue'] < 0) {
        $errors[] = 'Monthly revenue must be a number of 0 or more.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE customers
             SET company_name = :company_name,
                 contact_email = :contact_email,
                 subscription_plan = :subscription_plan,
                 monthly_revenue = :monthly_revenue
             WHERE id = :id'
        );
        $stmt->execute([
            ':company_name'      => $input['company_name'],
            ':contact_email'     => $input['contact_email'],
            ':subscription_plan' => $input['subscription_plan'],
            ':monthly_revenue'   => (float) $input['monthly_revenue'],
            ':id'                => $id,
        ]);
        header('Location: index.php?msg=updated');
        exit;
    }

    $customer = $input + ['id' => $id];
} else {
    $id   = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $customer = $stmt->fetch();

    if (!$customer) {
        http_response_code(404);
        render_header('Not found');
        echo '<p>No customer with that id.</p>';
        render_footer();
        exit;
    }
}

render_header('Edit customer');
?>

<?php if ($errors): ?>
    <ul class="errors">
        <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" class="card">
    <input type="hidden" name="customer_id" value="<?= (int) $customer['id'] ?>">
    <label>Company name
        <input type="text" name="company_name" value="<?= e($customer['company_name']) ?>" required>
    </label>
    <label>Contact email
        <input type="email" name="contact_email" value="<?= e($customer['contact_email']) ?>" required>
    </label>
    <label>Plan
        <select name="subscription_plan">
            <?php foreach (PLANS as $p): ?>
                <option value="<?= e($p) ?>" <?= ($customer['plan'] ?? '') === $p ? 'selected' : '' ?>><?= e(ucfirst($p)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Monthly revenue (USD)
        <input type="number" step="0.01" min="0" name="monthly_revenue" value="<?= e((string) $customer['monthly_revenue']) ?>" required>
    </label>
    <div class="actions">
        <button type="submit">Save changes</button>
        <a href="index.php">Cancel</a>
    </div>
</form>

<?php render_footer(); ?>
