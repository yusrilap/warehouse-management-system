# Warehouse Management System

A web-based Warehouse Management System built with Laravel and Filament for managing warehouse inventory, stock movements, and operational transactions in a manufacturing environment.

This project was developed as a portfolio project inspired by real-world warehouse and manufacturing workflows, with a focus on inventory accuracy, transaction traceability, and role-based access control.

## Features

### Master Data
- Category Management
- Unit Management
- Product Management
- Supplier Management
- Warehouse Management
- Storage Location Management

### Inventory Transactions
- Goods Receipt
- Stock Out
- Stock Transfer
- Stock Adjustment
- Stock Opname

### Inventory Monitoring
- Current Stock Monitoring
- Stock Movement History
- Low Stock Monitoring

### Reports
- Stock Report
- Stock Movement Report
- Filtered Report Export

### User & Access Management
- User Management
- Role Management
- Permission Management
- Role-Based Access Control (RBAC)

### Dashboard
- Active Product Overview
- Active Warehouse Overview
- Low Stock Summary
- Daily Inventory Transaction Summary
- Recent Stock Movements
- Low Stock Items

### Activity Log
- Master Data Activity Tracking
- Inventory Transaction Activity Tracking
- User Activity Tracking
- Old and New Value Comparison
- Activity Filtering by Event, Module, and User
- Read-Only Audit Trail

## Technology Stack

- PHP 8.3
- Laravel 12
- Filament 4
- Livewire 3
- MySQL 8
- Spatie Laravel Permission
- Spatie Laravel Activitylog
- Laravel Herd
- Git & GitHub

## Project Status

- [x] Master Data
- [x] Inventory Transactions
- [x] Inventory Monitoring
- [x] Inventory Reports
- [x] Report Export
- [x] Roles & Permissions
- [x] User Management
- [x] Activity Log
- [x] Dashboard Enhancement
- [ ] Automated Testing

## Inventory Flow

Inventory quantities are not modified directly from the stock monitoring page.

All stock changes are processed through inventory transactions:

```text
Goods Receipt ───────────────> Stock Increase

Stock Out ───────────────────> Stock Decrease

Stock Transfer ──────────────> Source Stock Decrease
                               Destination Stock Increase

Stock Adjustment ────────────> Stock Correction

Stock Opname ────────────────> Physical vs System Stock
                               Adjustment when required
```

## Inventory Integrity

The system is designed to maintain inventory consistency and transaction traceability.

- Stock cannot be modified directly from the stock monitoring page.
- Inventory changes are processed through dedicated transaction services.
- Database transactions are used to keep stock updates and stock movement records consistent.
- Stock Out and Stock Transfer prevent transactions when available stock is insufficient.
- Stock Transfer prevents transfers to the same source and destination location.
- Stock Adjustment records the difference between system stock and actual stock.
- Stock Opname records system quantity, physical quantity, and variance.
- Every inventory change is recorded in the Stock Movement ledger.

## Audit Trail

The system uses activity logging to provide an additional audit trail for administrative and operational activities.

Activity logs record:

- User who performed the activity
- Event type such as Created or Updated
- Related module and record
- Previous values for updated records
- New values after changes

Activity Logs are read-only and protected using role-based permissions.

Stock Movement and Activity Log serve different purposes:

- **Stock Movement** tracks how inventory quantities change.
- **Activity Log** tracks who performed an action and what data was changed.
