<?php

global $anno_review_options;
$anno_review_options = array(
	0 => '',
	1 => __('Approve', 'anno'),
	2 => __('Revisions', 'anno'),
	3 => __('Reject', 'anno'),
);

/**
 * Register meta boxes
 */
function anno_add_meta_boxes() {
	if (anno_user_can('view_general_comments')) {
		add_meta_box('general-comment', __('Internal Comments: General', 'anno'), 'anno_internal_comments_general_comments', 'article', 'normal');
	}
	if (anno_user_can('view_review_comments')) {
		add_meta_box('reviewer-comment', __('Internal Comments: Reviews', 'anno'), 'anno_internal_comments_reviewer_comments', 'article', 'normal');
	}
}
add_action('admin_head-post.php', 'anno_add_meta_boxes');

/**
 * Get a list of comments for a given type
 * 
 * @param string $type Type of comments to fetch
 * @return array Array of comment objects
 */
function anno_internal_comments_get_comments($type, $post_id) {
	remove_filter('comments_clauses', 'anno_internal_comments_clauses');
	$comments = get_comments( array(
	    'type' => $type,
		'post_id' => $post_id,
	));
	add_filter('comments_clauses', 'anno_internal_comments_clauses');

	return $comments;
}

/**
 * Display the content for internal comment meta boxes
 * @param $type The comment type base
 * @return void
 */ 
function anno_internal_comments_display($type) {
	global $post;
	$comments = anno_internal_comments_get_comments('article_'.$type, $post->ID);
?>
<table class="widefat fixed comments comments-box anno-comments" data-comment-type="<?php echo esc_attr($type); ?>" cellspacing="0">
	<tbody id="<?php echo esc_attr('the-comment-list-'.$type); ?>" class="list:comment">
<?php
	if (is_array($comments) && !empty($comments)) {
		foreach ($comments as $comment) {
			if (anno_user_can('view_'.$type.'_comment', null, $post->ID, $comment->comment_ID)) {
				anno_internal_comment_table_row($comment);
			}
		}
	}
	if (anno_user_can('manage_'.$type.'_comment')) {
		anno_internal_comments_form($type);
	}
?>
	</tbody>
</table>
<?php
}

/**
 * Displays comment markup for internal comments
 * 
 * @param int $comment_id ID of the comment to be displayed
 * @param string $type Type of comment to display (general, reviewer)
 * @return void
 */ 
function anno_internal_comment_table_row($cur_comment) {
	global $comment;
	$comment_holder = $comment;
	$comment = $cur_comment;
	$comment_list_table = _get_list_table('WP_Comments_List_Table');
?>
	<tr id="comment-<?php echo $comment->comment_ID; ?>" class="approved">
		<td class="author column-author">
			<?php

				$author_url = $comment->comment_author_url;
				if ( 'http://' == $author_url ) {
					$author_url = '';
				}
				$author_url_display = preg_replace( '|http://(www\.)?|i', '', $author_url );
				if ( strlen( $author_url_display ) > 50 ) {
					$author_url_display = substr( $author_url_display, 0, 49 ) . '...';
				}

				echo '<strong>'.get_avatar($comment->comment_author_email, '32').$comment->comment_author.'</strong><br />';
				if ( !empty( $author_url ) ) {
					echo "<a title='$author_url' href='$author_url'>$author_url_display</a><br />";
				}

				if ( !empty( $comment->comment_author_email ) ) {
					echo '<a href="mailto:'.esc_url($comment->comment_author_email).'">'.esc_html($comment->comment_author_email).'</a>';
					echo '<br />';
				}
				echo '<a href="edit-comments.php?s='.$comment->comment_author_IP.'&amp;mode=detail">'.esc_html($comment->comment_author_IP).'</a>';
			?>
		</td>
		<td class="comment column-comment">
			<?php
			echo '
			<div class="submitted-on">';
				printf( __('Submitted on <a href="%1$s">%2$s at %3$s</a>', 'anno'), esc_url(get_comment_link($comment)), get_comment_date(__( 'Y/m/d', 'anno')), get_comment_date(get_option('time_format')));
				if ( $comment->comment_parent ) {
					$parent = get_comment($comment->comment_parent);
					$parent_link = esc_url(get_comment_link($comment->comment_parent));
					$name = get_comment_author( $parent->comment_ID );
					printf( ' | '.__( 'In reply to <a href="%1$s">%2$s</a>.', 'anno'), $parent_link, $name );
				}
			echo '
			</div>
			<p class="comment-content">'.
				$comment->comment_content
			.'</p>';
			
			if (anno_user_can('manage_general_comment')) {
				$actions['reply'] = '<a href="#" class="reply">'.__( 'Reply', 'anno').'</a>';
				$actions['edit'] = '<a href="comment.php?action=editcomment&amp;c='.$comment->comment_ID.'" title="'.esc_attr__( 'Edit comment', 'anno').'">'.__('Edit', 'anno').'</a>';
				$actions['delete'] = '<a class="anno-trash-comment" href="'.wp_nonce_url('comment.php?action=trashcomment&amp;c='.$comment->comment_ID, 'delete-comment_'.$comment->comment_ID).'">'.__('Trash', 'anno').'</a>';
				echo '
				<div class="row-actions" data-comment-id="'.$comment->comment_ID.'">';
				$i = 1;
				foreach ($actions as $action) {
					if ($i == count($actions)) {
						$sep = '';
					}
					else {
						$sep = ' | ';
					}
					echo $action.$sep;
					$i++;
				}
				echo '
				</div>';
			}
			?>
		</td>
	</tr>
<?php
	$comment = $comment_holder;
}

