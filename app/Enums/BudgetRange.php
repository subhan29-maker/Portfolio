<?php

namespace App\Enums;

/**
 * The budget bands offered on the contact form.
 *
 * Values are stable identifiers stored with a submission; labels are built at
 * render time so the currency symbol can be changed from configuration.
 *
 * Note: the "intl" PHP extension is not required here. Laravel's Number helper
 * would need it, so amounts are formatted with plain PHP instead.
 */
enum BudgetRange: string
{
    case Under1000 = 'under-1000';
    case From1000To5000 = '1000-5000';
    case From5000To15000 = '5000-15000';
    case Over15000 = 'over-15000';
    case NotSure = 'not-sure';

    public function label(): string
    {
        $currency = (string) config('portfolio.contact.currency');

        return match ($this) {
            self::Under1000 => 'Under '.self::amount(1000, $currency),
            self::From1000To5000 => self::amount(1000, $currency).' – '.self::amount(5000, $currency),
            self::From5000To15000 => self::amount(5000, $currency).' – '.self::amount(15000, $currency),
            self::Over15000 => 'Over '.self::amount(15000, $currency),
            self::NotSure => 'Not sure yet',
        };
    }

    /**
     * Allowed values, used to keep configuration and validation in sync.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Value/label pairs for a select element.
     *
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

    private static function amount(int $amount, string $currency): string
    {
        return $currency.number_format($amount);
    }
}
