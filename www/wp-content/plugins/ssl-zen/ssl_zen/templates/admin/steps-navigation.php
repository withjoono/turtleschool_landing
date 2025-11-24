<?php
/**
 * Template
 *
 * @var string $step
 * @var boolean $isStep
 */
?>
<section class="controls clearfix">
    <ul class="progress-list list-unstyled">
		<?php $passed = $isStep && $step > 'step1'; ?>
        <li class="<?php echo esc_attr( $step == 'step1' ? 'active' : '' ); ?> mr-2">
            <a class="<?php echo esc_attr( $passed ? 'passed' : '' ); ?> mr-2"
               href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=step1' ); ?>">
				<?php echo esc_html( $passed ? '' : 1 ); ?>
            </a>
            <span class="mr-2"><?php _e( 'Website Details', 'ssl-zen' ); ?></span>
            <span></span>
        </li>
		<?php $passed = $isStep && $step > 'step2'; ?>
        <li class="<?php echo esc_attr( $step == 'step2' ? 'active' : '' ); ?> mr-2">
            <a class="<?php echo esc_attr( $passed ? 'passed' : '' ); ?> mr-2"
               href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=step2' ); ?>">
				<?php echo esc_html( $passed ? '' : 2 ); ?>
            </a>
            <span class="mr-2"><?php _e( 'Domain Verification', 'ssl-zen' ); ?></span>
            <span></span>
        </li>
		<?php $passed = $isStep && $step > 'step3'; ?>
        <li class="<?php echo esc_attr( $step == 'step3' ? 'active' : '' ); ?> mr-2">
            <a class="<?php echo esc_attr( $passed ? 'passed' : '' ); ?> mr-2"
               href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=step3' ); ?>">
				<?php echo esc_html( $passed ? '' : 3 ); ?>
            </a>
            <span class="mr-2"><?php _e( 'Install Certificate', 'ssl-zen' ); ?></span>
            <span></span>
        </li>
        <li class="last-child <?php echo esc_attr( $step == 'step4' ? 'active' : '' ) ?> mr-2">
            <a class="mr-2" href="<?php echo admin_url( 'admin.php?page=ssl_zen&tab=step4' ); ?>">4</a>
            <span><?php _e( 'Activate SSL', 'ssl-zen' ); ?></span>
        </li>
    </ul>
</section>
