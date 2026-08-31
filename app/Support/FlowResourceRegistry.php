<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FlowResourceRegistry
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const RESOURCES = [
        'company' => Company::class,
        'contact' => Contact::class,
        'project' => Project::class,
        'task' => Task::class,
        'asset' => Asset::class,
        'ticket' => Ticket::class,
    ];

    private const ROUTES = [
        Company::class => 'companies.show',
        Contact::class => 'contacts.show',
        Project::class => 'projects.show',
        Task::class => 'tasks.show',
        Asset::class => 'assets.show',
        Ticket::class => 'tickets.show',
        User::class => 'users.show',
        Role::class => 'roles.show',
    ];

    private const LABELS = [
        Company::class => 'Company',
        Contact::class => 'Contact',
        Project::class => 'Project',
        Task::class => 'Task',
        Asset::class => 'Asset',
        Ticket::class => 'Ticket',
        User::class => 'User',
        Role::class => 'Role',
    ];

    public static function find(
        string $type,
        int|string $id,
        bool $withTrashed = false
    ): Model {
        $class = self::RESOURCES[$type] ?? null;

        if (! $class) {
            throw (new ModelNotFoundException)->setModel(Model::class, [$id]);
        }

        $query = $class::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public static function findTrashed(
        string $type,
        int|string $id
    ): Model {
        $class = self::RESOURCES[$type] ?? null;

        if (! $class) {
            throw (new ModelNotFoundException)->setModel(Model::class, [$id]);
        }

        return $class::onlyTrashed()->findOrFail($id);
    }

    public static function typeFor(Model $model): string
    {
        return array_search($model::class, self::RESOURCES, true) ?: 'resource';
    }

    public static function labelForModel(Model $model): string
    {
        if ($model instanceof Contact) {
            return trim($model->first_name.' '.$model->last_name);
        }

        foreach (['name', 'title', 'subject', 'reference', 'code', 'asset_tag'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }

    public static function labelForClass(string $class): string
    {
        return __(self::LABELS[$class] ?? class_basename($class));
    }

    public static function routeFor(Model $model): ?string
    {
        return self::ROUTES[$model::class] ?? null;
    }

    public static function urlFor(Model $model): ?string
    {
        $route = self::routeFor($model);

        return $route
            ? route($route, $model)
            : null;
    }

    public static function urlForAudit(AuditLog $auditLog): ?string
    {
        $route = self::ROUTES[$auditLog->auditable_type] ?? null;

        if (! $route || ! class_exists($auditLog->auditable_type)) {
            return null;
        }

        /** @var class-string<Model> $class */
        $class = $auditLog->auditable_type;
        $model = $class::query()->find($auditLog->auditable_id);

        return $model
            ? route($route, $model)
            : null;
    }

    /**
     * @return array<string, class-string<Model>>
     */
    public static function auditResources(): array
    {
        return [
            ...self::RESOURCES,
            'user' => User::class,
            'role' => Role::class,
        ];
    }

    /**
     * @return array<string, class-string<Model>>
     */
    public static function trashResources(): array
    {
        return self::RESOURCES;
    }
}
