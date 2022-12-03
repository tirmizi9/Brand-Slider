<?php
/*
Plugin Name: Brand Slider
Plugin URI: https://github.com/tirmizi9/
Description: Add Woo Category Shortcode to get Product list
Version: 1.0.0
Author: Syed Muzaffar Tirmizi
Author URI: https://www.upwork.com/freelancers/syedtirmizi
License: GNU Public License v3
*/

define('MJTPLUGINURL', plugin_dir_url( __FILE__ )); 
add_action('init', 'mjt_sst_brands_post_init');
function mjt_sst_brands_post_init() {
	global $data;
	register_post_type(
		'sst_brands',
		array(
			'labels' => array(
				'name' => 'Brands',
				'singular_name' => 'Brand'
			),
			'public' => true,
			'has_archive' => 'sst_brands',
			'rewrite' => array('slug' => 'sst_brands', 'with_front' => false),
			'supports' => array('title', '', 'excerpt', 'author', 'thumbnail', '', 'revisions', 'custom-fields', '', ''),
			'can_export' => true,
		)
	);
	register_taxonomy('brands_category', 'sst_brands', array('hierarchical' => true, 'label' => 'Categories', 'query_var' => true, 'rewrite' => true)); 

}
if ( function_exists( 'add_image_size' ) ) { 
    add_image_size( 'slider-thumb', 90, 45 ); 
	//300 pixels wide (and unlimited height9999)
    // add_image_size( 'homepage-thumb', 220, 180, true ); //(cropped)
}

add_shortcode('sst_brands_slider','sst_brands_slider_callback');
function sst_brands_slider_callback($atts ){
	 ob_start();
 
	extract(shortcode_atts(array(
		'cats' 				=> '',			
	), $atts)); 
	$cat = (!empty($cats)) ? explode(',',$cats) : '';
	ob_start();
	 $args = array(
		'post_type'             => 'sst_brands',
		'post_status'           => 'publish',
		'ignore_sticky_posts'   => 1,
		'posts_per_page'        => '10'   
	);
	// Category Parameter
	if($cat != "") {			
		$args['tax_query'] = array(
									array( 
											'taxonomy' 	=> 'brands_category',
											'field' 	=> 'slug',
											'terms' 	=> $cat
								));

	}
$brands = new WP_Query($args);
if ( $brands->have_posts() ) :  ?> 
    <div style="padding:220px 0 100px;background:#00e2ff; ">
        <div id="thumbnail-slider">
            <div class="inner">
                <ul style="touch-action: pan-y; transition-property: transform; transition-timing-function: cubic-bezier(0.2, 0.88, 0.5, 1);">
					<?php while ( $brands->have_posts() ) : $brands->the_post();
					$pid = get_the_ID()  ;  
					if ( has_post_thumbnail() ) {  
					 $imgsrca = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ), 'slider-thumb' );
						$imgsrc = $imgsrca[0];
					}
					?>
						<li class="" style="display: inline-block; width: 90px; height: 45px;">
							 <a class="thumb" href="<?php echo $imgsrc ;?>" style="background-image: url('<?php echo $imgsrc ;?>'); cursor: default;">
								<h3><?php the_title() ;?></h3>
								<?php the_excerpt() ;?>								
							</a>
						</li>
					<?php endwhile; // end of the loop. ?>
				</ul>
            </div>
			<div id="thumbnail-slider-prev" class="disabled"></div>
			<div id="thumbnail-slider-next"></div>
			<div id="thumbnail-slider-pause-play"></div>
		</div>
    </div>
<?php  endif; 
wp_reset_postdata(); 
$myvariable = ob_get_clean();
    return $myvariable;
}

/** thumbnail-slider.js
 * Enqueue the wpdocs-script if the sst_brands_slider_shortcode_scripts is being used
 */
function sst_brands_slider_shortcode_scripts() {
	global $post;
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'sst_brands_slider') && !is_admin() ) {
		wp_register_style( 'thumbnail-slider', MJTPLUGINURL . 'assets/css/thumbnail-slider.css', array(), '1.0.0' );
			wp_enqueue_style( 'thumbnail-slider');
		wp_register_style( 'tooltip', MJTPLUGINURL . 'assets/css/tooltip.css', array(), '1.0.0' );
			wp_enqueue_style( 'tooltip');
			
		wp_register_script( 'thumbnail-slider', MJTPLUGINURL. 'assets/js/thumbnail-slider.js');
			wp_enqueue_script( 'thumbnail-slider' );
		wp_register_script( 'tooltip', MJTPLUGINURL. 'assets/js/tooltip.js');
			wp_enqueue_script( 'tooltip' );
	}
}
add_action( 'wp_enqueue_scripts', 'sst_brands_slider_shortcode_scripts');


function sst_brands_slider_init_scripts() {
	global $post;
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'sst_brands_slider') ) {
		?>
		 <script>
        //Note: this script should be placed at the bottom of the page, or after the slider markup. It cannot be placed in the head section of the page.
        var slides = document.getElementById("thumbnail-slider").getElementsByTagName("li");
        for (var i = 0; i < slides.length; i++) {
            slides[i].onmouseover = function (e) {
                var li = this;
                if (li.thumb) {
                    var content = "<div class='tip-wrap' style='background-image:url(" + li.thumbSrc + ");'><div class='tip-text'>" + li.thumb.innerHTML + "</div></div>";
                    tooltip.pop(li, content);
                }
            };
        }
    </script>
<?php	}
}

add_action( 'wp_footer', 'sst_brands_slider_init_scripts');


?>