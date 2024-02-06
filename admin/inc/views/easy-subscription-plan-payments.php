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
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $payment['plan_type'] . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $payment['transaction_id'] . "</td>";
                echo "<td class='manage-column column-columnname' style='vertical-align: middle; text-align: center;'>" . $payment['status'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
