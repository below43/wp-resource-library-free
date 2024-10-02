<?php
/*
Plugin Name: WP Simple Resource Library
Description: This plugin creates a custom post type named 'Resource'.
Version: 1.0
Author: Andrew Drake <andrew@drake.nz>
License: MIT License
*/

function create_resource_post_type()
{
	$labels = array(
		'name' => __('Resources'),
		'singular_name' => __('Resource')
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'resources'),
		'show_in_rest' => true,
		'supports' => array('title', 'thumbnail', 'excerpt', 'categories'), //'editor',
		'taxonomies' => array('category'),

		'hierarchical' => false,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 5,
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'can_export' => true,
		'has_archive' => true,
		'exclude_from_search' => false,
		'publicly_queryable' => true,
		'capability_type' => 'post',
	);

	register_post_type('resource', $args);
}

add_action('init', 'create_resource_post_type');

function add_resource_meta_boxes()
{
	//make this a box in the middle, but lower than Excerpt

	add_meta_box("resource_details_meta", "Resource details", "resource_details_meta_box_markup", "resource", "normal", "high", null);
	add_meta_box("audit_log_meta", "Audit Log", "audit_log_meta_box_markup", "resource", "normal", "low", null);
}

add_action("add_meta_boxes", "add_resource_meta_boxes");

function resource_details_meta_box_markup($object)
{
	wp_nonce_field(basename(__FILE__), "resource-details-nonce");

?>
	<p>A resource can be an uploaded file, a URL (website link), or an embed code (eg. for a video).</p>


	<label for="resource-url">Resource URL</label><br />

	<i>Paste a link to an external resource (eg. on Google Drive, Dropbox etc.), or select/upload media on this website</i>

	<input name="resource-url" placeholder="https://file-url-goes-here" id="resource-url" type="text" class="large-text" value="<?php echo esc_html(get_post_meta($object->ID, "resource-url", true)); ?>">
	<br />
	<br />
	<button type="button" name="resource-file" id="resource-file">Select / Upload Media</button><br />
	<br />
	<br />

	<!--embed code -->
	<label for="resource-embed-code">Embed Code</label><br />
	<i>Alternative option to Resource URL eg. YouTube embed code</i>
	<textarea name="resource-embed-code" rows="5" class="large-text"><?php echo esc_html(get_post_meta($object->ID, "resource-embed-code", true)); ?></textarea>
	<br />
	<br />


	<script>
		jQuery(document).ready(function($) {
			$('#resource-file').click(function(e) {
				e.preventDefault();
				var custom_uploader = wp.media({
						title: 'Select File',
						button: {
							text: 'Use this File'
						},
						multiple: false // Set this to true to allow multiple files to be selected
					})
					.on('select', function() {
						var attachment = custom_uploader.state().get('selection').first().toJSON();
						$('#resource-url').val(attachment.url);
					})
					.open();
			});
		});
	</script>
<?php

}

function audit_log_meta_box_markup($object)
{
	wp_nonce_field(basename(__FILE__), "audit-log-nonce");

?>
	<label for="dated">Document Created</label><br />
	<input name="created" type="date" value="<?php echo esc_html(get_post_meta($object->ID, "created", true)); ?>">
	<br />
	<br />

	<label for="last-updated">Last Updated</label><br />
	<input name="last-updated" type="date" value="<?php echo esc_html(get_post_meta($object->ID, "last-updated", true)); ?>">
	<br />
	<br />

	<label for="last-updated">Version</label><br />
	<input name="version" type="text" value="<?php echo esc_html(get_post_meta($object->ID, "version", true)); ?>">
	<br />
	<br />

	<label for="last-review-date">Last Date of Review</label><br />
	<input name="last-review-date" type="date" value="<?php echo esc_html(get_post_meta($object->ID, "last-review-date", true)); ?>">
	<br />
	<br />

	<label for="next-review-date">Next Date of Review</label><br />
	<input name="next-review-date" type="date" value="<?php echo esc_html(get_post_meta($object->ID, "next-review-date", true)); ?>">
	<br />
	<br />

	<label for="repealed-policies">Revisions</label><br />
	<textarea name="changes" rows="5" class="large-text"><?php echo esc_html(get_post_meta($object->ID, "changelog", true)); ?></textarea><br />
	eg. major changes, repealed policies, etc.
	<br />
	<br />
<?php
}

function save_resource_metadata($post_id, $post, $update)
{
	$meta_boxes = array(
		'resource-details' => array('resource-url', 'resource-embed-code'),
		'audit-log' => array('created', 'last-updated', 'version', 'last-review-date', 'next-review-date', 'changelog'),
	);

	if (!current_user_can("edit_post", $post_id))
		return $post_id;

	if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
		return $post_id;

	foreach ($meta_boxes as $nonce => $fields) {
		if (!isset($_POST["{$nonce}-nonce"]) || !wp_verify_nonce($_POST["{$nonce}-nonce"], basename(__FILE__)))
			return $post_id;

		foreach ($fields as $field) {

			$slug = "resource";
			if ($slug != $post->post_type)
				return $post_id;

			if ($field=="resource-embed-code") {
				$meta_value = $_POST[$field];
			}
			else {
				$meta_value = sanitize_text_field($_POST[$field]);
			}
			update_post_meta($post_id, $field, $meta_value);
		}
	}
}

