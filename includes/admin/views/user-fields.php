<?php
/**
 * HTML output for an extra user profile field for phone
 */
?>
<h3><?php _e("Extra profile information", "blank"); ?></h3>

<table class="form-table">
    <tr>
        <th><label for="phone"><?php _e("Phone"); ?></label></th>
        <td>
            <input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'user_phone', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Please enter your phone number."); ?></span>
        </td>
    </tr>
    <tr>
        <th><label for="secureID"><?php _e("secureID"); ?></label></th>
        <td>
            <input readonly type="text" name="secureID" id="secureID" value="<?php echo esc_attr( get_the_author_meta( 'user_secureID', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Your secureID will be stored here.", 'subscreasy'); ?></span>
        </td>
    </tr>

    <tr>
        <th><label for="customer_portal"><?php _e("Customer portal", 'subscreasy'); ?></label></th>
        <td>
            <?php
            $options = get_option( 'subscreasy', array() );
            $secureID = get_the_author_meta( 'user_secureID', $user->ID );

            // Customer portal URL.
            $customer_portal = 'production' === $options['environment'] ? 'https://' . $options['site_name'] . '.subscreasy.com/portal/na/subs/' . $secureID . '/' : 'https://' . $options['site_name'] . '.aboneliks.xyz/portal/na/subs/' . $secureID . '/';

            if ($secureID == ""): // If the secureID is not yet stored
            ?>
                <?php _e( 'Please make a subscription first', 'subscreasy' ); ?>
            <?php
            else: // If the secureID is already stored
            ?>
                <a href="<?php echo $customer_portal; ?>"><?php echo $customer_portal; ?></a>
            <?php
            endif;
            ?>
            <br />
            <span class="description"><?php _e("This is your customer portal URL."); ?></span>
        </td>
    </tr>
</table>