/**
 * Output of comments meta box
 */
function anno_internal_comments_general_comments() {
	anno_internal_comments_display('general');
}

function anno_internal_comments_reviewer_comments() {
	global $anno_review_options, $current_user, $post;
	$round = anno_get_round($post->ID);
	$user_review = get_user_meta($current_user->ID, '_'.$post->ID.'_review_'.$round, true);
	$reviewers = anno_get_post_users($post->ID, '_reviewers');
	if (in_array($current_user->ID, $reviewers)) {
?>
<div class="review-section">
	<label for="anno-review"><?php _e('Leave a Review: ', 'anno'); ?></label>
	<select id="anno-review" name="anno_review">

	<?php 
		foreach ($anno_review_options as $opt_key => $opt_val) {
	?>	

		<option value="<?php echo esc_attr($opt_key); ?>"<?php selected($user_review, $opt_key, true); ?>><?php echo esc_html($opt_val); ?></option>

	<?php 
		} 
	?>
	</select>
</div>

<?php 
		wp_nonce_field('anno_review', '_ajax_nonce-review', false);
	}
	anno_internal_comments_display('review');
}

/**
 * Output the form for internal comments
 */
function anno_internal_comments_form($type) {
?>
	<tr id="<?php echo esc_attr('comment-add-pos-'.$type); ?>">
	</tr>
	<tr id="<?php echo esc_attr('anno-internal-comment-'.$type.'-form'); ?>">
		<td colspan="2">
			<input type="hidden" name="anno_comment_type" value="<?php echo esc_attr($type); ?>" />
			<input type="hidden" class="parent-id" name="parent_id" value="0" />
			<p>
				<label for="<?php echo esc_attr('anno_comment_'.$type.'_textarea'); ?>"><?php _e('Comment', 'anno'); ?></label>
				<textarea id="<?php echo esc_attr('anno_comment_'.$type.'_textarea'); ?>"></textarea>
			</p>
			<p>
				<input class="anno-submit button" type="button" value="<?php _e('Submit', 'anno'); ?>" />
				<input class="anno-cancel button" type="button" value="<?php _e('Cancel', 'anno'); ?>" style="display: none;" onClick="location.href='#<?php echo esc_attr('comment-add-pos-'.$type); ?>'"/>
			</p>
			<?php wp_nonce_field('anno_comment', '_ajax_nonce-anno-comment-'.esc_attr($type), false ); ?>
		</td>
	</tr>
<?php
}

/**
 * Enqueue js for internal comments
 */
function anno_internal_comments_print_scripts() {
global $post;
	echo '
<script type="text/javascript">
	var ANNO_POST_ID = '.$post->ID.';
</script>
	';
	wp_enqueue_script('anno-internal-comments', trailingslashit(get_bloginfo('stylesheet_directory')).'plugins/internal-comments/js/internal-comments.js', array('jquery'));
}
add_action('admin_print_scripts', 'anno_internal_comments_print_scripts');

/**
 * Enqueue css for internal comments
 */
function anno_internal_comments_print_styles() {
	wp_enqueue_style('anno-internal-comments', trailingslashit(get_bloginfo('stylesheet_directory')).'plugins/internal-comments/css/internal-comments.css');
}
add_action('admin_print_styles', 'anno_internal_comments_print_styles');

/**
 * Filter the comment link if its an internal comment. Link will take you to the post edit page
 */ 
function anno_internal_comments_get_comment_link($link, $comment) {
	if (in_array($comment->comment_type, array('article_review', 'article_general'))) {
		$link = get_edit_post_link($comment->comment_post_ID).'#comment-'.$comment->comment_ID;
	}
	
	return $link;
}
add_filter('get_comment_link', 'anno_internal_comments_get_comment_link', 10, 2);

/**
 * Filter to prevent front end display of our comments
 */ 
function anno_internal_comments_clauses($clauses) {
	$clauses['where'] .= " AND comment_type NOT IN ('article_general', 'article_review')";
	return $clauses;
}
if (!is_admin()) {
	add_filter('comments_clauses', 'anno_internal_comments_clauses');
}


/**
 * Modify the comment count stored in the wp_post comment_count column, so internal comments don't show up there.
 * Based on code in the WP Core function wp_update_comment_count
 */
