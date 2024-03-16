<?php 
/**
* Plugin Name: Easy Subscription Plans
* Plugin URI: http://guitarchordslyrics.com
* Description: Process payments, establish subscription plans, and control access to content on your membership site with easy subscription Plans.
* Version: 1.1.6
* Author: Arif M.
* Author URI: http://guitarchordslyrics.com
* License: GNU GENERAL PUBLIC LICENSE
*/
// Include other plugin files
include_once(plugin_dir_path(__FILE__) . 'admin/esy-subscription-admin.php');
include_once(plugin_dir_path(__FILE__) . 'admin/inc/cpt-subscription.php');
include_once(plugin_dir_path(__FILE__) . '/subscription-plan-installer-db.php');
include_once(plugin_dir_path(__FILE__) . 'inc/views/subscription-plans.php');
require_once(plugin_dir_path(__FILE__) . 'inc/views/views-and-status.php');

register_activation_hook(__FILE__, 'subscription_views_and_status');


// Enqueue scripts
function enqueue_subscription_scripts() {
    // Enqueue PayPal SDK and Toastify
    $get_esysubscription_setting = unserialize(get_option('esysubscription_setting'));
    $client_id = $get_esysubscription_setting[1];
    $curency = $get_esysubscription_setting[0];
    wp_enqueue_script('paypal-sdk', 'https://www.paypal.com/sdk/js?client-id=' . $client_id . '&currency=' . symbol_url($curency) . '', array(), null, true);
    wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', array(), null, true);
    wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.css');
    wp_enqueue_style('easy-subscription-plan-css', plugin_dir_url(__FILE__) . 'assets/css/easy-subscription-plan.css');
    
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
    if ( !is_user_logged_in() ) {
        echo json_encode(array('Code' => '150', 'Value' => 'User not logged In'));
        exit;
    } else {
        $price = $_POST['price'];
        $planName = $_POST['planName'];
        $planId = $_POST['planId'];
        $validationPrice = get_meta_subscription_plan('easy_subscription_plan', $planId, 'easy_sub_price');
        $validationPlanname = get_the_title($planId);
        
        if($price == $validationPrice && $planName == $validationPlanname){
            echo json_encode(array('Code' => '200', 'Value' => 'Plan Exist'));
            exit;
        }
    }
    wp_die(); //terminate the AJAX handler
}
add_action('wp_ajax_plan_validator', 'plan_validator_ajax_handler'); // For logged in users
add_action('wp_ajax_nopriv_plan_validator', 'plan_validator_ajax_handler'); // For non-logged in users


// Function to check if the user has bought a plan and has an active subscription
function user_has_bought_plan() {
    $user_id = get_current_user_id();

    // if there is no user present than returning true so that payment method does not appear
    if (!$user_id) {
        return 404;
    }

    global $wpdb;
    $table_name_subscription = $wpdb->prefix . 'user_subscription_records';
    $result = $wpdb->get_row("SELECT * FROM $table_name_subscription WHERE user_id = $user_id");

    // Check if the user has an active subscription
    if ($result) {
        // If subscription is found, check if it's expired
        if (is_subscription_expired($user_id)) {
            // Subscription is expired, so return true to allow user to make payment
            echo json_encode(array('Code' => '202', 'Value' => 'Subscription is expired'));
            exit;
        } else {
            // Subscription is active, so return false to prevent payment method from appearing
            echo json_encode(array('Code' => '203', 'Value' => 'Subscription is active'));
            exit;
        }
    } else {
        // if user has not bought the plan than returning true so that payment user can make payment
        echo json_encode(array('Code' => '201', 'Value' => 'Not Bought Plan'));
        exit;
    }
}

// AJAX callback to check if the user has bought a plan
function ajax_user_has_bought_plan() {
    echo json_encode( user_has_bought_plan() );
    wp_die();
}
add_action('wp_ajax_user_has_bought_plan', 'ajax_user_has_bought_plan'); // For logged in users
add_action('wp_ajax_nopriv_user_has_bought_plan', 'ajax_user_has_bought_plan'); // For non-logged in users










// function block_qr_for_non_subscribers() {
//     $user_id = get_current_user_id();
//     $current_post_type = get_post_type();
//     $get_esysubscription_setting = unserialize(get_option('esysubscription_setting'));

