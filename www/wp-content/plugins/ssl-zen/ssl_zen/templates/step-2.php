<?php
/**
 * Template
 *
 * @var string $selectedVariant
 * @var boolean $cPanel
 */
?>
<form name="frmstep2" id="frmstep2" action="" method="post">
	<?php
	wp_nonce_field( 'ssl_zen_verify', 'ssl_zen_verify_nonce' );
	if ( empty( $selectedVariant ) ) :
		$showNextButton = true;
		?>
        <input type="hidden" id="ssl_zen_domain_verification"
               name="ssl_zen_domain_verification"
               value="http">
        <input type="hidden" id="ssl_zen_sub_step"
               name="ssl_zen_sub_step" value="1">
        <div class="ssl-zen-steps-container mb-4">
            <div class="row">
                <div class="col-md-12 mb-5">
                    <p class="verification-question">
						<?php _e( 'Which domain verification process would you like to use?', 'ssl-zen' ); ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="ssl-zen-domain-verification-variant-container http <?php echo esc_attr( $selectedVariant == 'http' || $selectedVariant == '' ? 'selected' : '' ); ?> p-4">
                        <div class="d-flex justify-content-between mb-5">
                            <div>
                                <span class="font-weight-bold http">HTTP</span>
                            </div>
                            <div>
                                <span class="minute">10 mins</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h5><?php _e( 'Step 1', 'ssl-zen' ); ?></h5>
                            <p><?php _e( 'Create .well-known/acme-challenge folder ', 'ssl-zen' ); ?></p>
                        </div>
                        <div>
                            <h5><?php _e( 'Step 2 ', 'ssl-zen' ); ?></h5>
                            <p><?php _e( 'Upload verification file(s) ', 'ssl-zen' ); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ssl-zen-domain-verification-variant-container <?php echo esc_attr( $selectedVariant == 'dns' ? 'selected' : '' ); ?> p-4">
                        <div class="d-flex justify-content-between mb-5">
                            <div>
                                <span class="font-weight-bold dns">DNS</span>
                            </div>
                            <div>
                                <span class="minute">7 mins</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h5><?php _e( 'Step 1', 'ssl-zen' ); ?></h5>
                            <p><?php _e( 'Identify your domain host', 'ssl-zen' ); ?></p>
                        </div>
                        <div>
                            <h5><?php _e( 'Step 2', 'ssl-zen' ); ?></h5>
                            <p><?php _e( 'Add a domain TXT record', 'ssl-zen' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<?php else:
		// If selected variant was HTTP , then we need to fetch pending authorizations for further download
		$arrPendingHttp = ssl_zen_certificate::getPendingAuthorization( \LEClient\LEOrder::CHALLENGE_TYPE_HTTP, false );
		$arrPendingDns = ssl_zen_certificate::getPendingAuthorization( \LEClient\LEOrder::CHALLENGE_TYPE_DNS, false );
		// Get verification status
		$showNextButton = get_option( 'ssl_zen_domain_verified', '' );
		// Get next DNS check time left and calc diff if it is not empty
		$dnsCheckActivation = get_option( 'ssl_zen_dns_check_activation', '' );
		$diff               = ! empty( $dnsCheckActivation ) ? $dnsCheckActivation - time() : null;
		// Logic for scan-dns button class and also timer class
		if ( empty( $arrPendingDns ) ) {
			$scanDnsButtonClass = 'disabled';
			$timerButtonClass   = 'd-none';
		} else {
			if ( empty( $diff ) || $diff < 0 ) {
				$scanDnsButtonClass = '';
				$timerButtonClass   = 'd-none';
			} else {
				$scanDnsButtonClass = 'disabled';
				$timerButtonClass   = '';
			}
		}
		//TODO show success message in proper variant container or in general container(is stage step2 and is verified)
		?>
        <input type="hidden" id="ssl_zen_sub_step"
               name="ssl_zen_sub_step" value="2">
        <ul class="ssl-zen-domain-verification-variant-tabs d-flex m-0">
            <li class="http <?php echo esc_attr( $selectedVariant == 'http' ? 'active' : '' ); ?>">HTTP
            </li>
            <li class="dns <?php echo esc_attr( $selectedVariant == 'dns' ? 'active' : '' ); ?>">DNS
            </li>
        </ul>
        <div class="ssl-zen-steps-container <?php echo esc_attr( $selectedVariant == 'dns' ? 'p-0' : '' ); ?> mb-4 custom-round">
            <div class="row">
                <div class="col-md-12 ssl-zen-domain-verification-variant-tab-container http <?php echo esc_attr( $selectedVariant == 'http' ? '' : 'd-none' ); ?>">
                    <div class="row ">
                        <div class="col-md-12">
                            <div class="border-bottom pb-5">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="mb-4">
											<?php _e( 'HTTP Verification', 'ssl-zen' ); ?>

											<?php if ( $cPanel ) : ?>
                                                <a href="https://www.youtube.com/watch?v=9PT7r8TSHks"
                                                   class="tutorial ml-3"
                                                   target="_blank"><?php _e( 'Video Tutorial', 'ssl-zen' ); ?></a>

											<?php else: ?>
                                                <a href="https://www.youtube.com/watch?v=XApeU26YcV8"
                                                   class="tutorial ml-3"
                                                   target="_blank"><?php _e( 'Video Tutorial', 'ssl-zen' ); ?></a>
											<?php endif; ?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <h5><?php _e( 'STEP 1', 'ssl-zen' ); ?></h5>
                                        <p><?php _e( 'Create a folder to upload verification files', 'ssl-zen' ); ?></p>
                                    </div>
                                    <div class="col-md-8">
                                        <span><?php _e( 'Navigate to the Folder where you have hosted WordPress.', 'ssl-zen' ); ?></span><br>
                                        <span><?php _e( 'Create a folder', 'ssl-zen' ); ?></span>
                                        <span class="folder">.well-known</span>
                                        <span><?php _e( 'and inside it another folder', 'ssl-zen' ); ?></span><br>
                                        <span class="folder">acme-challenge</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mt-5">
                            <h5><?php _e( 'STEP 2', 'ssl-zen' ); ?></h5>
                            <p><?php _e( 'Upload the verification file(s)', 'ssl-zen' ); ?></p>
                        </div>
                        <div class="col-md-8 mt-5">
                            <span><?php _e( 'Download the file(s) below on your local computer and', 'ssl-zen' ); ?></span>
                            <br>
                            <span><?php _e( 'upload them in', 'ssl-zen' ); ?></span>
                            <span class="folder">.well-known/acme-challenge</span>
                            <span>folder</span><br>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="col-md-8 mt-3">
                            <div class="d-flex justify-content-start align-items-center">
								<?php if ( ! empty( $arrPendingHttp ) ) :
									foreach (
										$arrPendingHttp as $index => $item
									) {
										?>
                                        <a href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=step2&download=' . $index ); ?>"
                                           class="download-file primary mr-3"><?php echo esc_html( __( 'File', 'ssl-zen' ) . ' ' . ( $index + 1 ) ); ?>
                                        </a>
										<?php
									}
								endif; ?>
                                <a class="scan-http primary mr-3 <?php echo esc_attr( empty( $arrPendingHttp ) ? 'disabled' : '' ); ?>"><?php _e( 'Verify', 'ssl-zen' ); ?></a>
                                <div class="message-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 ssl-zen-domain-verification-variant-tab-container dns <?php echo esc_attr( $selectedVariant == 'dns' ? '' : 'd-none' ); ?>">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ssl-zen-domain-verification-variant-tab-container-left">
                                <h4 class="mb-4">
									<?php _e( 'DNS Verification', 'ssl-zen' ); ?>
                                    <a href="https://youtu.be/ubT5EpBr6-U"
                                       class="tutorial ml-3"
                                       target="_blank"><?php _e( 'Video Tutorial', 'ssl-zen' ); ?></a>
                                </h4>
                                <p><?php _e(
										'To verify domain ownership, you will need to create a DNS record of the
                                                TXT type as shown below.', 'ssl-zen'
									); ?>
                                </p>
								<?php if ( ! empty( $arrPendingDns ) ) : ?>
                                    <div class="record-table mt-4">
                                        <div class="head"><?php _e( 'Domain TXT Record', 'ssl-zen' ); ?></div>
                                        <div class="head"><?php _e( 'Value', 'ssl-zen' ); ?></div>
										<?php
										foreach ( $arrPendingDns as $key => $item ) :
											$rowClass = ! $key ? 'first' : 'second';
											$value = ssl_zen_helper::checkWWWSubDomainExistence( $item['identifier'] ) ? '_acme-challenge.www' : '_acme-challenge';
											?>
                                            <div class="record <?php echo esc_attr( $rowClass ); ?> d-flex align-items-center justify-content-between">
                                                <input class="acme"
                                                       type="text"
                                                       value="<?php echo esc_attr( $value ); ?>">
                                                <i class="copy"
                                                   title="<?php _e( 'Copy', 'ssl-zen' ) ?>"></i>
                                            </div>
                                            <div class="record <?php echo esc_attr( $rowClass ); ?> d-flex align-items-center justify-content-between">
                                                <input class="txt"
                                                       type="text"
                                                       value="<?php echo esc_attr( $item['DNSDigest'] ); ?>">
                                                <i class="copy"
                                                   title="<?php _e( 'Copy', 'ssl-zen' ) ?>"></i>
                                            </div>
										<?php
										endforeach;
										?>
                                    </div>
								<?php endif; ?>
                                <div class="align-items-center d-flex mt-4">
                                    <a class="scan-dns primary mr-3 <?php echo esc_attr( $scanDnsButtonClass ); ?>">Scan
                                        DNS Record</a>
									<?php if ( ! is_null( $diff ) && $diff > 0 ) :
										?>
                                        <script>
                                            var sslDnsCheckTimeLeft = <?php echo esc_attr( $diff ); ?>;
                                        </script>
									<?php endif; ?>
                                    <span class="time-wait <?php echo esc_attr( $timerButtonClass ); ?>">
                                                    <?php echo sprintf(
                                                    /* translators: %s: Milliseconds div */
	                                                    __( 'Wait for %s to try again.', 'ssl-zen' ),
	                                                    '<span class="ms"></span>'
                                                    ) ?>
                                                </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="description pb-5 pt-5 pl-4 pr-4">
                                <h4><?php _e( 'How to add a TXT record ?', 'ssl-zen' ) ?></h4>
                                <ul>
                                    <li><?php _e( 'Sign in to your domain host.', 'ssl-zen' ) ?></li>
                                    <li><?php _e( 'Go to your domain’s DNS records.', 'ssl-zen' ) ?>
										<?php _e( 'The page might be called something like', 'ssl-zen' ) ?>
                                        DNS Management, Name Server
                                        Management, Control Panel,
                                        or Advanced
                                        Settings. <?php _e( 'Select the option to add a new record.', 'ssl-zen' ) ?>
                                    </li>
                                    <li><?php _e( 'For the record type, select TXT', 'ssl-zen' ) ?></li>
                                    <li><?php _e( 'In the Name/Host/Alias field, enter ', 'ssl-zen' ) ?> [
                                        _acme-challenge ]
                                    </li>
                                    <li><?php _e( 'In the TTL field, enter 300 or lower', 'ssl-zen' ) ?></li>
                                    <li><?php _e( 'In the Value/Answer/Destination field, paste the verification record and Save the record.', 'ssl-zen' ) ?></li>
                                    <li><?php _e( 'Come back here and click on Scan DNS Record button.', 'ssl-zen' ) ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<?php endif;
	$nextButtonClass = empty( $showNextButton ) ? 'disabled' : '';
	?>
    <div class="text-right mb-4">
        <a class="primary next <?php echo esc_attr( $nextButtonClass ); ?>"
           href="#"><?php _e( 'Next', 'ssl-zen' ); ?></a>
    </div>
</form>
