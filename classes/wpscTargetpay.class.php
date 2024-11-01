<?php
class wpscTargetpay extends wpsc_merchant {

    /**
     * Set up the fields that will be available as options for our WP E-Commerce
     * payment gateway settings section
     *
     * @var array
     *   Settings that can be user controlled in wp e commerce store settings.
     */
    private $fields = array(
        'layoutcode' => array(
            'label' => 'Layout code',
            'default' => '',
            'subtext' => 'Your unique TargetPay layout code like "00000"',
        ),
        'return_url' => array(
            'label' => 'Return URL',
            'default' => '/',
            'subtext' => 'Relative path like "/thank-you"',
        ),
        'popup_background_colour' => array(
            'label' => 'Popup background colour',
            'default' => '#FFF',
            'subtext' => 'Hex colour code like "#FFFFFF" or RGB/RGBA colour like "RGBA(255,255,255,0.8)"',
        ),
        'debug' => array(
            'label' => 'Debug mode',
            'default' => '0',
            'subtext' => 'When preparing a transation, an unsuccessful response will be sent by email. When a callback is trigger, an email will be sent to the debug email.',
            'type' => 'true_false',
        ),
        'debug_email' => array(
            'label' => 'Debug email',
            'default' => '',
            'subtext' => 'Email to receive callback info',
        ),
        'admin_only' => array(
            'label' => 'Admin only',
            'default' => '0',
            'subtext' => 'Only show the payment option when logged in as administrator (useful for testing)',
            'type' => 'true_false',
        ),
        'test_mode' => array(
            'label' => 'Test mode',
            'default' => '0',
            'subtext' => 'Should the transactions be done in test mode? IMPORTANT: Transactions will be marked as successful but you will not actually receive any funds!',
            'type' => 'true_false',
        ),
    );

    /**
     * Constructor function.
     *
     * Set up wp filters and actions as well as make the fields transalatable
     */
    public function __construct() {

        //setup of our settings fields
        $this->makeFieldsTranslatable();

        //add filters
        add_filter( 'wpsc_merchants_modules', array( $this, 'wpscTargetpayRegisterGateway' ) );

        //add actions
        add_action( 'init', array( $this, 'wpscTargetpayFormSubmit' ) );
        add_action( 'init', array( $this, 'wpscTargetpayBankSubmit' ) );
        add_action( 'init', array( $this, 'wpscTargetpayCallback' ) );

        //setup css
        wp_register_style( 'magnificPopup', plugins_url( '../assets/css/magnific-popup.min.css', __FILE__ ) );
        wp_register_style( 'wpECommerceTargetpay', plugins_url( '../assets/css/wp-e-commerce-targetpay.css', __FILE__ ) );

        //setup js
        wp_register_script( 'magnificPopupJs', plugins_url( '../assets/js/magnific-popup.min.js', __FILE__ ) );

    }

    /**
     * Wrap the field labels and subtexts in __() to make them translateable.
     */
    private function makeFieldsTranslatable() {

        foreach ( $this->fields as $key => $settings ) {
            $this->fields[$key]['label'] = __( $settings['label'], 'wpec-targetpay' );
            $this->fields[$key]['subtext'] = __( $settings['subtext'], 'wpec-targetpay' );
        }

    }

    /**
     * Callback for wpsc_merchants_modules filter to add our new gateway
     *
     * @param array   $gateways
     *   The existing gateways array passed by wp e commerce that we want to add to
     * @return array
     *   The gateways with our gateway added on.
     */
    public function wpscTargetpayRegisterGateway( $gateways ) {

        $num = count( $gateways )+2;
        $options = get_option( '_wpsc_targetpay_settings' );

        if ( is_admin() || !$options['admin_only'] || current_user_can( 'administrator' ) ) {
            $gateways[$num] = array(
                'name' => 'WP E-Commerce Targetpay iDeal',
                'display_name' => 'iDeal',
                'api_version' => 2.0,
                'class_name' => 'wpscTargetpay',
                'has_recurring_billing' => false,
                'wp_admin_cannot_cancel' => false,
                'requirements' => array(
                    'php_version' => 5.0,
                    'extra_modules' => array( 'curl' )
                ),
                'internalname' => 'wpscTargetpay',
                'form' => 'wpscTargetpayFormWrapper',
                'payment_type' => "wpsctargetpay",
                'supported_currencies' => array(
                    'currency_list' => array( 'EUR' ),
                ),
            );
        }
        return $gateways;

    }

