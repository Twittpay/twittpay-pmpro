<?php
/**
 * TwittPay settings shown in the Paid Memberships Pro payment settings.
 *
 * @package TwittPay\PMPro
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$display_name = empty( $values['twittpay_display_name'] ) ? 'Pay with bKash, Nagad, Rocket, Upay' : $values['twittpay_display_name'];
$api_key      = isset( $values['twittpay_api_key'] ) ? $values['twittpay_api_key'] : '';
$style_attr   = ( 'twittpay' === $gateway ) ? '' : 'display: none;';
?>
<tr class="pmpro_settings_divider gateway gateway_twittpay" style="<?php echo esc_attr( $style_attr ); ?>">
<td colspan="2">
<hr />
<h2><?php esc_html_e( 'TwittPay Settings', 'pmpro-twittpay' ); ?></h2>
</td>
</tr>
<tr class="gateway gateway_twittpay" style="<?php echo esc_attr( $style_attr ); ?>">
<th scope="row" valign="top">
<label for="twittpay_display_name"><?php esc_html_e( 'Display Name', 'pmpro-twittpay' ); ?>:</label>
</th>
<td>
<input type="text" id="twittpay_display_name" name="twittpay_display_name" size="60" value="<?php echo esc_attr( $display_name ); ?>" />
<p class="description"><?php esc_html_e( 'The text shown on the checkout button.', 'pmpro-twittpay' ); ?></p>
</td>
</tr>
<tr class="gateway gateway_twittpay" style="<?php echo esc_attr( $style_attr ); ?>">
<th scope="row" valign="top">
<label for="twittpay_api_key"><?php esc_html_e( 'Brand Key', 'pmpro-twittpay' ); ?>:</label>
</th>
<td>
<input type="text" id="twittpay_api_key" name="twittpay_api_key" size="60" value="<?php echo esc_attr( $api_key ); ?>" autocomplete="off" />
<p class="description"><?php esc_html_e( 'Copy it from your TwittPay dashboard under Brands. Set the currency to BDT.', 'pmpro-twittpay' ); ?></p>
</td>
</tr>
