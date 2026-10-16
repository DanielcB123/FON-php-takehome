<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/layout.php';

$pdo    = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hidden `id` drives which row gets updated.
    $id    = (int) ($_POST['id'] ?? 0);
    // Normalize user input early so all validation and persistence uses
    // the same canonical representation.
    $input = [
        'company_name'      => trim((string) ($_POST['company_name'] ?? '')),
        'contact_email'     => trim((string) ($_POST['contact_email'] ?? '')),
        'subscription_plan' => (string) ($_POST['subscription_plan'] ?? 'starter'),
        'status'            => (string) ($_POST['status'] ?? 'active'),
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
    if (!in_array($input['status'], STATUSES, true)) {
        $errors[] = 'Unknown status.';
    }
    if (!is_numeric($input['monthly_revenue']) || (float) $input['monthly_revenue'] < 0) {
        $errors[] = 'Monthly revenue must be a number of 0 or more.';
    }
    // Execute UPDATE only when every field passes validation.
    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE customers
             SET company_name = :company_name,
                 contact_email = :contact_email,
                 subscription_plan = :subscription_plan,
                 status = :status,
                 monthly_revenue = :monthly_revenue
             WHERE id = :id'
        );
        $stmt->execute([
            ':company_name'      => $input['company_name'],
            ':contact_email'     => $input['contact_email'],
            ':subscription_plan' => $input['subscription_plan'],
            ':status'            => $input['status'],
            ':monthly_revenue'   => (float) $input['monthly_revenue'],
            ':id'                => $id,
        ]);
        // Post/Redirect/Get prevents accidental duplicate updates on refresh.
        header('Location: index.php?msg=updated');
        exit;
    }

    // When validation fails, keep user-entered values visible in the form.
    $customer = $input + ['id' => $id];
} else {
    // GET branch: fetch the customer to edit.
    $id   = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $customer = $stmt->fetch();
    // Hard 404 for unknown ids keeps behavior explicit and debuggable.
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
    <!-- Validation errors are escaped before rendering back to the page. -->
    <ul class="errors">
        <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" class="card">
    <!-- Keep the customer id in sync with $_POST['id'] used in the update query. -->
    <input type="hidden" name="id" value="<?= (int) $customer['id'] ?>">
    <label>Company name
        <input type="text" name="company_name" value="<?= e($customer['company_name']) ?>" required>
    </label>
    <label>Contact email
        <input type="email" name="contact_email" value="<?= e($customer['contact_email']) ?>" required>
    </label>
    <label>Plan
        <select name="subscription_plan">
            <?php foreach (PLANS as $p): ?>
                <option value="<?= e($p) ?>" <?= ($customer['subscription_plan'] ?? '') === $p ? 'selected' : '' ?>><?= e(ucfirst($p)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Status
        <select name="status">
            <?php foreach (STATUSES as $status): ?>
                <option value="<?= e($status) ?>" <?= ($customer['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
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
