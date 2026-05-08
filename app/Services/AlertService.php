<?php

namespace App\Services;

class AlertService
{
    /**
     * Memicu alert flash ke session.
     * Variant yang tersedia: success, error, warning, info
     */
    public static function notify(string $variant, string $title, string $message): void
    {
        session()->flash('notification', [
            'variant' => $variant,
            'title'   => $title,
            'message' => $message,
        ]);
    }
}