# BTS Final-Year Project Defense: Complete Presentation, Pitch & Jury Master Guide

**Project Theme:** Design and Implementation of a Resource Assignment and Allocation Platform for Djezzy — Case Study: IT and HR Departments  
**Host Organization:** Djezzy — Optimum Telecom Algérie  
**Academic Institution:** INSFP Rahmania  
**Promotion / Academic Year:** 2025 / 2026  
**Presenters:** 
- **Presenter 1 (First):** Brahimi Mohamed Amine  
- **Presenter 2 (Second):** Benkheira Mohamed Kamal  
**Academic Supervisor:** Mme Krim (INSFP Rahmania)  
**Technical Promoter:** M. Ali Bouras (Djezzy)

---

# SECTION 1 — PROJECT SUMMARY (IN SIMPLE WORDS)

The **Djezzy Resource Assignment Platform** is an enterprise web application that manages technical qualifications, project staffing allocations, and governance workflows for Djezzy (18M+ mobile subscribers, 300+ technical employees).

Before our project, assigning staff to engineering projects was handled manually with spreadsheets and emails:
1. Managers could not easily find who possessed required technical competencies or certifications.
2. Some employees were assigned to multiple projects simultaneously, exceeding 100% workload capacity.
3. Assignment decisions lacked audit history.
4. Employees had no self-service channel to update skills or submit verified diplomas.

Our application solves this with:
- **Manager Back-Office (Filament 5):** For managers and HR officers to monitor project needs, run our recommendation matching engine, and authorize assignments.
- **Employee Portal (React 19 / Inertia):** For employees to track their personal workload, view project deadlines, and upload verified PDF certificates.
- **Smart Recommendation Engine:** Calculates candidate compatibility out of 100 points across 5 weighted factors (Skills 40%, Certifications 20%, Languages 15%, Availability 15%, Workload 10%).
- **Security & Relational Integrity:** Enforces database-level safety with a PostgreSQL partial unique index, FIDO2 Passkeys, two-factor authentication (2FA), and complete before/after audit logging.
- **Tested & Verified:** Validated through 124 automated PHPUnit tests (492 assertions, 100% pass rate) and 7 Playwright end-to-end browser specifications.

---

# SECTION 2 — TWO-PERSON DIVISION

**Target Presentation Time:** 15–18 Minutes.

| Section | Slides | Presenter | Key Focus |
| :--- | :--- | :--- | :--- |
| **Opening & Context** | Slides 1 – 3 | **Presenter 1: Brahimi Mohamed Amine** | Welcome, 3 real logos, manual staffing breakdowns, 4 objectives |
| **Solution & Governance** | Slides 4 – 6 | **Presenter 1: Brahimi Mohamed Amine** | Dual interfaces, 6 user roles, 2 core workflows |
| **Technical Architecture** | Slides 7 – 8 | **Presenter 2: Benkheira Mohamed Kamal** | 3-tier architecture, 44 relational tables, partial index |
| **Algorithms & Security** | Slides 9 – 10 | **Presenter 2: Benkheira Mohamed Kamal** | Matching formula (5 weights), Passkeys, audit trail |
| **Live Demonstration** | Slides 11 – 12 | **Presenter 1: Brahimi Mohamed Amine** | Employee portal & Manager back-office walkthrough |
| **Quality & Closing** | Slides 13 – 16 | **Presenter 2: Benkheira Mohamed Kamal** | 4 challenges solved, 124 tests, future roadmap, closing |

---

# SECTION 3 — SLIDE-BY-SLIDE SYNCHRONIZED FRAGMENTS & SPEECH

Every visual fragment of a slide faces its exact corresponding speech paragraph. No action markers or click tags clutter the text: simply advance to reveal the next fragment and read the matching paragraph.

---

### SLIDE 1 — TITLE & THREE LOGOS
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 45 seconds
- **Fragments on Slide:** Complete Slide (Single Unit)

#### Fragment 1 &bull; Visual on Slide
Three real organization logos side-by-side: **Djezzy Logo** | **INSFP Rahmania Logo** | **Ministry of Vocational Training Logo**, project title "Design and Implementation of a Resource Assignment and Allocation Platform for Djezzy", subtitle "Case Study: IT and HR Departments", supervisors, and academic year 2025/2026.

