# Procurement and Finance - Budget Request & Approval Workflow

## System Overview

Your system implements a **multi-level approval workflow** for budgets and procurement requests, with clear separation between departments requesting budgets and the finance team approving them. The workflow also integrates procurement (purchase requests) with approved budgets.

---

## 1. FINANCE MODULE - BUDGET REQUEST & APPROVAL

### 1.1 Budget Lifecycle

**Status Flow:**
```
draft → submitted → [approval_logs] → approved
                  ↘ [approval_logs] → rejected
```

#### Key Tables:
- **`department_budgets`** - Main budget records
- **`approval_logs`** - Tracks each approval decision at each level
- **`approval_workflows`** - Defines approval chain (roles & levels)

### 1.2 Budget Creation (Draft → Submitted)

**Who Can Create:** Any authenticated user
**API Endpoint:** `POST /api/v1/finance/budgets`

**Required Fields:**
- `request_title` - What the budget is for
- `fiscal_month` - YYYY-MM format
- `planned_amount` - Total budget amount
- `department` - Department requesting the budget

**Optional Fields:**
- `description` - Detailed explanation
- `event_id` - Link to specific event
- `category_id` - Finance category (e.g., SALARY, UTILITIES)
- `notes` - Additional notes

**Process:**
1. User submits budget request
2. Status immediately set to `"submitted"`
3. Audit log created
4. Budget awaits approval chain

```php
// From ApiController::createBudget()
$stmt = $this->pdo->prepare(
    "INSERT INTO department_budgets 
     (department, category_id, fiscal_month, planned_amount, status, submitted_by, notes)
     VALUES (:dept, :cat, :month, :amt, 'submitted', :uid, :notes)"
);
```

---

### 1.3 Multi-Level Budget Approval

**Approval Chain (Default):**
1. **Level 1:** Accountant reviews budget
2. **Level 2:** Approver (Pastor/Manager) makes final decision

**Database Structure:**
```
approval_workflows table:
- workflow_type: 'budget'
- level_no: 1, 2, 3... (sequential levels)
- role_id: references roles table
- is_active: 1
```

#### Approval Logic Flow:

```
GET Next Approval Level
    ↓
Check User's Role matches required role for this level
    ↓
If NOT matching role → Error: "Level X requires Role Y"
    ↓
If Admin → Allow at any level
    ↓
Process Decision:
  - If APPROVED at intermediate level → Status stays "submitted"
  - If APPROVED at FINAL level → Status → "approved"
  - If REJECTED at any level → Status → "rejected"
    ↓
Log action in approval_logs
Audit log entry
Response to user
```

**API Endpoint:** `PUT /api/v1/finance/budgets/{id}/approve`

**Request Payload:**
```json
{
  "decision": "approved",  // or "rejected"
  "notes": "Looks good"     // optional, required for rejection
}
```

**Approval Decision Function:**
```php
// From ApiController::approveBudget()
public function approveBudget(int $id, array $input): void
{
    $decision = $input['decision']; // 'approved' or 'rejected'
    
    // 1. Check user has finance.approve permission
    if (!Auth::can('finance.approve')) {
        Response::json(['forbidden'], 403);
    }
    
    // 2. Get next approval level required
    $nextLevel = $this->getNextApprovalLevel('budget', $id);
    
    // 3. Enforce role check
    if ($nextLevel && user's role_id !== $nextLevel['role_id']) {
        Response::json(['need role X at level Y'], 403);
    }
    
    // 4. Update budget status
    if ($decision === 'approved' && $isLastLevel) {
        // Final approval
        UPDATE department_budgets 
        SET status = 'approved', approved_by = :uid, approved_at = NOW()
        WHERE id = :id;
    } elseif ($decision === 'rejected') {
        // Rejected at any level stops approval chain
        UPDATE department_budgets 
        SET status = 'rejected', approved_by = :uid, approved_at = NOW()
        WHERE id = :id;
    } else {
        // Intermediate approval - status stays 'submitted'
    }
    
    // 5. Log the action
    INSERT INTO approval_logs
    (entity_type, entity_id, level_no, action, actor_id, notes)
    VALUES ('budget', :id, :level, :decision, :uid, :notes);
}
```

#### Key Function: `getNextApprovalLevel()`

