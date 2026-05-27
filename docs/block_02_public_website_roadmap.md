  
## Block 2: Public Website and Institutional Showcase

---

## Document Status

**Project:** SaaS Multi-Tenant Information Management System for Nigerian Schools of Health Sciences and Technology  
**Block:** Block 2  
**Block Name:** Public Website and Institutional Showcase  
**Audience:** Developers, AI coding agents, technical leads, project supervisors  
**Framework:** CodeIgniter 4  
**Authentication Foundation:** CodeIgniter Shield  
**Frontend Enhancement:** Vue.js Composition API where useful  
**Dependency:** Block 1 — SaaS and School Configuration  
**Development Mode:** Tenant-configurable, database-driven, mobile-first, Nigeria-aware  

---

# 1. Block 2 Purpose

Block 2 delivers the public-facing website layer for every tenant institution on the SaaS platform.

Each School of Health Sciences and Technology must be able to manage its own public website content from the system without custom development, without editing code, and without affecting other tenant schools.

The public website must help each school showcase its identity, departments, programmes, admission information, announcements, news, contact details, gallery, and portal access.

---

# 2. Strategic Decision

Block 2 shall be implemented as a **tenant-resolved, database-driven public website CMS**.

No public website content belonging to a school shall be hard-coded.

Every public page must load content based on resolved tenant context.

The public website must use tenant profile, branding, departments, programmes, and other setup data already created in Block 1.

---

# 3. Dependency on Block 1

Block 2 must not proceed unless the following Block 1 foundations exist:

1. Tenant creation and tenant status management
2. Tenant resolver
3. Tenant-scoped base model
4. Tenant profile configuration
5. Tenant theme and branding resolver
6. Department configuration
7. Programme configuration
8. File/media foundation
9. Audit trail foundation
10. Tenant admin layout
11. Role and authority foundation
12. Side navigation resolver
13. Standard API response format

If any of these are missing, Block 2 implementation must pause and complete the missing foundation first.

---

# 4. Block 2 Objective

At the end of Block 2, each tenant school must be able to:

- Display a public homepage
- Display school profile information
- Display departments
- Display programmes
- Display admission information
- Publish news and announcements
- Publish academic calendar notices
- Manage public images and gallery
- Show management profile
- Show contact information
- Provide student/applicant portal access links
- Control content status: draft, published, scheduled, archived
- Maintain tenant-specific branding on public pages
- Manage public content from tenant admin dashboard
- Prevent cross-tenant content leakage
- Audit public content publishing and updates

---

# 5. Block 2 Scope

## 5.1 In Scope

Block 2 includes:

1. Public website layout
2. Tenant public homepage
3. About page
4. Department listing page
5. Department detail page
6. Programme listing page
7. Programme detail page
8. Admission information page
9. News listing page
10. News detail page
11. Announcement listing page
12. Announcement detail page
13. Academic calendar notice page
14. Management profile page
15. Contact page
16. Gallery page
17. Public media handling
18. Public website content management inside tenant admin dashboard
19. Public content approval/publishing workflow
20. Public content audit logging
21. Public website navigation management
22. Public website SEO metadata fields
23. Portal access link placement
24. Tenant-branded public theme application

---

## 5.2 Out of Scope

The following must not be implemented in Block 2:

- Applicant registration
- Admission form submission
- Application fee payment
- Admission screening
- Student login workflows beyond portal link
- Student profile management
- Staff profile management
- Payment processing
- Result publication
- Course registration
- Transcript request
- Full CMS page builder with drag-and-drop complexity
- Blog-like multi-author editorial system beyond school news and announcements
- Advanced SEO automation
- SMS/email newsletter system
- Social media auto-posting

These belong to later blocks or optional future enhancement.

---

# 6. Non-Negotiable Rules for Block 2

1. Public pages must resolve tenant context before loading content.

2. Public website content must be tenant-scoped.

3. No tenant website content shall be hard-coded.

4. Public website must use tenant branding from database.

