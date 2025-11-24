<?php
/**
 * Template
 *
 * @var string $tab
 * @var string $stage
 * @var ssl_zen_admin self
 */
?>
<div class="ssl-zen-content-container <?php echo esc_attr( $tab == 'review' ? 'review-page' : '' ); ?>">
    <header class="header clearfix">
        <div class="container">
            <div class="row align-items-center ">
                <div class="col-lg-6 text-lg-left text-center logo mb-3 mb-lg-0">
                    <img src="<?php echo esc_url( SSL_ZEN_URL ); ?>img/logo.svg" alt="logo">
                    <span>V<?php echo esc_html( SSL_ZEN_PLUGIN_VERSION ); ?></span>
                    <span><?php echo esc_html( sz_fs()->can_use_premium_code__premium_only() ? 'Premium' : ' Free' ); ?></span>
                </div>
                <div class="col-lg-6 text-lg-right text-center external-actions-container">
					<?php
					// show settings button only when the stage is that.
					if ( $stage === 'settings' && ssl_zen_helper::isTabAvailableAtThisStage( $tab, 'settings', ssl_zen_admin::$allowedTabs ) ) { ?>
                        <a class="settings" href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=settings' ); ?>">
							<?php _e( 'Settings', 'ssl-zen' ); ?>
                        </a>
					<?php }
					if ( ssl_zen_helper::isTabAvailableAtThisStage( $tab, 'upgrade', ssl_zen_admin::$allowedTabs ) && SSLZenCPanel::detect_cpanel() ) { ?>
                        <a class="upgrade" href="https://checkout.freemius.com/mode/dialog/plugin/4586/plan/7397/licenses/1/">
							<?php _e( 'Upgrade', 'ssl-zen' ); ?>
                        </a>
					<?php }
					if ( $stage !== 'settings' ) { ?>
                        <a class="settings" href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=settings' ); ?>">
							<?php _e( 'Debug', 'ssl-zen' ); ?>
                        </a>
					<?php }
					if ( ssl_zen_helper::isTabAvailableAtThisStage( $tab, 'support', ssl_zen_admin::$allowedTabs ) ) { ?>
                        <a class="support" href="<?php echo admin_url( 'admin.php?page=ssl_zen-contact' ); ?>">
							<?php _e( 'Support', 'ssl-zen' ); ?>
                        </a>
					<?php } ?>
                </div>
            </div>
        </div>
    </header>
    <div class="container mt-5">
		<?php
		// Check weather to show steps navigation
		if ( ssl_zen_helper::showLayoutPart( $tab, ssl_zen_admin::$allowedTabs, 'steps_nav' ) ) {
			ssl_zen_admin::stepsNavigation( $tab );
		}
		// Show message container
		self::showMessage();
		?>
        <section class="ssl-zen-container">
			<?php
			$tabMethod = isset( ssl_zen_admin::$allowedTabs[ $tab ]['method'] ) ? ssl_zen_admin::$allowedTabs[ $tab ]['method'] : '';
			if ( method_exists( ssl_zen_admin::class, $tabMethod ) ) {
				self::$tabMethod();
			} else {
				$tabMethod = ssl_zen_admin::$allowedTabs[ get_option( 'ssl_zen_settings_stage', 'system_requirements' ) ]['method'];
				self::$tabMethod();
			}
			?>
        </section>
    </div>
	<?php if ( ssl_zen_helper::showLayoutPart( $tab, ssl_zen_admin::$allowedTabs, 'footer' ) && ! sz_fs()->is_premium() && SSLZenCPanel::detect_cpanel() ) {
		$upgradeUrl = add_query_arg( array(
			'checkout'      => 'true',
			'plan_id'       => 7397,
			'plan_name'     => 'pro',
			'billing_cycle' => 'annual',
			'pricing_id'    => 7115,
			'currency'      => 'usd'
		), sz_fs()->get_upgrade_url() );
		?>
        <footer class="ssl-zen-footer container">
            <a href="<?php echo esc_url( $upgradeUrl ); ?>">
                <div class="row align-items-center">
                    <div class="col-lg-3 text-center text-lg-left ssl-zen-pro-quote">
                        <h4><?php _e( 'Never Pay for SSL Again!', 'ssl-zen' ); ?></h4>
                        <p class="mt-1"><?php _e( 'Upgrade to our Pro Plan', 'ssl-zen' ); ?></p>
                    </div>
                    <div class="col-lg-7 ssl-zen-pro-features mt-4 mt-lg-0">
                        <span><?php _e( 'AUTOMATIC', 'ssl-zen' ); ?><br><?php _e( 'DOMAIN VERIFICATION', 'ssl-zen' ); ?></span>
                        <span><?php _e( 'AUTOMATIC SSL INSTALLATION', 'ssl-zen' ); ?></span>
                        <span><?php _e( 'AUTOMATIC SSL RENEWAL', 'ssl-zen' ); ?></span>
                    </div>
                    <div class="col-lg-2 text-center text-lg-right mt-4 mt-lg-0 align ssl-zen-pro-upgrade">
                        <button><?php _e( 'UPGRADE', 'ssl-zen' ); ?></button>
                    </div>
                </div>
            </a>
        </footer>
	<?php } ?>
</div>
