# Implementation Plan: Group Materials into Invoices

## Goal
Transform the material recording process into an "Invoice-based" system where all materials added together act as a single invoice.
1. Make the supplier global for the whole purchase.
2. Record the whole purchase as **one** transaction in the ledger (الرادار) instead of one per material.
3. Allow deleting individual materials from the project view, automatically reversing their financial effects.

## Open Questions
- Do we need to migrate existing `sy2_materials` to create fake invoices for them so the ledger doesn't break, or leave them as they are and only apply invoices to new materials? I plan to leave existing materials as they are and apply the invoice logic to new purchases to avoid corrupting historical debts and transactions. Is this acceptable?

## Proposed Changes

### Database & Models
- **[NEW] Migration**: `create_sy2_material_invoices_table`
  - Columns: `id`, `project_id`, `supplier_id`, `date`, `account_id` (wallet used), `total_amount`, `paid_amount`, `notes`.
- **[NEW] Migration**: `add_invoice_id_to_sy2_materials_table`
  - Column: `invoice_id` (nullable foreign key to `sy2_material_invoices`).
- **[NEW] Model**: `MaterialInvoice` with relationships to `Project`, `Supplier`, `Materials`, `Transaction`, and `SupplierDebt`.
- **[MODIFY] `Material` Model**: Add `invoice()` relation.

### Observers & Business Logic
- **[MODIFY] `MaterialObserver`**: Remove the logic that creates a `Transaction` and `SupplierDebt` for *each* material. Instead, materials will just rely on their parent invoice.
- **[NEW] `MaterialInvoiceObserver`**:
  - `created`: Create a single `Transaction` for the invoice. Create a single `SupplierDebt` if there is a remaining balance.
  - `updated`: Sync the `Transaction` and `SupplierDebt` when invoice totals change (e.g. after a material is deleted).
  - `deleted`: Delete the invoice's `Transaction` and `SupplierDebt`.
- **[MODIFY] `Material` Deletion Logic**: When a material is deleted, it deducts its gross cost from its parent `MaterialInvoice`, which triggers `MaterialInvoiceObserver` to adjust the `Transaction` and `SupplierDebt`.

### Controllers
- **[MODIFY] `MaterialController@store`**:
  - Accept a global `supplier_id` from the form.
  - Calculate total invoice gross.
  - Create the `MaterialInvoice` first.
  - Loop through all bands and items to create the `Material` rows linked to this `invoice_id`.

### Views
- **[MODIFY] `resources/views/materials/create.blade.php`**:
  - Add a global "Supplier" dropdown at the top.
  - Remove the "Supplier" dropdown from the individual material item bubbles.
- **[MODIFY] `resources/views/projects/show.blade.php`**:
  - In the `materials` tab, add a "Delete" button for each material row.
  - Deleting will call a route to delete the material and reverse its financial impact.
- **[MODIFY] `resources/views/radar/index.blade.php`** & `AuditLog`: Add `material_invoice` type to look nice in the radar, replacing the individual `material` types.

## Verification Plan
1. Record a new material purchase with multiple items and verify that only **one** transaction appears in the Radar.
2. Verify the wallet balance decreases correctly.
3. Verify that Supplier Debt is created correctly for the invoice as a whole.
4. Delete one of the material items and verify that the invoice total, wallet transaction, and supplier debt all decrease automatically.
