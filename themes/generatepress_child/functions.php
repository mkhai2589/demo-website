<?php
// Add Font Awesome
function wpb_load_fa() {
	wp_enqueue_style( 'wpb-fa', get_stylesheet_directory_uri() . '/fontawesome-free-6.1.1-web/css/all.css' );
}
add_action( 'wp_enqueue_scripts', 'wpb_load_fa' );
//xoá gg font
add_action('template_redirect', 'wptangtoc_disable_google_fonts');
function wptangtoc_disable_google_fonts() {
	ob_start('wptangtoc_disable_google_fonts_regex');
}

function wptangtoc_disable_google_fonts_regex($html) {
	$html = preg_replace('/<link[^<>]*\/\/fonts\.(googleapis|google|gstatic)\.com[^<>]*>/i', '', $html);
	return $html;
}
//Tắt lựa chọn ngôn ngữ trong trang đăng nhập WordPress	
add_filter( 'login_display_language_dropdown', '__return_false' );
//chuyển về classic editor
add_filter('use_block_editor_for_post_type', '__return_false', 10);
// Don't load Gutenberg-related stylesheets.
add_action( 'wp_enqueue_scripts', 'remove_block_css', 100 );
function remove_block_css() {
wp_dequeue_style( 'wp-block-library' ); // WordPress core
wp_dequeue_style( 'wp-block-library-theme' ); // WordPress core
wp_dequeue_style( 'wc-block-style' ); // WooCommerce
}
//*disable widget block editor
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false', 100 );
add_filter( 'use_widgets_block_editor', '__return_false' );

//loại global style nội tuyến
add_action( 'after_setup_theme','wptangtoc_xoa_style_global_css');
function wptangtoc_xoa_style_global_css(){
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
remove_action('in_admin_header', 'wp_global_styles_render_svg_filters');
}
// them ten thuong hieu cho hinh anh tai len
add_filter('wp_handle_upload_prefilter', 'custom_upload_filter' );
function custom_upload_filter( $file ){
$file['name'] = 'khai-minh-it-' . $file['name'];
return $file;
}
//loại bỏ tự viết hoa
$filters = array('the_content', 'the_title', 'wp_title', 'comment_text');
foreach($filters as $filter) {
	$priority = has_filter($filter, 'capital_P_dangit');
	if($priority !== false) {
		remove_filter($filter, 'capital_P_dangit', $priority);
	}
}
//ẩn phiên bản WordPress để nâng cao bảo mật
remove_action('wp_head', 'wp_generator');
	add_filter('the_generator', 'wptangtoc_hide_wp_version');
	function wptangtoc_hide_wp_version() {
	return '';
}

//loại bỏ emoji
/******** xoa bieu tuong cam xuc ************/
add_action('init', 'wptangtoc_tat_emojis_WordPress');
function wptangtoc_tat_emojis_WordPress() {
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');	
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');	
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter('tiny_mce_plugins', 'wptangtoc_tat_emojis_WordPress_tinymce');
	add_filter('wp_resource_hints', 'wptangtoc_tat_emojis_WordPress_dns_prefetch', 10, 2);
	add_filter('emoji_svg_url', '__return_false');
}
function wptangtoc_tat_emojis_WordPress_tinymce($plugins) {
	if(is_array($plugins)) {
		return array_diff($plugins, array('wpemoji'));
	} else {
		return array();
	}
}
function wptangtoc_tat_emojis_WordPress_dns_prefetch( $urls, $relation_type ) {
	if('dns-prefetch' == $relation_type) {
		$emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/');
		$urls = array_diff($urls, array($emoji_svg_url));
	}
return $urls;
}

//xóa Dashicons
add_action('wp_enqueue_scripts', 'wptangtoc_disable_dashicons');
function wptangtoc_disable_dashicons() {
	if(!is_user_logged_in()) {
		wp_dequeue_style('dashicons');
	    wp_deregister_style('dashicons');
	}
}
//chuyển toàn bộ javascript xuống footer
function wptangtoc_remove_head_scripts() { 
    remove_action('wp_head', 'wp_print_scripts'); 
    remove_action('wp_head', 'wp_print_head_scripts', 9); 
    remove_action('wp_head', 'wp_enqueue_scripts', 1);
   /**** lệnh trên là xóa javscript header còn lệnh dưới là cho javscript xuống footer ****/
    add_action('wp_footer', 'wp_print_scripts', 10);
    add_action('wp_footer', 'wp_enqueue_scripts', 10);
    add_action('wp_footer', 'wp_print_head_scripts', 10); 
   } 
   add_action( 'wp_enqueue_scripts', 'wptangtoc_remove_head_scripts' );
