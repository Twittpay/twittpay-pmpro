<?php
/**
 * Handles the customer's return and the server notice from TwittPay.
 *
 * @package TwittPay\PMPro\Webhook
 */

namespace TwittPay\PMPro\Webhook;

use TwittPay\PMPro\Gateway\GatewayInterface;

class WebhookHandler {

private GatewayInterface $gateway;

public function __construct( GatewayInterface $gateway ) {
$this->gateway = $gateway;
}

public function handleWebhook(): void {
try {
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- gateway callbacks carry no nonce.
$type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

switch ( $type ) {
case 'ipn':
$this->handleIPN();
break;
case 'success':
$this->handleSuccess();
break;
case 'cancel':
$this->handleCancel();
break;
default:
throw new \Exception( 'Unknown webhook type' );
}
} catch ( \Exception $e ) {
$this->logError( 'Webhook error: ' . $e->getMessage() );
wp_die( esc_html( $e->getMessage() ), 'Webhook Error', array( 'response' => 400 ) );
}
}

/** The id of the payment comes in the return link or in the notice body. */
private function readTransactionId(): string {
// phpcs:disable WordPress.Security.NonceVerification.Recommended
foreach ( array( 'transactionId', 'transaction_id' ) as $key ) {
if ( ! empty( $_REQUEST[ $key ] ) ) {
return trim( sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) );
}
}
// phpcs:enable

$body = json_decode( (string) file_get_contents( 'php://input' ), true );
if ( is_array( $body ) ) {
foreach ( array( 'transactionId', 'transaction_id' ) as $key ) {
if ( ! empty( $body[ $key ] ) ) {
return trim( sanitize_text_field( $body[ $key ] ) );
}
}
}

return '';
}

/** The notice is never trusted as sent: the payment is verified again. */
private function handleIPN(): void {
$transaction_id = $this->readTransactionId();
if ( '' === $transaction_id ) {
throw new \Exception( 'Transaction ID not found' );
}

$response = $this->gateway->verifyPayment( $transaction_id );
$meta     = $this->metadata( $response );

$order = new \MemberOrder( intval( $meta['order_id'] ?? 0 ) );
if ( empty( $order ) || empty( $order->id ) ) {
throw new \Exception( 'Order not found' );
}

if ( ! $this->validatePayment( $order, $response, $meta ) ) {
throw new \Exception( 'Payment validation failed' );
}

$this->completePayment( $order, $response, $transaction_id );
wp_send_json_success( array( 'message' => 'Payment completed' ) );
}

private function handleSuccess(): void {
$transaction_id = $this->readTransactionId();
if ( '' === $transaction_id ) {
throw new \Exception( 'Invalid transaction ID' );
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
$order    = new \MemberOrder( $order_id );
if ( empty( $order ) || empty( $order->id ) ) {
throw new \Exception( 'Order not found' );
}

$response = $this->gateway->verifyPayment( $transaction_id );
$meta     = $this->metadata( $response );

if ( ! $this->validatePayment( $order, $response, $meta ) ) {
$this->failPayment( $order, 'validation_failed' );
wp_safe_redirect( pmpro_url( 'account' ) );
exit;
}

$this->completePayment( $order, $response, $transaction_id );
wp_safe_redirect( pmpro_url( 'confirmation', '?level=' . $order->membership_id ) );
exit;
}

private function handleCancel(): void {
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
if ( $order_id ) {
$order = new \MemberOrder( $order_id );
if ( ! empty( $order ) && ! empty( $order->id ) && 'success' !== $order->status ) {
$this->failPayment( $order, 'cancelled' );
}
}

wp_safe_redirect( pmpro_url( 'levels' ) );
exit;
}

/** Verify returns metadata as a JSON string. */
private function metadata( array $response ): array {
$meta = $response['metadata'] ?? array();
if ( is_string( $meta ) ) {
$meta = json_decode( $meta, true );
}

return is_array( $meta ) ? $meta : array();
}

private function completePayment( \MemberOrder $order, array $response, string $transaction_id ): void {
// Never complete the same order twice.
if ( 'success' === $order->status ) {
return;
}

/* translators: %s: date and time */
$note  = sprintf( __( 'Payment completed via TwittPay on %s.', 'pmpro-twittpay' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . "\n";
/* translators: %s: transaction id */
$note .= sprintf( __( 'Transaction ID: %s', 'pmpro-twittpay' ), $transaction_id ) . "\n";
/* translators: %s: payment method */
$note .= sprintf( __( 'Payment Method: %s', 'pmpro-twittpay' ), $response['payment_method'] ?? '' ) . "\n";
/* translators: %s: sender number */
$note .= sprintf( __( 'Sender Number: %s', 'pmpro-twittpay' ), $response['sender_number'] ?? '' );

$order->getMembershipLevel();
$order->status                 = 'success';
$order->payment_transaction_id = $transaction_id;
$order->notes                  = $note;
$order->saveOrder();

pmpro_changeMembershipLevel(
array(
'user_id'       => $order->user_id,
'membership_id' => $order->membership_id,
'order_id'      => $order->id,
)
);

do_action( 'pmpro_twittpay_after_payment_completed', $order );
}

private function failPayment( \MemberOrder $order, string $reason ): void {
/* translators: %s: date and time */
$note  = sprintf( __( 'Payment failed via TwittPay on %s.', 'pmpro-twittpay' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . "\n";
/* translators: %s: reason */
$note .= sprintf( __( 'Reason: %s', 'pmpro-twittpay' ), $reason );

$order->status = 'failed';
$order->notes  = $note;
$order->saveOrder();

do_action( 'pmpro_twittpay_after_payment_failed', $order, $reason );
}

/** The payment must be complete, for this order, and cover its amount. */
private function validatePayment( \MemberOrder $order, array $response, array $meta ): bool {
if ( 'COMPLETED' !== strtoupper( trim( (string) ( $response['status'] ?? '' ) ) ) ) {
$this->logError( 'Payment is not completed.' );
return false;
}

$order_total = $this->calculateOrderTotal( $order );
$paid_amount = (float) ( $response['amount'] ?? 0 );

if ( $paid_amount + 0.01 < $order_total ) {
$this->logError( sprintf( 'Amount mismatch - order %f, paid %f.', $order_total, $paid_amount ) );
return false;
}

if ( ( $meta['order_code'] ?? '' ) !== $order->code || (int) ( $meta['order_id'] ?? 0 ) !== (int) $order->id ) {
$this->logError( 'This payment belongs to a different order.' );
return false;
}

return true;
}

private function calculateOrderTotal( \MemberOrder $order ): float {
$subtotal = (float) $order->subtotal;
$tax      = (float) $order->getTaxForPrice( $subtotal );

return round( $subtotal + $tax, 2 );
}

private function logError( string $message ): void {
error_log( '[TwittPay Error] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}
}
