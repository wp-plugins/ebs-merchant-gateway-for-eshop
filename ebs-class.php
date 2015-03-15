<?php
if ('ebs-class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
     
/*******************************************************************************
 *                      PHP EBS Gateway Integration Class
 *******************************************************************************
 *      Author:     L.CH.RAJKUMAR
 *      Based on:   EBS Gateway class
 *      
 *      To submit an order to EBS, have your order form POST to a file with:
 *
 *          $p = new EBS_class;
 *          $p->add_field('account_id', '0424');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_EBS_post();
 * 
 *******************************************************************************
*/

class ebs_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function ebs_class() {
       
      // initialization constructor.  Called when class is created.
      $this->last_error = '';
      $this->ipn_response = '';
    
   }
   
   function add_field($field, $value) {
      
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to EBS as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      
      $this->fields["$field"] = $value;
   }

   function submit_ebs_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to ebs.
       
       $refid = uniqid(rand());
       
      $echo= "<form method=\"post\" class=\"eshop\" action=\"".$this->autoredirect."\"><div>\n";
	/*
	*
	* Grab the standard data
	*
	*/
      foreach ($this->fields as $name => $value) {
			$pos = strpos($name, 'amount');
			if ($pos === false) {
			   $echo.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
			}else{
				$echo .= eshopTaxCartFields($name,$value);
      	    }
      }
      	/*
	  	* Changes the standard text of the redirect page.
		*/
      //$refid=uniqid(rand());
      $echo .= "<input type=\"hidden\" name=\"reference_no\" value=\"$refid\" />\n";
      $echo.='<label for="ebssubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at EBS.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ebssubmit" name="ebssubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }
	function eshop_submit_ebs_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to EBS.
            
      global $eshopoptions, $blog_id;
      $ebs = $eshopoptions['ebs'];
		$echortn='<div id="process">
         <p><strong>'.__('Please wait, your order is being processed&#8230;','eshop').'</strong></p>
	     <p>'. __('If you are not automatically redirected to EBS, please use the <em>Proceed to EBS</em> button.','eshop').'</p>
         <form method="post" id="eshopgatebs" class="eshop" action="'.$this->ebs_url.'">
          <p>';
		$replace = array("&#039;","'", "\"","&quot;","&amp;","&");
		$ebs = $eshopoptions['ebs']; 
		        
                if($eshopoptions['status']!='live'){
		
                    $echortn.='<input type="hidden" name="x_test_request" value="TRUE" />';
		}
                foreach ($this->fields as $name => $value){
                    $echortn.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
                }
                $echortn.='
         <input class="button" type="submit" id="ebssubmit" name="ebssubmit" value="'. __('Proceed to EBS &raquo;','eshop').'" /></p>
	     </form>
	  </div>';
	  	
		return $echortn;
   }
   function submit_ebs_response($response){
       $echortn='<div>
         <p><strong>'.__($_POST['ResponseMessage']).'</strong></p>
	     <p>'. __('If you are not automatically redirected to EBS, please use the <em>Proceed to EBS</em> button.','eshop').'</p>
     
          <p></div>';
			
		
	  //debug

		return $echortn;
   }
   function validate_ipn() {
      // generate the post string from the _POST vars aswell as load the
      // _POST vars into an arry so we can play with them from the calling
      // script.
      foreach ($_REQUEST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;
      }
     
   }
}  
?>