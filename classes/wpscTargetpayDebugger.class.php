<?php
class wpscTargetpayDebugger {

    /**
     * Send debug messages to the debug email address
     *
     * @param array   $options
     *   The settings options defined in the admin area.
     * @param string  $subject
     *   The subject of the email.
     * @param array   $messages
     *   The values keyed by titles.
     * @return boolean
     *   Whether the message was sent or not.
     */
    public static function sendDebugEmail( $options, $subject, $messages ) {

        if ( is_array( $messages ) && $options['debug'] && $options['debug_email'] ) {

            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            $to = trim( $options['debug_email'] );

            ob_start();

            foreach ( $messages as $label => $value ) {
                print '<h2>'.$label.'</h2>';
                print '<pre>';
                print_r( $value );
                print '</pre>';
            }

            $message .= ob_get_clean();

            if ( filter_var( $to, FILTER_VALIDATE_EMAIL ) !== false ) {
                return wp_mail( $to, $subject, $message, $headers );
            }

        }

        return false;

    }

    /**
     * Output a message to the screen
     *
     * @param string  $message 
     *   The message to be displayed.
     */
    public static function renderDebugMessage( $message ) {

        print '<p style="padding:8px 16px; background-color: #f2ff85; color: #000000;">';
        print __( $message, 'wpec-targetpay' );
        print '</p>';

    }

}
