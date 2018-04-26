<?php
class ControllerExtensionPaymentAw extends Controller {

	public function index() {	
    	$data['button_confirm'] = $this->language->get('button_confirm');
		$this->load->model('checkout/order');
		$this->language->load('extension/payment/aw');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$data['merchant'] = $this->config->get('aw_merchant');
		
		 /////////////////////////////////////Start AW Vital  Information /////////////////////////////////
		
		
		    $data['action'] = 'https://quickpay.alliedwallet.com';
            //$data['action'] = 'http://beevip.com/post.php';


				$data['CurrencyID'] = $order_info['currency_code'];

			
		$txnid        = 	$this->session->data['order_id'];

        
        $data['MerchantID'] = $this->config->get('aw_merchant_id');
        $data['SiteID'] = $this->config->get('aw_site_id');
        $data['QuickPayUserToken'] = $this->config->get('aw_auth_token');
        $data['Descriptor'] = $this->config->get('aw_descriptor');
        
        
		
		$data['AmountTotal'] = (int)$order_info['total'];
		$data['FirstName'] = $order_info['payment_firstname'];
		$data['LastName'] = $order_info['payment_lastname'];
		$data['Zip'] = $order_info['payment_postcode'];
		$data['Email'] = $order_info['email'];
		$data['Phone'] = $order_info['telephone'];
		$data['Address1'] = $order_info['payment_address_1'];
        $data['Address2'] = $order_info['payment_address_2'];
        $data['State'] = $order_info['payment_zone'];
        $data['City']=$order_info['payment_city'];
        $data['Country']=$order_info['payment_iso_code_2'];
        $data['Zip']=$order_info['payment_postcode'];
        $data['AmountShipping']=$order_info['shipping_cost'];
        $data['MerchantReference'] = $this->session->data['order_id'];
        
		$data['ReturnURL'] = $this->url->link('checkout/success');
		$data['ConfirmURL'] = $this->url->link('extension/payment/aw/callback');
        $data['DeclinedURL'] = $this->url->link('extension/payment/aw/callback');
        
        
        $data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						
						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$data['products'][]         = array(
					'ItemName'             => htmlspecialchars($product['name']),
					'ItemDesc'      => htmlspecialchars($product['model']),
					'ItemAmount'           => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
					'ItemQuantity'         => $product['quantity']
				);
			}

			$data['discount_amount_cart'] = 0;

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

			if ($total > 0) {
				$data['products'][] = array(
					'ItemName'     => $this->language->get('text_total'),
					'ItemDesc'    => '',
					'ItemAmount'    => $total,
					'ItemQuantity' => 1
				);
			} else {
				$data['discount_amount_cart'] -= $total;
			}
        
        
		
					/////////////////////////////////////End AW Vital  Information /////////////////////////////////
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/aw.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/extension/payment/aw', $data);
		} else {
			return $this->load->view('extension/payment/aw', $data);
		}		
		
		
		
	}
	
	public function callback() {
		    $this->config->get('aw_merchant');
			$this->language->load('extension/payment/aw');
			
			$data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

			if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$data['base'] = HTTP_SERVER;
			} else {
				$data['base'] = HTTPS_SERVER;
			}
		
			$data['charset'] = $this->language->get('charset');
			$data['language'] = $this->language->get('code');
			$data['direction'] = $this->language->get('direction');
			$data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
			$data['text_response'] = $this->language->get('text_response');
			$data['text_success'] = $this->language->get('text_success');
			$data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
			$data['text_failure'] = $this->language->get('text_failure');
			$data['text_cancelled'] = $this->language->get('text_cancelled');
			$data['text_cancelled_wait'] = sprintf($this->language->get('text_cancelled_wait'), $this->url->link('checkout/cart'));
			$data['text_pending'] = $this->language->get('text_pending');
			$data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/cart'));
			
			 $this->load->model('checkout/order');
			 $orderid = $this->request->post['MerchantReference'];
			 $order_info = $this->model_checkout_order->getOrder($orderid);
			 
			 
				
				$amount      		= 	$this->request->post['Amount'];
				$email        		=	$this->request->post['CustomerEmail'];
				$txnid		 		=   $this->request->post['TransactionID'];
			 
			 if (isset($this->request->post['TransactionStatus']) && $this->request->post['TransactionStatus'] == 'Successful') {
			 
				
				$order_id = $this->request->post['MerchantReference'];
				$message = '';
				$message .= 'orderId: ' . $this->request->post['MerchantReference'] . "\n";
				$message .= 'Transaction Id: ' . $this->request->post['TransactionID'] . "\n";
				foreach($this->request->post as $k => $val){
					$message .= $k.': ' . $val . "\n";
				}
					
							$this->model_checkout_order->addOrderHistory($this->request->post['MerchantReference'], $this->config->get('aw_order_status_id'), $message, false);
							$data['continue'] = $this->url->link('checkout/success');
							$data['column_left'] = $this->load->controller('common/column_left');
				            $data['column_right'] = $this->load->controller('common/column_right');
				            $data['content_top'] = $this->load->controller('common/content_top');
				            $data['content_bottom'] = $this->load->controller('common/content_bottom');
				            $data['footer'] = $this->load->controller('common/footer');
				            $data['header'] = $this->load->controller('common/header');
							if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/aw_success.tpl')) {
								$this->response->setOutput($this->load->view($this->config->get('config_template') . '/extension/payment/aw_success', $data));
							} else {
								$this->response->setOutput($this->load->view('extension/payment/aw_success', $data));
							}	
							
							
								
							
			 
			 
			 }else 
    			$data['continue'] = $this->url->link('checkout/cart');
				$data['column_left'] = $this->load->controller('common/column_left');
				$data['column_right'] = $this->load->controller('common/column_right');
				$data['content_top'] = $this->load->controller('common/content_top');
				$data['content_bottom'] = $this->load->controller('common/content_bottom');
				$data['footer'] = $this->load->controller('common/footer');
				$data['header'] = $this->load->controller('common/header');

		        if(isset($this->request->post['TransactionStatus'])  == 'Declined')
				{
			
				 if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/aw_cancelled.tpl')) {
					$this->response->setOutput($this->load->view($this->config->get('config_template') . '/extension/payment/aw_cancelled', $data));
				} else {
				    $this->response->setOutput($this->load->view('extension/payment/aw_cancelled', $data));
				}
				}
				else {
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/aw_failure.tpl')) {
					$this->response->setOutput($this->load->view($this->config->get('config_template') . '/extension/payment/aw_failure', $data));
				} else {
					$this->response->setOutput($this->load->view('extension/payment/aw_failure', $data));
				}	
				
				}					
			
		
   } 
}

?>