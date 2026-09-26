-- Phase 1 equivalent SQL for 2026-09-22-100000_HardenIdentityTenantBoundary.php
-- Target: MySQL 8.4 LTS, InnoDB, utf8mb4.
--
-- PRE-FLIGHT (must return zero rows; otherwise stop and create a distinct Shield
-- account for each extra tenant before applying this file):
SELECT user_id, COUNT(*) AS membership_count, GROUP_CONCAT(tenant_id ORDER BY tenant_id) AS tenant_ids
FROM tenant_memberships
GROUP BY user_id
HAVING COUNT(*) > 1;

-- BROAD-GROUP PRE-FLIGHT (must be reviewed before migrating legacy rows into
-- Shield; one tenant identity should have one primary broad group):
SELECT tenant_id, user_id, COUNT(DISTINCT group_name) AS broad_group_count,
       GROUP_CONCAT(DISTINCT group_name ORDER BY group_name) AS broad_groups
FROM tenant_iam_group_assignments
GROUP BY tenant_id, user_id
HAVING COUNT(DISTINCT group_name) > 1;

ALTER TABLE tenant_memberships
    ADD UNIQUE KEY uq_tenant_membership_user (user_id);

CREATE TABLE tenant_membership_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_membership_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    from_status VARCHAR(30) NULL,
    to_status VARCHAR(30) NOT NULL,
    reason VARCHAR(500) NULL,
    changed_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_membership_history_lookup (tenant_id, user_id, created_at),
    CONSTRAINT fk_membership_history_membership FOREIGN KEY (tenant_membership_id)
        REFERENCES tenant_memberships (id) ON DELETE CASCADE ON UPDATE RESTRICT,
    CONSTRAINT fk_membership_history_tenant FOREIGN KEY (tenant_id)
        REFERENCES tenants (id) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE platform_support_contexts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    public_token CHAR(48) NOT NULL,
    platform_user_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    reason VARCHAR(500) NOT NULL,
    access_mode VARCHAR(30) NOT NULL DEFAULT 'read_only',
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    started_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    ip_address VARCHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_platform_support_public_token (public_token),
    KEY idx_platform_support_actor (platform_user_id, status, expires_at),
    KEY idx_platform_support_tenant (tenant_id, status, expires_at),
    CONSTRAINT fk_platform_support_tenant FOREIGN KEY (tenant_id)
        REFERENCES tenants (id) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recovery/rollback limitations:
-- 1. Do not drop the unique membership constraint unless the singular-account ADR
--    has been formally superseded.
-- 2. Rollback removes support/history records and therefore requires an audit/data
--    retention decision before execution.
-- 3. Mechanical rollback commands, if explicitly approved:
-- DROP TABLE platform_support_contexts;
-- DROP TABLE tenant_membership_history;
-- ALTER TABLE tenant_memberships DROP INDEX uq_tenant_membership_user;
