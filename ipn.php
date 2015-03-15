<?php
/*  based on:
 * PHP ebs IPN Integration Class Demonstration File
 *  
*/
global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;
$detailstable=$wpdb->prefix.'eshop_orders';
$derror=__('There appears to have been an error, please contact the site admin','eshop');

//sanitise
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
$_POST=sanitise_array($_POST);


/*
* reqd info for your gateway
*/
include_once (WP_PLUGIN_DIR.'/ebs-mg-gateway/ebs-mg-gateway.php');
// Setup class
require_once(WP_PLUGIN_DIR.'/ebs-mg-gateway/ebs-class.php');  // include the class file
$p = new ebs_class;             // initiate an instance of the class

$p->ebs_url = 'https://secure.ebs.in/pg/ma/sale/pay';     // ebs url

/*
* reqd info /end
*/

$this_script = get_option('site_url');
global $wp_rewrite;
if($eshopoptions['checkout']!=''){
	$p->autoredirect=add_query_arg('eshopaction','redirect',get_permalink($eshopoptions['checkout']));
}else{
	die('<p>'.$derror.'</p>');
}

// if there is no action variable, set the default action of 'process'
if(!isset($wp_query->query_vars['eshopaction']))
	$eshopaction='process';
else
	$eshopaction=$wp_query->query_vars['eshopaction'];

