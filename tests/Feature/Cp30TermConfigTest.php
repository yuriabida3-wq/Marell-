<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-30: term and year for fee payments come from config, not hardcoded.
 */
class Cp30TermConfigTest extends TestCase
{
    private function controller(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/PaymentController.php'));
    }

    public function test_config_has_current_term_and_year(): void
    {
        $this->assertSame('Term 1', config('services.current_term'));
        $this->assertArrayHasKey('current_year', config('services'));
    }

    public function test_controller_no_longer_hardcodes_term_1(): void
    {
        $this->assertStringNotContainsString("'term'       => 'Term 1',", $this->controller());
    }

    public function test_controller_reads_term_from_config(): void
    {
        $this->assertStringContainsString(
            "'term'       => (string) config('services.current_term', 'Term 1'),",
            $this->controller()
        );
    }

    public function test_controller_reads_year_from_config_with_fallback(): void
    {
        $src = $this->controller();
        $this->assertStringContainsString("config('services.current_year') ?: date('Y')", $src);
    }

    public function test_env_example_documents_the_keys(): void
    {
        $src = (string) file_get_contents(base_path('.env.example'));
        $this->assertStringContainsString('CURRENT_TERM=', $src);
        $this->assertStringContainsString('CURRENT_YEAR=', $src);
    }
}