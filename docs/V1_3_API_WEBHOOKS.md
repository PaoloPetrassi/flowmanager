# FlowManager v1.3 — REST/OpenAPI & Webhook Documentation

v1.3 turns the integration layer introduced in v0.10 into a documented and testable interface.

## In-app documentation

Administrators can open:

```text
Administration → API documentation
```

The page documents bearer-token authentication, scopes, REST resources, pagination, inbound ticket creation and signed outbound webhooks.

The machine-readable OpenAPI 3.1 specification is exposed at:

```text
/api/openapi.json
```

The OpenAPI document is generated from `App\Support\OpenApiSpecification`, so its version follows `config('flowmanager.version')`.

## Authentication

Create a token in:

```text
Administration → API tokens
```

Send it as:

```http
Authorization: Bearer fm_your_token
```

Access is granted only when both conditions are satisfied:

1. the token contains the required `read` or `write` ability;
2. the token owner has the corresponding FlowManager permission.

Expired or unknown tokens return HTTP `401`.

## Read API

All list endpoints accept `per_page` from 1 to 100.

```text
GET /api/v1/companies
GET /api/v1/companies/{id}
GET /api/v1/contacts
GET /api/v1/contacts/{id}
GET /api/v1/projects
GET /api/v1/projects/{id}
GET /api/v1/tasks
GET /api/v1/tasks/{id}
GET /api/v1/tickets
GET /api/v1/tickets/{id}
GET /api/v1/assets
GET /api/v1/assets/{id}
```

These endpoints require the `read` ability and the matching `*.view` permission.

## Inbound support ticket

```text
POST /api/v1/tickets/inbound
```

Requires the `write` ability and `tickets.create` permission.

Example body:

```json
{
  "subject": "Inbound request",
  "body": "Message body",
  "from_email": "sender@example.com",
  "priority": "medium"
}
```

## Webhook management

The Webhooks page now supports:

- active/paused state;
- a queued test delivery;
- recent delivery status and delivery IDs;
- direct access to API/webhook documentation.

Configured business events remain:

```text
created
updated
deleted
task.completed
ticket.resolved
```

The manual test action emits the special event `test` only to the selected webhook.

## Delivery headers

Each request includes:

```text
X-FlowManager-Event
X-FlowManager-Delivery
X-FlowManager-Timestamp
X-FlowManager-Signature
User-Agent
```

`X-FlowManager-Delivery` matches the `delivery_id` UUID included in the JSON body.

## Signature verification

FlowManager serializes the final JSON body first, then signs:

```text
{unix_timestamp}.{exact_raw_json_body}
```

with HMAC-SHA256 and the webhook secret.

The header format is:

```text
X-FlowManager-Signature: sha256=<hex-digest>
```

PHP verification example:

```php
$timestamp = $_SERVER['HTTP_X_FLOWMANAGER_TIMESTAMP'];
$rawBody = file_get_contents('php://input');
$expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);
$valid = hash_equals($expected, $_SERVER['HTTP_X_FLOWMANAGER_SIGNATURE']);
```

Receivers should also reject timestamps outside an acceptable age window to reduce replay risk.

## Queue worker

Webhook delivery remains asynchronous. Keep the `webhooks` queue running:

```bash
php artisan queue:work --queue=system,imports,reports,webhooks,default --tries=3 --timeout=300
```