function anno_internal_comments_update_comment_count($post_id) {
	global $wpdb;
	$post_id = (int) $post_id;
	if ( !$post_id )
		return false;
	if ( !$post = get_post($post_id) )
		return false;
		
	$old = (int) $post->comment_count;
	$internal_count = (int) $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type IN ('article_general', 'article_review')", $post_id) );
	$wpdb->update( $wpdb->posts, array('comment_count' => max($old - $internal_count, 0)), array('ID' => $post_id) );

}
add_action('wp_update_comment_count', 'anno_internal_comments_update_comment_count');

/**
 * Processes an AJAX request when submitting an internal comment
 * Based on code in the WP Core
 */ 
function anno_internal_comments_ajax() {
	check_ajax_referer('anno_comment', '_ajax_nonce-anno-comment-'.$_POST['type']);
	//Check to make sure user can post comments on this post
	global $wpdb;
	$user = wp_get_current_user();
	if ($user->ID) {
		$comment_author = $wpdb->escape($user->display_name);
		$comment_author_email = $wpdb->escape($user->user_email);
		$comment_author_url = $wpdb->escape($user->user_url);
		$comment_content = trim($_POST['content']);
		$comment_type = 'article_'.trim($_POST['type']);
		$comment_post_ID = intval($_POST['post_id']);
		$user_ID = $user->ID;
	}
	else {
		die( __('Sorry, you must be logged in to reply to a comment.') );
	}
	
	if ( '' == $comment_content ) {
		die( __('Error: please type a comment.') );
	}
	
	$comment_parent = absint($_POST['parent_id']);
	
	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

	// Create the comment and automatically approve it
	add_filter('pre_comment_approved', 'anno_internal_comments_pre_comment_approved');
	$comment_id = wp_new_comment($commentdata);
	remove_filter('pre_comment_approved', 'anno_internal_comments_pre_comment_approved');
	
	$comment = get_comment($comment_id);
	if (!$comment) {
		 die('-1');
	}
	
	//Display markup for AJAX
	anno_internal_comment_table_row($comment);

}
add_action('wp_ajax_anno-internal-comment', 'anno_internal_comments_ajax');

/**
 * Processes an AJAX request when submitting a review from the dropdown.
 */
function anno_internal_comments_review_ajax() {
	check_ajax_referer('anno_review', '_ajax_nonce-review');
	if (isset($_POST['post_id']) && isset($_POST['review'])) {
		global $current_user;
		$post_id = $_POST['post_id'];
		$review = $_POST['review'];
		
		$post_round = anno_get_round($post_id);

		update_user_meta($current_user->ID, '_'.$post_id.'_review_'.$post_round, $review);
		
		$reviewed = anno_get_post_users($post_id, '_round_'.$post_round.'_reviewed');
		
		// If review is set to none, remove the user from reviewed, otherwise update it with the current user.
		if ($review != 0) {
			if (!in_array($current_user->ID, $reviewed)) {
				$reviewed[] = $current_user->ID;
				update_post_meta($post_id, '_round_'.$post_round.'_reviewed', array_unique($reviewed));
			}
		}
		else {
			$key = array_search($current_user->ID, $reviewed);
			if ($key !== false) {
				unset($reviewed[$key]);
				update_post_meta($post_id, '_round_'.$post_round.'_reviewed', array_unique($reviewed));
			}
		}
	}
}
add_action('wp_ajax_anno-review', 'anno_internal_comments_review_ajax');

/**
 * Filter to automatically approve internal comments
 */ 
function anno_internal_comments_pre_comment_approved($approved) {
	return 1;
}

/**
 * Dropdown filter to display only our internal comment types in the admin screen
 */ 
function anno_internal_comment_types_dropdown($comment_types) {
	$comment_types['article_general'] = __('Article General', 'anno');
	$comment_types['article_review'] = __('Article Review', 'anno');
	return $comment_types;
}
add_filter('admin_comment_types_dropdown', 'anno_internal_comment_types_dropdown');

/**
 * Get the top level comment of a series of comment replies.
 * 
 * @param int|object ID of the comment or the comment object itself
 * @return bool|obj Comment object, or false if the comment could not be found.
 */ 
function anno_internal_comments_get_comment_root($comment) {
	if (is_numeric($comment)) {
		$comment = get_comment($comment);
	}
	if ($commment !== false) {
		while($comment->comment_parent != 0) {
			$comment = get_comment($comment->comment_parent);
		}
	}
	return $comment;
}

//TODO Better implementation of below. Possible WP Core patch for hook in notify_author function

/**
 * Set a global to be checked by anno_pre_option_comments_notify
 */ 
function anno_insert_comment($id, $comment) {
	global $anno_insert_comment;
	$anno_insert_comment = $comment;
}
add_action('wp_insert_comment', 'anno_insert_comment', 10, 2);

/**
 * Prevent sending of comment notifications on new posts if its an internal comment type.
 */ 
function anno_pre_option_comments_notify($option) {
	global $anno_insert_comment;
	if ($anno_insert_comment && !in_array($anno_insert_comment->comment_type, array('article_general', 'article_reviewer'))) {
		unset ($anno_insert_comment);
		return 0;
	}
	return false;
}
add_filter('pre_option_comments_notify', 'anno_pre_option_comments_notify');


?>