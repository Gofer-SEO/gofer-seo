<?php
/**
 * Schema Graph Base Class
 *
 * Acts as the base class for Schema Thing.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph.
 *
 * @since 1.0.0
 *
 * @see Schema Thing
 * @link https://schema.org/Thing
 */
abstract class Gofer_SEO_Graph {

	/**
	 * Schema Slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Schema Name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name;

	// TODO Add Schema properties/content/context object to handled all post/page (post_type, taxonomy, terms, author) data.

	// TODO Add Static Variables to store what Schema IDs are in use. Implement when adding property types for schema.
	// For example, when using property schemas, like imageObject, more than 1 object can reference the same image object.

	/**
	 * Gofer_SEO_Graph Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @throws LogicException Shows which child class variables are missing or empty.
	 */
	public function __construct() {
		$this->slug = strtolower( $this->get_slug() );
		$this->name = $this->get_name();

		if ( ! isset( $this->slug ) || empty( $this->slug ) ) {
			throw new LogicException( 'Class ' . get_class( $this ) . ' property $slug is missing or empty.' );
		}
		if ( ! isset( $this->name ) || empty( $this->name ) ) {
			throw new LogicException( 'Class ' . get_class( $this ) . ' property $name is missing or empty.' );
		}

		$this->add_hooks();
	}

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract protected function get_slug();

	/**
	 * Get Graph Name.
	 *
	 * Intended for frontend use when displaying which schema graphs are available.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract protected function get_name();

	/**
	 * Prepare data.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	abstract protected function prepare();

	/**
	 * Add Hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_hooks() {
		add_action( 'gofer_seo_schema_internal_shortcodes_on', array( $this, 'add_shortcode' ) );
		add_action( 'gofer_seo_schema_internal_shortcodes_off', array( $this, 'remove_shortcode' ) );
	}

	/**
	 * Add Shortcode
	 *
	 * @since 1.0.0
	 */
	public function add_shortcode() {
		add_shortcode( 'gofer_seo_schema_' . $this->slug, array( $this, 'display_json_ld' ) );
	}

	/**
	 * Remove Shortcode
	 *
	 * @since 1.0.0
	 */
	public function remove_shortcode() {
		remove_shortcode( 'gofer_seo_schema_' . $this->slug );
	}

	/**
	 * Display JSON LD
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function display_json_ld() {
		/**
		 * Schema Class's Prepared Data
		 *
		 * Uses class name with hook `gofer_seo_schema_class_data_{CLASS NAME}`.
		 *
		 * TODO Change to `$this->slug`.
		 *
		 * @since 1.0.0
		 *
		 * @param array  Dynamically generated data through inherited schema graphs.
		 */
		$schema_data = apply_filters( 'gofer_seo_schema_class_data_' . get_class( $this ), $this->prepare() );

		// Encode to json string, and remove string type around shortcodes.
		if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
			$schema_data = wp_json_encode( (object) $schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore PHPCompatibility.Constants.NewConstants
		} else {
			// PHP <= 5.3 compatibility.
			$schema_data = wp_json_encode( (object) $schema_data );
			$schema_data = str_replace( '\/', '/', $schema_data );
		}
		// If json encode returned false, set as empty string.
		if ( ! $schema_data ) {
			$schema_data = '';
		}

