# SaaS Blueprint for Multi-Tenant SHST Information Management System

## Document Purpose

This document provides a structured product blueprint for building a web-based, multi-tenant SaaS Information Management System for Schools of Health Sciences and Technology (SHST) in Nigeria.

It is written as a planning reference for project supervisors, product owners, business analysts, system architects, and development teams who will participate in the phased implementation of the platform.

The document focuses on:

- Product vision
- SaaS operating model
- General SHST operational structure
- Recommended modules
- Phased implementation blocks
- Configurable school settings
- Standard platform workflows
- Development priority guidance

This is not a coding document, database design, UI/UX design, or technical architecture specification. It is a business and operational blueprint for planning implementation.

---

# 1. Project Vision

The goal is to build a web-based, multi-tenant SaaS Information Management System for Schools of Health Sciences and Technology in Nigeria.

The platform will allow any SHST institution to subscribe, configure its school profile, define its academic and administrative settings, and start using the system without requiring custom development for each school.

Each institution will experience the system as its own independent portal, while the core platform remains centrally managed, reusable, scalable, and flexible.

---

# 2. Core Product Philosophy

The system must not be built for one school only.

It must be built around the common operational structure of Nigerian Schools of Health Sciences and Technology, while allowing each school to configure its own specific details.

The guiding principle is:

> Build one flexible platform, not many separate school systems.

Therefore, the system should avoid hard-coding:

- School names
- Department names
- Programme names
- Course names
- Fee amounts
- Grading scales
- Admission requirements
- Academic sessions
- Academic levels
- Approval workflows
- Public website content
- Result rules
- Payment rules

Instead, each school should be able to configure these from its own administrative area.

---

# 3. SaaS Operating Model

## 3.1 Platform Owner

The platform owner controls the central SaaS platform.

### Responsibilities

The platform owner should be able to:

- Create and manage school tenants
- Manage subscriptions
- Manage platform-wide settings
- Monitor usage
- Provide support
- Maintain security
- Maintain system availability
- Deploy updates to the core system
- Control global product settings

---

## 3.2 Tenant Institution

Each school is treated as a tenant.

Each tenant institution should manage its own:

- School profile
- Departments
- Programmes
- Courses
- Students
- Staff
- Applicants
- Fees
- Results
- Website content
- Academic calendar
- Institutional users
- Role permissions

---

## 3.3 Tenant Users

Typical users inside each school include:

- School administrator
- Registrar
- Admission officer
- Bursar
- Accountant
- HOD
- Lecturer
- Course adviser
- Examination officer
- Student affairs officer
- ICT officer
- Applicant
- Student
- Public website visitor

---

# 4. Product Development Strategy

The platform should be developed in phases.

Each phase must be stable before the next phase expands the system.

The recommended development order is:

1. Build the SaaS foundation.
2. Build institutional self-configuration.
3. Build public website and admission.
4. Build payment and student records.
5. Build staff and academic structure.
6. Build course allocation and course registration.
7. Build result processing carefully.
8. Expand to clearance, transcripts, professional council reports, and advanced services.

---

# 5. Phase 0: SaaS Foundation and General SHST Operating Framework

## 5.1 Objective

To define the general SHST operating structure that the platform will support by default.

This phase is not about collecting custom documents from one school. It is about building a product that understands how Nigerian SHST institutions generally operate.

---

## 5.2 What This Phase Entails

The platform should be designed around standard SHST realities such as:

- Multiple schools using one platform
- Each school having its own portal
- Each school having departments
- Each department having programmes
- Each programme having levels or years
- Each level having courses
- Students applying into programmes
- Students paying fees
- Students registering courses
- Staff being assigned courses
- Lecturers submitting results
- Results passing through approval stages
- Public website being managed per school

---

## 5.3 General SHST Structures to Support

The system should support common programme structures such as:

- Certificate programmes
- Diploma programmes
- National Diploma programmes
- Higher National Diploma programmes
- Professional health programmes
- Technician programmes
- Community health programmes
- Environmental health programmes
- Health information management programmes
- Pharmacy technician programmes
- Dental health programmes
- Public health programmes
- Nutrition and dietetics programmes
- Medical laboratory-related programmes, where applicable

