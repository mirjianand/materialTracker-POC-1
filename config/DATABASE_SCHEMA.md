# Database Schema Proposal

Here is a proposed database schema based on the SRS.

**1. `users`**
This table will store user information. While authentication is done via LDAP, this table will hold application-specific user data and roles.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `emp_id` | VARCHAR(10) | Employee ID (e.g., AAxxxx, YYxxxx) |
| `name` | VARCHAR(255) | User's full name |
| `email` | VARCHAR(255) | User's email |
| `role` | ENUM('LogisticsManager', 'CommodityManager', 'User') | User role |
| `start_date` | DATE | |
| `end_date` | DATE | |
| `designation` | VARCHAR(255) | |
| `is_active` | BOOLEAN | |

**2. `item_master`**
This table will store the master list of all items.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key (Item_ID) |
| `item_code`| VARCHAR(6) | Sequential 6-digit code for physical item |
| `description` | TEXT | Item description |
| `category_id` | INT (FK) | Foreign key to `item_categories` |
| `item_type_id` | INT (FK) | Foreign key to `item_types` |
| `material_type_id` | INT (FK) | Foreign key to `material_types` table. |
| `commodity_manager_override_id` | INT (FK) | Nullable FK to `users`. Overrides the default manager set by material type. |
| `quantity_type` | ENUM('Number', 'Batch') | |
| `is_active` | BOOLEAN | |

**3. `item_categories`**
To store item categories.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `name` | VARCHAR(255) | Category name |

**4. `item_types`**
To store item types.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `name` | VARCHAR(255) | Type name |

**5. `material_types`**
To store item material types, e.g. Consumable, Non-Consumable.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `name` | VARCHAR(255) | Material type name |

**6. `commodity_manager_material_types`**
Maps which Commodity Manager is responsible for which Material Type by default.

| Column | Type | Description |
|---|---|---|
| `user_id` | INT (FK to `users`) | The ID of the Commodity Manager. |
| `material_type_id` | INT (FK to `material_types`)| The ID of the Material Type. |

**7. `purchase_orders`**
Stores Purchase Requisition/Purchase Order information.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `po_number` | VARCHAR(255) | PO Number |
| `pr_number` | VARCHAR(255) | PR Number |
| `created_by` | INT (FK) | User ID of Logistics-Manager |
| `created_at` | TIMESTAMP | |

**8. `inventory`**
This table tracks the lifecycle of each item instance.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `item_master_id` | INT (FK) | Foreign key to `item_master` |
| `po_id` | INT (FK) | Foreign key to `purchase_orders` |
| `serial_number` | VARCHAR(255) | |
| `qa_cert_no` | VARCHAR(255) | |
| `status` | ENUM('In-QA', 'Accepted', 'Rejected', 'To-Rework', 'Lost', 'Lost-but-found', 'Transferred', 'Surrendered') | Current status |
| `current_owner_id` | INT (FK) | User ID of the current owner |
| `received_at` | TIMESTAMP | When item entered the system |
| `acknowledged_at`| TIMESTAMP | When requester accepted the item |
| `notes`| TEXT | |

**9. `item_transactions`**
This table will log all movements and status changes of an item.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `inventory_id` | INT (FK) | Foreign key to `inventory` |
| `from_user_id` | INT (FK) | User ID of the transferor |
| `to_user_id` | INT (FK) | User ID of the transferee |
| `transaction_type` | ENUM('Inward', 'Transfer', 'Rework', 'Surrender', 'Reject', 'Lost', 'Found', 'Transfer-Lost') | |
| `quantity` | INT | |
| `remarks` | TEXT | |
| `transaction_date`| TIMESTAMP | |
| `file_attachment_id` | INT (FK) | Foreign key to `file_attachments` |

**10. `file_attachments`**
This table will store information about uploaded files.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `file_name` | VARCHAR(255) | |
| `file_path` | VARCHAR(255) | |
| `uploaded_by` | INT (FK) | User ID of the uploader |
| `uploaded_at` | TIMESTAMP | |

**11. `email_logs`**
This table will log all sent email notifications for auditing and debugging.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `recipient_email` | VARCHAR(255) | Email address of the recipient |
| `subject` | VARCHAR(255) | Subject of the email |
| `body` | TEXT | Body of the email |
| `sent_at` | TIMESTAMP | When the email was sent |
| `status` | ENUM('sent', 'failed') | Status of the email sending |
| `error_message` | TEXT | Error message if sending failed |

**12. `approval_requests`**
Tracks the approval workflow for 'Transfer Lost Qty' requests.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `item_transaction_id` | INT (FK) | The 'Transfer-Lost' transaction that triggered this. |
| `requester_id` | INT (FK) | The user who raised the request. |
| `approver_id` | INT (FK) | The assigned Commodity Manager. |
| `status` | ENUM('Pending', 'Approved', 'Rejected') | |
| `decision` | ENUM('WriteOff', 'DeductFromSettlement') | The approver's final choice. |
| `approver_remarks` | TEXT | |
| `created_at` | TIMESTAMP | When the request was raised. |
| `decided_at` | TIMESTAMP | When the approver made the decision. |

**13. `audit_log`**
This table provides a detailed log of all key user actions for reporting and auditing.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `user_id` | INT (FK) | The user who performed the action. Can be null for system actions. |
| `action_type` | VARCHAR(255) | E.g., 'user_login', 'report_viewed', 'item_accepted', 'item_transferred' |
| `target_id` | INT | ID of the object being acted upon (e.g., inventory_id, user_id). |
| `target_type` | VARCHAR(255) | The type of object (e.g., 'Inventory', 'User', 'Report'). |
| `details` | TEXT | Additional details about the action, potentially as JSON. |
| `created_at` | TIMESTAMP | Timestamp of the action. |

**14. `auth_settings`**
Stores settings for authentication, allowing an Admin to configure LDAP/AD connections.

| Column | Type | Description |
|---|---|---|
| `id` | INT (PK) | Primary Key |
| `name` | VARCHAR(255) (UNIQUE) | Setting name (e.g., 'auth_type', 'ldap_host', 'ldap_port', 'ldap_base_dn') |
| `value` | VARCHAR(255) | Setting value |
| `updated_by`| INT (FK to `users`)| Last user to update this setting. |
| `updated_at`| TIMESTAMP | |
