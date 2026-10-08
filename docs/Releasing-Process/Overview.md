# Releasing Process Overview

> The **releasing process** is the workflow through which requested stock items are physically handed over to a receiver. This vault documents both release flows and, critically, how the **receiver's signature** is captured, stored, and rendered.

## Two Release Flows

| Flow | What it releases | Signature captured? | Entry point |
|------|------------------|---------------------|-------------|
| **Request Release** | Stock items from a `Request` (spare parts / stock issuance) | ✅ **Yes** — receiver signature | `/requests/{request}/release` |
| **Request-To-Order Release** | Purchased items from a `RequestToOrder` | ❌ No | `/request-to-order/{order}/release` |

> [!important]
> Only the **Request Release** flow captures the receiver's signature. The Request-To-Order flow simply records who released the items (`released_by`).

## Request Release — Core Flow (with Signature)

```mermaid
flowchart LR
    A[Open /requests/{id}/release] --> B[Load release-data API]
    B --> C[Load signotec pad library]
    C --> D[Select items + quantities]
    D --> E[Open SignatureDialog]
    E --> F{Pad available?}
    F -->|Yes| G[signotec STPad signature]
    F -->|No| H[On-screen canvas draw]
    G --> I[POST /api/requests/{id}/release]
    H --> I
    I --> J[Create Release + signature]
    J --> K[Sync inventory]
    K --> L[Update tracking status]
    L --> M[Print STOCK ISSUANCE FORM]
```

## What Happens on Release (server side)

1. **Validate** the payload via `ReleaseItemsRequest` (items + optional signature).
2. **Create `Release`** record — stores `signature_image` (base64 PNG), `signed_by`, `signed_at`.
3. **Sync inventory** — creates an `Out` transaction for each item with an `item_id`.
4. **Create `ReleaseDetail`** rows — increments `request_detail.released_quantity`.
5. **Update tracking** — each `RequestDetail` gets `tracking_status = completed | partial`.
6. **Update request status** — `released` or `partially_released`.
7. **Log activity** — via `spatie/laravel-activitylog` ("Released Items").

## Key Definitions

- **Receiver** — the person physically receiving the items; their signature is captured on the signotec pad (or on-screen fallback).
- **Released Quantity** — per-line quantity actually handed out; can be partial.
- **Signature Image** — raw base64 PNG string stored in `releases.signature_image`, re-rendered in print as `data:image/png;base64,...`.

---

## Vault Contents

- 🔄 [[End-to-End-Flow|End-to-End Flow]]
- ✍️ [[Receiver-Signature|Receiver Signature Capture]]
- 🏭 [[Inventory-Integration|Inventory Integration & Auto-Deduction]]
- 📦 [[Packages-Used|Packages Used]]
- 🗄️ [[Data-Model|Data Model]]
- 🔌 [[API-Reference|API Reference]]
- 🎨 [[Frontend-Components|Frontend Components]]

> [!tip] Next
> Continue to [[End-to-End-Flow|End-to-End Flow]] for the step-by-step walkthrough.