5. Department colors must be usable as visual accents where department content is displayed.

6. Public pages must be mobile-first.

7. Public content management must happen inside tenant admin area.

8. Only authorized tenant users may create, edit, publish, archive, or delete public content.

9. All content publishing actions must be audited.

10. Public content status must be controlled using lifecycle states.

11. File access must respect file visibility rules.

12. Public images must not expose private tenant files.

13. Public website navigation must be database-driven.

14. Public pages must never leak another tenant’s departments, programmes, news, media, or contact information.

15. The public website must remain lightweight, fast, and friendly to low-bandwidth Nigerian users.

---

# 7. Primary Users

## 7.1 Public Users

Public users include:

- Prospective applicants
- Parents/guardians
- Current students
- Alumni
- Regulatory visitors
- General public
- Partner institutions
- Government officials
- Professional council representatives

Public users do not need authentication.

---

## 7.2 Tenant Internal Users

Internal users who may manage public website content include:

- Tenant Super Admin
- Tenant Admin
- Website Editor
- Registrar
- Admission Officer
- ICT Officer
- Public Relations Officer, if configured as operational authority

---

# 8. Operational Authorities Required

Block 2 must use the second-layer operational authority model from the foundation document.

Required authorities:

1. Website Editor
2. Website Publisher
3. News Manager
4. Announcement Manager
5. Admission Content Manager
6. Gallery Manager
7. Contact Info Manager
8. Management Profile Manager

A user must have the required authority before performing the related action.

---

# 9. IAM Group Rules

## Tenant Super Admin

Can manage all public website content for own tenant.

## Tenant Admin

Can manage public website content only if assigned required operational authority.

## Lecturer

Cannot manage public website content unless explicitly assigned a website-related operational authority.

## Student

Cannot manage public website content.

## Platform Admin

Can access tenant public website configuration only through controlled platform support mode, and every access must be audited.

---

# 10. Public Website Tenant Resolution

## 10.1 Resolution Methods

The public website must support tenant resolution through:

1. Custom domain  
   Example: `www.schoolname.edu.ng`

2. Subdomain  
   Example: `schoolname.platform.com`

3. Tenant slug route, where needed  
   Example: `platform.com/schoolname`

## 10.2 Resolution Rule

If a public request cannot resolve a valid active tenant, the system must show a controlled public error page.

The system must not show content from a fallback tenant.

## 10.3 Tenant Status Rule

Public website behavior based on tenant status:

| Tenant Status | Public Website Behavior |
|---|---|
| Active | Show public website |
| Pending setup | Show setup-in-progress page or platform placeholder |
| Suspended | Show service unavailable page |
| Deactivated | Show tenant not available page |
| Archived | Do not show public website |

---

# 11. Public Website Layout

## 11.1 Layout Decision

Block 2 must create a separate public-facing layout.

This layout is different from internal layouts.

It must not use the internal dashboard side navigation.

## 11.2 Public Layout Sections

The public layout must include:

1. Top utility/header section
2. Main navigation section
3. Hero/content header section
4. Main content section
5. Footer section

## 11.3 Header Requirements

The public header must support:

- Tenant logo
- Tenant name
- Tenant motto where configured
- Public navigation menu
- Admission link
- Portal login link
- Contact shortcut
- Mobile menu toggle

## 11.4 Footer Requirements

The public footer must support:

- School name
- Address
- Phone number
- Email
- Important links
- Departments/programmes quick links
- Portal link
- Copyright text
- Powered-by platform text, where enabled

## 11.5 Mobile Rule

On mobile:

- Header must collapse properly
- Navigation must use a mobile menu
- Hero content must not overflow
- Images must be optimized
- Important actions must remain visible
- Admission and portal buttons must be easy to tap

---

# 12. Tenant Branding Rules

The public website must use tenant theme settings from Block 1.

## 12.1 Required Branding Inputs

The public layout must use:

- Tenant logo
- Tenant short name or full name
- Primary color
- Secondary color
- Accent color
- Favicon
- Motto where available

## 12.2 Department Color Usage

Department colors must be used in:

- Department listing cards
- Department detail header
- Programme badges
- Department-related news labels
- Public programme pages where applicable

## 12.3 Branding Fallback Rule

If tenant branding is incomplete, the system may use platform default theme values.

The public page must never break because a tenant has not uploaded logo or colors.

---

# 13. Public Website Content Types

Block 2 must support the following content types.

---

## 13.1 Homepage Content

### Purpose

To present the tenant institution clearly and professionally.

### Required Sections

The homepage must support:

- Hero section
- Brief school introduction
- Featured programmes
- Featured departments
- Admission call-to-action
- Latest news
- Latest announcements
- Contact summary
- Portal access button

### Content Source

Homepage content must combine:

- Tenant profile data
- Homepage configuration records
- Published programmes
- Published departments
- Published news
- Published announcements

### Business Rule

Homepage content must be tenant-configurable but not overly complex.

Avoid a heavy page-builder in this block.

---

## 13.2 About Page

### Purpose

To explain the school’s identity, mission, vision, and background.

### Required Fields

- About summary
- History/background
- Mission
- Vision
- Core values
- Motto
- Ownership description
- Accreditation/professional-body note, where configured

### Statuses

- Draft
- Published
- Archived

---

## 13.3 Department Pages

### Purpose

To display tenant departments created in Block 1.

### Required Capabilities

Public users must be able to:

- View all published departments
- View department details
- See programmes under each department
- See department color identity
- See department description

### Content Source

Department base data comes from Block 1.

Block 2 may add public-facing department content such as:

- Public description
- Department image
- Department public status
- Department page slug
- SEO metadata

### Rule

A department may exist internally but be hidden from public website.

Therefore, public visibility must be configurable.

---

## 13.4 Programme Pages

### Purpose

To display programmes offered by the tenant school.

### Required Capabilities

Public users must be able to:

- View programme list
- Filter programmes by department
- View programme details
- See duration
- See award type
- See entry requirements
- See admission availability status

### Content Source

Programme base data comes from Block 1.

Block 2 may add public-facing programme details such as:

- Public description
- Entry requirements
- Career opportunity note
- Duration explanation
- Admission status
- Programme image
- SEO metadata

### Rule

A programme can exist internally but be hidden from public website.

Public visibility must be tenant-controlled.

---

## 13.5 Admission Information Page

### Purpose

To inform prospective applicants about available admission opportunities.

### Required Content

- Admission status: open or closed
- Application instructions
- General admission requirements
- Programme-specific requirements
- Application fee note, if configured
- Screening information
- Required documents
- Important dates
- How to apply
- Link to applicant portal, when Block 3 is available

### Rule

Block 2 only displays admission information.

It must not process applications.

Application processing belongs to Block 3.

---

## 13.6 News Content

### Purpose

To allow schools to publish official news.

### Required Fields

- Title
- Slug
- Summary
- Body
- Featured image
- Category
- Related department, optional
- Author/display name
- Publish date
- Status
- SEO title
- SEO description

### Statuses

- Draft
- Published
- Scheduled
- Archived

### Rule

Only published news should appear on the public website.

Draft, archived, or future scheduled news must not be visible publicly before publish time.

---

## 13.7 Announcement Content

### Purpose

To publish short official notices.

### Required Fields

- Title
- Body
- Announcement type
- Start date
- End date
- Priority
- Status
- Target audience
- Related department, optional
- Related programme, optional

### Target Audience Options

- Public
- Applicants
- Students
- Staff
- Department-specific audience
- Programme-specific audience

In Block 2, only public announcements appear on the public website.

Internal targeted announcements may be expanded in later blocks.

---

## 13.8 Academic Calendar Notices

### Purpose

To show important dates to the public.

### Required Fields

- Title
- Description
- Start date
- End date
- Academic session, optional
- Semester, optional
- Status
- Visibility