#### Fragment 1 &bull; Spoken Words
> Good afternoon, Madame President, and members of the jury. I am Brahimi Mohamed Amine, and with my project partner Benkheira Mohamed Kamal, we present our graduation project: the Design and Implementation of a Resource Assignment and Allocation Platform for Djezzy — Case Study: IT and HR Departments. This project was supervised by Mme Krim at INSFP Rahmania, and M. Ali Bouras at Djezzy. We are pleased to present our work to you today.

---

### SLIDE 2 — THE PROBLEM: MANUAL RESOURCE ALLOCATION
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 60 seconds
- **Fragments on Slide:** 5 Sequential Fragments

#### Fragment 1 &bull; Visual on Slide
Left Card illuminates: **The Scale at Djezzy** (18M+ mobile subscribers, 300+ engineers) and **The Old Routine** (Excel spreadsheets, email chains, personal memory).

#### Fragment 1 &bull; Spoken Words
> Djezzy is a major telecommunications operator with over 18 million mobile subscribers and more than 300 technical engineers. Before our project, assigning staff to engineering projects was handled manually through scattered Excel spreadsheets, endless email threads, and personal memory.

#### Fragment 2 &bull; Visual on Slide
Right Card Item 1 illuminates: **Hard to Find Skills** (No central search for tools or certifications).

#### Fragment 2 &bull; Spoken Words
> This created our first major operational breakdown: finding the right skills was difficult. Project managers had no central directory to search for specific technical competencies, database proficiencies, or certifications across different teams.

#### Fragment 3 &bull; Visual on Slide
Right Card Item 2 illuminates: **Staff Overload** (Engineers booked on 2-3 projects exceeding 100%).

#### Fragment 3 &bull; Spoken Words
> Second was staff overload. Without real-time workload visibility, some engineers were accidentally booked on two or three projects simultaneously, exceeding 100% capacity and creating severe delivery delays.

#### Fragment 4 &bull; Visual on Slide
Right Card Item 3 illuminates: **No Decision History** (No audit log of who approved or changed staff).

#### Fragment 4 &bull; Spoken Words
> Third was the absence of decision history. There was no audit trail showing who requested an assignment, who approved it, or why an engineer was replaced, making accountability impossible.

#### Fragment 5 &bull; Visual on Slide
Right Card Item 4 illuminates: **Outdated Profiles** (Employees had no self-service channel to upload certs).

#### Fragment 5 &bull; Spoken Words
> Finally, employee profiles quickly became outdated. Engineers had no self-service channel to submit newly acquired certifications or diplomas, leaving HR records weeks or months behind reality.

---

### SLIDE 3 — PROJECT OBJECTIVES
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 50 seconds
- **Fragments on Slide:** 4 Sequential Objective Cards

#### Fragment 1 &bull; Visual on Slide
Card 1 illuminates: **1. Centralize** (One central database for 300+ employees, skills, certifications, and availability).

#### Fragment 1 &bull; Spoken Words
> To solve these operational breakdowns, we established four clear project objectives. Our first objective is to Centralize: create a single, unified database holding verified profiles, technical skills, certifications, and availability for all 300 technical staff.

#### Fragment 2 &bull; Visual on Slide
Card 2 illuminates: **2. Automate** (Match candidate skills and free hours to project requirements using algorithmic scoring).

#### Fragment 2 &bull; Spoken Words
> Our second objective is to Automate: implement an intelligent matching algorithm that evaluates candidate competencies and free hours against project needs to recommend the best candidates.

#### Fragment 3 &bull; Visual on Slide
Card 3 illuminates: **3. Govern** (Require manager approval before work starts and maintain full audit trail).

#### Fragment 3 &bull; Spoken Words
> Our third objective is to Govern: establish structured validation workflows and an immutable audit log so every staffing allocation requires formal managerial authorization.

#### Fragment 4 &bull; Visual on Slide
Card 4 illuminates: **4. Empower** (Give employees a self-service portal to update skills and upload verified certificates).

#### Fragment 4 &bull; Spoken Words
> Our fourth objective is to Empower: give employees a dedicated self-service web portal where they can check their assigned projects, view their workload, and submit verified certificates.

---

### SLIDE 4 — OUR SOLUTION: TWO DEDICATED INTERFACES
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 50 seconds
- **Fragments on Slide:** 2 Sequential Interface Cards