// xoá url khỏi tên tác giả
add_filter( 'generate_post_author_output','tu_no_author_link' );
function tu_no_author_link() {
	printf( ' <span class="byline">%1$s</span>',
		sprintf( '<span class="author vcard" itemtype="http://schema.org/Person" itemscope="itemscope" itemprop="author">%1$s <span class="fn n author-name" itemprop="name">%4$s</span></span>',
			__( 'by','generatepress'),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'generatepress' ), get_the_author() ) ),
			esc_html( get_the_author() )
		)
	);
}
// fix thiếu chiều rộng và cao SVG Logo
add_filter('generate_logo_attributes', function($output){
	$add_attr = array(
				'width' => '160px',
				'height'   => '65px',
			);
	$new_output = array_merge($output,$add_attr);
	return $new_output;
});
// Xóa Trường URL khỏi Biểu mẫu Nhận xét
add_action( 'after_setup_theme', 'wplogout_add_comment_url_filter' );
function wplogout_add_comment_url_filter() {
    add_filter( 'comment_form_default_fields', 'wplogout_disable_comment_url', 20 );
}

function wplogout_disable_comment_url($fields) {
    unset($fields['url']);
    return $fields;
}
// Cập nhật bài viết lần cuối
add_filter( 'generate_post_date_output', function( $output, $time_string ) {
    $time_string = '<time class="entry-date published" datetime="%1$s" itemprop="datePublished">Published on: %2$s</time>';

    if ( get_the_date() !== get_the_modified_date() ) {
        $time_string = '<time class="entry-date updated-date" datetime="%3$s" itemprop="dateModified">Last Updated on: %4$s</time>';
    }

    $time_string = sprintf( $time_string,
        esc_attr( get_the_date( 'c' ) ),
        esc_html( get_the_date() ),
        esc_attr( get_the_modified_date( 'c' ) ),
        esc_html( get_the_modified_date() )
    );

    return sprintf( '<span class="posted-on">%s</span> ',
        $time_string
    );
}, 10, 2 );
//* Loại bỏ các kích thước ảnh mặc định của WordPress
function remove_default_image_sizes( $sizes) {
    unset( $sizes['large']);
    unset( $sizes['thumbnail']);
    unset( $sizes['medium']);
    unset( $sizes['medium_large']);
    unset( $sizes['1536x1536']);
    unset( $sizes['2048x2048']);
    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'remove_default_image_sizes');
//mặc định thêm thẻ div bao ngoài hình ảnh
function hdevvn_add_wrap_image($html, $id, $caption, $title, $align, $url, $size, $alt) {
    return '<div class="hdevvn_class">' . $html . '</div>';
}

add_filter('image_send_to_editor', 'hdevvn_add_wrap_image', 10, 8);
//tối ưu javascript hệ thống bình luận
add_action( 'wp_enqueue_scripts', 'wptangtoc_deregister_javascript_binh_luan', 100 );
    function wptangtoc_deregister_javascript_binh_luan() {
    wp_deregister_script( 'comment-reply' );
 }
 //Disable Heartbeat
 add_action( 'init', 'stop_heartbeat', 1 );
function stop_heartbeat() {
wp_deregister_script('heartbeat');
}
//Custom hiển thị số bài viết mới nhất
// Customize Lists in Recent Posts Widget
class Genesis_Widget_Recent_Posts extends WP_Widget {

	function __construct() {
	$widget_ops = array('classname' => 'widget_recent_entries', 'description' => __( "The most recent posts on your site") );
	parent::__construct('recent-posts', __('Recent Posts'), $widget_ops);
	$this->alt_option_name = 'widget_recent_entries';
   
	add_action( 'save_post', array($this, 'flush_widget_cache') );
	add_action( 'deleted_post', array($this, 'flush_widget_cache') );
	add_action( 'switch_theme', array($this, 'flush_widget_cache') );
	}
   
