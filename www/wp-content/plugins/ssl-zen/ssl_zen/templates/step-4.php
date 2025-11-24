<?php
/**
 * Template
 *
 * @var string $nonce_field
 */
?>
<form name="frmstep4" id="frmstep4" action="" method="post">
	<?php wp_nonce_field( 'ssl_zen_activate_ssl', $nonce_field ); ?>
    <div class="ssl-zen-steps-container p-0 mb-4">
        <div class="row ssl-zen-activate-ssl-container">
            <div class="col-md-8 steps">
                <div>
                    <h4 class="mb-4">
						<?php _e( 'To start serving your wordpress website over SSL, we need to do the following:', 'ssl-zen' ); ?>
                    </h4>
                    <ul>
						<?php if ( sz_fs()->is_plan( 'cdn', true ) ) { ?>
                            <li>
                                <span><?php _e( 'All incoming HTTP requests on your website will be redirected to HTTPS', 'ssl-zen' ); ?></span>
                            </li>
                            <li>
                                <span><?php _e( 'Add code to wp-config.php to enable administration over SSL', 'ssl-zen' ); ?></span>
                            </li>
                            <li>
                                <span><?php _e( 'Add code to avoid insecure content warning', 'ssl-zen' ); ?></span>
                            </li>
						<?php } else { ?>
                            <li>
                                <span><?php _e( 'All incoming HTTP requests on your website will be redirected to HTTPS', 'ssl-zen' ); ?></span>
                            </li>
                            <li>
                                <span><?php _e( 'Your site URL and Home URL will be changed from HTTP  to HTTPS', 'ssl-zen' ); ?></span>
                            </li>
                            <li>
                                <span><?php _e( 'We will fix insecure content warning by replacing HTTP URL\'s to HTTPS URL\'s', 'ssl-zen' ); ?></span>
                            </li>
						<?php } ?>
                    </ul>
					<?php if ( ! sz_fs()->is_plan( 'cdn', true ) && ! ( SSLZenCPanel::detect_cpanel() && sz_fs()->is_premium() ) ) :
						// Note that in case we will show this section we need to disable the next button below
						?>
                        <div class="checkbox checkbox-success">
                            <input type="checkbox" class="styled"
                                   name="ssl_zen_renew_confirm"
                                   id="ssl_zen_renew_confirm"
                                   value="1" required=""
                                   aria-required="true">
                            <label for="ssl_zen_renew_confirm">
								<?php echo sprintf(
								/* translators: 1: Start of important span 2: End of important span*/
									__(
										'If I don\'t renew my SSL certificate every %1$s 90 days %2$s,
                                                my website will start showing a', 'ssl-zen'
									),
									'<span class="important">',
									'</span>'
								); ?>
                            </label>
                            <div class="mt-2 note">
								<?php echo sprintf(
								/* translators: 1: Start of important danger span 2: End of important danger span 3: Start of span 4: End of span*/
									__( '%1$s Not Secure %2$s %3$s warning to my website visitors.%4$s', 'ssl-zen' ),
									'<span class="important red-rect">',
									'</span>',
									'<span>',
									'</span>'
								) ?>
                            </div>
                        </div>
					<?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div>
                    <div class="note">
                        <div class="head">
                            <span class="important"><?php _e( 'Note', 'ssl-zen' ) ?></span>
                        </div>
                        <div class="body">
                            <span><?php _e( 'Remember to clear your browser cache after SSL is activated on your website.', 'ssl-zen' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-right mb-4">
        <a class="primary next"
           href="#"><?php _e( 'Next', 'ssl-zen' ); ?></a>
    </div>
</form>
