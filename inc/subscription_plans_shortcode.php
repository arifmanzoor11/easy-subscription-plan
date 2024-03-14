<?php // Shortcode to display subscription plans
function subscription_plans_shortcode() {
    ob_start(); // Start output buffering  ?>
    <div class="subscription-plans">
        <?php display_subscription_plans(); ?>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('subscription_plans', 'subscription_plans_shortcode');