<?php // Shortcode to display subscription plans
function subscription_plans_shortcode() {
    ob_start(); // Start output buffering
    ?>
    <style>
        .subscription-plans button {
            flex: 1 0 0%;
            box-shadow: 0 0 6px 0px #00000052;
            border-radius: 10px;
            padding: 30px 20px;
            margin: 15px;
        }
        button.paypal-button {
    background: none;
    font-size: unset;
    text-align: unset;
    border: none;
    line-height: unset;
    letter-spacing: unset;
    display: unset;
    text-transform: none;
    text-decoration: unset;
    color: unset;
    font-weight: unset;
}
        .subscription-plan-card img {
    width: 70px;
}
    </style>
    <div class="subscription-plans" style="display:flex">
        <?php display_subscription_plans(); ?>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('subscription_plans', 'subscription_plans_shortcode');