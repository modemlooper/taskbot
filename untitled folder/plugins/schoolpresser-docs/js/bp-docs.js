window.schoolpresserDocs = {};
( function( window, $, that ) {

	// Constructor.
	that.init = function() {
		that.cache();
		that.bindEvents();
	}

	// Cache all the things.
	that.cache = function() {
		that.$c = {
			window: $( window ),
			body: $( 'body' ),
		};
	}

	// Combine all events.
	that.bindEvents = function() {

		$(".scrollbar-inner").scrollbar({
			autoScrollSize: true,
		});

		if( getURLParameter('add') === 'undefined' ) {
			$('.upload-form').hide();
		}

        that.$c.body.on( 'click', 'a.document-upload', function() {
            $( '.upload-form' ).slideToggle('fast');
        });

		that.$c.body.on( 'click', 'a#doc-delete-link', function(e) {
			if( confirm('Are you sure you want to delete this document?') ){
			 } else {
				 e.preventDefault();
			 }
        });

		that.$c.body.on( 'click', 'span.tool-tip', function(e) {
			alert( $(this).data('help') );
		});

		initialize_file();

        // $('select').on('change', function(e) {
        //     $( this ).find( 'option:not([value='+ this.value +'])' ).removeAttr('selected');
        //     $( this ).find( 'option[value='+ this.value +']' ).attr('selected','selected');
        // });

		// that.$c.body.on( 'click', '.document-list-tabs li a', function(e) {
		// 	e.preventDefault();
		// 	alert('tab');
		// });

    }

	function initialize_file() {

		$.ajaxSetup({ headers: { 'X-WP-Nonce': wpApiSettings.nonce } });

		var upload = $('#fileupload').fileupload({
			dataType: 'json',
			replaceFileInput: false,
			singleFileUploads: true,
			maxNumberOfFiles: 1,
			sequentialUploads: false,
			limitMultiFileUploads: 1,
			limitConcurrentUploads: 1,
			autoUpload: false,
			add: function (e, data) {
				var form = e;
				$('#edit-doc').remove();
				data.context = $('<button/>').text('Upload')
                .appendTo('#submit-wrap')
                .click(function () {
					var groups = $('.group-check input').serializeArray();
					var subjects = $('.subject-check input').serializeArray();
					var types = $('.type-check input').serializeArray();
					if ( '' === $('.document-title').val() ||
						 '' === $('.document-description').val() ||
						 groups.length <= 0 ||
						 subjects.length <= 0 ||
					 	 types.length <= 0 ) {
							form.preventDefault();
							alert('You must fill out all * required fields.');
							$('.document-title').focus();
							return false;
					} else {
						data.formData = $('#document-form').serializeArray();
	                    data.context = $('<button/>').text('Uploading...').replaceAll($(this));
	                    data.submit();
					}
                });
			},
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$('#progress .bar').css(
					'width',
					progress + '%'
				);
			},
			progressServerRate: 0.3,
			done: function (e, data) {
				console.log('Upload finished.');

				$(':input','.upload-form')
				 .not(':button, :submit, :reset, :hidden')
				 .val('')
				 .removeAttr('checked')
				 .removeAttr('selected');

				 $( '.document-file' ).val('');
				 $( 'option' ).removeAttr('selected','selected');
				 $( 'option[value=0]' ).attr('selected','selected');
				 $( '#progress .bar' ).css( 'width', '0' );
				 $( '.tagit-choice' ).remove();
				 data.context = $('#submit-wrap button').text('Upload Finished');
				 data.context = $('#submit-wrap button').remove();

				 if (data.result.error) {
					 alert(data.result.error);
				 } else {
					 $( '.upload-form' ).slideToggle('fast');
					 if ( $('#document-form').hasClass('edit') ) {
						 $('ul.item-list').html(data.result);
					 } else {
						 $('ul.item-list').prepend(data.result);
					 }
				 }
			}
		});


		$('#edit-doc').click( function(e){
		    e.preventDefault();

			var formData = new FormData($('#document-form')[0]);
			var button = e.target;
			var groups = $('.group-check input').serializeArray();

			if ( '' === $('.document-title').val() ||
				 '' === $('.document-description').val() ||
			   		groups.length <= 0 ) {
					alert('You must enter all required fields.');
					$('.document-title').focus();
					return false;
			} else {

				$(button).text('Saving...');

				$.ajax({
					url: wpApiSettings.root + 'schoolpresser/v1/documents',
					data: formData,
					type: 'POST',
					cache: false,
					contentType: false,
					processData: false,
					success: function(data){
						$(button).text('Save');
						$('ul.item-list').html(data);
					}
				});
			}

		});




	}


	$(document).ready(function() {

		var tagcache = {};

		 $('#doc-tags').tagit({
			 fieldName: 'doc-tags',
			 singleField: true,
			 allowSpaces: true,
			 autocomplete: {
				 minLength: 2,
				 source: function( request, response ) {

					if ( request.term in tagcache ) {
						choices = subtractArray(tagcache[ request.term ], $('#doc-tags').tagit('assignedTags') );
					  	response( choices );
					  	return;
					}
					$.ajax( {
						 url: wpApiSettings.root + 'schoolpresser/v1/documents/tags',
						 dataType: "json",
						 success: function( data ) {

							 var filter = request.term.toLowerCase();
							 var choices = $.grep(data, function(element) {
								 return (element.toLowerCase().indexOf(filter) === 0);
							 });
							 choices = subtractArray(choices, $('#doc-tags').tagit('assignedTags') );

							 tagcache[ request.term ] = choices;
						     response( choices );
						 }
					} );
		         },
			 },
		 });

	 });

	 $(document).on('facetwp-loaded', function() {
         $('.facetwp-facet').each(function() {
             var facet_name = $(this).attr('data-name');
             var facet_label = FWP.settings.labels[facet_name];
             if ($('.facet-label[data-for="' + facet_name + '"]').length < 1) {
                 $(this).before('<h3 class="facet-label" data-for="' + facet_name + '">' + facet_label + '</h3>');
             }
         });
     });

	 // Engage!
	 $( that.init );

})( window, jQuery, window.schoolpresserDocs );

function getURLParameter(name) {
  return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))|| 'undefined'
}

function subtractArray(a1, a2) {
	var result = [];
	for (var i = 0; i < a1.length; i++) {
		if ($.inArray(a1[i], a2) == -1) {
			result.push(a1[i]);
		}
	}
	return result;
}
