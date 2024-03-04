<?php
// Activation hook callback function
function subscription_views_and_status(){ 
    // Define the page titles and shortcodes
    $pages = array(
        'plans' => '[subscription_views]',
        'Subscription Status' => '[subscription_status]',
    );

    foreach ($pages as $title => $shortcode) {
        // Check if the page already exists
        $existing_page = get_page_by_title($title, OBJECT, 'page');

        if (!$existing_page) {
            // If the page does not exist, create a new one
            $page_args = array(
                'post_title'    => $title,
                'post_content'  => $shortcode,
                'post_status'   => 'publish',
                'post_type'     => 'page',
            );
            $new_page_id = wp_insert_post($page_args);

            if (is_wp_error($new_page_id)) {
                // Handle error if page creation fails
                error_log('Error creating the page: ' . $new_page_id->get_error_message());
            }
        } else {
            // If the page exists, check if the shortcode is already present
            if (strpos($existing_page->post_content, $shortcode) === false) {
                // If the shortcode is not present, append it to the page content
                $updated_content = $existing_page->post_content . PHP_EOL . $shortcode;
                $updated_page_args = array(
                    'ID'            => $existing_page->ID,
                    'post_content'  => $updated_content,
                );
                wp_update_post($updated_page_args);
            }
        }
    }
}

// Register activation hook

?>
