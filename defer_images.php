<?php

/* disable emoji js begins */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
/* disable emoji js ends */

/* divi specific lazy loading begins */
// Add action
add_action( 'wp', 'nabtron_lazy_bg_check_if_needed' );
function nabtron_lazy_bg_check_if_needed() {
	add_action( 'wp_print_styles', 'nabtron_lazy_bg_add_header_css' );
	add_action( 'wp_print_footer_scripts', 'nabtron_lazy_bg_add_footer_js' );
}

// Print CSS in <head> to hide all section backgrounds initially
function nabtron_lazy_bg_add_header_css() {
    
//    $css_selector_section 		= 'html.js #et-boc .et_builder_inner_content .et_pb_section.et_pb_with_background:not(.lazy-loaded-background)';
    $css_selector_section 			= 'html.js #et-boc .et_builder_inner_content .et_pb_section.et_pb_with_background:not(.lazy-loaded-background)';
    $css_selector_section_parallax 	= 'html.js #et-boc .et_builder_inner_content .et_pb_section .free-background-overlay:not(.lazy-loaded-background)';
    $css_selector_slide 			= 'html.js #et-boc .et_builder_inner_content .et_pb_slide:not(.et-pb-active-slide):not(.lazy-loaded-slide)';
    echo '
	<style id="nabtron-lazy-divi-section-slide-backgrounds-css">
		body:not(.lazy-loaded-background) * {background-image: none !important;}
		' . $css_selector_section . ',' . $css_selector_section . ' > .et_parallax_bg' . '{background-image:none !important;}
		' . $css_selector_section_parallax . ',' . $css_selector_section_parallax . ' > .et_parallax_bg' . '{background-image:none !important;}
		' . $css_selector_slide . ',' . $css_selector_slide . ' > .et_parallax_bg' . '{background-image:none !important;display:none !important;}
	</style>';
}

// Now we need to add the JS that will load the background images on scroll
function nabtron_lazy_bg_add_footer_js() {
    
    $css_selector_section 			= 'html.js #et-boc .et_builder_inner_content .et_pb_section.et_pb_with_background:not(.lazy-loaded-background)';
    $css_selector_section_parallax 	= 'html.js #et-boc .et_builder_inner_content .et_pb_section .free-background-overlay:not(.lazy-loaded-background)';
?>
<script id="nabtron-lazy-divi-section-slide-backgrounds-js">
	function nabtronlazyLoadDiviSectionBackgrounds(){
		jQuery('body').addClass('lazy-loaded-background');
		jQuery('<?php echo $css_selector_section ?>').each(function(){
			//console.log("nab 1");
			var divPos=jQuery(this).offset().top,topOfWindow=jQuery(window).scrollTop();
			if (divPos<topOfWindow+100){
				jQuery(this).addClass('lazy-loaded-background');
			}
		})
		jQuery('<?php echo $css_selector_section_parallax ?>').each(function(){
			//console.log("nab 1");
			var divPos=jQuery(this).offset().top,topOfWindow=jQuery(window).scrollTop();
			if (divPos<topOfWindow+100){
				jQuery(this).addClass('lazy-loaded-background');
			}
		})
	}
	jQuery(document).ready(function(){
		nabtronlazyLoadDiviSectionBackgrounds();
	});
	jQuery(window).scroll(function(){
		nabtronlazyLoadDiviSectionBackgrounds();
	});
	jQuery(window).resize(function(){
		nabtronlazyLoadDiviSectionBackgrounds();
	});
	
	jQuery(document).ready(function(){
		!function(){var a=jQuery.fn.addClass;jQuery.fn.addClass=function(){var e=a.apply(this,arguments);return"et-pb-active-slide"==arguments[0]&&setTimeout(function(){var a=jQuery(".et-pb-active-slide + .et_pb_slide");a.addClass("lazy-loaded-slide"),a.hasClass("et_pb_section_parallax")&&(a=jQuery(".et_parallax_bg",a)).css("background-image",a.css("background-image"))},2e3),e}}();
	});

// 	!function(){
// 		var a=jQuery.fn.addClass;
// 		jQuery.fn.addClass=function(){
// 			var e=a.apply(this,arguments);
// 			return"et-pb-active-slide"==arguments[0]&&setTimeout(function(){
// 				var a=jQuery(".et-pb-active-slide + .et_pb_slide");
// 				a.addClass("alazy-loaded-slide"),a.hasClass("et_pb_section_parallax")&&(a=jQuery(".et_parallax_bg",a)).css("background-image",a.css("background-image"))
// 			},2e3),e}
// 	}();

</script>
<?php
}

/* divi specific lazy loading ends */

/* regular lazy load of images begins */

function nabtron_filter_lazyload($content) {
    return preg_replace_callback('/(<\s*img[^>]+)(src\s*=\s*[\'"][^\'"]+[\'"])([^>]+>)/i', 'nabtron_preg_lazyload', $content);
}
//add_filter('the_content', 'nabtron_filter_lazyload',999);

function nabtron_preg_lazyload($img_match) {

    $img_replace = $img_match[1] . 'src="https://d3o1w89lo2cpl6.cloudfront.net/wp-content/plugins/a3-lazy-load/assets/css/loading.gif" data-src' . substr($img_match[2], 3) . $img_match[3];
 
	if(strpos($img_replace, 'class') !== false){
	    $img_replace = preg_replace('/class\s*=\s*"/i', 'class="lazyload ', $img_replace);
	}else{
	    $img_replace = str_replace('<img', '<img class="lazyload "', $img_replace);
	}
 
    $img_replace .= '<noscript>' . $img_match[0] . '</noscript>';
    return $img_replace;
}

// doing it via buffer thing
function nabtron_lazy_buffer_callback($buffer) {
  // modify buffer here, and then return the updated code
  return $buffer;
}

function nabtron_lazy_buffer_start() { 
//	ob_start("nabtron_lazy_buffer_callback"); 
	ob_start("nabtron_filter_lazyload"); 
}

function nabtron_lazy_buffer_end() {
	if(ob_get_contents()){
		ob_end_flush(); 
	}
}

if(!is_admin()){
	add_action('after_setup_theme', 'nabtron_lazy_buffer_start');
	add_action('shutdown', 'nabtron_lazy_buffer_end',1);
	//add_action('wp_head', 'nabtron_lazy_buffer_start');
	//add_action('wp_footer', 'nabtron_lazy_buffer_end');
	add_action('wp_footer', 'nabtron_lazyload_regular_footer');
}

function nabtron_lazyload_regular_footer() {
    echo '
<script src="https://cdn.jsdelivr.net/npm/lazyload@2.0.0-rc.2/lazyload.js"></script>
<script type="text/javascript">
    (function($){
		$(document).ready(function(){
			lazyload();
//      		$("img.lazyload").lazyload();
		});
    })(jQuery);
</script>
';
}
/* regular lazy load of images ends */
