# Per-Block Implementation Roadmap  
## Block 4: Fees and Levy Payment System

---

## Document Control

**Project:** SaaS Multi-Tenant Information Management System for Nigerian Schools of Health Sciences and Technology  
**Block:** Block 4  
**Block Name:** Fees and Levy Payment System  
**Target Users:** Developers, AI coding agents, technical leads, QA testers  
**Framework:** CodeIgniter 4  
**Authentication Foundation:** CodeIgniter Shield  
**Frontend Enhancement:** Vue.js Composition API where appropriate  
**Architecture:** Shared-schema SaaS multi-tenancy with strict tenant isolation  
**Primary Dependency:** Block 1 — SaaS and School Configuration  
**Functional Dependency:** Block 3 — Admission System for application and acceptance payment use cases  

---

# 1. Block 4 Purpose

Block 4 establishes the financial control foundation for each tenant institution.

The goal is to allow every School of Health Sciences and Technology using the SaaS platform to configure its own fees, generate invoices, record payments, verify payment status, manage debtors, apply waivers where permitted, and use payment status to control access to admission, clearance, course registration, examinations, result viewing, graduation clearance, and other institutional services.

This block must be built as a flexible Nigeria-aware fee and levy system, not as a hard-coded school-fee table.

---

# 2. Strategic Decision

The payment system shall be built as a **tenant-configurable financial rules engine**.

Each tenant school must be able to define its own:

- Fee types
- Fee amounts
- Fee applicability
- Payment deadlines
- Installment rules
- Payment restrictions
- Manual verification process
- Online payment gateway settings, where enabled
- Receipts and invoice references
- Waiver rules
- Debtor handling rules
- Operational control points

No fee name, fee amount, invoice pattern, payment rule, or eligibility rule shall be hard-coded for one institution.

---

# 3. Block 4 Position in the Overall Roadmap

Block 4 comes after:

1. **Block 1:** SaaS and School Configuration  
2. **Block 2:** Public Website and Institutional Showcase  
3. **Block 3:** Application and Admission System  

Block 4 must integrate with Block 3 because application fee and acceptance fee are part of the applicant journey.

Block 4 must also prepare for later blocks:

- Student records
- Course registration
- Course allocation
- Results
- Clearance
- Transcript requests
- Graduation processing

The financial control logic built in this block will be reused across all later blocks.

---

# 4. Block 4 Objective

At the end of Block 4, each tenant school must be able to:

- Define fee categories and fee items
- Create session-based fee schedules
- Assign fees to programmes, levels, student categories, or services
- Generate invoices for applicants and students
- Record online or manually verified payments
- Track full, partial, pending, waived, reversed, and unpaid balances
- Produce payment receipts
- View debtor lists
- Enforce payment rules against operational actions
- Audit all sensitive financial changes
- Generate basic financial reports
- Prevent cross-tenant financial leakage

---

# 5. Non-Negotiable Rules for Block 4

1. Every fee, invoice, payment, receipt, waiver, and debtor record must be tenant-scoped.

2. No tenant must see another tenant’s invoices, payments, fee schedules, or reports.

3. Fee names and fee amounts must come from the database.

4. Payment status must not be treated as a simple paid/unpaid field.

5. Manual payment verification must be supported because many Nigerian institutions still use bank deposits, transfers, tellers, or internal bursary confirmation.

6. Online payment integration must be adapter-based, not hard-wired to one gateway.

7. Every payment-sensitive action must be audited.

8. Receipts must be traceable and tied to a tenant, payer, invoice, payment record, and verification actor where applicable.

9. Financial permissions must use the two-layer authorization model.

10. Platform admin must not casually modify tenant financial records.

11. Tenant super admin and tenant admin access must still depend on operational authority.

12. Bursar, Accountant, Payment Verifier, and Finance Officer must be operational authorities, not Shield IAM groups.

13. Vue-powered interactions may be used for invoice generation, payment filters, debtor lists, and payment verification, but final sensitive actions must be server-confirmed.

14. Payment data must be protected more strictly than ordinary configuration data.

---

# 6. Block 4 Scope

## 6.1 In Scope

Block 4 includes:

