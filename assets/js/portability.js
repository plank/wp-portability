(function($,document,window){
	var chunksize = 500;
	var count = 0;
	var running = false;
	var timer = null;

	var sendPost = function(action){
		$.ajax({
			url: wp_ajax.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				nonce: wp_ajax.ajaxnonce,
				action: action,
				limit: chunksize
			},
		})
		.error(function(x,t,m) {
			console.log("error",t);
			if(t == 'timeout'){
				sendPost(action);
			}
		})
		.success(function(result) {
			count += parseInt(result.data.count,10);
			if(parseInt(result.data.count,10) == chunksize){
				sendPost(action);
			}else{
				alert('finished running '+action+'. Updated '+count+' posts.');
				finishSend();
			}
		});
	};

	var prepSend = function(){
		count = 0;
		running = true;
	};
	var finishSend = function(){
		count = 0;
		running = false;
		$('.spinner').hide();
	};

	$(document).ready(function(){
		$('#plk-wp-insert-shortcodes').click(function(e){
			e.preventDefault();
			if(!running){
				prepSend();
				sendPost('insert_shortcodes');
				$(this).next('.spinner').show();
			}
		});

		$('#plk-wp-purge-shortcodes').click(function(e){
			e.preventDefault();
			if(!running){
				prepSend();
				sendPost('purge_shortcodes');
				$(this).next('.spinner').show();
			}
		});
	});

	window.onbeforeunload = function(e){
		if(running){
			var message = "The database update has not yet completed. Stopping the process prematurely could corrupt your data.";
			e = e || window.event;
			// For IE and Firefox
			if (e) {
			e.returnValue = message;
			}

			// For Safari
			return message;
		}
	};

})(jQuery,document,window);
