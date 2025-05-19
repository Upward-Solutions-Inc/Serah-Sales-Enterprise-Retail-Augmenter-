<?php

namespace App\Helpers\Custom;

use Illuminate\Support\Collection;

class SidebarMenuHelper
{
    public static function injectBefore(array $menu, string $targetName, array $newItems): array
    {
        $menuCollection = collect($menu);
        $index = $menuCollection->search(fn ($item) => $item['name'] === $targetName);

        if ($index !== false) {
            $before = $menuCollection->slice(0, $index);
            $after = $menuCollection->slice($index);
            return $before->concat($newItems)->concat($after)->values()->all();
        }

        return array_merge($menu, $newItems);
    }
}