<?php
/*
Plugin Name: Site Health - Audit Enqueued Assets
Author: audrasjb, whodunitagency
Description: Adds a CSS and JS resource checker in Site Health checks
Version: 0.1
Author URI: https://jeanbaptisteaudras.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://paypal.me/audrasjb
Text Domain: site-health-audit-enqueued-assets
*/

add_action( 'wp_print_scripts', 'aea_audit_enqueued_scripts' );
function aea_audit_enqueued_scripts() {
	if ( ! is_admin() && ! get_transient( 'aea_enqueued_scripts' ) ) {
		global $wp_scripts;
		$enqueued_scripts = array();
		foreach( $wp_scripts->queue as $handle ) {
			$enqueued_scripts[] = $wp_scripts->registered[$handle]->src;
		}
		set_transient( 'aea_enqueued_scripts', $enqueued_scripts, 12 * HOUR_IN_SECONDS );
	}
}

add_action( 'wp_print_styles', 'aea_audit_enqueued_styles' );
function aea_audit_enqueued_styles() {
	if ( ! is_admin() && ! get_transient( 'aea_enqueued_styles' ) ) {
		global $wp_styles;
		$enqueued_styles = array();
		foreach( $wp_styles->queue as $handle ) {
			$enqueued_styles[] = $wp_styles->registered[$handle]->src;
		}
		set_transient( 'aea_enqueued_styles', $enqueued_styles, 12 * HOUR_IN_SECONDS );
	}
}

function aea_get_total_enqueued_scripts() {
	$enqueued_scripts = false;
	if ( get_transient( 'aea_enqueued_scripts' ) ) {
		$list_enqueued_scripts = get_transient( 'aea_enqueued_scripts' );
		$enqueued_scripts = count( $list_enqueued_scripts );
	}
	return $enqueued_scripts;
}

function aea_get_total_enqueued_styles() {
	$enqueued_styles = false;
	if ( get_transient( 'aea_enqueued_styles' ) ) {
		$list_enqueued_styles = get_transient( 'aea_enqueued_styles' );
		$enqueued_styles = count( $list_enqueued_styles );
	}
	return $enqueued_styles;
}

function aea_add_enqueued_assets_test( $tests ) {
	$tests['direct']['enqueued_js_assets'] = array(
		'label' => esc_html__( 'JS assets', 'site-health-audit-enqueued-assets' ),
		'test'  => 'aea_enqueued_js_assets_test',
	);
	$tests['direct']['enqueued_css_assets'] = array(
		'label' => esc_html__( 'CSS assets', 'site-health-audit-enqueued-assets' ),
		'test'  => 'aea_enqueued_css_assets_test',
	);
	return $tests;
}
add_filter( 'site_status_tests', 'aea_add_enqueued_assets_test' );
 
function aea_enqueued_js_assets_test() {
	$result = array(
		'label'	      => esc_html__( 'JS assets', 'site-health-audit-enqueued-assets' ),
		'status'      => 'good',
		'badge'	      => array(
			'label' => esc_html__( 'Performance', 'site-health-audit-enqueued-assets' ),
			'color' => 'orange',
		),
		'description' => sprintf(
			'<p>%s</p>',
			esc_html__( 'The amount of JS assets is acceptable.', 'site-health-audit-enqueued-assets' )
		),
		'actions'	  => '',
		'test'		  => 'enqueued_js_assets',
	);

	$enqueued_scripts = aea_get_total_enqueued_scripts();
	if ( false !== $enqueued_scripts && $enqueued_scripts > 10 ) {
		$result['status'] = 'recommended';
		$result['description'] = sprintf(
			esc_html__( 'Your website enqueues %s scripts. Try to reduce the number of JS assets, or to concatenate them.', 'site-health-audit-enqueued-assets' ),
			$enqueued_scripts
		);
		$result['actions'] .= sprintf(
			/* translators: 1: HelpHub URL. 2: Link description. */
			'<p><a href="%1$s">%2$s</a></p>',
			esc_url( __( 'https://wordpress.org/support/article/optimization/', 'site-health-audit-enqueued-assets' ) ),
			esc_html__( 'More info about performance optimization', 'site-health-audit-enqueued-assets' )
		);
	}
 
	return $result;
}

function aea_enqueued_css_assets_test() {
	$result = array(
		'label'	      => esc_html__( 'CSS assets', 'site-health-audit-enqueued-assets' ),
		'status'      => 'good',
		'badge'	      => array(
			'label' =>  esc_html__( 'Performance', 'site-health-audit-enqueued-assets' ),
			'color' => 'orange',
		),
		'description' => sprintf(
			'<p>%s</p>',
			esc_html__( 'The amount of CSS assets is acceptable.', 'site-health-audit-enqueued-assets' )
		),
		'actions'	  => '',
		'test'		  => 'enqueued_css_assets',
	);

	$enqueued_styles = aea_get_total_enqueued_styles();
	if ( false !== $enqueued_styles && $enqueued_styles > 10 ) {
		$result['status'] = 'recommended';
		$result['description'] = sprintf(
			esc_html__( 'Your website enqueues %s styles. Try to reduce the number of CSS assets, or to concatenate them.', 'site-health-audit-enqueued-assets' ),
			$enqueued_styles
		);
		$result['actions'] .= sprintf(
			/* translators: 1: HelpHub URL. 2: Link description. */
			'<p><a href="%1$s">%2$s</a></p>',
			esc_url( __( 'https://wordpress.org/support/article/optimization/', 'site-health-audit-enqueued-assets' ) ),
			esc_html__( 'More info about performance optimization', 'site-health-audit-enqueued-assets' )
		);
	}
 
	return $result;
}