<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Forms\Domain\Models\Form;

final class DuplicateFormAction extends Action
{
    public function execute(Form $form): Form
    {
        return $this->transaction(function () use ($form) {
            $clone = $form->replicate();
            $clone->name = $form->name . ' (Copy)';
            $clone->slug = Str::slug($clone->name) . '-' . time();
            $clone->is_active = false;
            $clone->save();

            foreach ($form->fields as $field) {
                $clone->fields()->create($field->only([
                    'name', 'label', 'type', 'placeholder', 'default_value',
                    'options', 'validation_rules', 'is_required', 'ordering', 'conditions'
                ]));
            }

            return $clone->fresh(['fields']);
        });
    }
}