### Example Notices

- Application opens
- Application closes
- Screening date
- Resumption date
- Examination period
- Registration deadline

---

## 13.9 Management Profile

### Purpose

To display institutional leadership.

### Required Fields

- Name
- Title/designation
- Profile photo
- Short biography
- Sort order
- Status

### Rule

Management profile is public content.

It must not be confused with internal staff management from later blocks.

If a staff record exists later, it may be linked, but Block 2 can use standalone public profile records.

---

## 13.10 Gallery

### Purpose

To showcase school facilities, events, laboratories, classrooms, and official activities.

### Required Fields

- Image
- Title
- Description
- Category
- Status
- Sort order

### Rule

Gallery images must use the file/media foundation and must be marked as public before displaying.

---

## 13.11 Contact Page

### Purpose

To show official contact details and inquiry channels.

### Required Content

- School address
- Email
- Phone numbers
- Office hours
- Location description
- Contact form, optional
- Social media links, optional

### Rule

Contact details must come from tenant profile or tenant public contact settings.

Do not hard-code contact information.

---

# 14. Public Website Navigation Management

## 14.1 Purpose

To allow tenant schools to control their public website menu without code changes.

## 14.2 Required Capabilities

Tenant admin must be able to:

- View public menu items
- Add menu item
- Edit menu item
- Reorder menu item
- Hide/show menu item
- Add external link
- Link menu item to internal public page

## 14.3 Menu Item Fields

- Tenant ID
- Label
- URL or route target
- Parent item
- Sort order
- Visibility
- Open in new tab flag
- Status

## 14.4 Rule

The public menu must be database-driven.

Default menu items may be seeded as templates but must remain editable by tenant users.

---

# 15. Content Lifecycle Management

## 15.1 Standard Content Statuses

All public content must use controlled lifecycle statuses:

1. Draft
2. Published
3. Scheduled
4. Archived

## 15.2 Publishing Rule

Only published content is publicly visible.

Scheduled content becomes visible only when publish date/time is reached.

Archived content must not appear in active public listings.

## 15.3 Delete Rule

Public content should use soft delete where applicable.

Deleting published content must be audited.

---

# 16. Approval and Publishing Workflow

## 16.1 Decision

Block 2 shall support a simple but expandable publishing workflow.

## 16.2 Minimum Workflow

1. Create draft
2. Review draft
3. Publish content
4. Archive content when no longer needed

## 16.3 Authority Rule

A user may create content but may not publish unless assigned publishing authority.

Example:

- Website Editor can create and edit drafts.
- Website Publisher can publish.
- Tenant Super Admin can do both.

## 16.4 Future Expansion

Complex multi-stage editorial approval is not required in Block 2.

The structure must allow it later without refactoring.

---

# 17. Required Admin Pages

Block 2 must add public website management pages inside the tenant admin layout.

Required pages:

1. Website dashboard
2. Homepage settings
3. About page manager
4. Department public content manager
5. Programme public content manager
6. Admission information manager
7. News manager
8. Announcement manager
9. Academic calendar notice manager
10. Management profile manager
11. Gallery manager
12. Contact settings manager
13. Public menu manager
14. Public media manager
15. Website preview link

Each page must use tenant admin layout and side navigation resolver.

---

# 18. Required Public Pages

Block 2 must create the following public pages:

1. Homepage
2. About page
3. Departments listing
4. Department detail
5. Programmes listing
6. Programme detail
7. Admission information
8. News listing
9. News detail
10. Announcements listing
11. Announcement detail
12. Academic calendar notices
13. Management profile
14. Gallery
15. Contact page
16. Public error/not-found page
17. Tenant unavailable page

---

# 19. Database Ownership Rules

## 19.1 Tenant-Owned Tables

The following content is tenant-owned:

- Public homepage settings
- Public pages
- Public menu items
- Public news
- Public news categories
- Public announcements
- Public calendar notices
- Public gallery items
- Management profiles
- Public contact settings
- Public media references
- Department public content
- Programme public content

