<?php
// Small built-in class loader. No Composer needed.
spl_autoload_register(
function ( $class ) {
$base = dirname( __DIR__ ) . '/src/';

if ( 'PMProGateway_TwittPay' === $class ) {
require_once $base . 'Gateway/PMProGateway_TwittPay.php';
return;
}

$prefix = 'TwittPay\\PMPro\\';
if ( 0 !== strpos( $class, $prefix ) ) {
return;
}

$file = $base . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
if ( is_readable( $file ) ) {
require_once $file;
}
}
);
