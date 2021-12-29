<?php
/**
 * Get/Query Functions.
 *
 * Contains functions relating to querying the database.
 * Either a get_*, WP_Query, or an SQL query (WPDB).
 *
 * @since 1.0.0
 */

/* **____________******************************************************************************************************/
/* _/ Post Types \____________________________________________________________________________________________________*/

/**
 * Get Post Types.
 *
 * Similar to WP's `get_post_types()` with improvements.
 * It is able to...
 * - Get registered post types before 'init' hook (loaded from prior instance), and refreshes list.
 *     - Added due to an issue with the update check constructing the options class without CPTs.
 * - Pluck any field.
 * - Cache the results.
 *
 * @since 1.0.0
 *
 * @link https://developer.wordpress.org/reference/functions/get_post_types/
 *
 * @param array $args  Arguments used with get_post_types()
 * @param bool  $field Returns the class field. Otherwise false returns objects.
 * @return array[]|WP_Post_Type[]
 */
function gofer_seo_get_post_types( $args = array(), $field = false ) {
	$post_type_objects = get_transient( 'gofer_seo_post_type_objects' );
	$cache = ( false !== $post_type_objects );
	if ( false === $post_type_objects ) {
		global $wp_post_types;
		$post_type_objects = $wp_post_types;
	}
	add_action( 'init', 'gofer_seo_set_post_type_objects_transient', 9999 );

	$default_args = array(
		'public' => true,
	);
	$args = wp_parse_args( $args, $default_args );

	$cache_group = '';
	if ( $cache ) {
		// TODO Investigate if foreach loop is preferred instead.
		$cache_group = array_map(
			function( $key, $value ) {
				return $key . '_' . $value;
			},
			array_keys( $args ),
			$args
		);
		$cache_group = 'post_types_' . implode( '_', $cache_group ) . '_' . $field;

		$post_types = wp_cache_get( 'gofer_seo_get_post_types', $cache_group );
		if ( false !== $post_types ) {
			return $post_types;
		}
	}

	$post_types = wp_filter_object_list( $post_type_objects, $args, 'and', $field );

	if ( $cache ) {
		wp_cache_set( 'gofer_seo_get_post_types', $post_types, $cache_group, DAY_IN_SECONDS );
	}

	return $post_types;
}

/**
 * Set Transient - Post Type Objects.
 *
 * Used to get around early calls to `get_post_types()` prior to 'init' hook.
 * Intended to run after post types have been registered.
 *
 * @since 1.0.0
 */
function gofer_seo_set_post_type_objects_transient() {
	global $wp_post_types;
	set_transient( 'gofer_seo_post_type_objects', $wp_post_types, 24 * HOUR_IN_SECONDS );
}

/**
 * Get Post Type Items for Admin Page.
 *
 * @since 1.0.0
 *
 * @param array $args
 * @return string[]|WP_Post_Type[]
 */
function gofer_seo_get_post_types_as_items( $args = array() ) {
	$post_types = gofer_seo_get_post_types( $args );

	$post_type_items = array();
	foreach ( $post_types as $post_type_slug => $post_type ) {
		if ( ! empty( $post_type->label ) ) {
			$post_type_items[ $post_type_slug ] = $post_type->label;
		} else {
			$post_type_items[ $post_type_slug ] = $post_type_slug;
		}
	}

	return $post_type_items;
}

/* **____________******************************************************************************************************/
/* _/ Taxonomies \____________________________________________________________________________________________________*/

/**
 * Get Taxonomies.
 *
 * Similar to WP's `get_taxonomies()` with a few improvements.
 *
 * @since 1.0.0
 *
 * @see gofer_seo_get_post_types() For improvement details.
 *
 * @param array $args  Arguments used with get_taxonomies()
 * @param bool  $field Returns the class field. Otherwise false returns objects.
 * @return array[]|WP_Taxonomy[]
 */