Each must include `tenant_id`.

---

## 19.2 Platform-Owned Tables

Platform-owned data may include:

- Default public page templates
- Default menu templates
- Public theme defaults
- Platform-level content templates, if needed

---

## 19.3 Shared Reference Data

Shared reference data may include:

- Nigerian states
- LGAs
- Announcement type templates
- News category templates

Shared references must not replace tenant-owned content.

---

# 20. Required Services

Block 2 must include service-layer logic for:

1. PublicTenantResolverService  
2. PublicWebsiteService  
3. PublicHomepageService  
4. PublicPageService  
5. PublicMenuService  
6. PublicDepartmentContentService  
7. PublicProgrammeContentService  
8. AdmissionInfoContentService  
9. NewsService  
10. AnnouncementService  
11. AcademicCalendarNoticeService  
12. GalleryService  
13. ManagementProfileService  
14. PublicContactService  
15. PublicMediaService  
16. PublicThemeService  
17. PublicContentPublishingService  
18. PublicWebsiteAuditService  

Controllers must remain thin.

---

# 21. Required Controllers

## 21.1 Public Controllers

Public controllers should handle:

- Homepage
- About
- Departments
- Programmes
- Admission information
- News
- Announcements
- Calendar notices
- Management profile
- Gallery
- Contact

Public controllers must resolve tenant context and load published tenant content only.

---

## 21.2 Tenant Admin Controllers

Tenant admin controllers should manage:

- Public website dashboard
- Homepage settings
- Page content
- Public menu
- News
- Announcements
- Gallery
- Admission info
- Programme public content
- Department public content
- Contact settings
- Management profiles

Tenant admin controllers must enforce:

- Authentication
- Tenant membership
- IAM group access
- Operational authority
- Tenant scoping
- Audit logging for changes

---

# 22. Required Models

Models must extend the correct tenant-aware base model.

Required models may include:

1. PublicPageModel
2. PublicMenuItemModel
3. PublicHomepageSettingModel
4. PublicNewsModel
5. PublicNewsCategoryModel
6. PublicAnnouncementModel
7. PublicCalendarNoticeModel
8. PublicGalleryItemModel
9. PublicManagementProfileModel
10. PublicContactSettingModel
11. DepartmentPublicContentModel
12. ProgrammePublicContentModel
13. PublicMediaModel

Every tenant-owned model must enforce `tenant_id`.

---

# 23. Required API Endpoints

Block 2 may expose API endpoints for Vue-powered admin interactions.

API endpoints must support:

- Content draft save
- Content publish action
- Content archive action
- Menu reorder
- Gallery reorder
- Image upload reference
- Department/programme public visibility toggle
- News filtering
- Announcement filtering
- Preview data loading

All API endpoints must return the standard API response shape.

---

# 24. Vue.js Usage

Vue Composition API shall be used where interactivity improves usability.

## 24.1 Vue Should Be Used For

- Public menu reorder
- News filtering in admin
- Gallery management
- Image preview
- Homepage section ordering
- Department/programme visibility toggle
- Draft autosave where useful
- Admission information form sections
- Contact settings live preview
- Theme preview if needed

## 24.2 Full Page Reload Should Be Used For

- Public page viewing
- Final publish action confirmation
- Archive action confirmation
- Public website preview
- Major content save where full server state refresh is safer

---

# 25. File and Media Rules

## 25.1 File Categories

Block 2 must support these public file categories:

- Public logo display reference
- Homepage hero image
- About page image
- Department image
- Programme image
- News featured image
- Announcement attachment, optional
- Gallery image
- Management profile photo

## 25.2 Visibility Rule

Only files marked as public may appear on the public website.

Internal tenant files must never be exposed through public website URLs.

## 25.3 Image Optimization Rule

Images must be handled in a mobile-aware way.

The public website must not load unnecessarily large images on mobile.

