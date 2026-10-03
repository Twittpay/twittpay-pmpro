<?php
/**
 * Admin Notice Handler
 *
 * Handles various admin notices and plugin links for the TwittPay PMPro integration.
 *
 * @package TwittPay\PMPro\Admin
 * @since   1.0.0
 */

namespace TwittPay\PMPro\Admin;

/**
 * Class NoticeHandler
 *
 * Manages admin notices, plugin action links, and meta information
 * for the TwittPay PMPro integration.
 *
 * @package TwittPay\PMPro\Admin
 * @since   1.0.0
 */
class NoticeHandler {

	/**
	 * Display notice for missing PMPro plugin dependency.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function missingNotice(): void {
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			wp_kses(
				sprintf(
					/* translators: %1$s: Plugin name, %2$s: Required plugin name */
					esc_html__(
						'%1$s requires %2$s to be installed and activated.',
						'pmpro-twittpay'
					),
					'<strong>TwittPay Gateway</strong>',
					'<strong>Paid Memberships Pro</strong>'
				),
				array(
					'strong' => array(),
				)
			)
		);
	}

	/**
	 * Display admin notices if any are set.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function displayNotices(): void {
		if ( get_transient( 'pmpro-twittpay-admin-notice' ) ) {
			$this->displayActivationNotice();
			delete_transient( 'pmpro-twittpay-admin-notice' );
		}
	}

	/**
	 * Display plugin activation success notice.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function displayActivationNotice(): void {
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			wp_kses(
				sprintf(
					/* translators: %s: Settings page URL */
					__(
						'Thank you for activating the Paid Memberships Pro: TwittPay Add On. <a href="%s">Visit the payment settings page</a> to configure the TwittPay Payment Gateway.',
						'pmpro-twittpay'
					),
					esc_url( admin_url( 'admin.php?page=pmpro-paymentsettings' ) )
				),
				array(
					'a' => array(
						'href' => array(),
					),
				)
			)
		);
	}

	/**
	 * Add plugin action links.
	 *
	 * @since  1.0.0
	 * @param  array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function addActionLinks( array $links ): array {
		if ( current_user_can( 'manage_options' ) ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'admin.php?page=pmpro-paymentsettings' ) ),
				esc_html__( 'Configure TwittPay', 'pmpro-twittpay' )
			);
		}
		return $links;
	}

	/**
	 * Add plugin row meta links.
	 *
	 * @since  1.0.0
	 * @param  array  $links Existing plugin row meta links.
	 * @param  string $file  Plugin file path.
	 * @return array Modified plugin row meta links.
	 */
	public function addPluginRowMeta( array $links, string $file ): array {
		if ( strpos( $file, 'pmpro-twittpay.php' ) !== false ) {
			$new_links = array(
				'docs'    => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( 'https://github.com/Twittpay/twittpay-pmpro' ),
					esc_html__( 'Documentation', 'pmpro-twittpay' )
				),
				'support' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( 'https://twittpay.com' ),
					esc_html__( 'Support', 'pmpro-twittpay' )
				),
			);
			$links     = array_merge( $links, $new_links );
		}
		return $links;
	}
}
