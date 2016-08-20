<?php
/*
Plugin Name: Extend Quick Edit
Plugin URI: https://elevate360.com.au/plugins/extend-quick-edit
Description: Extends the quick edit interface to display additional custom metadata
Version: 1.0.0
Author: Simon Codrington
Author URI: http://simoncodrington.com.au
Text Domain: post-quick-edit-extension
Domain Path: /languages
*/

class el_extend_quick_edit{
	
	private static $instance = null;
	
	public function __construct(){
		
		add_action('manage_post_posts_columns', array($this, 'add_custom_admin_column'), 10, 1); //add custom column
		add_action('manage_posts_custom_column', array($this, 'manage_custom_admin_columns'), 10, 2); //populate column
		add_action('quick_edit_custom_box', array($this, 'display_quick_edit_custom'), 10, 2); //output form elements for quickedit interface
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles')); //enqueue admin script (for prepopulting fields with JS)
		add_action('add_meta_boxes', array($this, 'add_metabox_to_posts'), 10, 2); //add metabox to posts to add our meta info
		add_action('save_post', array($this, 'save_post'), 10, 1); //call on save, to update metainfo attached to our metabox
		
	}

	
	//adds a new metabox on our single post edit screen
	public function add_metabox_to_posts($post_type, $post){
			
		add_meta_box(
			'additional-meta-box',
			__('Additional Info', 'post-quick-edit-extension'),
			array($this, 'display_metabox_output'),
			'post',
			'side',
			'default'
		);
	}
	
	//metabox output function, displays our fields, prepopulating as needed
	public function display_metabox_output($post){
		
		$html = '';
		wp_nonce_field('post_metadata', 'post_metadata_field');
		
		$post_featured = get_post_meta($post->ID, 'post_featured', true);
		$post_rating = get_post_meta($post->ID, 'post_rating', true);
		$post_subtitle = get_post_meta($post->ID, 'post_subtitle', true);

		//Featured post (radio)
		$html .= '<p>';
			$html .= '<p><strong>Featured Post?</strong></p>';
			$html .= '<label for="post_featured_no">';
				if($post_featured == 'no' || empty($post_featured)){
					$html .= '<input type="radio" checked name="post_featured" id="post_featured_no" value="no"/>';
				}else{
					$html .= '<input type="radio" name="post_featured" id="post_featured_no" value="no"/>';
				}
			$html .= ' No</label>';
			$html .= '</br>';
			$html .= '<label for="post_featured_yes">';
				if($post_featured == 'yes'){
					$html .= '<input type="radio" checked name="post_featured" id="post_featured_yes" value="yes"/>';
				}else{
					$html .= '<input type="radio" name="post_featured" id="post_featured_yes" value="yes"/>';
				}
			$html .= ' Yes</label>';
		$html .= '</p>';
		
		//Internal Rating (select)
		$html .= '<p>';
			$html .= '<p>';
				$html .= '<label for="post_rating"><strong>Post Rating</strong></label>';
			$html .= '</p>';
			$html .= '<select name="post_rating" id="post_rating" value="' . $post_rating .'" class="widefat">';
				$html .= '<option value="1" ' . (($post_rating == '1') ? 'selected' : '') . '>1</option>';
				$html .= '<option value="2" ' . (($post_rating == '2') ? 'selected' : '') . '>2</option>';
				$html .= '<option value="3" ' . (($post_rating == '3') ? 'selected' : '') . '>3</option>';
				$html .= '<option value="4" ' . (($post_rating == '4') ? 'selected' : '') . '>4</option>';
				$html .= '<option value="5" ' . (($post_rating == '5') ? 'selected' : '') . '>5</option>';
			$html .= '</select>';
			
		$html .= '</p>';
		
		
		//Subtitle (text)
		$html .= '<p>';
			$html .= '<p>';
				$html .= '<label for="post_subtitle"><strong>Subtitle</strong></label>';
			$html .= '</p>';
			$html .= '<input type="text" name="post_subtitle" id="post_subtitle" value="' . $post_subtitle .'" class="widefat"/>';
		$html .= '</p>';
		
				
		echo $html;
		
	}
	
	public function enqueue_admin_scripts_and_styles(){
		wp_enqueue_script('quick-edit-script', plugin_dir_url(__FILE__) . '/post-quick-edit-script.js', array('jquery','inline-edit-post' ));
	}
	
