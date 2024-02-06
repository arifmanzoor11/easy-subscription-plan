<?php if (isset($_POST['esysubscription_setting_submit'])) {
    $esysubscription_setting = serialize(array($_POST['easy_sub_currency_selector'],$_POST['easy_sub_client_id'],$_POST['easy_sub_currency_position'],$_POST['easy_sub_currency_format']));
    update_option('esysubscription_setting', $esysubscription_setting);
}
 $get_esysubscription_setting = unserialize(get_option('esysubscription_setting'));
//  print_r($get_esysubscription_setting);
  ?>
<div class="wrap">
    <style>
        .subscrtion-design{min-width: 300px;}
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
                                    <option <?php echo ($get_esysubscription_setting[0] == 'cad') ? 'selected="selected"' : ''; ?> value="cad">CAD</option>
                                    <option <?php echo ($get_esysubscription_setting[0] == 'usd') ? 'selected="selected"' : ''; ?> value="usd">USD</option>
                                    <option <?php echo ($get_esysubscription_setting[0] == 'eur') ? 'selected="selected"' : ''; ?> value="eur">EUR</option>
                                    <option <?php echo ($get_esysubscription_setting[0] == 'gbp') ? 'selected="selected"' : ''; ?> value="gbp">GBP</option>
                                    <option <?php echo ($get_esysubscription_setting[0] == 'ars') ? 'selected="selected"' : ''; ?> value="ars">ARS</option>
                                    <option <?php echo ($get_esysubscription_setting[0] == 'aud') ? 'selected="selected"' : ''; ?> value="aud">AUD</option>
                                    <option <?php echo ($get_esysubscription_setting[0] == 'bbd') ? 'selected="selected"' : ''; ?> value="bbd">BBD</option>
                                </select>
                            
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label>Client ID</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input class="subscrtion-design" type="text" value="<?php echo $get_esysubscription_setting[1] ?>" name="easy_sub_client_id" placeholder="">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label>Currency Position</label>
                            </th>
                            <td class="forminp forminp-text">
                                <select class="subscrtion-design" name="easy_sub_currency_position">
                                    <option <?php echo ($get_esysubscription_setting[2] == 'before') ? 'selected="selected"' : ''; ?> value="before" selected="selected">Before</option>
                                    <option <?php echo ($get_esysubscription_setting[2] == 'before_with_space') ? 'selected="selected"' : ''; ?> value="before_with_space">Before with space</option>
                                    <option <?php echo ($get_esysubscription_setting[2] == 'after') ? 'selected="selected"' : ''; ?> value="after">After</option>
                                    <option <?php echo ($get_esysubscription_setting[2] == 'after_with_space') ? 'selected="selected"' : ''; ?> value="after_with_space">After with space</option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label>Price Display Format</label>
                            </th>
                            <td class="forminp forminp-text">
                            <select class="subscrtion-design" name="easy_sub_currency_format">
                                    <option <?php echo ($get_esysubscription_setting[3] == 'without_insignificant_zeroes') ? 'selected="selected"' : ''; ?> value="without_insignificant_zeroes" selected="selected">$100</option>
                                    <option <?php echo ($get_esysubscription_setting[3] == 'with_insignificant_zeroes') ? 'selected="selected"' : ''; ?> value="with_insignificant_zeroes">$100.00</option>
                                </select>  
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button name="esysubscription_setting_submit" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
        </form>
    </div>

<?php