@php
    $resourceType = strtolower(class_basename($resourceModel));
    $customFields = App\Models\CustomField::query()->where('resource_type', $resourceType)->where('is_active', true)->orderBy('sort_order')->get();
    $availableTags = App\Models\Tag::query()->orderBy('name')->get();
    $existingValues = $resourceModel->exists ? $resourceModel->customFieldValues()->pluck('value', 'custom_field_id') : collect();
    $selectedTags = array_map('strval', old('tag_ids', $resourceModel->exists ? $resourceModel->tags()->pluck('tags.id')->all() : []));
@endphp

@if ($customFields->isNotEmpty() || $availableTags->isNotEmpty())
    <div class="col-12 mt-4">
        <div class="fm-form-section">
            <div class="fw-semibold mb-3">{{ __('Classification & custom data') }}</div>
            <div class="row g-3">
                @if ($availableTags->isNotEmpty())
                    <div class="col-12">
                        <label for="tag_ids" class="form-label">{{ __('Tags') }}</label>
                        <select id="tag_ids" name="tag_ids[]" class="form-select" multiple size="{{ min(5, max(2, $availableTags->count())) }}">
                            @foreach ($availableTags as $tag)
                                <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, $selectedTags, true))>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple tags.') }}</div>
                    </div>
                @endif

                @foreach ($customFields as $field)
                    @php
                        $fieldName = 'custom_'.$field->id;
                        $value = old($fieldName, $existingValues->get($field->id));
                    @endphp
                    <div class="{{ $field->field_type === 'textarea' ? 'col-12' : 'col-12 col-md-6' }}">
                        <label for="{{ $fieldName }}" class="form-label">{{ $field->name }} @if($field->is_required)<span class="text-danger">*</span>@endif</label>
                        @if ($field->field_type === 'textarea')
                            <textarea id="{{ $fieldName }}" name="{{ $fieldName }}" class="form-control" rows="3" @required($field->is_required)>{{ $value }}</textarea>
                        @elseif ($field->field_type === 'select')
                            <select id="{{ $fieldName }}" name="{{ $fieldName }}" class="form-select" @required($field->is_required)>
                                <option value="">—</option>
                                @foreach ($field->options ?? [] as $option)<option value="{{ $option }}" @selected((string)$value === (string)$option)>{{ $option }}</option>@endforeach
                            </select>
                        @elseif ($field->field_type === 'checkbox')
                            <div class="form-check mt-2"><input id="{{ $fieldName }}" name="{{ $fieldName }}" type="checkbox" value="1" class="form-check-input" @checked((bool)$value)><label class="form-check-label" for="{{ $fieldName }}">{{ __('Yes') }}</label></div>
                        @else
                            <input id="{{ $fieldName }}" name="{{ $fieldName }}" type="{{ in_array($field->field_type,['number','date']) ? $field->field_type : 'text' }}" class="form-control" value="{{ $value }}" @required($field->is_required)>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