    /**
     * WPSC submit handler
     *
     * The submit function called by wp e commerce where we
     * want to trigger loading up the bank selection in a magnific
     * popup window.
     */
    public function submit() {

        add_action( 'wp_head', array( $this, 'loadMagnificPopupAssets' ) );
        add_action( 'wp_footer', array( $this, 'renderBankSelectorInMagnificPopup' ) );

    }

    /**
     * Load the magnific popup assets including css and js.
     */
    public function loadMagnificPopupAssets() {

        wp_enqueue_style( 'magnificPopup' );
        wp_enqueue_style( 'wpECommerceTargetpay' );
        wp_enqueue_script( 'magnificPopupJs' );

    }

    /**
     * Render the magnific popup bank selector.
     *
     * Render the popup with the bank selector from TargetPay
     * so the visitor can choose their bank which we need in order to
     * initiate the transaction with TargetPay.
     */
    public function renderBankSelectorInMagnificPopup() {

        if ( wp_script_is( 'magnificPopupJs', 'enqueued' ) ) {

            $options = get_option( '_wpsc_targetpay_settings' );
            $submit_label = __( 'Pay now', 'wpec-targetpay' );
            ?>
            <a href="#wp-e-commerce-targetpay-choose-bank" id="magnific_choose_bank_form_open" class="wp_e_commerce_targetpay_open"><?php echo __( 'Choose your bank', 'wpec-targetpay' ) ?></a>
            <form name="wp-e-commerce-targetpay-choose-bank" class="mfp-hide white-popup-block wp_e_commerce_targetpay" id="wp-e-commerce-targetpay-choose-bank" method="post" action="" style="background-color:<?php echo $options['popup_background_colour'] ?>;">
                <h1><?php echo __( 'Choose your bank', 'wpec-targetpay' ) ?></h1>
                <input type="hidden" name="wpsc_targetpay_setup_ideal_transaction" id="wpsc_targetpay_setup_ideal_transaction" value="1" />
                <div class="wp_e_commerce_targetpay_row">
                    <select name="bank" class="wp_e_commerce_targetpay_select">
                        <script src="http://www.targetpay.com/ideal/issuers-<?php echo __( 'en', 'wpec-targetpay' ) ?>.js"></script>
                    </select>
                </div>
                <div class="wp_e_commerce_targetpay_row">
                    <input class="wp_e_commerce_targetpay_choose_bank_submit" id="choose_bank_submit" type="submit" value="<?php echo htmlentities( $submit_label ) ?>" />
                </div>
            </form>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('#magnific_choose_bank_form_open').hide();
                    $('#magnific_choose_bank_form_open').magnificPopup({
                        type: 'inline',
                        preloader: false,
                        focus: '#bank',
                        closeOnBgClick: false,
                        closeBtnInside: false,
                        showCloseBtn: false
                    });
                    $('#magnific_choose_bank_form_open').click();
                });
            </script>
            <?php

        }

    }

    /**
     * Prepare the parameters TargetPay expects for initiating the transaction.
     *
     * @param array   $options
     *   The settings options defined in the admin area.
     * @return array
     *   The data to be sent to TargetPay.
     */
    private function wpscTargetpayPrepareTransactionStartData( $options ) {

        global $wpsc_cart;
        $data = array();

        //general data
        $data['rtlo'] = $options['layoutcode'];
        $data['language'] = __( 'en', 'wpec-targetpay' );
        $data['currency'] = 'EUR';
        $data['description'] = get_bloginfo( 'name' ).' - '.wpsc_get_current_customer_id();
        $data['bank'] = $_REQUEST['bank'];
        $data['reporturl'] = get_option( 'siteurl' ).'/?wp_e_commerce_targetpay_callback=true';
        $data['cinfo_in_callback'] = 1;

        //amount in cents
        $data['amount'] = ( $wpsc_cart->calculate_total_price() * 100 );

        //return url
        $session_id = wpsc_get_customer_meta( 'checkout_session_id' );
        $separator = ( get_option( 'permalink_structure' ) != '' ? '?' : '&' );
        $transaction_url = get_option( 'transact_url' );
        $data['returnurl'] = $transaction_url.$separator.'sessionid='.$session_id.'&gateway=wp_e_commerce_targetpay';

        //test mode
        if ( $options['test_mode'] ) {
            $data['test'] = 1;
        } else {
            $data['test'] = 0;
        }

        return $data;

    }

    /**
     * Attempt to initiate the transaction.
     *
     * The TargetPay transaction is successfully initiated if the response begins
     * with 00000 and contains the URL and Transaction ID.
     *
     * @param array   $data
     *   The data to be sent to TargetPay.
     * @return string
     *   The response.
     */
    private function initiateTransaction( $data ) {

        $remote_url = 'https://www.targetpay.com/ideal/start?'.http_build_query( $data );
        return wp_remote_retrieve_body( wp_remote_get( $remote_url ) );

    }

    /**
     * Determine if the response is valid.
     *
     * The TargetPay transaction is successfully initiated if the response begins
     * with 00000 and contains the URL and Transaction ID.
     *
     * @param string  $response
     *   The response from initiating the transaction.
     * @return boolean
     *   Whether the response is valid.
     */
    private function isValidTransactionResponse( $response ) {

        $parts = explode( '|', $response );
        $response_info = explode( ' ', $parts[0] );
        if ( $response_info[0] == '000000' && strlen( $parts[1] )>0 && strlen( $response_info[1] )>0 ) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Get the URL of the prepared transaction.
     *
     * The response from TargetPay has this format
     * 000000 30626804185492|https://idealet.abnamro.nl/nl/ideal/identification.do?randomizedstring=1684152718&trxid=30626804185492
     * and we want the URL from that.
     *
     * @param string  $response
     *   The response from TargetPay.
     * @return string
     *   The URL we need to redirect the visitor to.
     */
    private function getTransactionUrl( $response ) {

        $parts = explode( '|', $response );
        return $parts[1];

    }

    /**
     * Get the Transaction ID of the prepared transaction.
     *
     * The response from TargetPay has this format
     * 000000 30626804185492|https://idealet.abnamro.nl/nl/ideal/identification.do?randomizedstring=1684152718&trxid=30626804185492
     * and we want the trxid from that.
     *
     * @param string  $response
     *   The response from TargetPay.
     * @return string
     *   The transaction id.
     */
    private function getTransactionId( $response ) {

        $parts = explode( '|', $response );
        $query_string = end( explode( '?', $parts[1] ) );
        parse_str( $query_string, $query_string_parts );
        return $query_string_parts['trxid'];

    }

    /**
     * Get the log ID by session id.
     *
     * @param string  $session_id
     *   The WPSC session id.
     * @return string
     *   The WPSC log id.
     */
    private function getLogIdBySessionId( $session_id ) {

        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( "
            SELECT `id`
            FROM `".WPSC_TABLE_PURCHASE_LOGS."`
            WHERE `sessionid` IN (%s) LIMIT 1
        ", $session_id ) );

    }

    /**
     * Record the transaction in the purchase logs.
     *
     * @param string  $transaction_id
     *   The TargetPay transaction id.
     * @param string  $log_id
     *   The WPSC log id.
     */
    private function recordTransactionStartInPurchaseLogs( $transaction_id, $log_id ) {

        global $wpdb;
        $wpdb->update(
            WPSC_TABLE_PURCHASE_LOGS,
            array(
                'transactid' => $transaction_id,
                'processed' => WPSC_Purchase_Log::ORDER_RECEIVED,
            ),
            array( 'id' => $log_id ),
            array(
                '%s',
                '%d',
            ),
            array( '%d' )
        );

    }

    /**
     * Handle the targetpay bank submission.
     *
     * Here we initiate the transaction and, if all goes well, use wp_redirect
     * to send the visitor off to TargetPay otherwise output some errors
     * depending on the store settings.
     */
    public function wpscTargetpayBankSubmit() {

        if ( isset( $_REQUEST['wpsc_targetpay_setup_ideal_transaction'] ) && $_REQUEST['wpsc_targetpay_setup_ideal_transaction'] ) {

            $options = get_option( '_wpsc_targetpay_settings' );
            $data = $this->wpscTargetpayPrepareTransactionStartData( $options );
            $response = $this->initiateTransaction( $data );

            if ( $this->isValidTransactionResponse( $response ) ) {

                $transaction_url = $this->getTransactionUrl( $response );
                $transaction_id = $this->getTransactionId( $response );
                $session_id = wpsc_get_customer_meta( 'checkout_session_id' );
                $log_id = $this->getLogIdBySessionId( $session_id );
                if ( $log_id ) {
                    $this->recordTransactionStartInPurchaseLogs( $transaction_id, $log_id );
                    wp_redirect( $transaction_url );
                    exit();
                } else {
                    trigger_error( 'Unable to finalise transaction preparation', E_ERROR );
                }

            } else {

                $messages = array(
                    'data' => $data,
                    'response' => $response,
                    'log_id' => $log_id,
                    'transaction_id' => $transaction_id,
                    'session_id' => $session_id,
                );

                wpscTargetpayDebugger::sendDebugEmail(
                    $options,
                    'WP E-Commerce Targetpay - Error initiating transaction',
                    $messages
                );

                $message = 'There was a problem initiating the transaction. Please try again or try an alternative payment method';
                wpscTargetpayDebugger::renderDebugMessage( $message );

            }

        }

    }

    /**
     * Prepare the parameters TargetPay expects for checking the payment.
     *
     * @param array   $options
     *   The settings options defined in the admin area.
     * @return array
     *   The data to be sent to TargetPay.
     */
    private function wpscTargetpayPrepareCallbackData( $options ) {

        global $wpsc_cart;
        $data = array();

        //general data
        $data['rtlo'] = $options['layoutcode'];
        $data['once'] = 0;
        $data['trxid'] = $_REQUEST['trxid'];

        //test mode
        if ( $options['test_mode'] ) {
            $data['test'] = 1;
        } else {
            $data['test'] = 0;
        }

        return $data;

    }

    /**
     * Attempt to initiate the callback.
     *
     * The TargetPay transaction response telling us how the transaction
     * went is in the format 000000 OK|name|Bankaccount|.
     *
     * @param array   $data
     *   The data to be sent to TargetPay.
     * @return string
     *   The response.
     */
    private function initiateCallback( $data ) {

        $remote_url = 'https://www.targetpay.com/ideal/check?'.http_build_query( $data );
        return wp_remote_retrieve_body( wp_remote_get( $remote_url ) );

    }

    /**
     * Get the log ID by transaction id.
     *
     * @param string  $transaction_id
     *   The TargetPay transaction id.
     * @return string
     *   The WPSC log id.
     */
    private function getLogIdByTransactionId( $transaction_id ) {

        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( "
            SELECT `id`
            FROM `".WPSC_TABLE_PURCHASE_LOGS."`
            WHERE `transactid` = %s
            LIMIT 1
        ", $transaction_id ) );

    }

    /**
     * Get the transaction status.
     *
     * Determine what the transaction status for WPSC should be 
     * based on the TargetPay transaction status and return that.
     * 
     * @param  string $response
     *   The response from the TargetPay callback.
     * @return string/boolean
     *   The WPSC Purchase Log status or false.
     */
    private function getTransactionStatus( $response ) {

        if ( $response ) {

            $response_info = explode( ' ', $response );
            $consumer_info = ( isset( $response_info[1] ) ? explode( '|', $response_info[1] ) : array() );
            if ( $response_info[0] == '000000' && $consumer_info[0] == 'OK' ) {
                return WPSC_Purchase_Log::ACCEPTED_PAYMENT; //success
            } else if ( $response_info[0] == 'TP0010' ) {
                return WPSC_Purchase_Log::INCOMPLETE_SALE; //incomplete
            } else if ( $response_info[0] == 'TP0011' ) {
                return WPSC_Purchase_Log::PAYMENT_DECLINED; //cancelled
            }

        }

        return false;

    }

    /**
     * Handle the callback from TargetPay.
     *
     * The callback from TargetPay is letting us know the status of the
     * transaction. Here we receive the status and record it in the appropriate
     * places.
     */
    public function wpscTargetpayCallback() {

        if ( isset( $_REQUEST['gateway'] ) && $_REQUEST['gateway'] == 'wp_e_commerce_targetpay' && isset( $_REQUEST['trxid'] ) ) {

            $options = get_option( '_wpsc_targetpay_settings' );
            $data = $this->wpscTargetpayPrepareCallbackData( $options );
            $response = $this->initiateCallback( $data );
            $log_id = $this->getLogIdByTransactionId( $_REQUEST['trxid'] );
            $status = $this->getTransactionStatus( $response );

            if ( $status !== false && $log_id ) {

                //update purchase logs and trigger emails
                $sessionid = $_REQUEST['sessionid'];
                $data = array(
                    'processed'  => $status,
                    'transactid' => $data['trxid'],
                    'date'       => time(),
                );
                wpsc_update_purchase_log_details( $sessionid, $data, 'sessionid' );
                transaction_results( $sessionid, false, $transaction_id );

            }

            if ( $status === false ) {

                $messages = array(
                    'check_url' => $check_url,
                    'request' => $_REQUEST,
                    'response' => $response,
                    'log_id' => $log_id,
                );

                wpscTargetpayDebugger::sendDebugEmail(
                    $options,
                    'WP E-Commerce Targetpay - Error checking transaction',
                    $messages
                );

            }

        }

    }

    /**
     * Callback from wp e commerce for the checkout payment type settings.
     * This is where the admin sets the TargetPay layout code, etc
     */
    public function wpscTargetpayForm() {

        $return = '';
        $options = get_option( '_wpsc_targetpay_settings' );

        foreach ( $this->fields as $key => $settings ) {

            //label
            $return .= '<tr>';
            $return .= '<td style="padding-top: 24px; padding-right: 16px;"><label for="'.$key.'">'.$settings['label'].'</label></td>';

            //input
            $return .= '<td style="padding-top: 24px">';
            switch ( $settings['type'] ) {
                case 'true_false';
                    $return .= '<input type="hidden" name="'.$key.'" value="0" />';
                    $return .= '<input type="checkbox" name="'.$key.'" id="'.$key.'" value="1" '.( $options && $options[$key] ? 'checked="checked"' : '' ).' />';
                    break;
                default:
                    $return .= '<input name="'.$key.'" id="'.$key.'" value="'.( $options ? $options[$key] : $settings['default'] ).'" />';
            }
            $return .= '<br /><small>'.$settings['subtext'].'</small></td>';

            //close row
            $return .= '</tr>';

        }

        return $return;

    }

    /**
     * Save the checkout payment type settings
     */
    public function wpscTargetpayFormSubmit() {

        if ( $_POST ) {
            $options = array();

            foreach ( $this->fields as $key => $settings ) {
                if ( isset( $_POST[$key] ) ) {
                    $options[$key] = $_POST[$key];
                }
            }

            if ( $options ) {
                update_option( '_wpsc_targetpay_settings', $options );
            }

        }

    }

}
