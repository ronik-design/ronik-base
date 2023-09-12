<?php 

function rmc_getLineWithString_ronikdesigns($fileName, $id) {
	$f_attached_file = get_attached_file( $id );
	$pieces = explode('/', $f_attached_file ) ;
	$lines = file( urldecode($fileName) );
	foreach ($lines as $lineNumber => $line) {
		if (strpos($line, end($pieces)) !== false) {
			return $id;
		}
	}
}

error_log(print_r(get_theme_file_path(), true));


function rmc_receiveAllFiles_ronikdesigns($id){
	$f_files = scandir( get_theme_file_path() );
	$array2 = array("functions.php", "package-lock.json", ".", "..", ".DS_Store");
	$results = array_diff($f_files, $array2);

	if($results){
		foreach($results as $file){
			if (is_file(get_theme_file_path().'/'.$file)){
				$f_url = urlencode(get_theme_file_path().'/'.$file);
				$image_ids = rmc_getLineWithString_ronikdesigns( $f_url , $id);
			}
		}
	}
	return $image_ids;
}