```php
private function getNextApprovalLevel(string $type, int $entityId): ?array
{
    // Get approval chain for this type (budget/procurement/finance_entry)
    $workflows = $this->pdo->prepare(
        "SELECT * FROM approval_workflows 
         WHERE workflow_type = :type 
         ORDER BY level_no ASC"
    )->fetchAll();
    
    // Get completed approvals for this entity
    $completed = $this->pdo->prepare(
        "SELECT level_no FROM approval_logs 
         WHERE entity_type = :type AND entity_id = :id 
         AND action IN ('approved', 'rejected')"
    )->fetchAll();
    
    // Find first incomplete level
    $completedLevels = array_map(fn($c) => $c['level_no'], $completed);
    
    foreach ($workflows as $level) {
        if (!in_array($level['level_no'], $completedLevels)) {
            return [
                'level_no' => $level['level_no'],
                'role_id' => $level['role_id'],
                'is_final' => ($level['level_no'] === count($workflows)),
                'total' => count($workflows),
                'done' => false
            ];
        }
    }
    
    // All levels approved
    return ['done' => true];
}
```

---

### 1.4 Budget Approval UI Flow (Finance Module)

**Finance → Approvals Tab** shows:
1. **Pending Budget Requests** (status = "submitted")
2. **Pending Finance Entries** (approval_status = "pending")

**What Approvers See:**
- Department name
- Fiscal month
- Planned amount
- Submitted by (user name)
- **Action Buttons:** Approve / Reject

**Rejection Process:**
1. Click "Reject"
2. Prompted for rejection reason
3. Status → "rejected"
4. Department can edit and resubmit

---

## 2. BUDGET TO PROCUREMENT LINK

### 2.1 Active Budgets (Approved Only)

**Only budgets with status = "approved" can be used for procurement requests.**

```php
// From ApiController::listActiveBudgetsForProcurement()
SELECT db.* FROM department_budgets db
WHERE db.status IN ("approved", "expenses_added")
AND db.planned_amount > COALESCE(db.reserved_amount, 0) + COALESCE(db.actual_amount, 0)
```

**Budget Availability Calculation:**
```
Available Budget = Planned Amount - Reserved Amount - Actual Amount

Available = 10,000 - 2,000 (reserved) - 1,000 (actual) = 7,000
```

---

### 2.2 Creating Procurement Requests from Approved Budgets

**Who Can Create:** Users with `procurement.create` permission
**API Endpoint:** `POST /api/v1/procurement/requests`

**Requirements:**
- Must select an **approved budget**
- Budget must have available balance
- Must specify items with quantities and costs
- Total cost must fit in available budget

**Request Payload:**
```json
{
  "budget_id": 123,
  "purpose": "Office supplies for Q2",
  "vendor_name": "Supplier ABC",
  "items": [
    {
      "item_name": "Paper (A4)",
      "quantity": 5,
      "estimated_unit_cost": 50,
      "notes": "White paper, 500 sheets per ream"
    },
    {
      "item_name": "Pens (blue)",
      "quantity": 10,
      "estimated_unit_cost": 10,
      "notes": "Ballpoint pens"
    }
  ]
}
```

**Budget Validation:**
```php
// Calculate total cost from items
$totalCost = 0;
foreach ($items as $item) {
    $totalCost += $item['quantity'] * $item['estimated_unit_cost'];
}

// Check available budget
$planned  = (float) $budget['planned_amount'];
$spent    = (float) $budget['actual_amount'];
$reserved = (float) $budget['reserved_amount'];
$available = $planned - $spent - $reserved;

if ($totalCost > $available) {
    Response::json(['Insufficient budget. Available: ' . $available], 422);
}
```

---

## 3. PROCUREMENT REQUEST & APPROVAL

### 3.1 Procurement Request Lifecycle

**Status Flow:**
```
submitted → [approval_logs] → approved → purchased → completed
         ↘ [approval_logs] → rejected
```

