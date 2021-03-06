<?php

/**
 * Sage includes
 *
 * The $sage_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042
 */

$sage_includes = [
  'lib/assets.php',    // Scripts and stylesheets
  'lib/extras.php',    // Custom functions
  'lib/setup.php',     // Theme setup
  'lib/titles.php',    // Page titles
  'lib/wrapper.php',   // Theme wrapper class
  'lib/customizer.php' // Theme customizer
];

function ga_meta_boxes( $meta_boxes ) {
    $color_field = array(
        'title'      => __( 'Extras', 'sage' ),
        'post_types' => array('page', 'header'),
        'placeholder' => __('Please select a color ...', 'sage'),
        'fields'     => array(
            array(
                'id'   => 'color',
                'name' => __( 'Color', 'sage' ),
                'type' => 'select',
                'options' => [
                  'rgba(255, 255, 255, .25)' => __('No color', 'sage'),
                  'rgb(102, 253, 179)' => __('Green', 'sage'),
                  'rgb(45, 255, 254)' => __('Turquoise', 'sage'),
                  'rgb(243, 149, 38)' => __('Orange', 'sage')
                ],
            ),
        ),
    );

    $meta_boxes[] = $color_field;

    return $meta_boxes;
}

function modify_admin_bar() {
  remove_menu_page('edit-comments.php');
  remove_menu_page('edit.php');
  remove_menu_page('edit.php?post_type=fa_gallery');
  remove_menu_page('edit.php?post_type=tribe_events');
}

add_action( 'admin_menu', 'modify_admin_bar' );

add_filter( 'rwmb_meta_boxes', 'ga_meta_boxes' );

foreach ($sage_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);

class Colored_Menu extends Walker_nav_menu {
  function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
    $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
    $class_names = $value = '';

    $classes = empty( $item->classes ) ? array() : (array) $item->classes;
    $classes[] = 'menu-item-' . $item->ID;

    $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
    $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

    $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
    $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

    $output .= $indent . '<li' . $id . $value . $class_names .'>';

    $atts = array();
    $atts['title'] = ! empty($item->attr_title) ? $item->attr_title : '';
    $atts['target'] = ! empty($item->target) ? $item->target     : '';
    $atts['rel'] = ! empty($item->xfn) ? $item->xfn : '';
    $atts['href'] = ! empty($item->url) ? $item->url : '';

    $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

    $attributes = '';
    foreach ( $atts as $attr => $value ) {
      if ( ! empty( $value ) ) {
        $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }

    $item_output = $args->before;
    $item_output .= '<a'. $attributes .'>';

    $post = get_post($item->object_id);
    if($post->post_parent) {
      $color = get_post_meta($post->post_parent, 'color', true);
    } else {
      $color = get_post_meta($item->object_id, 'color', true);
    }

    if($color) {
      $item_output .= '<span style="color: ' . $color . ' ; border-bottom: 1px solid ' . $color . '">';
    } else {
      $item_output .= '<span style="border-bottom: 1px solid rgba(255, 255, 255, .25)">';
    }

    $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

    $item_output .= '</span>';
    $item_output .= '</a>';
    $item_output .= $args->after;

    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
  }
}

function shortcode_date($atts) {
  $atts = shortcode_atts(array(
    'posts_per_page' => '5',
  ), $atts);

  if($atts['posts_per_page'] == 0) {
    $atts['posts_per_page'] = apply_filters('tribe_events_single_venue_posts_per_page', 100);
  }

  if(function_exists('tribe_include_view_list')) {
    return tribe_include_view_list(array(
      'order' => 'DESC',
      'posts_per_page' => $atts['posts_per_page'],
    ));
  }
}

add_shortcode('calendar', 'shortcode_date');

?>
