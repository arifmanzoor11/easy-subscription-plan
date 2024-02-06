<?php 

function display_subscription_plans() {
    // if (user_has_bought_plan()) {
    //     echo '<p>You have already purchased a subscription plan.</p>';
    //     return;
    // }
    global $wpdb;

    // Fetch the subscription plans from the database
    $table_name = $wpdb->prefix . 'easy_subscription_plan';
    $query = "SELECT * FROM $table_name";
    $plans = $wpdb->get_results($query, ARRAY_A);

    // Check if there are any plans
    if (!empty($plans)) {
        // Group plans by post ID
        $grouped_plans = [];
        foreach ($plans as $plan) {
            $grouped_plans[$plan['post_id']][] = $plan;
        }

        // Loop through each group
        foreach ($grouped_plans as $post_id => $post_plans) {
            // Get the post title based on the post ID
            $post_title = get_the_title($post_id);

            // Check if the post title exists
            if (!empty($post_title)) {
                // Loop through each plan for the current post ID
                foreach ($post_plans as $plan) {
                    // Display duration and price
                    if ($plan['meta_key'] === 'easy_sub_duration') {
                        $duration = esc_html($plan['meta_value']);
                    }
                    if ($plan['meta_key'] === 'easy_sub_price') {
                        $price = esc_html($plan['meta_value']);
                    }
                }
                $get_esysubscription_setting = unserialize(get_option('esysubscription_setting'));
                // Display the subscription plan card for the current post ID
                ?>
                <button class="paypal-button" data-price="<?php echo $price; ?>" data-plan-id="<?php echo $post_id; ?>"
                     data-plan-name="<?php echo esc_attr($post_title); ?>">
                <div class="subscription-plan-card">
                    
                <?php $image =  wp_get_attachment_image_src( get_post_thumbnail_id($post_id) ); ?>
                    <img src="<?php echo $image[0] ;?>">
                    <h3><b>$ <?php echo $price; ?></b></h3>
                    <p><b><?php echo esc_html($post_title); ?></b></p>
                    <p>Duration: <b><?php echo $duration; ?>  <?php echo get_meta_subscription_plan('easy_subscription_plan', $post_id , 'easy_sub_duration_days') ?>(s)</b></p>
                    <p>Price: <b>$<?php echo $price; ?></b></p>
                    <?php echo get_meta_subscription_plan('easy_subscription_plan', $post_id , 'subscription_plan_content') ?>
                </div>
                </button>
                <?php
            }
        }
    } else {
        echo '<p>No subscription plans available.</p>';  
    }
}
 