	function widget($args, $instance) {
	$cache = wp_cache_get('widget_recent_posts', 'widget');
   
	if ( !is_array($cache) )
	$cache = array();
   
	if ( ! isset( $args['widget_id'] ) )
	$args['widget_id'] = $this->id;
   
	if ( isset( $cache[ $args['widget_id'] ] ) ) {
	echo $cache[ $args['widget_id'] ];
	return;
	}
   
	ob_start();
	extract($args);
   
	$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts' );
	$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
	$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 10;
	if ( ! $number )
	$number = 10;
	$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
   
	$r = new WP_Query( apply_filters( 'widget_posts_args', array( 'posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) ) );
	if ($r->have_posts()) :
   ?>
<?php echo $before_widget; ?>
<?php if ( $title ) echo $before_title . $title . $after_title; ?>
<ul>
    <?php while ( $r->have_posts() ) : $r->the_post(); ?>
    <li class="custom-list">
        <a class="popular_content" href="<?php the_permalink() ?>"
            title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
        <?php if ( $show_date ) : ?>
        <span class="post-date"><?php echo get_the_date(); ?></span>
        <?php endif; ?>
    </li>
    <?php endwhile; ?>
</ul>
<?php echo $after_widget; ?>
<?php
	// Reset the global $the_post as this query will have stomped on it
	wp_reset_postdata();
   
	endif;
   
	$cache[$args['widget_id']] = ob_get_flush();
	wp_cache_set('widget_recent_posts', $cache, 'widget');
	}
   
	function update( $new_instance, $old_instance ) {
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['number'] = (int) $new_instance['number'];
	$instance['show_date'] = (bool) $new_instance['show_date'];
	$this->flush_widget_cache();
   
	$alloptions = wp_cache_get( 'alloptions', 'options' );
	if ( isset($alloptions['widget_recent_entries']) )
	delete_option('widget_recent_entries');
   
	return $instance;
	}
   
	function flush_widget_cache() {
	wp_cache_delete('widget_recent_posts', 'widget');
	}
   
	function form( $instance ) {
	$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
	$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
	$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
   ?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
        name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
    <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>"
        type="text" value="<?php echo $number; ?>" size="3" />
</p>

<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?>
        id="<?php echo $this->get_field_id( 'show_date' ); ?>"
        name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
    <label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label>
</p>
<?php
	}
   }
   
   // Register a New Widget for Recent Posts
   function genesis_register_custom_widgets() {
	register_widget( 'Genesis_Widget_Recent_Posts' );
   }
   add_action( 'widgets_init', 'genesis_register_custom_widgets' );
   /*
* Kiểm tra số điện thoại có 10 số của Việt Nam bằng Contact Form 7 (CF7)
*/
function custom_filter_wpcf7_is_tel( $result, $tel ) { 
	$result = preg_match( '/^(0|\+84)(\s|\.)?((3[2-9])|(5[689])|(7[06-9])|(8[1-689])|(9[0-46-9]))(\d)(\s|\.)?(\d{3})(\s|\.)?(\d{3})$/', $tel );
	return $result; 
  }
  add_filter( 'wpcf7_is_tel', 'custom_filter_wpcf7_is_tel', 10, 2 );
  //footer
  function wplogout_footer_creds_text () {
	$copyright = '<div class="creds"><p>Copyright © ' . date('Y') . ' · <a href="www.khaiminh.com">KhaiMinhIT</a> - All Rights Reserved</p></div>';
	return $copyright;
	 }
	add_filter( 'generate_copyright', 'wplogout_footer_creds_text' );
	// cải thiện tìm kiếm mặc định WordPress
	add_filter('posts_search','wptangtoc_search_by_title_only', 500, 2);
	function wptangtoc_search_by_title_only( $search, $wp_query )
		{
			global $wpdb;
			if ( empty( $search ) )
				return $search;
			$q = $wp_query->query_vars;    
			$n = ! empty( $q['exact'] ) ? '' : '%';
	
			$search =
				$searchand = '';
	
			foreach ( (array) $q['search_terms'] as $term ) {
				$term = esc_sql( like_escape( $term ) );
				$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
				$searchand = ' AND ';
			}
	
			if ( ! empty( $search ) ) {
				$search = " AND ({$search}) ";
				if ( ! is_user_logged_in() )
					$search .= " AND ($wpdb->posts.post_password = '') ";
			}
	
			return $search;
	}
	
// Tự động tắt link khi comments tránh spam link
remove_filter('comment_text', 'make_clickable', 9);
// Tự động thêm thuộc tính nofollow cho link trỏ ra ngoài
function my_nofollow($content) {
	return stripslashes(wp_rel_nofollow($content));
	}
	add_filter('the_content', 'my_nofollow');
//
/*=============================================
                BREADCRUMBS
=============================================*/
//  to include in functions.php
function the_breadcrumb()
{
    $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
    $delimiter = '&raquo;'; // delimiter between crumbs
    $home = 'Home'; // text for the 'Home' link
    $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
    $before = '<span class="current">'; // tag before the current crumb
    $after = '</span>'; // tag after the current crumb

    global $post;
    $homeLink = get_bloginfo('url');
    if (is_home() || is_front_page()) {
        if ($showOnHome == 1) {
            echo '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a></div>';
        }
    } else {
        echo '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
        if (is_category()) {
            $thisCat = get_category(get_query_var('cat'), false);
            if ($thisCat->parent != 0) {
                echo get_category_parents($thisCat->parent, true, ' ' . $delimiter . ' ');
            }
            echo $before . 'Archive by category "' . single_cat_title('', false) . '"' . $after;
        } elseif (is_search()) {
            echo $before . 'Search results for "' . get_search_query() . '"' . $after;
        } elseif (is_day()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
            echo '<a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
            echo $before . get_the_time('d') . $after;
        } elseif (is_month()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
            echo $before . get_the_time('F') . $after;
        } elseif (is_year()) {
            echo $before . get_the_time('Y') . $after;
        } elseif (is_single() && !is_attachment()) {
            if (get_post_type() != 'post') {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
                if ($showCurrent == 1) {
                    echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
                }
            } else {
                $cat = get_the_category();
                $cat = $cat[0];
                $cats = get_category_parents($cat, true, ' ' . $delimiter . ' ');
                if ($showCurrent == 0) {
                    $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
                }
                echo $cats;
                if ($showCurrent == 1) {
                    echo $before . get_the_title() . $after;
                }
            }
        } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
            $post_type = get_post_type_object(get_post_type());
            echo $before . $post_type->labels->singular_name . $after;
        } elseif (is_attachment()) {
            $parent = get_post($post->post_parent);
            $cat = get_the_category($parent->ID);
            $cat = $cat[0];
            echo get_category_parents($cat, true, ' ' . $delimiter . ' ');
            echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
            if ($showCurrent == 1) {
                echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
            }
        } elseif (is_page() && !$post->post_parent) {
            if ($showCurrent == 1) {
                echo $before . get_the_title() . $after;
            }
        } elseif (is_page() && $post->post_parent) {
            $parent_id  = $post->post_parent;
            $breadcrumbs = array();
            while ($parent_id) {
                $page = get_page($parent_id);
                $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                $parent_id  = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            for ($i = 0; $i < count($breadcrumbs); $i++) {
                echo $breadcrumbs[$i];
                if ($i != count($breadcrumbs)-1) {
                    echo ' ' . $delimiter . ' ';
                }
            }
            if ($showCurrent == 1) {
                echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
            }
        } elseif (is_tag()) {
            echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
        } elseif (is_author()) {
            global $author;
            $userdata = get_userdata($author);
            echo $before . 'Articles posted by ' . $userdata->display_name . $after;
        } elseif (is_404()) {
            echo $before . 'Error 404' . $after;
        }
        if (get_query_var('paged')) {
            if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
                echo ' (';
            }
          echo __('Page') . ' ' . get_query_var('paged');
            if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
                echo ')';
            }
        }
        echo '</div>';
    }
} // end the_breadcrumb()
// xoá dấu phẩy thẻ tags
add_filter( 'generate_term_separator', function() {
    return '';
} );

/*Tắt menu điều hướng phụ dành cho thiết bị di động
add_action( 'wp_enqueue_scripts', 'generate_dequeue_secondary_nav_mobile', 999 );
function generate_dequeue_secondary_nav_mobile() {
    wp_dequeue_style( 'generate-secondary-nav-mobile' );
}
*/



// phan biet admin khi binh luan
if ( ! class_exists( 'WPB_Comment_Author_Role_Label' ) ) :
    class WPB_Comment_Author_Role_Label {
    public function __construct() {
    add_filter( 'get_comment_author', array( $this, 'wpb_get_comment_author_role' ), 10, 3 );
    add_filter( 'get_comment_author_link', array( $this, 'wpb_comment_author_role' ) );
    }
    function wpb_get_comment_author_role($author, $comment_id, $comment) { 
    $authoremail = get_comment_author_email( $comment); 
    if (email_exists($authoremail)) {
    $commet_user_role = get_user_by( 'email', $authoremail );
    $comment_user_role = $commet_user_role->roles[0];
    $this->comment_user_role = ' <span class="comment-author-label comment-author-label-'.$comment_user_role.'">' . ucfirst($comment_user_role) . '</span>';
    } else { 
    $this->comment_user_role = '';
    } 
    return $author;
    } 
    function wpb_comment_author_role($author) { 
    return $author .= $this->comment_user_role; 
    } 
    }
    new WPB_Comment_Author_Role_Label;
    endif;
    // ket thuc

// hien thi thoi gian theo khoang o muc binh luan
function pressfore_comment_time_output($date, $d, $comment){
	return sprintf( _x( '%s trước', '%s = human-readable time difference', 'your-text-domain' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) );
}
add_filter('get_comment_date', 'pressfore_comment_time_output', 10, 3);
// ket thuc
