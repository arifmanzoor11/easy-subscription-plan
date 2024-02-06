<?php
global $wpdb;
// Table for storing subscription plans
$table_name = $wpdb->prefix . 'easy_subscription_plan';
$my_products_db_version = '1.0.1';
$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
    meta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `post_id` mediumint(9) NOT NULL,
    `meta_key` varchar(255) NOT NULL,
    `meta_value` varchar NOT NULL,
    PRIMARY KEY ID(meta_id)
);";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
add_option('my_db_version', $my_products_db_version);

$table_name_subscription = $wpdb->prefix . 'user_subscription_records';
$my_subscription_db_version = '1.0.0';
$sql_subscription = "CREATE TABLE IF NOT EXISTS `$table_name_subscription` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) NOT NULL,
    `user_name` varchar(255) NOT NULL,
    `subscription_name` varchar(255) NOT NULL,
    `amount` decimal(10, 2) NOT NULL,
    `datetime` datetime NOT NULL,
    `plan_type` varchar(255) NOT NULL,
    `transaction_id` varchar(255) NOT NULL,
    `status` varchar(50) NOT NULL,
    `ip_address` varchar(50) NOT NULL,
    `location` varchar(255) NOT NULL,
    `device_info` varchar(255) NOT NULL,
    `transaction_details` TEXT,
    PRIMARY KEY (`id`)
);";
dbDelta($sql_subscription);
add_option('my_subscription_db_version', $my_subscription_db_version);

?>
