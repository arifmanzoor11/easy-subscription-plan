<?php 
$cpt_public_slug_subscription_plan = "easysubscription";
add_action( 'add_meta_boxes_' . $cpt_public_slug_subscription_plan, 'adding_custom_meta_boxes_subscription_plan' );

function adding_custom_meta_boxes_subscription_plan(){
    global $cpt_public_slug_subscription_plan;
    add_meta_box(
        'plugin-site',
        __( 'Subscription Plan Details', 'text_domain' ),
        'cpt_form_subscription_plan',
        $cpt_public_slug_subscription_plan,
        'normal',
        'high'
    );
}
function cpt_form_subscription_plan() {
    global $post, $wpdb;

    // Fetching saved values from the database
    $table_name = $wpdb->prefix . 'easy_subscription_plan';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post->ID);
    $saved_data = $wpdb->get_results($query, ARRAY_A);
    // print_r($saved_data);
    $saved_values = array_column($saved_data, 'meta_value', 'meta_key');
    // print_r($saved_values);


    // Setting default values if no data found
    $easy_sub_duration = isset($saved_values['easy_sub_duration']) ? $saved_values['easy_sub_duration'] : '';
    $easy_sub_duration_days = isset($saved_values['easy_sub_duration_days']) ? $saved_values['easy_sub_duration_days'] : 'day';
    $easy_sub_price = isset($saved_values['easy_sub_price']) ? $saved_values['easy_sub_price'] : '';
    $easy_sub_status = isset($saved_values['easy_sub_status']) ? $saved_values['easy_sub_status'] : 'active';
    $easy_sub_services = get_pages();
    // variable that gets all the pages
    ?>

    <label for=""></label>
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row">
                    <label>Duration</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" placeholder="10" name="easy_sub_duration" value="<?php echo esc_attr($easy_sub_duration); ?>">
                    <select id="" name="easy_sub_duration_days">
                        <option value="day" <?php selected($easy_sub_duration_days, 'day'); ?>>Day(s)</option>
                        <option value="week" <?php selected($easy_sub_duration_days, 'week'); ?>>Week(s)</option>
                        <option value="month" <?php selected($easy_sub_duration_days, 'month'); ?>>Month(s)</option>
                        <option value="year" <?php selected($easy_sub_duration_days, 'year'); ?>>Year(s)</option>
                    </select>
                    <small>Set the subscription duration. Leave 0 for unlimited.</small>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label>Price</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" placeholder="5" name="easy_sub_price" value="<?php echo esc_attr($easy_sub_price); ?>"> <span>USD</span>
                    <br>
                    <small>Amount you want to charge people who join this plan. Leave 0 if you want this plan to be free.</small>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label>Status</label>
                </th>
                <td class="forminp forminp-text">
                    <select id="" name="easy_sub_status">
                        <option value="active" <?php selected($easy_sub_status, 'active'); ?>>Active</option>
                        <option value="inactive" <?php selected($easy_sub_status, 'inactive'); ?>>Inactive</option>
                    </select>
                    <br>
                    <small>Only active subscription plans will be displayed to the user.</small>
                </td>
                </tr>

                <tr valign="top">
                <th scope="row">
                    <label>Discription</label>
                </th>
                <td class="forminp forminp-text">
                <?php 
                  $content = stripslashes(get_meta_subscription_plan('easy_subscription_plan',get_the_ID(),'subscription_plan_content'));
                  
                    $custom_editor_id = "subscription_plan_content";
                    $custom_editor_name = "subscription_plan_content";
                    $args = array(
                            'media_buttons' => true, // This setting removes the media button.
                            'textarea_name' => $custom_editor_name, // Set custom name.
                            // 'textarea_rows' => get_option('subscription_plan_content', 10), //Determine the number of rows.
                            'quicktags' => true, // Remove view as HTML button.
                        );
                    wp_editor( $content, $custom_editor_id, $args ); ?>
                </td>
                </tr>


                <tr valign="top">
                <th scope="row">
                    <label>Services</label>
                </th>
                <td>
                    <?php
                    // Checking if pages exists
                    if ($easy_sub_services) {
                        echo '<ul>';
                        foreach ($easy_sub_services as $page) {
                            echo '<li>';
                            echo '<label><input type="checkbox" name="selected_pages[]" value="' . $page->ID . '"> ' . $page->post_title . '</label>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo 'No pages found.';
                    }
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
}


add_action('save_post', 'save_prompt');
function save_prompt($post_id) {
    // Skipping if autosave or a revision
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    global $wpdb;
  
    $table_name = $wpdb->prefix . 'easy_subscription_plan';

    // Check if the fields are set
    if (isset($_POST['easy_sub_duration'], $_POST['easy_sub_duration_days'], $_POST['easy_sub_price'], $_POST['easy_sub_status'])) {
        $easy_sub_duration = $_POST['easy_sub_duration'];
        $easy_sub_duration_days = $_POST['easy_sub_duration_days'];
        $easy_sub_price = $_POST['easy_sub_price'];
        $easy_sub_status = $_POST['easy_sub_status'];
        $subscription_plan_content = $_POST['subscription_plan_content'];
      
        add_meta_subscription_plan('easy_subscription_plan', $post_id, 'easy_sub_duration', $easy_sub_duration);
        add_meta_subscription_plan('easy_subscription_plan', $post_id, 'easy_sub_duration_days', $easy_sub_duration_days);
        add_meta_subscription_plan('easy_subscription_plan', $post_id, 'easy_sub_price', $easy_sub_price);
        add_meta_subscription_plan('easy_subscription_plan', $post_id, 'easy_sub_status', $easy_sub_status);
        add_meta_subscription_plan('easy_subscription_plan', get_the_ID() , 'subscription_plan_content', $subscription_plan_content);
    }
}