//     $redirect_page_id = isset($get_esysubscription_setting[6]) ? intval($get_esysubscription_setting[6]) : 0;
//         // Get the URL of the selected page or fallback to the homepage
//      $redirect_url = $redirect_page_id ? get_permalink($redirect_page_id) : home_url('/');

//     // Check if the user is not logged in and not already on the plans page
//     if (!$user_id && !is_page('plans') && is_blocked_content($current_post_type)) {
//         // If user is not logged in and tries to access blocked content, redirect them to the plans page
//         wp_redirect($redirect_url);
//         exit;

//     } elseif (is_blocked_content($current_post_type, $user_id)) {
//         // Check if the user is blocked
//         $blocked_users = get_option('blocked_users', array());
//         if (in_array($user_id, $blocked_users)) {
//             // If the user is blocked, redirect them to a 404 page or any other page
//             wp_redirect($redirect_url);
//             exit;
//         }

//         // Check if the user has bought a plan and if their subscription is not expired
//         if (!has_bought_plan() && !in_array($user_id, $blocked_users)) {
//             // If the user hasn't bought a subscription or their subscription is expired,
//             // redirect them to the subscription page or any other page
//             wp_redirect(home_url('/plans/?plan=paused')); // Redirecting to the plan paused page
//             exit;
//         }
//     }
// }
// add_action('template_redirect', 'block_qr_for_non_subscribers');


// function is_blocked_content($current_content, $user_id = null) {
//     // Get blocked post types and pages from WordPress options
//     $unserialized_data = unserialize(get_option('esysubscription_setting'));
//     if (!empty($unserialized_data) && is_array($unserialized_data)) {
//         // Extract blocked post types and pages
//         $blocked_content = isset($unserialized_data[4]) ? $unserialized_data[4] : array();
//         $blocked_pages = isset($unserialized_data[5]) ? $unserialized_data[5] : array();

//         // Convert page IDs to integers
//         $blocked_pages = array_map('intval', $blocked_pages);

//         // Check if the current content is blocked
//         if (in_array($current_content, $blocked_content) || ($current_content === 'page' && in_array(get_the_ID(), $blocked_pages))) {
//             // If a user ID is provided and it's not logged in, return false to allow access to non-blocked content
//             if ($user_id && !is_user_logged_in()) {
//                 return false;
//             }
//             return true;
//         }
//     }
//     return false;
// }

// Define a function to check the subscription status of the author of a post
function get_author_subscription_status_by_author_id($author_id) {
    // Get the user corresponding to the author ID
    $user = get_user_by('ID', $author_id);

    if ($user) {
        $user_id = $user->ID;

        // Check if the subscription is expired for this user
        return is_subscription_expired($user_id);
    }

    // Return false if the user is not found
    return false;
}




// Define a function to check if the user has bought a plan
function has_bought_plan() {
    $user_id = get_current_user_id();
    global $wpdb;
    $table_name_subscription = $wpdb->prefix . 'user_subscription_records';
    $result = $wpdb->get_row("SELECT * FROM $table_name_subscription WHERE user_id = $user_id");

    // Check if the user has bought a plan and if their subscription is not expired
    if ($result && !is_subscription_expired($user_id)) {
        return true; // User has bought a plan and subscription is active
    } else {
        return false; // User has not bought a plan or subscription is expired
    }
}