#### Fragment 1 &bull; Visual on Slide
Left Card illuminates: **1. Administrative Back-Office** (Filament 5 &bull; For Managers & HR &bull; KPIs, Matching Engine, Approvals).

#### Fragment 1 &bull; Spoken Words
> Rather than creating a single cluttered application, we designed two dedicated spaces tailored to each user type. For managers and HR personnel, we built an administrative back-office using Laravel Filament to manage projects, run the candidate matching engine, and approve staff requests.

#### Fragment 2 &bull; Visual on Slide
Right Card illuminates: **2. Employee Self-Service Portal** (React 19 &bull; For 300+ Staff &bull; Workload, Skill Updates, Notifications).

#### Fragment 2 &bull; Spoken Words
> For the 300 technical employees, we built a modern React web portal. Through this portal, engineers can view their active project assignments, monitor their workload in real time, and submit new certificates directly to HR.

---

### SLIDE 5 — USER ROLES & ACCESS CONTROL
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 60 seconds
- **Fragments on Slide:** 4 Sequential Role & Rule Cards

#### Fragment 1 &bull; Visual on Slide
Card 1 illuminates: **System Admins** (`super-admin` & `admin` &bull; Complete system control, roles, and project oversight).

#### Fragment 1 &bull; Spoken Words
> To maintain strict security and accountability, we organized users into six distinct roles. Our System Administrators include the super-admin, who maintains global system configuration and role permissions, and the admin, who oversees project operations and approves staff allocations.

#### Fragment 2 &bull; Visual on Slide
Card 2 illuminates: **Department Managers** (`hr` & `resource-manager` &bull; Verifies certificates and manages team capacity).

#### Fragment 2 &bull; Spoken Words
> Next, our Department Managers include HR officers, who verify employee certificates and approve skill updates, and Resource Managers, who supervise department staffing levels and team structures.

#### Fragment 3 &bull; Visual on Slide
Card 3 illuminates: **Project & Staff** (`project-manager` & `employee` &bull; Project creation, staffing requests, personal portal).

#### Fragment 3 &bull; Spoken Words
> For project execution, Project Managers define initiative requirements and request candidate allocations, while Employees access their portal to view assigned tasks and manage their personal profiles.

#### Fragment 4 &bull; Visual on Slide
Banner illuminates: **Key Rule: Separation of Duties** (Project Managers request staff, but only Admins or HR can approve).

#### Fragment 4 &bull; Spoken Words
> Our primary governance rule is Separation of Duties: Project Managers can search and propose candidates, but only an Administrator or HR officer can approve the booking. This prevents bias and eliminates accidental double-booking.

---

### SLIDE 6 — CORE WORKFLOWS: HOW WORK MOVES
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 75 seconds
- **Fragments on Slide:** 5 Sequential Workflow Steps

#### Fragment 1 &bull; Visual on Slide
Phase 1 illuminates: **1. PENDING: Manager Proposes Staff** (Selects candidate & creates draft).

#### Fragment 1 &bull; Spoken Words
> Our platform structures daily operations into two automated workflows. The first is Workflow 1: The Assignment Lifecycle. It begins in the PENDING state: when a Project Manager needs technical staff, they select qualified candidates from the recommendation engine and create a draft staffing proposal.

#### Fragment 2 &bull; Visual on Slide
Phase 2 illuminates: **2. APPROVED: Admin / HR Validates** (Reviews workload & confirms booking).

#### Fragment 2 &bull; Spoken Words
> Next is the APPROVED state. To enforce our separation-of-duties policy, an HR administrator reviews the proposed candidates, verifies that no engineer exceeds 100% capacity, and officially authorizes the assignment.

#### Fragment 3 &bull; Visual on Slide
Phase 3 illuminates: **3. ACTIVE: Work Begins on Date** (Project starts & capacity locks).

#### Fragment 3 &bull; Spoken Words
> Third is the ACTIVE state. When the project's calendar start date arrives, the system automatically transitions the assignment to active. The engineers are officially committed to the project, and their capacity is locked in real time.

#### Fragment 4 &bull; Visual on Slide
Phase 4 illuminates: **4. COMPLETED: Project Delivered** (Assignment ends & history archived).

#### Fragment 4 &bull; Spoken Words
> Fourth is the COMPLETED state. Once project deliverables are completed and signed off, the assignment is marked finished. The engineers are immediately released back to full availability, and the entire history is permanently saved in the audit log.

