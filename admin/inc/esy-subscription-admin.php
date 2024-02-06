<?php function easy_subscription_plan_enqueue() {
    // $dir = plugin_dir_url(__FILE__);
	wp_enqueue_style( 'easy-subscription-plan', plugin_dir_url(__FILE__) . 'assets/css/easy-subscription-plan.css' );
	wp_enqueue_script( 'easy-subscription-plan', plugin_dir_url(__FILE__) . 'assets/js/easy-subscription-plan.js', array(), '1.0.0', true );
}
add_action( 'admin_init', 'easy_subscription_plan_enqueue' );


add_action('admin_menu', 'easy_subscription_plan_payments');
function easy_subscription_plan_payments() {
    add_submenu_page(
        'edit.php?post_type=easysubscription',
        __( 'Payments', 'textdomain' ),
        __( 'Payments', 'textdomain' ),
        'manage_options',
        'payments',
        'easy_subscription_plan_payments_callback'
    );
}

/**
 * Display callback for the submenu page.
 */
function easy_subscription_plan_payments_callback() { 
    include_once(plugin_dir_path(__FILE__) . 'views/easy-subscription-plan-payments.php');
}


add_action('admin_menu', 'easy_subscription_plan_settings');
function easy_subscription_plan_settings() {
    add_submenu_page(
        'edit.php?post_type=easysubscription',
        __( 'Settings', 'textdomain' ),
        __( 'Settings', 'textdomain' ),
        'manage_options',
        'settings',
        'easy_subscription_plan_settings_callback'
    );
}

/**
 * Display callback for the submenu page.
 */
function easy_subscription_plan_settings_callback() { 
    include_once(plugin_dir_path(__FILE__) . 'views/easy-subscription-plan-settings.php');
}
/*
* Creating a function to create our CPT
*/
  
function easy_subscription_post() {
  
    // Set UI labels for Custom Post Type
        $labels = array(
            'name'                => _x( 'Easy Subscription', 'Post Type General Name', 'twentytwentyone' ),
            'singular_name'       => _x( 'Subscription Plans', 'Post Type Singular Name', 'twentytwentyone' ),
            'menu_name'           => __( 'Easy Subscription', 'twentytwentyone' ),
            'parent_item_colon'   => __( 'Parent Subscription Plans', 'twentytwentyone' ),
            'all_items'           => __( 'Subscription Plans', 'twentytwentyone' ),
            'view_item'           => __( 'View Subscription Plans', 'twentytwentyone' ),
            'add_new_item'        => __( 'Add New Subscription Plans', 'twentytwentyone' ),
            'add_new'             => __( 'Add New Plan', 'twentytwentyone' ),
            'edit_item'           => __( 'Edit Subscription Plans', 'twentytwentyone' ),
            'update_item'         => __( 'Update Subscription Plans', 'twentytwentyone' ),
            'search_items'        => __( 'Search Subscription Plans', 'twentytwentyone' ),
            'not_found'           => __( 'Not Found', 'twentytwentyone' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwentyone' ),
        );
          
    // Set other options for Custom Post Type
          
        $args = array(
            'label'               => __( 'subscription-plans', 'twentytwentyone' ),
            'description'         => __( 'Movie news and reviews', 'twentytwentyone' ),
            'labels'              => $labels,
            // Features this CPT supports in Post Editor
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            // You can associate this CPT with a taxonomy or custom taxonomy. 
            'taxonomies'          => array( 'genres' ),
            /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts.
            */
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-yes-alt',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest' => true,
      
        );
          
        // Registering your Custom Post Type
        register_post_type( 'Easy Subscription', $args );
      
    }
      
    /* Hook into the 'init' action so that the function
    * Containing our post type registration is not 
    * unnecessarily executed. 
    */
      
    add_action( 'init', 'easy_subscription_post', 0 );