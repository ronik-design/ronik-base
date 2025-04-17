
<?php 
class RbpHelper{
    // Write error logs cleanly.
	public function ronikdesigns_write_log_devmode($log, $severity_level='low', $error_type='general') {
        // Prevent logging outside admin interface.
        if (!is_admin()) {
			return;
		}

    
		$debugger_error_type = 'general';
		if(isset($_COOKIE['RbpDebug']) && array_key_exists( 'RbpDebug', $_COOKIE) && $_COOKIE['RbpDebug'] == 'valid'){
			error_log(print_r( 'DEBUG ACTIVATED', true));
		} else {
			if(isset($_COOKIE['RbpDebug']) && array_key_exists( 'RbpDebug', $_COOKIE) && $_COOKIE['RbpDebug'] == 'rbp_media_cleaner' ){
				error_log(print_r( 'DEBUG ACTIVATED rbp_media_cleaner', true));
				$debugger_error_type = 'rbp_media_cleaner';
			}
		}

		$cookie_severity_level = 'critical';
		if(isset($_COOKIE['RbpSeverity']) && array_key_exists( 'RbpSeverity', $_COOKIE) && $_COOKIE['RbpSeverity'] == 'low'){
			$cookie_severity_level = 'low';
		}

        // if($severity_level == 'low') {
        //     return false;
        // }
		if($debugger_error_type !== $error_type){
			return false;
		}


		$f_error_email = get_field('error_email', 'option');
        if(!$f_error_email){
            $f_error_email = 'kevin@ronikdesign.com';
        }
		// Lets run a backtrace to get more useful information.
		$t = debug_backtrace();
		$t_file = 'File Path Location: ' . $t[0]['file'];
		$t_line = 'On Line: ' .  $t[0]['line'];


		//  Low, Medium, High, and Critical
		if( $severity_level == 'critical' || $severity_level == $cookie_severity_level){
			if ($f_error_email) {
				// Remove whitespace.
				$f_error_email = str_replace(' ', '', $f_error_email);
				$to = $f_error_email;
				$subject = 'Error Found';
				$headers = array('Content-Type: text/html; charset=UTF-8');
				$body = 'Severity Level: '. $severity_level . ' User id: '. get_current_user_id() .' Website URL: '. $_SERVER['HTTP_HOST'] .'<br><br>Error Message: ' . $log . '<br><br>' . $t_file . '<br><br>' . $t_line;
				wp_mail($to, $subject, $body, $headers);
			}
		}
		if (is_array($log) || is_object($log)) {
			error_log(print_r('<----- ' . $log . ' ----->', true));
			error_log(print_r( 'Severity Level:' . $severity_level , true));
			error_log(print_r( 'USER ID:' . get_current_user_id() , true));
			error_log(print_r( $t_file , true));
			error_log(print_r( $t_line , true));
			error_log(print_r('<----- END LOG '.$log.' ----->', true));
			error_log(print_r('   ', true));

		} else {
			error_log(print_r('<----- ' . $log . ' ----->', true));
			error_log(print_r( 'Severity Level:' . $severity_level , true));
			error_log(print_r( 'USER ID:' . get_current_user_id() , true));
			error_log(print_r( $t_file , true));
			error_log(print_r( $t_line , true));
			error_log(print_r('<----- END LOG '.$log.' ----->', true));
			error_log(print_r('   ', true));
		}
	}

}