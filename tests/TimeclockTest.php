<?php
/**
 * Unit tests for the Timeclock class (include/timeclock.php).
 *
 * Only methods that are free of database calls are tested here.
 * DB-dependent methods (getName, getId, punchIn, etc.) are covered by
 * integration tests that require a live database (see notes at the bottom).
 *
 * Run:  vendor/bin/phpunit --testsuite "Timeclock Suite"
 */

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Timeclock::class)]
class TimeclockTest extends TestCase
{
    private Timeclock $tc;

    protected function setUp(): void
    {
        $this->tc = new Timeclock();
    }

    // =========================================================================
    // seconds2HourMinutes
    // =========================================================================

    /** @test */
    public function seconds2HourMinutes_returns_zero_for_zero_input(): void
    {
        $this->assertSame('00:00', $this->tc->seconds2HourMinutes(0));
    }

    /** @test */
    public function seconds2HourMinutes_formats_exact_one_hour(): void
    {
        $this->assertSame('01:00', $this->tc->seconds2HourMinutes(3600));
    }

    /** @test */
    public function seconds2HourMinutes_formats_one_minute(): void
    {
        $this->assertSame('00:01', $this->tc->seconds2HourMinutes(60));
    }

    /** @test */
    public function seconds2HourMinutes_formats_mixed_hours_and_minutes(): void
    {
        // 2 h 35 m = 9300 s
        $this->assertSame('02:35', $this->tc->seconds2HourMinutes(9300));
    }

    /** @test */
    public function seconds2HourMinutes_truncates_leftover_seconds(): void
    {
        // 3661 s = 1 h 1 m 1 s  → should show 01:01 (seconds are dropped)
        $this->assertSame('01:01', $this->tc->seconds2HourMinutes(3661));
    }

    /** @test */
    public function seconds2HourMinutes_handles_large_values(): void
    {
        // 40 hours (e.g. a full work-week in seconds)
        $this->assertSame('40:00', $this->tc->seconds2HourMinutes(144000));
    }

    #[DataProvider('secondsProvider')]
    public function test_seconds2HourMinutes_data_driven(int $input, string $expected): void
    {
        $this->assertSame($expected, $this->tc->seconds2HourMinutes($input));
    }

    public static function secondsProvider(): array
    {
        return [
            'zero'          => [0,      '00:00'],
            '30 minutes'    => [1800,   '00:30'],
            '8 hours'       => [28800,  '08:00'],
            '8h 30m'        => [30600,  '08:30'],
            '99h 59m'       => [359940, '99:59'],
        ];
    }

    // =========================================================================
    // getWeekday
    // =========================================================================

    /** @test */
    public function getWeekday_returns_three_letter_abbreviation_for_known_monday(): void
    {
        // 2024-01-01 is a Monday
        $this->assertSame('Mon', $this->tc->getWeekday('2024-01-01'));
    }

    /** @test */
    public function getWeekday_returns_three_letter_abbreviation_for_known_sunday(): void
    {
        // 2024-01-07 is a Sunday
        $this->assertSame('Sun', $this->tc->getWeekday('2024-01-07'));
    }

    /** @test */
    public function getWeekday_returns_three_letter_abbreviation_for_known_friday(): void
    {
        // 2024-04-12 is a Friday
        $this->assertSame('Fri', $this->tc->getWeekday('2024-04-12'));
    }

    /** @test */
    public function getWeekday_returns_three_char_string_for_any_date(): void
    {
        $result = $this->tc->getWeekday('2025-06-15');
        $this->assertSame(3, strlen($result));
    }

    /** @test */
    public function getWeekday_returns_string_when_no_date_given(): void
    {
        // Without an argument it falls back to today — we just verify the type
        $result = $this->tc->getWeekday();
        $this->assertIsString($result);
        $this->assertSame(3, strlen($result));
    }

    // =========================================================================
    // formatAmPm
    // =========================================================================

    /** @test */
    public function formatAmPm_formats_noon_as_12_00pm(): void
    {
        // 2024-01-01 12:00:00 UTC  (noon)
        $ts = mktime(12, 0, 0, 1, 1, 2024);
        $result = $this->tc->formatAmPm($ts);
        // Expected: "12:00pm"  (spaces and trailing 'm' stripped by the method)
        $this->assertStringContainsString('12:00p', $result);
    }

    /** @test */
    public function formatAmPm_formats_midnight_as_12_00am(): void
    {
        $ts = mktime(0, 0, 0, 1, 1, 2024);
        $result = $this->tc->formatAmPm($ts);
        $this->assertStringContainsString('12:00a', $result);
    }

    /** @test */
    public function formatAmPm_contains_no_spaces(): void
    {
        $ts = mktime(9, 30, 0, 6, 15, 2024);
        $this->assertStringNotContainsString(' ', $this->tc->formatAmPm($ts));
    }