add_action("save_post", "save_resource_metadata", 10, 3);


function display_resources_item_shortcode($atts)
{
	// Extract the attributes
	$atts = shortcode_atts(
		array(
			'id' => '', // Default value
		),
		$atts,
		'display_resources_shortcode'
	);

	$post = get_post($atts['id']); // Get the post with the ID from the shortcode attributes
	setup_postdata($post); // Set up global post data

	$output = '';
	if ($post) {
		$output .= '<h3 class="resource-item"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
		$output .= '<p><small>Published: ' . get_the_date() . '</small></p>';
		$last_updated = get_post_meta(get_the_ID(), 'last-updated', true);
		if (strpos($last_updated, "-") !== false) {
            $last_updated = date_i18n('j F Y', strtotime($last_updated));
		}

		$output .= '<p><small>Last updated: ' . $last_updated . '</small></p>';
		$output .= '<p>' . get_the_excerpt() . '</p>';
		$output .= '<a href="' . get_permalink() . '">View more</a>';
	} else {
		$output = '<p>No resource found.</p>';
	}

	wp_reset_postdata();

	return $output;
}

add_shortcode('display_resource_item', 'display_resources_item_shortcode');


function display_resource_shortcode($atts)
{
	// Extract the attributes
	$atts = shortcode_atts(
		array(
			'id' => '', // Default value
		),
		$atts,
		'display_resource_shortcode'
	);

	ob_start();

	$post = get_post($atts['id']); // Get the post with the ID from the shortcode attributes
	setup_postdata($post); // Set up global post data
	$last_updated = get_post_meta(get_the_ID(), 'last-updated', true);
	if (!$last_updated) {
		$last_updated = get_the_date();
	}
?>
	<h1><?php the_title(); ?></h1>
	<p>Published: <?php echo get_the_date(); ?><br />
		Last Updated: <?php echo $last_updated; ?><br />
		Categories: <?php echo get_the_category_list(', '); ?></p>

	<?php the_excerpt(); ?>

	<!-- if this has embed code then output it below -->
	<?php
	$url = get_post_meta(get_the_ID(), 'resource-url', true);
	$embed_code = get_post_meta(get_the_ID(), 'resource-embed-code', true);
	if (!empty($url)) {
		echo '<p><a class="button" href="' . esc_html($url) . '" target="_blank">Open this resource</a></p>';
	} else if (!empty($embed_code)) {
		echo '<div class="resource-embed-code">' . $embed_code . '</div>';
	} else {
		echo '<p>No file provided for this resource.</p>';
	}

	$created = get_post_meta(get_the_ID(), 'created', true);
	$last_updated = get_post_meta(get_the_ID(), 'last-updated', true);
	$version = get_post_meta(get_the_ID(), 'version', true);
	$last_review_date = get_post_meta(get_the_ID(), 'last-review-date', true);
	$next_review_date = get_post_meta(get_the_ID(), 'next-review-date', true);
	$changelog = get_post_meta(get_the_ID(), 'changelog', true);

	if (!empty($created) || !empty($last_updated) || !empty($last_review_date) || !empty($next_review_date) || !empty($changelog)) : ?>
		<hr>
		<div class="audit-details">
			<?php if (!empty($created)) : ?>
				<p>Created: <?php echo esc_html($created); ?></p>
			<?php endif; ?>
			<?php if (!empty($last_updated)) : ?>
				<p>Last Updated: <?php echo esc_html($last_updated); ?></p>
			<?php endif; ?>
			<?php if (!empty($version)) : ?>
				<p>Version: <?php echo esc_html($version); ?></p>
			<?php endif; ?>
			<?php if (!empty($last_review_date)) : ?>
				<p>Last Date of Review: <?php echo esc_html($last_review_date); ?></p>
			<?php endif; ?>
			<?php if (!empty($next_review_date)) : ?>
				<p>Next Date of Review: <?php echo esc_html($next_review_date); ?></p>
			<?php endif; ?>
			<?php if (!empty($changelog)) : ?>
				<p>Revisions: <?php echo esc_html($changelog); ?></p>
			<?php endif; ?>
		</div>
	<?php endif;

	wp_reset_postdata();

	return ob_get_clean();
}

add_shortcode('display_resource', 'display_resource_shortcode');

function include_resources_in_category_pages($query)
{
	if ($query->is_category() && $query->is_main_query()) {

		$post_types = $query->get('post_type');
		if (is_array($post_types)) {
			$post_types[] = 'resource';
		} else {
			$post_types = array('post', 'resource');
		}
		$query->set('post_type', $post_types);
	}
}
add_action('pre_get_posts', 'include_resources_in_category_pages');

function generate_table_url($params)
{
	return esc_url(add_query_arg($params, $_SERVER['REQUEST_URI']));
}

