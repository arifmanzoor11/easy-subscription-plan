<?php


function save_transaction_details() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    // Get current user ID and name
    $user_id = get_current_user_id();
    $user_name = get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true);

    $user = get_userdata($user_id);
    $profile_name = $user->display_name;

    // Get POST data
    $subscriptionName = isset($_POST['subscriptionName']) ? $_POST['subscriptionName'] : '';
    $amount = isset($_POST['amount']) ? $_POST['amount'] : '';
    $datetime = isset($_POST['datetime']) ? $_POST['datetime'] : '';
    $planType = isset($_POST['planType']) ? $_POST['planType'] : '';
    $transactionId = isset($_POST['transactionId']) ? $_POST['transactionId'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    // $details = isset($_POST['details']) ? json_encode($_POST['details']) : '';

    // Get the JSON string
    $json_details = isset($_POST['details']) ? json_encode($_POST['details']) : '';

    // Serialize the JSON string
    $details = isset($json_details) ? serialize($json_details) : '';


    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Get device information
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $location_data = json_decode(file_get_contents("http://ip-api.com/json/$ip_address"), true);
    $location = isset($location_data['city']) ? $location_data['city'] : 'Unknown';

    // Insert data into database
    global $wpdb;
    $table_name_subscription = $wpdb->prefix . 'user_subscription_records';
    $wpdb->insert($table_name_subscription, array(
        'user_id' => $user_id,
        'user_name' => $profile_name,
        'subscription_name' => $subscriptionName,
        'amount' => $amount,
        'datetime' => $datetime,
        'plan_type' => $planType,
        'transaction_id' => $transactionId,
        'status' => $status,
        'ip_address' => $ip_address,
        'location' => $location,
        'device_info' => $user_agent,
        'transaction_details' => $details
    ));

    // Send success response
    wp_send_json_success('Transaction details saved successfully');
}

add_action('wp_ajax_save_transaction_details', 'save_transaction_details');
add_action('wp_ajax_nopriv_save_transaction_details', 'save_transaction_details'); // For non-logged in users