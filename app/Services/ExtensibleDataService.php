<?php

namespace App\Services;

use App\Models\CustomField;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

class ExtensibleDataService
{
    public function sync(Model $model, array $input): void
    {
        if (array_key_exists('tag_ids', $input) && method_exists($model, 'tags')) {
            $ids = Tag::query()->whereIn('id', array_filter((array) $input['tag_ids']))->pluck('id')->all();
            $model->tags()->sync($ids);
        }
        if (! method_exists($model, 'customFieldValues')) {
            return;
        }
        $resource = $this->resourceType($model);
        $fields = CustomField::query()->where('resource_type', $resource)->where('is_active', true)->get();
        foreach ($fields as $field) {
            $key = 'custom_'.$field->id;
            if (! array_key_exists($key, $input)) {
                continue;
            } $value = is_array($input[$key]) ? json_encode($input[$key]) : $input[$key];
            $model->customFieldValues()->updateOrCreate(['custom_field_id' => $field->id], ['value' => $value]);
        }
    }

    public function resourceType(Model $model): string
    {
        return strtolower(class_basename($model));
    }
}