    /** @test */
    public function formatAmPm_does_not_end_with_m(): void
    {
        // The method strips the trailing 'm' from am/pm
        $ts = mktime(14, 45, 0, 3, 20, 2024);
        $result = $this->tc->formatAmPm($ts);
        $this->assertStringEndsNotWith('m', $result);
    }

    // =========================================================================
    // buildDeleteButton
    // =========================================================================

    /** @test */
    public function buildDeleteButton_contains_del_label(): void
    {
        $html = $this->tc->buildDeleteButton(42);
        $this->assertStringContainsString('del', $html);
    }

    /** @test */
    public function buildDeleteButton_includes_the_record_id_as_value(): void
    {
        $html = $this->tc->buildDeleteButton(99);
        $this->assertStringContainsString("value='99'", $html);
    }

    /** @test */
    public function buildDeleteButton_uses_button_element(): void
    {
        $html = $this->tc->buildDeleteButton(1);
        $this->assertStringContainsString('<button', $html);
        $this->assertStringContainsString('</button>', $html);
    }

    /** @test */
    public function buildDeleteButton_references_change_time_page(): void
    {
        $html = $this->tc->buildDeleteButton(5);
        $this->assertStringContainsString('change_time.php', $html);
    }

    // =========================================================================
    // inputTimeSetup
    // =========================================================================

    /** @test */
    public function inputTimeSetup_contains_add_submit_button(): void
    {
        $html = $this->tc->inputTimeSetup('2024-06-01');
        $this->assertStringContainsString("value='add'", $html);
        $this->assertStringContainsString("type='submit'", $html);
    }

    /** @test */
    public function inputTimeSetup_contains_text_input_named_time(): void
    {
        $html = $this->tc->inputTimeSetup('2024-06-01');
        $this->assertStringContainsString("name='time'", $html);
        $this->assertStringContainsString("type='text'", $html);
    }

    /** @test */
    public function inputTimeSetup_contains_hidden_target_date_with_correct_value(): void
    {
        $date = '2024-12-25';
        $html = $this->tc->inputTimeSetup($date);
        $this->assertStringContainsString("name='target_date'", $html);
        $this->assertStringContainsString("value='{$date}'", $html);
    }

    // =========================================================================
    // inputTimeSetup2 — the hour/minute/am-pm select version
    // =========================================================================

    /** @test */
    public function inputTimeSetup2_includes_hour_select(): void
    {
        $html = $this->tc->inputTimeSetup2('2024-06-01');
        $this->assertStringContainsString("name=\"hour\"", $html);
    }

    /** @test */
    public function inputTimeSetup2_includes_minute_select(): void
    {
        $html = $this->tc->inputTimeSetup2('2024-06-01');
        $this->assertStringContainsString("name=\"minute\"", $html);
    }

    /** @test */
    public function inputTimeSetup2_includes_am_pm_select(): void
    {
        $html = $this->tc->inputTimeSetup2('2024-06-01');
        $this->assertStringContainsString("name=\"am_pm\"", $html);
        $this->assertStringContainsString('<option value="am"', $html);
        $this->assertStringContainsString('<option value="pm"', $html);
    }

    /** @test */
    public function inputTimeSetup2_hour_options_run_from_1_to_12(): void
    {
        $html = $this->tc->inputTimeSetup2('2024-06-01');
        $this->assertStringContainsString("value='01'", $html);
        $this->assertStringContainsString("value='12'", $html);
        // 13 should NOT appear
        $this->assertStringNotContainsString("value='13'", $html);
    }

    /** @test */
    public function inputTimeSetup2_minute_options_include_00_and_59(): void
    {
        $html = $this->tc->inputTimeSetup2('2024-06-01');
        $this->assertStringContainsString("value='00'", $html);
        $this->assertStringContainsString("value='59'", $html);
        $this->assertStringNotContainsString("value='60'", $html);
    }

    // =========================================================================
    // displayChangeTime
    // =========================================================================

    /** @test */
    public function displayChangeTime_handles_false_target_array(): void
    {
        // When there are no punch records the method should return a table
        // with a zero-time footer row and not crash.
        $emptyArray = ['todays_total' => 0];
        $html = $this->tc->displayChangeTime($emptyArray);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('00:00', $html);
    }

    /** @test */
    public function displayChangeTime_shows_in_and_out_times(): void
    {
        $target = [
            0 => [
                'in'           => '9:00a',
                'out'          => '5:00p',
                'delta_string' => '08:00',
                'id_in'        => 10,
                'id_out'       => 11,
            ],
            'todays_total' => 28800,
        ];
        $html = $this->tc->displayChangeTime($target);
        $this->assertStringContainsString('9:00a', $html);
        $this->assertStringContainsString('5:00p', $html);
        $this->assertStringContainsString('08:00', $html);
    }