- Fee category configuration
- Fee item configuration
- Fee schedule configuration
- Fee applicability rules
- Invoice generation
- Payment initialization
- Manual payment recording
- Manual payment verification
- Online payment callback foundation
- Receipt generation
- Payment status tracking
- Partial payment support
- Waiver and discount foundation
- Refund/reversal foundation
- Debtor list
- Payment eligibility checks
- Payment reports
- Financial audit trail
- Finance dashboard widgets
- Payment-related notifications

---

## 6.2 Out of Scope

The following are not to be fully implemented in Block 4:

- Full accounting/general ledger system
- Payroll
- Procurement
- Expense management
- Bank reconciliation automation
- Advanced tax accounting
- Multi-currency payment
- Scholarship management beyond basic waiver/covered status
- Full hostel management
- Full transcript request workflow
- Full graduation clearance workflow
- Full course registration workflow
- Full result publication workflow

However, Block 4 must provide payment control hooks that later blocks can call.

---

# 7. Users and Operational Authorities

## 7.1 IAM Groups Involved

Block 4 uses the existing first-layer IAM groups:

- Platform Admin
- Tenant Super Admin
- Tenant Admin
- Student
- Lecturer, only for restricted visibility where future academic fee-related views are needed

## 7.2 Operational Authorities Involved

Block 4 requires these operational authorities:

- Bursar
- Accountant
- Payment Verifier
- Finance Officer
- Admission Officer, limited to admission-related payment visibility
- Registrar, limited to payment clearance visibility where needed
- Tenant Super Admin, with controlled financial oversight
- Platform Support Officer, only if later added as platform-level support authority

## 7.3 Authority Rule

Financial operations must not depend only on broad IAM group.

A tenant admin cannot verify payment unless the user has the appropriate financial operational authority.

---

# 8. Core Modules Inside Block 4

---

## Module 1: Fee Category Configuration

### Purpose

To allow tenants to group fees logically.

### Required Capabilities

Tenant finance-authorized users must be able to:

- Create fee category
- Edit fee category
- Activate/deactivate fee category
- Set fee category sort order
- Mark category as admission-related, student-related, service-related, or system-related

### Example Fee Categories

The platform may ship with editable templates such as:

- Admission Fees
- School Fees
- Departmental Levies
- Examination Fees
- Practical/Laboratory Fees
- Professional/Indexing Fees
- Hostel Fees
- Graduation Fees
- Transcript and Document Fees
- Miscellaneous Fees

### Business Rule

Default categories are templates only. Each tenant must be able to rename, disable, or create its own categories.

### Audit Requirements

Audit:

- Fee category created
- Fee category updated
- Fee category activated
- Fee category deactivated

---

## Module 2: Fee Item Configuration

### Purpose

To define individual fees and levies that a school charges.

### Required Capabilities

Authorized finance users must be able to:

- Create fee item
- Edit fee item
- Assign fee item to category
- Set fee item description
- Mark fee as compulsory or optional
- Mark fee as refundable or non-refundable
- Activate/deactivate fee item

### Examples of Fee Items

- Application Fee
- Acceptance Fee
- Tuition Fee
- Departmental Levy
- Examination Fee
- Indexing Fee
- Laboratory Fee
- Practical Fee
- Hostel Fee
- ID Card Fee
- Handbook Fee
- Graduation Fee
- Transcript Fee
- Certificate Fee

### Business Rule

Fee item names are tenant-specific and must be database-driven.

### Audit Requirements

Audit:

- Fee item created
- Fee item updated
- Fee item activated
- Fee item deactivated

---

## Module 3: Fee Schedule Configuration

### Purpose

To define actual payable amounts for a specific academic session, programme, level, or category of payer.

### Required Capabilities

Authorized finance users must be able to:

- Create fee schedule
- Select academic session
- Select fee item
- Set amount
- Set applicable programme
- Set applicable department
- Set applicable level
- Set applicable student category
- Set applicable admission status
- Set new/returning student applicability
- Set due date
- Allow or disallow installment
- Set minimum required payment
- Activate/deactivate schedule

### Applicability Dimensions

A fee schedule may apply by:

- Tenant
- Academic session
- Programme
- Department
- Level
- Applicant category
- Student category
- New student
- Returning student
- Hostel resident
- Service request type
- Custom tenant-defined grouping

### Business Rule

A fee item is not payable until it is attached to an active fee schedule.

### Conflict Rule

The system must prevent ambiguous duplicate schedules for the same fee, same session, same programme, same level, and same payer category unless explicitly allowed by configuration.

