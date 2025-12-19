<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Localization\Domain\Models\Language;

/**
 * Toggle Language Action.
 *
 * Activates or deactivates a language.
 *
 * @package Modules\Localization\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ToggleLanguageAction extends Action
{
    /**
     * Activate a language.
     *
     * @param Language $language The language to activate
     *
     * @return Language The activated language
     */
    public function activate(Language $language): Language
    {
        $language->update(['is_active' => true]);
        return $language->fresh();
    }

    /**
     * Deactivate a language.
     *
     * @param Language $language The language to deactivate
     *
     * @return Language The deactivated language (unchanged if default)
     */
    public function deactivate(Language $language): Language
    {
        if ($language->is_default) {
            return $language;
        }

        $language->update(['is_active' => false]);
        return $language->fresh();
    }
}
