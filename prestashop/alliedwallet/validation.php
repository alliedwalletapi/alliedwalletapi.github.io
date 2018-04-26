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

require_once( '../../config/config.inc.php' );
require_once(_PS_MODULE_DIR_."/alliedwallet/alliedwallet.php");

$ch = curl_init('https://quickpay.alliedwallet.com/iplist');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$content = curl_exec($ch);
curl_close($ch);

$ipAddress =  explode('|', $content);
$allied = new AlliedWallet();

//if (!in_array($_SERVER['REMOTE_ADDR'], $ipAddress))
//	die($allied->l('Your are not allowed to be here'));

$cartId = (int)$_POST['MerchantReference'];
$cart = new Cart($cartId);

if (!Validate::isLoadedObject($cart))
{
	Logger::AddLog('[AlliedWallet] Cart loading failed', 2);
	die($allied->l('Card loading failed'));
}

$responseDescription = 'VALID PAYMENT REF: '.$cartId;

$allied->validateOrder($cart->id,
	Configuration::get('PS_OS_PAYMENT'),
	(float)$_POST['Amount'],
	$allied->name,
	$responseDescription,
	array(),
	NULL,
	false,
	$cart->secure_key);

?>