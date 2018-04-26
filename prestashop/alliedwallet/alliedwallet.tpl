{*
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
*}


<p class="payment_module">
  <a href="javascript:$('#alliedwallet_form').submit();" title="{l s='Pay with AlliedWallet' mod='alliedwallet'}">
    <img style="width:86px" src="{$module_template_dir}alliedwallet.gif" alt="{l s='Pay with AlliedWallet' mod='alliedwallet'}" />
    {l s='Pay with AlliedWallet' mod='alliedwallet'}
  </a>
</p>

<form action="{$AlliedWalletUrl}" method="post" id="alliedwallet_form" class="hidden">
  <input type="hidden" name="MerchantID" value="{$merchant_id}" />
  <input type="hidden" name="SiteID" value="{$site_id}" />
    <input type="hidden" name="QuickPayUserId" value="{$qp_id}" />
    <input type="hidden" name="QuickPayPassword" value="{$qp_pass}" />
  <input type="hidden" name="AmountShipping" value="{$shipping}" />
  <input type="hidden" name="AmountTotal" value="{$amount}" />
  <input type="hidden" name="CurrencyID" value="{$currency->iso_code}" />
<input name="ShippingRequired" type="hidden" value="true">
<input name="QuickpayToken" type="hidden" value="0">

  <input type="hidden" name="Address" value="{$address->address1}" />
  {if !empty($address->address2)}
  <input type="hidden" name="Address2" value="{$address->address2}" />
  {/if}
  <input type="hidden" name="City" value="{$address->city}" />
  <input type="hidden" name="State" value="{$state->iso_code}" />
  <input type="hidden" name="Country" value="{$country->iso_code}" />
  <input type="hidden" name="FirstName" value="{$address->firstname}" />
  <input type="hidden" name="LastName" value="{$address->lastname}" />
  <input type="hidden" name="PostalCode" value="{$address->postcode}" />
  <input type="hidden" name="Email" value="{$customer->email}" />
    <input name="Phone" type="hidden" value="{$delivery_phone}">
  <input type="hidden" name="DeclinedURL" value="{$goBackUrl}" />
  <input type="hidden" name="ApprovedURL" value="{$goBackUrl}" />
  <input type="hidden" name="ConfirmURL" value="{$confirm}" />

  {foreach from=$alliedProducts key=k item=v}
  <input type="hidden" name="ItemAmount[{$k}]" value="{number_format($v.price_wt, 2, '.', '')}" />
  <input type="hidden" name="ItemQuantity[{$k}]" value="{$v.cart_quantity}" />
  <input type="hidden" name="ItemName[{$k}]" value="{$v.name}" />
  <input type="hidden" name="ItemDesc[{$k}]" value="{$v.name} ref : {$v.id_product}" />
  {/foreach}

  <input name="NoMembership" type="hidden" value="1" />
  <input type="hidden" name="MerchantReference" value="{$id_cart}" />
    
</form>
