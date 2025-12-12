<?php

declare(strict_types=1);

namespace Modules\Forms\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Forms\Domain\Models\Form;

class FormValidator
{
    public function validate(Form $form, array $data): array
    {
        $rules = $this->buildRules($form);
        $messages = $this->buildMessages($form);

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    protected function buildRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $fieldRules = $field->getValidationRulesArray();

            if (empty($fieldRules) && $field->is_required) {
                $fieldRules = ['required'];
            }

            $fieldRules = $this->addTypeRules($field->type, $fieldRules);

            if (!empty($fieldRules)) {
                $rules[$field->name] = $fieldRules;
            }
        }

        return $rules;
    }

    protected function addTypeRules(string $type, array $rules): array
    {
        $typeRules = match ($type) {
            'email' => ['email'],
            'url' => ['url'],
            'number' => ['numeric'],
            'date' => ['date'],
            'file' => ['file', 'max:' . config('forms.uploads.max_size', 5120)],
            'phone' => ['regex:/^[\d\s\-\+\(\)]+$/'],
            default => [],
        };

        return array_merge($rules, $typeRules);
    }

    protected function buildMessages(Form $form): array
    {
        $messages = [];

        foreach ($form->fields as $field) {
            $label = $field->label;
            $messages["{$field->name}.required"] = "{$label} is required.";
            $messages["{$field->name}.email"] = "Please enter a valid email address.";
            $messages["{$field->name}.url"] = "Please enter a valid URL.";
        }

        return $messages;
    }
}
