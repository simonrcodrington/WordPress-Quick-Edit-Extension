/*
 * Post Bulk Edit Script
 * Hooks into the inline post editor functionality to extend it to our custom metadata
 */

jQuery(document).ready(function($){
	
	//Prepopulating our quick-edit post info
	var $inline_editor = inlineEditPost.edit;
	inlineEditPost.edit = function(id){
		
		//call old copy 
		$inline_editor.apply( this, arguments);
		
		//our custom functionality below
		var post_id = 0;
		if( typeof(id) == 'object'){
			post_id = parseInt(this.getId(id));
		}
		
		//if we have our post
		if(post_id != 0){
			
			//find our row
			$row = $('#edit-' + post_id);
			
			//post featured
			$post_featured = $('#post_featured_' + post_id);
			post_featured_value = $post_featured.text(); 
			if(post_featured_value == 'yes'){
				$row.find('#post_featured_yes').val(post_featured_value).attr('checked', true);
			}else{
				$row.find('#post_featured_no').val(post_featured_value).attr('checked', true);
			}

			//post rating
			$post_rating = $('#post_rating_' + post_id);
			$post_rating_value = $post_rating.text();
			$row.find('#post_rating').val($post_rating_value);
			$row.find('#post_rating').children('[value="' + $post_rating_value + '"]').attr('selected', true);
			
			//post subtitle
			$post_subtitle= $('#post_subtitle_' + post_id);
			post_subtitle_value = $post_subtitle.text();
			$row.find('#post_subtitle').val(post_subtitle_value);
		
			
		}
		
	}
	
});
