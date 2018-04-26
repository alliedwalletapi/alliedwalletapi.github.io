<?php 
class ControllerExtensionPaymentAw extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('extension/payment/aw');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('aw', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_live'] = $this->language->get('text_live');
		$data['text_successful'] = $this->language->get('text_successful');
		$data['text_fail'] = $this->language->get('text_fail');
		$data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
		$data['entry_site_id'] = $this->language->get('entry_site_id');
        $data['entry_auth_token'] = $this->language->get('entry_auth_token');
        $data['entry_descriptor'] = $this->language->get('entry_descriptor');
		$data['entry_order_status'] = $this->language->get('entry_order_status');		
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
        $data['help_merchant_id'] = $this->language->get('help_merchant_id');
        $data['help_site_id'] = $this->language->get('help_site_id');
        $data['help_auth_token'] = $this->language->get('help_auth_token');
		$data['tab_general'] = $this->language->get('tab_general');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['merchant_id'])) {
			$data['error_merchant_id'] = $this->error['merchant_id'];
		} else {
			$data['error_merchant_id'] = '';
		}

	if (isset($this->error['site_id'])) {
			$data['error_site_id'] = $this->error['site_id'];
		} else {
			$data['error_site_id'] = '';
		}

     	if (isset($this->error['auth_token'])) {
			$data['error_auth_token'] = $this->error['auth_token'];
		} else {
			$data['error_auth_token'] = '';
		}
        
         	if (isset($this->error['descriptor'])) {
			$data['error_descriptor'] = $this->error['descriptor'];
		} else {
			$data['error_descriptor'] = '';
		}



  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/aw', 'token=' . $this->session->data['token'], true),
      		'separator' => ' :: '
   		);
				
		$data['action'] = $this->url->link('extension/payment/aw', 'token=' . $this->session->data['token'], true);
		
		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);
		
	
        
        	if (isset($this->request->post['aw_merchant_id'])) {
			$data['aw_merchant_id'] = $this->request->post['aw_merchant_id'];
		} else {
			$data['aw_merchant_id'] = $this->config->get('aw_merchant_id');
		}
        
           	if (isset($this->request->post['aw_site_id'])) {
			$data['aw_site_id'] = $this->request->post['aw_site_id'];
		} else {
			$data['aw_site_id'] = $this->config->get('aw_site_id');
		}
        
              	if (isset($this->request->post['aw_auth_token'])) {
			$data['aw_auth_token'] = $this->request->post['aw_auth_token'];
		} else {
			$data['aw_auth_token'] = $this->config->get('aw_auth_token');
		}
		

                 	if (isset($this->request->post['aw_descriptor'])) {
			$data['aw_descriptor'] = $this->request->post['aw_descriptor'];
		} else {
			$data['aw_descriptor'] = $this->config->get('aw_descriptor');
		}
				
		if (isset($this->request->post['aw_order_status_id'])) {
			$data['aw_order_status_id'] = $this->request->post['aw_order_status_id'];
		} else {
			$data['aw_order_status_id'] = $this->config->get('aw_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['aw_geo_zone_id'])) {
			$data['aw_geo_zone_id'] = $this->request->post['aw_geo_zone_id'];
		} else {
			$data['aw_geo_zone_id'] = $this->config->get('aw_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');
										
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['aw_status'])) {
			$data['aw_status'] = $this->request->post['aw_status'];
		} else {
			$data['aw_status'] = $this->config->get('aw_status');
		}
		
		if (isset($this->request->post['aw_sort_order'])) {
			$data['aw_sort_order'] = $this->request->post['aw_sort_order'];
		} else {
			$data['aw_sort_order'] = $this->config->get('aw_sort_order');
		}
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

				
		$this->response->setOutput($this->load->view('extension/payment/aw', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/aw')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['aw_merchant_id']) {
			$this->error['aw_merchant_id'] = $this->language->get('error_aw_merchant_id');
		}
			
			if (!$this->request->post['aw_site_id']) {
			$this->error['aw_site_id'] = $this->language->get('error_aw_site_id');
		}
        
        	if (!$this->request->post['aw_auth_token']) {
			$this->error['aw_auth_token'] = $this->language->get('error_aw_auth_token');
		}
		
				
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>