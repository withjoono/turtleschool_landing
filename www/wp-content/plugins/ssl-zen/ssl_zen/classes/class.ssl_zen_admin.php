<?php

/**
 * Helps install a free SSL certificate from LetsEncrypt, fixes mixed content, insecure content by redirecting to https, and forces SSL on all pages.
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * Plugin Name:       Free SSL Certificate & HTTPS Redirector for WordPress - SSL Zen
 * Plugin URI:        https://sslzen.com
 * Description:       Helps install a free SSL certificate from LetsEncrypt, fixes mixed content, insecure content by redirecting to https, and forces SSL on all pages.
 * Version:           4.4.1
 * Author:            SSL
 * Author URI:        http://sslzen.com
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ssl-zen
 * Domain Path:       ssl_zen/languages
 *
 * @author   SSL
 * @category Plugin
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
if ( !class_exists( 'ssl_zen_admin' ) ) {
    /**
     * Class to manage the admin settings of ssl_zen
     */
    class ssl_zen_admin {
        /**
         * Add hooks and filters for admin pages
         *
         * @since  1.0
         * @static
         */
        public static function init() {
            register_deactivation_hook( SSL_ZEN_BASEFILE, __CLASS__ . '::deactivate_plugin' );
            // Manage admin menu
            add_action( 'admin_menu', __CLASS__ . '::admin_menu' );
            // Linked menu for restart setup
            add_action( 'admin_menu', __CLASS__ . '::ssl_zen_admin_menu_linked' );
            add_action( 'admin_init', __CLASS__ . '::admin_init', 12 );
            add_action( 'plugin_action_links_' . SSL_ZEN_BASEFILE, __CLASS__ . '::plugin_action_links' );
            // Domain verification ajax hook
            add_action( 'wp_ajax_ssl_zen_domain_verification', __CLASS__ . '::ssl_zen_domain_verification' );
            // WWW sub domain checker ajax hook
            add_action( 'wp_ajax_ssl_zen_check_for_dns_records', __CLASS__ . '::ssl_zen_check_for_dns_records' );
            // Cert files ajax hook
            add_action( 'wp_ajax_ssl_zen_cert_files', __CLASS__ . '::ssl_zen_cert_files' );
            // Enable log debugging mode
            add_action( 'wp_ajax_ssl_zen_settings_debug', __CLASS__ . '::ssl_zen_settings_debug' );
            self::review_notice();
        }

        public static function detect_nginx_and_show_notice() {
            if ( strpos( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false ) {
                add_action( 'admin_notices', array('ssl_zen_admin', 'nginx_notice') );
            }
        }

        public static function nginx_notice() {
            $nginx_config_guide_url = 'https://docs.sslzen.com/article/12-installing-ssl-certificate-on-nginx';
            $keys_dir_name = ssl_zen_certificate::getKeysDir();
            $notice = sprintf(
                __( 'It seems that you are using NGINX as your web server. To ensure the security of your private encryption keys, please add the following configuration to your NGINX server block and restart NGINX: %s %s %s', 'ssl-zen' ),
                '<br><code>',
                'location ~ ^' . preg_quote( SSL_ZEN_URL . $keys_dir_name, '/' ) . '/ { deny all; return 403; }',
                '</code>'
            );
            $notice .= '<br>' . sprintf( __( 'For more information, please refer to the %sNGINX configuration guide%s.', 'ssl-zen' ), '<a href="' . esc_url( $nginx_config_guide_url ) . '" target="_blank">', '</a>' );
            echo '<div class="notice notice-info is-dismissible"><p>' . $notice . '</p></div>';
        }

        /**
         * Called when a stackpath license is deactivated and a free plan becomes active.
         */
        public static function stackpath_downgrade() {
        }

        /**
         * Single-entry ajax method.
         */
        public static function ajax_stackpath() {
            check_ajax_referer( 'ssl_zen_ajax', 'security' );
            $success = $error = array();
            switch ( sanitize_text_field( $_POST['_action'] ) ) {
                case 'step2':
                    $apiResponse = ssl_zen_auth::call( 'verify_records' );
                    $correct_records = ( $apiResponse ? $apiResponse['correct_records'] : array() );
                    if ( !$apiResponse || intval( $apiResponse['wait'] ) === 1 ) {
                        $error = array(
                            'notice'  => sprintf( '<div class="message warning sslzen-nowrap">%s</div>', $apiResponse['wait_reason'] ),
                            'records' => $correct_records,
                        );
                    } else {
                        $success = array(
                            'notice'  => sprintf( '<div class="message success">%s</div>', __( 'You have successfully pointed the records to Stackpath.', 'ssl-zen' ) ),
                            'records' => $correct_records,
                        );
                        update_option( 'ssl_zen_settings_stage', 'step3' );
                    }
                    break;
                case 'step3':
                    $apiResponse = ssl_zen_auth::call( 'request_ssl' );
                    switch ( $apiResponse['status'] ) {
                        case 'ACTIVE':
                            update_option( 'ssl_zen_settings_stage', 'step4' );
                            update_option( 'ssl_zen_cert_details', $apiResponse['details'] );
                            $success = array(
                                'notice' => sprintf( '<div class="message success">%s</div>', __( 'You have successfully generated a free SSL certificate for your website.', 'ssl-zen' ) ),
                            );
                            break;
                        default:
                            $error = array(
                                'notice' => '',
                            );
                    }
                    break;
            }
            if ( $error ) {
                wp_send_json_error( $error );
            }
            if ( $success ) {
                wp_send_json_success( $success );
            }
        }

        /**
         * All the stackpath related hooks, when a license is active.
         */
        public static function stackpath_hooks() {
        }

        /**
         * Deactivate stackpath.
         */
        public static function remove_stackpath( $check_plan = true ) {
        }

        /**
         * Purge url for a specific post.
         */
        public static function purge_url( $postID, WP_Post $post, $update ) {
        }

        /**
         * Update stackpath-related settings and fire any API calls.
         */
        private static function updateStackpathSettings() {
        }

        /**
         * Get the path for wp-config.php.
         */
        public static function get_wp_config() {
            $wp_config_path = null;
            return $wp_config_path;
        }

        /**
         * Removes the changes made in wp-config.php for stackpath.
         */
        public static function remove_fix_wp_config() {
        }

        /**
         * Determines if wp-config.php contains changes for stackpath.
         */
        public static function wp_config_has_stackpath_changes() {
            return false;
        }

        /**
         * Make changes to wp-config.php for stackpath.
         */
        public static function fix_wp_config() {
        }

        /**
         * to manage the allowed tabs after each step
         *
         * @var   $allowedTabs
         * @since 1.0
         */
        public static $allowedTabs = array(
            ''                          => array(''),
            'cloudflare_detected_state' => array(
                '',
                'cloudflare_detected_state',
                'layout' => [
                    'steps_nav' => false,
                    'footer'    => false,
                ],
                'method' => 'cloudflareDetectedState'
            ),
            'bluehost_detected_state'   => array(
                '',
                'bluehost_detected_state',
                'layout' => [
                    'steps_nav' => false,
                    'footer'    => false,
                ],
                'method' => 'bluehostDetectedState'
            ),
            'error_state'               => array(
                '',
                'error_state',
                'layout' => [
                    'steps_nav' => false,
                    'footer'    => false,
                ],
                'method' => 'errorState'
            ),
            'system_requirements'       => array(
                '',
                'system_requirements',
                'upgrade',
                'support',
                'layout' => [
                    'steps_nav' => false,
                    'footer'    => false,
                ],
                'method' => 'systemRequirements'
            ),
            'pricing'                   => array(
                '',
                'pricing',
                'layout' => [
                    'steps_nav' => false,
                    'footer'    => false,
                ],
                'method' => 'pricing'
            ),
            'step1'                     => array(
                '',
                'step1',
                'settings',
                'system_requirements',
                'pricing',
                'upgrade',
                'support',
                'layout' => [
                    'steps_nav' => true,
                    'footer'    => true,
                ],
                'method' => 'step1'
            ),
            'step2'                     => array(
                '',
                'step2',
                'step1',
                'settings',
                'system_requirements',
                'pricing',
                'upgrade',
                'support',
                'layout' => [
                    'steps_nav' => true,
                    'footer'    => true,
                ],
                'method' => 'step2'
            ),
            'step3'                     => array(
                '',
                'step3',
                'step1',
                'settings',
                'system_requirements',
                'pricing',
                'upgrade',
                'support',
                'layout' => [
                    'steps_nav' => true,
                    'footer'    => true,
                ],
                'method' => 'step3'
            ),
            'step4'                     => array(
                '',
                'step4',
                'step1',
                'settings',
                'system_requirements',
                'pricing',
                'upgrade',
                'support',
                'layout' => [
                    'steps_nav' => true,
                    'footer'    => true,
                ],
                'method' => 'step4'
            ),
            'review'                    => array(
                '',
                'review',
                'step1',
                'system_requirements',
                'pricing',
                'settings',
                'settings.advanced',
                'upgrade',
                'support',
                'layout' => [
                    'steps_nav' => false,
                    'footer'    => false,
                ],
                'method' => 'review'
            ),
            'settings'                  => array(
                '',
                'settings',
                'settings.advanced',
                'system_requirements',
                'pricing',
                'review',
                'upgrade',
                'support',
                'layout' => [
                    'steps_nav' => false,
                    'footer'    => true,
                ],
                'method' => 'settings'
            ),
        );

        /**
         * Ajax Method for domain verification scan
         */
        public static function ssl_zen_domain_verification() {
            // Initialize variables
            $nonce = ( isset( $_REQUEST['nonce'] ) ? sanitize_text_field( $_REQUEST['nonce'] ) : '' );
            $result = [];
            $result['status'] = 0;
            if ( wp_verify_nonce( $nonce, 'ssl_zen_verify' ) ) {
                $variant = ( isset( $_REQUEST['variant'] ) ? sanitize_text_field( $_REQUEST['variant'] ) : '' );
                if ( !empty( $variant ) ) {
                    $leVariantType = ( $variant == 'http' ? \LEClient\LEOrder::CHALLENGE_TYPE_HTTP : \LEClient\LEOrder::CHALLENGE_TYPE_DNS );
                    // Update selected verification variant ( The other one maybe be failed initially )
                    update_option( 'ssl_zen_domain_verification_variant', $variant );
                    // Check all the pending authorizations and update validation status
                    ssl_zen_certificate::updateAuthorizations( $leVariantType, false );
                    // Check if all authorizations are valid
                    $isValid = ssl_zen_certificate::validateAuthorization( false );
                    // If verification succeeded, then store the flag
                    if ( $isValid ) {
                        update_option( 'ssl_zen_domain_verified', '1' );
                        // Remove http verification files, no meter what variant have used before
                        ssl_zen_helper::deleteAll( ABSPATH . '.well-known/acme-challenge', true );
                        $result['message'] = __( 'Successfully verified', 'ssl-zen' );
                    } else {
                        // If not succeeded and variant was DNS then store now+300 sec for next time to allow to check
                        if ( $variant == 'dns' ) {
                            $fiveMinutes = 300;
                            update_option( 'ssl_zen_dns_check_activation', time() + $fiveMinutes );
                            $result['time'] = $fiveMinutes;
                            $result['message'] = __( 'We couldn\'t find your verification token in your domain\'s TXT records.', 'ssl-zen' ) . __( 'Please try again in 5 minutes', 'ssl-zen' ) . ' ' . __( 'or try http variant.', 'ssl-zen' );
                        } else {
                            $result['message'] = __( 'Verification failed, try dns variant.', 'ssl-zen' );
                        }
                    }
                    $result['status'] = $isValid;
                } else {
                    $result['message'] = __( 'Invalid verification variant', 'ssl-zen' );
                }
            } else {
                $result['message'] = __( 'Invalid nonce.Please refresh the page.', 'ssl-zen' );
            }
            print_r( json_encode( $result ) );
            wp_die();
        }

        /**
         * Ajax call handler for www sub domain checker
         */
        public static function ssl_zen_check_for_dns_records() {
            // Initialize variables
            $nonce = ( isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '' );
            $input = ( isset( $_POST['domain'] ) ? sanitize_text_field( $_POST['domain'] ) : '' );
            // Used for displaying the IP address in messages and then checking the IP address in an `if` condition
            // Both late sanitized and escaped as necessary
            $ip = $_SERVER['SERVER_ADDR'];
            $result = [];
            $result['status'] = 0;
            $result['message'] = sprintf( 
                /* translators: 1: Input 2: IP Address*/
                __( 'www.%1$s is not pointed to %2$s. Please create "A" or "CNAME" record for www sub-domain or uncheck the include www option', 'ssl-zen' ),
                $input,
                esc_html( $ip )
             );
            if ( wp_verify_nonce( $nonce, 'ssl_zen_generate_certificate' ) ) {
                if ( !empty( $input ) ) {
                    $input = trim( $input, '/' );
                    if ( !preg_match( '#^http(s)?://#', $input ) ) {
                        $input = 'http://' . $input;
                    }
                    $urlParts = parse_url( $input );
                    $domain = preg_replace( '/^www\\./', '', $urlParts['host'] );
                    $subDomain = 'www.' . $domain;
                    $data = dns_get_record( $subDomain );
                    if ( !empty( $data ) ) {
                        foreach ( $data as $item ) {
                            if ( $item['type'] == 'A' && !empty( $item['ip'] ) && $item['ip'] == sanitize_text_field( $ip ) || $item['type'] == 'CNAME' && !empty( $item['target'] ) && $item['target'] == $domain ) {
                                $result['status'] = 1;
                                unset($result['message']);
                                break;
                            }
                        }
                    }
                } else {
                    $result['message'] = __( 'Domain is empty', 'ssl-zen' );
                }
            } else {
                $result['message'] = __( 'Invalid nonce.Please refresh the page.', 'ssl-zen' );
            }
            print_r( json_encode( $result ) );
            wp_die();
        }

        /**
         * Ajax call handler for showing cert file
         */
        public static function ssl_zen_cert_files() {
            $nonce = ( isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : '' );
            if ( wp_verify_nonce( $nonce, 'ssl_zen_install_certificate' ) ) {
                $fileName = ( isset( $_REQUEST['file_name'] ) ? sanitize_text_field( $_REQUEST['file_name'] ) : '' );
                $keyDir = ssl_zen_certificate::getKeysDir();
                $filePath = $keyDir . $fileName;
                if ( file_exists( $filePath ) ) {
                    $fileContent = file_get_contents( $filePath );
                    $result = [
                        'status' => 1,
                        'file'   => $fileContent,
                    ];
                } else {
                    $result = [
                        'status'  => 0,
                        'message' => __( 'Invalid file.', 'ssl-zen' ),
                    ];
                }
            } else {
                $result = [
                    'status'  => 0,
                    'message' => __( 'Invalid nonce.Please refresh the page.', 'ssl-zen' ),
                ];
            }
            print_r( json_encode( $result ) );
            wp_die();
        }

        /**
         * Ajax call handler for enabling log debug mode
         */
        public static function ssl_zen_settings_debug() {
            $nonce = ( isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : '' );
            if ( wp_verify_nonce( $nonce, 'ssl_zen_settings' ) ) {
                $enableDebug = ( isset( $_GET['enable_debug'] ) ? sanitize_text_field( $_GET['enable_debug'] ) : 0 );
                if ( sz_fs()->is_plan( 'cdn', true ) ) {
                    $success = array(
                        'notice' => '',
                    );
                    if ( $enableDebug ) {
                        $url = ssl_zen_helper::exposeLogAsFile();
                        $success = array(
                            'notice' => sprintf(
                                '<div class="message success">%s <i class="copy-clipboard" title="%s" data-clipboard-text="%s"></i></div><div class="message-container"></div>',
                                $url,
                                __( 'Copy', 'ssl-zen' ),
                                $url
                            ),
                        );
                        update_option( 'ssl_zen_show_debug_url', $enableDebug );
                        update_option( 'ssl_zen_debug_url', $url );
                    } else {
                        ssl_zen_helper::removeLogs();
                        delete_option( 'ssl_zen_show_debug_url' );
                        delete_option( 'ssl_zen_debug_url' );
                    }
                    wp_send_json_success( $success );
                } else {
                    $status = update_option( 'ssl_zen_enable_debug', $enableDebug );
                    $result = [
                        'status' => $status,
                    ];
                }
            } else {
                $result = [
                    'status'  => 0,
                    'message' => __( 'Invalid nonce.Please refresh the page.', 'ssl-zen' ),
                ];
            }
            print_r( json_encode( $result ) );
            wp_die();
        }

        /**
         * Hook to manage the admin menu
         *
         * @since  1.0
         * @static
         */
        public static function admin_menu() {
            add_menu_page(
                __( 'SSL Zen', 'ssl-zen' ),
                __( 'SSL Zen', 'ssl-zen' ),
                'manage_options',
                'ssl_zen',
                __CLASS__ . '::ssl_zen_hook',
                'dashicons-lock',
                101
            );
            if ( sz_fs()->is_plan( 'pro', true ) ) {
                add_submenu_page(
                    'ssl_zen',
                    __( 'Setup', 'ssl-zen' ),
                    __( 'Setup', 'ssl-zen' ),
                    'manage_options',
                    'ssl_zen-restart-setup'
                );
            }
        }

        /**
         * Hook to manage the linked admin menu
         *
         * @since  1.13
         * @static
         */
        public static function ssl_zen_admin_menu_linked() {
            global $submenu;
            if ( sz_fs()->is_plan( 'pro', true ) ) {
                $submenu['ssl_zen'][1][2] = 'admin.php?page=ssl_zen&tab=step1';
            }
        }

        /**
         * Hook to validate input fields on step 1 cpanel username and password
         * Validating cpanel username and password API is available on both free and paid
         *
         * @since  1.2
         * @static
         */
        public static function ssl_zen_cpanel_check_credentials_ajax() {
            wp_die();
        }

        /**
         * Hook to display SSL Zen Settings page
         *
         * @since  1.0
         * @static
         */
        public static function ssl_zen_hook() {
            $tab = ( isset( $_REQUEST['tab'] ) ? trim( sanitize_text_field( $_REQUEST['tab'] ) ) : '' );
            ?>
            <div class="ssl-zen-content-container <?php 
            echo esc_attr( ( $tab == 'review' ? 'review-page' : '' ) );
            ?>">
                <header class="header clearfix">
                    <div class="container">
                        <div class="row align-items-center ">
                            <div class="col-lg-6 text-lg-left text-center logo mb-3 mb-lg-0">
                                <img src="<?php 
            echo esc_url( SSL_ZEN_URL );
            ?>img/logo.svg"
                                     alt="">
                                <span>V<?php 
            echo esc_html( SSL_ZEN_PLUGIN_VERSION );
            ?></span>
                                <span><?php 
            echo esc_html( ( sz_fs()->can_use_premium_code__premium_only() ? 'Premium' : ' Free' ) );
            ?></span>
                            </div>
                            <div class="col-lg-6 text-lg-right text-center external-actions-container">
                                <?php 
            $stage = get_option( 'ssl_zen_settings_stage', '' );
            // show settings button only when the stage is that.
            if ( $stage === 'settings' && ssl_zen_helper::isTabAvailableAtThisStage( $tab, 'settings', self::$allowedTabs ) ) {
                ?>
                                    <a class="settings"
                                       href="<?php 
                echo admin_url( 'admin.php?page=ssl_zen&tab=settings' );
                ?>">
                                        <?php 
                _e( 'Settings', 'ssl-zen' );
                ?>
                                    </a>
                                <?php 
            }
            if ( ssl_zen_helper::isTabAvailableAtThisStage( $tab, 'upgrade', self::$allowedTabs ) && SSLZenCPanel::detect_cpanel() ) {
                ?>
                                    <a class="upgrade"
                                       href="https://checkout.freemius.com/mode/dialog/plugin/4586/plan/7397/licenses/1/">
                                        <?php 
                _e( 'Upgrade', 'ssl-zen' );
                ?>
                                    </a>
                                <?php 
            }
            if ( $stage !== 'settings' ) {
                ?>
                                    <a class="settings"
                                       href="<?php 
                echo admin_url( 'admin.php?page=ssl_zen&tab=settings' );
                ?>">
                                        <?php 
                _e( 'Debug', 'ssl-zen' );
                ?>
                                    </a>
                                <?php 
            }
            if ( ssl_zen_helper::isTabAvailableAtThisStage( $tab, 'support', self::$allowedTabs ) ) {
                ?>
                                    <a class="support"
                                       href="<?php 
                echo admin_url( 'admin.php?page=ssl_zen-contact' );
                ?>">
                                        <?php 
                _e( 'Support', 'ssl-zen' );
                ?>
                                    </a>
                                <?php 
            }
            ?>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="container mt-5">
                    <?php 
            // Check weather to show steps navigation
            if ( ssl_zen_helper::showLayoutPart( $tab, self::$allowedTabs, 'steps_nav' ) ) {
                self::stepsNavigation( $tab );
            }
            // Show message container
            self::showMessage();
            ?>
                    <section class="ssl-zen-container">
                        <?php 
            $tabMethod = ( isset( self::$allowedTabs[$tab]['method'] ) ? self::$allowedTabs[$tab]['method'] : '' );
            if ( method_exists( ssl_zen_admin::class, $tabMethod ) ) {
                self::$tabMethod();
            } else {
                $tabMethod = self::$allowedTabs[get_option( 'ssl_zen_settings_stage', 'system_requirements' )]['method'];
                self::$tabMethod();
            }
            ?>
                    </section>
                </div>
                <?php 
            if ( ssl_zen_helper::showLayoutPart( $tab, self::$allowedTabs, 'footer' ) && !sz_fs()->is_premium() ) {
                $upgradeUrl = add_query_arg( array(
                    'checkout'      => 'true',
                    'plan_id'       => 10884,
                    'plan_name'     => 'cdn',
                    'billing_cycle' => 'annual',
                    'pricing_id'    => 11089,
                    'currency'      => 'usd',
                ), sz_fs()->get_upgrade_url() );
                if ( SSLZenCPanel::detect_cpanel() ) {
                    $upgradeUrl = add_query_arg( array(
                        'checkout'      => 'true',
                        'plan_id'       => 7397,
                        'plan_name'     => 'pro',
                        'billing_cycle' => 'annual',
                        'pricing_id'    => 7115,
                        'currency'      => 'usd',
                    ), sz_fs()->get_upgrade_url() );
                }
                ?>
                    <footer class="ssl-zen-footer container">
                        <a href="<?php 
                echo esc_url( $upgradeUrl );
                ?>">
                            <div class="row align-items-center">
                                <div class="col-lg-3 text-center text-lg-left ssl-zen-pro-quote">
                                    <h4>
                                        <?php 
                _e( 'Never Pay for SSL Again!', 'ssl-zen' );
                ?>
                                    </h4>
                                    <p class="mt-1">
                                        <?php 
                _e( 'Upgrade to our Pro Plan', 'ssl-zen' );
                ?>
                                    </p>
                                </div>
                                <div class="col-lg-7 ssl-zen-pro-features mt-4 mt-lg-0">
                                <span>
                                    <?php 
                _e( 'AUTOMATIC', 'ssl-zen' );
                ?><br>
                                    <?php 
                _e( 'DOMAIN VERIFICATION', 'ssl-zen' );
                ?>
                                </span>
                                    <span>
                                    <?php 
                _e( 'AUTOMATIC SSL INSTALLATION', 'ssl-zen' );
                ?>
                                </span>
                                    <span>
                                    <?php 
                _e( 'AUTOMATIC SSL RENEWAL', 'ssl-zen' );
                ?>
                                </span>
                                </div>
                                <div class="col-lg-2 text-center text-lg-right mt-4 mt-lg-0 align ssl-zen-pro-upgrade">
                                    <button><?php 
                _e( 'UPGRADE', 'ssl-zen' );
                ?></button>
                                </div>
                            </div>
                        </a>
                    </footer>
                <?php 
            }
            ?>
            </div>
            <?php 
        }

        /**
         * Showing the steps navigation
         *
         * @param $step
         *
         * @since 2.0
         */
        public static function stepsNavigation( $step ) {
            $isStep = ssl_zen_helper::stageIsStep( $step );
            ?>
            <section class="controls clearfix">
                <ul class="progress-list list-unstyled">
                    <?php 
            $passed = $isStep && $step > 'step1';
            ?>
                    <li class="<?php 
            echo esc_attr( ( $step == 'step1' ? 'active' : '' ) );
            ?> mr-2">
                        <a class="<?php 
            echo esc_attr( ( $passed ? 'passed' : '' ) );
            ?> mr-2"
                           href="<?php 
            echo admin_url( 'admin.php?page=ssl_zen&tab=step1' );
            ?>">
                            <?php 
            echo esc_html( ( $passed ? '' : 1 ) );
            ?>
                        </a>
                        <span class="mr-2"><?php 
            _e( 'Website Details', 'ssl-zen' );
            ?></span>
                        <span></span>
                    </li>
                    <?php 
            $passed = $isStep && $step > 'step2';
            ?>
                    <li class="<?php 
            echo esc_attr( ( $step == 'step2' ? 'active' : '' ) );
            ?> mr-2">
                        <a class="<?php 
            echo esc_attr( ( $passed ? 'passed' : '' ) );
            ?> mr-2"
                           href="<?php 
            echo admin_url( 'admin.php?page=ssl_zen&tab=step2' );
            ?>">
                            <?php 
            echo esc_html( ( $passed ? '' : 2 ) );
            ?>
                        </a>
                        <span class="mr-2"><?php 
            _e( 'Domain Verification', 'ssl-zen' );
            ?></span>
                        <span></span>
                    </li>
                    <?php 
            $passed = $isStep && $step > 'step3';
            ?>
                    <li class="<?php 
            echo esc_attr( ( $step == 'step3' ? 'active' : '' ) );
            ?> mr-2">
                        <a class="<?php 
            echo esc_attr( ( $passed ? 'passed' : '' ) );
            ?> mr-2"
                           href="<?php 
            echo admin_url( 'admin.php?page=ssl_zen&tab=step3' );
            ?>">
                            <?php 
            echo esc_html( ( $passed ? '' : 3 ) );
            ?>
                        </a>
                        <span class="mr-2"><?php 
            _e( 'Install Certificate', 'ssl-zen' );
            ?></span>
                        <span></span>
                    </li>
                    <li class="last-child <?php 
            echo esc_attr( ( $step == 'step4' ? 'active' : '' ) );
            ?> mr-2">
                        <a class="mr-2"
                           href="<?php 
            echo admin_url( 'admin.php?page=ssl_zen&tab=step4' );
            ?>">4</a>
                        <span><?php 
            _e( 'Activate SSL', 'ssl-zen' );
            ?></span>
                    </li>
                </ul>
            </section>
            <?php 
        }

        /**
         * Show message container
         *
         * @since 2.0
         */
        private static function showMessage() {
            $info = ( !empty( $_REQUEST['info'] ) ? sanitize_text_field( $_REQUEST['info'] ) : null );
            if ( !empty( $info ) ) {
                $messageArr = ssl_zen_messages::getMessage( $info );
                if ( !empty( $messageArr ) ) {
                    ?>
                    <section class="ssl-zen-message-container">
                        <div class="message <?php 
                    echo esc_attr( ( empty( $messageArr['type'] ) ? 'error' : $messageArr['type'] ) );
                    ?> mb-5 ml-auto mr-auto">
                            <?php 
                    echo $messageArr['msg'];
                    ?>
                        </div>
                    </section>
                    <?php 
                }
            }
        }

        /**
         * Function to display step 1 for SSL Zen.
         *
         * @since  1.0
         * @static
         */
        private static function step1() {
            $apiResponse = null;
            $image = 'lock';
            $heading = __( 'Secure your website with a free SSL certificate', 'ssl-zen' );
            $tagline = __( 'The SSL certificate for your website will be generated by LetsEncrypt.org, an open certificate authority (CA), run for the public\'s benefit.', 'ssl-zen' );
            require SSL_ZEN_TEMPLATE_DIR . 'step-1.php';
        }

        /**
         * Function to display step 2 (stackpath variation) for SSL Zen.
         *
         * @since  1.0
         * @static
         */
        private static function step2_stackpath() {
            if ( !sz_fs()->is_plan( 'cdn', true ) ) {
                return;
            }
            $notice = array();
            $diff = 10;
            $scanDnsButtonClass = '';
            $timerButtonClass = 'd-none';
            $nextButtonClass = 'disabled';
            $timerClass = 'timer-automatic timer-automatic-fire-ajax timer-automatic-enable-no';
            $image = 'done-circle';
            $ajaxData = array();
            $last_api_called = get_option( 'ssl_zen_last_auth_api_call' );
            $apiResponse = null;
            $domainconnectUrl = null;
            $nonce_field = 'ssl_zen_stackpath_verify_records';
            // if nonce is part of the payload, the user has come from step2.
            if ( $last_api_called === 'verify_records' || isset( $_POST[$nonce_field] ) && wp_verify_nonce( sanitize_text_field( $_POST[$nonce_field] ), 'ssl_zen_verify' ) ) {
                $apiResponse = ssl_zen_auth::call( 'verify_records' );
                if ( !$apiResponse || intval( $apiResponse['wait'] ) === 1 ) {
                    // stay on the same page and show a notice.
                    $notice['warning'] = ( empty( $apiResponse['wait_reason'] ) ? __( 'We are verifying your DNS records. Please wait…', 'ssl-zen' ) : $apiResponse['wait_reason'] );
                    $scanDnsButtonClass = 'd-none';
                    $timerButtonClass = '';
                    $image = 'warning-circle';
                    $ajaxData = array(
                        'security' => wp_create_nonce( 'ssl_zen_ajax' ),
                        'action'   => 'ssl_zen_stackpath',
                        '_action'  => 'step2',
                    );
                } else {
                    $notice['success'] = __( 'You have successfully added your website to Stackpath.', 'ssl-zen' );
                    $scanDnsButtonClass = 'd-none';
                    $nextButtonClass = '';
                    update_option( 'ssl_zen_settings_stage', 'step3' );
                }
            } else {
                // user has come from step1.
                $apiResponse = ssl_zen_auth::call( 'add_site' );
                if ( 'settings' === $apiResponse['goto'] ) {
                    // redirect to settings.
                    wp_safe_redirect( admin_url( 'admin.php?page=ssl_zen&tab=settings' ) );
                }
                if ( $apiResponse && array_key_exists( 'wait', $apiResponse ) && ssl_zen_domainconnect::is_enabled() ) {
                    $domainconnectUrl = ssl_zen_domainconnect::get_url( ssl_zen_helper::getStackpathEdgeName( $apiResponse['records'] ) );
                    // Fix for cases when domainconnectUrl is empty
                    if ( $domainconnectUrl ) {
                        $scanDnsButtonClass = 'd-none';
                        $timerButtonClass = 'd-none';
                    }
                } else {
                    if ( array_key_exists( 'www', $apiResponse['records'] ) && stripos( ssl_zen_helper::get_host(), 'www.' ) === false ) {
                        unset($apiResponse['records']['www']);
                    }
                }
            }
            ?>

            <div class="ssl-zen-steps-container mb-4 custom-round p-0">
                <div class="col-md-13 ssl-zen-domain-verification-variant-tab-container ">
                    <div class="row">
                        <div class="col-md-7 p-5">
                            <h4 class="mb-3">
                                <?php 
            _e( 'Domain Verification', 'ssl-zen' );
            ?>
                            </h4>
                            <p>
                                <?php 
            // Change message if a "Update DNS Record" is displayed
            _e( ( $domainconnectUrl ? 'Click on Update DNS Records to automatically update your DNS records on GoDaddy to start pointing to the StackPath Network.' : 'Your site is almost ready! Update your DNS to start pointing to the Stackpath Network.' ), 'ssl-zen' );
            ?>
                            </p>

                            <form name="frmstep2" id="frmstep2" class="stackpath" action="" method="post">
                                <?php 
            wp_nonce_field( 'ssl_zen_verify', $nonce_field );
            ?>
                                <table class="table table-bordered">
                                    <tbody>
                                    <tr class="grey">
                                        <th><?php 
            _e( 'Type', 'ssl-zen' );
            ?></th>
                                        <th><?php 
            _e( 'Name', 'ssl-zen' );
            ?></th>
                                        <th><?php 
            _e( 'Value', 'ssl-zen' );
            ?></th>
                                        <th><?php 
            _e( 'TTL', 'ssl-zen' );
            ?></th>
                                    </tr>
                                    <?php 
            foreach ( $apiResponse['records'] as $record ) {
                if ( empty( $record['name'] ) ) {
                    continue;
                }
                $copy_class = '';
                $img_class = 'd-none';
                if ( isset( $apiResponse['correct_records'] ) ) {
                    $copy_class = ( in_array( $record['type'], $apiResponse['correct_records'], true ) ? 'd-none' : '' );
                    $img_class = ( in_array( $record['type'], $apiResponse['correct_records'], true ) ? '' : 'd-none' );
                }
                ?>
                                        <tr class="record_type_<?php 
                echo esc_attr( $record['type'] );
                ?>">
                                            <td><?php 
                echo esc_html( $record['type'] );
                ?></td>
                                            <td><?php 
                echo esc_html( $record['name'] );
                ?></td>
                                            <td>
                                                <?php 
                echo esc_html( $record['value'] );
                ?>
                                                <i class="copy-clipboard <?php 
                echo esc_attr( $copy_class );
                ?>"
                                                   title="<?php 
                _e( 'Copy', 'ssl-zen' );
                ?>"
                                                   data-clipboard-text="<?php 
                echo esc_attr( $record['value'] );
                ?>"></i>
                                                <img class="record-done <?php 
                echo esc_attr( $img_class );
                ?>"
                                                     src="<?php 
                echo esc_url( SSL_ZEN_URL ) . 'img/success.svg';
                ?>" alt="">
                                            </td>
                                            <td><?php 
                echo esc_html( $record['ttl'] );
                ?></td>
                                        </tr>
                                        <?php 
            }
            ?>
                                    </tbody>
                                </table>
                                <div class="align-items-center d-flex mt-3">
                                    <a class="scan-dns-stackpath primary next mr-3 w-50 <?php 
            echo esc_attr( $scanDnsButtonClass );
            ?>"
                                       data-ajax-data="<?php 
            echo esc_attr( json_encode( $ajaxData ) );
            ?>"><?php 
            _e( 'Scan DNS Records', 'ssl-zen' );
            ?>
                                        <img src="<?php 
            echo esc_url( SSL_ZEN_URL );
            ?>img/<?php 
            echo esc_attr( $image );
            ?>.svg"></a>
                                    <span class="time-wait <?php 
            echo esc_attr( $timerClass );
            ?> <?php 
            echo esc_attr( $timerButtonClass );
            ?>"
                                          data-button=".scan-dns-stackpath" data-time="<?php 
            echo esc_attr( $diff );
            ?>"
                                          data-function="step2_mark_records_done"></span>
                                </div>
                                <form>
                                    <?php 
            if ( $domainconnectUrl ) {
                ?>
                                        <div class="align-items-center d-flex mt-3">
                                            <a class="update-dns-stackpath primary next mr-3 w-50"
                                               href="<?php 
                echo esc_url( $domainconnectUrl );
                ?>"><?php 
                _e( 'Update DNS Records', 'ssl-zen' );
                ?>
                                                <img src="<?php 
                echo esc_url( SSL_ZEN_URL );
                ?>img/done-circle.svg"></a>
                                        </div>
                                    <?php 
            }
            ?>
                                    <div class="message-container-2">
                                        <?php 
            if ( $notice ) {
                foreach ( $notice as $type => $message ) {
                    $extra = $class = '';
                    switch ( $type ) {
                        case 'warning':
                            $extra = '<span class="loader__dot">.</span><span class="loader__dot">.</span><span class="loader__dot">.</span>';
                            $class = 'sslzen-nowrap';
                            break;
                    }
                    echo sprintf(
                        '<div class="message %s %s">%s%s</div>',
                        esc_attr( $type ),
                        esc_attr( $class ),
                        esc_html( $message ),
                        $extra
                    );
                }
            }
            ?>
                                    </div>
                        </div>
                        <div class="col-md-5">

                            <div class="description pb-5 pt-5 pl-4 pr-4">
                                <h4 class="mb-4">
                                    <?php 
            _e( 'How to update DNS records?', 'ssl-zen' );
            ?>
                                    <br/>
                                    <a href="https://support.stackpath.com/hc/en-us/articles/360001105186-How-To-Configure-DNS-for-CDN-WAF-with-Your-Provider"
                                       class="tutorial ml-0"
                                       target="_blank"><?php 
            _e( 'Video Tutorial', 'ssl-zen' );
            ?></a>
                                </h4>
                                <ul>
                                    <li><?php 
            _e( 'Log in to your domain provider (e.g. GoDaddy).', 'ssl-zen' );
            ?></li>
                                    <li><?php 
            _e( 'Find your domain and click on it.', 'ssl-zen' );
            ?>
                                    <li><?php 
            _e( 'Find DNS Settings or just Settings.', 'ssl-zen' );
            ?></li>
                                    <li><?php 
            _e( 'Look for the A record and update it with the value displayed in the table on the left side.', 'ssl-zen' );
            ?> </li>
                                    <li><?php 
            _e( 'If you cannot enter TTL as 300, try 600 or the lowest value allowed by your domain provider.', 'ssl-zen' );
            ?></li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right mb-4">
                <a class="primary next <?php 
            echo esc_attr( $nextButtonClass );
            ?>"
                   href="<?php 
            echo admin_url( 'admin.php?page=ssl_zen&tab=step3' );
            ?>"><?php 
            _e( 'Next', 'ssl-zen' );
            ?></a>
            </div>

            <?php 
        }

        private static function step3_stackpath() {
            if ( !sz_fs()->is_plan( 'cdn', true ) ) {
                return;
            }
            $trustedImg = '<img src="' . SSL_ZEN_URL . 'img/success.svg" alt="">';
            ?>

            <form name="frmstep3" class="stackpath" id="frmstep3" action="" method="post">
                <?php 
            wp_nonce_field( 'ssl_zen_verify', 'ssl_zen_cert_not_active_nonce' );
            ?>
                <div class="ssl-zen-steps-container p-0 mb-4">
                    <div class="row ssl-zen-activate-ssl-container">
                        <div class="col-md-8 steps">
                            <div>
                                <h4 class="mb-3">
                                    <?php 
            _e( 'Free Dedicated Certificate', 'ssl-zen' );
            ?>
                                </h4>
                                <h5>
                                    <?php 
            _e( 'Details', 'ssl-zen' );
            ?>
                                </h5>
                                <h6><?php 
            _e( 'Issued by', 'ssl-zen' );
            ?>
                                    : <?php 
            echo esc_html( ( empty( $apiResponse['details']['issuer'] ) ? '-' : $apiResponse['details']['issuer'] ) );
            ?></h6>
                                <h6><?php 
            _e( 'Trusted', 'ssl-zen' );
            ?>
                                    : <?php 
            echo esc_html( ( intval( $apiResponse['details']['trusted'] ) === 1 ? $trustedImg : '' ) );
            ?></h6>
                                <h6><?php 
            _e( 'Expires on', 'ssl-zen' );
            ?>: <?php 
            $expires = '-';
            if ( !empty( $apiResponse['details']['expirationDate'] ) ) {
                $date = DateTime::createFromFormat( 'Y-m-d\\TH:i:s\\Z', $apiResponse['details']['expirationDate'] );
                if ( $date ) {
                    $expires = $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
                }
            }
            echo esc_html( $expires );
            ?>
                                </h6>
                                <h5 class="mt-4">
                                    <?php 
            _e( 'Hosts', 'ssl-zen' );
            ?>
                                </h5>
                                <?php 
            foreach ( $apiResponse['details']['subjectAlternativeNames'] as $host ) {
                ?>
                                    <p class="mb-0"><img src="<?php 
                echo esc_url( SSL_ZEN_URL );
                ?>img/padlock.svg"
                                                         alt="">&nbsp;<?php 
                echo esc_html( $host );
                ?></p>
                                <?php 
            }
            ?>


                                <div class="message-container-2 cstep3"><?php 
            if ( $notice ) {
                foreach ( $notice as $type => $message ) {
                    $extra = '';
                    switch ( $type ) {
                        case 'warning':
                            $extra = '<span class="loader__dot">.</span><span class="loader__dot">.</span><span class="loader__dot">.</span>';
                            break;
                    }
                    echo sprintf(
                        '<div class="message %s">%s%s</div>%s',
                        esc_attr( $type ),
                        esc_html( $message ),
                        $extra,
                        $subtext
                    );
                }
            }
            ?>
                                </div>

                                <div class="align-items-center d-flex mt-4">
                                    <a class="scan-ssl-stackpath primary next mr-3 w-50 <?php 
            echo esc_attr( $scanButtonClass );
            ?>"
                                       data-ajax-data="<?php 
            echo esc_attr( json_encode( $ajaxData ) );
            ?>"><?php 
            _e( 'Force Recheck', 'ssl-zen' );
            echo esc_url( file_get_contents( SSL_ZEN_DIR . 'img/' . $image . '.svg' ) );
            ?></a>
                                    <span class="time-wait <?php 
            echo esc_attr( $timerClass );
            ?>" data-button=".scan-ssl-stackpath"
                                          data-time="<?php 
            echo esc_attr( $diff );
            ?>"></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="text-right mb-4">
                    <a class="primary next <?php 
            echo esc_attr( $nextButtonClass );
            ?>"
                       href="#"><?php 
            _e( 'Next', 'ssl-zen' );
            ?></a>
                </div>

            </form>

            <?php 
        }

        /**
         * Function to display step 2 for SSL Zen.
         *
         * @since  1.0
         * @static
         */
        private static function step2() {
            // Get existing option for selected variant
            $selectedVariant = get_option( 'ssl_zen_domain_verification_variant', '' );
            $cPanel = SSLZenCPanel::detect_cpanel();
            require SSL_ZEN_TEMPLATE_DIR . 'step-2.php';
        }

        /**
         * Function to display step 3 for SSL Zen.
         *
         * @since  1.0
         * @static
         */
        private static function step3() {
            // The url variable needed for checking the environment specifications
            $url = wp_parse_url( home_url() );
            $cPanel = SSLZenCPanel::detect_cpanel();
            $downloadLink = admin_url( 'admin.php?page=ssl_zen&tab=step3&download=' );
            require SSL_ZEN_TEMPLATE_DIR . 'step-3.php';
        }

        /**
         * Function to display step 4 for SSL Zen.
         *
         * @since  1.0
         * @static
         */
        public static function step4() {
            $nonce_field = 'ssl_zen_activate_ssl_nonce';
            if ( sz_fs()->is_plan( 'cdn', true ) ) {
                $nonce_field = 'ssl_zen_activate_stackpath_cert';
            }
            require SSL_ZEN_TEMPLATE_DIR . 'step-4.php';
        }

        public static function review_notice() {
            // Get the right options from db, check if SSL is activated
            $is_activated = get_option( 'ssl_zen_ssl_activated', '' );
            $activated_time = get_option( 'ssl_zen_ssl_activated_date', '' );
            // This is for compatibility with old installations
            if ( $is_activated && !$activated_time ) {
                update_option( 'ssl_zen_ssl_activated_date', time() );
                $activated_time = time();
            }
            // Calculate the time to last review
            $time = time();
            $day = 86400;
            $diff = $time - (int) $activated_time;
            $days = (int) ($diff / $day);
            // Check for reviewed time
            if ( array_key_exists( 'reviewed', $_REQUEST ) ) {
                $reviewed = sanitize_text_field( $_REQUEST['reviewed'] );
                if ( $reviewed == 'done' ) {
                    // Never show me the reminder anymore
                    update_option( 'ssl_zen_review_reminder', '-1' );
                }
                if ( $reviewed == 'later' ) {
                    // Set the new reminder day
                    update_option( 'ssl_zen_review_reminder', $days );
                }
            }
            // Pick up the last review reminder
            $last_review_reminder = get_option( 'ssl_zen_review_reminder', '' );
            if ( !$is_activated || $last_review_reminder == -1 || strval( $days ) === strval( $last_review_reminder ) || $last_review_reminder != '' && !in_array( $days, [
                0,
                1,
                3,
                30,
                60,
                90
            ] ) ) {
                return;
            }
            add_action( 'admin_notices', function () {
                $class = 'm-1 notice notice-info is-dismissible';
                $heading = __( 'Wohooo!!!' );
                $message = sprintf( __( 'Your site has an SSL now! SSL Zen just saved you $60/year in SSL Certificate fees. Could you please do us a BIG favor and rate SSL Zen a 5-star on %1$swordpress.org%2$s and help us spread the word about the plugin?', 'ssl-zen' ), '<a href="https://wordpress.org/support/plugin/ssl-zen/reviews/#new-post" target="_blank">', '</a>' );
                $rate_the_plugin = sprintf( __( '%1$sRate the plugin%2$s', 'ssl-zen' ), '<a class="button button-primary" href="https://wordpress.org/support/plugin/ssl-zen/reviews/#new-post" target="_blank">', '</a>' );
                $dont_ask_again = sprintf( __( '%1$sDon\'t ask again%2$s', 'ssl-zen' ), '<a class="button" href="' . admin_url( 'admin.php?page=ssl_zen&tab=settings&reviewed=done' ) . '">', '</a>' );
                $remind_me_later = sprintf( __( '%1$sRemind me later%2$s', 'ssl-zen' ), '<a class="button" href="' . admin_url( 'admin.php?page=ssl_zen&tab=settings&reviewed=later' ) . '">', '</a>' );
                print sprintf(
                    '<div class="%1$s"><span class="notice-title">%2$s</span><p class="notice-content">%3$s</p><p>%4$s&nbsp;%5$s&nbsp;%6$s</p></div>',
                    $class,
                    $heading,
                    $message,
                    $rate_the_plugin,
                    $remind_me_later,
                    $dont_ask_again
                );
            } );
        }

        /**
         * Method to show review and congratulations of successfully SSL activation
         *
         * @since 2.0
         */
        public static function review() {
            update_option( 'ssl_zen_settings_stage', 'settings' );
            ?>
            <form name="frmReview" id="frmReview" action="" method="post">
                <?php 
            wp_nonce_field( 'ssl_zen_review', 'ssl_zen_review_nonce' );
            ?>
                <div class="ssl-zen-steps-container p-0 mb-4 border-0">
                    <div class="ssl-arrow"></div>
                    <div class="row ssl-zen-review-container">
                        <div class="col-md-10">
                            <div class="description pl-5 pr-0">
                                <div class="ssl mb-4">
                                    <div class="lock"></div>
                                    <div class="line"></div>
                                </div>
                                <h4><?php 
            _e( 'SSL Certificate Successfully Installed!', 'ssl-zen' );
            ?></h4>
                                <p class="saved-quote">
                                    <?php 
            _e( 'Wowzer! We just saved you $60/year in SSL Certificate fees.', 'ssl-zen' );
            ?>
                                </p>
                                <div class="propose d-lg-flex align-items-center">
                                    <?php 
            _e( 'Could you please do us a BIG favour and give SSL Zen a', 'ssl-zen' );
            ?>
                                    <i class="star ml-2 mr-2"></i>
                                    <i class="star mr-2"></i>
                                    <i class="star mr-2"></i>
                                    <i class="star mr-2"></i>
                                    <i class="star mr-2"></i>
                                    <?php 
            _e( 'on WordPress.org?', 'ssl-zen' );
            ?>
                                </div>
                                <a href="https://wordpress.org/support/plugin/ssl-zen/reviews/#new-post"
                                   target="_blank"
                                   class="review primary mt-4 mb-2"><?php 
            _e( 'LEAVE A REVIEW', 'ssl-zen' );
            ?></a>
                                <span class="review-timing"><?php 
            _e( 'It will only take few moments', 'ssl-zen' );
            ?></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex align-items-center remind" style="display: none!important;">
                                <a href="<?php 
            echo admin_url( 'admin.php?page=ssl_zen&tab=settings' );
            ?>">
                                    <?php 
            _e( 'REMIND ME LATER', 'ssl-zen' );
            ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php 
        }

        /**
         * Method for showing incompatibility of installing SSL due to cloudflare
         *
         * @since 3.1
         */
        private static function cloudflareDetectedState() {
            $heading = __( 'SSL certificate cannot be installed!', 'ssl-zen' );
            $message = sprintf( __( 'Due to technical limitations, it\'s currently not possible to install SSL certificate on CloudFlare hosted websites using our plugin. We are sorry for the inconvenience. %1$s Please watch the below video tutorial on how you can use CloudFlare Plugin (Unofficial) to get an SSL certificate on your website.%2$s', 'ssl-zen' ), "<br/>", '<br/><iframe width="560" height="315" src="https://www.youtube.com/embed/lPAt2nfgtPA" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>' );
            ?>
            <div class="ssl-zen-steps-container p-0 mb-4">
                <div class="row ssl-zen-error-state-container">
                    <div class="col-md-4">
                        <div class="mt-5 mb-5 banner"></div>
                    </div>
                    <div class="col-md-8">
                        <div class="pt-5 pb-5 pr-5 pl-0">
                            <h4><?php 
            echo esc_html( $heading );
            ?></h4>
                            <p><?php 
            echo esc_html( $message );
            ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
        }

        /**
         * Method for showing incompatibility of installing SSL due to cloudflare
         *
         * @since 3.1
         */
        private static function bluehostDetectedState() {
            $heading = __( 'SSL certificate cannot be installed!', 'ssl-zen' );
            $message = sprintf( __( 'You are trying to install the SSL certificate on a temporary Bluehost domain. SSL certificates can only be installed on your own domain name. Please follow these instructions to replace your temporary domain name with your own domain name.%1$s %2$s', 'ssl-zen' ), '<p>Video Tutorial - <a href="https://www.youtube.com/watch?v=E2I_8C5vMf4">https://www.youtube.com/watch?v=E2I_8C5vMf4</a></p>', '<p>Article - <a href="https://www.bluehost.com/help/article/using-your-temporary-url-with-wordpress#changing-from-temp">https://www.bluehost.com/help/article/using-your-temporary-url-with-wordpress#changing-from-temp</a></p>' );
            ?>
            <div class="ssl-zen-steps-container p-0 mb-4">
                <div class="row ssl-zen-error-state-container">
                    <div class="col-md-4">
                        <div class="mt-5 mb-5 banner"></div>
                    </div>
                    <div class="col-md-8">
                        <div class="pt-5 pb-5 pr-5 pl-0">
                            <h4><?php 
            echo esc_html( $heading );
            ?></h4>
                            <p><?php 
            echo esc_html( $message );
            ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
        }

        /**
         * Method for showing incompatibility of installing SSL
         *
         * @since 2.0
         */
        private static function errorState() {
            $heading = __( 'SSL certificate cannot be installed!', 'ssl-zen' );
            $message = __( 'Due to technical limitations, it\'s currently not possible to install SSL certificate on an IP address or a localhost. Please install the plugin on a publicly facing, worldwide unique domain name such as sslzen.com and try again.', 'ssl-zen' );
            ?>
            <div class="ssl-zen-steps-container p-0 mb-4">
                <div class="row ssl-zen-error-state-container">
                    <div class="col-md-4">
                        <div class="mt-5 mb-5 banner"></div>
                    </div>
                    <div class="col-md-8">
                        <div class="pt-5 pb-5 pr-5 pl-0">
                            <h4><?php 
            echo esc_html( $heading );
            ?></h4>
                            <p><?php 
            echo esc_html( $message );
            ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
        }

        /**
         * Method for showing system requirements
         *
         * @since 2.0
         */
        private static function systemRequirements() {
            $systemRequirements = ssl_zen_helper::getSystemRequirementsStatus();
            // Check at least one false value
            $col = 3;
            foreach ( $systemRequirements as $key => $item ) {
                if ( !$item ) {
                    $col = 9;
                    break;
                }
            }
            require SSL_ZEN_TEMPLATE_DIR . 'system-requirements.php';
        }

        /**
         * Method for showing pricing plans
         *
         * @static
         * @since  2.0
         */
        private static function pricing() {
            ssl_zen_pricing();
        }

        /**
         * Function to check support php function exec
         *
         * @since  1.2
         * @static
         */
        private static function check_exec_support() {
            if ( !\function_exists( 'shell_exec' ) || !\function_exists( 'exec' ) ) {
                return 'exec_not_support';
            }
        }

        /**
         * Function to add cron for renew certificate to the Cron Jobs
         *
         * @since  1.2
         * @static
         */
        private static function setup_cron() {
        }

        /**
         * Function to renew certificate which is calling from cron
         *
         * @return array|bool|mixed
         * @since  1.2
         * @static
         */
        public static function cron_ssl_renew() {
            return false;
        }

        /**
         * Function to display manage settings for SSL Zen.
         *
         * @since  1.0
         * @static
         */
        private static function settings() {
            ssl_zen_settings();
        }

        /**
         * Hook to be called when 'admin_init' action is called by wordpress.
         * Handles all the processing on the various setting steps as well as
         * redirection for incorrect steps
         *
         * @since  1.0
         * @static
         */
        public static function admin_init() {
            // let's avoid the header already sent error in case we want to redirect somewhere
            ob_start();
            $systemRequirementsNonce = ( isset( $_POST['ssl_zen_system_requirements_nonce'] ) ? sanitize_text_field( $_POST['ssl_zen_system_requirements_nonce'] ) : null );
            $sslZenPricingNonce = ( isset( $_POST['ssl_zen_pricing_nonce'] ) ? sanitize_text_field( $_POST['ssl_zen_pricing_nonce'] ) : null );
            $certificateNonce = ( isset( $_POST['ssl_zen_generate_certificate_nonce'] ) ? sanitize_text_field( $_POST['ssl_zen_generate_certificate_nonce'] ) : null );
            $verifyNonce = ( isset( $_POST['ssl_zen_verify_nonce'] ) ? sanitize_text_field( $_POST['ssl_zen_verify_nonce'] ) : null );
            $installCertificateNonce = ( isset( $_POST['ssl_zen_install_certificate_nonce'] ) ? sanitize_text_field( $_POST['ssl_zen_install_certificate_nonce'] ) : null );
            $activateSslNonce = ( isset( $_POST['ssl_zen_activate_ssl_nonce'] ) ? sanitize_text_field( $_POST['ssl_zen_activate_ssl_nonce'] ) : null );
            $settingsNonce = ( isset( $_POST['ssl_zen_settings_nonce'] ) ? sanitize_text_field( $_POST['ssl_zen_settings_nonce'] ) : null );
            // @TODO the below endless endifs should be changes to this switch format for easy maintenance and code readbility.
            $action = ( isset( $_POST['ssl_zen_activate_stackpath_cert'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ssl_zen_activate_stackpath_cert'] ), 'ssl_zen_activate_ssl' ) ? 'stackpathStep4' : null );
            switch ( $action ) {
                case 'stackpathStep4':
                    $siteUrl = str_replace( "http://", "https://", get_option( 'siteurl' ) );
                    $homeUrl = str_replace( "http://", "https://", get_option( 'home' ) );
                    update_option( 'siteurl', $siteUrl );
                    update_option( 'home', $homeUrl );
                    update_option( 'ssl_zen_ssl_activated', '1' );
                    update_option( 'ssl_zen_ssl_activated_date', time() );
                    update_option( 'ssl_zen_settings_stage', 'review' );
                    ssl_zen_helper::removeLogs();
                    self::fix_wp_config();
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=review' ) );
                    die;
                    break;
            }
            // Check system requirements step
            if ( !empty( $systemRequirementsNonce ) && wp_verify_nonce( $systemRequirementsNonce, 'ssl_zen_system_requirements' ) ) {
                self::checkSystemStatusFlag();
            } elseif ( !empty( $sslZenPricingNonce ) && wp_verify_nonce( $sslZenPricingNonce, 'ssl_zen_pricing' ) ) {
                // We have submitted from pricing page by selecting the free plan. So now need to move to step 1
                update_option( 'ssl_zen_settings_stage', 'step1' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step1' ) );
                exit;
            } elseif ( !empty( $certificateNonce ) && wp_verify_nonce( $certificateNonce, 'ssl_zen_generate_certificate' ) ) {
                self::handleStep1();
            } elseif ( !empty( $verifyNonce ) && wp_verify_nonce( $verifyNonce, 'ssl_zen_verify' ) ) {
                self::handleStep2();
            } elseif ( isset( $installCertificateNonce ) && wp_verify_nonce( $installCertificateNonce, 'ssl_zen_install_certificate' ) ) {
                self::handleStep3();
            } elseif ( isset( $activateSslNonce ) && wp_verify_nonce( $activateSslNonce, 'ssl_zen_activate_ssl' ) ) {
                self::handleStep4();
            } elseif ( isset( $settingsNonce ) && wp_verify_nonce( $settingsNonce, 'ssl_zen_settings' ) ) {
                self::handleSettings();
            }
            if ( isset( $_REQUEST['page'] ) ) {
                $page = sanitize_text_field( $_REQUEST['page'] );
            }
            if ( isset( $_REQUEST['tab'] ) ) {
                $tab = trim( sanitize_text_field( $_REQUEST['tab'] ) );
            }
            if ( isset( $page ) && $page == 'ssl_zen' ) {
                if ( isset( $_SERVER['HTTP_CF_RAY'] ) && $tab !== 'cloudflare_detected_state' ) {
                    update_option( 'ssl_zen_settings_stage', 'cloudflare_detected_state' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=cloudflare_detected_state' ) );
                    exit;
                }
                if ( (stripos( ssl_zen_helper::get_host(), '.temp.domains' ) !== false || stripos( ssl_zen_helper::get_host(), '.temp.domain' ) !== false) && $tab !== 'bluehost_detected_state' ) {
                    update_option( 'ssl_zen_settings_stage', 'bluehost_detected_state' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=bluehost_detected_state' ) );
                    exit;
                }
                self::premiumFeatures();
                self::checkForCorrectTab();
                self::handleDownload();
            }
        }

        /**
         * Check system status flag
         *
         * @return void
         */
        private static function checkSystemStatusFlag() {
            $systemRequirementsStatus = ( isset( $_POST['ssl_zen_system_requirements_status'] ) ? sanitize_text_field( $_POST['ssl_zen_system_requirements_status'] ) : null );
            if ( !empty( $systemRequirementsStatus ) ) {
                // Requirements are ok, then check cPanel availability (and also plan) then redirect properly
                if ( !sz_fs()->is_premium() ) {
                    // move to pricing page
                    update_option( 'ssl_zen_settings_stage', 'pricing' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=pricing' ) );
                    exit;
                } else {
                    // If not enabled then move to step1
                    update_option( 'ssl_zen_settings_stage', 'step1' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step1' ) );
                    exit;
                }
            } else {
                // Also we are able to omit this cause anyway we will get back to same stage
                // But we leave this , cause maybe we will add error message via GET
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=system_requirements&message' ) );
                exit;
            }
        }

        /**
         * Step 1 submitted
         *
         * @return void
         */
        private static function handleStep1() {
            // Define vars from sanitized POST
            $includeWWW = ( isset( $_POST['include_www'] ) ? sanitize_text_field( $_POST['include_www'] ) : '0' );
            $baseDomain = ( isset( $_POST['base_domain_name'] ) ? sanitize_text_field( $_POST['base_domain_name'] ) : '' );
            $email = ( isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '' );
            $ip_address = ( isset( $_POST['ip_address'] ) ? sanitize_text_field( $_POST['ip_address'] ) : '' );
            // Weird situation when our response returned are empty
            if ( sz_fs()->is_plan( 'cdn', true ) && !$ip_address ) {
                update_option( 'ssl_zen_settings_stage', 'step1' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step1&info=invalid_ip_address' ) );
                die;
            }
            $arrDomains = array($baseDomain);
            if ( !ssl_zen_helper::checkWWWSubDomainExistence( $baseDomain ) ) {
                // Include www sub domain
                if ( !empty( $includeWWW ) ) {
                    $arrDomains[] = 'www.' . $baseDomain;
                }
            } else {
                // Include non www domain too
                $arrDomains[] = preg_replace(
                    '/www./',
                    '',
                    $baseDomain,
                    1
                );
            }
            // Save form options in the db
            update_option( 'ssl_zen_include_wwww', $includeWWW );
            update_option( 'ssl_zen_domains', $arrDomains );
            update_option( 'ssl_zen_base_domain', $baseDomain );
            update_option( 'ssl_zen_email', $email );
            if ( !sz_fs()->is_plan( 'cdn', true ) ) {
                // Check with lets debug
                ssl_zen_certificate::debugLetsEncrypt( $baseDomain );
            }
            // Empty existing keys directory in the plugin
            $keys_dir = ssl_zen_certificate::getKeysDir();
            ssl_zen_helper::deleteAll( $keys_dir, true );
            // Remove http verification files, no meter what variant have used before
            ssl_zen_helper::deleteAll( ABSPATH . '.well-known/acme-challenge', true );
            update_option( 'ssl_zen_settings_stage', 'step2' );
            wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step2' ) );
            exit;
        }

        /**
         * Step 2 submitted
         *
         * @return void
         */
        private static function handleStep2() {
            $subStep = ( !empty( $_POST['ssl_zen_sub_step'] ) ? sanitize_text_field( $_POST['ssl_zen_sub_step'] ) : null );
            // Check sub steps
            if ( $subStep == 1 ) {
                // First sub step
                $variant = ( !empty( $_POST['ssl_zen_domain_verification'] ) ? sanitize_text_field( $_POST['ssl_zen_domain_verification'] ) : null );
                if ( $variant == 'http' || $variant == 'dns' ) {
                    // Generate order
                    ssl_zen_certificate::generateOrder();
                    // Store choosed variant
                    update_option( 'ssl_zen_domain_verification_variant', $variant );
                } else {
                    update_option( 'ssl_zen_settings_stage', 'step2' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step2&info=variant_error' ) );
                    exit;
                }
                // Again redirect to step 2, for continue the flow
                update_option( 'ssl_zen_settings_stage', 'step2' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step2' ) );
                exit;
            } elseif ( $subStep == 2 ) {
                // Check auth
                if ( ssl_zen_certificate::validateAuthorization() ) {
                    // Finalize the order for SSL Certificate
                    ssl_zen_certificate::finalizeOrder();
                    // Generate SSL Certificate
                    $certificate_created = ssl_zen_certificate::generateCertificate();
                    // E-Mail certificates to the user if certificates are generated successfully
                    if ( $certificate_created ) {
                        // Put the file in array of certificates for email
                        $arrCertificates = array(SSL_ZEN_DIR . 'keys/private.pem', SSL_ZEN_DIR . 'keys/certificate.crt', SSL_ZEN_DIR . 'keys/cabundle.crt');
                        //TODO move elsewhere
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        $message = __( 'Hello,', 'ssl-zen' ) . '<br><br>';
                        $message .= __( 'Thank you for using SSLZen.com for generating your SSL certificate.', 'ssl-zen' ) . '<br><br>';
                        $message .= __( 'Download the attached files on your local computer, You will need them in the next step to install SSL certificate on your website.', 'ssl-zen' ) . '<br>';
                        $message .= __( 'You can open these files using any text editors such as Notepad.', 'ssl-zen' ) . '<br><br>';
                        $message .= __( 'What does these files do?', 'ssl-zen' ) . '<br>';
                        $message .= __( 'private.pem = Private Key: ( KEY )', 'ssl-zen' ) . '<br>';
                        $message .= __( 'certificate.crt = Certificate: ( CRT )', 'ssl-zen' ) . '<br>';
                        $message .= __( 'cabundle.crt = Certificate Authority Bundle: ( CABUNDLE )', 'ssl-zen' ) . '<br><br>';
                        $message .= __( 'Please return back to SSL Zen and complete the remaining steps.', 'ssl-zen' ) . '<br><br>';
                        $message .= __( 'Thanks,', 'ssl-zen' ) . '<br>';
                        $message .= __( 'SSL Zen', 'ssl-zen' );
                        wp_mail(
                            get_option( 'ssl_zen_email', '' ),
                            'Confidential: SSL Certificates for ' . get_option( 'ssl_zen_base_domain', '' ),
                            $message,
                            $headers,
                            $arrCertificates
                        );
                    }
                    update_option( 'ssl_zen_settings_stage', 'step3' );
                    update_option( 'ssl_zen_certificate_60_days', date_i18n( 'Y-m-d', strtotime( "+60 day" ) ) );
                    update_option( 'ssl_zen_certificate_90_days', date_i18n( 'Y-m-d', strtotime( "+90 day" ) ) );
                    update_option( 'ssl_zen_certificate_60_days_email_sent', '' );
                    update_option( 'ssl_zen_certificate_90_days_email_sent', '' );
                    update_option( 'ssl_zen_settings_stage', 'step3' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step3&info=successfully_generated' ) );
                    die;
                } else {
                    update_option( 'ssl_zen_settings_stage', 'step2' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step2&info=invalid_sub_step' ) );
                    die;
                }
            } else {
                update_option( 'ssl_zen_settings_stage', 'step2' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step2&info=invalid_sub_step' ) );
                die;
            }
        }

        /**
         * Step 3 submitted
         *
         * @return void
         */
        private static function handleStep3() {
            $isValid = ssl_zen_certificate::verifyssl( get_option( 'ssl_zen_base_domain', '' ) );
            if ( $isValid ) {
                update_option( 'ssl_zen_settings_stage', 'step4' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step4' ) );
                die;
            } else {
                update_option( 'ssl_zen_settings_stage', 'step3' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step3&info=error' ) );
                die;
            }
        }

        /**
         * Step 4 submitted
         *
         * @return void
         */
        private static function handleStep4() {
            $isValid = ssl_zen_certificate::verifyssl( get_option( 'ssl_zen_base_domain', '' ) );
            if ( $isValid ) {
                $siteUrl = str_replace( "http://", "https://", get_option( 'siteurl' ) );
                $homeUrl = str_replace( "http://", "https://", get_option( 'home' ) );
                update_option( 'siteurl', $siteUrl );
                update_option( 'home', $homeUrl );
                update_option( 'ssl_zen_ssl_activated', '1' );
                update_option( 'ssl_zen_ssl_activated_date', time() );
                update_option( 'ssl_zen_settings_stage', 'review' );
                // Remove http verification files, no meter what variant have used before
                ssl_zen_helper::deleteAll( ABSPATH . '.well-known/acme-challenge', true );
                // Update acme-challenge htaccess to force https
                self::createHtaccessForWellKnown();
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=review' ) );
                die;
            } else {
                $siteUrl = str_replace( "https://", "http://", get_option( 'siteurl' ) );
                $homeUrl = str_replace( "https://", "http://", get_option( 'home' ) );
                update_option( 'siteurl', $siteUrl );
                update_option( 'home', $homeUrl );
                update_option( 'ssl_zen_ssl_activated', '' );
                update_option( 'ssl_zen_settings_stage', 'step4' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step4&info=error' ) );
                die;
            }
        }

        /**
         * Handle settings
         *
         * @return void
         */
        private static function handleSettings() {
            if ( !empty( $_POST['ssl_zen_deactivate_plugin'] ) ) {
                self::remove_plugin();
                wp_redirect( admin_url( 'plugins.php' ) );
                exit;
            } elseif ( !empty( $_POST['ssl_zen_renew_certificate'] ) ) {
                // Renew click handle. We avoid checks about valid renew date
                // If the post data is not empty then the renew is valid
                update_option( 'ssl_zen_settings_stage', 'step1' );
                wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step1' ) );
                die;
            } else {
                if ( sz_fs()->is_plan( 'cdn', true ) ) {
                    self::updateStackpathSettings();
                } else {
                    $htaccessRedirect = ( isset( $_POST['enable_301_htaccess_redirect'] ) ? '1' : '0' );
                    $htaccessLock = ( isset( $_POST['lock_htaccess_file'] ) ? '1' : '0' );
                    update_option( 'ssl_zen_enable_301_htaccess_redirect', $htaccessRedirect );
                    update_option( 'ssl_zen_lock_htaccess_file', $htaccessLock );
                    $info = 'success_settings';
                    $hasHtaccessRules = self::check_htaccess_rules();
                    if ( $htaccessRedirect == '1' && $hasHtaccessRules === false || $htaccessRedirect == '0' && $hasHtaccessRules === true ) {
                        if ( $htaccessLock ) {
                            $info = 'lock';
                        } else {
                            // Make sure htaccess is writable
                            if ( is_writable( ABSPATH . '.htaccess' ) ) {
                                $htaccess = file_get_contents( ABSPATH . '.htaccess' );
                                if ( $htaccessRedirect == '1' ) {
                                    // Add rules to htaccess
                                    $rules = self::get_htaccess_rules();
                                    // insert rules before wordpress part.
                                    if ( strlen( $rules ) > 0 ) {
                                        $wptag = "# BEGIN WordPress";
                                        if ( strpos( $htaccess, $wptag ) !== false ) {
                                            $htaccess = str_replace( $wptag, $rules . $wptag, $htaccess );
                                        } else {
                                            $htaccess = $htaccess . $rules;
                                        }
                                        insert_with_markers( ABSPATH . '.htaccess', 'SSL_ZEN', $htaccess );
                                    }
                                } else {
                                    // Remove rules from htaccess
                                    $pattern = "/#\\s?BEGIN\\s?SSL_ZEN.*?#\\s?END\\s?SSL_ZEN/s";
                                    if ( preg_match( $pattern, $htaccess ) ) {
                                        $htaccess = preg_replace( $pattern, "", $htaccess );
                                        insert_with_markers( ABSPATH . '.htaccess', '', $htaccess );
                                    }
                                }
                            } else {
                                $info = 'writeerr';
                            }
                        }
                    }
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=settings&info=' . $info ) );
                    die;
                }
            }
        }

        /**
         * Premium features flow
         *
         * @return void
         */
        private static function premiumFeatures() {
        }

        /**
         * Check if correct tab is loaded else redirect to the correct tab
         *
         * @return void
         */
        private static function checkForCorrectTab() {
            if ( isset( $_REQUEST['tab'] ) ) {
                $tab = trim( sanitize_text_field( $_REQUEST['tab'] ) );
            }
            $currentSettingTab = get_option( 'ssl_zen_settings_stage', '' );
            if ( $currentSettingTab != '' && !isset( $tab ) ) {
                if ( $currentSettingTab == 'settings' ) {
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=renew' ) );
                    exit;
                } else {
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=' . $currentSettingTab ) );
                    exit;
                }
            } else {
                $tab = ( isset( $tab ) ? $tab : '' );
                if ( $currentSettingTab != $tab && !in_array( $tab, self::$allowedTabs[$currentSettingTab] ) ) {
                    $url = 'admin.php?page=ssl_zen';
                    if ( $currentSettingTab != '' ) {
                        $url .= '&tab=' . $currentSettingTab;
                    }
                    wp_redirect( admin_url( $url ) );
                    exit;
                }
                // The initial point
                if ( $currentSettingTab == '' ) {
                    // Check if website is installed locally
                    if ( ssl_zen_helper::checkIfWebsiteInstalledLocally() ) {
                        // Set stage and redirect
                        update_option( 'ssl_zen_settings_stage', 'error_state' );
                        wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=error_state' ) );
                        exit;
                    }
                    // Check system requirements
                    update_option( 'ssl_zen_settings_stage', 'system_requirements' );
                    wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=system_requirements' ) );
                    exit;
                }
            }
        }

        /**
         * Handle download clicks
         *
         * @return void
         */
        private static function handleDownload() {
            if ( isset( $_REQUEST['download'] ) ) {
                $download = trim( sanitize_text_field( $_REQUEST['download'] ) );
            }
            if ( isset( $download ) && $download != '' ) {
                $currentSettingTab = get_option( 'ssl_zen_settings_stage', '' );
                if ( is_numeric( $download ) && $currentSettingTab == 'step2' ) {
                    $arrPending = ssl_zen_certificate::getPendingAuthorization( \LEClient\LEOrder::CHALLENGE_TYPE_HTTP );
                    // This is related to step2 verification files download
                    if ( isset( $arrPending[$download] ) && is_array( $arrPending[$download] ) ) {
                        $fileName = ( isset( $arrPending[$download]['filename'] ) ? $arrPending[$download]['filename'] : '' );
                        $fileContent = ( isset( $arrPending[$download]['content'] ) ? $arrPending[$download]['content'] : '' );
                    } else {
                        update_option( 'ssl_zen_settings_stage', 'step2' );
                        wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step2&info=dlerr' ) );
                        exit;
                    }
                } elseif ( $currentSettingTab == 'step3' ) {
                    // This is related to step3 certs files download
                    $fileName = ( $download === 'private' ? $download . '.pem' : $download . '.crt' );
                    $keyDir = ssl_zen_certificate::getKeysDir();
                    $filePath = $keyDir . $fileName;
                    if ( file_exists( $filePath ) ) {
                        $fileContent = file_get_contents( $filePath );
                    } else {
                        update_option( 'ssl_zen_settings_stage', 'step3' );
                        wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=step3&info=dlerr' ) );
                        exit;
                    }
                } elseif ( $currentSettingTab == 'review' ) {
                    // This is related to debug-log status-info download
                    if ( $download == 'status_info' ) {
                        $fileName = 'status_info.csv';
                        // File pointer connected with output stream
                        $file = fopen( 'php://output', 'w' );
                        // Get fields
                        $infoFields = array_merge( ssl_zen_helper::getServerStatusFields(), ssl_zen_helper::getWordPressStatusFields() );
                        // Start buffering, because here we are proceeding the output
                        ob_start();
                        // Set file columns
                        fputcsv( $file, array('Property', 'Value') );
                        // Set content
                        foreach ( $infoFields as $key => $field ) {
                            fputcsv( $file, [sanitize_text_field( $key ), sanitize_text_field( $field )] );
                        }
                        // Read to string, by get from buffer and cleaning it
                        $fileContent = ob_get_clean();
                    } elseif ( $download == 'debug_log' ) {
                        $fileName = 'debug.log';
                        $fileContent = ( file_exists( SSL_ZEN_DIR . 'log/debug.log' ) ? file_get_contents( SSL_ZEN_DIR . 'log/' . $fileName ) : '' );
                    } else {
                        wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=settings&info=dlerr_general' ) );
                        exit;
                    }
                }
                header( 'Content-Type: text/plain' );
                header( 'Content-Disposition: attachment; filename=' . $fileName );
                header( 'Expires: 0' );
                header( 'Cache-Control: must-revalidate' );
                header( 'Pragma: public' );
                header( 'Content-Length: ' . strlen( $fileContent ) );
                echo $fileContent;
                die;
            }
        }

        /**
         * Functon to check if htaccess rules exists
         *
         * @since  1.0
         * @static
         */
        public static function check_htaccess_rules() {
            if ( file_exists( ABSPATH . '.htaccess' ) && is_readable( ABSPATH . '.htaccess' ) ) {
                $htaccess = file_get_contents( ABSPATH . '.htaccess' );
                $check = null;
                preg_match( "/BEGIN\\s?SSL_ZEN/", $htaccess, $check );
                if ( count( $check ) === 0 ) {
                    return false;
                } else {
                    return true;
                }
            }
            return false;
        }

        /**
         * Functon to get all the htaccess rules
         *
         * @since  1.0
         * @static
         */
        public static function get_htaccess_rules() {
            $rule = "";
            $response = wp_remote_get( home_url() );
            $filecontents = '';
            if ( is_array( $response ) ) {
                $filecontents = wp_remote_retrieve_body( $response );
            }
            //if the htaccess test was successfull, and we know the redirectype, edit
            $rule = [];
            $rule[] = "<IfModule mod_rewrite.c>\n";
            $rule[] = "RewriteEngine on\n";
            if ( strpos( $filecontents, "#SERVER-HTTPS-ON#" ) !== false || isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' ) {
                $rule[] = "RewriteCond %{HTTPS} !=on [NC]\n";
            } elseif ( strpos( $filecontents, "#SERVER-HTTPS-1#" ) !== false || isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == '1' ) {
                $rule[] = "RewriteCond %{HTTPS} !=1\n";
            } elseif ( strpos( $filecontents, "#LOADBALANCER#" ) !== false || isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) {
                $rule[] = "RewriteCond %{HTTP:X-Forwarded-Proto} !https\n";
            } elseif ( strpos( $filecontents, "#HTTP_X_PROTO#" ) !== false || isset( $_SERVER['HTTP_X_PROTO'] ) && $_SERVER['HTTP_X_PROTO'] == 'SSL' ) {
                $rule[] = "RewriteCond %{HTTP:X-Proto} !SSL\n";
            } elseif ( strpos( $filecontents, "#CLOUDFLARE#" ) !== false || isset( $_SERVER['HTTP_CF_VISITOR'] ) && $_SERVER['HTTP_CF_VISITOR'] == 'https' ) {
                $rule[] = "RewriteCond %{HTTP:CF-Visitor} '\"scheme\":\"http\"'\n";
                //some concatenation to get the quotes right.
            } elseif ( strpos( $filecontents, "#SERVERPORT443#" ) !== false || isset( $_SERVER['SERVER_PORT'] ) && '443' == $_SERVER['SERVER_PORT'] ) {
                $rule[] = "RewriteCond %{SERVER_PORT} !443\n";
            } elseif ( strpos( $filecontents, "#CLOUDFRONT#" ) !== false || isset( $_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO'] ) && $_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO'] == 'https' ) {
                $rule[] = "RewriteCond %{HTTP:CloudFront-Forwarded-Proto} !https\n";
            } elseif ( strpos( $filecontents, "#HTTP_X_FORWARDED_SSL_ON#" ) !== false || isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' ) {
                $rule[] = "RewriteCond %{HTTP:X-Forwarded-SSL} !on\n";
            } elseif ( strpos( $filecontents, "#HTTP_X_FORWARDED_SSL_1#" ) !== false || isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && $_SERVER['HTTP_X_FORWARDED_SSL'] == '1' ) {
                $rule[] = "RewriteCond %{HTTP:X-Forwarded-SSL} !=1\n";
            } elseif ( strpos( $filecontents, "#ENVHTTPS#" ) !== false || isset( $_ENV['HTTPS'] ) && 'on' == $_ENV['HTTPS'] ) {
                $rule[] = "RewriteCond %{ENV:HTTPS} !=on\n";
            }
            //if multisite, and NOT subfolder install (checked for in the detect_config function)
            //, add a condition so it only applies to sites where plugin is activated
            if ( is_multisite() ) {
                global $wp_version;
                $sites = ( $wp_version >= 4.6 ? get_sites() : wp_get_sites() );
                foreach ( $sites as $domain ) {
                    //remove http or https.
                    $domain = preg_replace( "/(http:\\/\\/|https:\\/\\/)/", "", $domain );
                    //We excluded subfolders, so treat as domain
                    $domain_no_www = str_replace( "www.", "", $domain );
                    $domain_yes_www = "www." . $domain_no_www;
                    $rule[] = "#rewritecond " . sanitize_url( $domain ) . "\n";
                    $rule[] = "RewriteCond %{HTTP_HOST} ^" . sanitize_url( preg_quote( $domain_no_www, "/" ) ) . " [OR]\n";
                    $rule[] = "RewriteCond %{HTTP_HOST} ^" . sanitize_url( preg_quote( $domain_yes_www, "/" ) ) . " [OR]\n";
                    $rule[] = "#end rewritecond " . sanitize_url( $domain ) . "\n";
                }
                //now remove last [OR] if at least on one site the plugin was activated, so we have at lease one condition
                if ( count( $sites ) > 0 ) {
                    $rule = strrev( implode( "", explode( strrev( "[OR]" ), strrev( $rule ), 2 ) ) );
                }
            }
            //fastest cache compatibility
            if ( class_exists( 'WpFastestCache' ) ) {
                // Get wp content directory name from path constants defined in WordPress config.
                $wp_content_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR );
                $rule[] = "RewriteCond %{REQUEST_URI} !" . $wp_content_dir . "\\/cache\\/(all|wpfc-mobile-cache)\n";
            }
            //Exclude .well-known/acme-challenge for Let's Encrypt validation
            $rule[] = "RewriteCond %{REQUEST_URI} !^/\\.well-known/acme-challenge/\n";
            $rule[] = "RewriteRule ^(.*)\$ https://%{HTTP_HOST}/\$1 [R=301,L]\n";
            $rule[] = "</IfModule>\n";
            $final_rule = "\n# BEGIN SSL_ZEN\n" . implode( '', $rule ) . "# END SSL_ZEN\n";
            return preg_replace( "/\n+/", "\n", $final_rule );
        }

        /**
         * Functon to remove a plugin from the array
         *
         * @since  1.0
         * @static
         */
        private static function remove_plugin_from_array( $activePlugins ) {
            $key = array_search( SSL_ZEN_BASEFILE, $activePlugins );
            if ( false !== $key ) {
                unset($activePlugins[$key]);
            }
            return $activePlugins;
        }

        /**
         * Functon to remove a plugin from active plugins list
         *
         * @since  1.0
         * @static
         */
        private static function remove_plugin() {
            if ( is_multisite() ) {
                $activePlugins = get_site_option( 'active_sitewide_plugins', array() );
                if ( is_plugin_active_for_network( SSL_ZEN_BASEFILE ) ) {
                    unset($activePlugins[SSL_ZEN_BASEFILE]);
                }
                update_site_option( 'active_sitewide_plugins', $activePlugins );
                /* remove plugin one by one on each site */
                $sites = self::get_network_sites();
                foreach ( $sites as $site ) {
                    self::switch_network_site( $site );
                    $activePlugins = get_option( 'active_plugins', array() );
                    $activePlugins = self::remove_plugin_from_array( $activePlugins );
                    update_option( 'active_plugins', $activePlugins );
                    /* switches back to previous blog, not current, so we have to do it each loop */
                    restore_current_blog();
                }
            } else {
                $activePlugins = get_option( 'active_plugins', array() );
                $activePlugins = self::remove_plugin_from_array( $activePlugins );
                update_option( 'active_plugins', $activePlugins );
            }
            self::remove_stackpath();
        }

        /**
         * Function to get all network sites
         *
         * @since  1.0
         * @static
         */
        private static function get_network_sites() {
            global $wp_version;
            $sites = ( $wp_version >= 4.6 ? get_sites() : wp_get_sites() );
            return $sites;
        }

        /**
         * Functon to switch to network sites
         *
         * @since  1.0
         * @static
         */
        private static function switch_network_site( $site ) {
            global $wp_version;
            if ( $wp_version >= 4.6 ) {
                switch_to_blog( $site->blog_id );
            } else {
                switch_to_blog( $site['blog_id'] );
            }
        }

        /**
         * Hook to remove all the plugin settings on deactivation
         *
         * @since  1.0
         * @static
         */
        public static function deactivate_plugin() {
            if ( is_multisite() ) {
                // @TODO: this is wrong - this should be done in uninstall.
                delete_site_option( 'ssl_zen_settings_stage' );
                delete_site_option( 'ssl_zen_include_wwww' );
                delete_site_option( 'ssl_zen_domains' );
                delete_site_option( 'ssl_zen_base_domain' );
                delete_site_option( 'ssl_zen_email' );
                delete_site_option( 'ssl_zen_certificate_60_days' );
                delete_site_option( 'ssl_zen_certificate_90_days' );
                delete_site_option( 'ssl_zen_certificate_60_days_email_sent' );
                delete_site_option( 'ssl_zen_certificate_90_days_email_sent' );
                delete_site_option( 'ssl_zen_ssl_activated' );
                delete_site_option( 'ssl_zen_ssl_activated_date' );
                delete_site_option( 'ssl_zen_enable_301_htaccess_redirect' );
                delete_site_option( 'ssl_zen_lock_htaccess_file' );
                delete_site_option( 'ssl_zen_ssl_check_status' );
                delete_site_option( 'ssl_zen_domain_verification_variant' );
                delete_site_option( 'ssl_zen_dns_check_activation' );
                delete_site_option( 'ssl_zen_enable_debug' );
                delete_site_option( 'ssl_zen_cpanel_detected' );
                $sites = self::get_network_sites();
                foreach ( $sites as $site ) {
                    self::switch_network_site( $site );
                    $siteUrl = str_replace( "https://", "http://", get_option( 'siteurl', '' ) );
                    $homeUrl = str_replace( "https://", "http://", get_option( 'home', '' ) );
                    update_option( 'siteurl', $siteUrl );
                    update_option( 'home', $homeUrl );
                    // @TODO: this is wrong - this should be done in uninstall.
                    delete_option( 'ssl_zen_settings_stage' );
                    delete_option( 'ssl_zen_include_wwww' );
                    delete_option( 'ssl_zen_domains' );
                    delete_option( 'ssl_zen_base_domain' );
                    delete_option( 'ssl_zen_email' );
                    delete_option( 'ssl_zen_certificate_60_days' );
                    delete_option( 'ssl_zen_certificate_90_days' );
                    delete_option( 'ssl_zen_certificate_60_days_email_sent' );
                    delete_option( 'ssl_zen_certificate_90_days_email_sent' );
                    delete_option( 'ssl_zen_ssl_activated' );
                    delete_option( 'ssl_zen_ssl_activated_date' );
                    delete_option( 'ssl_zen_enable_301_htaccess_redirect' );
                    delete_option( 'ssl_zen_lock_htaccess_file' );
                    delete_option( 'ssl_zen_ssl_check_status' );
                    delete_option( 'ssl_zen_domain_verification_variant' );
                    delete_option( 'ssl_zen_dns_check_activation' );
                    delete_option( 'ssl_zen_enable_debug' );
                    delete_option( 'ssl_zen_cpanel_detected' );
                    restore_current_blog();
                }
            } else {
                /* Remove SSL from site and home urls */
                $siteUrl = str_replace( "https://", "http://", get_option( 'siteurl', '' ) );
                $homeUrl = str_replace( "https://", "http://", get_option( 'home', '' ) );
                update_option( 'siteurl', $siteUrl );
                update_option( 'home', $homeUrl );
                // @TODO: this is wrong - this should be done in uninstall.
                /* Remove all the database settings */
                delete_option( 'ssl_zen_settings_stage' );
                delete_option( 'ssl_zen_include_wwww' );
                delete_option( 'ssl_zen_domains' );
                delete_option( 'ssl_zen_base_domain' );
                delete_option( 'ssl_zen_email' );
                delete_option( 'ssl_zen_certificate_60_days' );
                delete_option( 'ssl_zen_certificate_90_days' );
                delete_option( 'ssl_zen_certificate_60_days_email_sent' );
                delete_option( 'ssl_zen_certificate_90_days_email_sent' );
                delete_option( 'ssl_zen_ssl_activated' );
                delete_option( 'ssl_zen_ssl_activated_date' );
                delete_option( 'ssl_zen_enable_301_htaccess_redirect' );
                delete_option( 'ssl_zen_lock_htaccess_file' );
                delete_option( 'ssl_zen_ssl_check_status' );
                delete_option( 'ssl_zen_domain_verification_variant' );
                delete_option( 'ssl_zen_dns_check_activation' );
                delete_option( 'ssl_zen_enable_debug' );
                delete_option( 'ssl_zen_activated' );
                delete_option( 'ssl_zen_activated_date' );
                delete_option( 'ssl_zen_cpanel_detected' );
                // this will help in firing reactivation.
                add_option( 'ssl_zen_deactivated', 1 );
                self::remove_fix_wp_config();
            }
            if ( !sz_fs()->is_plan( 'cdn', true ) ) {
                /* Remove rules from .htaccess file */
                if ( is_writable( ABSPATH . '.htaccess' ) ) {
                    $htaccess = file_get_contents( ABSPATH . '.htaccess' );
                    /* Remove rules from htaccess */
                    $pattern = "/#\\s?BEGIN\\s?SSL_ZEN.*?#\\s?END\\s?SSL_ZEN/s";
                    if ( preg_match( $pattern, $htaccess ) ) {
                        $htaccess = preg_replace( $pattern, "", $htaccess );
                    }
                    insert_with_markers( ABSPATH . '.htaccess', '', $htaccess );
                }
            }
            self::remove_plugin();
            // TODO check this
            // Added by Freemius to fix the 'Auto Install after payment' bug.
            if ( empty( $_POST['action'] ) || sanitize_text_field( $_POST['action'] ) !== sz_fs()->get_ajax_action( 'install_premium_version' ) ) {
                wp_redirect( admin_url( 'plugins.php?deactivate=true', 'http' ) );
                exit;
            }
        }

        /**
         * Hook to add custom links on the plugins page
         *
         * @param array $links
         *
         * @return array $links
         * @since  1.0
         * @static
         */
        public static function plugin_action_links( $links ) {
            if ( sz_fs()->is_plan( 'pro', true ) ) {
                $links[] = '<a href="' . admin_url( 'admin.php?page=ssl_zen&tab=step1' ) . '">' . __( 'Setup', 'ssl-zen' ) . '</a>';
            }
            $links[] = '<a href="' . admin_url( 'admin.php?page=ssl_zen&tab=settings' ) . '">' . __( 'Settings', 'ssl-zen' ) . '</a>';
            $links[] = '<a href="' . admin_url( 'admin.php?page=ssl_zen-contact' ) . '">' . __( 'Support', 'ssl-zen' ) . '</a>';
            return $links;
        }

        /**
         * Function to check is certificate files are exist
         *
         * @since  1.2
         * @static
         */
        private static function check_certificate_files() {
            return false;
        }

        /**
         * Function to generate acme files for validate domain in lets encrypt
         *
         * @param bool $is_ajax
         *
         * @return mixed
         * @since  1.2
         * @static
         */
        private static function generate_acme_files_and_verify( $is_ajax = false ) {
        }

        /**
         * Function to check is username and password are correct for login to cpanel
         * This function should be available to both free and paid plans
         *
         * @param string $username
         * @param string $password
         * @param bool   $is_ajax
         *
         * @return bool
         * @since  1.2
         * @static
         */
        private static function verify_cpanel_cred( $username = '', $password = '', $is_ajax = false ) {
        }

        /**
         * Function to update the database timestamps to look for in the next SSL run
         *
         * @return void
         */
        private static function update_timestamps() {
            update_option( 'ssl_zen_certificate_60_days', date_i18n( 'Y-m-d', strtotime( "+60 day" ) ) );
            update_option( 'ssl_zen_certificate_90_days', date_i18n( 'Y-m-d', strtotime( "+90 day" ) ) );
            update_option( 'ssl_zen_certificate_60_days_email_sent', '' );
            update_option( 'ssl_zen_certificate_90_days_email_sent', '' );
        }

        /**
         * Function to install generated certificate files to cPanel
         *
         * @param bool $is_ajax
         *
         * @return mixed
         * @since  1.2
         * @static
         */
        private static function install_certificate( $is_ajax = false ) {
        }

        /**
         * Creating htaccess file for well-known folder in order to force https to it
         */
        public static function createHtaccessForWellKnown() {
            $acmeHtaccessFileDir = ABSPATH . '.well-known/acme-challenge/.htaccess';
            if ( !file_exists( $acmeHtaccessFileDir ) ) {
                $file = fopen( $acmeHtaccessFileDir, "w" );
            } else {
                $file = true;
            }
            if ( $file !== false && is_writable( $acmeHtaccessFileDir ) ) {
                $rule = "<IfModule mod_rewrite.c>" . "\n";
                $rule .= "RewriteEngine on" . "\n";
                $rule .= "RewriteCond %{HTTPS} =on [NC]" . "\n";
                $rule .= "RewriteRule ^(.*)\$ http://%{HTTP_HOST} [R=301,L]" . "\n";
                $rule .= "</IfModule>" . "\n";
                insert_with_markers( $acmeHtaccessFileDir, 'SSL_ZEN', $rule );
            }
        }

    }

    /**
     * Calling init function and activate hooks and filters.
     */
    ssl_zen_admin::init();
}