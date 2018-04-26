<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Alliedwallet extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'alliedwallet';
		$this->tab = 'payments_gateways';
		$this->author = 'PrestaShop';

		$this->version = 1.0;

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Allied Wallet');
		$this->description = $this->l('Accept payments by Allied Wallet');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

    /** Backward compatibility 1.4 and 1.5 */
    require(_PS_MODULE_DIR_.'/alliedwallet/backward_compatibility/backward.php');
	}



	public function install()
	{
		if (!parent::install()
			|| !Configuration::updateValue('ALLIEDWALLET_MERCHANT_ID', '')
			|| !Configuration::updateValue('ALLIEDWALLET_SITE_ID', '')
            || !Configuration::updateValue('ALLIEDWALLET_QP_ID', '')
            || !Configuration::updateValue('ALLIEDWALLET_QP_PASS', '')
			|| !Configuration::updateValue('ALLIEDWALLET_CONFIRM_PAGE',
				'http://'.Tools::safeOutput($_SERVER['HTTP_HOST']).
				__PS_BASE_URI__.'modules/alliedwallet/validation.php')
			|| !Configuration::updateValue('ALLIEDWALLET_RETURN_PAGE',
				'http://'.Tools::safeOutput($_SERVER['HTTP_HOST']).
				__PS_BASE_URI__.'history.php')
			|| !$this->registerHook('payment'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('ALLIEDWALLET_MERCHANT_ID')
			|| !Configuration::deleteByName('ALLIEDWALLET_SITE_ID')
            || !Configuration::deleteByName('ALLIEDWALLET_QP_ID')
            || !Configuration::deleteByName('ALLIEDWALLET_QP_PASS')
			|| !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$this->_html = '<h2>
<img src="../modules/alliedwallet/alliedwallet.gif" alt="alliedwallet"/>
</h2>';
		$this->displayAlliedWallet();
		if (isset($_POST['submitAlliedwallet']))
		{
			if (empty($_POST['merchant_id']))
				$this->_postErrors[] = $this->l('Allied Wallet Merchant ID is required');
			if (empty($_POST['site_id']))
				$this->_postErrors[] = $this->l('Allied Wallet Site ID is required');
            if (empty($_POST['qp_id']))
				$this->_postErrors[] = $this->l('Allied Wallet QP User is required');
            if (empty($_POST['qp_pass']))
				$this->_postErrors[] = $this->l('Allied Wallet QP PASSWORD is required');
			if (empty($_POST['return_url']))
				$this->_postErrors[] = $this->l('Complete URL is required and must be correct.');
			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('ALLIEDWALLET_MERCHANT_ID',
					pSQL($_POST['merchant_id']));
				Configuration::updateValue('ALLIEDWALLET_SITE_ID',
					pSQL($_POST['site_id']));
                Configuration::updateValue('ALLIEDWALLET_QP_ID',
					pSQL($_POST['qp_id']));
                Configuration::updateValue('ALLIEDWALLET_QP_PASS',
					pSQL($_POST['qp_pass']));
				$this->displayConf();
			}
			else
				$this->displayErrors();
		}
		$this->displayFormSettings();
		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') :
				$this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ?
					$this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}


	public function displayAlliedWallet()
	{
		$this->_html .= '
<fieldset style="margin-bottom:10px">
'.$this->l('This module allows you to accept payments by Allied Wallet.').'<br/><br/>
'.$this->l('In order to user this module, you have to create an account with Allied Wallet').'<br/>
'.$this->l('If you already have an account, please fill in the required fileds with the Merchant ID and Site ID and Token provided to you by Allied Wallet.').'<br/><br/>
'.$this->l('Don\'t wait and').' <a target="_BLANK" style="color:blue;text-decoration:underline" href="https://www.alliedwallet.com/sign-up">'.$this->l('sign-up today').'</a> '.$this->l('to get your Allied Wallet account.').'
</fieldset>
';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('ALLIEDWALLET_MERCHANT_ID', 'ALLIEDWALLET_SITE_ID', 'ALLIEDWALLET_QP_ID', 'ALLIEDWALLET_QP_PASS'));
		$merchant_id = array_key_exists('merchant_id', $_POST) ? $_POST['merchant_id'] : (array_key_exists('ALLIEDWALLET_MERCHANT_ID', $conf) ? $conf['ALLIEDWALLET_MERCHANT_ID'] : '');
		$site_id = array_key_exists('site_id', $_POST) ? $_POST['site_id'] : (array_key_exists('ALLIEDWALLET_SITE_ID', $conf) ? $conf['ALLIEDWALLET_SITE_ID'] : '');
        $qp_id = array_key_exists('qp_id', $_POST) ? $_POST['qp_id'] : (array_key_exists('ALLIEDWALLET_QP_ID', $conf) ? $conf['ALLIEDWALLET_QP_ID'] : '');
        $qp_pass = array_key_exists('qp_pass', $_POST) ? $_POST['qp_pass'] : (array_key_exists('ALLIEDWALLET_QP_PASS', $conf) ? $conf['ALLIEDWALLET_QP_PASS'] : '');
		$complete_url = Configuration::get('ALLIEDWALLET_RETURN_PAGE');

		$this->_html .= '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" style="clear: both;">
		<fieldset style="margin-top:10px">
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
			<label>'.$this->l('Allied Wallet Merchant ID').'</label>
			<div class="margin-form"><input type="text" size="40" name="merchant_id" value="'.Tools::safeOutput($merchant_id).'" /> * </div>
			<label>'.$this->l('Allied Wallet Site ID').'</label>
			<div class="margin-form"><input type="text" size="40" name="site_id" value="'.Tools::safeOutput($site_id).'" /> * </div>
            <label>'.$this->l('Allied Wallet QP ID').'</label>
			<div class="margin-form"><input type="text" size="40" name="qp_id" value="'.Tools::safeOutput($qp_id).'" /> * </div>
            <label>'.$this->l('Allied Wallet QP PASSWORD').'</label>
			<div class="margin-form"><input type="text" size="40" name="qp_pass" value="'.Tools::safeOutput($qp_pass).'" /> * </div>
	<!--		<label>'.$this->l('Confirm URL').'</label>
			<div class="margin-form"><input type="text" size="40" name="confirm_url" value="" /> '.$this->l('Link where AlliedWallet will post back on every transaction').' </div>-->
			<label>'.$this->l('Redirect URL').'</label>
			<div class="margin-form"><input type="text" size="40" name="return_url" value="'.Tools::safeOutput($complete_url).'" /> '.$this->l('Please enter the URL of the page where customers will be redirected after their purchases.').'</div>
			<div class="margin-form"><input type="submit" name="submitAlliedwallet" value="'.$this->l('Update settings').'" class="button" /></div>
		</fieldset>
		</form>';

	}

	public function hookPayment($params)
	{
		if (!$this->active
			|| !Configuration::get('ALLIEDWALLET_MERCHANT_ID')
			|| !Configuration::get('ALLIEDWALLET_SITE_ID')
            || !Configuration::get('ALLIEDWALLET_QP_ID')
            || !Configuration::get('ALLIEDWALLET_QP_PASS'))
			return ;

    $smarty = $this->context->smarty;

		$address = new Address((int)$params['cart']->id_address_invoice);
		$customer = new Customer((int)$params['cart']->id_customer);
		$merchant_id = Tools::safeOutput(Configuration::get('ALLIEDWALLET_MERCHANT_ID'));
		$site_id = Tools::safeOutput(Configuration::get('ALLIEDWALLET_SITE_ID'));
        $qp_id = Tools::safeOutput(Configuration::get('ALLIEDWALLET_QP_ID'));
		$qp_pass = Tools::safeOutput(Configuration::get('ALLIEDWALLET_QP_PASS'));
        $currency = new Currency((int)$params['cart']->id_currency);

		if (!Validate::isLoadedObject($address)
			|| !Validate::isLoadedObject($customer)
			|| !Validate::isLoadedObject($currency))
			return $this->l('Alliedwallet error: (invalid address or customer)');

		$products = $params['cart']->getProducts();
		if (_PS_VERSION_ >= 1.5)
			$discounts = $params['cart']->getCartRules();
		else
			$discounts = $params['cart']->getDiscounts();

		if ($discounts != null)
		{
			foreach ($discounts as $k => $v)
			{
				$v['total'] = (!isset($v['value']) ? $v['reduction_amount'] : $v['value']) * -1;
				$v['cart_quantity'] = 1;
				$v['name'] = 'Discount id: '.$v['id_discount'];
				$v['id_product'] = $v['id_discount'];
				$products[] = $v;
			}
		}

		$address->address1 = substr($address->address1, 0, 50);
		if (isset($address->address2) && $address->address2 != '')
			$address->address2 = substr($address->address2, 0, 50);
		$address->city = substr($address->city, 0, 20);

		if (_PS_VERSION_ >= 1.5)
			$shippingCost = $params['cart']->getOrderTotal(true, Cart::ONLY_SHIPPING);
		else
			$shippingCost = $params['cart']->getOrderShippingCost();

		$smarty->assign(array(
				'address' => $address,
				'country' => new Country((int)$address->id_country),
                'state' => new State((int)$address->id_state),
				'customer' => $customer,
				'merchant_id' => $merchant_id,
				'site_id' => $site_id,
                'qp_id' => $qp_id,
                'qp_pass' => $qp_pass,
				'currency' => $currency,
                'delivery_phone' => $address->phone_mobile,
				'AlliedWalletUrl' => 'https://quickpay.alliedwallet.com',
                //'AlliedWalletUrl' => 'http://beevip.com/post.php',
				'amount' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 4), $currency), 2, '.', '    '),
				'shipping' =>  number_format(Tools::convertPrice(($shippingCost + $params['cart']->getOrderTotal(true, 6)), $currency), 2, '.', ''),
				'alliedProducts' => $products,
				'total' => number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', ' '),
				'id_cart' => (int)$params['cart']->id,
				'goBackUrl' => Tools::safeOutput(Configuration::get('ALLIEDWALLET_RETURN_PAGE')),
				'confirm' => Tools::safeOutput(Configuration::get('ALLIEDWALLET_CONFIRM_PAGE')),
				'this_path' => $this->_path
			));

		return $this->display(__FILE__, 'alliedwallet.tpl');
	}
}