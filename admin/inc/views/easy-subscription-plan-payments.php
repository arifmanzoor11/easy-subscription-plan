<div class="wrap">
    <h1><?php _e( 'Payments' ); ?></h1>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th id="cb" class="manage-column column-columnname num" scope="col">User</th> 
                <th id="columnname" class="manage-column column-columnname num" scope="col">Subscription</th>
                <th id="columnname" class="manage-column column-columnname num" scope="col">Amount</th> 
                <th id="columnname" class="manage-column column-columnname num" scope="col">Date / Time</th> 
                <th id="columnname" class="manage-column column-columnname num" scope="col">Plan Type</th> 
                <th id="columnname" class="manage-column column-columnname num" scope="col">Transaction ID</th> 
                <th id="columnname" class="manage-column column-columnname num" scope="col">Status</th>
                <th id="columnname" class="manage-column column-columnname num" scope="col">Actions</th> <!-- New column for actions -->
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th class="manage-column column-cb check-column" scope="col"></th>
                <th class="manage-column column-columnname" scope="col"></th>
                <th class="manage-column column-columnname num" scope="col"></th>
                <th class="manage-column column-columnname num" scope="col"></th>
                <th class="manage-column column-columnname num" scope="col"></th>
                <th class="manage-column column-columnname num" scope="col"></th>
                <th class="manage-column column-columnname num" scope="col"></th>
                <th class="manage-column column-columnname num" scope="col"></th> <!-- Empty footer for consistency -->
            </tr>
        </tfoot>

        <tbody>
            <?php
            global $wpdb;
            $table_name_subscription = $wpdb->prefix . 'user_subscription_records';
            $payments = $wpdb->get_results("SELECT * FROM $table_name_subscription", ARRAY_A);
            
            foreach ($payments as $payment) {
                echo "<tr>";
                echo "<td class='manage-column column-columnname num' style='vertical-align: middle; text-align: center;'>" . $payment['user_name'] . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $payment['subscription_name'] . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>$" . $payment['amount'] . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $payment['datetime'] . "</td>";
                // Deserialize the plan_type
                $plan_type_array = unserialize($payment['plan_type']);
                // Concatenate the values for display
                $plan_type_display = $plan_type_array['plan_Duration'] . ' ' . $plan_type_array['plan_Days'];
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $plan_type_display . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $payment['transaction_id'] . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $payment['status'] . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>";
                
                // Get the array of blocked user IDs from options
                $blocked_users = get_option('blocked_users', array());
                
                // Check if the current user ID is in the blocked users array
                if (in_array($payment['user_id'], $blocked_users)) {
                    ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                        <input type="hidden" name="action" value="unblock_user">
                        <input type="hidden" name="user_id" value="<?php echo esc_attr($payment['user_id']); ?>">
                        <input type="hidden" name="redirect_url" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                        <?php submit_button('Unblock User', 'primary', 'block_user_submit', false); ?>
                    </form>
                    <?php
                } else {
                    ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                        <input type="hidden" name="action" value="block_user">
                        <input type="hidden" name="user_id" value="<?php echo esc_attr($payment['user_id']); ?>">
                        <input type="hidden" name="redirect_url" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                        <?php submit_button('Block User', 'primary', 'block_user_submit', false); ?>
                    </form>
                    <?php
                }
                
                echo "</td>"; // Add button to block user
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
