<?php
/*
Plugin Name: EBS Merchant Gateway For eShop
Plugin URI: http://www.skrilleshopplugin.com/product/ebs-merchant-gateway-for-eshop/
Description: EBS Merchant Gateway for eShop is an add-on plugin for eShop WordPress plugin which adds up additional merchant gateway namely "EBS" for all INDIAN & US currency based country eShop powered site owners.
Version: 0.1
Author: L.CH.RAJKUMAR 
Author URI: https://www.twitter.com/lchrajkumar

    Copyright 2014 L.CH.RAJKUMAR  (email : l.ch.rajkumar@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
register_activation_hook(__FILE__,'eshopebs_activate');
function eshopebs_activate(){
	/*
	* Activation routines
	*/
	global $wpdb;
	$opts=get_option('active_plugins');
	$eshopthere=false;
	foreach($opts as $opt){
		if($opt=='eshop/eshop.php')
			$eshopthere=true;
	}
	if($eshopthere==false){
		deactivate_plugins('ebs-mg-gateway.php'); //Deactivate ourself
		wp_die(__('ERROR! eShop is not active.','eshop')); 
	}
	/*
	* insert email template for use with this merchant gateway, if 151 is changed, then ipn.php needs amending as well 
	*/
	$table = $wpdb->prefix ."eshop_emails";
	$esubject=__('Your order from ','eshop').get_bloginfo('name');
	$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('151','".__('Automatic EBS email','eshop')."','$esubject')"); 
	
}
add_action('eshop_setting_merchant_load','eshopmgpage2');
function eshopmgpage2($thist){
	/*
	* adding the meta box for this gateway
	*/
	add_meta_box('eshop-m-ebs', __('ebs','eshop'), 'ebs_box', $thist->pagehook, 'normal', 'core');
}
//Adding shortcode...
add_shortcode('eshop_show_ebs_response', 'eshop_show_ebs_response');
//Function for Shortcode...
function eshop_show_ebs_response(){
    include_once 'response.php';
    return eshop_ebs_response($_GET);
}
//Scripts Enqueue
function ebsgateway_adds_to_the_head(){
wp_register_script( 'ebsminscriptjs', plugins_url( '/ebs-mg-gateway/jquery.min.js', __FILE__));
wp_register_script( 'ebsscriptjs', plugins_url( '/ebs-mg-gateway/script.js', __FILE__));
wp_enqueue_script('jquery');
wp_enqueue_script( 'ebsminscriptjs' );
wp_enqueue_script( 'ebsscriptjs' );
}
add_action( 'wp_enqueue_scripts', 'ebsgateway_adds_to_the_head' );
function ebs_box($eshopoptions) {
	/*
	* the meta box content, obviously you have to set up the required fields for your gateway here
	*/
	if(isset($eshopoptions['ebs'])){
		$eshopebs = $eshopoptions['ebs']; 
	}else{
		$eshopebs['account_id']='';
		$eshopebs['secret_key']='';
		$eshopebs['return_url']='';
        $eshopebs['mode']='';
	}
	//add the image
	$eshopmerchantimgpath=WP_PLUGIN_DIR.'/ebs-mg-gateway/ebs.png';
	$eshopmerchantimgurl=WP_PLUGIN_URL.'/ebs-mg-gateway/ebs.png';
	$dims[3]='';
	if(file_exists($eshopmerchantimgpath))
	$dims=getimagesize($eshopmerchantimgpath);
	echo '<fieldset>';
	echo '<p class="eshopgatebs"><img src="'.$eshopmerchantimgurl.'" '.$dims[3].' alt="ebs" title="ebs" /></p>'."\n";
?>  
	<p class="cbox"><input id="eshop_methodebs" name="eshop_method[]" onclick="return ebsscript();" type="checkbox" value="ebs"<?php if(in_array('ebs',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodebs" class="eshopmethod"><?php _e('Accept payment by EBS','eshop'); ?></label></p>
	<script>
	function ebsscript(){if(document.getElementById('eshop_methodebs').checked){jQuery("#eshop_ebsaccount_id").attr("disabled",true);jQuery("#eshop_ebssecret_key").attr("disabled",true);jQuery("#eshop_ebsreturn_url").attr("disabled",true);jQuery("#eshop_ebsmode").attr("disabled",true);}else{jQuery("#eshop_ebsaccount_id").removeAttr("disabled");jQuery("#eshop_ebssecret_key").removeAttr("disabled");jQuery("#eshop_ebsreturn_url").removeAttr("disabled");jQuery("#eshop_ebsmode").removeAttr("disabled");}}
	</script>
	<label for="eshop_accountid"><?php _e('Account ID','eshop'); ?></label><input id="eshop_ebsaccount_id" name="ebs[account_id]" type="text" value="<?php echo $eshopebs['account_id']; ?>" size="50" maxlength="50" /><br/>
	<label for="eshop_secretkey"><?php _e('Secret Key','eshop'); ?></label><input id="eshop_ebssecret_key" name="ebs[secret_key]" type="text" value="<?php echo $eshopebs['secret_key']; ?>" size="50" maxlength="50" /><br />
	<label for="eshop_returnurl"><?php _e('Return/Success URL','eshop'); ?></label><input id="eshop_ebsreturn_url" name="ebs[return_url]" type="text" value="<?php echo $eshopebs['return_url']; ?>" size="50" maxlength="100" /><br />
    <label for="eshop_mode"><?php _e('Mode','eshop');?></label><select id="eshop_ebsmode" name="ebs[mode]" value="<?php echo $eshopebs['mode']; ?>" ><option value="TEST">TEST</option><option value="LIVE">LIVE</option></select><br/><br/>
	<table align="left" border="4"><tr><th style="font-size:24px;"><u>**NOTE**</u><br/></th></tr><tr><td style="font-family:Lucida Handwriting; font-size:24px; color:#BBB222;">This plugin is the <i><a href="#">DEMO</a></i> version</u>. <br/><br/>To get control over this plugin, goto <i><a href="http://www.skrilleshopplugin.com/product/ebs-merchant-gateway-for-eshop/" target="_blank">EBS Merchant Gateway For eShop</a></i> and <br/><br/> grab one copy for your site starting from $4.5 only & install freshly onto your site  <br/><br/> to start sales on your eShop store. Hurry Up!<br/><br/><hr/>For troubleshooting issues & updates about this plugin, goto <br/><br/> <a href="http://lchrajkumar.tumblr.com" target="_blank">L.Ch.Rajkumar@Tumblr</a> and follow me/ask me there.</td></tr></table>
	
	</fieldset>
<?php
}

add_filter('eshop_setting_merchant_save','ebssave',10,2);
function ebssave($eshopoptions,$posted){
	/*
	* save routine for the fields you added above
	*/
	global $wpdb;
    $eshopoptions['ebs']=$ebspost;
	$ebs = $eshopoptions['ebs'];
	return $eshopoptions;
}

add_action('eshop_include_mg_ipn','eshopebs');
function eshopebs($eshopaction){
	/*
	* adding the necessary link for the instant payment notification of your gateway
	*/
	if($eshopaction=='ebsipn'){
		include_once WP_PLUGIN_DIR.'/ebs-mg-gateway/ipn.php';
	}
}

add_filter('eshop_merchant_img_ebs','ebsimg');
function ebsimg($array){
	/*
	* adding the image for this gateway, for use on the front end of the site
	*/
	$array['path']=WP_PLUGIN_DIR.'/ebs-mg-gateway/ebs.png';
	$array['url']=WP_PLUGIN_URL.'/ebs-mg-gateway/ebs.png';
	return $array;
}
add_filter('eshop_mg_inc_path','ebspath',10,2);
function ebspath($path,$paymentmethod){
	/*
	* adding another necessary link for the instant payment notification of your gateway
	*/
	if($paymentmethod=='ebs')
		return WP_PLUGIN_DIR.'/ebs-mg-gateway/ipn.php';
	return $path;
}
add_filter('eshop_mg_inc_idx_path','ebsidxpath',10,2);
function ebsidxpath($path,$paymentmethod){
	/*
	* adding the necessary link to the class for this gateway
	*/
	if($paymentmethod=='ebs')
		return WP_PLUGIN_DIR.'/ebs-mg-gateway/ebs-class.php';
	return $path;
}
//message on fail.
add_filter('eshop_show_success', 'eshop_ebs_return_fail',10,3);
function eshop_ebs_return_fail($echo, $eshopaction, $postit){
	/*
	* failed payment, you can add in details for this, will need tweaking for your gateway
	*/
	//these are the successful codes, all others fail
	$ebsrescodes=array('00','08','10','11','16');
	if($eshopaction=='ebsipn'){
		if($postit['ebsTrxnStatus']=='False' && !in_array($postit['ebsresponseCode'],$ebsrescodes))
			$echo .= '<p>There was a problem with your order, please contact admin@ ... quoting Error Code '.$postit['ebsresponseCode']."</p>\n";
	}
	return $echo;
}
?>