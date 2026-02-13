-- SQL to add more plans directly
INSERT INTO plans (name, price, duration, max_users, max_employees, storage_limit, enable_chatgpt, description, created_at, updated_at) VALUES
('Starter Plan', 4.99, 'month', 2, 10, 2048, 'on', 'Perfect for startups', NOW(), NOW()),
('Business Plan', 49.99, 'month', 50, 200, 20480, 'on', 'For medium businesses', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name);
