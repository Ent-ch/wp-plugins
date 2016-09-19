<?php
/*
	Plugin Name: Millikart Payment Gateway
	Description: Millikart Payment Gateway
	Version: 0.4
	License:           GPL-2.0+
 	GitHub Plugin URI: https://github.com/tubiz/interswitch-webpay-woocommerce-payment-gateway
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

add_action('plugins_loaded', 'tbz_wc_interswitch_webpay_init', 0);

function tbz_wc_interswitch_webpay_init() {

	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

	/**
 	 * Gateway class
 	 */
	class WC_Tbz_Webpay_Gateway extends WC_Payment_Gateway {

		public function __construct(){
			global $woocommerce;

			$this->id 					= 'tbz_webpay_gateway';
//    		$this->icon 				= apply_filters('woocommerce_webpay_icon', plugins_url( 'assets/images/interswitch.png' , __FILE__ ) );
			$this->has_fields 			= false;
        	$this->testurl 				= 'http://test.millikart.az:8513/gateway/payment/register';
			$this->liveurl 				= 'http://test.millikart.az:8513/gateway/payment/register';
			$this->redirect_url        	= WC()->api_request_url( 'WC_Tbz_Webpay_Gateway' );
        	$this->method_title     	= 'Millikart';
        	$this->method_description  	= 'Accepts Mastercard, Verve Card and Visa Card';


			// Load the form fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Define user set variables
			$this->title 					= $this->get_option( 'title' );
			$this->description 				= $this->get_option( 'description' );
			$this->product_id				= $this->get_option( 'product_id' );
			$this->pay_item_id				= $this->get_option( 'pay_item_id' );
			$this->mac_key					= $this->get_option( 'mac_key' );
			$this->testmode					= $this->get_option( 'testmode' );

			//Actions
			add_action('woocommerce_receipt_tbz_webpay_gateway', array($this, 'receipt_page'));
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// Payment listener/API hook
			add_action( 'woocommerce_api_wc_tbz_webpay_gateway', array( $this, 'check_webpay_response' ) );

			//Display Transaction Reference on checkout
			add_action( 'before_woocommerce_pay', array( $this, 'display_transaction_id' ) );
		}
        /**
         * Admin Panel Options
         **/
        public function admin_options(){
            echo '<h3>Millikart</h3>';

            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }
	    /**
	     * Initialise Gateway Settings Form Fields
	    **/
		function init_form_fields(){
			$this->form_fields = array(
			'enabled' => array(
							'title' 			=> 'Enable/Disable',
							'type' 				=> 'checkbox',
							'label' 			=> 'Enable Millikart Payment Gateway',
							'description' 		=> 'Enable or disable the gateway.',
                    		'desc_tip'      	=> true,
							'default' 			=> 'yes'
						),
				 'title' => array(
								'title' 		=> 'Title',
								'type' 			=> 'text',
								'description' 	=> 'This controls the title which the user sees during checkout.',
                    			'desc_tip'      => false,
								'default' 		=> 'Millikart'
							),
				'description' => array(
								'title' 		=> 'Description',
								'type' 			=> 'textarea',
								'description' 	=> 'This controls the description which the user sees during checkout.',
								'default' 		=> 'Accepts Mastercard and Visa Card'
							),
				'pay_item_id' => array(
								'title' 		=> 'Merchant ID',
								'type' 			=> 'text',
								'description' 	=> 'Millikart Merchant ID' ,
								'default' 		=> '',
                    			'desc_tip'      => false
							),
				'mac_key' => array(
								'title' 		=> 'Secret key',
								'type' 			=> 'text',
								'description' 	=> 'Given to Merchant by Millikart' ,
								'default' 		=> '',
                    			'desc_tip'      => false
							),
			);
		}

		/**
		 * Get Webpay Args for passing to Interswitch
		**/
		function get_webpay_args( $order ) {
			global $woocommerce;

			$order_total	= $order->get_total();
			$order_total    = $order_total * 100;
            
            $reference = $order->id.'_'.date("ymds");
            $redirect_url = ($this->redirect_page_id=="" || $this->redirect_page_id==0)?get_site_url() . "/":get_permalink($this->redirect_page_id);
            $productinfo = "Order {$order->id}";
            $currency = '944';
            $mid = $this->pay_item_id;
            $amount = intval($order->order_total * 100);
            $language = 'en';
            $key = $this->mac_key;

            $signature = strtoupper(md5(strlen($mid).$mid.strlen($amount).$amount.strlen($currency).$currency.strlen($productinfo).$productinfo
                             .strlen($reference).$reference.strlen($language).$language.$key));

            $webpay_args = array(
              'mid' => $mid,
              'reference' => $reference,
              'amount' => $amount,
              'description' => $productinfo,
              'currency' => $currency,
              'language' => $language,
              'signature' => $signature,
              'redirect' => 1,
              );

			$webpay_args = apply_filters( 'woocommerce_webpay_args', $webpay_args );

			return $webpay_args;
		}

	    /**
		 * Generate the Webpay Payment button link
	    **/
	    function generate_webpay_form( $order_id ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );

			if ( 'yes' == $this->testmode ) {
        		$webpay_adr = $this->testurl;
			} else {
				$webpay_adr = $this->liveurl;
			}

			$webpay_args = $this->get_webpay_args( $order );

			// before payment hook
            do_action('tbz_wc_webpay_before_payment', $webpay_args);

			$webpay_args_array = array();

			foreach ($webpay_args as $key => $value) {
				$webpay_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
			}

			wc_enqueue_js( '
				$.blockUI({
						message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Interswitch to make payment.', 'woocommerce' ) ) . '",
						baseZ: 99999,
						overlayCSS:
						{
							background: "#fff",
							opacity: 0.6
						},
						css: {
							padding:        "20px",
							zindex:         "9999999",
							textAlign:      "center",
							color:          "#555",
							border:         "3px solid #aaa",
							backgroundColor:"#fff",
							cursor:         "wait",
							lineHeight:		"24px",
						}
					});
				jQuery("#submit_webpay_payment_form").click();
			' );

			return '<form action="' . esc_url( $webpay_adr ) . '" method="get" id="webpay_payment_form" target="_top">
					' . implode( '', $webpay_args_array ) . '
					<!-- Button Fallback -->
					<div class="payment_buttons">
						<input type="submit" class="button alt" id="submit_webpay_payment_form" value="' . __( 'Pay via Interswitch Webpay', 'woocommerce' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce' ) . '</a>
					</div>
					<script type="text/javascript">
						jQuery(".payment_buttons").hide();
					</script>
				</form>';
		}

	    /**
	     * Process the payment and return the result
	    **/
		function process_payment( $order_id ) {

			$order 			= new WC_Order( $order_id );

	        return array(
	        	'result' => 'success',
				'redirect'	=> $order->get_checkout_payment_url( true )
	        );
		}

	    /**
	     * Output for the order received page.
	    **/
		function receipt_page( $order ) {
			echo '<p>' . __( 'Thank you - your order is now pending payment. You should be automatically redirected to Interswitch to make payment.', 'woocommerce' ) . '</p>';
			echo $this->generate_webpay_form( $order );
		}


		/**
		 * Verify a successful Payment!
		**/
		function check_webpay_response( $posted ){
			global $woocommerce;

			if( isset( $_GET['reference'] ) ){

				$txnref 		= $_GET['reference'];
				$order_details 	= explode('_', $txnref);
				$txn_ref 		= $order_details[0];
				$order_id 		= $order_details[1];

				$order_id 		= (int) $order_id;

		        $order 			= new WC_Order($order_id);
		        $order_total	= $order->get_total();

		        $total          = $order_total * 100;

//		        $response       = $this->tbz_webpay_transaction_details( $txnref, $total);

//				$response_code 	= $response['ResponseCode'];
//				$amount_paid    = $response['Amount'] / 100;
//				$response_desc  = $response['ResponseDescription'];

                $response_code 	= '00';
				$amount_paid    = 15;
				$response_desc  = 'hz hz';

				// after payment hook
                do_action('tbz_wc_webpay_after_payment', $_POST, $response );

				//process a successful transaction
				if( '00' == $response_code){

					// check if the amount paid is equal to the order amount.
					if($order_total != $amount_paid)
					{

		                //Update the order status
						$order->update_status('on-hold', '');

						//Error Note
						$message = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.<br />Transaction Reference: '.$txnref;
						$message_type = 'notice';

						//Add Customer Order Note
	                    $order->add_order_note( $message, 1 );

	                    //Add Admin Order Note
	                    $order->add_order_note('Look into this order. <br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was &#8358; '.$amount_paid.' while the total order amount is &#8358; '.$order_total.'<br />Transaction Reference: '.$txnref);

						// Reduce stock levels
						$order->reduce_order_stock();

						// Empty cart
						$woocommerce->cart->empty_cart();
					}
					else
					{

		                if($order->status == 'processing'){
		                    $order->add_order_note('Payment Via Interswitch Webpay<br />Transaction Reference: '.$txnref);

		                    //Add customer order note
		 					$order->add_order_note('Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.<br /Transaction Reference: '.$txnref, 1);

							// Reduce stock levels
							$order->reduce_order_stock();

							// Empty cart
							WC()->cart->empty_cart();

							$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.<br />Transaction Reference: '.$txnref;
							$message_type = 'success';
		                }
		                else{

		                	if( $order->has_downloadable_item() ){

		                		//Update order status
								$order->update_status( 'completed', 'Payment received, your order is now complete.' );

			                    //Add admin order note
			                    $order->add_order_note('Payment Via Interswitch Webpay<br />Transaction Reference: '.$txnref);

			                    //Add customer order note
			 					$order->add_order_note('Payment Received.<br />Your order is now complete.<br />Transaction Reference: '.$txnref, 1);

								$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is now complete.<br />Transaction Reference: '.$txnref;
								$message_type = 'success';

		                	}
		                	else{

		                		//Update order status
								$order->update_status( 'processing', 'Payment received, your order is currently being processed.' );

								//Add admin order noote
			                    $order->add_order_note('Payment Via Interswitch Webpay<br />Transaction Reference: '.$txnref);

			                    //Add customer order note
			 					$order->add_order_note('Payment Received.<br />Your order is currently being processed.<br />We will be shipping your order to you soon.<br />Transaction Reference: '.$txnref, 1);

								$message = 'Thank you for shopping with us.<br />Your transaction was successful, payment was received.<br />Your order is currently being processed.<br />Transaction Reference: '.$txnref;
								$message_type = 'success';
		                	}

							// Reduce stock levels
							$order->reduce_order_stock();

							// Empty cart
							WC()->cart->empty_cart();
		                }
	                }
				}
				else{
					//process a failed transaction
	            	$message = 	'Thank you for shopping with us. <br />However, the transaction wasn\'t successful, payment wasn\'t received.<br />Reason: '. $response_desc.'<br />Transaction Reference: '.$txnref;
					$message_type = 'error';

					//Add Customer Order Note
                   	$order->add_order_note( $message, 1 );

                    //Add Admin Order Note
                  	$order->add_order_note( $message );

	                //Update the order status
					$order->update_status('failed', '');
				}
			}
			else{

            	$message = 	'Thank you for shopping with us. <br />However, the transaction wasn\'t successful, payment wasn\'t received.';
				$message_type = 'error';

			}

            $notification_message = array(
            	'message'	=> $message,
            	'message_type' => $message_type
            );

			if ( version_compare( WOOCOMMERCE_VERSION, "2.2" ) >= 0 ) {
				add_post_meta( $order_id, '_transaction_id', $txnref, true );
			}

			update_post_meta( $order_id, '_tbz_interswitch_wc_message', $notification_message );

            $redirect_url = $this->get_return_url( $order );

            wp_redirect( $redirect_url );
            exit;
		}

		/**
	 	* Query a transaction details
	 	**/
		function tbz_webpay_transaction_details( $txnref, $total ){

			$product_id 	= $this->product_id;
			$mac_key        = $this->mac_key;

			if ( 'yes' == $this->testmode ) {
        		$query_url = 'https://stageserv.interswitchng.com/test_paydirect/api/v1/gettransaction.json';
			} else {
				$query_url = 'https://webpay.interswitchng.com/paydirect/api/v1/gettransaction.json';
			}

			$url 	= "$query_url?productid=$product_id&transactionreference=$txnref&amount=$total";

			$hash 	= $product_id.$txnref.$mac_key;
			$hash 	= hash("sha512", $hash);

			$headers = array(
				'Hash' => $hash
			);

			$args = array(
				'timeout'	=> 30,
				'headers' 	=> $headers
			);

			$response 		= wp_remote_get( $url, $args );
			$response  		= json_decode($response['body'], true);

			return $response;
		}

	    /**
	     * Display the Transaction Reference on the payment confirmation page.
	    **/
		function display_transaction_id(){
			$order_id = absint( get_query_var( 'order-pay' ) );
			$order = new WC_Order( $order_id );

			$payment_method =  $order->payment_method;

			if( !isset( $_GET['pay_for_order'] ) && ( 'tbz_webpay_gateway' == $payment_method ) ){
				$txn_ref =$order_id = WC()->session->get( 'tbz_wc_webpay_txn_id' );
				WC()->session->__unset( 'tbz_wc_webpay_txn_id' );
				echo '<h4>Transaction Reference: '. $txn_ref .'</h4>';
			}
		}
	}


	function tbz_wc_interswitch_message(){

		$order_id 		= absint( get_query_var( 'order-received' ) );
		$order 			= new WC_Order( $order_id );
		$payment_method = $order->payment_method;

		if( is_order_received_page() &&  ( 'tbz_webpay_gateway' == $payment_method ) ){

			$notification 		= get_post_meta( $order_id, '_tbz_interswitch_wc_message', true );

			$message 			= isset( $notification['message'] ) ? $notification['message'] : '';
			$message_type 		= isset( $notification['message_type'] ) ? $notification['message_type'] : '';

			delete_post_meta( $order_id, '_tbz_interswitch_wc_message' );

			if( ! empty( $message) ){
				wc_add_notice( $message, $message_type );
			}
		}
	}
	add_action('wp', 'tbz_wc_interswitch_message', 0);


	/**
 	* Add Webpay Gateway to WC
 	**/
	function wc_add_webay_gateway($methods) {
		$methods[] = 'WC_Tbz_Webpay_Gateway';
		return $methods;
	}

	add_filter('woocommerce_payment_gateways', 'wc_add_webay_gateway' );
}
