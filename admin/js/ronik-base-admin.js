(function( $ ) {
	'use strict';

	function initUnusedMedia() {
		// Exit Function if node doesnt exist.
		if ($("#page_media_cleaner_field").length == 0) {
			return;
		}
		$('<span name="button" class="page_unused_media__link" href="#" style="cursor:pointer;background: #7210d4;border: none;padding: 10px;color: #fff;border-radius: 5px;">Init Unused Media Migration</span>').appendTo( $('#page_media_cleaner_field') );
		// Trigger rejection.
		$(".page_unused_media__link").on('click', function(){
			alert('Data is processing. Please do not reload!');
			$('.wp-core-ui').css({'pointer-events':'none', 'opacity':.5});

			$.ajax({
				type: 'post',
				url: wpVars.ajaxURL,
				data: {
					action: 'do_init_unused_media_migration',
					nonce: wpVars.nonce,
				},
				dataType: 'json',
				success: data => {
					if(data.success){
						console.log('success');
						console.log(data);
						$('.wp-core-ui').css({'pointer-events':'', 'opacity':''});
						alert('Data processing. Page will reload after processing! Please do not reload!');
						setTimeout(() => {
							window.location.reload(true);
						}, 500);
					} else{
						console.log(data.success);
						console.log(data.data);
						if(data.data == 'No rows found!'){
							alert('Great News! No un-detached images were found. Please try again later!');
						} else{
							alert('Whoops! Something went wrong! Please try again later!');
						}
						$('.wp-core-ui').css({'pointer-events':'', 'opacity':''});
						setTimeout(() => {
							window.location.reload(true);
						}, 500);
					}
				},
				error: err => {
					console.log(err);
					$('.wp-core-ui').css({'pointer-events':'', 'opacity':''});
					alert('Whoops! Something went wrong! Please try again later!');
					// Lets Reload.
					setTimeout(() => {
						window.location.reload(true);
					}, 500);
				}
			});
		});
	}

	function deleteUnusedMedia() {
		// Exit Function if node doesnt exist.
		if ($("#page_media_cleaner_field").length == 0) {
			return;
		}
		$('<span name="button" class="page_delete_media__link" href="#" style="margin-left:10px;cursor:pointer;background: #d4104e;border: none;padding: 10px;color: #fff;border-radius: 5px;">Delete Unused Media</span>').appendTo( $('#page_media_cleaner_field') );
		// Trigger rejection.
		$(".page_delete_media__link").on('click', function(){
			// alert('Please make sure you have reviewed the images listed below. If you see any image that you want to keep please remove from the repeater row and click update and then click the delete button.');
			$('.wp-core-ui').css({'pointer-events':'none', 'opacity':.5});

			$.ajax({
				type: 'post',
				url: wpVars.ajaxURL,
				data: {
					action: 'do_init_remove_unused_media',
					nonce: wpVars.nonce,
				},
				dataType: 'json',
				success: data => {
					if(data.success){
						console.log('success');
						console.log(data);
						$('.wp-core-ui').css({'pointer-events':'', 'opacity':''});
						alert('Data processing. Page will reload after processing! Please do not reload!');
						setTimeout(() => {
							window.location.reload(true);
						}, 500);
					} else{
						console.log('error');
						console.log(data);
						$('.wp-core-ui').css({'pointer-events':'', 'opacity':''});
						alert('Whoops! Something went wrong! Please try again later!');
						setTimeout(() => {
							window.location.reload(true);
						}, 500);
					}
				},
				error: err => {
					console.log(err);
					$('.wp-core-ui').css({'pointer-events':'', 'opacity':''});
					alert('Whoops! Something went wrong! Please try again later!');
					// Lets Reload.
					setTimeout(() => {
						window.location.reload(true);
					}, 500);
				}
			});
		});
	}


	// Load JS once windows is loaded. 
	$(window).on('load', function(){
		// SetTimeOut just incase things havent initialized just yet.
		setTimeout(() => {
			initUnusedMedia();
			deleteUnusedMedia();
		}, 250);
	});
})( jQuery );