### Audit Requirements

Audit:

- Fee schedule created
- Fee schedule updated
- Fee amount changed
- Fee deadline changed
- Installment rule changed
- Fee schedule activated
- Fee schedule deactivated

---

## Module 4: Payment Rule and Control Point Configuration

### Purpose

To allow each tenant to define which operations are blocked or allowed based on payment status.

### Required Control Points

The system must support payment control for:

- Application submission
- Admission acceptance
- Admission clearance
- Student activation
- Course registration
- Examination eligibility
- Result viewing
- Graduation clearance
- Transcript request
- Certificate collection
- Hostel allocation, where later implemented

### Required Capabilities

Authorized users must be able to define:

- Required fee item or category
- Minimum percentage required
- Full payment required
- Partial payment allowed
- Waiver allowed
- Deadline enforcement
- Grace period
- Operational action affected
- Message shown to user when blocked

### Business Rule

Payment control must be reusable by later blocks.

Course Registration Block, Results Block, Clearance Block, and Transcript Block must call the payment eligibility service instead of implementing their own payment logic.

### Audit Requirements

Audit:

- Payment control rule created
- Payment control rule updated
- Payment control rule activated
- Payment control rule deactivated

---

## Module 5: Invoice Generation

### Purpose

To create payable invoices for applicants, students, and service users.

### Required Capabilities

The system must be able to:

- Generate invoice for applicant
- Generate invoice for student
- Generate invoice for specific service request
- Generate invoice for one fee item
- Generate invoice for multiple fee items
- Generate session-based invoice
- Prevent duplicate active invoices where not allowed
- Cancel invoice where permitted
- Regenerate invoice where policy permits
- Track invoice status

### Invoice Statuses

The system must support:

- Draft
- Pending payment
- Partially paid
- Fully paid
- Cancelled
- Expired
- Waived
- Reversed

### Required Invoice Data

Each invoice must capture:

- Tenant ID
- Payer type: applicant, student, staff, external, other
- Payer ID where applicable
- Academic session where applicable
- Programme where applicable
- Level where applicable
- Invoice number
- Total amount
- Amount paid
- Balance
- Status
- Due date
- Created by
- Created at

### Invoice Number Rule

Invoice numbers must be tenant-scoped and generated using a configurable reference pattern.

Example:

- `INV/2026/000001`
- `PAY-SHST/2026/000045`
- `APP-FEE/2026/0010`

### Audit Requirements

Audit:

- Invoice generated
- Invoice cancelled
- Invoice expired
- Invoice regenerated
- Invoice manually adjusted, if allowed

---

## Module 6: Payment Initialization and Recording

### Purpose

To support both online and manual payment flows.

### Required Payment Channels

The system must support:

- Online payment gateway
- Bank transfer
- Bank deposit/teller
- POS/manual office payment
- Cash office payment, where tenant policy allows
- Scholarship/covered payment
- Waiver/discount
- Custom tenant-defined channel

### Required Capabilities

The system must be able to:

- Start online payment transaction
- Record manual payment evidence
- Upload payment proof where required
- Record payment reference
- Track pending payment
- Match payment to invoice
- Prevent overpayment unless allowed
- Support partial payment
- Support multiple payments against one invoice

### Business Rule

A payment record does not automatically mean verified payment unless the channel and tenant rule allow automatic verification.

### Manual Payment Rule

Manual payments must pass through verification before being treated as confirmed, unless a tenant explicitly allows trusted finance officers to confirm immediately.

### Audit Requirements

Audit:

- Payment initiated
- Payment evidence submitted
- Payment recorded manually
- Payment matched to invoice
- Payment confirmation attempted
- Payment overpayment detected

---

## Module 7: Payment Verification

### Purpose

To allow authorized finance officers to verify or reject payments.

### Required Capabilities

Payment verifier must be able to:

- View pending payments
- Filter by date, payer, invoice, programme, level, session, channel, status
- View payment evidence
- Approve payment
- Reject payment
- Request correction
- Add verification note
- Attach verification reference
- Lock verified payment from normal editing

### Payment Verification Statuses

The system must support:

- Pending verification
- Verified
- Rejected
- Correction required
- Reversed
- Refunded, where applicable

### Business Rule

Only users with appropriate operational authority may verify payments.

Tenant admin status alone is not enough.