#### Fragment 5 &bull; Visual on Slide
Bottom Section illuminates: **Workflow 2: Employee Profile & Certificate Verification Loop** (Upload &rarr; Pending &rarr; HR Verify &rarr; Updated).

#### Fragment 5 &bull; Spoken Words
> Below is our second process: the Profile and Certificate Verification Loop. When an employee uploads a new PDF diploma through their portal, it enters a verification queue. An HR administrator inspects the document, approves the update, and the employee's skills are instantly updated across the platform. Now, my partner Benkheira Mohamed Kamal will present our technical architecture.

---

### SLIDE 7 — APPLICATION ARCHITECTURE: HOW IT WORKS
- **Presenter:** Benkheira Mohamed Kamal (Presenter 2)
- **Time:** 60 seconds
- **Fragments on Slide:** 3 Sequential Tier Cards

#### Fragment 1 &bull; Visual on Slide
Tier 1 illuminates: **1. User Browser** (Filament 5 Manager Back-Office + React 19 Employee Portal).

#### Fragment 1 &bull; Spoken Words
> Thank you, Mohamed Amine. Now, let us examine how the system is engineered. We selected a proven 3-tier architecture. On the client tier, users interact with the system through standard web browsers: managers access the Filament back-office, while employees use our React 19 single-page application.

#### Fragment 2 &bull; Visual on Slide
Tier 2 illuminates: **2. Application Server** (Laravel 13 &bull; PHP 8.3 + Inertia.js Bridge).

#### Fragment 2 &bull; Spoken Words
> On the application tier, our backend runs on Laravel 13 and PHP 8.3. It coordinates all business logic, permission rules, and workflow state transitions. We use Inertia.js to seamlessly transmit data between Laravel and React without maintaining duplicate API endpoints.

#### Fragment 3 &bull; Visual on Slide
Tier 3 illuminates: **3. Database** (PostgreSQL 16 &bull; 44 Relational Tables + Engine Constraints).

#### Fragment 3 &bull; Spoken Words
> On the database tier, PostgreSQL 16 stores all relational company data across 44 tables. We enforce business constraints, relationship integrity, and assignment rules directly inside the PostgreSQL engine to guarantee complete reliability.

---

### SLIDE 8 — DATABASE DESIGN & RELATIONAL RIGOR
- **Presenter:** Benkheira Mohamed Kamal (Presenter 2)
- **Time:** 55 seconds
- **Fragments on Slide:** 2 Sequential Database Cards

#### Fragment 1 &bull; Visual on Slide
Left Card illuminates: **44 Relational Tables** (Organization, Employees, Projects & Staff, Governance).

#### Fragment 1 &bull; Spoken Words
> Our database schema is structured into 44 relational tables grouped into four main modules: organizational structure, employee profiles and skills, project tracking and assignments, and governance audit logs. Foreign keys enforce data integrity across all relationships.

#### Fragment 2 &bull; Visual on Slide
Right Card illuminates: **Key Technical Feat: Partial Index** (`single_active_assignment` on assignments where status IN ('approved', 'active')).

#### Fragment 2 &bull; Spoken Words
> A central technical achievement in our database is a PostgreSQL partial unique index on assignments. It guarantees at the database engine level that a project can have only one active assignment at any time, while preserving all historical records. Even if two managers submit simultaneous approvals, the database prevents any duplicate booking.

---

### SLIDE 9 — KEY FEATURE: THE RECOMMENDATION ENGINE
- **Presenter:** Benkheira Mohamed Kamal (Presenter 2)
- **Time:** 65 seconds
- **Fragments on Slide:** 3 Sequential Algorithmic Components

#### Fragment 1 &bull; Visual on Slide
Top Formula Bar illuminates: **Match Score = 40% Skills + 20% Certs + 15% Languages + 15% Availability + 10% Workload**.

#### Fragment 1 &bull; Spoken Words
> Our most important algorithmic feature is the Recommendation Engine. When a manager needs staff, the engine scores all eligible candidates out of 100 points: 40 points for required technical skills, 20 points for verified certifications, 15 for languages, 15 for schedule availability, and 10 for free workload capacity.

#### Fragment 2 &bull; Visual on Slide
Left Card illuminates: **What the Engine Evaluates** (Skills proficiency 1-5, Active certifications, Timeline availability, Free capacity).