function block_qr_for_non_subscribers() {
    $user_id = get_current_user_id();
    $current_post_type = get_post_type();
    $get_esysubscription_setting = unserialize(get_option('esysubscription_setting'));

    $redirect_page_id = isset($get_esysubscription_setting[6]) ? intval($get_esysubscription_setting[6]) : 0;
        // Get the URL of the selected page or fallback to the homepage
     $redirect_url = $redirect_page_id ? get_permalink($redirect_page_id) : home_url('/');

    // Check if the user is not logged in and not already on the plans page
    if (!$user_id && !is_page('plans') && is_blocked_content($current_post_type) ) {
        // Get the author ID of the post
        $author_id = get_post_field('post_author', get_the_ID());
        
        // Check if the author's subscription is expired
        $subscription_expired = get_author_subscription_status_by_author_id($author_id);
    
        // If the author's subscription is expired, redirect them to the plans page
        if ($subscription_expired) {
            // wp_redirect($redirect_url);
            wp_redirect(home_url('/plans/?plan=bar-code-expired')); // Redirecting to the plan paused page
            exit;
        }
    }
     elseif (is_blocked_content($current_post_type, $user_id)) {
        // Check if the user is blocked
        $blocked_users = get_option('blocked_users', array());
        if (in_array($user_id, $blocked_users)) {
            // If the user is blocked, redirect them to a 404 page or any other page
            wp_redirect($redirect_url);
            exit;
        }

        // Check if the user has bought a plan and if their subscription is not expired
        if (!has_bought_plan() && !in_array($user_id, $blocked_users)) {
            // If the user hasn't bought a subscription or their subscription is expired,
            // redirect them to the subscription page or any other page
            wp_redirect(home_url('/plans/?plan=paused')); // Redirecting to the plan paused page
            exit;
        }
    }
}
add_action('template_redirect', 'block_qr_for_non_subscribers');


function is_blocked_content($current_content, $user_id = null) {
    // Get blocked post types and pages from WordPress options
    $unserialized_data = unserialize(get_option('esysubscription_setting'));
    if (!empty($unserialized_data) && is_array($unserialized_data)) {
        // Extract blocked post types and pages
        $blocked_content = isset($unserialized_data[4]) ? $unserialized_data[4] : array();
        $blocked_pages = isset($unserialized_data[5]) ? $unserialized_data[5] : array();

        // Convert page IDs to integers
        $blocked_pages = array_map('intval', $blocked_pages);

        // Check if the current content is blocked
        if (in_array($current_content, $blocked_content) || ($current_content === 'page' && in_array(get_the_ID(), $blocked_pages))) {
            // If a user ID is provided and it's not logged in, return false to allow access to non-blocked content
            if ($user_id && !is_user_logged_in()) {
                return false;
            }
            return true;
        }
    }
    return false;
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


    // Function to render View 1 content
    function render_view1_content() {
        echo do_shortcode('[subscription_plans]');
    }

    // Function to render View 2 content
    function render_view2_content() {
        
        ?>
        <style>#paused-message {
        background-color: #f8d7da; /* Red color or any other color you prefer */
        color: #721c24; /* Text color */
        padding: 10px;
        border: 1px solid #f5c6cb; /* Border color */
        border-radius: 5px;
    }

    #paused-message h3 {
        margin-top: 0;
        font-size: 1.2em;
    }

    #paused-message p {
        margin-bottom: 0;
    }
    </style>
        <div id="paused-message">
        <h3>Plan Paused</h3>
        <p>You need to <a href="<?php echo home_url('/plans/'); ?>">Choose a plan</a> to view the QR code.</p>
    </div>
        <?php
    }

    // Function to render View 2 content
    function render_view3_content() {
        
        ?>
        <style>#paused-message {
        background-color: #f8d7da; /* Red color or any other color you prefer */
        color: #721c24; /* Text color */
        padding: 10px;
        border: 1px solid #f5c6cb; /* Border color */
        border-radius: 5px;
    }

    #paused-message h3 {
        margin-top: 0;
        font-size: 1.2em;
    }

    #paused-message p {
        margin-bottom: 0;
    }
    </style>
        <div id="paused-message">
        <h3>Paused</h3>
        <p>You do not have any plan. Please <a href="<?php echo home_url('/plans/'); ?>">Choose a plan</a> to continue.</p>
    </div>
        <?php
    }

    // Function to render View 3 content
    function render_view4_content() {
        
        ?>
        <style>#paused-message {
        background-color: #f8d7da; /* Red color or any other color you prefer */
        color: #721c24; /* Text color */
        padding: 10px;
        border: 1px solid #f5c6cb; /* Border color */
        border-radius: 5px;
    }

    #paused-message h3 {
        margin-top: 0;
        font-size: 1.2em;
    }

    #paused-message p {
        margin-bottom: 0;
    }
    </style>
        <div id="paused-message">
        <h3>Paused</h3>
        <p>Please <a href="<?php echo home_url('/plans/'); ?>">Signup</a> to continue.</p>
    </div>
        <?php
    }
    // Function to render View 3 content
    function render_view5_content() {
        
        ?>
        <style>#paused-message {
        background-color: #f8d7da; /* Red color or any other color you prefer */
        color: #721c24; /* Text color */
        padding: 10px;
        border: 1px solid #f5c6cb; /* Border color */
        border-radius: 5px;
    }

    #paused-message h3 {
        margin-top: 0;
        font-size: 1.2em;
    }

    #paused-message p {
        margin-bottom: 0;
    }
    </style>
        <div id="paused-message">
        <h3>Paused</h3>
        <p>Bar code Expired!</p>
    </div>
        <?php
    }
   // Function to redirect to the home page
