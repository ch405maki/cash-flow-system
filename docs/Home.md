---
title: CashFlow Documentation Vault
aliases:
  - Home
  - Vault Home
cssclasses:
  - vault
---

# CashFlow Documentation Vault

> Welcome to the internal documentation vault for the **CashFlow** system — a Laravel + Inertia + Vue.js requisition, purchasing, release, and voucher management application.

## Quick Navigation

### Releasing Process
- 🌿 [[Releasing-Process/Overview|Releasing Process Overview]]
- 🔄 [[Releasing-Process/End-to-End-Flow|End-to-End Flow]]
- ✍️ [[Releasing-Process/Receiver-Signature|Receiver Signature Capture]]
- 🏭 [[Releasing-Process/Inventory-Integration|Inventory Integration & Auto-Deduction]]
- 📦 [[Releasing-Process/Packages-Used|Packages Used]]
- 🗄️ [[Releasing-Process/Data-Model|Data Model]]
- 🔌 [[Releasing-Process/API-Reference|API Reference]]
- 🎨 [[Releasing-Process/Frontend-Components|Frontend Components]]

### Guides
- [[Releasing-Process/Receiver-Signature|How the receiver signature works]]
- [[Releasing-Process/Packages-Used|Signature & release packages]]

## About This Vault

This vault documents the **releasing process** with a deep focus on the **signature of the receiver**. Each note uses `[[wikilinks]]` for cross-navigation — explore them in **Graph View**.

### Tech Stack Snapshot
| Layer | Technology |
|-------|-----------|
| Backend | PHP / Laravel 11, Sanctum (auth) |
| Frontend | Vue 3, Inertia.js, TypeScript, Vite |
| UI Library | shadcn-vue (`reka-ui` / `radix-vue`) |
| Signature (Hardware) | signotec STPad `STPadServerLib-3.5.0.js` |
| Signature (Fallback) | Hand-rolled HTML5 Canvas composable |

---

```dataview
TABLE
  length(file.inlinks) as Backlinks,
  length(file.outlinks) as Outlinks
FROM "Releasing-Process"
SORT file.name ASC
```

> [!info]
> **Start here** → [[Releasing-Process/Overview|Releasing Process Overview]].