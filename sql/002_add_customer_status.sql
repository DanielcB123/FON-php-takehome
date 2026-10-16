ALTER TABLE customers
    ADD COLUMN status ENUM('active','trialing','cancelled') NOT NULL DEFAULT 'active'
    AFTER subscription_plan;

UPDATE customers
SET status = 'active'
WHERE status IS NULL OR status = '';
