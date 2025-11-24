<?php
/**
 * Template
 *
 * @var string $url
 * @var boolean $cPanel
 * @var string $downloadLink
 */
?>
<form name="frmstep3" id="frmstep3" action="" method="post">
	<?php wp_nonce_field( 'ssl_zen_install_certificate', 'ssl_zen_install_certificate_nonce' ); ?>
    <div class="ssl-zen-steps-container p-0 mb-4">
        <div class="row ssl-zen-install-certificate-container">
            <div class="col-lg-7 steps">
                <div class="pt-5 pb-5 pl-5 pr-0">
					<?php if ( $cPanel ) : ?>
                        <h4 class="mb-4">
							<?php _e( 'Install SSL Certificate', 'ssl-zen' ); ?>
                            <a href="https://www.youtube.com/watch?v=UOPBUcym144"
                               class="tutorial ml-3"
                               target="_blank"><?php _e( 'Video Tutorial', 'ssl-zen' ); ?></a>
                        </h4>
                        <ul>
                            <li>
                                <a href="<?php echo esc_url( site_url( 'cpanel' ) ); ?>"
                                   target="_blank"><?php _e( 'Click here', 'ssl-zen' ); ?></a>
                                <span><?php _e( 'to login into your cPanel account.', 'ssl-zen' ); ?></span>
                            </li>
                            <li>
                                <span><?php _e( 'Locate and click on', 'ssl-zen' ); ?></span>
                                <span class="ssl-tls important"><?php _e( 'SSL/TLS', 'ssl-zen' ); ?></span>
                                <span><?php _e( 'icon in Security panel.', 'ssl-zen' ); ?></span>
                            </li>
                            <li>
                                <span><?php _e( 'Click on', 'ssl-zen' ); ?> </span>
                                <span class="important"><?php _e( 'Manage SSL sites', 'ssl-zen' ); ?> </span>
                                <span><?php _e( 'under the Install and Manage SSL for your site.', 'ssl-zen' ); ?></span>
                            </li>
                            <li>
                                <span><?php _e( 'Copy the contents of', 'ssl-zen' ); ?> </span>
                                <span class="important"><?php _e( 'Certificate, Private Key & CA Bundle', 'ssl-zen' ); ?></span>
                                <span><?php _e( 'file on the right and paste them in the relevant section in cPanel.', 'ssl-zen' ); ?></span>
                            </li>
                        </ul>
					<?php else: ?>
                        <h4 class="mb-3">
							<?php _e( 'Install SSL Certificate', 'ssl-zen' ); ?>
                        </h4>
                        <p class="mb-3">
							<?php _e( 'Depending on which server type you are looking to install your SSL certificate on, we have prepared a number of instructional guides.', 'ssl-zen' ); ?>
							<?php _e( 'Please choose your server type below to get installation instructions:', 'ssl-zen' ); ?>
                        </p>
                        <ul class="ssl-zen-non-cpanel-external-links">
                            <li>
                                <a href="https://docs.sslzen.com/article/9-install-ssl-certificate-on-apache"
                                   target="_blank">
									<?php _e( 'Install SSL Certificate on Apache', 'ssl-zen' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://docs.sslzen.com/article/14-installing-ssl-certificate-on-amazon-web-services-aws"
                                   target="_blank">
									<?php _e( 'Install SSL Certificate on AWS', 'ssl-zen' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://docs.sslzen.com/article/13-installing-ssl-certificate-on-google-app-engine"
                                   target="_blank">
									<?php _e( 'Install SSL Certificate on Google App Engine', 'ssl-zen' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://docs.sslzen.com/article/12-installing-ssl-certificate-on-nginx"
                                   target="_blank">
									<?php _e( 'Install SSL Certificate on NGINX', 'ssl-zen' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://docs.sslzen.com/article/11-installing-ssl-certificate-on-plesk-12"
                                   target="_blank">
									<?php _e( 'Install SSL Certificate on Plesk', 'ssl-zen' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://docs.sslzen.com/article/10-install-ssl-certificate-on-ubuntu"
                                   target="_blank">
									<?php _e( 'Install SSL Certificate on Ubuntu', 'ssl-zen' ); ?>
                                </a>
                            </li>
                        </ul>
					<?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 cpanel">
                <div>
                    <div class="head"></div>
                    <div></div>
                    <div class="body">
                        <ul>
                            <li>
                                <h6>Certificate : (CRT)</h6>
                                <div>
                                    <div class="filename">certificate.crt</div>
                                    <div>
                                        <i class="copy"
                                           title="<?php _e( 'Copy', 'ssl-zen' ); ?>"
                                           data-content="certificate.crt"></i>
                                        <a title="<?php _e( 'Download', 'ssl-zen' ); ?>"
                                           href="<?php echo esc_url( $downloadLink . 'certificate' ); ?>"></a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <h6>Private Key (KEY)</h6>
                                <div>
                                    <div class="filename">private.pem</div>
                                    <div>
                                        <i class="copy"
                                           title="<?php _e( 'Copy', 'ssl-zen' ); ?>"
                                           data-content="private.pem"></i>
                                        <a title="<?php _e( 'Download', 'ssl-zen' ); ?>"
                                           href="<?php echo esc_url( $downloadLink . 'private' ); ?>"></a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <h6>Certificate Authority Bundle:
                                    (CABUNDLE)</h6>
                                <div>
                                    <div class="filename">cabundle.crt</div>
                                    <div><i class="copy"
                                            title="<?php _e( 'Copy', 'ssl-zen' ); ?>"
                                            data-content="cabundle.crt"></i>
                                        <a title="<?php _e( 'Download', 'ssl-zen' ); ?>"
                                           href="<?php echo esc_url( $downloadLink . 'cabundle' ); ?>"></a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="ssl-zen-copy-certs-wrapper d-none justify-content-center align-items-center">
            <div class="ssl-zen-copy-certs-container">
                <div class="head d-flex align-items-center">
                    <span class="title"></span>
                    <div class="ml-auto mr-3 message d-none success"><?php _e( 'Copied successfully', 'ssl-zen' ) ?></div>
                    <div class="ml-auto mr-3 message d-none error"><?php _e( 'Failed to copy', 'ssl-zen' ) ?></div>
                    <span class="ml-auto mr-3 primary copy">Copy</span>
                    <span class="close"></span>
                </div>
                <div class="body"><textarea></textarea></div>
            </div>
        </div>
    </div>
    <div class="text-right mb-4">
        <a class="primary next"
           href="#"><?php _e( 'Next', 'ssl-zen' ); ?></a>
    </div>
</form>
