<?php
/**
 * TwittPay API client.
 *
 * @package TwittPay\PMPro\Api
 */

namespace TwittPay\PMPro\Api;

class ApiClient {

private const ENDPOINT = 'https://checkout.twittpay.com';

private string $api_key;

public function __construct( string $api_key ) {
$this->api_key = trim( $api_key );
}

/** Create a payment. Returns the decoded answer, which holds payment_url. */
public function createPayment( array $data ): array {
return $this->send_request( '/api/payment/create', $data );
}

/** Ask TwittPay for the real state of a payment. */
public function verifyPayment( string $transaction_id ): array {
return $this->send_request( '/api/payment/verify', array( 'transaction_id' => $transaction_id ) );
}

private function send_request( string $path, array $data ): array {
if ( '' === $this->api_key ) {
throw new ApiException( esc_html__( 'The Brand Key is not set', 'pmpro-twittpay' ) );
}

$response = wp_remote_post(
self::ENDPOINT . $path,
array(
'timeout' => 45,
'headers' => array(
'API-KEY'      => $this->api_key,
'Content-Type' => 'application/json',
'Accept'       => 'application/json',
),
'body'    => wp_json_encode( $data ),
)
);

if ( is_wp_error( $response ) ) {
throw new ApiException( esc_html( $response->get_error_message() ) );
}

$result = json_decode( wp_remote_retrieve_body( $response ), true );

if ( ! is_array( $result ) ) {
throw new ApiException( esc_html__( 'Unreadable response from the payment service', 'pmpro-twittpay' ) );
}

return $result;
}
}