switch ($eshopaction) {
    case 'redirect':
    	//auto-redirect bits
		header('Cache-Control: no-cache, no-store, must-revalidate'); //HTTP/1.1
		header('Expires: Sun, 01 Jul 2005 00:00:00 GMT');
		header('Pragma: no-cache'); //HTTP/1.0
                
                $p = new ebs_class;
		//enters all the data into the database
                $ebs=$eshopoptions['ebs'];
		/*
		* this works out eShop's security field
		*/
                $Key=$ebs['secret_key'];
		$Cost=$_POST['amount']-$_POST['shipping_1'];
		/*if(isset($_POST['tax']))
			$Cost += $_POST['tax'];
		if(isset($_SESSION['shipping'.$blog_id]['tax'])) $Cost += $_SESSION['shipping'.$blog_id]['tax'];
		$theid=$eshopoptions['extra']['id'];
		$Cost=number_format($Cost,2);
		$checkid=md5($_POST['extraoption1'].$theid.'$'.$Cost);
		//debug
			//echo 'check: '.$_POST['extraoption1'].$theid.'$'.$Cost;
		//
		if(isset($_COOKIE['ap_id'])) $_POST['affiliate'] = $_COOKIE['ap_id'];
		orderhandle($_POST,$checkid);
		if(isset($_COOKIE['ap_id'])) unset($_POST['affiliate']);*/
		//$p = new extra_class; 
                $ExtraCost=$_POST['shipping_1'];
		//ebs uses comma not decimal point
		$Cost=number_format($Cost, 2, ',', '');
		$ExtraCost=number_format($ExtraCost, 2, ',', '');
		$OkUrl=urlencode($_POST['notify_url']);
		$GuaranteeOffered='1';
		$MD5string = $ebs['email'] . ":" . $Cost . ":" . $ExtraCost . ":" . $OkUrl . ":" . $GuaranteeOffered . $Key;
		$token=$MD5Hash = md5($MD5string);

		$refno = uniqid(rand());
		$p->add_field('reference_no',$refno);
		
		orderhandle($_POST,$refno);
		$_POST['custom']=$token;

		$slink=add_query_arg('DR','{DR}',$ebs['return_url']);


		$p->add_field('return_url',$slink);

		$p->add_field('account_id',$ebs['account_id']);
		$p->add_field('description',$description);
		$p->add_field('mode',$ebs['mode']);

		$p->add_field('description',$description);
		$p->add_field('mode',$ebs['mode']);
		
		
		
		$p->add_field('name',$_POST['first_name']." ".$_POST['last_name']);
		$p->add_field('address',$_POST['address1']." ".$_POST['address2']);
		$p->add_field('city',$_POST['city']);
		$p->add_field('state',$_POST['state']);
		$p->add_field('country',$_POST['country']);
		$p->add_field('postal_code',$_POST['zip']);
		$p->add_field('description',$_POST['item_number_1']);

		$p->add_field('ship_name',$_POST['ship_name']);
		$p->add_field('ship_address',$_POST['ship_address']);
		$p->add_field('ship_city',$_POST['ship_city']);
		$p->add_field('ship_state',$_POST['ship_state']);
		$p->add_field('ship_country',$_POST['ship_country']);
		$p->add_field('ship_postal_code',$_POST['ship_postcode']);
		$p->add_field('email',$_POST['email']);

		$p->add_field('phone',$_POST['phone']);
		$p->add_field('ship_phone',$_POST['ship_phone']);

/*		$total = 0;
		foreach($_POST as $name=>$value){
			if(strstr($name,'amount'))
				$total += $value;	
			
		}*/
                $amount = str_replace(',', '', $_POST['amount']);
                        $p->add_field('amount',$amount);

		$hash = $ebs['secret_key']. "|".$ebs['account_id']."|".$amount."|".$refno."|".html_entity_decode($slink)."|".$ebs['mode'];

                $secure_hash = md5($hash);
                        $p->add_field('secure_hash',$secure_hash);
		/*
		* more reqd info
		*/
		$p->ebs_url = 'https://secure.ebs.in/pg/ma/sale/pay';     // ebs url
		$echoit.=$p->eshop_submit_ebs_post($_POST);
		break;
        
   case 'process':      // Process and order...
		// There should be no output at this point.  To process the POST data,
		// the submit_extra_post() function will output all the HTML tags which
		// contains a FORM which is submited instantaneously using the BODY onload
		// attribute.  In other words, don't echo or printf anything when you're
		// going to be calling the submit_extra_post() function.
		
		// This is where you would have your form validation  and all that jazz.
		// You would take your POST vars and load them into the class like below,
		// only using the POST values instead of constant string expressions.

		// For example, after ensureing all the POST variables from your custom
		// order form are valid, you might have:
		//
		// $p->add_field('first_name', $_POST['first_name']);
		// $p->add_field('last_name', $_POST['last_name']);
      
      /****** The order has already gone into the database at this point ******/
      
		//goes direct to this script as nothing needs showing on screen.
                global $wp_rewrite,$blog_id;
		if($eshopoptions['cart_success']!=''){
			$ilink=add_query_arg(array('eshopaction'=>'ebsipn'),get_permalink($eshopoptions['cart_success']));
		}else{
			die('<p>'.$derror.'</p>');
		}
		//$p->add_field('extraURL', $ilink);

		/*$p->add_field('shipping_1',eshopShipTaxAmt());
		$sttable=$wpdb->prefix.'eshop_states';
		$getstate=$eshopoptions['shipping_state'];
		if($eshopoptions['show_allstates'] != '1'){
			$stateList=$wpdb->get_results("SELECT id,code,stateName FROM $sttable WHERE list='$getstate' ORDER BY stateName",ARRAY_A);
		}else{
			$stateList=$wpdb->get_results("SELECT id,code,stateName,list FROM $sttable ORDER BY list,stateName",ARRAY_A);
		}
		foreach($stateList as $code => $value){
			$eshopstatelist[$value['id']]=$value['code'];
		}*/
                //$p->add_field('shipping_1', number_format($_SESSION['shipping'.$blog_id],2));
                $p->add_field('shipping_1',eshopShipTaxAmt());
		foreach($_POST as $name=>$value){
			//have to do a discount code check here - otherwise things just don't work - but fine for free shipping codes
			if(strstr($name,'amount_')){
				if(isset($_SESSION['eshop_discount'.$blog_id]) && eshop_discount_codes_check()){
					$chkcode=valid_eshop_discount_code($_SESSION['eshop_discount'.$blog_id]);
					if($chkcode && apply_eshop_discount_code('discount')>0){
						$discount=apply_eshop_discount_code('discount')/100;
						$value = number_format(round($value-($value * $discount), 2),2);
						$vset='yes';
					}
				}
				if(is_discountable(calculate_total())!=0 && !isset($vset)){
					$discount=is_discountable(calculate_total())/100;
					$value = number_format(round($value-($value * $discount), 2),2);
				}
			}
			if(sizeof($stateList)>0 && ($name=='state' || $name=='ship_state')){
				if($value!='')
					$value=$eshopstatelist[$value];
			}
			$p->add_field($name, $value);
		}
		if($eshopoptions['status']!='live' && is_user_logged_in() &&  current_user_can('eShop_admin')||$eshopoptions['status']=='live'){
			$echoit .= $p->submit_ebs_post(); // submit the fields to extra
    	}
      	break;
      	
   //case 'extraipn':
   		/*
   		* the routine for when the merchant gateway sontacts your site to validate the order.
   		* may need altering to suit your gateway
   		*/
   		//$p->validate_ipn();
		//$theid=$eshopoptions['extra']['id'];
		//$checked=md5($p->ipn_data['extraoption1'].$theid.$p->ipn_data['extraReturnAmount']);
		//if($eshopoptions['status']=='live'){
		//	$txn_id = $wpdb->escape($p->ipn_data['extraTrxnReference']);
		//	$subject = __('extra IPN -','eshop');
		//}else{
		//	$txn_id = __("TEST-",'eshop').$wpdb->escape($p->ipn_data['extraTrxnReference']);
		//	$subject = __('Testing: extra IPN - ','eshop');
		//}
		//check txn_id is unique
		//$checktrans=$wpdb->get_results("select transid from $detailstable");
		//$astatus=$wpdb->get_var("select status from $detailstable where checkid='$checked' limit 1");
		//foreach($checktrans as $trans){
		//	if(strpos($trans->transid, $p->ipn_data['extraTrxnReference'])===true){
		//		$astatus='Failed';
		//		$txn_id .= __(" - Duplicated",'eshop');
		//		$extradetails .= __("Duplicated Transaction Id.",'eshop');
		//	}
		//}
		//accepted response codes - all other fail.
		//$extrarescodes=array('00','08','10','11','16');
		/*if(!in_array($p->ipn_data['extraresponseCode'],$extrarescodes)){
			$astatus='Failed';
			$txn_id .= __(" - Failed",'eshop');
			$extradetails .= ' '.$p->ipn_data['extraresponseText'];
		}*/

		//the magic bit  + creating the subject for our email.
		/*if($astatus=='Pending' && $p->ipn_data['extraTrxnStatus']=='True'){
			$subject .=__("Completed Payment",'eshop');	
			$ok='yes';
			eshop_mg_process_product($txn_id,$checked);
		}else{
			$query2=$wpdb->query("UPDATE $detailstable set status='Failed',transid='$txn_id' where checkid='$checked'");
			$subject .=__("A Failed Payment",'eshop');
			$ok='no';
			$extradetails .= __("The transaction was not completed successfully. eShop could not validate the order.",'eshop');
			$extradetails .= ' '.$p->ipn_data['extraresponseText'];
		}*/
		/*$subject .=" Ref:".$txn_id;
		$array=eshop_rtn_order_details($checked);
		// email to business a complete copy of the notification from extra to keep!!!!!
		 $body =  __("An extra payment notification was received",'eshop')."\n";
		 $body .= "\n".__("from ",'eshop').$array['eemail'].__(" on ",'eshop').date('m/d/Y');
		 $body .= __(" at ",'eshop').date('g:i A')."\n\n".__('Details','eshop').":\n";
		 if(isset($array['dbid']))
			$body .= get_option( 'siteurl' ).'/wp-admin/admin.php?page=eshop-orders.php&view='.$array['dbid']."\n";

		if($extradetails!='') $body .= $extradetails."\n\n";
		foreach ($p->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
		//debug
		//	$body .= "\n".'check: '.$p->ipn_data['extraoption1'].$theid.$p->ipn_data['extraReturnAmount'];

		$body .= "\n\n".__('Regards, Your friendly automated response.','eshop')."\n\n";
		$headers=eshop_from_address();
		$eshopemailbus=$eshopoptions['extra']['email'];
		$to = apply_filters('eshop_gatextra_details_email', array($eshopemailbus));
		wp_mail($to, $subject, $body, $headers);*/

		/*if($ok=='yes'){
			//only need to send out for the successes!
			//lets make sure this is here and available
			include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
			eshop_send_customer_email($checked, '151');
		}*/
		//$_SESSION = array();
		//session_destroy();
		//break;
}
?>