---

# 26. SEO and Public Metadata

## 26.1 Purpose

Basic SEO metadata helps schools appear more credible and discoverable online.

## 26.2 Required Metadata Fields

Public content should support:

- Meta title
- Meta description
- Slug
- Canonical URL where applicable
- Featured image alt text
- Open graph title, optional
- Open graph description, optional

## 26.3 Slug Rule

Slugs must be tenant-scoped.

Two tenants may use the same slug.

One tenant should not have conflicting slugs within the same content type.

---

# 27. Nigeria-Aware Public Website Considerations

The public website must be built for Nigerian SHST realities.

## 27.1 User Reality

Assume public users may:

- Use mobile phones
- Have slow network
- Browse with limited data
- Need direct admission information
- Prefer phone contact over email
- Need clear programme requirements
- Need visible school legitimacy markers

## 27.2 Content Priority

Public pages must emphasize:

- Programmes offered
- Admission requirements
- Application deadline
- School contact
- Location
- Accreditation/professional notes where available
- Official announcements
- Portal link

## 27.3 Language and Clarity

Content management should allow simple English.

The system should not force overly technical terminology on tenant users.

---

# 28. Required Audit Events

Block 2 must audit:

- Homepage updated
- About page updated
- Department public content updated
- Programme public content updated
- Admission information updated
- News created
- News updated
- News published
- News archived
- Announcement created
- Announcement updated
- Announcement published
- Announcement archived
- Calendar notice created
- Calendar notice updated
- Gallery item uploaded
- Gallery item archived
- Management profile created
- Management profile updated
- Contact settings updated
- Public menu updated
- Public content deleted
- Public image changed
- Website preview accessed by admin
- Platform admin accessed tenant website settings

Audit logs must include:

- Tenant ID
- User ID
- Action
- Content type
- Content ID
- Old value where needed
- New value where needed
- IP address
- User agent
- Timestamp
- Status

---

# 29. Required Reports

Block 2 must provide basic reports inside tenant admin.

Required reports:

1. Published news report
2. Draft news report
3. Archived news report
4. Published announcements report
5. Public pages status report
6. Gallery items report
7. Public menu structure report
8. Website content audit report
9. Department public visibility report
10. Programme public visibility report

Reports must be tenant-scoped.

---

# 30. Tenant Admin Website Dashboard

Block 2 must add a website dashboard inside tenant admin.

## Dashboard Must Show

- Public website status
- Homepage completion status
- Number of published news items
- Number of draft news items
- Number of active announcements
- Number of visible programmes
- Number of visible departments
- Gallery item count
- Last published content
- Quick links to manage homepage, news, admission info, and contact settings
- Public website preview link

---

# 31. Public Website Setup Checklist

Each tenant must see a public website setup checklist.

A public website is considered minimally ready when:

- Tenant logo exists
- Tenant colors configured
- Homepage content configured
- About page published
- Contact information completed
- At least one department visible
- At least one programme visible
- Admission information page configured
- Public menu configured
- Portal link configured

The checklist should appear in the tenant website dashboard.

---

# 32. Access Control Matrix

## Tenant Super Admin

Can:

- Manage all public website content
- Publish and archive content
- Manage public menu
- Manage contact settings
- Manage gallery
- View website audit

## Tenant Admin

Can:

- Access website management only if assigned related operational authority
- Manage content within authority scope

## Website Editor

Can:

- Create drafts
- Edit assigned content
- Upload public images
- Preview content

Cannot:

- Publish unless also assigned Website Publisher authority

## Website Publisher

Can:

- Publish approved content
- Archive content
- Restore archived content where allowed

## Admission Content Manager

Can:

- Manage admission information page
- Update admission dates and instructions

Cannot:

- Process applications in Block 2

## Gallery Manager

Can:

- Upload gallery images
- Edit gallery captions
- Archive gallery items

## Platform Admin

Can:

- View tenant website configuration only through platform support mode
- Access must be audited

