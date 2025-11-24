<?php
/**
 * Template
 *
 * @var string $heading
 * @var string $message
 */
?>
<div class="ssl-zen-steps-container p-0 mb-4">
	<div class="row ssl-zen-error-state-container">
		<div class="col-md-4">
			<div class="mt-5 mb-5 banner"></div>
		</div>
		<div class="col-md-8">
			<div class="pt-5 pb-5 pr-5 pl-0">
				<h4><?php echo esc_html($heading); ?></h4>
				<p><?php echo esc_html($message); ?></p>
			</div>
		</div>
	</div>
</div>