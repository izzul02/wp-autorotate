<?php
/*
Plugin Name: Auto Rotate Post Lite
description: A plugin to autorotate posts with specific rules.
Version: 1.1.1
Author: PUYUP-izzul02
License: Private
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
   echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
   exit;
}

define( 'ARP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ARP__TEXT_DOMAIN', 'autorotate' );

require_once( ARP__PLUGIN_DIR . 'inc/rotator.php' );
require_once( ARP__PLUGIN_DIR . 'inc/ajax.php' );
require_once( ARP__PLUGIN_DIR . 'inc/admin.php' );

add_action( 'init', 'github_plugin_updater_init' );
function github_plugin_updater_init() {

	include_once 'updater.php';

	define( 'WP_GITHUB_FORCE_UPDATE', true );

	if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

		$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'github-updater',
			'api_url' => 'https://api.github.com/izzul02/wp-autorotate',
			'raw_url' => 'https://raw.github.com/izzul02/wp-autorotate/master',
			'github_url' => 'https://github.com/izzul02/wp-autorotate',
			'zip_url' => 'https://github.com/izzul02/wp-autorotate/archive/master.zip',
			'sslverify' => true,
			'requires' => '4.9.0',
			'tested' => '5.3.0',
			'readme' => 'README.md',
			'access_token' => '',
		);

		new WP_GitHub_Updater( $config );

	}

}

/**
 * Create database
 */
function create_database() {
	global $wpdb;

	$table_ruleset = $wpdb->prefix . 'arp_ruleset';
	$table_log = $wpdb->prefix . 'arp_log';
	$charset_collate = $wpdb->get_charset_collate();

	$ruleset = "CREATE TABLE IF NOT EXISTS $table_ruleset (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		name varchar(255) DEFAULT NULL,
		post_age int(20) DEFAULT NULL,
		category_id varchar(255) DEFAULT NULL,
		tag_id varchar(255) DEFAULT NULL,
		keyword varchar(255) DEFAULT NULL,
		item int(20) NOT NULL,
        schedule int(20) NOT NULL,
        paused tinyint(2) DEFAULT 0,
        date_created date DEFAULT NULL,
		PRIMARY KEY (id)
    ) $charset_collate;";

    $log = "CREATE TABLE IF NOT EXISTS $table_log (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		ruleset_id int(20) DEFAULT NULL,
		post_ids text DEFAULT NULL,
        date_created date DEFAULT NULL,
		PRIMARY KEY (id)
    ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	dbDelta( $ruleset );
	dbDelta( $log );

    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_ruleset' AND column_name = 'paused'"  );

	if(empty($row)){
		$wpdb->query("ALTER TABLE $table_ruleset ADD paused tinyint(2) DEFAULT 0");
	}

    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_ruleset' AND column_name = 'name'"  );

	if(empty($row)){
		$wpdb->query("ALTER TABLE $table_ruleset ADD name varchar(255) DEFAULT NULL");
	}

    $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_log' AND column_name = 'post_ids'"  );

	if(empty($row)){
		$wpdb->query("ALTER TABLE $table_log ADD post_ids text DEFAULT null");
	}

	dbDelta( $ruleset );
	dbDelta( $log );
}


/***
 * Load static
 */
