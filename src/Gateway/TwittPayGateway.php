<?php
/**
 * TwittPay gateway for Paid Memberships Pro.
 *
 * @package TwittPay\PMPro\Gateway
 */

namespace TwittPay\PMPro\Gateway;

use TwittPay\PMPro\Api\ApiClient;

class TwittPayGateway implements GatewayInterface {

private ApiClient $api_client;

public function __construct() {
$this->api_client = new ApiClient( (string) get_option( 'pmpro_twittpay_api_key' ) );
}

/** Create the payment and send the member to the TwittPay checkout. */
public function processPayment( &$order ): bool {
try {
if ( 'BDT' !== strtoupper( (string) get_option( 'pmpro_currency' ) ) ) {
$order->error = esc_html__( 'TwittPay accepts BDT. Set the currency to BDT in the payment settings.', 'pmpro-twittpay' );
return false;
}

$response = $this->api_client->createPayment( $this->preparePaymentData( $order ) );

if ( ! empty( $response['payment_url'] ) ) {
// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- trusted checkout address.
wp_redirect( $response['payment_url'] );
exit;
}

if ( ! empty( $response['message'] ) ) {
/* translators: %s: error from the payment service */
$order->error = sprintf( esc_html__( 'Gateway error: %s', 'pmpro-twittpay' ), esc_html( $response['message'] ) );
} else {
$order->error = esc_html__( 'Could not get a payment link from TwittPay', 'pmpro-twittpay' );
}

return false;
} catch ( \Exception $e ) {
$order->error = $e->getMessage();
return false;
}
}

public function verifyPayment( string $transaction_id ): array {
return $this->api_client->verifyPayment( $transaction_id );
}

public function preparePaymentData( &$order ): array {
$base = array( 'action' => 'twittpay-pmpro-webhook' );

$success_url = add_query_arg( array_merge( $base, array( 'type' => 'success', 'order_id' => $order->id ) ), admin_url( 'admin-ajax.php' ) );
$cancel_url  = add_query_arg( array_merge( $base, array( 'type' => 'cancel', 'order_id' => $order->id ) ), admin_url( 'admin-ajax.php' ) );
$ipn_url     = add_query_arg( array_merge( $base, array( 'type' => 'ipn' ) ), admin_url( 'admin-ajax.php' ) );

$email = pmpro_getParam( 'bemail', 'REQUEST' );
if ( empty( $email ) ) {
$user  = get_userdata( $order->user_id );
$email = $user ? $user->user_email : 'default@gmail.com';
}

$name = isset( $order->billing->name ) ? trim( (string) $order->billing->name ) : '';

return array(
'cus_name'    => '' !== $name ? $name : 'Customer',
'cus_email'   => $email,
'amount'      => number_format( $this->calculateAmount( $order ), 2, '.', '' ),
'metadata'    => array(
'order_id'      => (string) $order->id,
'user_id'       => (string) $order->user_id,
'membership_id' => (string) $order->membership_id,
'order_code'    => (string) $order->code,
),
'success_url' => $success_url,
'cancel_url'  => $cancel_url,
'webhook_url' => $ipn_url,
);
}

private function calculateAmount( $order ): float {
$initial_payment     = $order->subtotal;
$initial_payment_tax = $order->getTaxForPrice( $initial_payment );

return pmpro_round_price( (float) $initial_payment + (float) $initial_payment_tax );
}
}
