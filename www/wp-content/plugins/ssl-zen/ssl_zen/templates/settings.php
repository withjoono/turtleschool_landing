<?php
/**
 * Template
 *
 * @var array $tabsToShow
 * @var string $activeTab
 * @var string $primaryDomain
 * @var string $issuer
 * @var string $days
 * @var string $circleColor
 * @var string $renewButtonClass
 * @var string $allowRenew
 * @var string $miniMessage
 * @var string $deactivateMsg
 * @var array $serverStatusFields
 * @var array $wordpressStatusFields
 */
?>
<form name="frmSettings" id="frmSettings" action="" method="post">
	<?php wp_nonce_field( 'ssl_zen_settings', 'ssl_zen_settings_nonce' ); ?>
    <ul class="ssl-zen-settings-tab-container d-flex mb-4">
		<?php if ( in_array( 'advanced', $tabsToShow, true ) ) : ?>
            <li data-tab="advanced"
                class="advanced <?php echo esc_attr( $activeTab === 'advanced' ? 'active' : '' ); ?>">
				<?php _e( 'Advanced', 'ssl-zen' ) ?>
            </li>
		<?php endif; ?>
		<?php if ( in_array( 'status', $tabsToShow, true ) ) : ?>
            <li data-tab="status" class="status <?php echo esc_attr( $activeTab === 'status' ? 'active' : '' ); ?>">
				<?php _e( 'Status', 'ssl-zen' ) ?>
            </li>
		<?php endif; ?>
		<?php if ( in_array( 'debug', $tabsToShow, true ) ) : ?>
            <li data-tab="debug" class="debug <?php echo esc_attr( $activeTab === 'debug' ? 'active' : '' ); ?>">
				<?php _e( 'Debug', 'ssl-zen' ) ?>
            </li>
		<?php endif; ?>
    </ul>
    <div class="ssl-zen-steps-container p-0 mb-4 border-0">
		<?php if ( in_array( 'advanced', $tabsToShow, true ) ) : ?>
            <div class="row ssl-zen-settings-container advanced-container">
                <div class="col-md-4">
                    <div class="table">
                        <div class="head">Renew SSL Certificate</div>
                        <div class="body">
                            <ul>
                                <li class="mb-4 mt-3">
                                    <span class="d-block title"><?php _e( 'Issued to', 'ssl-zen' ); ?></span>
                                    <span class="d-block"><?php echo esc_html( $primaryDomain ); ?></span>
                                </li>
                                <li class="mb-4">
                                    <span class="d-block title"><?php _e( 'Issued by', 'ssl-zen' ); ?></span>
                                    <span class="d-block"><?php echo esc_html( $issuer ); ?></span>
                                </li>
                                <li class="mb-4">
                                    <span class="d-block title mb-4">Certificate Validity</span>
                                    <div class="d-flex justify-content-start ">
										<?php
										if ( ! empty( $days ) && ! empty( $circleColor ) ) :
											?>
                                            <div class="days-left-container">
                                                <div class="days-num d-flex align-items-center justify-content-center">
                                                    <span><?php echo esc_html( $days ); ?></span>
                                                    <span>days</span>
                                                </div>
                                                <div
                                                        class="days-left"
                                                        data-donutty
                                                        data-radius=15
                                                        data-text="days"
                                                        data-min=0
                                                        data-max=90
                                                        data-value=<?php echo esc_attr( $days ); ?>
                                                        data-thickness=3
                                                        data-padding=0
                                                        data-round=true
                                                        data-color="<?php echo esc_attr( $circleColor ); ?>"
                                                >
                                                </div>
                                            </div>
										<?php endif; ?>
                                    </div>
                                </li>
                                <li>
                                    <a
                                            href="#"
                                            class="primary renew <?php echo esc_attr( $renewButtonClass ); ?> <?php echo esc_attr( empty( $allowRenew ) ? 'disabled' : '' ); ?>"
                                    >
                                        RENEW CERTIFICATE
                                    </a>
									<?php if ( $miniMessage ) : ?>
                                        <span class="mini-message d-block w-100"><?php echo esc_attr( $miniMessage ); ?></span>
									<?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="table">
                        <div class="head"><?php _e( 'Advanced settings', 'ssl-zen' ); ?></div>
                        <div class="body right">
                            <ul class="mb-4">
								<?php if ( sz_fs()->is_plan( 'cdn', true ) ) : ?>
                                    <li class="mb-4 line">
                                        <label for="stackpath_purge_everything"
                                               class="d-block title"><?php _e( 'Purge Everything', 'ssl-zen' ) ?></label>
                                        <span><?php _e( 'Remove files from cache globally and retrieve them from your origin again the next time they are requested.', 'ssl-zen' ) ?></span>
                                        <div class="mb-5 mt-2">
                                            <a href="#"
                                               class="d-inline-block primary stackpath-purge sslzen-form-button"
                                               data-hidden="#stackpath_purge_all"
                                               data-hidden-value="1"><?php echo strtoupper( __( 'Purge Everything', 'ssl-zen' ) ); ?></a>
                                            <input type="hidden" name="stackpath_purge_all"
                                                   id="stackpath_purge_all">
                                        </div>
                                    </li>
                                    <li class="d-flex mb-4 line">
                                        <div>
                                            <input class="toggle-event"
                                                   name="stackpath_auto_purge"
                                                   id="stackpath_auto_purge"
                                                   type="checkbox"
												<?php echo ( get_option( 'ssl_zen_stackpath_auto_purge', '' ) == '1' ) ? 'checked="checked"' : ''; ?> >
                                        </div>
                                        <div>
                                            <label for="stackpath_auto_purge"
                                                   class="d-block title"><?php _e( 'Auto Purge', 'ssl-zen' ) ?></label>
                                            <span><?php _e( 'Automatically purge pages and posts as they are updated in WordPress.', 'ssl-zen' ) ?></span>
                                        </div>
                                    </li>
                                    <li class="d-flex mb-4 line">
                                        <div>
                                            <input class="toggle-event"
                                                   name="stackpath_bypass_cache"
                                                   id="stackpath_bypass_cache"
                                                   type="checkbox"
												<?php echo ( get_option( 'ssl_zen_stackpath_bypass_cache', '' ) == '1' ) ? 'checked="checked"' : ''; ?> >
                                        </div>
                                        <div>
                                            <label for="stackpath_bypass_cache"
                                                   class="d-block title"><?php _e( 'Bypass Cache for WordPress cookies', 'ssl-zen' ) ?></label>
                                            <span><?php _e( '[wp-*, wordpress, comment_*, woocommerce_*]', 'ssl-zen' ) ?></span>
                                        </div>
                                    </li>
								<?php else: ?>
                                    <li class="d-flex mb-4 line">
                                        <div>
                                            <input class="toggle-event"
                                                   name="enable_301_htaccess_redirect"
                                                   id="enable_301_htaccess_redirect"
                                                   type="checkbox"
												<?php echo ( get_option( 'ssl_zen_enable_301_htaccess_redirect', '' ) == '1' ) ? 'checked="checked"' : ''; ?> >
                                        </div>
                                        <div>
                                            <label for="enable_301_htaccess_redirect"
                                                   class="d-block title"><?php _e( 'Enable 301 .htaccess redirect', 'ssl-zen' ) ?></label>
                                            <span><?php _e( 'Speeds up your website but might also cause a redirect loop and lock you out of your website.', 'ssl-zen' ) ?></span>
                                        </div>
                                    </li>
                                    <li class="d-flex mb-4 line">
                                        <div>
                                            <input class="toggle-event"
                                                   id="lock_htaccess_file"
                                                   name="lock_htaccess_file"
                                                   type="checkbox"
												<?php echo ( get_option( 'ssl_zen_lock_htaccess_file', '' ) == '1' ) ? 'checked="checked"' : ''; ?> >
                                        </div>
                                        <div>
                                            <label for="lock_htaccess_file"
                                                   class="d-block title"><?php _e( 'Lock down .htaccess file', 'ssl-zen' ) ?></label>
                                            <span><?php _e( 'Disables the plugin from making any changes so you can edit the file manually.', 'ssl-zen' ) ?></span>
                                        </div>
                                    </li>
								<?php endif; ?>
                            </ul>
							<?php if ( sz_fs()->is__premium_only() && sz_fs()->is_plan( 'cdn', true ) ) : ?>
                                <span class="block sslzen-info">
                                            <img
                                                    src="<?php echo esc_url( SSL_ZEN_URL ); ?>img/warning-circle.svg"
                                                    alt="Warning"
                                            >
                                            <a
                                                    href="https://docs.sslzen.com/article/19-how-to-safely-disable-ssl-zen-cdn-plugin"
                                                    target="_blank"
                                                    class="text-muted sslzen-link-underline"
                                            >
                                                <?php _e( 'How to safely disable the plugin?', 'ssl-zen' ); ?>
                                            </a>
                                        </span>
                                <span class="error mini-message d-block w-100"><?php echo esc_attr( $deactivateMsg ); ?></span>
							<?php endif; ?>
                            <div class="mb-2 d-flex justify-content-between">
                                <a href="#"
                                   class="d-inline-block error primary deactivate">DEACTIVATE
                                    PLUGIN</a>
                                <a href="#"
                                   class="d-inline-block primary save">SAVE</a>
                            </div>
                            <div class="message info mt-4">
								<?php
								echo sprintf(
								/* translators: 1: Link tag start 2: Link tag close */
									__( 'Would you like to use SSL Zen plugin in your local language? Click %1$shere%2$s to contribute.' ),
									'<a href="https://translate.wordpress.org/projects/wp-plugins/ssl-zen/">',
									'</a>'
								);
								?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		<?php endif; ?>
		<?php
		if ( ! sz_fs()->is_plan( 'cdn', true ) ) :
			$extraClass = in_array( 'advanced', $tabsToShow, true ) || $activeTab !== 'status' ? 'd-none' : '';
			?>
            <div class="row ssl-zen-settings-container status-container <?php echo esc_attr( $extraClass ); ?>">
                <div class="col-md-5">
                    <table class="table table-bordered">
                        <tbody>
                        <tr class="grey">
                            <th>Server</th>
                            <th>Info</th>
                        </tr>
						<?php foreach (
							$serverStatusFields as $key => $field
						):
							?>
                            <tr>
                                <td><?php echo esc_html( $key ); ?></td>
                                <td><?php echo esc_html( $field ); ?></td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=settings&download=status_info' ); ?>"
                       class="d-inline-block primary mb-2 download-status">Download
                        Status Info</a>
                    <span class="d-block mini-message"><?php _e( 'When asked, please download and share this file with SSL Zen support team.', 'ssl-zen' ) ?></span>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                        <tr class="grey">
                            <th>WordPress</th>
                            <th>Info</th>
                        </tr>
						<?php foreach (
							$wordpressStatusFields as $key => $field
						) :
							?>
                            <tr>
                                <td><?php echo esc_html( $key ); ?></td>
                                <td><?php echo esc_html( $field ); ?></td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
		<?php endif; ?>
		<?php ssl_zen_debug_container( $tabsToShow, $activeTab ); ?>
    </div>
</form>
