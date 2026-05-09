<?php

namespace App\Filters;

use App\Services\AcademicCalendar\AcademicCalendarService;
use Illuminate\Support\Facades\Auth;
use TakiElias\Tablar\Menu\Filters\FilterInterface;

class RolePermissionMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (! $this->isVisible($item)) {
            return false;
        }

        if (isset($item['calendar_process_key'])) {
            $item = $this->applyCalendarAvailability($item);
        }

        return $item['header'] ?? $item;
    }

    protected function isVisible($item)
    {
        $user = Auth::user();

        if (isset($item['hasAnyRole']) && !$user->hasAnyRole($item['hasAnyRole'])) {
            return false;
        }

        if (isset($item['hasRole']) && !$user->hasRole($item['hasRole'])) {
            return false;
        }

        return true;
    }

    protected function applyCalendarAvailability(array $item): array
    {
        static $windowAvailability = [];

        $processKey = (string) $item['calendar_process_key'];
        $isOpen = $windowAvailability[$processKey] ??= AcademicCalendarService::isProcessWindowOpen($processKey);

        if ($isOpen) {
            return $item;
        }

        $item['href'] = '#';
        $item['icon_color'] = 'secondary';
        $item['text'] = rtrim((string) ($item['text'] ?? '')) . ' (no disponible)';

        return $item;
    }
}