		return $schema_data;
	}

	/**
	 * Prepare Image Data.
	 *
	 * TODO !?Move/Create schema properties object?!
	 *
	 * @since 1.0.0
	 *
	 * @param array  $image_data See `Gofer_SEO_Graph::get_image_data_defaults()` for details.
	 * @param string $schema_id  Schema reference id.
	 * @return array Image schema. False on failure.
	 */
	protected function prepare_image( $image_data, $schema_id ) {
		if ( empty( $image_data['url'] ) ) {
			return false;
		}

		$rtn_data = array(
			'@type' => 'ImageObject',
			'@id'   => $schema_id,
		);

		// Only use valid variables from defaults.
		foreach ( array_keys( $this->get_image_data_defaults() ) as $key ) {
			if ( ! empty( $image_data[ $key ] ) ) {
				$rtn_data[ $key ] = $image_data[ $key ];
			}
		}

		return $rtn_data;
	}

	/**
	 * Get Image Data Defaults.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_image_data_defaults() {
		return array(
			'url'     => '',
			'width'   => 0,
			'height'  => 0,
			'caption' => '',
		);
	}

	/**
	 * Get Image Data from Site.
	 *
	 * @since 1.0.0
	 *
	 * @uses wp_get_attachment_metadata()
	 * @link https://developer.wordpress.org/reference/functions/wp_get_attachment_metadata/
	 *
	 * @param int $image_id Image ID to retrieve data.
	 * @return array|bool Image data. False on failure.
	 */
	protected function get_site_image_data( $image_id ) {
		if ( ! is_numeric( $image_id ) ) {
			return false;
		}

		// Defaults.
		$rtn_image_data = $this->get_image_data_defaults();

		// Store ID just in case of any other operations, but is not required with schema.
		$rtn_image_data['id']  = intval( $image_id );
		$rtn_image_data['url'] = wp_get_attachment_image_url( $image_id, 'full' );

		$image_meta = wp_get_attachment_metadata( $image_id );
		if ( $image_meta ) {
			$rtn_image_data['width']  = $image_meta['width'];
			$rtn_image_data['height'] = $image_meta['height'];
		}

		$caption = wp_get_attachment_caption( $image_id );
		if ( false !== $caption || ! empty( $caption ) ) {
			$rtn_image_data['caption'] = $caption;
		}

		return $rtn_image_data;
	}

	/**
	 * Get Image Data from User Gravatar.
	 *
	 * @since 1.0.0
	 *
	 * @uses get_avatar_data()
	 * @link https://developer.wordpress.org/reference/functions/get_avatar_data/
	 *
	 * @param int $user_id User ID to retrieve data.
	 * @return array|bool Gravatar image data. False on failure.
	 */
	protected function get_user_image_data( $user_id ) {
		if ( ! is_numeric( $user_id ) ) {
			return false;
		}

		// Defaults.
		$rtn_image_data = $this->get_image_data_defaults();

		if ( get_option( 'show_avatars' ) ) {
			$avatar_data = get_avatar_data( $user_id );
			if ( $avatar_data['found_avatar'] ) {
				$rtn_image_data['url']     = $avatar_data['url'];
				$rtn_image_data['width']   = $avatar_data['width'];
				$rtn_image_data['height']  = $avatar_data['height'];
				$rtn_image_data['caption'] = get_the_author_meta( 'display_name', $user_id );
			}
		}

		return $rtn_image_data;
	}

	/**
	 * Get Featured Image URL.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post See WP_Post for details.
	 * @return false|string
	 */
	protected function get_image_url_from_content( $post ) {
		$image_url = '';

		// Get first image from content.
		if ( ( substr_count( $post->post_content, '<img' ) + substr_count( $post->post_content, '<IMG' ) ) ) {
			if ( class_exists( 'DOMDocument' ) ) {
				$dom = new domDocument();

				// Non-compliant HTML might give errors, so ignore them.
				libxml_use_internal_errors( true );
				$dom->loadHTML( $post->post_content );
				libxml_clear_errors();

				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$dom->preserveWhiteSpace = false;

				$matches = $dom->getElementsByTagName( 'img' );
				foreach ( $matches as $match ) {
					$image_url = $match->getAttribute( 'src' );
				}
			} else {
				preg_match_all( '/<img.*src=([\'"])?(.*?)\\1/', $post->post_content, $matches );
				if ( $matches && isset( $matches[2] ) ) {
					$image_url = $matches[2];
				}
			}
		}

		return $image_url;
	}


	/**
	 * Get Social Profiles from user id.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id
	 * @return array
	 */
	protected function get_user_social_profile_links( $user_id ) {
		$rtn_social_profiles = array();
		$social_sites        = array(
			'facebook',
			'twitter',
		);

		foreach ( $social_sites as $social_site ) {
			$author_social_link = get_the_author_meta( $social_site, $user_id );

			if ( $author_social_link ) {
				$rtn_social_profiles[] = $author_social_link;
			}
		}

		return $rtn_social_profiles;
	}

}