The system should not force one naming pattern. Some schools may use:

- Faculty
- School
- Directorate
- Department
- Programme
- Unit

Therefore, the platform should be flexible enough to support different institutional naming styles.

---

## 5.4 Output of Phase 0

At the end of this phase, the product direction should be clear:

- The system is SaaS-based.
- Each school is a tenant.
- Each tenant configures its own setup.
- The platform supports general SHST operations.
- No important school-specific structure is hard-coded for one institution.

---

# 6. Phase 1: Tenant Onboarding and Institutional Configuration

## 6.1 Objective

To allow a new school to join the platform and configure its own operating structure.

This is the real foundation of the SaaS platform.

---

## 6.2 Module 1: Tenant/School Account Setup

### Purpose

To create a separate operating environment for each school using the SaaS platform.

### What It Entails

When a school joins the platform, the platform owner should be able to create or approve that school as a tenant.

The school should then configure:

- School name
- School short name
- Logo
- Address
- Contact phone numbers
- Email address
- Website or subdomain identity
- Ownership type
- State and location
- School motto
- Management details
- Academic system preference

### Why It Matters

This allows the same platform to serve many schools while each school appears independent.

Example:

- School A sees its own logo, name, students, staff, fees, results, and website.
- School B sees only its own logo, name, students, staff, fees, results, and website.
- The platform owner manages both from the central SaaS level.

---

## 6.3 Module 2: Institutional Structure Configuration

### Purpose

To allow each school to define its academic and administrative structure.

### What It Entails

Each school should configure:

- Faculties, schools, or directorates, where applicable
- Departments
- Programmes
- Programme duration
- Levels or years
- Semesters
- Academic sessions
- Course categories
- Course lists
- Programme-course mapping

### Examples

A school may configure:

- Department of Community Health
  - CHEW
  - JCHEW

Another school may configure:

- Department of Health Information Management
  - ND Health Information Management
  - HND Health Information Management

Another school may configure:

- Department of Environmental Health
  - Environmental Health Technician
  - Environmental Health Assistant

### Why It Matters

This module makes the system flexible.

Instead of building separate versions for each institution, each school defines its own structure inside the platform.

---

## 6.4 Module 3: Academic Rules Configuration

### Purpose

To allow each school to define academic rules that control how the system behaves.

### What It Entails

Each school should configure:

- Academic session format
- Semester structure
- Level or year naming
- Programme duration
- Course unit or credit system
- Compulsory courses
- Elective courses
- Carryover rules
- Registration rules
- Minimum course load
- Maximum course load
- Grading scales
- Pass mark
- CA/exam ratio
- Result approval stages
- Repeat rules
- Withdrawal rules
- Graduation requirements

### Why It Matters

Not all SHST institutions operate exactly the same way.

One school may use a 40% pass mark. Another may use a 50% pass mark for some professional courses.

One programme may use 30% CA and 70% examination. Another programme may use 40% CA and 60% examination.

The platform should support these differences through configuration, not custom coding.

---

## 6.5 Module 4: Role and Permission Configuration

### Purpose

To allow each school to control who can access different parts of the system and what each user can do.

### What It Entails

Each school should configure users and assign responsibilities.

Common roles include:

- Super admin
- School administrator
- Registrar
- Admission officer
- Bursar
- Accountant
- HOD
- Lecturer
- Course adviser
- Examination officer
- Student affairs officer
- ICT officer
- Student
- Applicant

### Why It Matters

A SaaS system must allow each school to control responsibility and authority.

For example:

- Admission officer manages applicants.
- Bursar manages payments.
- HOD reviews departmental academic activities.
- Lecturer enters scores.
- Exam officer reviews result sheets.
- Registrar manages student records.
- Student views personal information, payments, courses, and results.

---

# 7. Phase 2: Public Website and Institutional Showcase

## 7.1 Objective