### Sensitive Rule

Verified payment records must not be edited directly.

Any correction must happen through reversal, adjustment, refund, or approved correction flow with audit trail.

### Audit Requirements

Audit:

- Payment verified
- Payment rejected
- Payment correction requested
- Payment reversed
- Verification note added
- Payment proof viewed by finance officer

---

## Module 8: Receipt Generation

### Purpose

To provide traceable proof of verified payment.

### Required Capabilities

The system must generate receipts for verified payments.

Receipts must include:

- Tenant name
- Tenant logo, where available
- Receipt number
- Invoice number
- Payer name
- Payer type
- Fee item(s)
- Amount paid
- Payment date
- Verification date
- Payment channel
- Verified by
- Balance, where applicable
- QR/reference code placeholder
- Print/download option

### Receipt Statuses

- Active
- Cancelled
- Reissued
- Reversed

### Business Rule

Receipts must not be generated for unverified payments unless the payment channel is automatically trusted by confirmed gateway callback.

### Audit Requirements

Audit:

- Receipt generated
- Receipt viewed
- Receipt downloaded
- Receipt reissued
- Receipt cancelled

---

## Module 9: Waiver, Discount, and Scholarship-Covered Payment Foundation

### Purpose

To allow approved financial exceptions without destroying payment records.

### Required Capabilities

The system must support:

- Full waiver
- Partial waiver
- Discount
- Scholarship-covered payment
- Management-approved exemption
- Reason for waiver
- Approval workflow
- Audit record

### Business Rule

Waivers must not be silently applied.

They must be:

- Requested
- Approved
- Applied
- Audited
- Visible in financial reports

### Required Approval

Waivers require operational authority and approval.

Default approval authority:

- Bursar or Tenant Super Admin, depending on tenant configuration

### Audit Requirements

Audit:

- Waiver requested
- Waiver approved
- Waiver rejected
- Waiver applied
- Waiver reversed

---

## Module 10: Debtor Management

### Purpose

To help tenants identify unpaid, partially paid, and overdue financial obligations.

### Required Capabilities

Authorized users must be able to:

- View debtor list
- Filter by programme
- Filter by level
- Filter by session
- Filter by fee type
- Filter by payment status
- Filter by overdue status
- Export debtor report
- View individual debtor profile
- Send notification where available

### Debtor Categories

The system must support:

- Unpaid
- Partially paid
- Overdue
- Payment pending verification
- Waiver pending approval
- Disputed payment
- Cleared

### Business Rule

Debtor status must be calculated from invoices, payments, waivers, and control rules.

It must not be manually typed as ordinary text.

### Audit Requirements

Audit export of debtor reports.

---

## Module 11: Payment Eligibility Service

### Purpose

To provide one central service that other blocks use to check whether a user is financially eligible for an action.

### Required Capabilities

The service must answer questions like:

- Can this applicant submit application?
- Can this applicant accept admission?
- Can this applicant proceed to clearance?
- Can this student register courses?
- Can this student sit examination?
- Can this student view result?
- Can this student request transcript?
- Can this student proceed to graduation clearance?

### Required Response

The service must return:

- Eligible: true/false
- Required fees
- Amount required
- Amount paid
- Balance
- Blocking reason
- Friendly user message
- Internal rule reference

### Business Rule

Later blocks must call this service instead of duplicating payment checks.

### Audit Requirements

Payment eligibility checks do not require audit for every ordinary read.

Audit is required when eligibility check is used to approve or block a critical action.

---

## Module 12: Payment Gateway Adapter Foundation

### Purpose

To prepare the platform for online payment without locking it to one provider.

### Required Capabilities

The platform must support a gateway adapter pattern.

Each gateway adapter should be responsible for:

- Initializing payment
- Generating payment reference
- Receiving callback/webhook
- Verifying transaction
- Returning normalized transaction response
- Logging gateway response

### Supported Gateway Statuses

The normalized gateway result must support:

- Pending
- Successful
- Failed
- Abandoned
- Reversed
- Duplicate reference
- Unknown

### Business Rule

No controller should contain direct gateway-specific logic.

Gateway-specific logic must live inside gateway adapter services.

### Nigeria-Aware Rule

The system must be ready for common Nigerian gateway patterns, including:

- Payment reference
- Transaction reference
- Bank transfer confirmation
- Webhook/callback verification
- Delayed settlement status
- Failed but debited complaints

