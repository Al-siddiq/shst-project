-- Phase 2 equivalent SQL for 2026-09-23-100000_AddTrustedMutationFoundation.php
-- Target: MySQL 8.4 LTS / InnoDB / utf8mb4 / READ COMMITTED.

-- PRE-FLIGHT: every query must return zero rows before applying DDL.
SELECT MIN(id) AS id FROM applicant_applications
GROUP BY tenant_id, applicant_profile_id, admission_cycle_id, programme_opening_id
HAVING COUNT(*)>1 LIMIT 20;
SELECT d.id FROM application_documents d JOIN applicant_profiles p ON p.id=d.applicant_profile_id WHERE d.tenant_id<>p.tenant_id LIMIT 20;
SELECT d.id FROM application_documents d JOIN applicant_applications a ON a.id=d.application_id WHERE d.application_id IS NOT NULL AND d.tenant_id<>a.tenant_id LIMIT 20;
SELECT a.id FROM applicant_applications a JOIN applicant_profiles p ON p.id=a.applicant_profile_id WHERE a.tenant_id<>p.tenant_id LIMIT 20;
SELECT a.id FROM applicant_applications a JOIN admission_cycles c ON c.id=a.admission_cycle_id WHERE a.tenant_id<>c.tenant_id LIMIT 20;
SELECT a.id FROM applicant_applications a JOIN admission_programme_openings o ON o.id=a.programme_opening_id WHERE a.tenant_id<>o.tenant_id LIMIT 20;
SELECT o.id FROM admission_programme_openings o JOIN admission_cycles c ON c.id=o.admission_cycle_id WHERE o.tenant_id<>c.tenant_id LIMIT 20;
SELECT o.id FROM admission_programme_openings o JOIN programmes p ON p.id=o.programme_id WHERE o.tenant_id<>p.tenant_id LIMIT 20;

ALTER TABLE applicant_applications
  ADD COLUMN lock_version INT UNSIGNED NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN submission_version INT UNSIGNED NOT NULL DEFAULT 0 AFTER submission_snapshot_id,
  DROP INDEX idx_applicant_programme_draft,
  ADD UNIQUE KEY uq_applicant_programme_application (tenant_id, applicant_profile_id, admission_cycle_id, programme_opening_id);

ALTER TABLE application_submission_snapshots
  ADD COLUMN submission_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER application_number,
  DROP INDEX uq_application_submission_snapshot,
  ADD UNIQUE KEY uq_application_submission_snapshot_version (tenant_id, applicant_application_id, submission_version);

ALTER TABLE application_documents
  ADD COLUMN storage_state VARCHAR(30) NOT NULL DEFAULT 'legacy_unverified' AFTER checksum_sha256,
  ADD COLUMN scan_status VARCHAR(30) NOT NULL DEFAULT 'not_scanned' AFTER storage_state,
  ADD COLUMN scan_attempts INT UNSIGNED NOT NULL DEFAULT 0 AFTER scan_status,
  ADD COLUMN scan_error VARCHAR(500) NULL AFTER scan_attempts,
  ADD COLUMN scanned_at DATETIME NULL AFTER scan_error;

ALTER TABLE admission_notification_outbox
  ADD COLUMN idempotency_key VARCHAR(64) NULL AFTER payload_json,
  ADD COLUMN locked_at DATETIME NULL AFTER attempts,
  ADD COLUMN locked_by VARCHAR(120) NULL AFTER locked_at,
  ADD COLUMN last_error TEXT NULL AFTER locked_by,
  ADD COLUMN failed_at DATETIME NULL AFTER last_error,
  ADD COLUMN sent_at DATETIME NULL AFTER failed_at,
  ADD UNIQUE KEY uq_admission_outbox_idempotency (tenant_id, channel, idempotency_key),
  ADD KEY idx_admission_outbox_claim (status, available_at, locked_at);

CREATE TABLE idempotency_records (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id INT UNSIGNED NOT NULL,
  operation VARCHAR(120) NOT NULL,
  idempotency_key VARCHAR(100) NOT NULL,
  request_hash VARCHAR(64) NOT NULL,
  resource_type VARCHAR(120) NULL,
  resource_id VARCHAR(120) NULL,
  response_json LONGTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'completed',
  created_at DATETIME NOT NULL,
  expires_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_idempotency_operation_key (tenant_id, operation, idempotency_key),
  KEY idx_idempotency_expiry (tenant_id, expires_at),
  CONSTRAINT fk_idempotency_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tenant_cache_revisions (
  tenant_id INT UNSIGNED NOT NULL,
  namespace VARCHAR(120) NOT NULL,
  revision BIGINT UNSIGNED NOT NULL DEFAULT 1,
  updated_at DATETIME NULL,
  PRIMARY KEY (tenant_id, namespace),
  CONSTRAINT fk_cache_revision_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE applicant_profiles ADD UNIQUE KEY uq_applicant_profiles_tenant_id (tenant_id, id);
ALTER TABLE admission_cycles ADD UNIQUE KEY uq_admission_cycles_tenant_id (tenant_id, id);
ALTER TABLE programmes ADD UNIQUE KEY uq_programmes_tenant_id (tenant_id, id);
ALTER TABLE admission_programme_openings ADD UNIQUE KEY uq_admission_openings_tenant_id (tenant_id, id);
ALTER TABLE applicant_applications ADD UNIQUE KEY uq_applicant_applications_tenant_id (tenant_id, id);
ALTER TABLE application_documents ADD UNIQUE KEY uq_application_documents_tenant_id (tenant_id, id);
ALTER TABLE admission_programme_openings
  ADD CONSTRAINT fk_opening_tenant_cycle FOREIGN KEY (tenant_id, admission_cycle_id)
    REFERENCES admission_cycles (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_opening_tenant_programme FOREIGN KEY (tenant_id, programme_id)
    REFERENCES programmes (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE applicant_applications
  ADD CONSTRAINT fk_application_tenant_profile FOREIGN KEY (tenant_id, applicant_profile_id)
    REFERENCES applicant_profiles (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_application_tenant_cycle FOREIGN KEY (tenant_id, admission_cycle_id)
    REFERENCES admission_cycles (tenant_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_application_tenant_opening FOREIGN KEY (tenant_id, programme_opening_id)
    REFERENCES admission_programme_openings (tenant_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE application_documents
  ADD CONSTRAINT fk_document_tenant_profile FOREIGN KEY (tenant_id, applicant_profile_id)
    REFERENCES applicant_profiles (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT fk_document_tenant_application FOREIGN KEY (tenant_id, application_id)
    REFERENCES applicant_applications (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT;

-- Existing files intentionally remain legacy_unverified and fail closed. They
-- require reconciliation/scanning before an explicit transition to active/clean.
-- Rollback is destructive for idempotency/scan/outbox operational metadata and
-- must only run after export and an approved recovery decision.
-- Mechanical rollback must drop the two composite document foreign keys before
-- their supporting composite indexes and columns. See the synchronized PHP
-- migration for the complete ordered rollback.