## Public Visitor

Can:

- View published public content only

Cannot:

- View draft content
- View archived content
- View tenant internal records

---

# 33. Validation Rules

## General Content Validation

- Title is required.
- Slug is required.
- Slug must be unique within tenant and content type.
- Status must be valid.
- Publish date must be valid.
- Tenant ID must be present.
- User must have authority to save or publish.

## News Validation

- News title is required.
- News body is required before publish.
- Featured image is optional but recommended.
- Published news must have publish date.
- Scheduled news must have future publish date.

## Announcement Validation

- Announcement title is required.
- Announcement body is required.
- Start date must not be after end date.
- Target audience must be valid.
- Public announcement must be visible only if published and within valid date range.

## Gallery Validation

- Image is required.
- Image must be public-visible before display.
- Caption is optional.
- Status must be valid.

## Contact Validation

- At least one official phone number or email must be provided.
- Nigerian phone number must be normalized where possible.
- Email must be valid.

## Programme/Department Public Content Validation

- Related department/programme must belong to tenant.
- Public visibility must be tenant-controlled.
- Public slug must be tenant-scoped.

---

# 34. Routing Rules

## Public Routes

Public routes must be tenant-resolved.

Example route groups may include:

- `/`
- `/about`
- `/departments`
- `/departments/{slug}`
- `/programmes`
- `/programmes/{slug}`
- `/admission`
- `/news`
- `/news/{slug}`
- `/announcements`
- `/announcements/{slug}`
- `/calendar`
- `/management`
- `/gallery`
- `/contact`

## Admin Routes

Tenant admin routes may include:

- `/tenant/website`
- `/tenant/website/homepage`
- `/tenant/website/about`
- `/tenant/website/departments`
- `/tenant/website/programmes`
- `/tenant/website/admission`
- `/tenant/website/news`
- `/tenant/website/announcements`
- `/tenant/website/calendar`
- `/tenant/website/management`
- `/tenant/website/gallery`
- `/tenant/website/contact`
- `/tenant/website/menu`

Actual route names may differ, but must follow the project route naming convention.

---

# 35. Error Handling

## Public Error Messages

Public errors must be simple:

- “This school website is not available.”
- “This page could not be found.”
- “This announcement is no longer available.”
- “Admission information is not yet published.”
- “No programme has been published yet.”

## Admin Error Messages

Admin errors must be direct:

- “You are not allowed to publish this content.”
- “This programme does not belong to your school.”
- “Please publish the page before previewing publicly.”
- “Only public files can be used on public website pages.”

Technical errors must be logged privately.

---

# 36. Performance Rules

The public website must be optimized for fast loading.

Mandatory rules:

1. Avoid unnecessary database queries.
2. Cache public menu where safe.
3. Cache tenant theme where safe.
4. Paginate news and announcement listings.
5. Use optimized images.
6. Avoid heavy JavaScript on public pages.
7. Use Vue only where necessary in admin interfaces.
8. Public pages must remain usable on low-bandwidth connections.

---

# 37. Security Rules

Block 2 must enforce:

- Tenant context for every public request
- Tenant scoping for every public content query
- Authorization for admin content management
- CSRF protection for admin form submissions
- File visibility checks
- Input validation
- Output escaping
- Soft delete where appropriate
- Audit logging for sensitive actions
- No draft content leakage
- No cross-tenant slug resolution
- No direct access to private files

---

# 38. Implementation Order for Block 2

Developers must implement Block 2 in this order:

## Step 1: Confirm Block 1 Foundations

- Tenant resolver working
- Tenant-scoped models working
- Tenant admin layout working
- File/media foundation working
- Audit foundation working
- Theme resolver working
- Operational authority foundation working

## Step 2: Public Layout Foundation

- Create public layout
- Apply tenant branding
- Add responsive header
- Add public navigation placeholder
- Add footer
- Add mobile menu behavior

## Step 3: Public Website Data Models

