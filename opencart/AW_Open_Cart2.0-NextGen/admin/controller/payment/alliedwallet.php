<?php 
class ControllerPaymentAlliedwallet extends Controller {

	private $error = array(); 

	public function index() {

		$this->language->load('payment/alliedwallet');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('alliedwallet', $this->request->post);				

			$this->session->data['success'] = $this->language->get('text_success');

			
            
            if (version_compare(VERSION, '2.0', '>=')) {
            $this->response->redirect($this->url->link('payment/alliedwallet', 'token=' . $this->session->data['token'], 'SSL'));
                    } else {
            $this->redirect($this->url->link('payment/alliedwallet', 'token=' . $this->session->data['token'], 'SSL'));
                    }
            
            
            
            
            
            //$this->redirect($this->url->link('payment/alliedwallet', 'token=' . $this->session->data['token'], 'SSL'));

		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['entry_token'] = $this->language->get('entry_token');
		$data['entry_site_id'] = $this->language->get('entry_site_id');
		$data['entry_descriptor'] = $this->language->get('entry_descriptor');		
		$data['entry_transaction'] = $this->language->get('entry_transaction');


		$data['entry_total'] = $this->language->get('entry_total');	

		$data['entry_canceled_reversal_status'] = $this->language->get('entry_canceled_reversal_status');

		$data['entry_completed_status'] = $this->language->get('entry_completed_status');
		
		$data['entry_ApprovedURL'] = $this->language->get('entry_ApprovedURL');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_ConfirmURL'] = $this->language->get('entry_ConfirmURL');
		
		

		$data['entry_denied_status'] = $this->language->get('entry_denied_status');

		$data['entry_expired_status'] = $this->language->get('entry_expired_status');

		$data['entry_failed_status'] = $this->language->get('entry_failed_status');

		$data['entry_pending_status'] = $this->language->get('entry_pending_status');

		$data['entry_processed_status'] = $this->language->get('entry_processed_status');

		$data['entry_refunded_status'] = $this->language->get('entry_refunded_status');

		$data['entry_reversed_status'] = $this->language->get('entry_reversed_status');

		$data['entry_voided_status'] = $this->language->get('entry_voided_status');

		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');

		$data['entry_status'] = $this->language->get('entry_status');

		$data['entry_sort_order'] = $this->language->get('entry_sort_order');


		$data['button_save'] = $this->language->get('button_save');

		$data['button_cancel'] = $this->language->get('button_cancel');        

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}
		
 		if (isset($this->error['error_merchant_quickpay_token'])) {
			$data['error_merchant_quickpay_token'] = $this->error['error_merchant_quickpay_token'];
		} else {
			$data['error_merchant_quickpay_token'] = '';
		}
		
			
 		if (isset($this->error['error_descriptor'])) {
			$data['error_descriptor'] = $this->error['error_descriptor'];
		} else {
			$data['errort_descriptor'] = '';
		}
		 		

        if (isset($this->error['site'])) {
			$data['error_site'] = $this->error['site'];
		} else {
			$data['error_site'] = '';
		}


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/alliedwallet', 'token=' . $this->session->data['token'], 'SSL')
		);
		

		$data['action'] = HTTPS_SERVER . 'index.php?route=payment/alliedwallet&token=' . $this->session->data['token'];

		$data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

		
		
		$data['ApprovedURL'] = HTTP_CATALOG . 'index.php?route=payment/alliedwallet/callback';
		$data['ConfirmURL'] = HTTP_CATALOG . 'index.php?route=payment/alliedwallet/callback';
		$data['DeclinedURL'] = HTTP_CATALOG . 'index.php';
		
		if (isset($this->request->post['alliedwallet_quickpay_token'])) {
			$data['alliedwallet_quickpay_token'] = $this->request->post['alliedwallet_quickpay_token'];
		} else {
			$data['alliedwallet_quickpay_token'] = $this->config->get('alliedwallet_quickpay_token');
		}
		
		if (isset($this->request->post['alliedwallet_descriptor'])) {
			$data['alliedwallet_descriptor'] = $this->request->post['alliedwallet_descriptor'];
		} else {
			$data['alliedwallet_descriptor'] = $this->config->get('alliedwallet_descriptor');
		}
		
        if (isset($this->request->post['alliedwallet_site_id'])) {
			$data['alliedwallet_site_id'] = $this->request->post['alliedwallet_site_id'];
		} else {
			$data['alliedwallet_site_id'] = $this->config->get('alliedwallet_site_id');
		}
		if (isset($this->request->post['alliedwallet_status'])) {
			$data['alliedwallet_status'] = $this->request->post['alliedwallet_status'];
		} else {
			$data['alliedwallet_status'] = $this->config->get('alliedwallet_status');
		}
		
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		/*if (isset($this->request->post['alliedwallet_currency'])) {
			$this->data['alliedwallet_currency'] = $this->request->post['alliedwallet_currency'];
		} else {
			$this->data['alliedwallet_currency'] = $this->config->get('alliedwallet_currency');
		}*/

		if (isset($this->request->post['alliedwallet_total'])) {

			$data['alliedwallet_total'] = $this->request->post['alliedwallet_total'];

		} else {

			$data['alliedwallet_total'] = $this->config->get('alliedwallet_total'); 

		} 
		
		if (isset($this->request->post['alliedwallet_quickpay_completed_status_id'])) {

			$data['alliedwallet_quickpay_completed_status_id'] = $this->request->post['alliedwallet_quickpay_completed_status_id'];

		} else {

			$data['alliedwallet_quickpay_completed_status_id'] = $this->config->get('alliedwallet_quickpay_completed_status_id');

		}	
		
		if (isset($this->request->post['alliedwallet_quickpay_denied_status_id'])) {

			$data['alliedwallet_quickpay_denied_status_id'] = $this->request->post['alliedwallet_quickpay_denied_status_id'];

		} else {
			$data['alliedwallet_quickpay_denied_status_id'] = $this->config->get('alliedwallet_quickpay_denied_status_id');

		}
		if (isset($this->request->post['alliedwallet_sort_order'])) {
			$data['alliedwallet_sort_order'] = $this->request->post['alliedwallet_sort_order'];
		} else {
			$data['alliedwallet_sort_order'] = $this->config->get('alliedwallet_sort_order');
		}
		//$this->template = 'payment/alliedwallet.tpl';
		//$this->children = array(
		//	'common/header',
		//	'common/footer'
		//);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('payment/alliedwallet.tpl', $data));
	}


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/alliedwallet')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->request->post['alliedwallet_quickpay_token']) {
			$this->error['error_merchant_quickpay_token'] = $this->language->get('error_merchant_quickpay_token');
		}
		

			
		if (!$this->request->post['alliedwallet_status']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}
		if (!$this->request->post['alliedwallet_site_id']) {
			$this->error['merchant_site'] = $this->language->get('merchant_site');
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>