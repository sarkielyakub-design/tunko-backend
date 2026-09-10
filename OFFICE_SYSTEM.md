# Tunko Office System

The backend now supports a backend-driven office network, office staff accounts, and destination-office assignment for office transfers.

## Migration

Run:

```bash
php artisan migrate --force
```

The new migrations are:

- `2026_09_09_100000_create_office_staff_table.php`
- `2026_09_09_100001_add_office_staff_to_office_transfers.php`
- `2026_09_09_100002_allow_refund_transaction_type.php`

## Admin API

All admin endpoints require the existing admin Sanctum token.

### Offices

- `GET /api/admin/offices`
- `POST /api/admin/offices`
- `GET /api/admin/offices/{office}`
- `PUT /api/admin/offices/{office}`
- `DELETE /api/admin/offices/{office}`
- `POST /api/admin/offices/{office}/activate`
- `POST /api/admin/offices/{office}/deactivate`
- `POST /api/admin/offices/{office}/head-office`

### Office Staff

- `GET /api/admin/office-staff`
- `POST /api/admin/office-staff`
- `GET /api/admin/office-staff/{officeStaff}`
- `PUT /api/admin/office-staff/{officeStaff}`
- `DELETE /api/admin/office-staff/{officeStaff}`
- `POST /api/admin/office-staff/{officeStaff}/activate`
- `POST /api/admin/office-staff/{officeStaff}/deactivate`

Staff roles are `manager` and `agent`.

### Office Transfers

- `GET /api/admin/office-transfers`
- `GET /api/admin/office-transfers/{officeTransfer}`
- `PATCH /api/admin/office-transfers/{officeTransfer}/status`

`office_id` filters by destination office. Status values are `pending`, `processing`, `completed`, `failed`, and `cancelled`.

Cancelling or failing an office transfer refunds the customer's debited total exactly once and records a `refund` transaction.

## Office Staff API

### Authentication

- `POST /api/v1/office/login`
- `POST /api/v1/office/logout`
- `GET /api/v1/office/profile`

### Dashboard and transfers

- `GET /api/v1/office/dashboard`
- `GET /api/v1/office/transfers`
- `GET /api/v1/office/transfers/{officeTransfer}`
- `PATCH /api/v1/office/transfers/{officeTransfer}/status`

Office staff can only access transfers whose `destination_office_id` belongs to their assigned office.

## Customer Office Transfer

The existing customer endpoints remain:

- `GET /api/v1/office-transfers/destinations`
- `POST /api/v1/office-transfers/quote`
- `POST /api/v1/office-transfers/send`
- `GET /api/v1/office-transfers/history`
- `GET /api/v1/office-transfers/receipt/{reference}`

`destination_office_id` is now supported and preferred. The destinations response includes actual active offices while retaining `cities` for compatibility with the current Flutter client. The legacy country/city input is still accepted during migration, but it resolves to an active office and stores its ID.
