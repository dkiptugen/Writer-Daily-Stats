<?php
	/**
	 * Plugin Name: Writer Daily Stats
	 * Description: Shows number of posts and views per writer per day.
	 * Version: 1.1
	 * Author: Dennis Kiptoo Kiptugen
	 */
	require_once plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';
	
	$updateChecker = Puc_v4_Factory::buildUpdateChecker(
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