### Audit Requirements

Audit:

- Gateway payment initialized
- Gateway callback received
- Gateway verification successful
- Gateway verification failed
- Duplicate callback detected
- Payment status changed by gateway

---

# 9. Database Ownership Rules for Block 4

## 9.1 Tenant-Owned Financial Tables

The following are tenant-owned:

- Fee categories
- Fee items
- Fee schedules
- Fee applicability rules
- Payment control rules
- Invoices
- Invoice items
- Payments
- Payment evidence files
- Payment verifications
- Receipts
- Waivers
- Refund/reversal records
- Debtor report snapshots, where stored

All must include `tenant_id`.

## 9.2 Platform-Owned Tables

The following are platform-owned:

- Payment gateway providers
- Platform gateway configuration templates
- Subscription billing records, if later implemented
- Platform transaction monitoring logs, if used

## 9.3 Shared Reference Data

The following may be shared templates:

- Default fee categories
- Default payment channels
- Default payment statuses
- Nigerian states and LGAs
- Default payer types

## 9.4 Strict Rule

Shared templates must never replace tenant-owned configuration.

A tenant must store its actual fee schedules, amounts, and payment rules under tenant context.

---

# 10. Required Services

Block 4 must include service-layer logic for:

1. FeeCategoryService  
2. FeeItemService  
3. FeeScheduleService  
4. FeeApplicabilityService  
5. PaymentControlRuleService  
6. InvoiceService  
7. InvoiceNumberService  
8. PaymentService  
9. PaymentVerificationService  
10. ReceiptService  
11. WaiverService  
12. DebtorService  
13. PaymentEligibilityService  
14. PaymentGatewayManagerService  
15. ManualPaymentService  
16. PaymentReportService  
17. FinanceDashboardService  
18. PaymentNotificationService  
19. FinancialAuditService  

Controllers must call services.

Controllers must not contain payment business logic.

---

# 11. Required Models

Block 4 should include tenant-scoped models for:

- FeeCategoryModel
- FeeItemModel
- FeeScheduleModel
- FeeApplicabilityRuleModel
- PaymentControlRuleModel
- InvoiceModel
- InvoiceItemModel
- PaymentModel
- PaymentEvidenceModel
- PaymentVerificationModel
- ReceiptModel
- WaiverModel
- PaymentReversalModel
- PaymentGatewayTransactionModel

All tenant-owned models must extend the tenant-scoped base model established in Block 1.

---

# 12. Required Controllers

## Tenant Admin / Finance Controllers

- FeeCategoryController
- FeeItemController
- FeeScheduleController
- PaymentControlRuleController
- InvoiceController
- PaymentVerificationController
- ReceiptController
- WaiverController
- DebtorController
- FinanceReportController
- FinanceDashboardController

## Applicant/Student-Facing Controllers

- MyInvoiceController
- MyPaymentController
- MyReceiptController

## API Controllers

- Api/FeeScheduleApiController
- Api/InvoiceApiController
- Api/PaymentApiController
- Api/PaymentVerificationApiController
- Api/DebtorApiController
- Api/PaymentEligibilityApiController

## Gateway Controllers

- PaymentGatewayCallbackController
- PaymentGatewayWebhookController

Gateway callback routes must be protected using gateway verification logic, not ordinary user session only.

---

# 13. Required Views and Pages

## Tenant Finance/Admin Pages

- Fee category list
- Fee category form
- Fee item list
- Fee item form
- Fee schedule list
- Fee schedule form
- Payment control rules page
- Invoice list
- Invoice details
- Manual payment recording page
- Pending verification list
- Payment verification details
- Receipt view
- Waiver request/approval page
- Debtor list
- Finance dashboard
- Payment reports page

## Applicant/Student Pages

- My invoices
- Invoice details
- Payment instruction page
- Upload payment evidence page
- Payment history
- Receipt view/download page

## Layout Usage

- Finance/admin pages extend Tenant Admin Layout.
- Applicant pages may use applicant portal layout from Block 3.
- Student payment pages later extend Student Layout.
- Platform payment gateway management pages, if any, extend Platform Admin Layout.

---

# 14. Vue Composition API Usage

Vue should be used for:

