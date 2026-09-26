-- Equivalent MySQL 8.4 SQL for 2026-09-25-100000_AddBlockThreeOperations.php
ALTER TABLE application_documents
  ADD COLUMN scan_locked_at DATETIME NULL AFTER scan_status,
  ADD COLUMN scan_locked_by VARCHAR(120) NULL AFTER scan_locked_at,
  ADD KEY idx_document_scan_claim (scan_status, scan_locked_at, created_at);
ALTER TABLE admission_notification_outbox
  ADD COLUMN provider_message_id VARCHAR(190) NULL AFTER sent_at;

CREATE TABLE in_app_notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  event_name VARCHAR(120) NOT NULL,
  title VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  action_url VARCHAR(500) NULL,
  read_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_in_app_recipient (tenant_id, user_id, read_at, created_at),
  CONSTRAINT fk_in_app_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rollback drops in-app delivery history. Export it first if retention policy
-- requires preservation, then drop the table, scan-claim index, and two columns.
