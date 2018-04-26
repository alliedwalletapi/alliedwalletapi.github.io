<?php
class ControllerPaymentAlliedwallet extends Controller {
	public function index() {
		$this->language->load('payment/alliedwallet');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['action'] = 'https://quickpay.alliedwallet.com/';
		//$data['action'] = 'http://beevip.com/post.php';
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		if ($order_info) {
			$currencies = array('AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD','SEK','DKK','PLN','NOK','HUF','CZK','ILS','MXN','MYR','BRL','PHP','TWD','THB','TRY','INR');
			if (in_array($order_info['currency_code'], $currencies)) {
				$CurrencyID = $order_info['currency_code'];
			} else {
				$CurrencyID = 'USD';
			}		
		$data['QuickpayToken'] = $this->config->get('alliedwallet_quickpay_token');
        $data['Descriptor'] = $this->config->get('alliedwallet_descriptor');    
		$data['SiteID'] = $this->config->get('alliedwallet_site_id');
		$data['fields']['NoMembership'] = 1;
		$data['fields']['ConfirmURL'] = HTTPS_SERVER . 'index.php?route=payment/alliedwallet/pdt';
		
		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$data['fields']['CancelURL'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		} else {
			$data['fields']['CancelURL'] = HTTPS_SERVER . 'index.php?route=checkout/guest_step_2';
		}
		$data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');				
			$data['products'] = array();
			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();
				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'name'  => $option['name'],
						//'value' => $option['option_value']
					);
				}
				$data['products'][] = array(
					'ItemName'     => $product['name'],
					'ItemDesc'    => $product['model'],
					'ItemAmount'    => $this->currency->format($product['price'], $CurrencyID, false, false),
					'ItemQuantity' => $product['quantity']
					);
			}	
			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $CurrencyID, false, false);
            $data['shippingtotal'] = $this->session->data['shipping_method']['text'];
            $data['shippingtotal'] = str_replace('$','',$data['shippingtotal']);
			$data['discount_amount_cart'] = 0;
			if ($total > 0) {
				$data['products'][] = array(
					'ItemName'     => $this->language->get('text_total'),
					'ItemDesc'    => '',
					'ItemAmount'    => $total,
					'ItemQuantity' => 1,
				);	
			} else {
				$data['discount_amount_cart'] -= $this->currency->format($total, $CurrencyID, false, false);
			}
			
			$data['CurrencyID'] = $CurrencyID;
			$data['AmountTotal'] = $total;
			$data['FirstName'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');	
			$data['LastName'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');	
			$data['Address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');	
			$data['Address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');	
			$data['City'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');	
			$data['State'] = html_entity_decode($order_info['payment_zone_code'], ENT_QUOTES, 'UTF-8');	
			$data['Zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');	
			$data['Country'] = $order_info['payment_iso_code_2'];
			$data['Phone'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
			$data['Email'] = $order_info['email'];
			$data['ConfirmURL'] = $this->url->link('payment/alliedwallet/callback', '', '');
            $data['ReturnURL'] = $this->url->link('checkout/success');
			$data['MerchantReference'] = $this->session->data['order_id'];		
			$data['DeclinedURL'] = $this->url->link('checkout/checkout', '', 'SSL');
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/alliedwallet.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/alliedwallet.tpl', $data);
			} else {
				return $this->load->view('default/template/payment/alliedwallet.tpl', $data);
			}
			
		}
	}

	public function callback() {
		$this->load->model('checkout/order');
		
		if(($_POST['MerchantReference']!='') and ($_POST['TransactionID']!='') and ($_POST['TransactionStatus']=='Successful')){
            $order_info = $this->model_checkout_order->getOrder($_POST['MerchantReference']);
			$this->model_checkout_order->addOrderHistory($_POST['MerchantReference'], 5);

			/*if (strtoupper(md5($this->config->get('twocheckout_secret') . $this->config->get('twocheckout_account') . $order_number . $this->request->post['total'])) == $this->request->post['key']) {
			if ($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) == $this->request->post['total']) {
				$this->model_checkout_order->confirm($this->request->post['cart_order_id'], $this->config->get('twocheckout_order_status_id'));
			} else {
				$this->model_checkout_order->confirm($this->request->post['cart_order_id'], $this->config->get('config_order_status_id'));// Ugh. Some one've faked the sum. What should we do? Probably drop a mail to the shop owner?				
			}*/
			echo '<html>' . "\n";
			echo '<head>' . "\n";
			echo '  <meta http-equiv="Refresh" content="0; url=' . $this->url->link('checkout/success') . '">' . "\n";
			echo '</head>'. "\n";
			echo '<body>' . "\n";
			echo '  <p>Please follow <a href="' . $this->url->link('checkout/success') . '">link</a>!</p>' . "\n";
			echo '</body>' . "\n";
			echo '</html>' . "\n";
			exit();
		} else {
			echo 'The response from AlliedWallet can\'t be parsed. Contact site administrator, please!'; 

		}
		}

}
?>