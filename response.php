<?php
/*  based on:
 * PHP ebs IPN Integration Class Demonstration File
 *  4.16.2005 - Micah Carrick, email@micahcarrick.com
*/



//function eshop_ebs_response($_GET){
function eshop_ebs_response(){

//sanitise
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
include_once(ABSPATH.'wp-includes/wp-db.php');
global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;
$detailstable=$wpdb->prefix.'eshop_orders';




//include_once (WP_PLUGIN_DIR.'/eshop/ebs/index.php');
/*
* reqd info for your gateway
*/
include_once (WP_PLUGIN_DIR.'/ebs-mg-gateway/ebs-mg-gateway.php');
// Setup class
require_once(WP_PLUGIN_DIR.'/ebs-mg-gateway/ebs-class.php');  // include the class file
$p = new ebs_class;             // initiate an instance of the class


$p->ebs_url = 'https://secure.ebs.in/pg/ma/sale/pay';     // ebs url

$this_script = get_option('siteurl');

// if there is no action variable, set the default action of 'process'
if(!isset($wp_query->query_vars['eshopaction']))
	$eshopaction='process';
else
	$eshopaction=$wp_query->query_vars['eshopaction'];


	

	$ps = new ebs_class; // initiate an instance of the class
	$DR = $_GET['DR'];

		foreach ($_GET as $field=>$value) { 
		  $ps->ipn_data["$field"] = $value;
		}

		$ebs = $eshopoptions['ebs']; 
		$secret_key = $ebs['secret_key'];
	//	$secret_key = "ebskey";

	if(isset($DR)) {
		 require('Rc43.php');
		 $DR = preg_replace("/\s/","+",$DR);
		 $rc4 = new Crypt_RC4($secret_key);
	 	 $QueryString = base64_decode($DR);
		 $rc4->decrypt($QueryString);
		 $QueryString = split('&',$QueryString);

		 $response = array();
		 foreach($QueryString as $param){
		 	$param = split('=',$param);
			$response[$param[0]] = urldecode($param[1]);
		 }
	}
	
	$refno = $response['MerchantRefNo'];
	
	$transid = $response['PaymentID'];
	if($response['ResponseCode']==0){
	
		print_r('<div><h2>Thank you for your order !! </h2><b> Transaction Successful</b></div>');
		$query2=$wpdb->query("UPDATE $detailstable set status='Completed',transid='$transid' where checkid='$refno'");



	


//only need to send out for the successes!
			//lets make sure this is here and available
			include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
			//this is an email sent to the customer:
			//first extract the order details
			$array=eshop_rtn_order_details($refno);
			$etable=$wpdb->prefix.'eshop_emails';
			//grab the template
			$thisemail=$wpdb->get_row("SELECT emailSubject,emailContent FROM ".$etable." WHERE id='1'  order by id DESC limit 1");

			$this_email = stripslashes($thisemail->emailContent);

			// START SUBST
			$csubject=stripslashes($thisemail->emailSubject);
			$this_email = eshop_email_parse($this_email,$array);

			//try and decode various bits - may need tweaking Mike, we may have to write 
			//a function to handle this depending on what you are using - but for now...
			$this_email=html_entity_decode($this_email,ENT_QUOTES);
			$headers=eshop_from_address();
			

			
	

$subject .=" Ref:".$refno;
			$array=eshop_rtn_order_details($refno);
			// email to business a complete copy of the notification from paypal to keep!!!!!
			 $to = $array['eemail'];    //  your email
			 $body =  __("An instant payment notification was received",'eshop')."\n";
			 $body .= "\n".__("from ",'eshop').$p->ipn_data['payer_email'].__(" on ",'eshop').date('m/d/Y');
			 $body .= __(" at ",'eshop').date('g:i A')."\n\n".__('Details','eshop').":\n";
//			 if(isset($array['dbid']))
//			 	$body .= get_option( 'siteurl' ).'/wp-admin/admin.php?page=eshop_orders.php&view='.$array['dbid']."\n";

			 //debug
			//$body .= 'checked:'.$checked."\n".$p->ipn_data['business'].$p->ipn_data['custom'].$p->ipn_data['payer_email'].$chkamt."\n";
			if($ebsdetails!='') $body .= $ebsdetails."\n\n";
			 //foreach ($response as $key => $value) { $body .= "\n$key: $value"; }
			 $body .= "\n\n".__('Regards, Your friendly automated response.','eshop')."\n\n";

			$headers=eshop_from_address();
			$message = wordwrap($message, 2000);
			//mail($to, $subject, $body);

		$_SESSION = array();
	      	session_destroy();

	} else{
		print_r('<div><h2>Sorry Try Again !! </h2><b> Transaction Failed</b></div>'); 
	}

}

?>
