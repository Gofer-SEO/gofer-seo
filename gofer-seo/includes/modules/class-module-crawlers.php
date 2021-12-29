<?php
/**
 * Module - Crawlers
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module_Crawlers.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module_Crawlers extends Gofer_SEO_Module {

	/**
	 * Gofer_SEO_Module_Crawlers constructor.
	 */
	public function __construct() {
		parent::__construct();

		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( ! is_user_logged_in() ) {
			if ( $gofer_seo_options->options['modules']['crawlers']['enable_block_user_agent'] ) {
				if ( ! $this->is_agent_whitelisted() && $this->is_agent_blacklisted() ) {
					status_header( 503 );

					$ip         = '';
					$user_agent = '';
					if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
						// Already being sanitized.
						// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$ip = $this->sanitize_ip( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
						// phpcs:enable
					}
					if ( isset( $_SERVER['HTTP_USER_AGENT']  ) ) {
						$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
					}

					$block_message = sprintf(
						/* translators: %1$s is the bot IP, and %2$s is the Bot user-agent. */
						__( 'Blocked IP: %1$s User-Agent: %2$s - Matched %2$s in user-agent blacklist.', 'gofer-seo' ),
						$ip,
						$user_agent
					);
					$this->log_blocked_bot( $block_message );
					exit();
				}
			}

			if ( $gofer_seo_options->options['modules']['crawlers']['enable_block_referer'] ) {
				if ( $this->is_referral_blacklisted() ) {
					status_header( 503 );

					$ip      = '';
					$referer = '';
					if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
						// Already being sanitized.
						// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$ip = $this->sanitize_ip( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
						// phpcs:enable
					}
					if ( isset( $_SERVER['HTTP_REFERER']  ) ) {
						$referer = sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
					}

					$block_message = sprintf(
						/* translators: %1$s is the bot IP, and %2$s is the Bot referer. */
						__( 'Blocked IP %1$s Referer %2$s - Matched %2$s in referer blacklist.', 'gofer-seo' ),
						$ip,
						$referer
					);
					$this->log_blocked_bot( $block_message );
					exit();
				}
			}
		}
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		parent::load();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		parent::init();

		// TODO Move to `$this->load()` when 'load_modules' action-hook is changed from `init` to `plugins_loaded`.
		add_filter( 'robots_txt', array( $this, 'robots_txt' ), 9999, 2 );
	}

	/**
	 * Robots.txt hook.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output The robots.txt output.
	 * @param bool   $public Whether the site is considered "public".
	 * @return string
	 */
	public function robots_txt( $output, $public ) {
		$rtn_output = '';
		$gofer_seo_options = $this->get_network_options();

		// If override rules is NOT enabled, then combine Rules with the original output.
		if ( ! $gofer_seo_options->options['modules']['crawlers']['enable_override_robots_txt'] ) {
			$output_data  = $this->parse_robots_rules( $output );
			$robots_rules = array_replace_recursive( $output_data, $gofer_seo_options->options['modules']['crawlers']['robots_txt_rules'] );
		} else {
			$robots_rules = $gofer_seo_options->options['modules']['crawlers']['robots_txt_rules'];
		}

		// Sitemaps.
		if ( ! empty( $robots_rules['sitemaps'] ) ) {
			foreach ( $robots_rules['sitemaps'] as $index => $sitemap ) {
				$rtn_output .= sprintf( 'Sitemap: %s%s', $sitemap, "\n" );
			}
			$rtn_output .= "\n";
		}

		// User-agent(s).
		foreach ( $robots_rules['user_agents'] as $agent_slug => $agent_values ) {
			$rtn_output .= sprintf( 'User-agent: %s%s', $agent_values['user_agent'], "\n" );

			if ( ! empty( $agent_values['crawl_delay'] ) ) {
				$rtn_output .= sprintf( 'Crawl-delay: %s%s', $agent_values['crawl_delay'], "\n" );
			}

			foreach ( $agent_values['path_rules'] as $path => $rule ) {
				$rtn_output .= sprintf( '%s: %s%s', ucwords( $rule ), $path, "\n" );
			}

			$rtn_output .= "\n";
		}

		return $rtn_output;
	}


	/* **_________________*********************************************************************************************/
	/* _/ Block HTTP Bots \___________________________________________________________________________________________*/


	/**
	 * Default Agent Whitelist.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function default_agent_whitelist() {
		return array(
			'Yahoo! Slurp' => 'crawl.yahoo.net',
			'googlebot'    => '.googlebot.com',
			'msnbot'       => 'search.msn.com',
		);
	}

	/**
	 * Default Agent Blacklist.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function default_agent_blacklist() {
		return array(
			'Abonti',
			'aggregator',
			'AhrefsBot',
			'asterias',
			'BDCbot',
			'BLEXBot',
			'BuiltBotTough',
			'Bullseye',
			'BunnySlippers',
			'ca-crawler',
			'CCBot',
			'Cegbfeieh',
			'CheeseBot',
			'CherryPicker',
			'CopyRightCheck',
			'cosmos',
			'Crescent',
			'discobot',
			'DittoSpyder',
			'DotBot',
			'Download Ninja',
			'EasouSpider',
			'EmailCollector',
			'EmailSiphon',
			'EmailWolf',
			'EroCrawler',
			'ExtractorPro',
			'Fasterfox',
			'FeedBooster',
			'Foobot',
			'Genieo',
			'grub-client',
			'Harvest',
			'hloader',
			'httplib',
			'HTTrack',
			'humanlinks',
			'ieautodiscovery',
			'InfoNaviRobot',
			'IstellaBot',
			'Java/1.',
			'JennyBot',
			'k2spider',
			'Kenjin Spider',
			'Keyword Density/0.9',
			'larbin',
			'LexiBot',
			'libWeb',
			'libwww',
			'LinkextractorPro',
			'linko',
			'LinkScan/8.1a Unix',
			'LinkWalker',
			'LNSpiderguy',
			'lwp-trivial',
			'magpie',
			'Mata Hari',
			'MaxPointCrawler',
			'MegaIndex',
			'Microsoft URL Control',
			'MIIxpc',
			'Mippin',
			'Missigua Locator',
			'Mister PiX',
			'MJ12bot',
			'moget',
			'MSIECrawler',
			'NetAnts',
			'NICErsPRO',
			'Niki-Bot',
			'NPBot',
			'Nutch',
			'Offline Explorer',
			'Openfind',
			'panscient.com',
			'PHP/5.{',
			'ProPowerBot/2.14',
			'ProWebWalker',
			'Python-urllib',
			'QueryN Metasearch',
			'RepoMonkey',
			'SISTRIX',
			'sitecheck.Internetseer.com',
			'SiteSnagger',
			'SnapPreviewBot',
			'Sogou',
			'SpankBot',
			'spanner',
			'spbot',
			'Spinn3r',
			'suzuran',
			'Szukacz/1.4',
			'Teleport',
			'Telesoft',
			'The Intraformant',
			'TheNomad',
			'TightTwatBot',
			'Titan',
			'toCrawl/UrlDispatcher',
			'True_Robot',
			'turingos',
			'TurnitinBot',
			'UbiCrawler',
			'UnisterBot',
			'URLy Warning',
			'VCI',
			'WBSearchBot',
			'Web Downloader/6.9',
			'Web Image Collector',
			'WebAuto',
			'WebBandit',
			'WebCopier',
			'WebEnhancer',
			'WebmasterWorldForumBot',
			'WebReaper',
			'WebSauger',
			'Website Quester',
			'Webster Pro',
			'WebStripper',
			'WebZip',
			'Wotbox',
			'wsr-agent',
			'WWW-Collector-E',
			'Xenu',
			'Zao',
			'Zeus',
			'ZyBORG',
			'coccoc',
			'Incutio',
			'lmspider',
			'memoryBot',
			'serf',
			'Unknown',
			'uptime files',
		);
	}

	/**
	 * Default Referral Blacklist.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function default_referer_blacklist() {
		return array(
			'semalt.com',
			'kambasoft.com',
			'savetubevideo.com',
			'buttons-for-website.com',
			'sharebutton.net',
			'soundfrost.org',
			'srecorder.com',
			'softomix.com',
			'softomix.net',
			'myprintscreen.com',
			'joinandplay.me',
			'fbfreegifts.com',
			'openmediasoft.com',
			'zazagames.org',
			'extener.org',
			'openfrost.com',
			'openfrost.net',
			'googlsucks.com',
			'best-seo-offer.com',
			'buttons-for-your-website.com',
			'www.Get-Free-Traffic-Now.com',
			'best-seo-solution.com',
			'buy-cheap-online.info',
			'site3.free-share-buttons.com',
			'webmaster-traffic.com',
		);
	}

	/**
	 * Get Agent Whitelist.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_agent_whitelist() {
		$agents = self::default_agent_whitelist();

		/**
		 * Crawler/Bot Agent Whitelist.
		 *
		 * @since 1.0.0
		 *
		 * @param array $agents
		 *
		 */
		$agents = apply_filters( 'gofer_seo_crawler_agent_whitelist', $agents );

		return $agents;
	}

	/**
	 * Get Agent Blacklist.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_agent_blacklist() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( $gofer_seo_options->options['modules']['crawlers']['use_custom_blacklist'] ) {
			$agents = preg_split( '/\r\n|[\r\n]/', $gofer_seo_options->options['modules']['crawlers']['user_agent_blacklist'] );
		} else {
			$agents = self::default_agent_blacklist();
		}

		/**
		 * Crawler/Bot Agent Blacklist.
		 *
		 * @since 1.0.0
		 *
		 * @param array $agents
		 *
		 */
		$agents = apply_filters( 'gofer_seo_crawler_agent_blacklist', $agents );

		return $agents;
	}

	/**
	 * Get Referer Blacklist.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_referral_blacklist() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( $gofer_seo_options->options['modules']['crawlers']['use_custom_blacklist'] ) {
			$referers = preg_split( '/\r\n|[\r\n]/', $gofer_seo_options->options['modules']['crawlers']['referer_blacklist'] );
		} else {
			$referers = self::default_referer_blacklist();
		}

		/**
		 * Crawler/Bot Referer Blacklist.
		 *
		 * @since 1.0.0
		 *
		 * @param array $referers
		 *
		 */
		$referers = apply_filters( 'gofer_seo_crawler_referer_blacklist', $referers );

		return $referers;
	}

	/**
	 * Is User-Agent Whitelisted.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_agent_whitelisted() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$agent_whitelist = $this->get_agent_whitelist();
		if ( ! empty( $agent_whitelist ) && isset( $_SERVER['HTTP_USER_AGENT'] ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$regex      = $this->convert_array_to_regex( $agent_whitelist );
			$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
			if ( preg_match( '/' . $regex . '/i', $user_agent ) ) {
				$ip          = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
				$hostname    = gethostbyaddr( $ip );
				$hostname_ip = gethostbyname( $hostname );

				// Check if IP matches the Hostname IP.
				if ( $ip === $hostname_ip ) {
					$hosts = array_values( $agent_whitelist );
					foreach ( $hosts as $i => $host ) {
						$hosts[ $i ] = $host . '$';
					}
					$hosts = join( '|', $hosts );

					if ( preg_match( '/' . $hosts . '/i', $hostname ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Is User-Agent Blacklisted.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_agent_blacklisted() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$agent_blacklist = $this->get_agent_blacklist();
		if ( ! empty( $agent_blacklist ) && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$regex      = $this->convert_array_to_regex( $agent_blacklist );
			$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
			if ( preg_match( '/' . $regex . '/i', $user_agent ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is Referer Blacklisted.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_referral_blacklisted() {
		if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}

		$referer_blacklist = $this->get_referral_blacklist();
		if ( ! empty( $referer_blacklist ) && isset( $_SERVER['HTTP_REFERER'] ) ) {
			$regex   = $this->convert_array_to_regex( $referer_blacklist );
			$referer = sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			if ( preg_match( '/' . $regex . '/i', $referer ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Log Blocked Bot.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Prepend message to log.
	 */
	private function log_blocked_bot( $message ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( ! $gofer_seo_options->options['modules']['crawlers']['enable_log_blocked_bots'] ) {
			// Only log if track blocks is checked.
			return;
		}

		$gofer_seo_options->options['modules']['crawlers']['blocked_bots_log'] = sprintf(
			'%1$s %2$s %3$s %4$s',
			wp_date( 'Y-m-d H:i:s' ),
			sanitize_text_field( $message ),
			"\n",
			$gofer_seo_options->options['modules']['crawlers']['blocked_bots_log']
		);

		// Trim log string if needed.
		if ( 4096 < Gofer_SEO_PHP_Functions::strlen( $gofer_seo_options->options['modules']['crawlers']['blocked_bots_log'] ) ) {
			$end = Gofer_SEO_PHP_Functions::strrpos( $gofer_seo_options->options['modules']['crawlers']['blocked_bots_log'], "\n" );
			if ( false === $end ) {
				$end = 4096;
			}
			$gofer_seo_options->options['modules']['crawlers']['blocked_bots_log'] = Gofer_SEO_PHP_Functions::substr(
				$gofer_seo_options->options['modules']['crawlers']['blocked_bots_log'],
				0,
				$end
			);
		}

		$gofer_seo_options->update_options();
	}


	/* **________******************************************************************************************************/
	/* _/ ROBOTS \____________________________________________________________________________________________________*/


	/**
	 * Get Robots.txt Output.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $disable_plugin_filter
	 * @return string
	 */
	public function get_robots_txt( $disable_plugin_filter = false ) {
		if ( $disable_plugin_filter ) {
			remove_filter( 'robots_txt', array( $this, 'robots_txt' ), 9999 );

			$output = get_transient( 'gofer_seo_module_crawlers_robots_txt_without_filters' );
		} else {
			$output = get_transient( 'gofer_seo_module_crawlers_robots_txt_with_filters' );
		}

		if ( false !== $output ) {
			return $output;
		}

		$this->do_robots();

		if ( $disable_plugin_filter ) {
			set_transient( 'gofer_seo_module_crawlers_robots_txt_without_filters', $output, HOUR_IN_SECONDS );
		} else {
			set_transient( 'gofer_seo_module_crawlers_robots_txt_with_filters', $output, HOUR_IN_SECONDS );
		}

		return $output;
	}

	/**
	 * (WP) Do Robots.
	 *
	 * Reproduces WP results with do_robots(), and unfortunately cannot use 'do_robots' hook
	 * without causing a PHP warning "Cannot modify header information - headers already sent...".
	 * Which may cause missing lines from other plugins/themes that do use 'do_robots'.
	 *
	 * @since 1.0.0
	 * @since WP 5.3.0
	 *
	 * @return void
	 */
	private function do_robots() {
		// Simulates WP's `do_robots()`.
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( 'do_robotstxt' );

		$output = "User-agent: *\n";
		$public = get_option( 'blog_public' );

		$site_url = wp_parse_url( site_url() );
		$path     = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';
		$output  .= "Disallow: $path/wp-admin/\n";
		$output  .= "Allow: $path/wp-admin/admin-ajax.php\n";

		/**
		 * Filters the robots.txt output.
		 *
		 * @since 1.0.0
		 * @since WP 3.0.0
		 *
		 * @param string $output The robots.txt output.
		 * @param bool   $public Whether the site is considered "public".
		 */
		return apply_filters( 'robots_txt', $output, $public );
		// phpcs:enable
	}

	/**
	 * Parse string for Robots Rules.
	 *
	 * @since 1.0.0
	 *
	 * @param string $robots_str
	 * @return array {
	 *     @type string[] $sitemaps
	 *     @type array    $user_agents {
	 *         @type array [$user_agent] {
	 *             @type string   $user_agent
	 *             @type int      $crawl_delay
	 *             @type string[] $path_rules ${path} => ${rule}
	 *         }
	 *     }
	 * }
	 */
	public function parse_robots_rules( $robots_str ) {
		$robots_rules = array(
			'sitemaps'    => array(),
			'user_agents' => array(),
		);
		$matches_i     = array();
		$matches_j     = array();
		$tmp_str = $robots_str;

		// Sitemaps.
		preg_match_all(
			'/(?:(?:(?:\n|\r)?(?:sitemap)(?:\:\s?)([A-Za-z0-9\:\/\-\.]+)))/i',
			$tmp_str,
			$matches_i
		);
		$i_count = count( $matches_i[0] );
		for ( $i = 0; $i < $i_count; $i++ ) {
			$robots_rules['sitemaps'][] = $matches_i[1][ $i ];

			$tmp_str = str_replace( $matches_i[0][ $i ], '', $tmp_str );
		}

		// User-Agents.
		preg_match_all(
			'/(?:((?:user-agent)(?:\:\s?)([A-Z\*\$]+))((?:(?:\n|\r)+(?:disallow|allow|crawler-delay)(?:\:\s?)(?:[A-Za-z0-9\/\-\.]+))+))/i',
			$tmp_str,
			$matches_i
		);
		$i_count = count( $matches_i[0] );
		for ( $i = 0; $i < $i_count; $i++ ) {
			$user_agent = $matches_i[2][ $i ];
			$robots_rules['user_agents'][ $user_agent ] = array(
				'user_agent'  => $user_agent,
				'crawl_delay' => 0,
				'path_rules'  => array(),
			);

			// Rules for User-Agent.
			preg_match_all(
				'/(?:(disallow|allow|crawler-delay)(?:\:\s?)([A-Za-z0-9\/\-\.]+))/i',
				$matches_i[3][ $i ],
				$matches_j
			);
			$j_count = count( $matches_j[0] );
			for ( $j = 0; $j < $j_count; $j++ ) {
				$type  = strtolower( $matches_j[1][ $j ] );
				$value = $matches_j[2][ $j ];
				switch ( $type ) {
					case 'crawl-delay':
						$robots_rules['user_agents'][ $user_agent ]['crawl_delay'] = intval( $value );
						break;
					case 'disallow':
					case 'allow':
						$robots_rules['user_agents'][ $user_agent ]['path_rules'][ $value ] = $type;
						break;
				}
			}
		}

		return $robots_rules;
	}

	/* **____________**************************************************************************************************/
	/* _/ VALIDATION \________________________________________________________________________________________________*/


	/**
	 * Validate IP.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip The IP address to check.
	 * @return bool True if valid IPv4 or IPv6.
	 */
	public function validate_ip( $ip ) {
		if (
				filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ||
				filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 )
		) {
			// Valid IPV4 or IPV6.
			return true;
		}

		//Invalid IP.
		return false;
	}


	/* **___________________________***********************************************************************************/
	/* _/ FORMATTING (Sanitize|Esc) \_________________________________________________________________________________*/


	/**
	 * Sanitize IP.
	 *
	 * @since 1.0.0
	 *
	 * @param string|mixed $ip The IP string to sanitize.
	 * @return string
	 */
	public function sanitize_ip( $ip ) {
		$ip_sanitized = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
		if ( $ip_sanitized ) {
			// Valid IPV4.
			return $ip_sanitized;
		}

		$ip_sanitized = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
		if ( $ip_sanitized ) {
			// Valid IPV6.
			return $ip_sanitized;
		}

		return __( '(Invalid IP)', 'gofer-seo' );
	}


	/* **________******************************************************************************************************/
	/* _/ COMMON \____________________________________________________________________________________________________*/


	/**
	 * Multi-dimensional Array Unique.
	 *
	 * @since 1.0.0
	 *
	 * @param $array
	 * @param $columns
	 * @return array
	 */
	public function md_array_unique( $array, $columns ) {
		foreach ( $columns as $column ) {
			$tmp_array = array_combine( array_keys( $array ), array_column( $array, $column ) );
			$tmp_array = array_unique( $tmp_array );
			$array = array_intersect_key( $array, $tmp_array );
		}

		return $array;
	}

	/**
	 * Get Network Options.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $blog_id
	 * @return Gofer_SEO_Options
	 */
	public function get_network_options( $blog_id = null ) {
		if ( ! is_multisite() ) {
			return Gofer_SEO_Options::get_instance();
		}

		if ( null === $blog_id ) {
			$network = get_network();
			$blog_id = $network->site_id;
		}

		switch_to_blog( $blog_id );
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		restore_current_blog();

		return $gofer_seo_options;
	}

	/**
	 * Convert Array to Regex.
	 *
	 * Converts the crawler/bot array into a regex pattern.
	 *
	 * @since 1.0.0
	 *
	 * @param array $list
	 * @return string
	 */
	function convert_array_to_regex( $list ) {
		$tmp_list = array();
		foreach ( $list as $value ) {
			$value = trim( $value );
			if ( ! empty( $value ) ) {
				$tmp_list[] = preg_quote( $value, '/' );
			}
		}

		return implode( '|', $tmp_list );
	}

}
// phpcs:enable