	//Display our custom content on the quick-edit interface, no values can be prepopulated (all done in JS)
	public function display_quick_edit_custom($column){
			
		$html = '';
		wp_nonce_field('post_metadata', 'post_metadata_field');
		
		//output post featured checkbox
		if($column == 'post_featured'){
			$html .= '<fieldset class="inline-edit-col-left clear">';
				$html .= '<div class="inline-edit-group wp-clearfix">';
					$html .= '<label class="alignleft" for="post_featured_no">';
						$html .= '<input type="radio" name="post_featured" id="post_featured_no" value="no"/>';
					$html .= '<span class="checkbox-title">Post Not Featured</span></label>';
					$html .= '<label class="alignleft" for="post_featured_yes">';
						$html .= '<input type="radio" name="post_featured" id="post_featured_yes" value="yes"/>';
					$html .= '<span class="checkbox-title">Post Featured</span></label>';
					
				$html .= '</div>';
			$html .= '</fieldset>';
		}
		//output post rating select field
		else if($column == 'post_rating'){		
			$html .= '<fieldset class="inline-edit-col-center ">';
				$html .= '<div class="inline-edit-group wp-clearfix">';
					$html .= '<label class="alignleft" for="post_rating">Post Rating</label>';
					$html .= '<select name="post_rating" id="post_rating" value="">';
						$html .= '<option value="1">1</option>';
						$html .= '<option value="2">2</option>';
						$html .= '<option value="3">3</option>';
						$html .= '<option value="4">4</option>';
						$html .= '<option value="5">5</option>';
					$html .= '</select>';
				$html .= '</div>';
			$html .= '</fieldset>';	
		}
		//output post subtitle text field 
		else if($column == 'post_subtitle'){
			$html .= '<fieldset class="inline-edit-col-right ">';
				$html .= '<div class="inline-edit-group wp-clearfix">';
					$html .= '<label class="alignleft" for="post_rating">Post Subtitle</label>';
					$html .= '<input type="text" name="post_subtitle" id="post_subtitle" value="" />';
				$html .= '</div>';
			$html .= '</fieldset>';	
		}
		
		echo $html;
	}
	
	//add a custom column to hold our data
	public function add_custom_admin_column($columns){
		$new_columns = array();
		
		$new_columns['post_featured'] = 'Featured?';
		$new_columns['post_rating'] = 'Rating';
		$new_columns['post_subtitle'] = 'Subtitle';
		
		return array_merge($columns, $new_columns);
	}

	//customise the data for our custom column, it's here we pull in meatdata info
	public function manage_custom_admin_columns($column_name, $post_id){
		
		$html = '';

		if($column_name == 'post_featured'){
			$post_featured = get_post_meta($post_id, 'post_featured', true);

			$html .= '<div id="post_featured_' . $post_id . '">';
			if($post_featured == 'no' || empty($post_featured)){
				$html .= 'no';
			}else if ($post_featured == 'yes'){
				$html .= 'yes';
			}
			$html .= '</div>';
		}
		else if($column_name == 'post_rating'){
			$post_rating = get_post_meta($post_id, 'post_rating', true);
			
			$html .= '<div id="post_rating_' . $post_id . '">';
				$html .= $post_rating;
			$html .= '</div>';
		}
		else if($column_name == 'post_subtitle'){
			$post_subtitle = get_post_meta($post_id, 'post_subtitle', true);
			
			$html .= '<div id="post_subtitle_' . $post_id . '">';
				$html .= $post_subtitle;
			$html .= '</div>';
		}
		
		echo $html;
	}
	
	//saving meta info (used for both traditional and quick-edit saves)
	public function save_post($post_id){
					
					
		$post_type = get_post_type($post_id);
	
		if($post_type == 'post'){
				
			//check nonce set
			if(!isset($_POST['post_metadata_field'])){
				return false;
			}

			//verify nonce
			if(!wp_verify_nonce($_POST['post_metadata_field'], 'post_metadata')){
				return false;
			}
		
			
			//if not autosaving
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
	        	return false;
	   	 	}
			
			//all good to save
			$featured_post = isset($_POST['post_featured']) ? sanitize_text_field($_POST['post_featured']) : '';
			$post_rating = isset($_POST['post_rating']) ? sanitize_text_field($_POST['post_rating']) : '';
			$post_subtitle = isset($_POST['post_subtitle']) ? sanitize_text_field($_POST['post_subtitle']) : '';
			
			update_post_meta($post_id, 'post_featured', $featured_post);
			update_post_meta($post_id, 'post_rating', $post_rating);
			update_post_meta($post_id, 'post_subtitle', $post_subtitle);
		}
		
		
	}
	
	//gets singleton instance
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	
}
$el_extend_quick_edit = el_extend_quick_edit::getInstance();
?>