<?php
/**
 * Unit tests for the procedural helpers in include/functions.php.
 *
 * Run:  vendor/bin/phpunit --testsuite "Timeclock Suite"
 */

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FunctionsTest extends TestCase
{
    // =========================================================================
    // phonef() — phone number formatter
    // =========================================================================

    /** @test */
    public function phonef_formats_10_digit_number_with_dashes(): void
    {
        $this->assertSame('555-867-5309', phonef('5558675309'));
    }

    /** @test */
    public function phonef_formats_7_digit_number_with_single_dash(): void
    {
        $this->assertSame('867-5309', phonef('8675309'));
    }

    /** @test */
    public function phonef_returns_original_string_for_other_lengths(): void
    {
        // 11-digit, 6-digit, empty — all pass through unchanged
        $this->assertSame('15558675309', phonef('15558675309'));
        $this->assertSame('123456',      phonef('123456'));
        $this->assertSame('',            phonef(''));
    }

    /** @test */
    public function phonef_10_digit_area_code_is_first_segment(): void
    {
        $result = phonef('2025551234');
        $this->assertStringStartsWith('202-', $result);
    }

    /** @test */
    public function phonef_10_digit_last_four_are_last_segment(): void
    {
        $result = phonef('2025551234');
        $this->assertStringEndsWith('-1234', $result);
    }

    /** @test */
    public function phonef_10_digit_produces_two_dashes(): void
    {
        $result = phonef('2025551234');
        $this->assertSame(2, substr_count($result, '-'));
    }

    /** @test */
    public function phonef_7_digit_produces_one_dash(): void
    {
        $result = phonef('5551234');
        $this->assertSame(1, substr_count($result, '-'));
    }

    #[DataProvider('phoneProvider')]
    public function test_phonef_data_driven(string $input, string $expected): void
    {
        $this->assertSame($expected, phonef($input));
    }

    public static function phoneProvider(): array
    {
        return [
            '10-digit standard'    => ['5558675309', '555-867-5309'],
            '7-digit standard'     => ['8675309',    '867-5309'],
            '11-digit passthrough' => ['15558675309','15558675309'],
            'empty passthrough'    => ['',            ''],
            'letters passthrough'  => ['abcdefghij', 'abcdefghij'],
        ];
    }

    // =========================================================================
    // tabs() — indentation helper
    // =========================================================================

    /** @test */
    public function tabs_default_returns_one_tab(): void
    {
        $this->assertSame("\t", tabs());
    }

    /** @test */
    public function tabs_returns_correct_count_of_tabs(): void
    {
        $this->assertSame("\t\t\t", tabs(3));
    }

    /** @test */
    public function tabs_returns_one_tab_for_zero_input(): void
    {
        // 0 is not an int > 0, but is_int(0) is true — str_repeat("\t", 0) = ""
        // The method accepts any int; 0 produces empty string via str_repeat
        $this->assertSame('', tabs(0));
    }

    /** @test */
    public function tabs_falls_back_to_one_tab_for_non_integer_input(): void
    {
        // The method resets $i to 1 when !is_int($i)
        $this->assertSame("\t", tabs('foo'));
        $this->assertSame("\t", tabs(2.5));
        $this->assertSame("\t", tabs(null));
    }

    /** @test */
    public function tabs_returns_string_type(): void
    {
        $this->assertIsString(tabs(4));
    }

    #[DataProvider('tabsProvider')]
    public function test_tabs_data_driven(mixed $input, string $expected): void
    {
        $this->assertSame($expected, tabs($input));
    }

    public static function tabsProvider(): array
    {
        return [
            'default (1)'      => [1,    "\t"],
            'two tabs'         => [2,    "\t\t"],
            'five tabs'        => [5,    "\t\t\t\t\t"],
            'float falls back' => [1.9,  "\t"],
            'string fallback'  => ['2',  "\t"],
        ];
    }

    // =========================================================================
    // countLevels() — relative path depth calculator
    // =========================================================================

    /** @test */
    public function countLevels_returns_empty_string_at_public_root(): void
    {
        // When $dir == PUBLIC_ROOT there are zero extra directory separators,
        // so the count difference is 0 and the result is ""
        $result = countLevels(PUBLIC_ROOT);
        $this->assertSame('', $result);
    }

    /** @test */
    public function countLevels_returns_one_level_up_for_one_subdir(): void
    {
        $subdir = PUBLIC_ROOT . DS . 'admin';
        $result = countLevels($subdir);
        $this->assertSame('../', $result);
    }

    /** @test */
    public function countLevels_returns_two_levels_up_for_two_subdirs(): void
    {
        $subdir = PUBLIC_ROOT . DS . 'admin' . DS . 'reports';
        $result = countLevels($subdir);
        $this->assertSame('../../', $result);
    }

    /** @test */
    public function countLevels_respects_adjust_parameter(): void
    {
        // With adjust=1, one extra level is subtracted
        $subdir = PUBLIC_ROOT . DS . 'admin';
        $result = countLevels($subdir, 1);
        $this->assertSame('', $result);
    }

    /** @test */
    public function countLevels_returns_string(): void
    {
        $this->assertIsString(countLevels(PUBLIC_ROOT));
    }
}
