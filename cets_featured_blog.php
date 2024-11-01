<?php
/*
Plugin Name: Featured Blog Widget
Plugin URI: 
Description: Adds a widget that pulls the avatar of a user and the most recent posts in headline format from a "featured blog"
Version: 1.2.1
Author: Deanna Schneider
Copyright:

    Copyright 2009 CETS

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111_1307  USA

*/


class cets_widget_featured_blog extends WP_Widget{
	/** constructor **/
	function cets_widget_featured_blog() {
		parent::WP_Widget(false, $name = 'Featured Blog');
	}


/** This function displays the output of the widget **/
    function widget($args, $instance) {		
        extract( $args );
        $options = $instance;
		$title = empty($options['title']) ? __('Featured Blog') : apply_filters('widget_title', $options['title']);
		$defaults = array(
		'id' => 1, 'number' => 5, 'author' => '', 'include_content' => false, 'content_length' => 50, include_date => false, include_tags => false, include_author_name => false, limit_to_author => false
		);
		$args = wp_parse_args( $options, $defaults );
		// get the user to highlight based on the username
		
		// get the blog details and don't show anything if the featured blog has been deleted.
		$details = get_blog_details($args['blogid']);
		if ($details->deleted == 0){
			$user = get_userdatabylogin($args['author']);
			
		
		
			switch_to_blog($args['blogid']);
			// Get the query with the number of posts we need
			if (is_object($user) && $args['limit_to_author'] == true) {
				$query_string = array(
							'showposts' => $args['number'],
							'author' => $user->ID
							);
				
			}
			else {
				$query_string = array(
							'showposts' => $args['number']
							);
				
			}
			
							
		 	$query = new WP_Query($query_string);
			$blogurl = get_bloginfo('url');
			
			// if there are posts, output the info
			if ($query->have_posts()) :
			
			echo $before_widget;
			
			
			if (is_object($user)){
				?>
				<div class="image">
						<?php echo get_avatar($user->ID, 76); ?>
			        	
			    </div>
				<?php 
			}
			?>	
		        <div class="featuredContent topicListing">
		        	
		        	<?php echo $before_title; ?><a href="<?php echo $blogurl ?>"><?php echo $title ?></a><?php echo $after_title; ?>
		            <ul>
		            	<?php
		            	while ($query->have_posts()) : $query->the_post();
						?>
						<li>
						<a href="<?php the_permalink() ?>" class="headline" title="Permanent Link to <?php if ( get_the_title() ) the_title(); else the_ID(); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
						<?php if($args['include_date']) { ?> <div class="postdate"> <?php the_time('F jS, Y'); ?> </div> <?php 
						} 
						 if($args['include_author_name']) { ?> <div class="postauthor"> <?php the_author(); ?> </div> <?php 
						} 
						
						if($args['include_content']) { ?>
						
						<div class="entry">
							<?php echo wp_html_excerpt(get_the_content(), $args['content_length']) . '...'; ?>
						</div>
						<?php }; ?>
						
						</li>
						<?php 
						endwhile;
						?>
		            </ul>
		            <div class="more"><a href="<?php echo($blogurl) ?>">(More)</a></div>
		        </div>
			
			<?php
			echo $after_widget;
			endif; // ends the if for have posts
			restore_current_blog();
		
		}
	}


/** This function handles the updating **/
	 /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {	
		$instance = $old_instance;
		
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['blogid'] = $new_instance['blogid'];
		$instance['include_content'] = $new_instance['include_content'];
		$instance['limit_to_author'] = $new_instance['limit_to_author'];
		$instance['include_date'] = $new_instance['include_date'];
		$instance['include_tags'] = $new_instance['include_tags'];
		$instance['include_author_name'] = $new_instance['include_author_name'];
		if (is_numeric($new_instance['number'])){
			$instance['number'] = $new_instance['number'];
		}
		else {
			$instance['number'] = 5;
		}
		if (is_numeric($new_instance['content_length']) && strlen($new_instance['content_length'])){
			$instance['content_length'] = $new_instance['content_length'];
			}
		else {
			$instance['content_length'] = 50;
		}
		$instance['author'] = strip_tags(stripslashes($new_instance['author']));

					
        return $new_instance;
    }


	/** This function creates the form **/
	/** @see WP_Widget::form */
    function form($instance) {				
        global $blog_id;
		
		$title = esc_attr($instance['title']);
		$number = esc_attr($instance['number'] );
		$author = esc_attr($instance['author'] );
		$include_content = $instance['include_content'];
		$limit_to_author = $instance['limit_to_author'];
		$include_author_name = $instance['include_author_name'];
		$include_tags = $instance['include_tags'];
		if (is_numeric($instance['content_length'])){
			$content_length = $instance['content_length'];
		}
		else {
			$content_length = 50;
		}
		$include_date = $instance['include_date'];
		$blogid = $instance['blogid'];
		if ($blogid == NULL) {
			$blogid = $blog_id;
		}
		
		
		
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		
		<p><label for="<?php echo $this->get_field_id('blogid'); ?>"><?php _e('Blog ID:'); ?> <input size="5" id="<?php echo $this->get_field_id('blogid'); ?>" name="<?php echo $this->get_field_name('blogid'); ?>" type="text" value="<?php echo $blogid; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Posts to include:') ?></label>
			<input name="<?php echo $this->get_field_name('number'); ?>" id="<?php echo $this->get_field_id('number'); ?>" value="<?php echo $number?>" size="5" /></label>
			</p>
			<p><label for="<?php echo $this->get_field_id('author'); ?>"><?php _e('Author:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('author'); ?>" name="<?php echo $this->get_field_name('author'); ?>" value="<?php echo $author ?>" /></label>
			</p>
			<p><label for="<?php echo $this->get_field_id('limit_to_author'); ?>"><?php _e('Limit Posts to Author:'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('limit_to_author'); ?>" name="<?php echo $this->get_field_name('limit_to_author'); ?>" value="1" <?php if($limit_to_author == 1) echo (" checked='checked' "); ?> /></label>	</p>
		<p><label for="<?php echo $this->get_field_id('include_content'); ?>"><?php _e('Include Content:'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('include_content'); ?>" name="<?php echo $this->get_field_name('include_content'); ?>" value="1" <?php if($include_content == 1) echo (" checked='checked' "); ?> /></label>	</p>
		<p><label for="<?php echo $this->get_field_id('content_length'); ?>"><?php _e('Length of Content:') ?></label>
			<input name="<?php echo $this->get_field_name('content_length'); ?>" id="<?php echo $this->get_field_id('content_length'); ?>" value="<?php echo $content_length?>" size="5" /></label>
			</p>
		
		<p><label for="<?php echo $this->get_field_id('include_date'); ?>"><?php _e('Include Post Date:'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('include_date'); ?>" name="<?php echo $this->get_field_name('include_date'); ?>" value="1" <?php if($include_date == 1) echo (" checked='checked' "); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id('include_author_name'); ?>"><?php _e('Include Post Author:'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('include_author_name'); ?>" name="<?php echo $this->get_field_name('include_author_name'); ?>" value="1" <?php if($include_author_name == 1) echo (" checked='checked' "); ?> /></label></p>
		<!-- <p><label for="<?php echo $this->get_field_id('include_tags'); ?>"><?php _e('Include Tags:'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('include_tags'); ?>" name="<?php echo $this->get_field_name('include_tags'); ?>" value="1" <?php if($include_tags == 1) echo (" checked='checked' "); ?> /></label></p> -->
			
	<?php
		
    }
	


} // end class	







// register  widget
add_action('widgets_init', create_function('', 'return register_widget("cets_widget_featured_blog");'));







?>