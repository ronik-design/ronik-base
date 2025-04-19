<?php
namespace Ronik\Base;

use Ronik\Base\RbpHelper;

class RonikBaseHelper{
    // Semi Imitates the loose LIKE%% Comparison
    public function ronik_compare_like($a_value , $b_value){
        if(stripos($a_value, $b_value) !== FALSE){
            return true;
        } else {
            return false;
        }
    }


    // Function that detects if on local mode.
    public function localValidator(){
        // Check for local string in host.
        $is_local =  str_contains( $_SERVER['HTTP_HOST'] , 'local');
        // If false we check to see if the REMOTE_ADDR is the default local host ip address..
        if(!$is_local){
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                $is_local = true;
            } else {
                $is_local = false;
            }
        }
        return $is_local;
    }

    // Creates an encoded svg for src, lazy loading.
    // This pretty much creates a basebone svg structure.
    public function ronikdesignsbase_svgplaceholder($imgacf=null) {
        $iacf = $imgacf;
        if($iacf){
            if($iacf['width']){
                $width = $iacf['width'];
            }
            if($iacf['height']){
                $height = $iacf['height'];
            }
            $viewbox = "width='{$width}' height='{$height}' viewBox='0 0 {$width} {$height}'";
        } else{
            $viewbox = "viewBox='0 0 100 100'";
        }
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' {$viewbox}%3E%3C/svg%3E";
    }
    

    public function ronikdesigns_increase_memory(){
        $rbpHelper = new RbpHelper;
        // Lets us set the max_execution_time to 1hr
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 13a, ronikdesigns_increase_memory First max_execution_time: ' . ini_get('max_execution_time'), 'low', 'rbp_media_cleaner');
        @set_time_limit( intval( 3600*2 ) );
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 13b, ronikdesigns_increase_memory Rewrite max_execution_time: ' . ini_get('max_execution_time'), 'low', 'rbp_media_cleaner');
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 13c, ronikdesigns_increase_memory First memory_limit: ' . ini_get('memory_limit'), 'low', 'rbp_media_cleaner');
        ini_set('memory_limit', '5024M');
        $rbpHelper->ronikdesigns_write_log_devmode('Media Cleaner: Ref 13d, ronikdesigns_increase_memory Rewrite memory_limit: ' . ini_get('memory_limit'), 'low', 'rbp_media_cleaner');
    }

	// Write error logs cleanly.
    public function ronikdesigns_write_log($log) {
		// $f_error_email = get_field('error_email', 'option');
        $f_error_email = get_option( 'admin_email' );
		if ($f_error_email) {
			// Remove whitespace.
			$f_error_email = str_replace(' ', '', $f_error_email);
			// Lets run a backtrace to get more useful information.
			$t = debug_backtrace();
			$t_file = 'File Path Location: ' . $t[0]['file'];
			$t_line = 'On Line: ' .  $t[0]['line'];
			$to = $f_error_email;
			$subject = 'Error Found';
			$body = 'Error Message: ' . $log . '<br><br>' . $t_file . '<br><br>' . $t_line;
			$headers = array('Content-Type: text/html; charset=UTF-8');
			wp_mail($to, $subject, $body, $headers);
		}
		if (is_array($log) || is_object($log)) {
			error_log(print_r('<----- ' . $log . ' ----->', true));
		} else {
			error_log(print_r('<----- ' . $log . ' ----->', true));
		}
	}

	// Write error logs cleanly.
	public function ronikdesigns_write_log_devmode($log, $severity_level='low') {
		if($severity_level == 'low'){
			return false;
		}
		// $f_error_email = get_field('error_email', 'option');
        $f_error_email = get_option( 'admin_email' );
		// Lets run a backtrace to get more useful information.
		$t = debug_backtrace();
		$t_file = 'File Path Location: ' . $t[0]['file'];
		$t_line = 'On Line: ' .  $t[0]['line'];

		//  Low, Medium, High, and Critical
		if( $severity_level == 'critical' ){
			if ($f_error_email) {
				// Remove whitespace.
				$f_error_email = str_replace(' ', '', $f_error_email);
				$to = $f_error_email;
				$subject = 'Error Found';
				$headers = array('Content-Type: text/html; charset=UTF-8');
				$body = 'Website URL: '. $_SERVER['HTTP_HOST'] .'<br><br>Error Message: ' . $log . '<br><br>' . $t_file . '<br><br>' . $t_line;
				wp_mail($to, $subject, $body, $headers);
			}
		}
		if (is_array($log) || is_object($log)) {
			error_log(print_r('<----- ' . json_encode($log) . ' ----->', true));
			error_log(print_r( $t_file , true));
			error_log(print_r( $t_line , true));
			error_log(print_r('<----- END LOG '.json_encode($log).' ----->', true));
			error_log(print_r('   ', true));

		} else {
			error_log(print_r('<----- ' . $log . ' ----->', true));
			error_log(print_r( $t_file , true));
			error_log(print_r( $t_line , true));
			error_log(print_r('<----- END LOG '.$log.' ----->', true));
			error_log(print_r('   ', true));
		}
	}
}