- Dynamic fee schedule forms
- Programme/level/department applicability selectors
- Invoice item builder
- Payment amount calculator
- Installment preview
- Payment verification filters
- Debtor list filters
- Fee schedule search
- Payment eligibility preview
- Receipt preview
- Dashboard widgets

Vue must not bypass server-side validation or authorization.

All Vue API calls must use the standard API response shape from the foundation document.

---

# 15. Full Page Reload vs API Interaction

## Full Page Reload Required For

- Final creation of critical fee schedule
- Final payment verification
- Payment reversal
- Waiver approval
- Gateway callback handling
- Receipt generation after verified payment
- Sensitive finance settings save

## API/Vue Interaction Allowed For

- Searching fee schedules
- Filtering payments
- Loading invoice items
- Previewing calculated totals
- Checking eligibility
- Loading debtor lists
- Previewing receipts
- Drafting fee schedules

---

# 16. API Response Standard

All Block 4 APIs must return the standard response format:

```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "errors": {},
  "meta": {}
}
```

Sensitive financial actions must include audit reference where applicable:

```json
{
  "success": true,
  "message": "Payment verified successfully.",
  "data": {
    "payment_status": "verified",
    "receipt_number": "RCT/2026/000001"
  },
  "errors": {},
  "meta": {
    "audit_reference": "AUD/2026/000045"
  }
}
```

---

# 17. Validation Rules

## Fee Category Validation

- Category name is required.
- Category name must be unique within tenant.
- Category status must be valid.

## Fee Item Validation

- Fee item name is required.
- Fee category is required.
- Fee item must belong to active tenant.
- Fee item must not duplicate within same category unless allowed.

## Fee Schedule Validation

- Fee item is required.
- Academic session is required where applicable.
- Amount is required.
- Amount must be non-negative.
- Applicable programme/level/department must belong to same tenant.
- Due date must be valid.
- Installment rule must be valid.
- Minimum payment cannot exceed total amount.

## Invoice Validation

- Payer type is required.
- Payer ID is required where applicable.
- Invoice items are required.
- Invoice amount must match invoice items.
- Invoice must belong to tenant.
- Duplicate invoice must be prevented where policy disallows it.

## Payment Validation

- Invoice is required.
- Payment amount is required.
- Payment amount must be greater than zero.
- Payment channel is required.
- Payment reference is required where applicable.
- Payment evidence is required for manual channel where tenant policy demands it.
- Payment must not exceed allowed amount unless overpayment is allowed.

## Verification Validation

- Payment must exist.
- Payment must belong to tenant.
- Payment must be pending verification.
- User must have payment verification authority.
- Verification decision is required.
- Rejection must include reason.

## Waiver Validation

- Invoice or fee item is required.
- Waiver amount is required.
- Waiver reason is required.
- Waiver amount must not exceed balance.
- User must have waiver authority.
- Waiver approval must be audited.

---

# 18. Required Audit Events

Block 4 must audit:

- Fee category created
- Fee category updated
- Fee item created
- Fee item updated
- Fee schedule created
- Fee schedule updated
- Fee amount changed
- Fee deadline changed
- Payment control rule created
- Payment control rule updated
- Invoice generated
- Invoice cancelled
- Payment initiated
- Manual payment recorded
- Payment evidence uploaded
- Payment evidence viewed
- Payment verified
- Payment rejected
- Payment reversed
- Payment refunded, where implemented
- Receipt generated
- Receipt viewed
- Receipt downloaded
- Waiver requested
- Waiver approved
- Waiver rejected
- Debtor report exported
- Gateway callback received
- Gateway verification failed
- Gateway verification successful

Financial audit entries must include:

- Tenant ID
- User ID
- User group
- Operational authority
- Action
- Module
- Entity type
- Entity ID
- Old value where relevant
- New value where relevant
- IP address
- User agent
- Timestamp
- Status
- Human-readable summary

---

# 19. Reports Required in Block 4

## Finance Dashboard Summary

- Total expected revenue
- Total paid
- Total outstanding
- Pending verification
- Total waived
- Total reversed
- Payment by channel
- Recent payments

## Fee Reports

- Fee schedule report
- Fee item report
- Programme fee report
- Level fee report
- Session fee report

## Payment Reports

- Payment history report
- Verified payment report
- Pending payment report
- Rejected payment report
- Manual payment report
- Online payment report
- Payment by date range
- Payment by programme
- Payment by level
- Payment by fee item