function gofer_seo_get_taxonomies( $args = array(), $field = false ) {
	$taxonomy_objects = get_transient( 'gofer_seo_taxonomy_objects' );
	$cache = ( false !== $taxonomy_objects );
	if ( false === $taxonomy_objects ) {
		global $wp_taxonomies;
		$taxonomy_objects = $wp_taxonomies;
	}
	add_action( 'init', 'gofer_seo_set_taxonomy_objects_transient', 9999 );

	$default_args = array(
		'public' => true,
	);
	$args = wp_parse_args( $args, $default_args );

	$cache_group = '';
	if ( $cache ) {
		$cache_group = array_map(
			function( $key, $value ) {
				return $key . '_' . $value;
			},
			array_keys( $args ),
			$args
		);
		$cache_group = 'taxonomies_' . implode( '_', $cache_group ) . '_' . $field;

		$taxonomies = wp_cache_get( 'gofer_seo_get_taxonomies', $cache_group  );
		if ( false !== $taxonomies ) {
			return $taxonomies;
		}
	}

	$taxonomies = wp_filter_object_list( $taxonomy_objects, $args, 'and', $field );

	if ( $cache ) {
		wp_cache_set( 'gofer_seo_get_taxonomies', $taxonomies, $cache_group, DAY_IN_SECONDS );
	}

	return $taxonomies;
}

/**
 * Set Transient - Taxonomy Objects.
 *
 * Used to get around early calls to `get_taxonomies()` prior to 'init' hook.
 * Intended to run after post types have been registered.
 *
 * @since 1.0.0
 */
function gofer_seo_set_taxonomy_objects_transient() {
	global $wp_taxonomies;
	set_transient( 'gofer_seo_taxonomy_objects', $wp_taxonomies, 24 * HOUR_IN_SECONDS );
}

/**
 * Get Taxonomy Items for Admin Page.
 *
 * @since 1.0.0
 *
 * @param array $args Arguments similar to get_taxonomies() to match objects to.
 * @return string[]
 */
function gofer_seo_get_taxonomies_as_items( $args = array() ) {
	$taxonomies = gofer_seo_get_taxonomies();

	$taxonomies_items = array();
	foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
		if ( ! empty( $taxonomy->label ) ) {
			$taxonomies_items[ $taxonomy_slug ] = $taxonomy->label;
		} else {
			$taxonomies_items[ $taxonomy_slug ] = $taxonomy_slug;
		}
	}

	return $taxonomies_items;
}

/* **_______***********************************************************************************************************/
/* _/ Terms \_________________________________________________________________________________________________________*/

/**
 * Get Term.
 *
 * Similar to WP's `get_taxonomies()` with a few improvements.
 *
 * @since 1.0.0
 *
 * @see WP_Term_Query::__construct() For $args
 *
 * @param array $args Optional. Array or string of arguments. See WP_Term_Query::__construct()
 * @return WP_Term[]|WP_Error
 */
function gofer_seo_get_terms( $args = array() ) {
	$defaults = array(
		'suppress_filter' => false,
	);
	$args = wp_parse_args( $args, $defaults );

//	if ( isset( $args['taxonomy'] ) && null !== $args['taxonomy'] ) {
	if ( ! empty( $args['taxonomy'] ) && ! is_array( $args['taxonomy'] ) ) {
		$args['taxonomy'] = (array) $args['taxonomy'];
	}


	$cache_group = array_map(
		function( $key, $value ) {
			if ( is_array( $value ) ) {
				$value = implode( '_', $value );
			}
			return $key . '_' . $value;
		},
		array_keys( $args ),
		$args
	);
	$cache_group = 'terms_' . implode( '_', $cache_group );

	$terms = wp_cache_get( 'gofer_seo_get_terms', $cache_group );
	if ( false !== $terms ) {
		return $terms;
	}

	$term_query = new WP_Term_Query();

	if ( ! empty( $args['taxonomy'] ) ) {
		foreach ( $args['taxonomy'] as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.', 'gofer-seo' ) );
			}
		}
	}

	// Don't pass suppress_filter to WP_Term_Query.
	$suppress_filter = $args['suppress_filter'];
	unset( $args['suppress_filter'] );

	$terms = $term_query->query( $args );

	wp_cache_set( 'gofer_seo_get_terms', $terms, $cache_group, DAY_IN_SECONDS );

	if ( $suppress_filter ) {
		return $terms;
	}

	// Uses the same WP hook to produce similar results as `get_terms()` would.
	// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	/**
	 * Filters the found terms.
	 *
	 * Applies the same filter as WP's `get_terms()`.
	 *
	 * @since 1.0.0
	 *
	 * @param array         $terms      Array of found terms.
	 * @param array         $taxonomies An array of taxonomies.
	 * @param array         $args       An array of get_terms() arguments.
	 * @param WP_Term_Query $term_query The WP_Term_Query object.
	 */
	return apply_filters( 'get_terms', $terms, $term_query->query_vars['taxonomy'], $term_query->query_vars, $term_query );
	// phpcs:enable
}