// Function to redirect to the home page
function home_content() {
    wp_redirect(home_url('/')); // Redirecting to the root URL (WordPress homepage)
    exit; // Make sure to exit after redirecting
}
// Shortcode to display subscription views
function subscription_views_shortcode() {
    ob_start(); // Start output buffering  

    // Get the view from the URL path
    $view = isset($_GET['plan']) ? $_GET['plan'] : 'choose-plan'; // Default to 'choose-plan' if no plan is specified

    // Output the content based on the selected view
    switch ($view) {
        case 'choose-plan':
            render_view1_content();
            break;
        case 'paused':
            render_view2_content();
            break;
        case 'no-plan':
            render_view3_content();
            break;
        case 'no-authentication':
            render_view4_content();
            break;
        case 'bar-code-expired':
            render_view5_content();
            break;
        default:
            // Handle invalid view or if no plan is specified, default to 'choose-plan'
            render_view1_content(); // Render 'choose-plan' content by default
            break;
    }

    // Output the view links

    return ob_get_clean(); // Return the buffered content
}
add_shortcode('subscription_views', 'subscription_views_shortcode');














// Define a function to check if a subscription is expired
function is_subscription_expired($user_id) {
    global $wpdb;

    // Retrieve subscription details from the database
    $table_name = $wpdb->prefix . 'user_subscription_records';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY datetime DESC LIMIT 1", $user_id);
    $subscription = $wpdb->get_row($query, ARRAY_A);

    if (!$subscription) {
        // No subscription found for the user
        return true;
    }

    // Extract subscription duration and days from the subscription plan_type
    $plan_type_array = unserialize($subscription['plan_type']);
    $duration = intval($plan_type_array['plan_Duration']);
    $days = $plan_type_array['plan_Days'];

    // Calculate expiration date based on the duration and days
    $start_date = strtotime($subscription['datetime']);
    switch ($days) {
        case 'day':
            $expiration_date = strtotime("+ $duration days", $start_date);
            break;
        case 'week':
            $expiration_date = strtotime("+ $duration weeks", $start_date);
            break;
        case 'month':
            $expiration_date = strtotime("+ $duration months", $start_date);
            break;
        case 'year':
            $expiration_date = strtotime("+ $duration years", $start_date);
            break;
        default:
            return true; // Invalid duration or days
    }

    $current_time = current_time('timestamp');

    // Check if the subscription is expired
    return ($current_time > $expiration_date);
}

