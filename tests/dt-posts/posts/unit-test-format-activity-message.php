<?php

/**
 * @testdox Disciple_Tools_Posts::format_activity_message
 */
class DT_Posts_Posts_Format_Activity_Message extends WP_UnitTestCase {

    /**
     * @testdox Field Update: Tags - add
     */
    public function test_field_update_tags_add() {
        $message = Disciple_Tools_Posts::format_activity_message( (object) array(
            'action' => 'field_update',
            'meta_key' => 'my_field',
            'meta_value' => 'tag-name',
            ), array(
            'fields' => array(
                'my_field' => array(
                    'type' => 'tags',
                    'name' => 'My Field',
                ),
            ),
        ) );
        // Replace this with some actual testing code.
        $this->assertEquals( 'tag-name added to My Field', $message );
    }
    /**
     * @testdox Field Update: Tags - remove
     */
    public function test_field_update_tags_remove() {
        $message = Disciple_Tools_Posts::format_activity_message( (object) array(
            'action' => 'field_update',
            'meta_key' => 'my_field',
            'meta_value' => 'value_deleted',
            'old_value' => 'old-tag',
            ), array(
            'fields' => array(
                'my_field' => array(
                    'type' => 'tags',
                    'name' => 'My Field',
                ),
            ),
        ) );
        // Replace this with some actual testing code.
        $this->assertEquals( 'old-tag removed from My Field', $message );
    }
}