/* **_______***********************************************************************************************************/
/* _/ Media \_________________________________________________________________________________________________________*/


/* **______************************************************************************************************************/
/* _/ User \__________________________________________________________________________________________________________*/

/**
 * Get Users.
 *
 * Similar to WP's `get_users()` with a few improvements.
 *
 * @since 1.0.0
 *
 * @see WP_User_Query::prepare_query()
 *
 * @param array $args Arguments to retrieve users. See WP_User_Query::prepare_query().
 *                    for more information on accepted arguments.
 * @return array|WP_User[]
 */
function gofer_seo_get_users( $args = array() ) {
	$default_args = array();
	$args = wp_parse_args( $args, $default_args );

	$cache_group = array_map(
		function( $key, $value ) {
			if ( is_array( $value ) ) {
				$value = implode( '_', $value );
			}
			return $key . '_' . $value;
		},
		array_keys( $args ),
		$args
	);
	$cache_group = 'users_' . implode( '_', $cache_group );

	$users = wp_cache_get( 'gofer_seo_get_users', $cache_group  );
	if ( false !== $users ) {
		return $users;
	}

	$user_search = new WP_User_Query( $args );
	$users       = (array) $user_search->get_results();

	wp_cache_set( 'gofer_seo_get_users', $users, $cache_group, DAY_IN_SECONDS );

	return $users;
}

/**
 * Get User Items for Admin Page.
 *
 * @since 1.0.0
 *
 * @return string[]
 */
function gofer_seo_get_user_roles_as_items() {
	global $wp_roles;
	if ( isset( $wp_roles ) && $wp_roles instanceof WP_Roles ) {
		$user_roles = $wp_roles;
	} else {
		$user_roles = new WP_Roles();
	}
	$role_names = $user_roles->get_names();
	ksort( $role_names );

	return $role_names;
}

/* **_______***********************************************************************************************************/
/* _/ Dates \_________________________________________________________________________________________________________*/

/**
 * Get Date.
 *
 * @since 1.0.0
 *
 * @param $args
 * @return array|object|null {
 *     @type int $year
 *     @type int $month
 * }
 *
 */
function gofer_seo_get_dates( $args ) {
	global $wpdb;

	$sql_query = $wpdb->prepare(
		"SELECT
				YEAR(post_date) AS `year`,
				MONTH(post_date) AS `month`
			FROM {$wpdb->posts}
			WHERE post_type in (%s) AND post_status = 'publish'
			GROUP BY
				YEAR(post_date),
				MONTH(post_date)
			ORDER BY post_date ASC LIMIT %d",
		implode( ', ', $args['post_type'] ),
		$args['posts_per_page']
	);

	$group = sprintf(
		'%1$s:%2$s',
		md5( $sql_query ),
		wp_cache_get_last_changed( 'posts' )
	);

	$date_results = wp_cache_get( 'gofer_seo_get_dates', $group );
	if ( ! $date_results ) {
		// Already prepared, and is used to set cache group prior to if `get_results()` is needed.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$date_results = $wpdb->get_results( $sql_query, OBJECT );
		wp_cache_set( 'gofer_seo_get_dates', $date_results, $group, DAY_IN_SECONDS );
	}

	return $date_results;
}
