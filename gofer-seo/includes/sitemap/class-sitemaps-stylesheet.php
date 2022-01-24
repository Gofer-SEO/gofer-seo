<?php
/**
 * Gofer SEO Sitemaps: Stylesheet class
 *
 * Responsible for rendering Sitemaps data to XML in accordance with sitemap protocol.
 *
 * @package Gofer SEO
 * @subpackage Sitemaps
 */

/**
 * Class Gofer_SEO_Sitemaps_Stylesheet.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Sitemaps_Stylesheet extends WP_Sitemaps_Stylesheet {

	/**
	 * Returns the escaped XSL for all sitemaps, except index.
	 *
	 * @since 1.0.0
	 */
	public function get_sitemap_stylesheet() {
		$css         = $this->get_stylesheet_css();
		$css_file    = GOFER_SEO_URL . 'public/css/sitemaps.css';
		$title       = esc_xml( __( 'XML Sitemap', 'gofer-seo' ) );
		$description = sprintf(
			/* translators: %1$s: Technical name for sitemap. %2$s: Link & Name of plugin. */
			__( 'This is an %1$s generated by %2$s, and is utilized by search engines like Google, Bing, Yandex, etc..', 'gofer-seo' ),
			'XML Sitemap',
			sprintf( '<a href="https://wordpress.org/plugins/gofer-seo/">%s</a>', GOFER_SEO_NAME )
		);
		$learn_more = sprintf(
			/* translators: %1$s: Link & Name of website. */
			__( 'Visit %1$s for more information regarding Sitemaps.', 'gofer-seo' ),
			sprintf(
				'<a href="%s">%s</a>',
				'https://www.sitemaps.org/',
				'sitemaps.org'
			)
		);
		$text       = sprintf(
			/* translators: %s: Number of URLs. */
			esc_xml( __( 'Number of URLs: %s.', 'gofer-seo' ) ),
			'<xsl:value-of select="count( sitemap:urlset/sitemap:url )" />'
		);

		$lang          = get_language_attributes( 'html' );
		$th_url        = esc_xml( __( 'URL', 'gofer-seo' ) );
		$th_lastmod    = esc_xml( __( 'Last Modified', 'gofer-seo' ) );
		$th_changefreq = esc_xml( __( 'Change Frequency', 'gofer-seo' ) );
		$th_priority   = esc_xml( __( 'Priority', 'gofer-seo' ) );
		$th_image      = esc_xml( __( 'Images', 'gofer-seo' ) );

		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$xsl_content = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
		version="1.0"
		xmlns:html="https://www.w3.org/TR/html/"
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
		xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
		>
	<xsl:output method="html" version="5.0" encoding="UTF-8" indent="yes"/>

	<!--
	  Set variables for whether lastmod, changefreq or priority occur for any url in the sitemap.
	  We do this up front because it can be expensive in a large sitemap.
	  -->
	<xsl:variable name="has-lastmod"    select="count( /sitemap:urlset/sitemap:url/sitemap:lastmod )"    />
	<xsl:variable name="has-changefreq" select="count( /sitemap:urlset/sitemap:url/sitemap:changefreq )" />
	<xsl:variable name="has-priority"   select="count( /sitemap:urlset/sitemap:url/sitemap:priority )"   />
	<xsl:variable name="has-image"      select="count( /sitemap:urlset/sitemap:url/image:image )"   />

	<xsl:template match="/">
		<html {$lang}>
			<head>
				<title>{$title}</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>
					{$css}
				</style>
				<link rel="stylesheet" type="text/css" href="{$css_file}" />
			</head>
			<body>
				<div id="sitemap">
					<div id="sitemap__header">
						<h1>{$title}</h1>
						<p>{$description}</p>
						<p>{$learn_more}</p>
						<p class="text">{$text}</p>
					</div>
					<div id="sitemap__content">
						<table id="sitemap__table" cellpadding="3">
							<thead>
								<tr>
									<th class="loc">{$th_url}</th>
									<xsl:if test="\$has-lastmod">
										<th class="lastmod">{$th_lastmod}</th>
									</xsl:if>
									<xsl:if test="\$has-changefreq">
										<th class="changefreq">{$th_changefreq}</th>
									</xsl:if>
									<xsl:if test="\$has-priority">
										<th class="priority">{$th_priority}</th>
									</xsl:if>
									<xsl:if test="\$has-image">
										<th class="image">{$th_image}</th>
									</xsl:if>
								</tr>
							</thead>
							<tbody>
								<xsl:for-each select="sitemap:urlset/sitemap:url">
									<tr>
										<td class="loc"><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc" /></a></td>
										<xsl:if test="\$has-lastmod">
											<td class="lastmod"><xsl:value-of select="sitemap:lastmod" /></td>
										</xsl:if>
										<xsl:if test="\$has-changefreq">
											<td class="changefreq"><xsl:value-of select="sitemap:changefreq" /></td>
										</xsl:if>
										<xsl:if test="\$has-priority">
											<td class="priority"><xsl:value-of select="sitemap:priority" /></td>
										</xsl:if>
										<xsl:if test="\$has-image">
											<td class="image"><xsl:value-of select="count(image:image)"/></td>
										</xsl:if>
									</tr>
								</xsl:for-each>
							</tbody>
						</table>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

XSL;
		// phpcs:enable

		/**
		 * Filters the content of the sitemap stylesheet.
		 *
		 * @since 1.0.0
		 *
		 * @param string $xsl_content Full content for the XML stylesheet.
		 */
		return apply_filters( 'gofer_seo_sitemaps_stylesheet_content', $xsl_content );
	}


	/**
	 * Returns the escaped XSL for the index sitemaps.
	 *
	 * @since 1.0.0
	 */
	public function get_sitemap_index_stylesheet() {
		$css         = $this->get_stylesheet_css();
		$css_file    = GOFER_SEO_URL . 'public/css/sitemaps.css';
		$title       = esc_xml( __( 'XML Sitemap Index', 'gofer-seo' ) );
		$description = sprintf(
			/* translators: %1$s: Technical name for sitemap. %2$s: Link & Name of plugin. */
			__( 'This is an %1$s generated by %2$s, and is utilized by search engines like Google, Bing, Yandex, etc..', 'gofer-seo' ),
			'XML Sitemap',
			sprintf( '<a href="https://wordpress.org/plugins/gofer-seo/">%s</a>', GOFER_SEO_NAME )
		);
		$learn_more = sprintf(
			/* translators: %1$s: Link & Name of website. */
			__( 'Visit %1$s for more information regarding Sitemaps.', 'gofer-seo' ),
			sprintf(
				'<a href="%s">%s</a>',
				'https://www.sitemaps.org/',
				'sitemaps.org'
			)
		);
		$text       = sprintf(
			/* translators: %s: Number of URLs. */
			esc_xml( __( 'Number of URLs: %s.', 'gofer-seo' ) ),
			'<xsl:value-of select="count( sitemap:sitemapindex/sitemap:sitemap )" />'
		);

		$lang       = get_language_attributes( 'html' );
		$th_url     = esc_xml( __( 'URL', 'gofer-seo' ) );
		$th_lastmod = esc_xml( __( 'Last Modified', 'gofer-seo' ) );

		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$xsl_content = <<<XSL
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
		version="1.0"
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
		xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns:html="https://www.w3.org/TR/html/"
		xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
		xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
		>
	<xsl:output method="html" version="5.0" encoding="UTF-8" indent="yes"/>

	<!--
	  Set variables for whether lastmod, changefreq or priority occur for any url in the sitemap.
	  We do this up front because it can be expensive in a large sitemap.
	  -->
	<xsl:variable name="has-lastmod" select="count( /sitemap:sitemapindex/sitemap:sitemap/sitemap:lastmod )" />

	<xsl:template match="/">
		<html {$lang}>
			<head>
				<title>{$title}</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>
					{$css}
				</style>
				<link rel="stylesheet" type="text/css" href="{$css_file}" />
			</head>
			<body>
				<div id="sitemap">
					<div id="sitemap__header">
						<h1>{$title}</h1>
						<p>{$description}</p>
						<p>{$learn_more}</p>
						<p class="text">{$text}</p>
					</div>
					<div id="sitemap__content">
						<table id="sitemap__table" cellpadding="3">
							<thead>
								<tr>
									<th class="loc">{$th_url}</th>
									<xsl:if test="\$has-lastmod">
										<th class="lastmod">{$th_lastmod}</th>
									</xsl:if>
								</tr>
							</thead>
							<tbody>
								<xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
									<tr>
										<td class="loc">
											<a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc" /></a>
										</td>
										<xsl:if test="\$has-lastmod">
											<td class="lastmod">
												<xsl:value-of select="sitemap:lastmod" />
											</td>
										</xsl:if>
									</tr>
								</xsl:for-each>
							</tbody>
						</table>
					</div>
						
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>

XSL;
		// phpcs:enable

		/**
		 * Filters the content of the sitemap index stylesheet.
		 *
		 * @since 1.0.0
		 *
		 * @param string $xsl_content Full content for the XML stylesheet.
		 */
		return apply_filters( 'gofer_seo_sitemaps_stylesheet_index_content', $xsl_content );
	}

	/**
	 * Gets the CSS to be included in sitemap XSL stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @return string The CSS.
	 */
	public function get_stylesheet_css() {
		$text_align = is_rtl() ? 'right' : 'left';

		$css = <<<EOF
					
					#sitemap__table tr th {
						text-align: {$text_align};
					}
					
EOF;

		/**
		 * Filters the CSS only for the sitemap stylesheet.
		 *
		 * @since 1.0.0
		 *
		 * @param string $css CSS to be applied to default XSL file.
		 */
		return apply_filters( 'gofer_seo_sitemaps_stylesheet_css', $css );
	}

}