// Define a shortcode to check subscription expiration and display remaining time
function subscription_status_shortcode($atts) {
    // Get the user ID of the logged-in user
    $user_id = get_current_user_id();

    if (!$user_id) {
        // If user is not logged in, display a message
        return "Please log in to check your subscription status.";
    }

    // Retrieve subscription details from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_subscription_records';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY datetime DESC LIMIT 1", $user_id);
    $subscription = $wpdb->get_row($query, ARRAY_A);

    // Check if subscription exists
    if (!$subscription) {
        // No subscription found for the user, provide a link to the plan page
        $plan_page_url = home_url('/plans'); // Adjust 'plan-page' to the actual slug of your plan page
        return "You do not have any plan. <a href='$plan_page_url'>View Plans</a>";
    }

    // Extract subscription duration and days from the subscription plan_type
    $plan_type_array = unserialize($subscription['plan_type']);
    $duration = intval($plan_type_array['plan_Duration']);
    $days = $plan_type_array['plan_Days'];

    // Calculate remaining time based on the expiration date and current time
    $start_date = strtotime($subscription['datetime']);
    switch ($days) {
        case 'day':
            $expiration_date = strtotime("+ $duration days", $start_date);
            break;
        case 'week':
            $expiration_date = strtotime("+ $duration weeks", $start_date);
            break;
        case 'month':
            $expiration_date = strtotime("+ $duration months", $start_date);
            break;
        case 'year':
            $expiration_date = strtotime("+ $duration years", $start_date);
            break;
        default:
            return "Invalid subscription duration or days.";
    }

    $current_time = current_time('timestamp');
    $remaining_time = $expiration_date - $current_time;

    // Check if subscription is expired
    if ($current_time > $expiration_date) {
        $plan_purchase_url = home_url('/plans'); // Adjust 'plans' to the actual slug of your plan purchase page
        $message = "Your subscription is expired. <a href='$plan_purchase_url'>Purchase a New Plan</a>";
        return $message;
    } else {
        // Convert remaining time to days, hours, minutes, and seconds
        $remaining_days = floor($remaining_time / (60 * 60 * 24));
        $remaining_hours = floor(($remaining_time % (60 * 60 * 24)) / (60 * 60));
        $remaining_minutes = floor(($remaining_time % (60 * 60)) / 60);
        $remaining_seconds = $remaining_time % 60;

        // Construct the message
        $message = "Plan expiring in ";
        $message .= "$remaining_days days, ";
        $message .= "$remaining_hours hours, ";
        $message .= "$remaining_minutes minutes, and ";
        $message .= "$remaining_seconds seconds.";

        return $message;
    }
}
add_shortcode('subscription_status', 'subscription_status_shortcode');











// Function to handle blocking a user
function block_user_action() {
    if (isset($_POST['block_user_submit'])) {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $redirect_url = isset($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : admin_url('admin.php?page=your_page_slug'); // Adjust the redirect URL as needed

        // Retrieve the array of blocked users
        $blocked_users = get_option('blocked_users', array());

        // Add the user ID to the array of blocked users if it's not already there
        if (!in_array($user_id, $blocked_users)) {
            $blocked_users[] = $user_id;
            update_option('blocked_users', $blocked_users);
        }

        // Redirect back to the previous page after saving
        wp_redirect($redirect_url);
        exit;
    }
}

// Function to handle unblocking a user
function unblock_user_action() {
    if (isset($_POST['block_user_submit'])) {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $redirect_url = isset($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : admin_url('admin.php?page=your_page_slug'); // Adjust the redirect URL as needed

        // Retrieve the array of blocked users
        $blocked_users = get_option('blocked_users', array());

        // Remove the user ID from the array of blocked users if it's there
        if (in_array($user_id, $blocked_users)) {
            $blocked_users = array_diff($blocked_users, array($user_id));
            update_option('blocked_users', $blocked_users);
        }

        // Redirect back to the previous page after saving
        wp_redirect($redirect_url);
        exit;
    }
}

// Hook the functions to the admin-post.php actions
add_action('admin_post_block_user', 'block_user_action');
add_action('admin_post_nopriv_block_user', 'block_user_action'); // Allow non-logged in users to access the action
add_action('admin_post_unblock_user', 'unblock_user_action');
add_action('admin_post_nopriv_unblock_user', 'unblock_user_action'); // Allow non-logged in users to access the action

function symbol_settings($price = "usd") {
    switch ($price) {
    case 'usd':
        echo '$';
        break;
    case 'gbp':
        echo '£';
        break;
    case 'cad':
        echo '$';
        break;
    case 'eur':
        echo '€';
        break;
    case 'ars':
        echo '$';
        break;
    case 'bbd':
        echo '$';
        break;
    case 'aud':
        echo '$';
        break;
    }
}

function symbol_url($price = "usd") {
    switch ($price) {
    case 'usd':
        return 'USD';
        break;
    case 'gbp':
        return 'GBP';
        break;
    case 'cad':
        return 'CAD';
        break;
    case 'eur':
        return 'EUR';
        break;
    case 'ars':
        return 'ARS';
        break;
    case 'bbd':
        return 'BBD';
        break;
    case 'aud':
        return 'AUD';
        break;
    }
}