#### Fragment 2 &bull; Spoken Words
> The engine checks proficiency levels from one to five, validates that certificates are current and verified, matches working dates against the project timeline, and prioritizes engineers who have available hours.

#### Fragment 3 &bull; Visual on Slide
Right Card illuminates: **Smart Safeguards** (Mandatory blockers, Overload protection at 100%, 1-click assignment creation).

#### Fragment 3 &bull; Spoken Words
> The engine also enforces strict safety blockers. If a candidate is already at 100% capacity or lacks a mandatory project certification, they are excluded from the candidate pool. The manager can then assign the top-ranked engineer with a single click.

---

### SLIDE 10 — SECURITY & COMPLETE AUDIT TRAIL
- **Presenter:** Benkheira Mohamed Kamal (Presenter 2)
- **Time:** 60 seconds
- **Fragments on Slide:** 4 Sequential Security Elements

#### Fragment 1 &bull; Visual on Slide
Card 1 illuminates: **1. Passkeys (FIDO2)** (Passwordless biometric login via TouchID, Windows Hello, or Security Keys).

#### Fragment 1 &bull; Spoken Words
> Enterprise telecom systems require rigorous security. First, we implemented FIDO2 Passkeys, enabling passwordless authentication using biometric sensors such as fingerprint readers, Windows Hello, or physical security keys for strong protection against phishing.

#### Fragment 2 &bull; Visual on Slide
Card 2 illuminates: **2. Two-Factor (2FA)** (TOTP 6-digit codes via Google/Microsoft Authenticator, plus 8 recovery codes).

#### Fragment 2 &bull; Spoken Words
> Second, we built time-based two-factor authentication. Users can pair authenticator apps like Google or Microsoft Authenticator to generate 6-digit TOTP codes, and receive emergency recovery codes.

#### Fragment 3 &bull; Visual on Slide
Card 3 illuminates: **3. Audit Log Trail** (User ID, IP address, timestamp, and JSON before/after diff).

#### Fragment 3 &bull; Spoken Words
> Third, we developed a complete audit log system. Every critical action—such as approving staff or updating a project—automatically captures the user ID, IP address, timestamp, and a before-and-after JSON snapshot of the changed data.

