<?php

namespace App\Support;

class OpenApiSpecification
{
    public static function build(): array
    {
        $paths = [];

        foreach (['companies', 'contacts', 'projects', 'tasks', 'tickets', 'assets'] as $resource) {
            $schema = ucfirst($resource === 'companies' ? 'company' : rtrim($resource, 's'));
            $tag = ucfirst($resource);

            $paths['/'.$resource] = [
                'get' => [
                    'tags' => [$tag],
                    'summary' => 'List '.$resource,
                    'operationId' => 'list'.ucfirst($resource),
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [
                        [
                            'name' => 'per_page',
                            'in' => 'query',
                            'required' => false,
                            'schema' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'maximum' => 100,
                                'default' => 25,
                            ],
                        ],
                    ],
                    'responses' => self::listResponses($schema),
                ],
            ];

            $paths['/'.$resource.'/{id}'] = [
                'get' => [
                    'tags' => [$tag],
                    'summary' => 'Get a '.strtolower($schema),
                    'operationId' => 'get'.$schema,
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [self::idParameter()],
                    'responses' => self::showResponses($schema),
                ],
            ];
        }

        $paths['/tickets/inbound'] = [
            'post' => [
                'tags' => ['Tickets'],
                'summary' => 'Create an inbound support ticket',
                'operationId' => 'createInboundTicket',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/InboundTicketRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Ticket created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Ticket'],
                                        'url' => ['type' => 'string', 'format' => 'uri'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => self::errorResponse('Unauthenticated'),
                    '403' => self::errorResponse('Token or user lacks the required write permission'),
                    '422' => self::errorResponse('Validation failed'),
                ],
            ],
        ];

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'FlowManager REST API',
                'version' => (string) config('flowmanager.version'),
                'description' => 'Read API for FlowManager business resources plus an inbound support-ticket endpoint. Authentication uses scoped bearer tokens created from Administration → API tokens.',
            ],
            'servers' => [
                ['url' => rtrim((string) config('app.url'), '/').'/api/v1'],
            ],
            'tags' => collect(['Companies', 'Contacts', 'Projects', 'Tasks', 'Tickets', 'Assets'])
                ->map(fn (string $name) => ['name' => $name])
                ->all(),
            'paths' => $paths,
            'webhooks' => [
                'flowManagerEvent' => [
                    'post' => [
                        'summary' => 'Signed outbound FlowManager event',
                        'description' => 'Configured webhook destinations receive JSON events. Verify X-FlowManager-Signature using HMAC-SHA256 over "{timestamp}.{rawBody}" with the webhook signing secret.',
                        'parameters' => [
                            [
                                'name' => 'X-FlowManager-Event',
                                'in' => 'header',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'X-FlowManager-Delivery',
                                'in' => 'header',
                                'required' => true,
                                'schema' => ['type' => 'string', 'format' => 'uuid'],
                            ],
                            [
                                'name' => 'X-FlowManager-Timestamp',
                                'in' => 'header',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                            ],
                            [
                                'name' => 'X-FlowManager-Signature',
                                'in' => 'header',
                                'required' => true,
                                'schema' => [
                                    'type' => 'string',
                                    'example' => 'sha256=0123456789abcdef',
                                ],
                            ],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/WebhookPayload'],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Receiver accepted the webhook'],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'FlowManager API token',
                    ],
                ],
                'schemas' => self::schemas(),
            ],
        ];
    }

    private static function schemas(): array
    {
        return [
            'Company' => self::resourceSchema([
                'name' => ['type' => 'string'],
                'legal_name' => ['type' => ['string', 'null']],
                'type' => ['type' => 'string'],
                'status' => ['type' => 'string'],
                'vat_number' => ['type' => ['string', 'null']],
                'email' => ['type' => ['string', 'null'], 'format' => 'email'],
                'phone' => ['type' => ['string', 'null']],
                'city' => ['type' => ['string', 'null']],
                'country_code' => ['type' => ['string', 'null']],
            ]),
            'Contact' => self::resourceSchema([
                'company_id' => ['type' => ['integer', 'null']],
                'first_name' => ['type' => 'string'],
                'last_name' => ['type' => 'string'],
                'job_title' => ['type' => ['string', 'null']],
                'email' => ['type' => ['string', 'null'], 'format' => 'email'],
                'phone' => ['type' => ['string', 'null']],
                'is_primary' => ['type' => 'boolean'],
            ]),
            'Project' => self::resourceSchema([
                'company_id' => ['type' => ['integer', 'null']],
                'manager_id' => ['type' => ['integer', 'null']],
                'code' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'status' => ['type' => 'string'],
                'priority' => ['type' => 'string'],
                'start_date' => ['type' => ['string', 'null'], 'format' => 'date'],
                'due_date' => ['type' => ['string', 'null'], 'format' => 'date'],
                'budget' => ['type' => ['string', 'number', 'null']],
            ]),
            'Task' => self::resourceSchema([
                'project_id' => ['type' => 'integer'],
                'assigned_to' => ['type' => ['integer', 'null']],
                'title' => ['type' => 'string'],
                'status' => ['type' => 'string'],
                'priority' => ['type' => 'string'],
                'due_date' => ['type' => ['string', 'null'], 'format' => 'date'],
                'completed_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ]),
            'Ticket' => self::resourceSchema([
                'company_id' => ['type' => ['integer', 'null']],
                'contact_id' => ['type' => ['integer', 'null']],
                'assigned_to' => ['type' => ['integer', 'null']],
                'reference' => ['type' => 'string'],
                'subject' => ['type' => 'string'],
                'category' => ['type' => 'string'],
                'status' => ['type' => 'string'],
                'priority' => ['type' => 'string'],
                'description' => ['type' => ['string', 'null']],
                'resolved_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ]),
            'Asset' => self::resourceSchema([
                'company_id' => ['type' => ['integer', 'null']],
                'assigned_to' => ['type' => ['integer', 'null']],
                'asset_tag' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'category' => ['type' => ['string', 'null']],
                'brand' => ['type' => ['string', 'null']],
                'model' => ['type' => ['string', 'null']],
                'serial_number' => ['type' => ['string', 'null']],
                'status' => ['type' => 'string'],
            ]),
            'InboundTicketRequest' => [
                'type' => 'object',
                'required' => ['subject', 'body'],
                'properties' => [
                    'subject' => ['type' => 'string', 'maxLength' => 200],
                    'body' => ['type' => 'string', 'maxLength' => 20000],
                    'from_email' => ['type' => ['string', 'null'], 'format' => 'email'],
                    'company_id' => ['type' => ['integer', 'null']],
                    'contact_id' => ['type' => ['integer', 'null']],
                    'priority' => ['type' => ['string', 'null']],
                    'category' => ['type' => ['string', 'null']],
                ],
            ],
            'WebhookPayload' => [
                'type' => 'object',
                'required' => ['delivery_id', 'event', 'occurred_at', 'resource', 'id', 'data'],
                'properties' => [
                    'delivery_id' => ['type' => 'string', 'format' => 'uuid'],
                    'event' => ['type' => 'string'],
                    'occurred_at' => ['type' => 'string', 'format' => 'date-time'],
                    'resource' => ['type' => 'string'],
                    'id' => ['type' => ['integer', 'string']],
                    'data' => ['type' => 'object', 'additionalProperties' => true],
                ],
            ],
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                    'errors' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function resourceSchema(array $properties): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                ...$properties,
                'created_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                'updated_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
            ],
            'additionalProperties' => true,
        ];
    }

    private static function idParameter(): array
    {
        return [
            'name' => 'id',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'integer', 'minimum' => 1],
        ];
    }

    private static function listResponses(string $schema): array
    {
        return [
            '200' => [
                'description' => 'Paginated result',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'data' => [
                                    'type' => 'array',
                                    'items' => ['$ref' => '#/components/schemas/'.$schema],
                                ],
                                'current_page' => ['type' => 'integer'],
                                'last_page' => ['type' => 'integer'],
                                'per_page' => ['type' => 'integer'],
                                'total' => ['type' => 'integer'],
                            ],
                            'additionalProperties' => true,
                        ],
                    ],
                ],
            ],
            '401' => self::errorResponse('Unauthenticated'),
            '403' => self::errorResponse('Token or user lacks the required read permission'),
        ];
    }

    private static function showResponses(string $schema): array
    {
        return [
            '200' => [
                'description' => 'Resource found',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'data' => ['$ref' => '#/components/schemas/'.$schema],
                            ],
                        ],
                    ],
                ],
            ],
            '401' => self::errorResponse('Unauthenticated'),
            '403' => self::errorResponse('Token or user lacks the required read permission'),
            '404' => self::errorResponse('Resource not found'),
        ];
    }

    private static function errorResponse(string $description): array
    {
        return [
            'description' => $description,
            'content' => [
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/Error'],
                ],
            ],
        ];
    }
}