To allow each school to manage its own public-facing website from the platform.

The public website should help schools showcase themselves, publish admission information, and communicate with applicants, students, parents, and the public.

---

## 7.2 Module 5: Public Website Management

### Purpose

To provide each tenant school with an official online presence.

### What It Entails

Each school should be able to manage:

- Homepage content
- About the school
- Mission and vision
- Management profile
- Departments
- Programmes
- Admission requirements
- News and announcements
- Academic calendar notices
- Contact details
- Gallery
- Portal access links

### Why It Matters

Many SHST institutions need online visibility.

The website also supports admission because applicants can read programme requirements, application instructions, fees, deadlines, and official announcements.

### SaaS Flexibility

Each school should have its own public identity.

Possible examples:

- `schoolA.platform.com`
- `schoolB.platform.com`

Or later:

- `portal.schoolA.edu.ng`
- `portal.schoolB.edu.ng`

The key point is that each school manages its own public content without affecting other schools.

---

# 8. Phase 3: Application and Admission System

## 8.1 Objective

To allow each school to receive applications, screen applicants, offer admission, and convert admitted applicants into students.

---

## 8.2 Module 6: Student Application System

### Purpose

To digitize the applicant-facing application process.

### What It Entails

Applicants should be able to:

- Create application account
- Select school
- Select programme
- Fill personal information
- Enter O’Level results
- Upload passport
- Submit application
- Track application status
- Receive admission offer
- Accept admission

### Configurable Admission Settings

Each school should configure:

- Available programmes for application
- Application opening date
- Application closing date
- Application fee
- Required subjects
- Number of O’Level sittings allowed
- Screening method
- Admission quota
- Acceptance fee
- Admission letter template

### Why It Matters

This module gives immediate value because admission is one of the most common manual bottlenecks in these institutions.

---

## 8.3 Module 7: Admission Management

### Purpose

To enable school officers to manage applications and admission decisions.

### What It Entails

School officers should be able to:

- View applicants
- Filter applicants by programme
- Check application status
- Mark screening status
- Shortlist applicants
- Offer admission
- Reject application
- Publish admission list
- Track acceptance fee
- Mark clearance status
- Convert applicant to student

### Key Outputs

The system should produce:

- Applicant list
- Screening list
- Admission list
- Acceptance list
- Cleared student list
- Admission statistics

### Why It Matters

Admission must connect smoothly to student records.

Once an applicant is admitted, accepted, and cleared, the applicant becomes a student in the system.

---

# 9. Phase 4: Fees and Levy Payment System

## 9.1 Objective

To help each school manage fees, levies, payment status, and financial eligibility rules.

---

## 9.2 Module 8: Fee Configuration

### Purpose

To allow each school to define its own fee structure.

### What It Entails

Each school should configure its own fees.

Fee types may include:

- Application fee
- Acceptance fee
- School fees
- Departmental levy
- Examination fee
- Practical/lab fee
- Indexing fee
- Hostel fee
- Graduation fee
- Transcript fee
- Certificate fee
- Other custom levies

### Configurable Fee Rules

Each school should define:

- Fee name
- Fee amount
- Fee category
- Applicable programme
- Applicable level
- Applicable session
- Whether payment is compulsory
- Whether installment is allowed
- Minimum payment required
- Payment deadline
- Penalty rules, where applicable

### Why It Matters

Different schools charge different fees.

Even within the same school, fees may vary by:

- Programme
- Level
- New student or returning student
- Indigenous or non-indigenous status
- Hostel status
- Professional indexing requirement

The system must not hard-code fee structures.

---

## 9.3 Module 9: Student Payment Management

### Purpose

To track student payments, debt status, and financial clearance.

### What It Entails

The system should manage:

- Payment invoice
- Payment confirmation
- Payment history
- Outstanding balance
- Debtor list
- Fee clearance
- Payment reports
- Revenue summary

### Payment Control Areas

The school should decide whether payment affects:

- Application submission
- Admission acceptance
- Course registration
- Examination eligibility
- Result viewing
- Graduation clearance
- Transcript request

