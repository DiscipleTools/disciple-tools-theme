<?php
/**
 * Creates a new capability object.  This is an extension of the core `get_capability()` functionality.  It's
 * just been beefed up a bit to provide more useful info for our plugin.
 *
 * @package    Members
 */

/**
 * Capability class.
 *
 * @since  0.1.0
 * @access public
 */
class Disciple_Tools_Capability {

    /**
     * The capability/slug.
     * @access public
     * @var    string
     */
    public $slug = '';

    /**
     * The capability name.
     * @access public
     * @var    string
     */
    public $name = '';

    /**
     * Where did the capability come from?
     * @var string
     */
    public $source = '';

    /**
     * A text description of the capability
     * @var string
     */
    public $description = '';

    /**
     * Return the capability string in attempts to use the object as a string.
     * @access public
     * @return string
     */
    public function __toString() {
        return $this->slug;
    }

    /**
     * Creates a new capability object.
     *
     * @param $capability
     * @param string $source
     * @param string $name
     * @param string $description
     */
    public function __construct( $capability, $source, $name = '', $description = '' ) {
        $this->slug = $capability;
        $this->name = $name;
        $this->source = $source;
        $this->description = $description;
    }

    /**
     * Get the capability as an array
     * @return array
     */
    public function to_array() {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'source' => $this->source
        ];
    }
}
