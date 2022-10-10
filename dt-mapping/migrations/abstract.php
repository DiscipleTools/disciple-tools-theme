<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * The abstract class that represents one migration.
 *
 * Our migration strategy is inspired by Phinx, Laravel and Django:
 *
 * - https://phinx.org/
 * PHP database migration library
 *
 * - https://laravel.com/docs/5.5/migrations
 * PHP framework which includes database migrations
 *
 * - https://docs.djangoproject.com/en/stable/topics/migrations/
 * Python framework which includes database migrations
 *
 * Migrations should be placed in this directory, with names like this:
 *
 *      0000-initial.php
 *      0001-add-table-photos.php
 *      0002-alter-photos-table.php
 *      0003.php
 *
 *  The names must begin with four digits, and they must be in order with no
 *  gaps, starting at 0000. Each file must contain a migration class named
 *  "DT_Mapping_Module_Migration_xxxx", where xxxx is the migration number. For
 *  example:
 *
 *      class DT_Mapping_Module_Migration_0002 extends DT_Mapping_Module_Migration {
 *          // ...
 *      }
 *
 *  See the documentation for DT_Mapping_Module_Migration_Engine.
 *
 */
abstract class DT_Mapping_Module_Migration {

    /**
     * Migrate up to this migration version. Override this method and add code
     * that creates or updates tables as needed.
     *
     * @return void
     */
    abstract public function up();

    /**
     * Migrate down, that is, undo this migration. This should reverse whatever
     * the method `up` does.
     *
     * @return void
     */
    abstract public function down();

    /**
     * Test that this migration has been applied. Override this method and add
     * code that checks that the tables that have been modified are in the
     * correct state. This code may be called before and after migrations. This
     * method should throw an exception to indicate a test failure.
     *
     * We recommend using the utility method test_expected_tables here.
     *
     * @return void
     */
    abstract public function test();

    /**
     * Return an array mapping table names to table definitions. For example:
     *
     *      array(
     *          "{$wpdb->prefix}example" => "CREATE TABLE `{$wpdb->prefix}` (`id` INT NOT NULL);",
     *      );
     *
     *  It is recommended to use this function, as the database migration may
     *  use it to run some sanity checks for you automatically.
     *
     *  @return array
     */
    public function get_expected_tables(): array {
        return array();
    }

    /**
     * @throws \DT_Mapping_Module_Migration_Test_Exception Table $name not as expected, see error log.
     */
    protected function test_expected_tables() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $expected_table ) {
            $got_table = $wpdb->get_var( "SHOW CREATE TABLE `$name`", 1, 0 ); // WPCS: unprepared SQL OK
            $got_table = self::clean_create_query( $got_table );
            $expected_table = self::clean_create_query( $expected_table );
            if ( $got_table !== $expected_table ) {
                error_log( "Got: $got_table\n\nExpected:\n\n$expected_table\n\n" );
                dt_write_log( __METHOD__ . ': ' . $name . ' Failed test' );
                throw new DT_Mapping_Module_Migration_Test_Exception( "Table $name not as expected, see error log" );
            }
        }
    }

    /**
     * Private function that is used to delete some information from "CREATE
     * TABLE" queries, to make them easier to compare.
     *
     * @param string $table_definition
     *
     * @return string
     */
    private static function clean_create_query( string $table_definition ): string {
        $rv = preg_replace( '/^\s*/m', '', strtolower( $table_definition ) );
        $rv = preg_replace( '/\s*$/m', '', $rv );
        $rv = preg_replace( '/\bcollate [^\s]+\b/i', '', $rv );
        $rv = preg_replace( '/\bcollate=[^\s]+\b/i', '', $rv );
        $rv = preg_replace( '/\bengine=innodb\b/', '', $rv );
        $rv = preg_replace( '/\bdefault character set\s/', 'default charset=', $rv );
        $rv = preg_replace( '/\s+,/', ',', $rv );
        $rv = preg_replace( '/;$/', '', $rv );
        $rv = preg_replace( '/\s\s+/', ' ', $rv );
        return $rv;
    }


}

/**
 * Class DT_Mapping_Module_Migration_Test_Exception
 */
class DT_Mapping_Module_Migration_Test_Exception extends Exception {
}
