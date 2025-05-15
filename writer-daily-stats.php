<?php
	/**
	 * Plugin Name: Writer Daily Stats
	 * Description: Shows number of posts and views per writer per day.
	 * Version: 1.4
	 * Author: Dennis Kiptoo Kiptugen
	 */
	plugin_dir_path(__FILE__) . 'plugin-update-checker/load-v5p5.php';
	
	use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
	$updateChecker = PucFactory::buildUpdateChecker(
		'https://github.com/dkiptugen/Writer-Daily-Stats/',
		__FILE__,
		'writer-daily-stats'
	);
	$updateChecker->setBranch('master');
	
	require_once plugin_dir_path(__FILE__) . 'includes/stats-functions.php';
	
	add_action('admin_menu', function ()
		{
			add_menu_page('Writer Stats', 'Writer Stats', 'manage_options', 'writer-stats', 'wds_stats_page');
		});
	add_action('wp_head', function ()
		{
			if (is_single())
				{
					global $post;
					$views = (int)get_post_meta($post->ID, 'views', true);
					update_post_meta($post->ID, 'views', $views + 1);
				}
		});
	// Add the column to the admin posts list
	add_filter('manage_posts_columns', function($columns) {
		$columns['views'] = 'Views';
		return $columns;
	});

// Populate the "Views" column
	add_action('manage_posts_custom_column', function($column, $post_id) {
		if ($column === 'views') {
			echo (int) get_post_meta($post_id, 'post_views', true);
		}
	}, 10, 2);
// Make the column sortable
	add_filter('manage_edit-post_sortable_columns', function($columns) {
		$columns['views'] = 'post_views';
		return $columns;
	});

// Handle custom sorting
	add_action('pre_get_posts', function($query) {
		if (!is_admin() || !$query->is_main_query()) return;
		
		if ($query->get('orderby') === 'post_views') {
			$query->set('meta_key', 'post_views');
			$query->set('orderby', 'meta_value_num');
		}
	});
