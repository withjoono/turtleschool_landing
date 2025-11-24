<?php
/**
 * Template
 */
?>
<form name="frmReview" id="frmReview" action="" method="post">
	<?php wp_nonce_field( 'ssl_zen_review', 'ssl_zen_review_nonce' ); ?>
    <div class="ssl-zen-steps-container p-0 mb-4 border-0">
        <div class="ssl-arrow"></div>
        <div class="row ssl-zen-review-container">
            <div class="col-md-10">
                <div class="description pl-5 pr-0">
                    <div class="ssl mb-4">
                        <div class="lock"></div>
                        <div class="line"></div>
                    </div>
                    <h4><?php _e( 'SSL Certificate Successfully Installed!', 'ssl-zen' ); ?></h4>
                    <p class="saved-quote">
						<?php _e( 'Wowzer! We just saved you $60/year in SSL Certificate fees.', 'ssl-zen' ); ?>
                    </p>
                    <div class="propose d-lg-flex align-items-center">
						<?php _e( 'Could you please do us a BIG favour and give SSL Zen a', 'ssl-zen' ); ?>
                        <i class="star ml-2 mr-2"></i>
                        <i class="star mr-2"></i>
                        <i class="star mr-2"></i>
                        <i class="star mr-2"></i>
                        <i class="star mr-2"></i>
						<?php _e( 'on WordPress.org?', 'ssl-zen' ); ?>
                    </div>
                    <a href="https://wordpress.org/support/plugin/ssl-zen/reviews/#new-post"
                       target="_blank"
                       class="review primary mt-4 mb-2"><?php _e( 'LEAVE A REVIEW', 'ssl-zen' ); ?></a>
                    <span class="review-timing"><?php _e( 'It will only take few moments', 'ssl-zen' ); ?></span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="d-flex align-items-center remind" style="display: none!important;">
                    <a href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=settings' ); ?>">
						<?php _e( 'REMIND ME LATER', 'ssl-zen' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