#### Fragment 4 &bull; Visual on Slide
Bottom Card illuminates: **Sample Logged Event** (`assignment_approved` &bull; Admin #14 &bull; pending &rarr; approved &bull; Audit ID #2084).

#### Fragment 4 &bull; Spoken Words
> Here in our audit log display, when an administrator approves an assignment, the system records the exact transition from pending to approved under a permanent audit ID for complete transparency. Now, Mohamed Amine will demonstrate the live application screens.

---

### SLIDE 11 — APPLICATION DEMO: EMPLOYEE PORTAL
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 55 seconds
- **Fragments on Slide:** 3 Sequential Demo Elements

#### Fragment 1 &bull; Visual on Slide
Left Screenshot illuminates: **Employee Dashboard &bull; Workload & Projects** (Active initiatives, workload percentage, evaluations).

#### Fragment 1 &bull; Spoken Words
> Thank you, Mohamed Kamal. Now let us examine the live application screens. Here is the Employee Self-Service Portal built with React 19. The main dashboard gives employees a clear view of their active project assignments, their current workload percentage, and upcoming milestone dates.

#### Fragment 2 &bull; Visual on Slide
Right Screenshot illuminates: **Request Modal &bull; Certificate Upload** (Skill selection & PDF verification upload).

#### Fragment 2 &bull; Spoken Words
> When an employee earns a new technical certification, they open this Request Modal to enter the details and upload their PDF verification document. The upload is securely staged for HR inspection.

#### Fragment 3 &bull; Visual on Slide
Bottom Highlights illuminate: **Personal Workload Tracking &bull; Document Proof &bull; Notification Bell**.

#### Fragment 3 &bull; Spoken Words
> The portal ensures employees always have clear visibility over their workload, provides document proof for every registered skill, and delivers instant notifications whenever a request is approved.

---

### SLIDE 12 — APPLICATION DEMO: MANAGER BACK-OFFICE
- **Presenter:** Brahimi Mohamed Amine (Presenter 1)
- **Time:** 55 seconds
- **Fragments on Slide:** 3 Sequential Demo Elements

#### Fragment 1 &bull; Visual on Slide
Left Screenshot illuminates: **Matching Modal &bull; Scored Candidates** (Ranked candidates with percentage match and one-click assignment).

#### Fragment 1 &bull; Spoken Words
> Next is the Manager Back-Office built with Filament. When a Project Manager needs engineers, this Recommendation Modal displays automatically scored candidates ranked by match percentage, showing their skills and available hours.

#### Fragment 2 &bull; Visual on Slide
Right Screenshot illuminates: **HR Verification &bull; Request Approval** (Inspect proof document, verify details, approve update).

#### Fragment 2 &bull; Spoken Words
> On the HR administration side, officers inspect submitted employee requests, review the attached certificate documents, and approve or reject the update with a single click.

#### Fragment 3 &bull; Visual on Slide
Bottom Highlights illuminate: **Smart Ranking &bull; One-Click Assignment &bull; HR Validation**.

#### Fragment 3 &bull; Spoken Words
> This back-office eliminates manual spreadsheet tracking, provides one-click staff assignments, and guarantees that every skill update is verified before entering the corporate database. Now, Mohamed Kamal will walk through the technical challenges and software quality.

---

### SLIDE 13 — TECHNICAL CHALLENGES & HOW WE SOLVED THEM
- **Presenter:** Benkheira Mohamed Kamal (Presenter 2)
- **Time:** 70 seconds
- **Fragments on Slide:** 4 Sequential Problem-to-Solution Cards

#### Fragment 1 &bull; Visual on Slide
Card 1 illuminates: **1. Single Active Staff vs. History** (Solved by PostgreSQL Partial Unique Index).

#### Fragment 1 &bull; Spoken Words
> Thank you, Mohamed Amine. During development, we solved four concrete engineering challenges. First, we needed to permit only one active assignment per project while retaining full historical records. A standard UNIQUE constraint would delete past history, so we implemented a PostgreSQL partial unique index filtering only approved and active records.

#### Fragment 2 &bull; Visual on Slide
Card 2 illuminates: **2. React with Laravel without 2 APIs** (Solved via Inertia.js protocol).

#### Fragment 2 &bull; Spoken Words
> Second, building an independent REST API for React would have doubled our validation rules and data models. We used Inertia.js to connect Laravel controllers directly to React components without maintaining duplicate API endpoints.

#### Fragment 3 &bull; Visual on Slide
Card 3 illuminates: **3. Candidate Scoring Speed** (Solved by SQL eager-loading skills and certifications).

#### Fragment 3 &bull; Spoken Words
> Third, calculating match scores across 300 employee profiles initially caused slow database queries. We solved this by pre-filtering inactive records in SQL and eager-loading skills and certificates in a single efficient query.

#### Fragment 4 &bull; Visual on Slide
Card 4 illuminates: **4. Circular Manager Relationships** (Solved via two-pass migration scripts).

#### Fragment 4 &bull; Spoken Words
> Fourth, circular database references between departments and managers caused migration deadlocks. We resolved this by creating tables first, and attaching relational foreign keys in a dedicated second migration.

---

### SLIDE 14 — TESTING & SOFTWARE QUALITY
- **Presenter:** Benkheira Mohamed Kamal (Presenter 2)
- **Time:** 50 seconds
- **Fragments on Slide:** 2 Sequential Quality Cards

#### Fragment 1 &bull; Visual on Slide
Left Card illuminates: **100% Automated Test Pass Rate** (124 PHPUnit Tests, 492 Assertions, 7 E2E Specs).

#### Fragment 1 &bull; Spoken Words
> To ensure system reliability, we implemented comprehensive automated testing. We developed 124 PHPUnit tests with 492 assertions, validating recommendation math, workflow state transitions, and role-based permissions with a 100% pass rate.

#### Fragment 2 &bull; Visual on Slide
Right Card illuminates: **Automated Verification Output** (Test output terminal screenshot, Laravel Pint, PHPStan Level 5).

#### Fragment 2 &bull; Spoken Words
> Our entire test suite runs in under three seconds with zero failures. We also enforce code cleanliness using Laravel Pint for PSR-12 standard compliance and PHPStan at Level 5 to catch errors before deployment.

---

### SLIDE 15 — CURRENT LIMITS & FUTURE IMPROVEMENTS
- **Presenter:** Benkheira Mohamed Kamal (Presenter 2)
- **Time:** 50 seconds
- **Fragments on Slide:** 2 Sequential Horizon Columns

#### Fragment 1 &bull; Visual on Slide
Left Column illuminates: **Current Boundaries** (Fixed scoring percentages, 30s notification polling, Responsive web layout).

#### Fragment 1 &bull; Spoken Words
> In engineering, evaluating system limits is just as important as showing features. Today, our matching formula uses fixed percentage weights, notification updates refresh every 30 seconds through polling, and mobile access is provided via responsive web browsing rather than a native mobile application.

#### Fragment 2 &bull; Visual on Slide
Right Column illuminates: **Future Improvements** (Machine learning weight tuning, Live WebSockets via Laravel Reverb, Native mobile app).

#### Fragment 2 &bull; Spoken Words
> Looking forward, three key enhancements are planned: training machine learning models on post-project evaluations to dynamically tune matching weights, adding real-time WebSockets via Laravel Reverb for instant push alerts, and developing a dedicated cross-platform mobile app.

---

### SLIDE 16 — CONCLUSION & JURY Q&A
- **Presenter:** Amine & Kamal (Both Presenters)
- **Time:** 40 seconds
- **Fragments on Slide:** Complete Slide (Single Unit)

#### Fragment 1 &bull; Visual on Slide
Closing Slide: Three real organization logos, "Thank You for Your Attention", and Project Achievement Badges.

#### Spoken Words &bull; Brahimi Mohamed Amine
> In conclusion, we designed and implemented a production-ready resource assignment platform tailored to Djezzy\'s real operational needs. We validated our solution through 124 passing automated tests, clear workflows, and strict database integrity rules.

#### Spoken Words &bull; Benkheira Mohamed Kamal
> This graduation project provided us with invaluable practical experience in full-stack development, database architecture, and agile teamwork. We thank Madame President and members of the jury for your kind attention, and we are now ready to answer your questions.

---

# SECTION 4 — 10 ESSENTIAL JURY QUESTIONS & SIMPLE ANSWERS

#### Q1: Why did you choose Laravel and React instead of just Laravel Blade?
- **Simple Answer:** "We used Laravel for the backend because it is secure and handles the database very well. For the employee portal, we chose React because it gives a fast, modern single-page experience without page reloads. With Inertia.js, we connected them easily without writing a separate API."

#### Q2: How does the Recommendation Engine calculate the score?
- **Simple Answer:** "It uses a weighted score: 40% for required skills, 20% for active certifications, 15% for languages, 15% for calendar availability, and 10% for current workload. Employees who are already overloaded or miss a mandatory certificate are excluded."

#### Q3: Why does your report mention 68 tests, but your presentation says 124 tests?
- **Simple Answer:** "The 68 tests in the report were written during our early milestone. As we added the profile request approval and recommendation engine, we wrote 56 more tests to make sure everything works. Today, all 124 tests pass with zero errors."

#### Q4: What is a PostgreSQL Partial Unique Index?
- **Simple Answer:** "It is a rule in the database that says: a project can only have one assignment with status 'approved' or 'active'. But it allows many assignments with status 'completed' or 'rejected'. This guarantees no double-booking while preserving history."

#### Q5: Can an employee see another employee's information in the portal?
- **Simple Answer:** "No. In the portal code, all queries use `auth()->user()->employee`. An employee can only view and edit their own information. They cannot access anyone else's profile."

#### Q6: Why did you use Passkeys?
- **Simple Answer:** "Passkeys let employees log in with their fingerprint or Windows Hello instead of passwords. This is much faster and completely protects against stolen passwords or phishing."

#### Q7: What happens when an employee uploads a fake certificate?
- **Simple Answer:** "The certificate is never published immediately. It is saved in a pending queue. An HR administrator must open the PDF document, verify it, and click approve. If it is invalid, HR can reject it with a note."

#### Q8: What is stored in the Audit Log?
- **Simple Answer:** "The audit log records who made a change, what action they performed, their IP address, the date and time, and the exact data before and after the change."

#### Q9: Why did you not use WebSockets for real-time notifications?
- **Simple Answer:** "For our use case, 30-second polling was simple, reliable, and works easily behind corporate firewalls. We designed the notification service so that WebSockets can be added in the next version without changing the database."

#### Q10: What did you learn most from this project?
- **Simple Answer:** "We learned how to design a real database with 44 tables, how to connect Laravel and React with Inertia, how to write automated tests, and how to build a software solution that solves a real business problem for Djezzy."
