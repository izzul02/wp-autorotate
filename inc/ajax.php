<?php
/***
 * DELETE ITEM
 */
function arp_ajx_delete_item() {
    $data = array();
	$delete_item_id = isset( $_POST['delete_item_id'] ) ? $_POST['delete_item_id'] : '';
    
    if ( $delete_item_id ) {
        $rotator = new Rotator;
        $rotator->delete_item_id = $delete_item_id;
        $rotator->deleteRuleset();

        $data['is_success'] = true;
    } else {
        $data['is_success'] = false;
    }

	wp_send_json( $data );
	wp_die();
}

add_action( 'wp_ajax_delete_item', 'arp_ajx_delete_item' );
add_action( 'wp_ajax_nopriv_delete_item', 'arp_ajx_delete_item' );

/***
 * PAUSE ITEM
 */
function arp_ajx_pause_item() {
    $data = array();
    $ruleset_id = isset( $_POST['ruleset_id'] ) ? $_POST['ruleset_id'] : '';
    
    if ( $ruleset_id ) {
        $rotator = new Rotator;
        $rotator->ruleset_id = $ruleset_id;
        $rotator->pauseRuleset();

        $data['is_success'] = true;
    } else {
        $data['is_success'] = false;
    }

    wp_send_json( $data );
    wp_die();
}

add_action( 'wp_ajax_pause_item', 'arp_ajx_pause_item' );
add_action( 'wp_ajax_nopriv_pause_item', 'arp_ajx_pause_item' );

/***
 * PAUSE ITEM
 */
function arp_ajx_resume_item() {
    $data = array();
    $ruleset_id = isset( $_POST['ruleset_id'] ) ? $_POST['ruleset_id'] : '';
    
    if ( $ruleset_id ) {
        $rotator = new Rotator;
        $rotator->ruleset_id = $ruleset_id;
        $rotator->resumeRuleset();

        $data['is_success'] = true;
    } else {
        $data['is_success'] = false;
    }

    wp_send_json( $data );
    wp_die();
}

add_action( 'wp_ajax_resume_item', 'arp_ajx_resume_item' );
add_action( 'wp_ajax_nopriv_resume_item', 'arp_ajx_resume_item' );

/***
 * UPDATE ITEM
 */
function arp_ajx_update_item() {
    $data = array();

    $ruleset_id = isset( $_POST['ruleset_id'] ) ? $_POST['ruleset_id'] : '';
    $item = isset( $_POST['item'] ) ? $_POST['item'] : '';
    $schedule = isset( $_POST['schedule'] ) ? $_POST['schedule'] : '';
    $keyword = isset( $_POST['keyword'] ) ? $_POST['keyword'] : '';
    $category_id = isset( $_POST['category_id'] ) ? $_POST['category_id'] : '';
    $post_age = isset( $_POST['post_age'] ) ? $_POST['post_age'] : '';
    
    $rotator = new Rotator;
    $rotator->item = $item;
    $rotator->schedule = $schedule;
    $rotator->keyword = $keyword;
    $rotator->category_id = $category_id;
    $rotator->post_age = $post_age;
    $rotator->ruleset_id = $ruleset_id;

    $query = $rotator->runRuleset();
    $log = $rotator->createLog();

	wp_send_json( $query );
	wp_die();
}

add_action( 'wp_ajax_update_item', 'arp_ajx_update_item' );
add_action( 'wp_ajax_nopriv_update_item', 'arp_ajx_update_item' );

/***
 * ACTIVATION
 */
function arp_ajx_activation() {
    $rotator = new Rotator;
    $res = $rotator->activation(isset( $_POST['key'] ) ? $_POST['key'] : '');
    
    wp_send_json( $res );
    wp_die();
}

add_action( 'wp_ajax_activation', 'arp_ajx_activation' );
add_action( 'wp_ajax_nopriv_activation', 'arp_ajx_activation' );

/***
 * CLEAR LOGS
 */
function arp_ajx_clear_log() {
    $data = array();
    
    $rotator = new Rotator;
    $rotator->clearLog();

    $data['is_success'] = true;
    
    wp_send_json( $data );
    wp_die();
}

add_action( 'wp_ajax_clear_log', 'arp_ajx_clear_log' );
add_action( 'wp_ajax_nopriv_clear_log', 'arp_ajx_clear_log' );