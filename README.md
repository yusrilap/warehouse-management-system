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

## Technology Stack

- PHP 8.3
- Laravel 12
- Filament 4
- Livewire 3
- MySQL 8
- Spatie Laravel Permission
- Laravel Herd
- Git & GitHub

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