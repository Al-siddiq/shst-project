# Post-delivery Shield investigation

## Verified package state

`composer.lock` pins CodeIgniter Shield **v1.3.0**, source reference
`92b6864dcb4153b41feeb754d5368540aec9fe54`, with CodeIgniter Framework v4.7.3.
The current checkout did not contain `vendor/codeigniter4/shield`. A locked
`composer install` was attempted on 2026-09-27, but both distribution and source
downloads from GitHub were rejected by the execution proxy with HTTP 403. The
installed package source and runtime extension contract therefore could not be
inspected or executed in this environment.

This is materially different from merely assuming an API from documentation or
memory. Tenant-scoped authentication needs verified behavior for the v1.3.0:

- `auth_identities` uniqueness and validation;
- session authenticator credential lookup;
- identity provider/model customization;
- registration identity creation;
- email/phone verification and recovery lookup;
- user creation and primary group APIs.

## Decision

No tenant-aware authenticator/provider, Shield schema migration, or credential
provisioner is added in this reconciliation commit. Implementing one without the
actual package contract could create a parallel authentication path, bypass
Shield validation, or retain global identifier uniqueness while falsely claiming
tenant scope. Synthetic `tenant|identifier` secrets and vendor patches remain
prohibited.

The approved architecture remains unchanged: resolved tenant plus normalized
identifier plus credential, permanent singular membership, one primary Shield
group, tenant operational authorities, and separate platform identities. The
implementation gate is access to the exact locked Shield source and successful
Shield migration/session/recovery tests.

## Required completion procedure

1. Install exactly from `composer.lock` in an environment with package access.
2. Inspect and record the v1.3.0 classes/migrations listed above.
3. Prefer a documented configurable user provider/identity model/authenticator.
4. Add a forward application migration only after confirming Shield's table and
   index names. Preflight duplicate identities and preserve verification/recovery
   metadata.
5. Implement one atomic provisioning service: Shield user + tenant-scoped
   identity + primary group + permanent membership + required authorities + audit.
6. Roll back the complete operation if any database step fails; send verification
   externally only after commit.
7. Prove two tenants may independently use the same normalized identifier, while
   the same Shield user ID cannot bind twice and platform users cannot bind once.
8. Prove tenant-context login, generic non-enumerating recovery, verification,
   suspension/revocation, and cross-host rejection through real HTTP sessions.

Until those checks pass, tenant administrator credential provisioning, applicant
tenant-scoped authentication, the requested minimal manual-testing baseline, and
production readiness remain blocked.
