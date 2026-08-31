<?php

namespace App\Models\Concerns;

use App\Models\CustomFieldValue;
use App\Models\Tag;
use App\Services\ExtensibleDataService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasExtensibleData
{
    public static function bootHasExtensibleData(): void
    {
        static::saved(function ($model): void {
            if (! app()->bound('request') || ! auth()->check()) {
                return;
            }

            if (! request()->has('tag_ids') && collect(request()->keys())->doesntContain(fn ($key) => str_starts_with((string) $key, 'custom_'))) {
                return;
            }

            app(ExtensibleDataService::class)->sync($model, request()->all());
        });
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'valued');
    }
}