### Why It Matters

Payment control is one of the strongest reasons schools will adopt the system.

It reduces:

- Fake payment claims
- Missing receipts
- Manual debtor lists
- Revenue leakage
- Bursary confusion
- Unauthorized registration

---

# 10. Phase 5: Student Data Management

## 10.1 Objective

To create a central student record for each school.

This becomes the official student profile used across admission, fees, course registration, results, clearance, and graduation.

---

## 10.2 Module 10: Student Profile Management

### Purpose

To maintain a single reliable student record.

### What It Entails

The system should maintain:

- Biodata
- Contact information
- Next of kin
- Guardian/sponsor details
- Admission details
- Department
- Programme
- Level
- Academic session admitted
- Student status
- O’Level records
- Passport
- Payment history
- Course registration history
- Result history
- Clearance status

### Student Statuses to Support

The system should support statuses such as:

- Applicant
- Offered admission
- Accepted
- Cleared
- Active
- Deferred
- Suspended
- Withdrawn
- Graduated
- Cleared graduate

### Why It Matters

Manual records are usually scattered across many offices.

This module makes the student record centralized, consistent, and available to authorized officers.

---

# 11. Phase 6: Staff Data Management

## 11.1 Objective

To manage staff records and support academic responsibilities such as course allocation, approvals, and result submission.

---

## 11.2 Module 11: Staff Profile Management

### Purpose

To maintain accurate staff records for administrative and academic use.

### What It Entails

The system should maintain:

- Staff biodata
- Contact details
- Employment type
- Staff category
- Department
- Rank/designation
- Qualification
- Professional qualification
- Area of specialization
- Administrative role
- Academic responsibility

### Staff Categories to Support

The system should support:

- Academic staff
- Non-academic staff
- Registry staff
- Bursary staff
- Admission staff
- Examination staff
- Student affairs staff
- ICT staff
- Departmental staff
- Management staff
- Part-time lecturers
- External examiners, where applicable

### Why It Matters

Staff records are not only for HR purposes.

They also determine:

- Who teaches courses
- Who advises students
- Who reviews course registration
- Who enters scores
- Who approves results
- Who manages departments
- Who handles payments
- Who controls admissions

---

# 12. Phase 7: Staff Course Allocation System

## 12.1 Objective

To allow departments to assign courses to lecturers for each academic session and semester.

---

## 12.2 Module 12: Course Allocation

### Purpose

To formally assign teaching and assessment responsibility.

### What It Entails

The system should support:

- Course selection by department
- Lecturer assignment
- Multiple lecturers for one course
- Practical/lab supervisor assignment
- Course adviser assignment
- Teaching workload report
- HOD review
- Allocation approval

### Why It Matters

Before results can be submitted, the system must know who is responsible for teaching and assessing each course.

This improves accountability and prevents unauthorized score submission.

### Configurable Rules

Each school should define:

- Who can allocate courses
- Whether HOD approval is required
- Whether lecturers can teach across departments
- Whether team teaching is allowed
- Whether part-time lecturers can be assigned courses

---

# 13. Phase 8: Student Course Registration System

## 13.1 Objective

To allow students to register approved courses based on programme, level, semester, payment status, and academic standing.

---

## 13.2 Module 13: Course Registration

### Purpose

To control the courses each student is officially taking.

### What It Entails

The system should support:

- Student course registration
- Compulsory courses
- Elective courses
- Carryover courses
- Course adviser review
- HOD approval
- Registration deadline
- Late registration
- Add/drop courses
- Course registration form
- Class list generation

### Configurable Rules

Each school should define:

- Whether registration is per semester or session
- Whether payment is required before registration
- Minimum payment required
- Whether students with debt can register
- Maximum course load
- Minimum course load
- Carryover registration rules
- Add/drop deadline
- Approval workflow

### Why It Matters

Course registration connects students to teaching, examination, results, and academic progression.

Without proper course registration, result processing becomes unreliable.

---