function arp_scripts() {
	// Styled
	wp_enqueue_style( 'arp-style', plugins_url( 'static/css/admin.css', __FILE__), array(), '1.0' );

    // JS
	wp_enqueue_script( 'arp-script', plugins_url( 'static/js/admin.js', __FILE__), array( 'jquery' ), '1.0', true );

	// Global params
	wp_localize_script( 'arp-script', 'arpParam', array(
		'resturl' => get_rest_url(),
		'siteurl' => get_home_url(),
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'arp_scripts' );


/***
 * Create CRON
 */
function create_schedule( $ruleset_id, $reschedule = false ) {
	global $wpdb;

	$key = 'ruleset_' . $ruleset_id . '_schedule';
	$db_ruleset = 'arp_ruleset';

	$ruleset = $wpdb->get_row( $wpdb->prepare( "
		SELECT * FROM {$wpdb->prefix}{$db_ruleset}
		WHERE id = %d
		", $ruleset_id
	) );

	if ( ! wp_next_scheduled( $key ) && ( ( isset( $ruleset->paused ) && !$ruleset->paused ) && $reschedule ) ) {
		$args = array( 'ruleset_id' => $ruleset_id );
		wp_schedule_event( time(), $key, 'arp_run_schedule', $args );
	}
}
add_action( 'after_ruleset_created', 'create_schedule', 10, 7 );
add_action( 'after_ruleset_resumed', 'create_schedule', 10, 7 );


/***
 * Remove CRON
 */
function delete_schedule( $ruleset_id ) {
	$key = 'ruleset_' . $ruleset_id . '_schedule';
	$timestamp = wp_next_scheduled( $key );
	$original_args = array();

	wp_unschedule_event( $timestamp, $key, $original_args );
}
add_action( 'after_ruleset_deleted', 'delete_schedule', 10, 7 );
add_action( 'after_ruleset_paused', 'delete_schedule', 10, 7 );


/**
 * Adds a custom cron schedule for every 5 minutes.
 *
 * @param array $schedules An array of non-default cron schedules.
 * @return array Filtered array of non-default cron schedules.
 */
function register_cron_schedule( $schedules ) {
    global $wpdb;

    $db_ruleset = 'arp_ruleset';
    $query = 'SELECT * FROM ' . $wpdb->prefix . $db_ruleset;
    $results = $wpdb->get_results( $query );

    foreach ( $results as $item ) {
    	$key = 'ruleset_' . $item->id . '_schedule';
    	// $interval = $item->schedule * 24 * 60 * MINUTE_IN_SECONDS;
    	// $interval = $item->schedule * 60;

    	$schedule = (int) $item->schedule;
    	$interval = $schedule * 86400;

    	$schedules[$key] = array(
	        'interval' => $interval, 
	        'display' => __( 'Every ' . $item->schedule . ' days' )
	    );
    }

    return $schedules;
}
add_filter( 'cron_schedules', 'register_cron_schedule' );


/***
 * On plugin active
 */
function arp_activation() {
    global $wpdb;

    // Create database
    create_database();

    // Init CRON
    $db_ruleset = 'arp_ruleset';
    $query = 'SELECT * FROM ' . $wpdb->prefix . $db_ruleset;
    $results = $wpdb->get_results( $query );

    foreach ( $results as $item ) {
    	$ruleset_id = $item->id;
    	$key = 'ruleset_' . $ruleset_id . '_schedule';

    	if ( ! wp_next_scheduled( $key ) ) {
			$args = array( 'ruleset_id' => $ruleset_id );
			wp_schedule_event( time(), $key, 'arp_run_schedule', $args );
		}
    }
}
register_activation_hook( __FILE__, 'arp_activation' );


/***
 * On plugin deactive
 */
function arp_deactivation() {
    wp_clear_scheduled_hook( 'arp_run_schedule' );
}
register_deactivation_hook( __FILE__, 'arp_deactivation' );


/***
 * Run schedule
 */
function arp_run_schedule_execute( $ruleset_id ) {
	global $wpdb;

	$db_ruleset = 'arp_ruleset';
	$ruleset = $wpdb->get_row( $wpdb->prepare( "
		SELECT * FROM {$wpdb->prefix}{$db_ruleset}
		WHERE id = %d
		", $ruleset_id
	) );

	if ( $ruleset ) {
		$rotator = new Rotator;
    	$rotator->item = $ruleset->item;
	    $rotator->schedule = $ruleset->schedule;
	    $rotator->keyword = $ruleset->keyword;
	    $rotator->category_id = $ruleset->category_id;
	    $rotator->post_age = $ruleset->post_age;
	    $rotator->ruleset_id = $ruleset_id;

	    $postIds = $rotator->runRuleset();
	    $log = $rotator->createLog($postIds);
	}
}
add_action( 'arp_run_schedule', 'arp_run_schedule_execute', 10, 2 );