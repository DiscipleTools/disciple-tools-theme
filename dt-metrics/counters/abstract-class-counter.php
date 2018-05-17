<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter
 */
abstract class Disciple_Tools_Counter_Base
{

    /**
     * Disciple_Tools_Counter constructor.
     */
    public function __construct()
    {
    }

    public static function build_generation_tree( array $elements, $parentId = 0 ) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::build_generation_tree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public static function get_array_depth( array $array  ) {
        $i = 1;
        while( isset( $array['children'] ) && is_array( $array['children'] ) ) {
            $i++;
            foreach( $array['children'] as $item ) {
                $array = $item;
            }
        }
        return $i;
    }
}
