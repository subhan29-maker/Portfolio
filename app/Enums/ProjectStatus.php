<?php

namespace App\Enums;

/**
 * The three lifecycle states a project moves through.
 *
 * - Draft:       created but never published; never visible publicly.
 * - Published:   live on the public portfolio.
 * - Unpublished: was public, now hidden again.
 */
enum ProjectStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Unpublished = 'unpublished';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Unpublished => 'Unpublished',
        };
    }

    /**
     * Only published projects are shown to visitors.
     */
    public function isPublic(): bool
    {
        return $this === self::Published;
    }

    /**
     * Modifier used by the status badge in the admin and public markup.
     */
    public function badgeModifier(): string
    {
        return match ($this) {
            self::Draft => 'badge--muted',
            self::Published => 'badge--ok',
            self::Unpublished => 'badge--warn',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
