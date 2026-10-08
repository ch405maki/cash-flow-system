# API Reference — Releasing

> All API endpoints involved in the releasing process. The **Request Release** endpoint captures the receiver signature; the **Request-To-Order Release** endpoint does not.

---

## Authentication & Prefix

All endpoints are:
- Mounted under the `/api` prefix
- Protected by **`auth:sanctum`** middleware
- Sent with `X-Requested-With: XMLHttpRequest` and `Accept: application/json` (via `resources/js/services/api.ts`)

---

## 1. Request Release (with Signature)

### GET `/api/requests/{request}/release-data`
**Route:** `routes/api/requests.php:14` → `Api\RequestController::releaseData` (line 256)

Loads the data needed to render the release page.

**Response includes:**
- The request with its details (`quantity != 0`)
- User + department
- `inventoryStatus` per line (via `InventoryApiService::checkProductQuantity`)
- `current_user` — `{ id, name }` used as default signer name

---

### POST `/api/requests/{request}/release`
**Route:** `routes/api/requests.php:10` → `Api\RequestController::releaseItems` (line 488)

Performs the release **and** stores the receiver signature.

**Request body:**
```json
{
  "items": [
    { "request_detail_id": 10, "quantity": 2 }
  ],
  "notes": "Items released from request",
  "signature": {
    "image": "<base64-png>",
    "signer_id": 5,
    "signer_name": "Juan Dela Cruz",
    "signed_at": "2026-08-04T10:30:00.000Z"
  }
}
```

**Validation** — `app/Http/Requests/ReleaseItemsRequest.php`:
| Field | Rules |
|-------|-------|
| `items` | required, array |
| `notes` | nullable, string |
| `signature` | nullable, array |
| `signature.image` | nullable, string |
| `signature.signer_id` | nullable, integer |
| `signature.signer_name` | nullable, string |
| `signature.signed_at` | nullable, date |

**Server-side behavior (`releaseItems`):**
1. Creates a `Release` with `signature_image`, `signed_by`, `signed_at`.
2. Syncs inventory → `Out` transaction per item with an `item_id`.
3. Creates `ReleaseDetail` rows.
4. Increments `request_detail.released_quantity`.
5. Sets `tracking_status` = `completed` / `partial`.
6. Calls `updateRequestStatus()` (line 686) → request `status` = `released` / `partially_released`.
7. Logs "Released Items" via `spatie/laravel-activitylog`.

---

### PATCH `/api/requests/{request}/status`
Used to transition request status (supports `released`). Requires a password.

---

## 2. Request-To-Order Release (no signature)

### GET `/api/request-to-order/{order}/release-data`
**Route:** `routes/api/request-to-order.php:15` → `Api\RequestToOrderController::releaseData` (line 485)
- Uses `withSum('releases','quantity_released')` to compute remaining quantities.

### POST `/api/request-to-order/{order}/release`
**Route:** `routes/api/request-to-order.php:16` → `Api\RequestToOrderController::release` (line 502)
- Creates `RequestToOrderRelease` rows.
- Sets `released_by` (the releasing user).
- **No signature payload.**

---

## 3. Supporting Services (frontend)

### `resources/js/services/requestService.ts`
```typescript
interface SignatureData {
    image: string;
    signer_id: number;
    signer_name: string;
    signed_at: string;
}

release: async (id, payload: {
    items: ReleaseItem[];           // [{ request_detail_id, quantity }]
    notes: string;
    user_id?: number;
    signature?: SignatureData;
}) => api.post(`/api/requests/${id}/release`, payload)

releaseData: async (id) => api.get(`/api/requests/${id}/release-data`)
```

### `resources/js/services/requestToOrderService.ts`
```typescript
release: async (id, payload: any) => api.post(`/api/request-to-order/${id}/release`, payload)
releaseData: async (id) => api.get(`/api/request-to-order/${id}/release-data`)
```

---

## Web Pages (Inertia render routes — not data APIs)

| Route | Controller | Renders |
|-------|-----------|---------|
| `GET /requests/{request}/release` | `Web\RequestController::release` (line 40) | `Request/Release/Index.vue` |
| `GET /request-to-order/{order}/release` | `Web\RequestToOrderController::releaseCreate` (line 39) | `RequestToOrder/Release/Create.vue` |

## Report Route

| Route | Controller | Renders |
|-------|-----------|---------|
| `GET /reports/request-released` | `Report\RequestReportController::releasedItems` (line 13) | `Reports/Requests/ReleasedItems.vue` |

---

> [!info] Related
> - [[End-to-End-Flow|End-to-End Flow]]
> - [[Data-Model|Data Model]]
> - [[Frontend-Components|Frontend Components]]