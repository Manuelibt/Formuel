<?php

class Formuel_DB_Test extends WP_UnitTestCase
{
    public function test_table_name_contains_prefix(): void
    {
        $table = Formuel_DB::table_name();
        $this->assertStringContainsString($GLOBALS['wpdb']->prefix, $table);
    }
}
