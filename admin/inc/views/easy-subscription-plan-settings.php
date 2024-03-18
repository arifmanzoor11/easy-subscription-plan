<?php 
if (isset($_POST['esysubscription_setting_submit'])) {
    $blocked_post_types = isset($_POST['blocked_post_types']) ? $_POST['blocked_post_types'] : array();
    $blocked_pages = isset($_POST['blocked_pages']) ? $_POST['blocked_pages'] : array();

    // Convert page IDs to integers
    $blocked_pages = array_map('intval', $blocked_pages);

    // Store all settings including blocked post types, pages, and redirect page
    $esysubscription_setting = serialize(array(
        $_POST['easy_sub_currency_selector'],
        $_POST['easy_sub_client_id'],
        $_POST['easy_sub_currency_position'],
        $_POST['easy_sub_currency_format'],
        $blocked_post_types, // Store blocked post types
        $blocked_pages, // Store blocked pages
        intval($_POST['blocked_page']), // Store selected redirect page as an integer,
        'after_purchasing_plan' => $_POST['after_purchasing_plan']
    ));
    update_option('esysubscription_setting', $esysubscription_setting);
}

$get_esysubscription_setting = unserialize(get_option('esysubscription_setting'));
// Get all public post types excluding "Posts" and "Pages"
$post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
$pages = get_pages(); ?>

