CREATE DATABASE IF NOT EXISTS saas_admin;
USE saas_admin;

DROP TABLE IF EXISTS customers;

CREATE TABLE customers (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    company_name    VARCHAR(120)  NOT NULL,
    contact_email   VARCHAR(190)  NOT NULL,
    subscription_plan ENUM('starter','growth','enterprise') NOT NULL DEFAULT 'starter',
    monthly_revenue DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_date    DATE          NOT NULL,
    UNIQUE KEY uq_contact_email (contact_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO customers (company_name, contact_email, subscription_plan, monthly_revenue, created_date) VALUES
('Acme Logistics',        'ops@acmelogistics.example',   'growth',     299.00, '2024-01-12'),
('Bluebird Dental',       'admin@bluebirddental.example','starter',     49.00, '2024-02-03'),
('Cobalt Analytics',      'cto@cobaltanalytics.example', 'enterprise',1499.00, '2024-02-19'),
('Driftwood Interiors',   'hello@driftwood.example',     'starter',     49.00, '2024-03-07'),
('Everline Fitness',      'team@everline.example',       'growth',     299.00, '2024-03-22'),
('Fairmont Realty Group', 'it@fairmontrealty.example',   'growth',     349.00, '2024-04-01'),
('Granite Peak Outdoors', 'orders@granitepeak.example',  'starter',     49.00, '2024-04-15'),
('Harborview Clinics',    'systems@harborview.example',  'enterprise',1899.00, '2024-05-02'),
('Ironclad Security',     'ops@ironclad.example',        'growth',     299.00, '2024-05-20'),
('Juniper & Sons Bakery', 'owner@junipersons.example',   'starter',     49.00, '2024-06-04'),
('Kestrel Aviation',      'dispatch@kestrelav.example',  'enterprise',2200.00, '2024-06-18'),
('Lumen Creative Co',     'studio@lumencreative.example','growth',     299.00, '2024-07-01'),
('Meridian Law LLP',      'admin@meridianlaw.example',   'growth',     399.00, '2024-07-15'),
('Northgate Auto',        'service@northgateauto.example','starter',    49.00, '2024-08-02'),
('Orchard Hill Farms',    'office@orchardhill.example',  'starter',     79.00, '2024-08-19'),
('Pinnacle Staffing',     'hr@pinnaclestaffing.example', 'growth',     299.00, '2024-09-03'),
('Quarry Street Coffee',  'hi@quarrystreet.example',     'starter',     49.00, '2024-09-21'),
('Riverstone Consulting', 'partners@riverstone.example', 'enterprise',1750.00, '2024-10-07'),
('Summit Pet Care',       'frontdesk@summitpet.example', 'starter',     49.00, '2024-10-25'),
('Tidewater Marine',      'docks@tidewatermarine.example','growth',    299.00, '2024-11-11'),
('Uptown Physio',         'booking@uptownphysio.example','starter',     49.00, '2024-12-01'),
('Vantage Point Media',   'ads@vantagepoint.example',    'growth',     329.00, '2025-01-09'),
('Willow Creek Vet',      'care@willowcreekvet.example', 'starter',     49.00, '2025-01-27');
