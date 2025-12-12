<?php

declare(strict_types=1);

namespace Modules\Menu\Services;

use Illuminate\Support\HtmlString;
use Modules\Menu\Domain\Models\Menu;
use Modules\Menu\Domain\Models\MenuItem;

class MenuBuilder
{
    protected array $options = [
        'ul_class' => 'menu',
        'li_class' => 'menu-item',
        'a_class' => 'menu-link',
        'active_class' => 'active',
        'has_children_class' => 'has-children',
        'children_ul_class' => 'submenu',
    ];

    public function render(Menu|string $menu, array $options = []): HtmlString
    {
        if (is_string($menu)) {
            $menu = Menu::findBySlug($menu) ?? Menu::findByLocation($menu);
        }

        if (!$menu) {
            return new HtmlString('');
        }

        $this->options = array_merge($this->options, $options);
        $items = $menu->getTree();

        return new HtmlString($this->buildList($items));
    }

    protected function buildList($items, bool $isSubmenu = false): string
    {
        if ($items->isEmpty()) {
            return '';
        }

        $class = $isSubmenu ? $this->options['children_ul_class'] : $this->options['ul_class'];
        $html = "<ul class=\"{$class}\">";

        foreach ($items as $item) {
            $html .= $this->buildItem($item);
        }

        $html .= '</ul>';

        return $html;
    }

    protected function buildItem(MenuItem $item): string
    {
        if (!$item->is_active) {
            return '';
        }

        $classes = [$this->options['li_class']];

        if ($this->isActive($item)) {
            $classes[] = $this->options['active_class'];
        }

        if ($item->hasChildren()) {
            $classes[] = $this->options['has_children_class'];
        }

        $classString = implode(' ', $classes);
        $html = "<li class=\"{$classString}\">";
        $html .= $this->buildLink($item);

        if ($item->children->isNotEmpty()) {
            $html .= $this->buildList($item->children, true);
        }

        $html .= '</li>';

        return $html;
    }

    protected function buildLink(MenuItem $item): string
    {
        $url = $item->url;
        $title = $item->title;
        $target = $item->target;
        $class = $this->options['a_class'];
        $icon = $item->icon ? "<i class=\"{$item->icon}\"></i> " : '';

        return "<a href=\"{$url}\" class=\"{$class}\" target=\"{$target}\">{$icon}{$title}</a>";
    }

    protected function isActive(MenuItem $item): bool
    {
        $currentUrl = request()->url();

        if ($item->url === $currentUrl) {
            return true;
        }

        foreach ($item->children as $child) {
            if ($this->isActive($child)) {
                return true;
            }
        }

        return false;
    }

    public function toArray(Menu|string $menu): array
    {
        if (is_string($menu)) {
            $menu = Menu::findBySlug($menu) ?? Menu::findByLocation($menu);
        }

        if (!$menu) {
            return [];
        }

        return $this->itemsToArray($menu->getTree());
    }

    protected function itemsToArray($items): array
    {
        $result = [];

        foreach ($items as $item) {
            if (!$item->is_active) {
                continue;
            }

            $data = [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'target' => $item->target,
                'icon' => $item->icon,
                'is_active' => $this->isActive($item),
            ];

            if ($item->children->isNotEmpty()) {
                $data['children'] = $this->itemsToArray($item->children);
            }

            $result[] = $data;
        }

        return $result;
    }
}