# 14. Phase 9: Result Compilation and Computation System

## 14.1 Objective

To digitize student result processing in a controlled, reliable, and auditable way.

This phase should be handled carefully because result processing is the most sensitive academic module.

---

## 14.2 Module 14: Result Setup and Rules

### Purpose

To allow each school to configure its result processing rules.

### What It Entails

Each school should configure:

- Grading scale
- Pass mark
- CA/exam ratio
- Grade points
- Remark rules
- Carryover rules
- Repeat rules
- Withdrawal rules
- Special exam rules
- Resit rules
- Result approval workflow

### Why It Matters

Result rules differ across programmes and institutions.

The platform must allow schools to configure rules instead of forcing one grading model.

---

## 14.3 Module 15: Result Entry and Computation

### Purpose

To support controlled entry and computation of student scores.

### What It Entails

The system should support:

- CA score entry
- Exam score entry
- Total score computation
- Grade computation
- Grade point computation
- Failed course detection
- Carryover detection
- Missing score tracking
- Result sheet generation

### Users Involved

- Lecturer enters scores.
- HOD reviews departmental results.
- Exams officer reviews completeness.
- Academic authority approves results.
- Student views result after publication.

---

## 14.4 Module 16: Result Approval and Publication

### Purpose

To protect result integrity through review, approval, publication control, and audit trail.

### What It Entails

The system should support:

- Lecturer submission
- HOD review
- Departmental approval
- Exams office review
- Academic Board approval
- Result publication
- Result withholding
- Result correction request
- Result audit trail

### Why It Matters

Result publication must not be casual.

The system should protect the school from:

- Unauthorized score changes
- Missing scores
- Wrong calculations
- Premature result publication
- Result disputes
- Lack of approval evidence

---

# 15. Recommended SaaS Phase One Scope

## 15.1 Phase One Core Goal

To launch a working SaaS platform that allows any SHST institution to onboard, configure its academic structure, manage its public website, process admissions, collect and manage payments, maintain student and staff records, allocate courses, register courses, and begin controlled result processing.

---

## 15.2 Phase One Modules

The recommended Phase One modules are:

1. SaaS tenant/school setup
2. Institutional structure configuration
3. Academic rules configuration
4. Role and permission configuration
5. Public website management
6. Student application system
7. Admission management
8. Fee configuration
9. Student payment management
10. Student profile management
11. Staff profile management
12. Staff course allocation
13. Student course registration
14. Result setup and rules
15. Result entry and computation
16. Result approval and publication

---

# 16. Better Internal Grouping for Development

Although the blueprint contains 16 modules, they should be grouped into practical development blocks.

---

## 16.1 Block 1: SaaS and School Configuration

### Includes

- Tenant/school setup
- Institutional structure
- Academic sessions
- Departments
- Programmes
- Levels
- Semesters
- Courses
- Roles and permissions

### Purpose

To allow any school to configure itself and prepare the system for use.

---

## 16.2 Block 2: Public Website

### Includes

- School profile
- Programme pages
- News
- Announcements
- Admission information
- Contact details

### Purpose

To give each school public visibility and support admission communication.

---

## 16.3 Block 3: Admission

### Includes

- Applicant registration
- Programme application
- Application fee
- Screening
- Admission offer
- Acceptance
- Clearance
- Student creation

### Purpose

To digitize the applicant-to-student journey.

---

## 16.4 Block 4: Payment

### Includes

- Fee setup
- Invoices
- Payment status
- Debtor list
- Clearance
- Payment reports

### Purpose

To control revenue and connect payment to school operations.

---

## 16.5 Block 5: Records

### Includes

- Student records
- Staff records
- Student status
- Staff responsibilities
- Departmental lists

### Purpose

To make the system the official record center for students and staff.

---

## 16.6 Block 6: Academic Operations

### Includes

- Course allocation
- Course registration
- Class lists
- Registration approval
- Carryover registration

### Purpose

To control teaching responsibility and student academic participation.

---

## 16.7 Block 7: Results

### Includes