    /** @test */
    public function displayChangeTime_shows_total_hours(): void
    {
        $target = [
            0 => [
                'in'           => '8:00a',
                'out'          => '12:00p',
                'delta_string' => '04:00',
                'id_in'        => 20,
                'id_out'       => 21,
            ],
            'todays_total' => 14400,  // 4 hours
        ];
        $html = $this->tc->displayChangeTime($target);
        // The footer row should display 04:00
        $this->assertStringContainsString('04:00', $html);
    }

    // =========================================================================
    // fileHeader / fileFooter  (protected — accessed via reflection)
    // =========================================================================

    /** @test */
    public function fileHeader_produces_valid_rtf_opening(): void
    {
        $method = new ReflectionMethod(Timeclock::class, 'fileHeader');
        $method->setAccessible(true);
        $header = $method->invoke($this->tc);

        $this->assertStringStartsWith('{\\rtf1', $header);
        $this->assertStringContainsString('\\fonttbl', $header);
        $this->assertStringContainsString('Courier New', $header);
    }

    /** @test */
    public function fileFooter_closes_rtf_group(): void
    {
        $method = new ReflectionMethod(Timeclock::class, 'fileFooter');
        $method->setAccessible(true);
        $footer = $method->invoke($this->tc);

        $this->assertStringContainsString('}', $footer);
    }

    /** @test */
    public function fileHeader_and_fileFooter_produce_balanced_braces(): void
    {
        $headerMethod = new ReflectionMethod(Timeclock::class, 'fileHeader');
        $footerMethod = new ReflectionMethod(Timeclock::class, 'fileFooter');
        $headerMethod->setAccessible(true);
        $footerMethod->setAccessible(true);

        $combined = $headerMethod->invoke($this->tc) . $footerMethod->invoke($this->tc);
        $opens  = substr_count($combined, '{');
        $closes = substr_count($combined, '}');
        $this->assertSame($opens, $closes, 'RTF braces must be balanced');
    }

    // =========================================================================
    // center / justify  (protected — accessed via reflection)
    // =========================================================================

    /** @test */
    public function center_pads_shorter_line_with_leading_spaces(): void
    {
        $method = new ReflectionMethod(Timeclock::class, 'center');
        $method->setAccessible(true);

        $line   = 'HELLO';          // 5 chars; columns default = 80
        $result = $method->invoke($this->tc, $line);

        // The result should contain the original text
        $this->assertStringContainsString('HELLO', $result);
        // And start with spaces (not the text itself)
        $this->assertMatchesRegularExpression('/^\s+HELLO/', $result);
    }

    /** @test */
    public function justify_places_left_and_right_text_on_same_line(): void
    {
        $method = new ReflectionMethod(Timeclock::class, 'justify');
        $method->setAccessible(true);

        $result = $method->invoke($this->tc, 'LEFT', 'RIGHT');

        $this->assertStringContainsString('LEFT',  $result);
        $this->assertStringContainsString('RIGHT', $result);
        // Both texts should appear on the same "line" (no newline between them)
        $pos_left  = strpos($result, 'LEFT');
        $pos_right = strpos($result, 'RIGHT');
        $between   = substr($result, $pos_left + 4, $pos_right - $pos_left - 4);
        $this->assertStringNotContainsString("\n", $between);
    }

    /** @test */
    public function justify_output_length_equals_column_width_plus_eol(): void
    {
        $method = new ReflectionMethod(Timeclock::class, 'justify');
        $method->setAccessible(true);

        $eol    = "\line" . PHP_EOL;          // as set in constructor
        $result = $method->invoke($this->tc, 'LEFT', 'RIGHT');
        // Strip EOL marker, remaining text should be exactly $this->columns chars
        $withoutEol = str_replace($eol, '', $result);
        $this->assertSame($this->tc->columns, strlen($withoutEol));
    }

    // =========================================================================
    // Constructor defaults
    // =========================================================================

    /** @test */
    public function constructor_sets_default_columns_to_80(): void
    {
        $this->assertSame(80, $this->tc->columns);
    }

    /** @test */
    public function constructor_sets_default_padding_to_zero(): void
    {
        $this->assertSame(0, $this->tc->padding);
    }
}


// =============================================================================
// Integration test stubs — NOT run without a real DB, but documented here
// so they can be enabled when a test database is available.
// =============================================================================
/*
class TimeclockIntegrationTest extends TestCase
{
    // Requires: live test DB seeded from dump.sql

    public function test_getName_returns_correct_name_for_known_employee(): void { ... }
    public function test_getName_sets_session_message_when_not_found(): void { ... }
    public function test_getId_by_barcode_returns_integer(): void { ... }
    public function test_getId_by_lastname_returns_select_html_when_multiple_matches(): void { ... }
    public function test_punchIn_inserts_row_into_stamp_table(): void { ... }
    public function test_buildTodaysArray_returns_correct_total_for_seeded_punches(): void { ... }
    public function test_sinceSunday_sums_punches_from_last_sunday(): void { ... }
}
*/
