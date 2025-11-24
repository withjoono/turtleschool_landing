<?php

if ( !function_exists( 'ssl_zen_debug_container' ) ) {
    /**
     * Shows the container that shows the debug related markup
     * v4.0.2
     */
    function ssl_zen_debug_container(  $tabsToShow, $activeTab  ) {
        if ( sz_fs()->is_plan( 'cdn', true ) && in_array( 'debug', $tabsToShow, true ) ) {
            // Check for the domain's IP address,
            // if they have accidentally changed the IP to stackpath for a reset
            $detectedStackPathIp = null;
            $apiResponse = ssl_zen_auth::call( 'get_ip' );
            if ( in_array( $apiResponse['ip'], ssl_zen_helper::$STACKPATH_IP ) ) {
                $detectedStackPathIp = $apiResponse['ip'];
            }
            ?>
            <div class="row ssl-zen-settings-container debug-container">
                <div class="col-md-9">
                    <ul class="mb-4">
                        <li class="d-flex mb-4 line">
                            <div>
                                <input class="toggle-event"
                                       type="checkbox"
                                       id="enable_debug"
                                       name="enable_debug"
                                    <?php 
            echo ( get_option( 'ssl_zen_show_debug_url', '' ) == '1' ? 'checked="checked"' : '' );
            ?> >
                            </div>
                            <div>
                                <label for="enable_debug"
                                       class="d-block title"><?php 
            _e( 'Show Debug URL', 'ssl-zen' );
            ?></label>
                                <span><?php 
            _e( 'Generates the debug log for sharing with the support team.', 'ssl-zen' );
            ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="message-container-2">
                        <?php 
            $url = get_option( 'ssl_zen_debug_url' );
            if ( $url ) {
                echo sprintf(
                    '<div class="message success">%s <i class="copy-clipboard" title="%s" data-clipboard-text="%s"></i></div><div class="message-container"></div>',
                    esc_url( $url ),
                    __( 'Copy', 'ssl-zen' ),
                    esc_url( $url )
                );
            }
            ?>
                    </div>

                    <a
                        href="#"
                        class="d-inline-block primary mb-2 stackpath-reset sslzen-form-button"
                        data-hidden="#stackpath_reset_plugin"
                        data-hidden-value="<?php 
            echo esc_attr( ( !$detectedStackPathIp ? '2' : '1' ) );
            ?>"
                    >
                        <?php 
            _e( 'Reset Plugin', 'ssl-zen' );
            ?>
                    </a>
                    <input type="hidden" name="stackpath_reset_plugin" id="stackpath_reset_plugin">
                    <span class="d-block mini-message"><?php 
            _e( 'This will reset the plugin and allow you to start from the beginning.', 'ssl-zen' );
            ?></span>
                    <?php 
            if ( $detectedStackPathIp ) {
                ?>
                        <div class="message error">
                            <?php 
                echo sprintf( 
                    /* translators: 1: StackPath IP 2: Host IP */
                    __( 'Your website DNS record for type A is currently pointing to StackPath\'s IP - %1$s. Please change your website A record to %2$s and CNAME record for "www" to your domain before we can reset the plugin' ),
                    $detectedStackPathIp,
                    get_option( 'ssl_zen_stackpath_host_ip' )
                 );
                ?>
                        </div>
                    <?php 
            }
            ?>
                </div>
            </div>
            <?php 
        } else {
            $extraClass = ( $activeTab === 'debug' ? '' : 'd-none' );
            // Get debug file if it exists
            $debugLog = ( file_exists( SSL_ZEN_DIR . 'log/debug.log' ) ? file_get_contents( SSL_ZEN_DIR . 'log/debug.log' ) : '' );
            ?>
            <div class="row ssl-zen-settings-container debug-container <?php 
            echo esc_attr( $extraClass );
            ?>">
                <div class="col-md-9">
                    <ul class="mb-4">
                        <li class="d-flex mb-4 line">
                            <div>
                                <input
                                    class="toggle-event"
                                    type="checkbox"
                                    id="enable_debug"
                                    name="enable_debug"
                                    <?php 
            echo ( get_option( 'ssl_zen_enable_debug', '' ) == '1' ? 'checked="checked"' : '' );
            ?>
                                >
                            </div>
                            <div>
                                <label
                                    for="enable_debug"
                                    class="d-block title"><?php 
            _e( 'Enable Debugging', 'ssl-zen' );
            ?></label>
                                <span><?php 
            _e( 'Enables LOG_DEBUG for full debugging. Only enable when asked by the support team.', 'ssl-zen' );
            ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="table">
                        <div class="head"><?php 
            _e( 'Debug Log', 'ssl-zen' );
            ?></div>

                        <div class="body p-0">
                            <textarea class="border-0 w-100 p-4"><?php 
            echo esc_html( $debugLog );
            ?></textarea>
                        </div>

                        <a
                            href="<?php 
            echo admin_url( 'admin.php?page=ssl_zen&tab=settings&download=debug_log' );
            ?>"
                            class="d-inline-block primary mb-2 download-debug"><?php 
            _e( 'Download Debug Log', 'ssl-zen' );
            ?></a>
                        <span class="d-block mini-message"><?php 
            _e( 'When asked, please download and share this file with SSL Zen support team.', 'ssl-zen' );
            ?></span>
                    </div>
                </div>
            </div>
            <?php 
        }
    }

}
if ( !function_exists( 'ssl_zen_settings' ) ) {
    /**
     * Method to render SSL Zen settings
     */
    function ssl_zen_settings() {
        global $wp_version;
        // Define variables
        $currentTimestamp = strtotime( date_i18n( 'Y-m-d' ) );
        $expiryDate = get_option( 'ssl_zen_certificate_90_days', '' );
        $primaryDomain = get_option( 'ssl_zen_base_domain', '' );
        $currentSettingTab = get_option( 'ssl_zen_settings_stage', '' );
        $tabsToShow = array('status', 'debug');
        if ( ssl_zen_helper::isTabAvailableAtThisStage( $currentSettingTab, 'settings.advanced', ssl_zen_admin::$allowedTabs ) ) {
            $tabsToShow[] = 'advanced';
        }
        // Get server status fields
        $serverStatusFields = ssl_zen_helper::getServerStatusFields();
        $wordpressStatusFields = ssl_zen_helper::getWordPressStatusFields();
        $issuer = "Let's Encrypt Authority X3";
        $miniMessage = $renewButtonClass = '';
        $deactivateMsg = __( 'You will be unable to renew your SSL certificate if you uninstall this plugin.', 'ssl-zen' );
        $activeTab = 'advanced';
        if ( $currentSettingTab !== 'settings' ) {
            $activeTab = 'debug';
        }
        if ( !empty( $expiryDate ) ) {
            // Calc days left
            $expiryTime = strtotime( $expiryDate );
            $timeLeft = $expiryTime - $currentTimestamp;
            $days = floor( $timeLeft / (60 * 60 * 24) );
            $renewalDate = get_option( 'ssl_zen_certificate_60_days', '' );
            $allowRenew = $renewalDate <= date_i18n( 'Y-m-d' );
            // Days circle color category
            if ( $days >= 0 && $days <= 30 ) {
                $circleColor = "#FA541C";
            } elseif ( $days > 30 && $days <= 60 ) {
                $circleColor = "#e9ec00";
            } else {
                $circleColor = "#73D13D";
            }
        }
        require SSL_ZEN_TEMPLATE_DIR . 'settings.php';
    }

}