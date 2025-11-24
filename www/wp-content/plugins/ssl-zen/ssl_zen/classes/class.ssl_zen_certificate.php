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
 * Version:           1.9.6
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

use LEClient\LEClient;
use LEClient\LEOrder;

if (!class_exists('ssl_zen_certificate')) {
    /**
     * Class to manage ssl certificates by interacting with LEClient library
     */
    class ssl_zen_certificate
    {
		public static $client = null;

		public static $order = null;

		public static $pendingAuths = [];

        /**
         * Create client on Let's Encrypt
         *
         * @param bool $redirect
         *
         * @return LEClient Returns the object of the Let's Encrypt Client
         * @since  1.0
         * @static
         */
        public static function createClient($redirect = true)
        {
            try {
	            if ( ! empty( self::$client ) ) {
		            return self::$client;
	            }
                $email = get_option('ssl_zen_email', '');
        
                // Check the log flag
                if (!empty(get_option('ssl_zen_enable_debug', ''))) {
                    $log = LEClient::LOG_DEBUG;
                } else {
                    $log = LEClient::LOG_STATUS;
                }
        
                if (!defined('SSL_ZEN_LE_STAGING')) {
                    define('SSL_ZEN_LE_STAGING', false);
                }

	            $keys_dir = self::getKeysDir();

                // Create the keys directory if it doesn't exist
                if (!file_exists($keys_dir)) {
                    mkdir($keys_dir, 0700, true);
                }

	            self::$client = new LEClient(array($email), SSL_ZEN_LE_STAGING, $log, $keys_dir);
				return self::$client;
            } catch (Exception $e) {
                self::redirect_on_error($e, $redirect);
            }
	        return null;
        }
        public static function getKeysDir()
        {
            $keys_dir_name = get_option('ssl_zen_keys_dir_name', '');
            if (empty($keys_dir_name)) {
                // Generate a unique and hard-to-guess directory name
                $keys_dir_name = 'keys_' . wp_generate_password(12, false, false);
                update_option('ssl_zen_keys_dir_name', $keys_dir_name);
            }
            return SSL_ZEN_DIR . $keys_dir_name . '/';
        }
        /**
         * Remove the htaccess file for well-known folder in order to force https to it
         */
        public static function removeHtaccessForWellKnown()
        {
            $wellKnownHtaccessPath = ABSPATH . '.well-known/acme-challenge/.htaccess';
            # If it exists, delete it
            if (file_exists($wellKnownHtaccessPath)) {
                unlink($wellKnownHtaccessPath);
            }
        }

        /**
         * Generates an order on Let's Encrypt
         *
         * @param bool $redirect
         *
         * @return LEOrder Returns the object of the Let's Encrypt Order
         * @since  1.0
         * @static
         */
        public static function generateOrder($redirect = true)
        {
            try {
	            if ( ! empty( self::$order ) ) {
		            return self::$order;
	            }
                $baseDomainName = get_option('ssl_zen_base_domain', '');
                $domains = get_option('ssl_zen_domains', array());
                $client = self::createClient($redirect);
                if (!empty($client)) {
                    # Remove the .htaccess file from well-known
                    self::removeHtaccessForWellKnown();
	                self::$order = $client->getOrCreateOrder($baseDomainName, $domains);
                    return self::$order;
                } else {
                    return null;
                }
            } catch (Exception $e) {
	            self::redirect_on_error($e, $redirect);
            }
			return null;
        }

        /**
         * Checks for all the pending authorizations on Let's Encrypt for an order and
         * update the authorization status
         *
         * @param $type
         * @param bool $redirect
         *
         * @since  1.0
         * @static
         */
        public static function updateAuthorizations($type, $redirect = true)
        {
            try {
                $arrPending = self::getPendingAuthorization($type, $redirect);
                if (is_array($arrPending) && count($arrPending)) {
                    $order = self::generateOrder($redirect);
                    foreach ($arrPending as $pending) {
                        $order->verifyPendingOrderAuthorization($pending['identifier'], $type, false);
                    }
                }
            } catch (Exception $e) {
	            self::redirect_on_error($e, $redirect);
            }
        }

        /**
         * Check if the authorizations are valid for the particular order
         *
         * @param bool $redirect
         *
         * @return Boolean Returns the status of domain verification
         * @since  1.0
         * @static
         */
        public static function validateAuthorization($redirect = true)
        {
            try {
                $order = self::generateOrder($redirect);

                if (empty($order)) {
                    throw new Exception('Order is empty');
                }

                return $order->allAuthorizationsValid();
            } catch (Exception $e) {
                self::redirect_on_error($e, $redirect);
            }
	        return false;
        }

        /**
         * Fetches all the pending authorizations for the particular order
         *
         * @param $type
         *
         * @param bool $redirect
         *
         * @return array|object Returns all the pending authorizations
         * @since  1.0
         * @static
         */
        public static function getPendingAuthorization($type, $redirect = true)
        {
            try {
	            if ( ! empty( self::$pendingAuths[$type] ) ) {
		            return self::$pendingAuths[$type];
	            }
                $order = self::generateOrder($redirect);

                if (!empty($order)) {
	                self::$pendingAuths[$type] = $order->getPendingAuthorizations($type);
                    return self::$pendingAuths[$type];
                } else {
                    return null;
                }
            } catch (Exception $e) {
	            self::redirect_on_error($e, $redirect);
            }
			return [];
        }

        /**
         * Finalizes the Let's Encrypt order
         *
         * @since  1.0
         * @static
         */
        public static function finalizeOrder()
        {

            try {
                $order = ssl_zen_certificate::generateOrder();
                if (!$order->isFinalized()) {
                    $order->finalizeOrder();
                }
            } catch (Exception $e) {
                self::redirect_on_error($e);
            }
        }

        /**
         * Generates and returns the path in the form of array for the certificates for a particular order
         *
         * @return bool Paths of the certificates generated for a particular order
         * @since  1.0
         * @static
         */
        public static function generateCertificate()
        {
            try {
                $order = ssl_zen_certificate::generateOrder();
                if ($order->isFinalized()) {
                    $order->getCertificate();
                }
                $isCreated = $order->getCertificate();
                if ($isCreated) {
					$keyDir = ssl_zen_certificate::getKeysDir();
                    // When certificates are created, split the file to get the CA bundle cert'
                    $fullchainPath = $keyDir . 'fullchain.crt';
                    $certificatePath = $keyDir . 'certificate.crt';
                    $cabundlePath = $keyDir . 'cabundle.crt';
                    $fullchainData = file_get_contents($fullchainPath);
                    $certificateData = file_get_contents($certificatePath);
                    $cabundleData = trim(str_replace($certificateData, '', $fullchainData));
                    file_put_contents($cabundlePath, $cabundleData);
                }
                return $isCreated;
            } catch (Exception $e) {
                self::redirect_on_error($e);
            }
        }

        /**
         * Verifies if the SSL certificate is successfully installed on the domain or not.
         *
         * @return Bool True if the SSL certificate is installed successfully, false otherwise.
         * @since  1.0
         * @static
         */
        public static function verifyssl($domain)
        {
	        try {
		        $connection = @fsockopen( 'ssl://' . $domain, 443, $errno, $errstr, 30 );
		        if ( $connection ) {
			        // SSL is activated
			        fclose( $connection );

			        return true;
		        } else {
			        // SSL is not activated or connection failed
			        error_log( "SSL_ZEN (verifyssl): $errstr ($errno)" );
		        }
	        } catch ( Exception $e ) {
		        error_log( "SSL_ZEN (verifyssl): " . $e->getMessage() );
	        }

	        return false;
        }

        /**
         * Redirect user to the page when error is raised in the Lets Encrypt API
         *
         * @param $e Exception|null
         * @param $redirect boolean
         *
         * @since  1.1
         * @static
         */
        private static function redirect_on_error($e = null, $redirect = true)
        {
			error_log( "SSL_ZEN: " . $e->getMessage() );

			if ($redirect) {
				$currentSettingTab = get_option('ssl_zen_settings_stage', '');

				if ($currentSettingTab == '') {
					$currentSettingTab = 'step1';
				}

				wp_redirect(admin_url('admin.php?page=ssl_zen&tab=' . $currentSettingTab . '&info=api_error'));
				exit;
			}
        }

        /**
         * Check support of shell_exec function
         *
         * @return bool
         * @since  1.7
         */
        public static function supportShellExec()
        {
            return function_exists('shell_exec');
        }

        /**
         * Check support of cPanel command line api
         *
         * @return bool
         * @since  1.7
         */
        public static function supportCPanelCommandLineApi()
        {
            return self::supportShellExec() && !empty(shell_exec('which uapi'));
        }

        /**
         * Install SSL certs via uapi command line
         *
         * @param $domain
         * @param $keysPath
         *
         * @return string|null
         * @since  1.7
         */
        public static function installSslViaUApiCommandline($domain, $keysPath)
        {
            // Define the SSL certificate and key files.
            $cert = urlencode(str_replace('\r\n', '\n', file_get_contents($keysPath . 'certificate.crt')));
            $key = urlencode(str_replace('\r\n', '\n', file_get_contents($keysPath . 'private.pem')));
            $caBundle = urlencode(str_replace('\r\n', '\n', file_get_contents($keysPath . 'cabundle.crt')));

            return shell_exec("uapi SSL install_ssl domain=$domain cert=$cert key=$key cabundle=$caBundle");
        }


        /**
         * Check domain with let's debug
         *
         * @param $baseDomain
         * @param string $method
         *
         * @since 2.0.4
         */
        public static function debugLetsEncrypt($baseDomain, $method = LEOrder::CHALLENGE_TYPE_HTTP)
        {
            if (SSL_ZEN_PLUGIN_ALLOW_DEV) {
                return false;
            }

            if (SSL_ZEN_DISABLE_LETS_DEBUG) {
                return false;
            }

            $apiResponse = wp_remote_post(
                'https://letsdebug.net', [
                'timeout' => '15',
                'sslverify' => false,
                'headers' => array(
                    'content-type' => 'application/json'
                ),
                'body' => json_encode(
                    [
                    'method' => $method,
                    'domain' => $baseDomain
                    ]
                )
                ]
            );

            if (!empty($apiResponse) && !is_wp_error($apiResponse)) {
                $bodyObj = !empty($apiResponse['body']) ? json_decode($apiResponse['body']) : null;
                $id = !empty($bodyObj) && !empty($bodyObj->ID) ? (int)$bodyObj->ID : null;
                // Fetch the id and
                if (!empty($id)) {

                    // Sleep in order to pass the processing status
                    sleep(10);

                    // Prepare get call
                    $apiResponse = wp_remote_get(
                        'https://letsdebug.net/' . $baseDomain . '/' . $id, [
                        'timeout' => '15',
                        'sslverify' => false,
                        'headers' => array(
                            'Accept' => 'application/json'
                        )
                        ]
                    );

                    if (!empty($apiResponse) && !is_wp_error($apiResponse)) {
                        $bodyObj = !empty($apiResponse['body']) ? json_decode($apiResponse['body']) : null;
                        if (!empty($bodyObj->result) && !empty($bodyObj->result->problems)) {
                            $problems = $bodyObj->result->problems;
                            $error = false;
                            foreach ($problems as $problem) {
                                if (in_array(strtolower($problem->severity), ["error", "fatal"])) {
                                    // So the domain has problem, then redirect with error
                                    $error = true;
                                    break;
                                }
                            }

                            if ($error) {
                                $currentSettingTab = get_option('ssl_zen_settings_stage', '');
                                if ($currentSettingTab == '') {
                                    $currentSettingTab = 'step1';
                                }

                                wp_redirect(admin_url('admin.php?page=ssl_zen&tab=' . $currentSettingTab . '&info=lets_encrypt_error_' . strtolower($problem->name)));
                                exit;
                            }
                        }
                    }
                }
            }
        }
    }
}