<div class="wrap">
    <style>
    .subscrtion-design {
        min-width: 300px;
    }
    </style>
    <h1><?php _e( 'Settings', 'textdomain' ); ?></h1>
    <form action="" method="POST" style="margin-top:20px">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label>Currency</label>
                    </th>
                    <td class="forminp forminp-text">
                        <select class="subscrtion-design" name="easy_sub_currency_selector">
                            <option
                                <?php echo ($get_esysubscription_setting[0] == 'cad') ? 'selected="selected"' : ''; ?>
                                value="cad">CAD</option>
                            <option
                                <?php echo ($get_esysubscription_setting[0] == 'usd') ? 'selected="selected"' : ''; ?>
                                value="usd">USD</option>
                            <option
                                <?php echo ($get_esysubscription_setting[0] == 'eur') ? 'selected="selected"' : ''; ?>
                                value="eur">EUR</option>
                            <option
                                <?php echo ($get_esysubscription_setting[0] == 'gbp') ? 'selected="selected"' : ''; ?>
                                value="gbp">GBP</option>
                            <option
                                <?php echo ($get_esysubscription_setting[0] == 'ars') ? 'selected="selected"' : ''; ?>
                                value="ars">ARS</option>
                            <option
                                <?php echo ($get_esysubscription_setting[0] == 'aud') ? 'selected="selected"' : ''; ?>
                                value="aud">AUD</option>
                            <option
                                <?php echo ($get_esysubscription_setting[0] == 'bbd') ? 'selected="selected"' : ''; ?>
                                value="bbd">BBD</option>
                        </select>

                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>Client ID</label>
                    </th>
                    <td class="forminp forminp-text">
                        <input class="subscrtion-design" type="text"
                            value="<?php echo $get_esysubscription_setting[1] ?>" name="easy_sub_client_id"
                            placeholder="">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>Currency Position</label>
                    </th>
                    <td class="forminp forminp-text">
                        <select class="subscrtion-design" name="easy_sub_currency_position">
                            <option
                                <?php echo ($get_esysubscription_setting[2] == 'before') ? 'selected="selected"' : ''; ?>
                                value="before" selected="selected">Before</option>
                            <option
                                <?php echo ($get_esysubscription_setting[2] == 'before_with_space') ? 'selected="selected"' : ''; ?>
                                value="before_with_space">Before with space</option>
                            <option
                                <?php echo ($get_esysubscription_setting[2] == 'after') ? 'selected="selected"' : ''; ?>
                                value="after">After</option>
                            <option
                                <?php echo ($get_esysubscription_setting[2] == 'after_with_space') ? 'selected="selected"' : ''; ?>
                                value="after_with_space">After with space</option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                <tr valign="top">
                    <th scope="row">
                        <label for="blocked_page"><?php _e( 'Page to Redirect When Blocked', 'textdomain' ); ?></label>
                    </th>
                    <td>
                        <select name="blocked_page">
                            <?php foreach ($pages as $page) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>"
                                <?php selected($get_esysubscription_setting[6], $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <th scope="row">
                    <label for="blocked_page"><?php _e( 'Page to Redirect After Purshing Plan', 'textdomain' ); ?></label>
                </th>
                <td>
                    <select name="after_purchasing_plan">
                        <?php foreach ($pages as $page) : ?>
                        <option value="<?php echo esc_attr($page->ID); ?>"
                            <?php selected($get_esysubscription_setting['after_purchasing_plan'], $page->ID); ?>>
                            <?php echo esc_html($page->post_title); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>Price Display Format</label>
                    </th>
                    <td class="forminp forminp-text">
                        <select class="subscrtion-design" name="easy_sub_currency_format">
                            <option
                                <?php echo ($get_esysubscription_setting[3] == 'without_insignificant_zeroes') ? 'selected="selected"' : ''; ?>
                                value="without_insignificant_zeroes" selected="selected">$100</option>
                            <option
                                <?php echo ($get_esysubscription_setting[3] == 'with_insignificant_zeroes') ? 'selected="selected"' : ''; ?>
                                value="with_insignificant_zeroes">$100.00</option>
                        </select>
                    </td>
                </tr>
                <!-- Post Types to Block -->
                <tr valign="top">
                    <th scope="row">
                        <label for="post_types">Post Types to Block</label>
                    </th>
                    <td>
                        <!-- Display checkboxes for post types -->
                        <?php foreach ($post_types as $post_type) : ?>
                        <?php
                            // Check if $get_esysubscription_setting has the 'blocked_post_types' element before accessing it
                            if (is_array($get_esysubscription_setting) && isset($get_esysubscription_setting[4]) && is_array($get_esysubscription_setting[4])) {
                                $blocked_post_types = $get_esysubscription_setting[4];
                            } else {
                                $blocked_post_types = array(); // Default to empty array
                            }
                            $checked = in_array($post_type->name, $blocked_post_types) ? 'checked' : '';
                            ?>
                        <label>
                            <input type="checkbox" name="blocked_post_types[]"
                                value="<?php echo esc_attr($post_type->name); ?>" <?php echo $checked; ?>>
                            <?php echo esc_html($post_type->label); ?>
                        </label><br>
                        <?php endforeach; ?>
                    </td>
                </tr>

                <!-- Pages to Block -->
                <tr valign="top">
                    <th scope="row">
                        <label for="pages">Pages to Block</label>
                    </th>
                    <td>
                        <!-- Display checkboxes for pages -->
                        <?php foreach ($pages as $page) : ?>
                        <?php
                            // Check if $get_esysubscription_setting has the 'blocked_pages' element before accessing it
                            if (is_array($get_esysubscription_setting) && isset($get_esysubscription_setting[5]) && is_array($get_esysubscription_setting[5])) {
                                $blocked_pages = $get_esysubscription_setting[5];
                            } else {
                                $blocked_pages = array(); // Default to empty array
                            }
                            $checked = in_array($page->ID, $blocked_pages) ? 'checked' : '';
                            ?>
                        <label>
                            <input type="checkbox" name="blocked_pages[]" value="<?php echo esc_attr($page->ID); ?>"
                                <?php echo $checked; ?>>
                            <?php echo esc_html($page->post_title); ?>
                        </label><br>
                        <?php endforeach; ?>
                    </td>
                </tr>

                </tr>

            </tbody>
        </table>
        <button name="esysubscription_setting_submit" class="button-primary woocommerce-save-button" type="submit"
            value="Save changes">Save changes</button>
    </form>
</div>

<?php 