- Result rules
- Score entry
- Computation
- Approval
- Publication
- Correction control

### Purpose

To safely digitize result processing after academic data is clean.

---

# 17. Product Configuration Principle

The platform should come with default SHST templates, but every school should be able to edit them.

## 17.1 Example Default Setup Options

The system may come with default options such as:

- First Semester
- Second Semester
- Year 1
- Year 2
- Year 3
- ND I
- ND II
- HND I
- HND II
- Compulsory Course
- Elective Course
- Carryover Course
- Application Fee
- Acceptance Fee
- School Fee
- Departmental Levy
- Examination Fee
- Practical Fee
- Indexing Fee

Each school should be able to:

- Rename them
- Add new ones
- Disable unused ones
- Modify applicable rules

This is what makes the platform SaaS-ready.

---

# 18. What Should Be Configurable Per School

## 18.1 Institution Settings

Each school should configure:

- School name
- Logo
- Address
- Contact details
- Motto
- Public website content
- Academic calendar
- Management profile

---

## 18.2 Academic Settings

Each school should configure:

- Departments
- Programmes
- Levels
- Semesters
- Sessions
- Courses
- Course units
- Programme duration
- Course categories

---

## 18.3 Admission Settings

Each school should configure:

- Application periods
- Application fees
- Available programmes
- Entry requirements
- Screening rules
- Admission quota
- Acceptance rules
- Clearance requirements

---

## 18.4 Fee Settings

Each school should configure:

- Fee types
- Fee amounts
- Fee deadlines
- Installment rules
- Programme-based fees
- Level-based fees
- Payment restrictions

---

## 18.5 Staff Settings

Each school should configure:

- Staff categories
- Staff departments
- Staff roles
- Academic responsibilities
- Approval authority

---

## 18.6 Course Registration Settings

Each school should configure:

- Registration period
- Required payment level
- Course load rules
- Carryover rules
- Add/drop rules
- Approval workflow

---

## 18.7 Result Settings

Each school should configure:

- CA/exam ratio
- Pass mark
- Grading scale
- Grade points
- Result approval stages
- Result publication rules
- Result correction rules

---

# 19. What Should Be Standard Across the Platform

Even though schools can configure their own data, some operational concepts should remain standard across the SaaS platform.

These include:

- Tenant separation
- Applicant lifecycle
- Student lifecycle
- Staff lifecycle
- Admission workflow
- Payment tracking
- Course allocation workflow
- Course registration workflow
- Result approval workflow
- User roles and permissions
- Audit trail
- Reports
- Status tracking

The names and details may differ per school, but the underlying operational pattern should remain consistent.

---

# 20. Standard Student Lifecycle

The platform should support this general student journey:

1. Applicant creates account.
2. Applicant applies for a programme.
3. Applicant pays application fee.
4. School screens application.
5. School offers admission.
6. Applicant accepts admission.
7. Applicant pays acceptance fee.
8. School clears applicant.
9. Applicant becomes student.
10. Student pays school fees.
11. Student registers courses.
12. Student attends lectures and examinations.
13. Lecturer submits scores.
14. Result is approved.
15. Result is published.
16. Student progresses, repeats, carries over, withdraws, or graduates.

This lifecycle should be standard across the platform.

---

# 21. Standard Staff Lifecycle

The platform should support this general staff journey:

1. Staff profile is created.
2. Staff is assigned to a department.
3. Staff is assigned a role.
4. Academic staff is assigned courses.
5. Staff performs assigned duties.
6. Staff submits or approves academic records depending on role.
7. Staff status is updated when transferred, suspended, resigned, retired, or inactive.

---

# 22. Standard Admission Lifecycle

The platform should support this general admission journey:

1. Admission session is opened.
2. Programmes are made available.
3. Application fee is set.
4. Applicants submit forms.
5. Applications are reviewed.
6. Screening is conducted.
7. Admission list is generated.
8. Applicants accept admission.
9. Acceptance fee is confirmed.
10. Clearance is completed.
11. Student records are created.

---

# 23. Standard Result Lifecycle

