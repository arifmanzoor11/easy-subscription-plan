<?php 
/**
* Plugin Name: Easy Subscription Plans
* Plugin URI: http://guitarchordslyrics.com
* Description: Process payments, establish subscription plans, and control access to content on your membership site with easy subscription Plans.
* Version: 1.0
* Author: Arif M.
* Author URI: http://guitarchordslyrics.com
* License: GNU GENERAL PUBLIC LICENSE
*/
// Include other plugin files
include_once(plugin_dir_path(__FILE__) . 'admin/esy-subscription-admin.php');
include_once(plugin_dir_path(__FILE__) . 'admin/inc/cpt-subscription.php');
include_once(plugin_dir_path(__FILE__) . '/subscription-plan-installer-db.php');
include_once(plugin_dir_path(__FILE__) . 'inc/views/subscription-plans.php');

// Enqueue scripts
function enqueue_subscription_scripts() {
    // Enqueue PayPal SDK and Toastify
    $get_esysubscription_setting = unserialize(get_option('esysubscription_setting'));
    $client_id = $get_esysubscription_setting[1];                
    wp_enqueue_script('paypal-sdk', 'https://www.paypal.com/sdk/js?client-id=' . $client_id . '', array(), null, true);
    wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', array(), null, true);
    wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.css');
    
    // Enqueue custom JavaScript
    wp_enqueue_script('subscription-scripts', plugin_dir_url(__FILE__) . 'subscription-scripts.js', array('jquery'), null, true);

    // Localize AJAX URL for JavaScript
    wp_localize_script('subscription-scripts', 'subscription_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_subscription_scripts');

include_once(plugin_dir_path(__FILE__) . 'admin/inc/save-transaction-details.php');
include_once(plugin_dir_path(__FILE__) . 'inc/subscription_plans_shortcode.php');

// AJAX handler to check if a plan exists in the database
function plan_validator_ajax_handler() {
    // Retrieve price and plan name from AJAX request
    $price = $_POST['price'];
    $planName = $_POST['planName'];
    $planId = $_POST['planId'];
    $validationPrice = get_meta_subscription_plan('easy_subscription_plan', $planId, 'easy_sub_price');
    $validationPlanname = get_the_title($planId);
    
    if($price == $validationPrice && $planName == $validationPlanname){
      echo 1;
    }

    wp_die(); //terminate the AJAX handler
}
add_action('wp_ajax_plan_validator', 'plan_validator_ajax_handler'); // For logged in users
add_action('wp_ajax_nopriv_plan_validator', 'plan_validator_ajax_handler'); // For non-logged in users


function user_has_bought_plan() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return false; // User not logged in
    }

    global $wpdb;
    $table_name_subscription = $wpdb->prefix . 'user_subscription_records';
    $result = $wpdb->get_row("SELECT * FROM $table_name_subscription WHERE user_id = $user_id");
    return $result ? true : false;
}

// Get Data of plan from plan Meta Table
function get_meta_subscription_plan($table_name, $post_id, $key) {
    global $wpdb;
    $table = $wpdb->prefix . $table_name;
    $query_sql = "SELECT * FROM $table WHERE post_id = $post_id AND meta_key LIKE '$key'";
    $query = $wpdb->get_results( $query_sql );
    foreach ($query as $value) {
        return $value->meta_value;
    }
}
// Store Data of plan in plan Meta Table
function add_meta_subscription_plan( $table_name, $post_id, $key, $value ){
  
    global $wpdb;
    $table_name = $wpdb->prefix . $table_name;
    $easy_sub_duration = $_POST['easy_sub_duration'];
    $values = array(
        'post_id' => $post_id, 
        'meta_key' => $key,
        'meta_value' => $value
    );
    $table_name;
    $query = "SELECT * FROM $table_name WHERE `post_id` = $post_id AND `meta_key` LIKE '$key'";
    $checkIfExists = $wpdb->get_col( $query );
    if($checkIfExists  == NULL){
       $wpdb->insert($table_name, $values,  array( 
        '%s',     //specifying which type of value entering in table
        '%s',     //specifying which type of value entering in table
        '%s'     //specifying which type of value entering in table
      ));
    } else {
        $where = array( 'post_id' => $post_id , 'meta_key' => $key );
        // $wpdb->update($table_name, $values, $where );

        $wpdb->update( 
            $table_name, 
            $values, 
            $where, //two where  clause
           array( 
               '%s',     //specifying which type of value entering in table
               '%s',     //specifying which type of value entering in table
               '%s'     //specifying which type of value entering in table
             ), 
            array('%s', '%s' ) //specifying which type of value using in where cluase
            );
    }
}


add_action('wp_footer', 'output_paypal_dialog');

function output_paypal_dialog() { ?>
    <!-- Dialog container -->
    <div id="paypal-dialog" class="dialog">
        <div class="dialog-content">
            <span class="close">&times;</span>
            <div id="paypal-button-container"></div>
        </div>
    </div>
    <?php
}

add_filter( 'page_template', 'wpa3396_page_template' );
function wpa3396_page_template( $page_template )
{
    if ( is_page( 'my-custom-page-slug' ) ) {
        $page_template = dirname( __FILE__ ) . '/custom-page-template.php';
    }
    return $page_template;
}



// Remove editor from the custom post type
add_action( 'init', function() {
    remove_post_type_support( 'easysubscription', 'editor' );
}, 99);

function subscription_plan_installer_db(){ 
    include('subscription-plan-installer-db.php');
}
register_activation_hook(__file__, 'subscription_plan_installer_db');

// function remove_menu_items() {
//     if( !current_user_can( 'administrator' ) ):
//         remove_menu_page( 'edit.php?post_type=easysubscription' );
//     endif;
// }
// add_action( 'admin_menu', 'remove_menu_items' );