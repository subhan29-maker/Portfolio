<?php

namespace Tests\Unit;

use App\Enums\BudgetRange;
use Tests\TestCase;

class BudgetRangeTest extends TestCase
{
    public function test_labels_are_built_from_the_configured_currency(): void
    {
        config(['portfolio.contact.currency' => '$']);

        $this->assertSame('Under $1,000', BudgetRange::Under1000->label());
        $this->assertSame('$1,000 – $5,000', BudgetRange::From1000To5000->label());
        $this->assertSame('$5,000 – $15,000', BudgetRange::From5000To15000->label());
        $this->assertSame('Over $15,000', BudgetRange::Over15000->label());
        $this->assertSame('Not sure yet', BudgetRange::NotSure->label());
    }

    public function test_values_stay_in_sync_with_the_enum_cases(): void
    {
        $this->assertSame(
            ['under-1000', '1000-5000', '5000-15000', 'over-15000', 'not-sure'],
            BudgetRange::values()
        );
    }

    public function test_options_map_each_value_to_its_label(): void
    {
        config(['portfolio.contact.currency' => '$']);

        $options = BudgetRange::options();

        $this->assertCount(5, $options);
        $this->assertSame('Not sure yet', $options['not-sure']);
        $this->assertSame('Under $1,000', $options['under-1000']);
    }
}
