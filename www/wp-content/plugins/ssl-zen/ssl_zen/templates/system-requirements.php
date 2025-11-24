<?php
/**
 * Template
 *
 * @var array $systemRequirements
 * @var string $col
 */
?>
<form name="frmsysreq" id="frmsysreq" action="" method="post">
	<?php wp_nonce_field( 'ssl_zen_system_requirements', 'ssl_zen_system_requirements_nonce' ); ?>
    <div class="ssl-zen-steps-container p-0 border-0">
        <h4 class="ssl-zen-system-requirement-header pb-2 mb-4">
			<?php _e( 'System Requirements Check', 'ssl-zen' ); ?>
        </h4>
        <div class="row ssl-zen-system-requirement-container">
            <div class="col-lg-<?php echo esc_attr( $col ); ?>">
                <table class="table table-bordered">
                    <tbody>
                    <tr class="grey">
                        <th>Server</th>
                        <th><?php _e( 'Info', 'ssl-zen' ); ?></th>
                    </tr>
                    <tr>
                        <td>PHP Version > 5.6.20+</td>
                        <td class="text-center">
							<?php if ( $systemRequirements['php'] ) : ?>
                                <i class="check"></i>
							<?php else: ?>
                                <div class="d-flex justify-content-between align-items-center">
									<?php _e( 'Please ask your hosting provider to upgrade your PHP to the latest version.', 'ssl-zen' ); ?>
                                    <i class="check error"></i>
                                </div>
							<?php endif; ?>
                        </td>
                    </tr>
                    <tr class="grey">
                        <td>cURL enabled</td>
                        <td class="text-center">
							<?php if ( $systemRequirements['curl'] ) : ?>
                                <i class="check"></i>
							<?php else: ?>
                                <div class="d-flex justify-content-between align-items-center">
									<?php _e( 'Please ask your hosting provider to enable cURL on your website server.', 'ssl-zen' ); ?>
                                    <i class="check error"></i>
                                </div>
							<?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>openSSL enabled</td>
                        <td class="text-center">
							<?php if ( $systemRequirements['openssl'] ) : ?>
                                <i class="check"></i>
							<?php else: ?>
                                <div class="d-flex justify-content-between align-items-center">
									<?php _e( 'Please ask your hosting provider to enable open SSL on your website server.', 'ssl-zen' ); ?>
                                    <i class="check error"></i>
                                </div>
							<?php endif; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>

				<?php if ( $col == 3 ) : ?>
                    <span class="mb-4 d-block mini-message">Success! You will be automatically redirected to the plugin page in few seconds ...</span>
                    <a href="#" id="next"
                       class="d-inline-block primary">NEXT</a>
                    <input type="hidden"
                           name="ssl_zen_system_requirements_status"
                           value="5">
				<?php else: ?>
                    <span class="d-block mb-4 error mini-message">Our plugin won’t work until you fix the issues above.</span>
                    <a href="#" id="reCheck"
                       class="d-inline-block primary">RE-CHECK</a>
                    <input type="hidden"
                           name="ssl_zen_system_requirements_status"
                           value="0">
				<?php endif; ?>
            </div>
        </div>
    </div>
</form>