The platform should support this general result journey:

1. Result rules are configured.
2. Courses are allocated to lecturers.
3. Students register courses.
4. Lecturer enters CA and exam scores.
5. System computes totals and grades.
6. Lecturer submits result.
7. HOD reviews result.
8. Exams officer reviews result.
9. Academic authority approves result.
10. Result is published.
11. Student views result.
12. Corrections follow controlled approval.

---

# 24. Development Priority Recommendation

## 24.1 First Build

Start with the parts that make the SaaS platform usable by any school:

1. Tenant setup
2. School profile
3. Departments
4. Programmes
5. Sessions
6. Semesters
7. Levels
8. Courses
9. Roles
10. Public website
11. Admission
12. Fees
13. Students
14. Staff

---

## 24.2 Then Build

After the above is stable, proceed to:

15. Course allocation
16. Course registration
17. Result rules
18. Result computation
19. Result approval
20. Result publication

This keeps the system useful early while protecting the sensitive result process from being rushed.

---

# 25. Key Implementation Rules for the Project Team

The project team should follow these rules throughout implementation:

1. Do not hard-code school-specific data.
2. Every school-specific setting must belong to the tenant.
3. Use general SHST workflows as defaults.
4. Allow each school to configure its own variations.
5. Keep result processing controlled and auditable.
6. Ensure payment rules can affect academic access where required.
7. Ensure roles and permissions are configurable.
8. Protect tenant data separation.
9. Provide reports for every major operational area.
10. Build each phase to stability before expanding.

---

# 26. Final Blueprint Summary

The correct product direction is:

> A configurable SaaS platform for Nigerian Schools of Health Sciences and Technology, built around common SHST operations, where each institution can onboard, configure its own academic structure, manage admissions, payments, students, staff, courses, results, and public website without custom development.

The system should provide standard SHST workflows, but every school should be able to configure its own:

- Departments
- Programmes
- Courses
- Fees
- Admission rules
- Academic levels
- Grading scales
- Approval workflows
- Website content
- Staff roles
- Result rules

This direction allows the project to produce one scalable product instead of many custom school systems.

---

# 27. Supervisor Planning Checklist

A project supervisor can use this checklist to guide planning.

## 27.1 Product Scope Checklist

- [ ] Is the system planned as SaaS, not a single-school application?
- [ ] Can multiple schools use the system independently?
- [ ] Can each school configure its own identity?
- [ ] Can each school configure departments?
- [ ] Can each school configure programmes?
- [ ] Can each school configure sessions, semesters, and levels?
- [ ] Can each school configure courses?
- [ ] Can each school configure fees?
- [ ] Can each school configure admission settings?
- [ ] Can each school configure grading rules?
- [ ] Can each school configure roles and permissions?

---

## 27.2 Phase Readiness Checklist

- [ ] Tenant setup is defined.
- [ ] School configuration is defined.
- [ ] Public website module is defined.
- [ ] Admission workflow is defined.
- [ ] Payment workflow is defined.
- [ ] Student record workflow is defined.
- [ ] Staff record workflow is defined.
- [ ] Course allocation workflow is defined.
- [ ] Course registration workflow is defined.
- [ ] Result workflow is defined.

---

## 27.3 Risk Control Checklist

- [ ] Result processing is not rushed before academic data is clean.
- [ ] Payment rules are configurable.
- [ ] Approval workflow is configurable.
- [ ] Tenant data is separated.
- [ ] Audit trail is planned for sensitive actions.
- [ ] Result correction requires approval.
- [ ] Role permissions prevent unauthorized access.
- [ ] Reports are planned for management use.
- [ ] Public website content is tenant-specific.
- [ ] No school-specific item is hard-coded into the core platform.

---

# 28. Closing Note

This blueprint should serve as the main reference for planning the SaaS SHST Information Management System.

The central idea is to create a flexible platform that understands the general structure of Nigerian Schools of Health Sciences and Technology while allowing every subscribing school to configure its own academic, administrative, financial, and public-facing identity.
