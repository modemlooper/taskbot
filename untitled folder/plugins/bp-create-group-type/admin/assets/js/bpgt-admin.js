jQuery(document).ready(function(){
	
	//Add Group Types
	jQuery(document).on('click', '#add-grp-type', function(){
		var name = jQuery( '#group-type-name' ).val();
		var slug = jQuery( '#group-type-slug' ).val();
		var desc = jQuery( '#group-type-desc' ).val();

		if( name == '' ) {
			jQuery( '.group-type-add-error-name' ).fadeIn('slow').delay(10000);
			jQuery( '.group-type-add-error-name' ).fadeOut('slow');
		} else {
			if( slug == '' ) {
				lower_case_name = name.toLowerCase();
				slug = lower_case_name.replace(/ /g, "-");
			}
			var all_bp_group_types = jQuery('#all_bp_group_types').val();
			if( all_bp_group_types.length != 0 ) {
				bp_group_types = JSON.parse( all_bp_group_types );

				if (jQuery.inArray(slug, bp_group_types) != -1) {
					jQuery( '.group-type-add-error' ).fadeIn('slow').delay(10000);
					jQuery( '.group-type-add-error' ).fadeOut('slow');
				} else {
					jQuery( '.ajax-loader' ).show();
					jQuery.post(
						ajaxurl,
						{
							'action' : 'bpgt_add_group_type',
							'name' : name,
							'slug' : slug,
							'desc' : desc,
							'all_bp_group_types' : all_bp_group_types
						},
						function( response ){
							if( response['msg'] == 'group-type-added' ) {
								jQuery( '.ajax-loader' ).hide();
								jQuery( '.group-type-add-success' ).fadeIn('slow').delay(10000);
								jQuery( '.group-type-add-success' ).fadeOut('slow');
								var html = '';
								html += '<tr class="bpgt-'+slug+'">';
								html += '<td class="name column-name has-row-actions column-primary">';
								html += '<strong>';
								html += '<a class="row-title" href="javascript:void(0);" id="name-'+slug+'">';
								html += name;
								html += '</a>';
								html += '</strong><br>';
								html += '<div class="row-actions">';
								html += '<span class="edit">';
								html += '<a class="edit-bpgt" href="javascript:void(0);" id="'+slug+'">Edit</a> | ';
								html += '</span>';
								html += '<span class="delete">';
								html += '<a class="dlt-bpgt" href="javascript:void(0);" id="'+slug+'">Delete</a>';
								html += '</span>';
								html += '</div>';
								html += '</td>';
								html += '<td class="column-description" id="desc-'+slug+'">'+desc+'</td>';
								html += '<td class="column-slug" id="slug-'+slug+'">'+slug+'</td>';
								//html += '<td class="column-posts">2</td>';
								html += '</tr>';
								html += '<!-- Row Editor -->';
								html += '<tr class="inline-edit-row bpgt-editor" id="edit-bpgt-'+slug+'">';
								html += '<td colspan="3" class="colspanchange">';
								html += '<fieldset>';
								html += '<legend class="inline-edit-legend">';
								html += 'Edit '+name;
								html += '</legend>';
								html += '<div class="inline-edit-col">';
								html += '<label>';
								html += '<span class="title">Name</span>';
								html += '<span class="input-text-wrap">';
								html += '<input id="'+slug+'-name" class="ptitle" value="'+name+'" type="text">';
								html += '</span>';
								html += '</label>';
								html += '<label>';
								html += '<span class="title">Slug</span>';
								html += '<span class="input-text-wrap">';
								html += '<input id="'+slug+'-slug" class="ptitle" value="'+slug+'" type="text">';
								html += '</span>';
								html += '</label>';
								html += '<label>';
								html += '<span class="title">Description</span>';
								html += '<span class="input-text-wrap">';
								html += '<textarea id="'+slug+'-desc">'+desc+'</textarea>';
								html += '</span>';
								html += '</label>';
								html += '</div>';
								html += '</fieldset>';
								html += '<p class="inline-edit-save submit">';
								html += '<button type="button" class="close button-secondary alignleft">Cancel</button>';
								html += '<button class="bpgt-update button-primary alignright" id="'+slug+'">Update Group Type</button>';
								html += '<span class="ajax-loader alignright" id="ajax-loader-for-'+slug+'">';
								html += '<i class="fa fa-spinner fa-spin"></i>';
								html += '</span>';
								html += '<br class="clear">';
								html += '</p>';
								html += '</td>';
								html += '</tr>';

								jQuery( html ).prependTo( '.bpgt-list' );
								jQuery( '.bpgt-not-found' ).hide();
								jQuery( '#group-type-name' ).val('');
								jQuery('#all_bp_group_types').val( response['all_bp_group_types'] );
							}
						},
						"JSON"
					);
				}
				
			} else {
					jQuery( '.ajax-loader' ).show();
					jQuery.post(
						ajaxurl,
						{
							'action' : 'bpgt_add_group_type',
							'name' : name,
							'slug' : slug,
							'desc' : desc,
							'all_bp_group_types' : all_bp_group_types
						},
						function( response ){
							if( response['msg'] == 'group-type-added' ) {
								jQuery( '.ajax-loader' ).hide();
								jQuery( '.group-type-add-success' ).fadeIn('slow').delay(10000);
								jQuery( '.group-type-add-success' ).fadeOut('slow');
								var html = '';
								html += '<tr class="bpgt-'+slug+'">';
								html += '<td class="name column-name has-row-actions column-primary">';
								html += '<strong>';
								html += '<a class="row-title" href="javascript:void(0);" id="name-'+slug+'">';
								html += name;
								html += '</a>';
								html += '</strong><br>';
								html += '<div class="row-actions">';
								html += '<span class="edit">';
								html += '<a class="edit-bpgt" href="javascript:void(0);" id="'+slug+'">Edit</a> | ';
								html += '</span>';
								html += '<span class="delete">';
								html += '<a class="dlt-bpgt" href="javascript:void(0);" id="'+slug+'">Delete</a>';
								html += '</span>';
								html += '</div>';
								html += '</td>';
								html += '<td class="column-description" id="desc-'+slug+'">'+desc+'</td>';
								html += '<td class="column-slug" id="slug-'+slug+'">'+slug+'</td>';
								//html += '<td class="column-posts">2</td>';
								html += '</tr>';
								html += '<!-- Row Editor -->';
								html += '<tr class="inline-edit-row bpgt-editor" id="edit-bpgt-'+slug+'">';
								html += '<td colspan="3" class="colspanchange">';
								html += '<fieldset>';
								html += '<legend class="inline-edit-legend">';
								html += 'Edit '+name;
								html += '</legend>';
								html += '<div class="inline-edit-col">';
								html += '<label>';
								html += '<span class="title">Name</span>';
								html += '<span class="input-text-wrap">';
								html += '<input id="'+slug+'-name" class="ptitle" value="'+name+'" type="text">';
								html += '</span>';
								html += '</label>';
								html += '<label>';
								html += '<span class="title">Slug</span>';
								html += '<span class="input-text-wrap">';
								html += '<input id="'+slug+'-slug" class="ptitle" value="'+slug+'" type="text">';
								html += '</span>';
								html += '</label>';
								html += '<label>';
								html += '<span class="title">Description</span>';
								html += '<span class="input-text-wrap">';
								html += '<textarea id="'+slug+'-desc">'+desc+'</textarea>';
								html += '</span>';
								html += '</label>';
								html += '</div>';
								html += '</fieldset>';
								html += '<p class="inline-edit-save submit">';
								html += '<button type="button" class="close button-secondary alignleft">Cancel</button>';
								html += '<button class="bpgt-update button-primary alignright" id="'+slug+'">Update Group Type</button>';
								html += '<span class="ajax-loader alignright" id="ajax-loader-for-'+slug+'">';
								html += '<i class="fa fa-spinner fa-spin"></i>';
								html += '</span>';
								html += '<br class="clear">';
								html += '</p>';
								html += '</td>';
								html += '</tr>';

								jQuery( html ).prependTo( '.bpgt-list' );
								jQuery( '.bpgt-not-found' ).hide();
								jQuery( '#group-type-name' ).val('');
								jQuery('#all_bp_group_types').val( response['all_bp_group_types'] );
							}
						},
						"JSON"
					);
				}
		}
	});

	//Delete Group Types
	jQuery(document).on('click', '.dlt-bpgt', function(){
		var slug = jQuery( this ).attr( 'id' );
		jQuery( this ).html( 'Deleting..' );
		jQuery( this ).closest('tr').css( 'background-color', '#FF9999' );
		jQuery.post(
			ajaxurl,
			{
				'action' : 'bpgt_delete_group_type',
				'slug' : slug
			},
			function( response ){
				jQuery( '.bpgt-'+slug ).remove();
				jQuery( '#edit-bpgt-'+slug ).remove();
			}
		);
	});

	//Search Buddypress Group Types
	jQuery(document).on('click', '#search-bpgt', function(){
		var search_txt = jQuery( '#group-type-search-input' ).val();
		jQuery( this ).val( 'Searching..' );
		jQuery.post(
			ajaxurl,
			{
				'action' : 'bpgt_search_group_type',
				'search_txt' : search_txt
			},
			function( response ) {
				jQuery( '#search-bpgt' ).val( 'Search Group Types' );
				var found = response['found'];
				var html = '';
				if( found == 'no' ) {
					html += '<tr class="bpgt-not-found">';
					html += '<td colspan="4">Group Types Not Found!</td>';
					html += '</tr>';
					jQuery( '.bpgt-list' ).html( html );
				} else if( found == 'yes' ) {
					var group_types = response['group_types'];
					for( i in group_types ) {
						var name = group_types[i]['name'];
						var slug = group_types[i]['slug'];
						var desc = group_types[i]['desc'];

						html += '<tr class="bpgt-'+slug+'">';
						html += '<td class="name column-name has-row-actions column-primary">';
						html += '<strong>';
						html += '<a class="row-title" href="javascript:void(0);">'+name+'</a>';
						html += '</strong>';
						html += '<br>';
						html += '<div class="row-actions">';
						html += '<span class="edit"><a class="edit-bpgt" href="javascript:void(0);" id="'+slug+'">Edit</a> | </span>';
						html += '<span class="delete"><a class="dlt-bpgt" href="javascript:void(0);" id="'+slug+'">Delete</a></span>';
						html += '</div>';
						html += '</td>';
						html += '<td class="column-description">'+desc+'</td>';
						html += '<td class="column-slug">'+slug+'</td>';
						//html += '<td class="column-posts">2</td>';
						html += '</tr>';
						html += '<!-- Row Editor -->';
						html += '<tr class="inline-edit-row bpgt-editor" id="edit-bpgt-'+slug+'">';
						html += '<td colspan="3" class="colspanchange">';
						html += '<fieldset>';
						html += '<legend class="inline-edit-legend">';
						html += 'Edit '+name;
						html += '</legend>';
						html += '<div class="inline-edit-col">';
						html += '<label>';
						html += '<span class="title">Name</span>';
						html += '<span class="input-text-wrap">';
						html += '<input id="'+slug+'" class="ptitle" value="'+name+'" type="text">';
						html += '</span>';
						html += '</label>';
						html += '<label>';
						html += '<span class="title">Slug</span>';
						html += '<span class="input-text-wrap">';
						html += '<input id="'+slug+'-slug" class="ptitle" value="'+slug+'" type="text">';
						html += '</span>';
						html += '</label>';
						html += '<label>';
						html += '<span class="title">Description</span>';
						html += '<span class="input-text-wrap">';
						html += '<textarea id="'+slug+'-desc">'+desc+'</textarea>';
						html += '</span>';
						html += '</label>';
						html += '</div>';
						html += '</fieldset>';
						html += '<p class="inline-edit-save submit">';
						html += '<button type="button" class="close button-secondary alignleft">Cancel</button>';
						html += '<button class="bpgt-update button-primary alignright" id="'+slug+'">Update Group Type</button>';
						html += '<span class="ajax-loader alignright" id="ajax-loader-for-'+slug+'">';
						html += '<i class="fa fa-spinner fa-spin"></i>';
						html += '</span>';
						html += '<br class="clear">';
						html += '</p>';
						html += '</td>';
						html += '</tr>';
					}
					jQuery( '.bpgt-list' ).html( html );
				}
			},
			"JSON"
		);
	});

	//Edit Buddypress Group Types
	 jQuery(document).on('click', '.edit-bpgt', function(){
	 	var slug = jQuery( this ).attr( 'id' );
	 	jQuery('.bpgt-editor').hide();
	 	jQuery('#edit-bpgt-'+slug).show();
	 });

	 //Close Editor Buddypress Group Types
	 jQuery(document).on('click', '.close', function(){
	 	jQuery('.bpgt-editor').hide();
	 });

	 //Update Buddypress Group Types
	 jQuery(document).on('click', '.bpgt-update', function(){
	 	var curr_slug = jQuery( this ).attr( 'id' );
	 	jQuery('.bpgt-editor').hide();
	 	jQuery('#edit-bpgt-'+curr_slug).show();
	 	var new_name = jQuery( '#'+curr_slug+'-name' ).val();
		var new_slug = jQuery( '#'+curr_slug+'-slug' ).val();
		var new_desc = jQuery( '#'+curr_slug+'-desc' ).val();
		if( new_slug == '' ) {
			lower_case_name = new_name.toLowerCase();
			new_slug = lower_case_name.replace(/ /g, "-");
		}

		jQuery( '#ajax-loader-for-'+curr_slug ).show();
		jQuery.post(
			ajaxurl,
			{
				'action' : 'bpgt_update_group_type',
				'new_slug' : new_slug,
				'new_desc' : new_desc,
				'old_slug' : curr_slug,
				'new_name' : new_name
			},
			function( response ){
				jQuery( '#ajax-loader-for-'+curr_slug ).hide();
				jQuery('.bpgt-editor').hide();
				jQuery('#desc-'+curr_slug).html( new_desc );
				jQuery('#slug-'+curr_slug).html( new_slug );
				jQuery('#name-'+curr_slug).html( new_name );
			}
		);
	 });
});