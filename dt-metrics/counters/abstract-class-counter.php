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

    /**
     * @param array $elements
     * @param int   $parent_id
     *
     * @return array
     */
    public static function build_generation_tree( array $elements, $parent_id = 0, $generation = 0 ) {
        $branch = array();
        $generation++;

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parent_id) {
                $children = self::build_generation_tree( $elements, $element['id'], $generation );
                if ($children) {
                    $element['generation'] = $generation;
                    $element['children'] = $children;
                }
                else {
                    $element['generation'] = $generation;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

<<<<<<< HEAD
    public static function count_generation_tree( array $elements, $parent_id = 0, $generation = 0 ) {
        if ( is_null( $elements ) ) {
            $elements = Disciple_Tools_Metrics_Hooks_Base::query_get_group_generations();
        }

        $branch = array();
        $generation++;

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parent_id) {
                $children = self::build_generation_tree( $elements, $element['id'], $generation );
                if ($children) {
                    $element['generation'] = $generation;
                }
                else {
                    $element['generation'] = $generation;
                }
                $branch[] = $element;
            }
        }

        return $branch;
=======
    /**
     * @param array $tree
     *
     * @return array
     */
    public static function get_type_by_level( array $tree ) {
        $groups_at_level = [];
        foreach ( $tree as $key => $level ) {
            $groups_at_level[] = self::get_items_by_level( $level );
        }
        return $groups_at_level;
>>>>>>> master
    }

    /**
     * Get the total counts of streams
     * @param array $tree
     *
     * @return array
     */
    public static function get_stream_count( array $tree ) {
        $streams = [];

        foreach ( $tree as $key => $level ) {
            // stream
            $depth = self::get_array_depth( $level );
            if ( ! isset( $streams[ $depth ] ) ) {
                $streams[ $depth ] = 0;
            }
            $inc = $streams[ $depth ] + 1;
            $streams[ $depth ] = $inc;
        }

        return $streams;
    }

    /**
     * @param array $array
     *
     * @return int
     */
    public static function get_array_depth( array $array ) {
        $i = 1;
        while ( isset( $array['children'] ) && is_array( $array['children'] ) ) {
            $i++;
            foreach ( $array['children'] as $item ) {
                $array = $item;
            }
        }
        return $i;
    }

<<<<<<< HEAD
=======
    /**
     * @param array $array
     *
     * @return array
     */
    public static function get_array_item_levels( array $array ) {
        $item_levels =[];
        $i = 1;

        while ( isset( $array['children'] ) && is_array( $array['children'] ) ) {
            if ( ! isset( $item_levels[ $i ] ) ) {
                $item_levels[ $i ] = 0;
            }
            $item_levels[ $i ] = $item_levels[ $i ] + 1;
            $i++;
            foreach ( $array['children'] as $item ) {
                $array = $item;
            }
        }
        return $item_levels;
    }
>>>>>>> master

    /**
     * @param array $array
     *
     * @return array
     */
    public static function get_items_by_level( array $array ) {
        $item_levels =[];
        $i = 1;

        while ( isset( $array['children'] ) && is_array( $array['children'] ) ) {
            if ( ! isset( $item_levels[ $i ] ) ) {
                $item_levels[ $i ] = 0;
            }

            $item_levels[ $i ] = $array['id'];
            $i++;
            foreach ( $array['children'] as $item ) {
                $array = $item;
            }
        }
        return $item_levels;
    }


}
