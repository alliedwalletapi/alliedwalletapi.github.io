<form action="<?php echo $action; ?>" method="post" id="payment">



  <?php $i = 0; 
  
  $total = 0;
  ?>

  <?php foreach ($products as $product) { ?>

  <input type="hidden" name="ItemName[<?php echo $i; ?>]" value="<?php echo $product['ItemName']; ?>" />

  <input type="hidden" name="ItemDesc[<?php echo $i; ?>]" value="<?php echo $product['ItemDesc']; ?>" />

  <input type="hidden" name="ItemAmount[<?php echo $i; ?>]" value="<?php echo $product['ItemAmount']; ?>" />

  <input type="hidden" name="ItemQuantity[<?php echo $i; ?>]" value="<?php echo $product['ItemQuantity']; ?>" />




  <?php $i++; 
  $total = $total+$product['ItemAmount'];
  
  ?>

  <?php } ?>

  <?php if ($discount_amount_cart) { ?>

  

  <?php } ?>

  <input type="hidden" name="CurrencyID" value="<?php echo $CurrencyID; ?>" />

  <input type="hidden" name="FirstName" value="<?php echo $FirstName; ?>" />

  <input type="hidden" name="LastName" value="<?php echo $LastName; ?>" />

  <input type="hidden" name="Address" value="<?php echo $Address1; ?>" />

  <input type="hidden" name="Address2" value="<?php echo $Address2; ?>" />

  <input type="hidden" name="City" value="<?php echo $City; ?>" />

  <input type="hidden" name="Zip" value="<?php echo $Zip; ?>" />

  <input type="hidden" name="Country" value="<?php echo $Country; ?>" />

  <input type="hidden" name="Email" value="<?php echo $Email; ?>" />

  <input type="hidden" name="MerchantReference" value="<?php echo $MerchantReference; ?>" />

  <input type="hidden" name="ApprovedURL" value="<?php echo $ReturnURL; ?>" />

  <input type="hidden" name="ConfirmURL" value="<?php echo $ConfirmURL; ?>" />

  <input type="hidden" name="DeclinedURL" value="<?php echo $DeclinedURL; ?>" />
  
    <input type="hidden" name="SiteID" value="<?php echo $SiteID; ?>" />

  
      <input type="hidden" name="QuickPayToken" value="<?php echo $QuickpayToken; ?>" />

   <input type="hidden" name="AmountTotal" value="<?php echo $total; ?>" /> 
  <input type="hidden" name="MembershipRequired" value="false" />
  
  <input name="AmountShipping" type="hidden" value="0" /> 
<input name="ShippingRequired" type="hidden" value="false" /> 



<div class="buttons">

  <div class="right"><a id="button-confirm" class="button" onclick="$('#payment').submit();"><span><?php echo $button_confirm; ?></span></a></div>

</div>
    
    </form>

For billing questions please call Allied Wallet at +44-203-3188334 or <a href="https://www.alliedwallet.com" rel="nofollow">Click Here</a><br />
Your credit card statement will read <?php echo $Descriptor; ?><br />
<a href="http://www.alliedwallet.com" rel="nofollow"><img src="catalog/view/theme/default/template/payment/images/AWLOGO.GIF" alt="accept credit cards online" border="0" /></a>