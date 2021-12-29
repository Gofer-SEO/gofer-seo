<?php
/**
 * The Social Media class.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module_Social_Media
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module_Social_Media extends Gofer_SEO_Module {

	/**
	 * Gofer_SEO_Module_Social_Media constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		parent::load();

		if ( ! is_admin() ) {
			if ( ! wp_doing_ajax() ) {
				$gofer_seo_options = Gofer_SEO_Options::get_instance();

				add_action( 'gofer_seo_wp_head', array( $this, 'add_meta' ), 5 );
				// Add social meta to AMP plugin.
				if ( apply_filters( 'gofer_seo_enable_amp_social_meta', true ) ) {
					add_action( 'amp_post_template_head', array( $this, 'add_meta' ), 12 );
				}

				if ( $gofer_seo_options->options['enable_modules']['schema_graph'] ) {
					add_filter( 'language_attributes', array( $this, 'add_attributes' ) );
				}
			}
		}

		// Avoid having duplicate meta tags.
		add_filter( 'jetpack_enable_open_graph', '__return_false' );
		add_filter( 'user_contactmethods', array( $this, 'add_contact_methods' ) );

		// Force refresh of Facebook cache.
		add_action( 'post_updated', array( $this, 'force_fb_refresh_update' ), 10, 3 );
		add_action( 'transition_post_status', array( $this, 'force_fb_refresh_transition' ), 10, 3 );
	}

	/**
	 * Initialize Module.
	 *
	 * Mainly used for adding action/filter hooks.
	 * There may be some function/method calls, but avoid adding code with operations/processes.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		parent::init();
	}

	/**
	 * Add Contact Methods.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/hooks/user_contactmethods/
	 *
	 * @param array $contact_methods Array of contact method labels keyed by contact method.
	 * @return array
	 */
	function add_contact_methods( $contact_methods ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( $gofer_seo_options->options['modules']['social_media']['fb_use_post_author_fb_url'] ) {
			$contact_methods['facebook'] = 'Facebook URL';
		}
		if ( $gofer_seo_options->options['modules']['social_media']['twitter_use_post_author_twitter_username'] ) {
			/* translators: %s is Twitter's name. */
			$contact_methods['twitter'] = sprintf( __( '%s Username', 'gofer-seo' ), 'Twitter' );
		}

		$contact_methods['linkedin']   = 'LinkedIn URL';
		$contact_methods['pinterest']  = 'Pinterest URL';
		$contact_methods['instagram']  = 'Instagram URL';
		$contact_methods['myspace']    = 'MySpace URL';
		$contact_methods['tumblr']     = 'Tumblr URL';
		$contact_methods['youtube']    = 'YouTube URL';
		$contact_methods['soundcloud'] = 'SoundCloud URL';

		return $contact_methods;
	}

	/**
	 * Face Facebook (OpenGraph) Refresh on Transition.
	 *
	 * Forces FaceBook OpenGraph to refresh its cache when a post is changed.
	 *
	 * @since 1.0.0
	 *
	 * @see https://developers.facebook.com/docs/sharing/opengraph/using-objects#update
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	function force_fb_refresh_transition( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status ) {
			return;
		}
		if ( 'future' !== $old_status ) {
			return;
		}
		$gofer_seo_options  = Gofer_SEO_Options::get_instance();
		$current_post_type  = get_post_type();
		$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['social_media']['enable_post_types'] ) );
		if ( ! in_array( $current_post_type, $enabled_post_types, true ) ) {
			return;
		}

		$post_url   = gofer_seo_decode_url( get_permalink( $post->ID ) );
		$html_query = http_build_query(
			array(
				'id'     => $post_url,
				'scrape' => true,
			)
		);
		$endpoint = sprintf(
			'https://graph.facebook.com/?%s',
			$html_query
		);
		wp_remote_post( $endpoint, array( 'blocking' => false ) );
	}

	/**
	 * Forces FaceBook (OpenGraph) Refresh on Update.
	 *
	 * @since 1.0.0
	 *
	 * @see https://developers.facebook.com/docs/sharing/opengraph/using-objects#update
	 *
	 * @param int     $post_id    Post ID.
	 * @param WP_Post $post_after Post object following the update.
	 */
	function force_fb_refresh_update( $post_id, $post_after ) {
		if ( 'publish' !== $post_after->post_status ) {
			return;
		}
		$gofer_seo_options  = Gofer_SEO_Options::get_instance();
		$current_post_type  = get_post_type();
		$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['social_media']['enable_post_types'] ) );
		if ( ! in_array( $current_post_type, $enabled_post_types, true ) ) {
			return;
		}

		$post_url   = gofer_seo_decode_url( get_permalink( $post_id ) );
		$html_query = http_build_query(
			array(
				'id'     => $post_url,
				'scrape' => true,
			)
		);
		$endpoint = sprintf(
			'https://graph.facebook.com/?%s',
			$html_query
		);
		wp_remote_post( $endpoint, array( 'blocking' => false ) );
	}

	/**
	 * Add Lang Attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output A space-separated list of language attributes.
	 * @return string
	 */
	function add_attributes( $output ) {
		$attributes = apply_filters( 'gofer_seo_lang_attributes', array( 'prefix="og: https://ogp.me/ns#"' ) );

		foreach ( $attributes as $attr ) {
			if ( strpos( $output, $attr ) === false ) {
				$output .= sprintf( '%s%s ', "\n\t", $attr );
			}
		}

		return $output;
	}

	/**
	 * Add Meta.
	 *
	 * @since 1.0.0

	 * @global WP_Query $wp_query WP_Query global instance.
	 *
	 * @return void
	 */
	function add_meta() {
		global $wp_query;
		$gofer_seo_options  = Gofer_SEO_Options::get_instance();
		$context            = Gofer_SEO_Context::get_instance();
		$post               = get_post();
		$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['social_media']['enable_post_types'] ) );
		/**
		 * @var Gofer_SEO_Module_General $gofer_seo_module_general
		 */
		$gofer_seo_module_general = Gofer_SEO::get_instance()->module_loader->get_loaded_module( 'Gofer_SEO_Module_General' );

		$title           = '';
		$description     = '';
		$image           = '';
		$image_url       = '';
		$image_width     = '';
		$image_height    = '';
		$image_mime_type = '';
		$video           = '';
		$video_url       = '';
		$video_width     = '';
		$video_height    = '';
		$video_mime_type = '';
		$tag             = '';
		$fb_object_type  = 'website';

		switch ( $context->context_type ) {
			case 'WP_Post':
				$post = Gofer_SEO_Context::get_object( $context->context_type, $context->context_key );
				if ( 'posts_page' === Gofer_SEO_Context::get_is() ) {
					break;
				} elseif ( ! in_array( $post->post_type, $enabled_post_types, true ) ) {
					return;
				}

				$gofer_seo_post = new Gofer_SEO_Post( $post );

				// Title.
				if ( ! empty( $gofer_seo_post->meta['modules']['social_media']['title'] ) ) {
					$title = $gofer_seo_post->meta['modules']['social_media']['title'];
				} elseif ( ! empty( $gofer_seo_post->meta['modules']['general']['title'] ) ) {
					$title = $gofer_seo_post->meta['modules']['general']['title'];
				} else {
					$title = get_the_title();
				}

				// Description.
				if ( ! empty( $gofer_seo_post->meta['modules']['social_media']['description'] ) ) {
					$description = $gofer_seo_post->meta['modules']['social_media']['description'];
				} elseif ( ! empty( $gofer_seo_post->meta['modules']['general']['description'] ) ) {
					$description = $gofer_seo_post->meta['modules']['general']['description'];
				} elseif ( ! post_password_required( $post ) ) {
					if ( $gofer_seo_options->options['modules']['social_media']['generate_description']['enable_generator'] ) {
						if (
								! empty( $post->post_excerpt ) &&
								$gofer_seo_options->options['modules']['social_media']['generate_description']['use_excerpt']
						) {
							$description = $post->post_excerpt;
						} elseif (
								! empty( $post->post_content ) &&
								$gofer_seo_options->options['modules']['social_media']['generate_description']['use_content']
						) {
							$description = $post->post_content;
						}
					}
				}

				$image        = $gofer_seo_post->meta['modules']['social_media']['image'];
				$image_width  = $gofer_seo_post->meta['modules']['social_media']['image_width'];
				$image_height = $gofer_seo_post->meta['modules']['social_media']['image_height'];

				$video        = $gofer_seo_post->meta['modules']['social_media']['video'];
				$video_width  = $gofer_seo_post->meta['modules']['social_media']['video_width'];
				$video_height = $gofer_seo_post->meta['modules']['social_media']['video_height'];

				if ( 'posts_page' !== Gofer_SEO_Context::get_is() ) {
					// FB Object Type.
					if ( 'default' !== $gofer_seo_post->meta['modules']['social_media']['facebook']['object_type'] ) {
						$fb_object_type = $gofer_seo_post->meta['modules']['social_media']['facebook']['object_type'];
					} elseif ( in_array( $post->post_type, $enabled_post_types, true ) ) {
						$fb_object_type = $gofer_seo_options->options['modules']['social_media']['fb_post_type_settings'][ $post->post_type ]['fb_object_type'];
					}
					if ( empty( $fb_object_type ) ) {
						$fb_object_type = 'article';
					}

					if ( 'article' === $fb_object_type ) {
						if ( ! empty( $gofer_seo_post->meta['modules']['social_media']['facebook']['article_section'] ) ) {
							$section = $gofer_seo_post->meta['modules']['social_media']['facebook']['article_section'];
						}
						if ( ! empty( $gofer_seo_post->meta['modules']['social_media']['keywords'] ) ) {
							$tag = $gofer_seo_post->meta['modules']['social_media']['keywords'];
						}
						if ( ! empty( $gofer_seo_options->options['modules']['social_media']['fb_publisher_fb_url'] ) ) {
							$publisher = $gofer_seo_options->options['modules']['social_media']['fb_publisher_fb_url'];
						}

						if ( ! empty( $post ) ) {
							if ( isset( $post->post_author ) && $gofer_seo_options->options['modules']['social_media']['fb_use_post_author_fb_url'] ) {
								$author = get_the_author_meta( 'facebook', $post->post_author );
							}

							if ( isset( $post->post_date_gmt ) ) {
								$published_time = wp_date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_date_gmt ) );
							}

							if ( isset( $post->post_modified_gmt ) ) {
								$modified_time = wp_date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_modified_gmt ) );
							}

							if ( $gofer_seo_options->options['modules']['social_media']['generate_keywords']['enable_generator'] ) {
								if ( $gofer_seo_options->options['modules']['social_media']['generate_keywords']['use_keywords'] ) {
									$keywords = $gofer_seo_module_general->get_the_keywords();

									if ( ! empty( $keywords ) && ! empty( $tag ) ) {
										$tag .= ',' . $keywords;
									} elseif ( empty( $tag ) ) {
										$tag = $keywords;
									}
								}

								$keywords = $tag;
								$traverse   = array();
								$keywords_i = str_replace( '"', '', $keywords );
								if ( isset( $keywords_i ) && ! empty( $keywords_i ) ) {
									$traverse = explode( ',', $keywords_i );
								}
								$tag = $traverse;

								$keywords_arr = array();
								$generate_use_taxonomies = $gofer_seo_options->options['modules']['social_media']['generate_keywords']['use_taxonomies'];
								$generate_use_taxonomies = array_keys( array_filter( $generate_use_taxonomies ) );
								if ( $post instanceof WP_Post ) {
									foreach ( $generate_use_taxonomies as $use_taxonomy ) {
										$terms = get_the_terms( $post, $use_taxonomy );
										foreach ( $terms as $term ) {
											$keywords_arr[] = trim( Gofer_SEO_PHP_Functions::strtolower( $term->name ) );
										}
									}
								}
								$tag = array_merge( $tag, $keywords_arr );
							}

							if ( ! empty( $tag ) ) {
								// FIXME Restore deleted method.
								//$tag = $gofer_seo_module_general->clean_keyword_list( $tag );
							}
						}
					}
				}
				break;

			case 'WP_Term':
				if ( ! empty( $post ) && ! in_array( $post->post_type, $enabled_post_types, true ) ) {
					return;
				}
				$wp_term        = Gofer_SEO_Context::get_object( $context->context_type, $context->context_key );
				$gofer_seo_term = new Gofer_SEO_Term( $wp_term );

				// Title.
				if ( ! empty( $gofer_seo_term->meta['modules']['social_media']['title'] ) ) {
					$title = $gofer_seo_term->meta['modules']['social_media']['title'];
				} elseif ( ! empty( $gofer_seo_term->meta['modules']['general']['title'] ) ) {
					$title = $gofer_seo_term->meta['modules']['general']['title'];
				} else {
					$title = get_the_title();
				}

				// Description.
				if ( ! empty( $gofer_seo_term->meta['modules']['social_media']['description'] ) ) {
					$description = $gofer_seo_term->meta['modules']['social_media']['description'];
				} elseif ( ! empty( $gofer_seo_term->meta['modules']['general']['description'] ) ) {
					$description = $gofer_seo_term->meta['modules']['general']['description'];
				} elseif ( ! post_password_required( $post ) ) {
					$description = get_queried_object()->description;
				}

				$image        = $gofer_seo_term->meta['modules']['social_media']['image'];
				$image_width  = $gofer_seo_term->meta['modules']['social_media']['image_width'];
				$image_height = $gofer_seo_term->meta['modules']['social_media']['image_height'];

				$video        = $gofer_seo_term->meta['modules']['social_media']['video'];
				$video_width  = $gofer_seo_term->meta['modules']['social_media']['video_width'];
				$video_height = $gofer_seo_term->meta['modules']['social_media']['video_height'];

				// FB Object Type.
				$fb_object_type = $gofer_seo_term->meta['modules']['social_media']['facebook']['object_type'];

				if ( 'article' === $fb_object_type ) {
					if ( ! empty( $gofer_seo_term->meta['modules']['social_media']['facebook']['article_section'] ) ) {
						$section = $gofer_seo_term->meta['modules']['social_media']['facebook']['article_section'];
					}

					if ( ! empty( $gofer_seo_term->meta['modules']['social_media']['facebook']['twitter']['card_type'] ) ) {
						$tag = $gofer_seo_term->meta['modules']['social_media']['facebook']['twitter']['card_type'];
					}

					if ( ! empty( $gofer_seo_options->options['modules']['social_media']['fb_publisher_fb_url'] ) ) {
						$publisher = $gofer_seo_options->options['modules']['social_media']['fb_publisher_fb_url'];
					}

					if ( ! empty( $post ) ) {
						if ( isset( $post->post_author ) && $gofer_seo_options->options['modules']['social_media']['fb_use_post_author_fb_url'] ) {
							$author = get_the_author_meta( 'facebook', $post->post_author );
						}

						if ( isset( $post->post_date_gmt ) ) {
							$published_time = wp_date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_date_gmt ) );
						}
						if ( isset( $post->post_modified_gmt ) ) {
							$modified_time = wp_date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_modified_gmt ) );
						}
					}
				}

				if ( empty( $fb_object_type ) ) {
					// check if the post type's object type is set.
					if ( isset( $gofer_seo_options->options['modules']['social_media']['fb_post_type_settings'][ $post->post_type ]['fb_object_type'] ) ) {
						$fb_object_type = $gofer_seo_options->options['modules']['social_media']['fb_post_type_settings'][ $post->post_type ]['fb_object_type'];
					} elseif ( in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
						$fb_object_type = 'article';
					}
				}
				break;
		}

		/* *** SITE/DEFAULT *** */
		if ( empty( $title ) ) {
			$title = $gofer_seo_module_general->wp_title();
		}
		if ( empty( $title ) ) {
			if ( $gofer_seo_options->options['modules']['social_media']['enable_site_title'] ) {
				$title = $gofer_seo_options->options['modules']['social_media']['site_title'];
			} else {
				$title = get_bloginfo( 'name' );
			}
		}

		// Pagination.
		if ( ! $gofer_seo_options->options['modules']['general']['show_paginate_descriptions'] ) {
			$first_page = false;
			if ( 2 > gofer_seo_the_page_number() ) {
				$first_page = true;
			}
		} else {
			$first_page = true;
		}

		if ( empty( $description ) && $first_page ) {
			$description = $gofer_seo_module_general->get_the_description( $post );

			if ( ! empty( $post ) && ! post_password_required( $post ) ) {
				if ( ! empty( $post->post_content ) ) {
					if ( $gofer_seo_options->options['modules']['social_media']['generate_description']['enable_generator'] ) {
						$description = $gofer_seo_module_general->trim_excerpt_without_filters(
							$gofer_seo_module_general->internationalize( preg_replace( '/\s+/', ' ', $post->post_content ) ),
							200
						);
					}
				} elseif ( ! empty( $post->post_excerpt ) ) {
					$description = $gofer_seo_module_general->trim_excerpt_without_filters(
						$gofer_seo_module_general->internationalize( preg_replace( '/\s+/', ' ', $post->post_excerpt ) ),
						200
					);
				}
			}

			if ( empty( $description ) ) {
				if ( $gofer_seo_options->options['modules']['social_media']['enable_site_description'] ) {
					$description = $gofer_seo_options->options['modules']['social_media']['site_description'];
				} else {
					$description = get_bloginfo( 'description' );
				}
			}
		}

		/* *** HANDLE IMAGES *** */
		// Add user supplied default image.
		if ( empty( $image ) ) {
			$image = $this->get_image_by_source();
		}
		if ( empty( $image ) ) {
			if ( ! empty( $gofer_seo_options->options['modules']['social_media']['site_image'] ) ) {
				$image = $gofer_seo_options->options['modules']['social_media']['site_image'];
			} elseif ( ! empty( $gofer_seo_options->options['modules']['social_media']['default_image'] ) ) {
				$image        = $gofer_seo_options->options['modules']['social_media']['default_image'];
				$image_width  = $gofer_seo_options->options['modules']['social_media']['default_image_width'];
				$image_height = $gofer_seo_options->options['modules']['social_media']['default_image_height'];
			}
			if ( GOFER_SEO_IMAGES_URL . 'default-user-image.png' === $image ) {
				$theme_custom_logo = get_theme_mod( 'custom_logo' );
				if ( ! empty( $theme_custom_logo ) ) {
					$image = $theme_custom_logo;
				}
			}
		}

		if ( is_numeric( $image ) ) {
			$image_data = image_get_intermediate_size( $image );
			if ( ! $image_data ) {
				$image_data = image_get_intermediate_size( $image, 'full' );
			}

			if ( $image_data ) {
				$image_url       = $image_data['url'];
				$image_width     = $image_data['width'];
				$image_height    = $image_data['height'];
				$image_mime_type = $image_data['mime-type'];
			}
		} elseif ( ! empty( $image ) && is_string( $image ) ) {
			$image_url = $image;
		}

		/* *** HANDLE VIDEO *** */
		/* TODO Add Video to Social-Media settings.
		if ( empty( $video ) ) {
			$video        = $gofer_seo_options->options['modules']['social_media']['video'];
			$video_width  = $gofer_seo_options->options['modules']['social_media']['video_width'];
			$video_height = $gofer_seo_options->options['modules']['social_media']['video_height'];
		} */

		if ( is_numeric( $video ) ) {
			$video_data = image_get_intermediate_size( $video, 'full' );

			if ( $video_data ) {
				$video_url       = $video_data['url'];
				$video_width     = $video_data['width'];
				$video_height    = $video_data['height'];
				$video_mime_type = $video_data['mime-type'];
			}
		} else {
			$video_url = $video;
		}

		/* *** HANDLE TWITTER CARD *** */
		$twitter_card      = 'summary';
		$twitter_site      = '';
		$twitter_creator   = '';
		$twitter_thumbnail = '';

		if ( ! empty( $gofer_seo_options->options['modules']['social_media']['image_source'] ) ) {
			$twitter_card = $gofer_seo_options->options['modules']['social_media']['image_source'];
		}

		if ( ! empty( $gofer_seo_post->meta['modules']['social_media']['twitter']['card_type'] ) ) {
			$twitter_card = $gofer_seo_post->meta['modules']['social_media']['twitter']['card_type'];
		}

		// Support for changing legacy Twitter card-type photo to summary large image.
		if ( 'photo' === $twitter_card ) {
			$twitter_card = 'summary_large_image';
		}

		if ( ! empty( $gofer_seo_options->options['modules']['social_media']['twitter_username'] ) ) {
			$twitter_site = $gofer_seo_options->options['modules']['social_media']['twitter_username'];
			$twitter_site = $this->prepare_twitter_username( $twitter_site );
		}

		if (
				! empty( $post ) &&
				isset( $post->post_author ) &&
				$gofer_seo_options->options['modules']['social_media']['twitter_use_post_author_twitter_username']
		) {
			$twitter_creator = get_the_author_meta( 'twitter', $post->post_author );
			$twitter_creator = $this->prepare_twitter_username( $twitter_creator );
		}

		if ( ! empty( $gofer_seo_post->meta['modules']['social_media']['twitter']['image'] ) ) {
			// Set Twitter image from custom.
			$twitter_thumbnail = set_url_scheme( $gofer_seo_post->meta['modules']['social_media']['twitter']['image'] );
		} elseif ( ! empty( $image_url ) ) {
			// Default Twitter image if custom isn't set.
			$twitter_thumbnail = $image_url;
		}

		// Run Shortcodes.
		if ( $gofer_seo_options->options['modules']['social_media']['enable_title_shortcodes'] ) {
			$title = gofer_seo_do_shortcodes( $title );
		}

		if ( ! empty( $description ) ) {
			$description = $gofer_seo_module_general->internationalize( preg_replace( '/\s+/', ' ', $description ) );
			if ( $gofer_seo_options->options['modules']['social_media']['enable_description_shortcodes'] ) {
				$description = gofer_seo_do_shortcodes( $description );
			}
			if ( $gofer_seo_options->options['modules']['social_media']['generate_description']['enable_generator'] ) {
				$description = $gofer_seo_module_general->trim_excerpt_without_filters( $description, 200 );
			} else {
				// User input still needs to be run through this function to strip tags.
				$description = $gofer_seo_module_general->trim_excerpt_without_filters( $description, 99999 );
			}
		}

		$url       = $context->get_canonical_url();
		$site_name = $gofer_seo_options->options['modules']['social_media']['site_name'];
		if ( empty( $site_name ) ) {
			$site_name = get_bloginfo( 'name' );
		}

		/* Data Validation */
		$title       = Gofer_SEO_PHP_Functions::substr( $title, 0, 70 );
		$description = Gofer_SEO_PHP_Functions::substr( $description, 0, 200 );

		$site_name   = wp_strip_all_tags( esc_attr( $site_name ) );
		$title       = trim( wp_strip_all_tags( esc_attr( $title ) ) );
		$description = trim( wp_strip_all_tags( esc_attr( $description ) ) );

		if ( ! empty( $image_url ) ) {
			$image_url = esc_url( $image_url );
			$image_url = set_url_scheme( $image_url );
		}

		/* *** COLLECT DATA *** */
		/* ** FACEBOOK ** */
		// https://developers.facebook.com/docs/sharing/webmasters/
		$meta_facebook = array(
			'og:type'                => $fb_object_type,
			'og:title'               => $title,
			'og:description'         => $description,
			'og:url'                 => $url,
			'og:site_name'           => $site_name,
			'og:image'               => $image_url,
			'og:image:width'         => $image_width,
			'og:image:height'        => $image_height,
			'og:image:type'          => $image_mime_type,
			'og:video'               => $video_url,
			'og:video:width'         => $video_width,
			'og:video:height'        => $video_height,
			'og:video:type'          => $video_mime_type,
			//'og:locale'              => get_locale(),

			'fb:admins'              => $gofer_seo_options->options['modules']['social_media']['fb_admin_id'],
			'fb:app_id'              => $gofer_seo_options->options['modules']['social_media']['fb_app_id'],

			'article:section'        => isset( $section ) ? $section : '',
			'article:tag'            => $tag,
			'article:published_time' => isset( $published_time ) ? $published_time : '',
			'article:modified_time'  => isset( $modified_time ) ? $modified_time : '',
			'article:author'         => isset( $author ) ? $author : '',
			'article:publisher'      => isset( $publisher ) ? $publisher : '',
		);
		if ( is_ssl() ) {
			$meta_facebook += array( 'og:image:secure_url' => $image_url );
			$meta_facebook += array( 'og:video:secure_url' => $video_url );
		}

		/* ** TWITTER ** */
		// https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards
		$meta_twitter = array(
			'twitter:card'        => $twitter_card,
			'twitter:site'        => $twitter_site,
			'twitter:title'       => $title,
			'twitter:description' => $description,

			// summary, summary_large_image, player
			'twitter:image'       => $twitter_thumbnail,
			//'twitter:image:alt'   => '',

			// summary_large_image
			'twitter:creator'     => $twitter_creator,

			// player
			// 'twitter:player'        => '',
			// 'twitter:player:width'  => '',
			// 'twitter:player:height' => '',
		);

		$allowed_html = array(
			'meta' => array(
				'name'     => true,
				'property' => true,
				'content'  => true,
			),
		);

		/* *** RENDER DATA *** */
		echo gofer_seo_esc_head( $this->convert_facebook_meta_arr_to_html( $meta_facebook ) );
		echo gofer_seo_esc_head( $this->convert_twitter_meta_arr_to_html( $meta_twitter ) );
	}

	/**
	 * Get Facebook Meta HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta Property => Content.
	 * @return string
	 */
	public function convert_facebook_meta_arr_to_html( $meta ) {
		$meta_html = '';

		foreach ( $meta as $k1_meta_property => $v1_meta_content ) {
			if ( ! empty( $v1_meta_content ) ) {
				if ( ! is_array( $v1_meta_content ) ) {
					$v1_meta_content = array( $v1_meta_content );
				}

				if ( 'fb:admins' === $k1_meta_property ) {
					// Trim spaces then turn comma-separated values into an array.
					$fb_admins = explode( ',', str_replace( ' ', '', $v1_meta_content[0] ) );

					foreach ( $fb_admins as $fb_admin ) {
						$meta_html .= sprintf(
							'<meta property="%s" content="%s" />%s',
							$k1_meta_property,
							$fb_admin,
							"\n"
						);
					}
				} else {
					foreach ( $v1_meta_content as $f ) {
						$meta_html .= sprintf(
							'<meta property="%s" content="%s" />%s',
							$k1_meta_property,
							esc_attr( $f ),
							"\n"
						);
					}
				}
			}
		}

		return $meta_html;
	}

	/**
	 * Get Twitter Meta HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta Name => Content.
	 * @return string
	 */
	public function convert_twitter_meta_arr_to_html( $meta ) {
		$meta_html = '';

		foreach ( $meta as $k1_meta_name => $v1_meta_content ) {
			if ( ! empty( $v1_meta_content ) ) {
				if ( ! is_array( $v1_meta_content ) ) {
					$v1_meta_content = array( $v1_meta_content );
				}

				foreach ( $v1_meta_content as $f ) {
					$meta_html .= sprintf(
						'<meta name="%s" content="%s" />%s',
						$k1_meta_name,
						esc_attr( $f ),
						"\n"
					);
				}
			}
		}

		return $meta_html;
	}


	/**
	 * Get Image by Source.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|null $p WP Post object.
	 * @return int|string Image ID or URL. Empty string on failure.
	 */
	public function get_image_by_source( $p = null ) {
		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}

		$image = '';
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$img_type = $gofer_seo_options->options['modules']['social_media']['image_source'];
		switch ( $img_type ) {
			case 'auto':
				$image = $this->get_image_from_auto( $post );
				break;

			case 'featured':
				$image = $this->get_image_from_post_thumbnail( $post );
				break;

			case 'attach':
				$image = $this->get_image_from_attachment( $post );
				break;

			case 'content':
				$image = $this->get_image_from_scan( $post );
				break;

			case 'author':
				$image = $this->get_image_from_author( $post );
				break;

			case 'custom':
				$meta_key = $gofer_seo_options->options['modules']['social_media']['image_source_meta_keys'];
				if ( ! empty( $meta_key ) ) {
					$meta_keys  = explode( ',', $meta_key );
					if ( $meta_keys ) {
						$image = $this->get_image_from_meta_key(
							$post,
							$meta_keys
						);
					}
				}
				break;

			case 'default':
			default:
				$image = $this->get_image_from_default();
		}

		return $image;
	}

	/**
	 * Get Image from Auto.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|null $p WP Post object.
	 * @return int|string Image ID or URL. Empty string on failure.
	 */
	function get_image_from_auto( $p = null ) {
		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}

		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$meta_key = $gofer_seo_options->options['modules']['social_media']['image_source_meta_keys'];
		if ( ! empty( $meta_key ) && ! empty( $post ) ) {
			$meta_keys = explode( ',', $meta_key );
			if ( $meta_keys ) {
				$image    = $this->get_image_from_meta_key(
					$post,
					$meta_keys
				);
			}
		}
		if ( empty( $image ) ) {
			$image = $this->get_image_from_post_thumbnail( $post );
		}
		if ( empty( $image ) ) {
			$image = $this->get_image_from_attachment( $post );
		}
		if ( empty( $image ) ) {
			$image = $this->get_image_from_scan( $post );
		}
		if ( empty( $image ) ) {
			$image = $this->get_image_from_default();
		}

		return $image;
	}

	/**
	 * Get Image from Post Thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param null $p WP Post object.
	 * @return int|string Attachment ID or empty string if the post does not exist.
	 */
	function get_image_from_post_thumbnail( $p = null ) {
		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}

		$thumbnail_id = get_post_thumbnail_id( $post );
		return $thumbnail_id;
	}

	/**
	 * Get Image from Attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|null $p WP Post object.
	 * @return int|string  Image ID. Empty string on failure.
	 */
	function get_image_from_attachment( $p = null ) {
		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}

		if ( empty( $post ) ) {
			return '';
		}

		$attachment_id = '';

		if ( 'attachment' === $post->post_type ) {
			$attachment_id = $post->ID;
		} else {
			$attachments = get_children(
				array(
					'post_parent'    => $post->ID,
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => 'ASC',
					'orderby'        => 'menu_order ID',
					'fields'         => 'ids',
				)
			);
			if ( ! empty( $attachments ) ) {
				$attachment_id = $attachments[0];
			}
		}

		return $attachment_id;
	}

	/**
	 * Get Image from Scan.
	 *
	 * Scans a Post's content by (regex) capturing an <img> element's source for the image URL.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|null $p WP Post object.
	 * @return int|string Image ID or URL. Empty string on failure.
	 */
	function get_image_from_scan( $p = null ) {
		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}

		if ( empty( $post ) ) {
			return '';
		}

		$image = '';
		/* Search the post's content for the <img /> tag and get its URL. */
		preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', get_post_field( 'post_content', $post->ID ), $matches );
		if ( isset( $matches ) && ! empty( $matches[1][0] ) ) {
			$image = $matches[1][0];
			$attachment_id = Gofer_SEO_Methods::attachment_url_to_postid( $matches[1][0] );
			if ( $attachment_id ) {
				$image = $attachment_id;
			}
		}

		return $image;
	}

	/**
	 * Get Image from Author.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|null $p WP Post object.
	 * @return string Author avatar/image URL.
	 */
	function get_image_from_author( $p = null ) {
		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}

		if ( ! empty( $post ) && ! empty( $post->post_author ) ) {
			$matches    = array();
			$get_avatar = get_avatar( $post->post_author, 300 );
			if ( preg_match( "/src='(.*?)'/i", $get_avatar, $matches ) ) {
				return $matches[1];
			}
		}

		return '';
	}

	/**
	 * Get Image from Meta Key.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|null $p WP Post object.
	 * @param array        $meta_keys
	 * @return int|string|mixed Image ID or URL. Empty string on failure.
	 */
	function get_image_from_meta_key( $p = null, $meta_keys = array() ) {
		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}

		// Loop through each of the meta_keys and get the image URL.
		foreach ( $meta_keys as $meta_key ) {
			$image = get_post_meta( $post->ID, $meta_key, true );
			if ( ! empty( $image ) ) {
				$attachment_id = Gofer_SEO_Methods::attachment_url_to_postid( $image );
				if ( $attachment_id ) {
					$image = $attachment_id;
				}

				return $image;
			}
		}

		return '';
	}

	/**
	 * Get Image from Default.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function get_image_from_default() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		return $gofer_seo_options->options['modules']['social_media']['default_image'];
	}

	/**
	 * Prepare Twitter Username
	 *
	 * We do things like strip out the URL, etc and return just (at)username.
	 * At the moment, we'll check for 1 of 3 things... (at)username, username, and https://twitter.com/username.
	 * In the future, we'll need to start validating the information on the way in, so we don't have to do it one the way out.
	 *
	 * @since 1.0.0
	 *
	 * @param $twitter_profile
	 * @return string
	 */
	public function prepare_twitter_username( $twitter_profile ) {
		// Test for valid Twitter username, with or without `@`.
		if ( preg_match( '/^(\@)?[A-Za-z0-9_]+$/', $twitter_profile ) ) {
			$twitter_profile = $this->prepend_at_symbol( $twitter_profile );

			return $twitter_profile;
		}

		// Check if it has Twitter.com.
		if ( strpos( $twitter_profile, 'twitter.com' ) ) {
			$twitter_profile = esc_url( $twitter_profile );

			// Extract the Twitter username from the URL.
			$parsed_twitter_profile = wp_parse_url( $twitter_profile );
			$path                   = $parsed_twitter_profile['path'];
			$path_parts             = explode( '/', $path );
			$new_profile            = $path_parts[1];

			if ( $new_profile ) {
				$new_profile = $this->prepend_at_symbol( $new_profile );

				return $new_profile;
			}
		}

		// If all else fails, just send it back.
		return $twitter_profile;
	}

	/**
	 * Prepend at Symbol
	 *
	 * @since 1.0.0
	 *
	 * @param array $twitter_profile
	 * @return string
	 */
	public function prepend_at_symbol( $twitter_profile ) {
		// Checks for @ in the beginning, if it's not there adds it.
		if ( '@' !== $twitter_profile[0] ) {
			$twitter_profile = '@' . $twitter_profile;
		}

		return $twitter_profile;
	}

}