- Public pages
- Homepage settings
- Public menu
- News
- Announcements
- Calendar notices
- Gallery
- Contact settings
- Management profiles
- Department public content
- Programme public content

## Step 4: Public Website Admin Area

- Website dashboard
- Setup checklist
- Homepage manager
- About manager
- Contact manager
- Menu manager

## Step 5: Department and Programme Public Content

- Public visibility controls
- Department page content
- Programme page content
- Programme requirements fields
- Department/programme public pages

## Step 6: News and Announcements

- News manager
- Announcement manager
- Status lifecycle
- Publishing controls
- Public listing/detail pages

## Step 7: Gallery and Management Profile

- Gallery manager
- Image handling
- Management profile manager
- Public gallery page
- Public management page

## Step 8: Admission Information Page

- Admission information manager
- Admission public page
- Portal/application link placeholder
- Important dates and requirements display

## Step 9: Audit, Reports, and Access Control

- Audit all content changes
- Add website reports
- Enforce operational authorities
- Add website dashboard summaries

## Step 10: Testing and Stabilization

- Tenant isolation testing
- Public visibility testing
- Mobile testing
- Content lifecycle testing
- File visibility testing
- Audit testing
- Performance testing

---

# 39. Testing Checklist

## Tenant Resolution Tests

- Public website resolves correct tenant by subdomain.
- Public website resolves correct tenant by custom domain placeholder.
- Invalid tenant shows controlled unavailable page.
- Suspended tenant does not show public content.
- Tenant A route cannot display Tenant B content.

## Public Content Tests

- Published content displays publicly.
- Draft content does not display publicly.
- Archived content does not display publicly.
- Scheduled content does not display before scheduled time.
- News listing shows only tenant news.
- Announcement listing shows only tenant announcements.
- Department listing shows only visible tenant departments.
- Programme listing shows only visible tenant programmes.

## Admin Access Tests

- Tenant Super Admin can manage website content.
- Tenant Admin needs correct operational authority.
- Website Editor cannot publish unless granted publishing authority.
- Student cannot access website admin.
- Lecturer cannot access website admin unless assigned authority.
- Platform admin access is audited.

## File Visibility Tests

- Public image displays correctly.
- Private file cannot be accessed publicly.
- Tenant A cannot access Tenant B public media records by ID.
- Deleted or archived image does not appear publicly.

## Mobile Tests

- Homepage works on mobile.
- Public navigation collapses correctly.
- News detail page is readable.
- Programme pages are readable.
- Contact page is easy to use.
- Gallery does not break layout.
- Admin content forms work on mobile.

## Audit Tests

- News publish is audited.
- Announcement publish is audited.
- Contact update is audited.
- Public menu change is audited.
- Homepage update is audited.
- Gallery upload is audited.

## Performance Tests

- Homepage loads quickly.
- News listing paginates correctly.
- Gallery does not load too many images at once.
- Public menu is not rebuilt unnecessarily on every request if caching is enabled.

---

# 40. Acceptance Criteria

Block 2 is complete only when:

1. Each active tenant has a public website endpoint.

2. Public website uses tenant branding from database.

3. Public website content is tenant-scoped.

4. Public layout is mobile-first and responsive.

5. Public navigation is database-driven.

6. Tenant admin can manage homepage content.

7. Tenant admin can manage about content.

8. Tenant admin can manage public department and programme visibility.

9. Tenant admin can manage admission information.

10. Tenant admin can create, edit, publish, and archive news.

11. Tenant admin can create, edit, publish, and archive announcements.

12. Tenant admin can manage gallery items.

13. Tenant admin can manage management profile.

14. Tenant admin can manage contact information.

15. Public visitors can view only published content.

16. Draft, scheduled, archived, and private content is protected.

17. All publishing and content changes are audited.

18. Public website admin pages enforce IAM and operational authority.

19. File visibility rules prevent private file leakage.

20. Tenant A public website cannot display Tenant B content.

21. Block 2 does not implement admission application processing.
