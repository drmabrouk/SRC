<?php
/**
 * Register CPT and Taxonomies for the plugin.
 */
class SRC_Post_Types {

	/**
	 * Register the research_paper custom post type.
	 */
	public static function register_research_paper_cpt() {
		$labels = array(
			'name'               => _x( 'Research Papers', 'Post Type General Name', 'scientific-research-center' ),
			'singular_name'      => _x( 'Research Paper', 'Post Type Singular Name', 'scientific-research-center' ),
			'menu_name'          => __( 'Research Papers', 'scientific-research-center' ),
			'name_admin_bar'     => __( 'Research Paper', 'scientific-research-center' ),
			'archives'           => __( 'Paper Archives', 'scientific-research-center' ),
			'attributes'         => __( 'Paper Attributes', 'scientific-research-center' ),
			'parent_item_colon'  => __( 'Parent Paper:', 'scientific-research-center' ),
			'all_items'          => __( 'All Papers', 'scientific-research-center' ),
			'add_new_item'       => __( 'Add New Research Paper', 'scientific-research-center' ),
			'add_new'            => __( 'Add New', 'scientific-research-center' ),
			'new_item'           => __( 'New Research Paper', 'scientific-research-center' ),
			'edit_item'          => __( 'Edit Research Paper', 'scientific-research-center' ),
			'update_item'        => __( 'Update Research Paper', 'scientific-research-center' ),
			'view_item'          => __( 'View Research Paper', 'scientific-research-center' ),
			'view_items'         => __( 'View Papers', 'scientific-research-center' ),
			'search_items'       => __( 'Search Research Paper', 'scientific-research-center' ),
			'not_found'          => __( 'Not found', 'scientific-research-center' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'scientific-research-center' ),
			'featured_image'     => __( 'Featured Image', 'scientific-research-center' ),
			'set_featured_image' => __( 'Set featured image', 'scientific-research-center' ),
			'remove_featured_image' => __( 'Remove featured image', 'scientific-research-center' ),
			'use_featured_image'    => __( 'Use as featured image', 'scientific-research-center' ),
			'insert_into_item'      => __( 'Insert into paper', 'scientific-research-center' ),
			'uploaded_to_this_item' => __( 'Uploaded to this paper', 'scientific-research-center' ),
			'items_list'            => __( 'Papers list', 'scientific-research-center' ),
			'items_list_navigation' => __( 'Papers list navigation', 'scientific-research-center' ),
			'filter_items_list'     => __( 'Filter papers list', 'scientific-research-center' ),
		);
		$args = array(
			'label'               => __( 'Research Paper', 'scientific-research-center' ),
			'description'         => __( 'Research paper submissions', 'scientific-research-center' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields' ),
			'taxonomies'          => array( 'scientific_category', 'research_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		);
		register_post_type( 'research_paper', $args );
	}

	/**
	 * Register scientific categories and research tags taxonomies.
	 */
	public static function register_taxonomies() {
		// Scientific Category
		$labels_cat = array(
			'name'                       => _x( 'Scientific Categories', 'Taxonomy General Name', 'scientific-research-center' ),
			'singular_name'              => _x( 'Scientific Category', 'Taxonomy Singular Name', 'scientific-research-center' ),
			'search_items'               => __( 'Search Categories', 'scientific-research-center' ),
			'all_items'                  => __( 'All Categories', 'scientific-research-center' ),
			'parent_item'                => __( 'Parent Category', 'scientific-research-center' ),
			'parent_item_colon'          => __( 'Parent Category:', 'scientific-research-center' ),
			'edit_item'                  => __( 'Edit Category', 'scientific-research-center' ),
			'update_item'                => __( 'Update Category', 'scientific-research-center' ),
			'add_new_item'               => __( 'Add New Scientific Category', 'scientific-research-center' ),
			'new_item_name'              => __( 'New Scientific Category Name', 'scientific-research-center' ),
			'menu_name'                  => __( 'Scientific Categories', 'scientific-research-center' ),
		);
		$args_cat = array(
			'hierarchical'          => true,
			'labels'                => $labels_cat,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'scientific-category' ),
			'show_in_rest'          => true,
		);
		register_taxonomy( 'scientific_category', array( 'research_paper' ), $args_cat );

		// Research Tag
		$labels_tag = array(
			'name'                       => _x( 'Research Tags', 'Taxonomy General Name', 'scientific-research-center' ),
			'singular_name'              => _x( 'Research Tag', 'Taxonomy Singular Name', 'scientific-research-center' ),
			'search_items'               => __( 'Search Tags', 'scientific-research-center' ),
			'popular_items'              => __( 'Popular Tags', 'scientific-research-center' ),
			'all_items'                  => __( 'All Tags', 'scientific-research-center' ),
			'edit_item'                  => __( 'Edit Tag', 'scientific-research-center' ),
			'update_item'                => __( 'Update Tag', 'scientific-research-center' ),
			'add_new_item'               => __( 'Add New Research Tag', 'scientific-research-center' ),
			'new_item_name'              => __( 'New Research Tag Name', 'scientific-research-center' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'scientific-research-center' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'scientific-research-center' ),
			'choose_from_most_used'      => __( 'Choose from most used', 'scientific-research-center' ),
			'menu_name'                  => __( 'Research Tags', 'scientific-research-center' ),
		);
		$args_tag = array(
			'hierarchical'          => false,
			'labels'                => $labels_tag,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'research-tag' ),
			'show_in_rest'          => true,
		);
		register_taxonomy( 'research_tag', array( 'research_paper' ), $args_tag );
	}
}