function generate_table_header($sortby, $sortorder, $category_name, $column_name, $display_name)
{
	$class = $sortby == $column_name ? 'active-sort' : '';
	$class = $class . ' ' . $column_name;
	$order = $sortby == $column_name && $sortorder == 'ASC' ? 'desc' : 'asc';
	$arrow = $sortby == $column_name ? ($sortorder == 'ASC' ? '↑' : '↓') : '';
	$url = generate_table_url(array('sortby' => $column_name, 'sortorder' => $order, 'category' => urlencode($category_name)));

	return "<th class=\"$class\"><a href=\"$url\">$display_name $arrow</a></th>";
}

function display_resources_table_shortcode($atts)
{
	// Extract the attributes
	$atts = shortcode_atts(
		array(
			'category' => '', // Default value
		),
		$atts,
		'display_resources_table_shortcode'
	);

	// If a category is specified in the shortcode, or GET params, add it to the query args
	$category_name = (!empty($atts['category'])) ? $atts['category'] : sanitize_text_field($_GET['category']);

	// If a search term is submitted, add it to the query args
	$search_term = isset($_GET['resource_search_term']) ? sanitize_text_field($_GET['resource_search_term']) : '';

	// Capture the selected option
	$sortby = isset($_GET['sortby']) ? sanitize_text_field($_GET['sortby']) : 'title';
	$sortorder = isset($_GET['sortorder']) && strtolower(sanitize_text_field($_GET['sortorder'])) == 'desc' ? 'DESC' : 'ASC';

    if ($sortby == 'last-updated') {
        $args['meta_key'] = 'last-updated';
    }

	$args = array(
		'post_type' => 'resource',
		'posts_per_page' => -1, // Get all posts
		'orderby'   => $sortby,
		'order'     => $sortorder,
		'category_name' => $category_name,
		's' => $search_term,
	);

	$query = new WP_Query($args);

	ob_start();

	// Display the search form
	?>
	<br />
	<form method="get" class="wp-simple-resource-library-search" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
		<input type="text" name="resource_search_term" placeholder="Search resources..." value="<?php echo isset($_GET['resource_search_term']) ? esc_attr($_GET['resource_search_term']) : ''; ?>">
		<input type="submit" value="Search">
	</form>
	<?php

	// Display the active category and a "X" to clear the category filter, but only if the shortcode isn't filtering by default
	if (empty($atts['category']) && $category_name) {
		$category_obj = get_category_by_slug($category_name);
		$category_display_name = is_object($category_obj) ? $category_obj->name : $category_name;
		echo '<p class="active-category-filter">';
		echo 'Category: ' . esc_html($category_display_name);
		echo ' <a href="' . esc_url(remove_query_arg('category', $_SERVER['REQUEST_URI'])) . '">X</a>';
		echo '</p>';
	}
	// Display the resources in a table
	if ($query->have_posts()) {
	?>
		<table class="wp-simple-resource-library-results">
			<thead>
				<tr>
					<?php echo generate_table_header($sortby, $sortorder, $category_name, 'title', 'Resource Name'); ?>
					<th class="excerpt">Summary</th>
					<?php if (empty($atts['category'])) {
						echo generate_table_header($sortby, $sortorder, $category_name, 'category', 'Category');
					} ?>
					<?php echo generate_table_header($sortby, $sortorder, $category_name, 'date', 'Published'); ?>
					<?php echo generate_table_header($sortby, $sortorder, $category_name, 'last-updated', 'Last Updated'); ?>
					<th class="actions">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php

				while ($query->have_posts()) {
					$query->the_post();
				?>
					<tr>
						<td width="30%" class="title"><?php echo get_the_title(); ?></td>
						<?php if (empty($atts['category'])) : // Only display the category data if no category is specified in the shortcode 
						?>
							<?php
							$categories = get_the_category();
							$category_links = array();
							foreach ($categories as $category) {
								$category_slug = $category->slug;
								$url_params = array('sortby' => $sortby, 'sortorder' => $sortorder, 'category' => $category_slug);
								$category_links[] = '<a href="' . generate_table_url($url_params) . '">' . $category->name . '</a>';
							}
							?>
							<td><?php echo implode(', ', $category_links); ?></td>
						<?php endif; ?>
						<td class="excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></td>
						<td nowrap class="date"><?php echo get_the_date(); ?></td>
						<td nowrap class="last-updated">
							<?php
							$last_updated = get_post_meta(get_the_ID(), 'last-updated', true);
							$created = get_post_meta(get_the_ID(), 'created', true);
							$date = $last_updated ? $last_updated : ($created ? $created : '');
							$date_formatted = '';
							if ($date) {
								//convert from iso to standard wordpress date output format  (as definted in wordpress settings)
								$date_formatted = date_i18n(get_option('date_format'), strtotime($date));
							}
							echo $date_formatted ? $date_formatted : '-';
							?>
						</td>
						<td nowrap class="actions"><a href="<?php echo get_permalink(); ?>">View more</a></td>
					</tr>
				<?php

				}

				?>
			</tbody>
		</table>
<?php

	} else {
		echo '<p>No resources found.</p>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}

add_shortcode('display_resources_table', 'display_resources_table_shortcode');
