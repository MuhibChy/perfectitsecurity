<?php

namespace App\Support;

/**
 * DecorativeZone — page-context decoration config. Selects a restrained
 * IT object per page type; bedroom rules live in one place:
 * data-heavy pages get (almost) nothing, empty states get one contextual
 * object. Never hard-code decorations per page.
 */
class DecorativeZone
{
    public const OBJECTS = [
        'server-rack', 'shield-lock', 'database', 'network-nodes',
        'laptop-code', 'document-stack', 'chart-analytics', 'cloud-wifi',
        'cpu-chip', 'terminal-window', 'invoice-receipt', 'headset-support',
    ];

    public static function for(string $pageType): array
    {
        return match ($pageType) {
            'cybersecurity' => ['object' => 'shield-lock', 'zone' => 'side-right', 'size' => 'md', 'max' => 1, 'mobile' => 'hidden'],
            'networking' => ['object' => 'network-nodes', 'zone' => 'side-right', 'size' => 'md', 'max' => 1, 'mobile' => 'hidden'],
            'servers' => ['object' => 'server-rack', 'zone' => 'side-right', 'size' => 'md', 'max' => 1, 'mobile' => 'hidden'],
            'ai' => ['object' => 'network-nodes', 'zone' => 'side-right', 'size' => 'md', 'max' => 1, 'mobile' => 'hidden'],
            'finance' => ['object' => 'invoice-receipt', 'zone' => 'bottom-right', 'size' => 'sm', 'max' => 1, 'mobile' => 'hidden'],
            'reports' => ['object' => 'document-stack', 'zone' => 'bottom-right', 'size' => 'sm', 'max' => 1, 'mobile' => 'hidden'],
            'training' => ['object' => 'laptop-code', 'zone' => 'side-right', 'size' => 'md', 'max' => 1, 'mobile' => 'hidden'],
            'support' => ['object' => 'headset-support', 'zone' => 'side-right', 'size' => 'md', 'max' => 1, 'mobile' => 'hidden'],
            'dashboard' => ['object' => 'cloud-wifi', 'zone' => 'top-right', 'size' => 'sm', 'max' => 1, 'mobile' => 'hidden'],
            'hero' => ['object' => 'server-rack', 'zone' => 'side-right', 'size' => 'lg', 'max' => 2, 'mobile' => 'hidden'],
            default => ['object' => 'terminal-window', 'zone' => 'bottom-right', 'size' => 'sm', 'max' => 0, 'mobile' => 'hidden'],
        };
    }

    /** Contextual object for empty states (type → object). */
    public static function emptyObject(string $type): string
    {
        return match ($type) {
            'services', 'service-history', 'tasks', 'projects' => 'server-rack',
            'orders' => 'document-stack',
            'tickets', 'support' => 'headset-support',
            'reports', 'documents' => 'document-stack',
            'invoices', 'payments', 'transactions', 'receipts' => 'invoice-receipt',
            'commissions', 'revenue' => 'chart-analytics',
            'notifications' => 'cloud-wifi',
            'training' => 'laptop-code',
            'security' => 'shield-lock',
            'team', 'directory' => 'network-nodes',
            default => 'terminal-window',
        };
    }

    public static function isKnownObject(string $object): bool
    {
        return in_array($object, self::OBJECTS, true);
    }
}