## Debtor Reports

- Debtor list
- Programme debtor report
- Level debtor report
- Overdue payment report
- Partial payment report

## Waiver Reports

- Waiver request report
- Approved waiver report
- Rejected waiver report
- Waiver by programme/session

## Export Rule

Every exported report must include:

- Tenant name
- Report title
- Date generated
- Generated by
- Filters applied
- Academic session where applicable
- Programme/level where applicable

---

# 20. Notifications Required in Block 4

The notification foundation from the general architecture must be used.

## Notification Events

The system should prepare notifications for:

- Invoice generated
- Payment submitted
- Payment verified
- Payment rejected
- Receipt generated
- Payment deadline approaching
- Payment overdue
- Waiver approved
- Waiver rejected

## Channels

Initial implementation may use:

- In-app notification
- Email notification placeholder
- SMS notification placeholder

SMS is important in the Nigerian context but may be activated through provider integration later.

---

# 21. Dashboard Requirements

## Tenant Finance Dashboard

Must show:

- Expected revenue for current session
- Total verified payments
- Pending verification count
- Debtor count
- Recent payments
- Revenue by fee category
- Revenue by channel
- Quick link to pending verification
- Quick link to debtor list

## Tenant Admin Dashboard Widget

Block 4 must expose widgets for the Tenant Admin Dashboard:

- Payment summary
- Pending verification
- Debtor alert
- Current session revenue summary

## Applicant/Student Dashboard Widget

Where applicable:

- Outstanding invoice
- Payment status
- Receipt access
- Blocked action reason where payment is required

---

# 22. Access Control Matrix

## Tenant Super Admin

Can:

- View finance dashboard
- View fee settings
- Create fee categories/items/schedules if authority permits
- Assign financial operational authorities
- View financial reports
- Approve waivers if configured

Cannot:

- Bypass audit trail
- Access another tenant’s financial records
- Edit verified payments directly

## Tenant Admin with Finance Authority

Can:

- Manage assigned fee settings
- View invoices
- View payments
- Generate reports within permitted scope

Cannot:

- Verify payment without Payment Verifier authority
- Approve waiver without waiver authority
- Access records outside tenant

## Bursar

Can:

- Manage fee schedules
- View revenue reports
- View debtor list
- Approve financial operations within tenant policy
- Verify or supervise payment verification where configured

## Accountant

Can:

- View and manage payment records
- Verify payments if granted Payment Verifier authority
- Generate financial reports

## Payment Verifier

Can:

- View pending payment evidence
- Verify payment
- Reject payment
- Request correction

Cannot:

- Change fee schedules unless also granted fee configuration authority
- Approve waivers unless granted waiver authority

## Applicant

Can:

- View own invoices
- Submit payment evidence
- View own payment status
- View own receipts

Cannot:

- View other applicants’ invoices or payments

## Student

Can:

- View own invoices
- View own payment history
- View own receipts
- See financial eligibility status

Cannot:

- View other students’ financial records

## Platform Admin

Can:

- Configure platform-level gateway provider settings
- View platform-level transaction monitoring where implemented
- Access tenant financial data only through controlled support access

Cannot:

- Modify tenant payment records without audited support process

---

# 23. Implementation Order for Block 4

Developers must implement Block 4 in this order:

## Step 1: Financial Data Foundation

- Create fee category structure
- Create fee item structure
- Create fee schedule structure
- Create invoice and invoice item structures
- Create payment structure
- Create receipt structure
- Create waiver/reversal foundation
- Ensure all tenant-owned tables include tenant isolation fields

## Step 2: Fee Configuration

- Fee category CRUD
- Fee item CRUD
- Fee schedule CRUD
- Applicability rules
- Installment and minimum payment settings

## Step 3: Payment Control Rules

- Define payment control points
- Create eligibility rules
- Build PaymentEligibilityService
- Expose eligibility check API for later blocks

## Step 4: Invoice Engine

- Invoice number generation
- Invoice creation
- Invoice item calculation
- Duplicate invoice prevention
- Invoice status calculation
- Applicant/student invoice view

## Step 5: Manual Payment Flow

- Manual payment recording
- Payment evidence upload
- Pending verification queue
- Payment verification/rejection
- Receipt generation after verification

## Step 6: Online Payment Adapter Foundation

