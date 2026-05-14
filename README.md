# TableSync Pro

A web-based Restaurant Management System built as a Software Engineering course project.


**Live:** [m.1234.al](https://m.1234.al)

---

## What is it?

TableSync Pro is a multi-role restaurant management system that brings reservations, ordering, and billing into one place. It gives customers a way to book tables online, waitstaff a clean POS interface for managing orders, and managers full control over the menu, tables, and reports.

---

## Why we built it

Most small restaurants still rely on phone calls, paper notes, and verbal communication between staff. This causes:

- Double-booked tables and reservation conflicts
- Orders getting lost or delayed between the floor and the kitchen
- Manual billing that is slow and error-prone
- No easy way for managers to track what is happening in real time

TableSync Pro tackles these problems with a role-based system, live order tracking, and automated workflows.

---

## User Roles

| Role | What they can do |
|------|------------------|
| **Customer** | Create and manage reservations, browse the menu, view booking history |
| **Waiter** | Check in tables, create and update orders, generate invoices |
| **Manager** | Manage menu items and tables, configure settings, view reports |

---

## Features

- **Reservations** - Create, update, and cancel bookings with availability checks
- **POS Order Entry** - Tablet-friendly split-screen interface for fast order input
- **Order Tracking** - Live status updates: Pending, Preparing, Served
- **Menu Management** - Add, edit, delete, and toggle menu item availability
- **Table Management** - Set up tables by capacity and location, track occupancy
- **Reports** - 14-day revenue overview, reservation stats, and top-selling items
- **Access Control** - Session-based login with role-specific page restrictions

---

## Project Structure

```
project/
├── assets/
│   ├── style.css               # Global stylesheet
│   └── app.js                  # Global client-side scripts
├── config/
│   └── db.php                  # PDO database connection
├── includes/
│   ├── auth.php                # Session guard + require_role()
│   ├── logout.php              # Session termination and redirect
│   ├── invoice_generate.php
│   ├── forgot_password.php
│   ├── turnover_prediction.php
│   └── backup.php
├── pages/
│   ├── index.php               # Entry point, redirects by role
│   ├── login.php
│   ├── register.php
│   ├── dashboard_customer.php
│   ├── dashboard_waiter.php
│   ├── dashboard_manager.php
│   ├── orders_new.php
│   ├── orders_active.php
│   ├── orders_add_items.php
│   ├── orders_cancel.php
│   ├── orders_update_status.php
│   ├── orders_manage.php
│   ├── menu_manage.php
│   ├── menu_search.php
│   ├── reservations_create.php
│   ├── reservations_history.php
│   ├── reservations_cancel.php
│   ├── reservations_update.php
│   ├── reservations_manage.php
│   ├── tables_status.php
│   ├── tables_mark_occupied.php
│   ├── settings.php
│   └── reports.php
└── schema.sql                  # Database schema and seed data
```

---

## Demo Accounts

All accounts use the password **`demo123`**.

| Email | Role |
|-------|------|
| `customer@demo.local` | Customer |
| `waiter@demo.local` | Waiter |
| `manager@demo.local` | Manager |

Quick-login buttons are available on the login page.

---

## Waiter Order Workflow

1. **Check-In** - Mark a table as occupied when guests arrive
2. **New Order** - Start an order for that table
3. **Add Items** - Pick items from the menu using the POS card view
4. **Update Status** - Move the order from Pending to Preparing to Served
5. **Invoice** - Generate the bill, print it, and mark it as paid

---

## Team

| Name |
|------|
| Klaudio Sula |
| Muhamed Tereziu |
| Kei Paravani |
| Kleo Kryemadhi |
| Rian Rada |
| Ensar Kraja |
| Erdin Mujaxhi |