#### Key Tables:
- **`purchase_requests`** - Main procurement records
- **`purchase_request_items`** - Line items (what's being purchased)
- **`approval_logs`** - Approval tracking for procurement

### 3.2 Creating Purchase Request

**Process:**
1. User creates request with approved budget
2. Status set to `"submitted"`
3. Enters approval chain (default: Level 1 = Approver)
4. Awaits procurement approval

**API Call:**
```php
// From ApiController::createPurchaseRequest()
INSERT INTO purchase_requests 
(request_no, requested_by, department, purpose, estimated_cost, 
 event_id, budget_id, status, requested_date)
VALUES (:rno, :uid, :dept, :purpose, :cost, :eid, :bid, 'submitted', CURDATE());

// Insert line items
foreach ($items as $item) {
    INSERT INTO purchase_request_items 
    (purchase_request_id, item_name, quantity, estimated_unit_cost, notes)
    VALUES (:prid, :name, :qty, :cost, :notes);
}

// Log approval tracking
INSERT INTO approval_logs
(entity_type, entity_id, action, actor_id)
VALUES ('procurement', :prid, 'submitted', :uid);
```

### 3.3 Procurement Approval

**Who Can Approve:** Users with `procurement.approve` permission
**Default Approver:** "Approver" role (e.g., Pastor/Manager)

**API Endpoint:** `POST /api/v1/procurement/requests/{id}/approve`

**Request Payload:**
```json
{
  "decision": "approved",    // or "rejected"
  "notes": "Approved for purchase"  // optional
}
```

**Approval Process:**

```php
public function approvePurchaseRequest(int $id, array $input): void
{
    $decision = $input['decision']; // 'approved' or 'rejected'
    
    // 1. Check permission
    if (!Auth::can('procurement.approve')) {
        Response::json(['forbidden'], 403);
    }
    
    // 2. Get next approval level
    $nextLevel = $this->getNextApprovalLevel('procurement', $id);
    
    // 3. Check user role
    if ($nextLevel && user_role_id !== $nextLevel['role_id']) {
        Response::json(['need role X'], 403);
    }
    
    // 4. Get PR details
    $pr = SELECT * FROM purchase_requests WHERE id = :id;
    $budgetId = $pr['budget_id'];
    $totalCost = $pr['estimated_cost'];
    
    // 5. On final approval, reserve budget
    if ($decision === 'approved' && $isLastLevel) {
        UPDATE department_budgets 
        SET reserved_amount = reserved_amount + :cost
        WHERE id = :bid;
        
        UPDATE purchase_requests 
        SET status = 'approved', approved_by = :uid, approved_at = NOW()
        WHERE id = :id;
    } elseif ($decision === 'rejected') {
        UPDATE purchase_requests 
        SET status = 'rejected', rejection_reason = :reason
        WHERE id = :id;
    }
    
    // 6. Log approval
    INSERT INTO approval_logs
    (entity_type, entity_id, level_no, action, actor_id)
    VALUES ('procurement', :id, :level, :decision, :uid);
}
```

**What Happens:**
- **Approved:** 
  - PR status → "approved"
  - Budget `reserved_amount` increases by PR total
  - Can now be converted to purchase order
- **Rejected:** 
  - PR status → "rejected"
  - Budget NOT reserved
  - Can be edited and resubmitted (if allowed)

---

### 3.4 Converting Approved PR to Purchase Order & Marking Complete

**Stage 1: Mark as Purchased**
- PR status → "purchased"

**Stage 2: Complete Purchase (Procurement Officer Action)**
- PR status → "completed"
- Creates finance entry (expense)
- Deducts from budget `actual_amount`

**API Endpoint:** `POST /api/v1/procurement/requests/{id}/complete`

---

## 4. FINANCE ENTRIES APPROVAL

### 4.1 Finance Entry Approval (Optional Second Workflow)

Finance entries can have their own approval chain separate from budgets/procurement.

**Default Chain:** Level 1 = Accountant (for review)

**When Used:**
- Manual expense entries
- Entries auto-created from approved PRs
- Entries from events

**Status:**
```
pending → [approval_logs] → approved
       ↘ [approval_logs] → rejected
```

---

## 5. APPROVAL WORKFLOWS TABLE STRUCTURE

```sql
CREATE TABLE approval_workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_type VARCHAR(50),           -- 'budget', 'procurement', 'finance_entry'
    level_no TINYINT UNSIGNED,           -- 1, 2, 3...
    role_id BIGINT UNSIGNED,             -- references roles table
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY uq_workflow_level (workflow_type, level_no)
);

-- Default setup:
INSERT INTO approval_workflows VALUES
(NULL, 'budget', 1, 3, 1),              -- Accountant (role 3)
(NULL, 'budget', 2, 4, 1),              -- Approver (role 4)
(NULL, 'procurement', 1, 4, 1),         -- Approver
(NULL, 'finance_entry', 1, 3, 1);       -- Accountant
```

---

## 6. APPROVAL LOGS TABLE STRUCTURE

```sql
CREATE TABLE approval_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50),             -- 'budget', 'procurement', 'finance_entry'
    entity_id BIGINT UNSIGNED,           -- ID of the budget/PR/entry
    level_no TINYINT UNSIGNED,           -- Which approval level (1, 2, 3...)
    action ENUM('submitted', 'approved', 'rejected', 'returned'),
    actor_id BIGINT UNSIGNED,            -- User who made decision
    notes TEXT,                          -- Approval notes/rejection reason
    acted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_approval_entity (entity_type, entity_id)
);
```

**Example Log Entry:**
```
entity_type: 'budget'
entity_id: 42
level_no: 1
action: 'approved'
actor_id: 5 (John the Accountant)
notes: 'Budget looks reasonable'
acted_at: 2026-03-15 10:30:00
```

---

## 7. KEY PERMISSIONS

```
Permissions defined in database:

-- Finance Module
finance.budget.create      - Create budget requests
finance.budget.approve     - Approve/reject budget requests
finance.entries.create     - Create finance entries
finance.entries.approve    - Approve finance entries
finance.reports.view       - View financial reports

-- Procurement Module
procurement.request.create   - Create purchase requests
procurement.request.approve  - Approve procurement requests
procurement.po.create        - Create purchase orders
procurement.po.complete      - Mark purchases as completed

-- Roles & Assignments
Admin                 → ALL permissions
Accountant            → Create/approve budgets & entries, view reports
Approver (Pastor)     → Approve budgets (level 2), approve PRs
Procurement Officer   → Create POs, mark complete
Event Organizer       → Request budgets, create events
```

---

## 8. WORKFLOW SEQUENCE DIAGRAM

### Budget Approval Flow
```
Department Secretary
    ↓
[1] Creates Budget Request
    ↓ (status: submitted)
Accountant (Level 1)
    ↓
[2] Reviews Budget
    ├─→ APPROVED (status stays: submitted, moves to level 2)
    └─→ REJECTED (status: rejected, done)
    ↓
Approver/Pastor (Level 2 - Final)
    ├─→ APPROVED (status: approved, DONE ✓)
    └─→ REJECTED (status: rejected)
    ↓
[3] Budget Now Available for Procurement
```

### Procurement from Approved Budget Flow
```
Department Secretary
    ↓
[1] Creates Procurement Request
    (selects approved budget)
    ↓ (status: submitted, budget reserved amount increases)
Approver (Level 1 - Final)
    ├─→ APPROVED (status: approved, DONE ✓)
    │    ↓
    │  [2] Can be converted to PO
    │    ↓
    │  Procurement Officer
    │    ├─→ Mark Purchased (status: purchased)
    │    └─→ Mark Completed (status: completed)
    │         → Creates Finance Entry
    │         → Deducts from budget.actual_amount
    │
    └─→ REJECTED (status: rejected)
```

---

## 9. BUDGET TRACKING - Reserved vs Actual

```
Budget State:
┌────────────────────────────────────┐
│ Planned Amount: 10,000 TZS        │
├────────────────────────────────────┤
│ Reserved Amount: 2,000 (PR #1)    │ ← PR approved but not yet purchased
│ Actual Amount:   1,500 (Spent)    │ ← Finance entries (approved)
├────────────────────────────────────┤
│ Available:       6,500             │ ← Can still spend this much
└────────────────────────────────────┘

Calculation: 10,000 - 2,000 - 1,500 = 6,500
```

---

## 10. UI COMPONENTS

### Finance Module - Budget Requests Tab
```
Displays: submitted + draft + rejected budgets
Actions:
- View detail
- Approve button (if user has permission & right role)
- Reject button (if user has permission & right role)
```

### Finance Module - Approvals Tab
```
Displays: Pending Budget Requests + Pending Finance Entries
Shows:
- Department/Description
- Amount
- Submitted by
- Status badge
- Approval buttons
- Notes field (for rejection reason)
```

### Procurement Module
```
All Requests Tab:
- Shows: All PRs (submitted, approved, rejected, completed)
- Can create new PR from approved budget only

Approved Orders Tab:
- Shows: PRs with status = approved
- Can mark as purchased/completed

Completed Tab:
- Shows: Finished PRs (status = completed)
```

---

## 11. APPROVAL WORKFLOW CONFIGURATION

**Settings → Approval Settings Tab**

Admins can:
1. View current workflow chains
2. Add/modify approval levels
3. Assign roles to levels
4. Reorder approval chain

Example Configuration UI:
```
Workflow Type: Budget Approval
└─ Level 1: Accountant (Role #3)
└─ Level 2: Approver (Role #4) [FINAL]

Workflow Type: Procurement Approval
└─ Level 1: Approver (Role #4) [FINAL]

Workflow Type: Finance Entry Approval
└─ Level 1: Accountant (Role #3) [FINAL]
```

---

## 12. COMMON SCENARIOS

### Scenario 1: Budget Exceeds Available
**User tries to create PR for 8,000 TZS but budget only has 6,500 available**
```
Response: 422 Unprocessable Entity
Message: "Insufficient budget. Available: 6500.00, Requested: 8000.00"
```

### Scenario 2: Budget Rejected
**Accountant rejects budget at Level 1**
```
Budget Status: rejected
Department can:
- Edit the budget
- Resubmit (goes back to submitted state)
- Create new budget
```

### Scenario 3: Partial PR Approval
**First approver approves PR, but second level rejects**
```
- PR status stays "submitted" until final approval
- Final rejection → status: rejected
- Department must resubmit if changes made
```

### Scenario 4: Multiple POs from Same Budget
**Budget has 10,000, can create multiple PRs until reserved reaches 10,000**
```
Budget: 10,000
PR #1: 3,000 (approved) → Reserved: 3,000
PR #2: 4,000 (approved) → Reserved: 7,000
PR #3: 2,000 (approved) → Reserved: 9,000
PR #4: 2,000 (submitted) → FAIL: Would need reserved 11,000

Available = 10,000 - 9,000 = 1,000
```

---

## 13. KEY DATABASE QUERIES

### Get Budget with Approval Status
```sql
SELECT db.*, 
       al.action AS last_action,
       al.level_no AS last_level,
       u.full_name AS last_actor
FROM department_budgets db
LEFT JOIN approval_logs al ON al.entity_type = 'budget' 
                           AND al.entity_id = db.id
                           AND al.acted_at = (
                               SELECT MAX(acted_at) 
                               FROM approval_logs 
                               WHERE entity_type = 'budget' 
                               AND entity_id = db.id
                           )
LEFT JOIN users u ON u.id = al.actor_id
WHERE db.id = 42;
```

### Get Next Required Approver
```sql
SELECT aw.level_no, aw.role_id, r.name AS role_name
FROM approval_workflows aw
LEFT JOIN roles r ON r.id = aw.role_id
WHERE aw.workflow_type = 'budget'
  AND aw.level_no NOT IN (
      SELECT level_no FROM approval_logs 
      WHERE entity_type = 'budget' AND entity_id = 42
  )
ORDER BY aw.level_no ASC
LIMIT 1;
```

### Get Complete Approval Trail
```sql
SELECT al.level_no, al.action, al.acted_at,
       u.full_name AS actor,
       al.notes
FROM approval_logs al
LEFT JOIN users u ON u.id = al.actor_id
WHERE al.entity_type = 'budget' AND al.entity_id = 42
ORDER BY al.level_no ASC;
```

---

## 14. FILE LOCATIONS

- **API Logic:** [app/controllers/ApiController.php](app/controllers/ApiController.php#L3843-4650)
- **Finance UI:** [app/views/pages/finance.php](app/views/pages/finance.php)
- **Procurement UI:** [app/views/pages/procurement.php](app/views/pages/procurement.php)
- **Approvals UI:** [app/views/pages/finance/finance_approvals.php](app/views/pages/finance/finance_approvals.php)
- **Database Migrations:** [database/migrations/2026_04_02_001_finance_procurement_integration.sql](database/migrations/2026_04_02_001_finance_procurement_integration.sql)