- Payment gateway manager
- Gateway transaction records
- Payment initialization endpoint
- Callback/webhook handler
- Transaction verification service
- Normalized gateway response

## Step 7: Debtor and Balance Engine

- Calculate outstanding balance
- Debtor list
- Partial payment tracking
- Overdue tracking
- Filter and export debtor report

## Step 8: Waiver and Exception Handling

- Waiver request
- Waiver approval
- Waiver application to invoice
- Waiver reporting
- Audit enforcement

## Step 9: Reports and Dashboards

- Finance dashboard
- Payment reports
- Fee reports
- Debtor reports
- Waiver reports

## Step 10: Stabilization

- Tenant isolation tests
- Payment calculation tests
- Partial payment tests
- Manual verification tests
- Receipt generation tests
- Audit tests
- Mobile UI tests
- Authorization tests

---

# 24. Testing Checklist

## Tenant Isolation Tests

- Tenant A cannot see Tenant B fee schedules.
- Tenant A cannot see Tenant B invoices.
- Tenant A cannot see Tenant B payments.
- Tenant A cannot verify Tenant B payments.
- Tenant A cannot access Tenant B receipts by changing URL IDs.
- Debtor report must show only active tenant data.

## Fee Configuration Tests

- Tenant can create fee category.
- Tenant can create fee item.
- Tenant can create fee schedule.
- Fee schedule respects programme/level/session applicability.
- Duplicate conflicting fee schedules are blocked.
- Disabled fee item cannot generate invoice unless policy allows historical display only.

## Invoice Tests

- Invoice total equals invoice item total.
- Invoice number is unique within tenant.
- Duplicate invoice is prevented where policy disallows it.
- Invoice status changes correctly after partial payment.
- Invoice status changes correctly after full payment.
- Cancelled invoice cannot accept payment unless restored.

## Payment Tests

- Manual payment can be submitted.
- Manual payment requires evidence when tenant setting requires it.
- Manual payment remains pending until verified.
- Verified payment updates invoice balance.
- Partial payment updates balance correctly.
- Overpayment is blocked unless tenant setting allows it.
- Rejected payment does not reduce balance.

## Receipt Tests

- Receipt is generated only after verified payment.
- Receipt number is unique within tenant.
- Receipt shows correct invoice and payment information.
- Cancelled/reversed payment affects receipt status.

## Waiver Tests

- Waiver requires approval.
- Waiver amount cannot exceed balance.
- Approved waiver reduces outstanding balance.
- Rejected waiver does not affect balance.
- Waiver is visible in reports.

## Payment Eligibility Tests

- Applicant cannot submit application if required application fee is unpaid.
- Applicant can proceed when application fee is verified.
- Student cannot register courses if payment rule blocks registration.
- Eligibility response returns clear blocking reason.
- Later modules can consume eligibility response.

## Authorization Tests

- Tenant admin without finance authority cannot verify payment.
- Payment verifier cannot change fee schedule.
- Applicant sees only own invoices.
- Student sees only own receipts.
- Platform admin access is audited.

## Audit Tests

- Fee amount change is audited.
- Payment verification is audited.
- Payment rejection is audited.
- Receipt generation is audited.
- Waiver approval is audited.
- Debtor export is audited.

## Mobile Tests

- Invoice list is usable on mobile.
- Payment form is mobile-friendly.
- Payment verification page is usable on tablet/mobile.
- Debtor list uses filters/cards on small screens.
- Receipt is readable on mobile.

---

# 25. Acceptance Criteria

Block 4 is complete only when:

1. Tenants can configure fee categories and fee items.

2. Tenants can configure session/programme/level-based fee schedules.

3. Tenants can configure payment control rules.

4. The system can generate invoices for applicants and students.

5. The system can record manual payments.

6. Manual payments can be verified or rejected by authorized users only.

7. Verified payments update invoice balance correctly.

8. Receipts are generated for verified payments.

9. Partial payment is supported.

10. Waiver foundation is implemented with approval and audit trail.

11. Debtor lists are generated from real invoice/payment data.

12. Payment eligibility service is available for other blocks.

13. Payment reports are available.

14. Finance dashboard is available.

15. All sensitive payment actions are audited.

16. Tenant isolation is fully enforced.

17. Mobile-first UI requirements are satisfied.

18. No fee name, amount, school-specific payment rule, or receipt label is hard-coded.
