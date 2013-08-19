<?php
/**
 * Links widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Hierarchical_Links extends WP_Widget {
	
	function __construct() {
		$widget_ops = array('description' => __( "Your blogroll, with sub-categories." ) );
		parent::__construct('hierarchical-links', __('Links (Hierarchical)'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);

		$show_description = isset($instance['description']) ? $instance['description'] : false;
		$show_name = isset($instance['name']) ? $instance['name'] : false;
		$title = !empty($instance['title']) ? $instance['title'] : false;
		$show_rating = isset($instance['rating']) ? $instance['rating'] : false;
		$show_images = isset($instance['images']) ? $instance['images'] : true;
		$link_category = isset($instance['link_category']) ? $instance['link_category'] : false;
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'name';
		$order = ($orderby == 'rating') ? 'DESC' : 'ASC';
		$limit = isset( $instance['limit'] ) ? $instance['limit'] : -1;
		$include_parent = !empty($instance['include_parent']) ? true : false;
		// Display All Links widget as such in the widgets screen
		if(!empty($title)) {
			echo $before_title. $title . $after_title;
		}
		
		#echo '<pre>'.print_r($instance,true).'</pre>';
		#$before_widget = preg_replace('/id="[^"]*"/','id="%id"', $before_widget);
		$output = wp_list_bookmarks_hierarchical(apply_filters('widget_hierarchical_links_args', array(
			'title_before' => $before_title, 'title_after' => $after_title,
			'category_before' => '<li>', 
			'category_after' => '</li>',
			'show_images' => $show_images, 'show_description' => $show_description,
			'show_name' => $show_name, 'show_rating' => $show_rating,
			'category' => $link_category,
			'child_of' => !empty($instance['include_children']) ? $link_category : null,
			'class' => 'linkcat widget',
			'limit' => $limit,
			'orderby' => $orderby, 
			'order' => $order,
			'echo' => 0,
			'include_parent' => $include_parent
		)));
		
		echo $before_widget.$output.$after_widget;
	}

	function update( $new_instance, $old_instance ) {
		
		$new_instance = (array) $new_instance;
		$instance = array( 'images' => 0, 'name' => 0, 'description' => 0, 'rating' => 0, 'include_parent' => 0, 'include_children' => 0);
		foreach ( $instance as $field => $val ) {
			if ( !empty($new_instance[$field]) ) {
				$instance[$field] = 1;
			}
		}

		$instance['orderby'] = 'name';
		if ( in_array( $new_instance['orderby'], array( 'name', 'rating', 'id', 'rand' ) ) )
			$instance['orderby'] = $new_instance['orderby'];

		$instance['link_category'] = intval( $new_instance['link_category'] );
		$instance['limit'] = ! empty( $new_instance['limit'] ) ? intval( $new_instance['limit'] ) : -1;
		
		$instance['title'] = trim(rtrim($new_instance['title']));
		
		return $instance;
	}

	function form( $instance ) {

		$defaults = array( 
			'title' => '',
			'images' => true, 
			'name' => true, 
			'description' => false, 
			'rating' => false, 
			'link_category' => 0, 
			'include_children' => 1,
			'include_parent' => 1,
			'limit' => -1,
			'orderby' => 'name',
		);
		
		$instance = wp_parse_args((array)$instance,$defaults);
		$link_cats = get_terms( 'link_category' );
		$tax = get_taxonomy( 'link_category' );
		$taxonomy = 'link_category';
		if ( !$limit = intval( $instance['limit'] ) ) { $limit = -1; }
		
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>" style="display:block;">
				<span>Widget Title</span>
				<input class="text widefat" type="text" value="<?php echo esc_attr($instance['title']); ?>" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" />
			</label>
		</p>
		<p>
			<input type="hidden" name="<?php echo $this->get_field_name('include_children'); ?>" value="0" />
			<input type="hidden" name="<?php echo $this->get_field_name('include_parent'); ?>" value="0" />
			<input type="hidden" name="<?php echo $this->get_field_name('images'); ?>" value="0" />
			<input type="hidden" name="<?php echo $this->get_field_name('name'); ?>" value="0" />
			<input type="hidden" name="<?php echo $this->get_field_name('description'); ?>" value="0" />
			<input type="hidden" name="<?php echo $this->get_field_name('rating'); ?>" value="0" />
			
			<label for="<?php echo $this->get_field_id('link_category'); ?>" style="display:block;"><?php _e('Select Parent Link Category'); ?></label>
			<?php 
			
			wp_dropdown_categories(
				array( 
					'taxonomy' => $taxonomy, 
					'hide_empty' => 0, 
					'name' => $this->get_field_name('link_category'), 
					'orderby' => 'name', 
					'hierarchical' => 1, 
					'show_option_none' => 'All Links', 
					'tab_index' => 3, 
					'selected' => $instance['link_category'],
					'include_children' => $instance['include_children'],
				) 
			);
			 
			?>
			<label for="<?php echo $this->get_field_id('orderby'); ?>" style="display:block;"><?php _e( 'Sort Links by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat">
				<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php _e( 'Link title' ); ?></option>
				<option value="rating"<?php selected( $instance['orderby'], 'rating' ); ?>><?php _e( 'Link rating' ); ?></option>
				<option value="id"<?php selected( $instance['orderby'], 'id' ); ?>><?php _e( 'Link ID' ); ?></option>
				<option value="rand"<?php selected( $instance['orderby'], 'rand' ); ?>><?php _e( 'Random' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('include_children'); ?>" style="display:block;"><input class="checkbox" type="checkbox" <?php checked(!empty($instance['include_children']), true) ?> id="<?php echo $this->get_field_id('include_children'); ?>" name="<?php echo $this->get_field_name('include_children'); ?>" />
				<?php _e('Include Sub-Categories'); ?></label>
				
			<label for="<?php echo $this->get_field_id('include_parent'); ?>" style="display:block;"><input class="checkbox" type="checkbox" <?php checked(!empty($instance['include_parent']), true) ?> id="<?php echo $this->get_field_id('include_parent'); ?>" name="<?php echo $this->get_field_name('include_parent'); ?>" />
				<?php _e('Include Links From Parent Category'); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e( 'Number of links to show per category:' ); ?></label>
			<input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $limit == -1 ? '' : intval( $limit ); ?>" size="3" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked(!empty($instance['images']), true) ?> id="<?php echo $this->get_field_id('images'); ?>" name="<?php echo $this->get_field_name('images'); ?>" value="1" />
			<label for="<?php echo $this->get_field_id('images'); ?>"><?php _e('Show Link Image'); ?></label><br />
			<input class="checkbox" type="checkbox" value="1" <?php checked(!empty($instance['name']), true) ?> id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" />
			<label for="<?php echo $this->get_field_id('name'); ?>"><?php _e('Show Link Name'); ?></label><br />
			<input class="checkbox" type="checkbox" value="1" <?php checked(!empty($instance['description']), true) ?> id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" />
			<label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Show Link Description'); ?></label><br />
			<input class="checkbox" type="checkbox" value="1" <?php checked(!empty($instance['rating']), true) ?> id="<?php echo $this->get_field_id('rating'); ?>" name="<?php echo $this->get_field_name('rating'); ?>" />
			<label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Show Link Rating'); ?></label>
		</p>
<?php
	}
}
?>