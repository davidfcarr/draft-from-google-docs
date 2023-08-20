<?php
/*
Plugin Name: Draft from Google Docs
Plugin URI: https://rsvpmaker.com/draft-from-google-docs/
Description: If you use Google Docs to edit and revise content with a team of people, you can now create a draft blog post or page in WordPress without cluttering your web content with inappropriate HTML and inline styles. The tool extracts images and allows you to download them or import them directly into WordPress.
Author: David F. Carr
Author URI: http://www.carrcommunications.com
Version: 1.1
*/

add_shortcode('draft_from_google_docs','draft_from_google_docs');

function draftgd_imagesize() {
    $imgsize = draft_from_google_image_size();
    add_image_size('max'.$imgsize,$imgsize,$imgsize);
}
add_action('after_setup_theme','draftgd_imagesize');

add_filter( 'image_size_names_choose', 'draft_from_google_custom_sizes' );
 
function draft_from_google_custom_sizes( $sizes ) {
    $imgsize = draft_from_google_image_size();
    return array_merge( $sizes, array(
        'max'.$imgsize => __( 'Max Width: '.$imgsize.'px' ),
    ) );
}

add_action('admin_menu','draft_from_google_docs_menu');
function draft_from_google_docs_menu() {
    add_submenu_page('edit.php','Import Google Doc','Import Google Doc','edit_posts','draft_from_google_docs','draft_from_google_docs');
}

function draft_from_google_nq() {
    global $post;
    if( (isset($_GET['page']) && 'draft_from_google_docs' == $_GET['page']) || (isset($post->post_content) && strpos($post->post_content,'[draft_from_google_docs')) ) {
        wp_enqueue_script( 'wp-tinymce' );
        wp_enqueue_script( 'draft-google', plugins_url( 'draft-from-google-docs/drafty.js' ), array('wp-tinymce'), '3.1', true );    
    }
}
add_action( 'wp_enqueue_scripts', 'draft_from_google_nq', 10000 );
add_action( 'admin_enqueue_scripts', 'draft_from_google_nq' );

function draft_from_google_docs_link($arg) {
    $link = $arg[0]; // without closing tag
    if(!strpos($link,$_SERVER['SERVER_NAME']))
        $link .= ' target="_blank"';
    return $link;
}

function draft_from_google_docs_image($img) {
    global $imgcount, $download_images, $image_slug, $download_ok, $editor_format;
    $imgcount++;
    $src = $img[1];
    $imgsize = draft_from_google_image_size();
    $image = '<img style="max-width: 200px; object-fit: contain; object-position: bottom;" src="'.$src.'">';
    if($download_ok) {
        $attach_id = draft_from_insert_attachment_from_url( $src, $image_slug.'_'.$imgcount.'.png' );
        if($attach_id) {
            $imgarr = wp_get_attachment_image_src($attach_id,'max'.$imgsize);
            $url = $imgarr[0];
            if('block' == $editor_format) {
                $imghtml = "\n\n".'<!-- wp:image {"id":'.$attach_id.',"width":'.$imgarr[1].',"height":'.$imgarr[2].',"sizeSlug":"max'.$imgsize.'"} -->'."\n".'<figure class="wp-block-image size-max'.$imgsize.'"><img src="'.$url.'" width="'.$imgarr[1].'" height="'.$imgarr[2].'" alt="" class="wp-image-'.$attach_id.'"'."></figure>\n<!-- /wp:image -->\n\n";
            }
            else {
                $imghtml = wp_get_attachment_image($attach_id,'max'.$imgsize, false, array('sizes' => implode(',',get_intermediate_image_sizes())));
                $imghtml = str_replace('class="','class="wp-image-'.$attach_id.' ',$imghtml);
            }
            $imghtml = str_replace('loading="lazy"','',$imghtml);
            $dlimagehtml = str_replace('<img ','<img style="max-width: 200px; object-fit: contain; object-position: bottom;" ',$imghtml);
            $dlimagehtml = preg_replace('/height="[^"]+"/','',$dlimagehtml);
            $dlimagehtml = preg_replace('/width="[^"]+"/','',$dlimagehtml);
            if(!is_admin())
                $download_images .= sprintf('<span style="display: inline-block; margin: 10px;"><a href="%s" download>%s</a></span>',$url,$dlimagehtml);
            if($download_ok && !is_admin()) //shortcode version
                return sprintf('<p>*** IMAGE %s GOES HERE *** %s</p>',$imgcount, wp_get_attachment_image($attach_id,'thumbnail', false));
            return $imghtml;
        }
        else
            printf('<p><strong>Error importing image %s</strong><br />%s</p>',esc_html($image_slug).'_'.$imgcount.'.png',$src);
    }
    else {
        $download_images .= sprintf('<span style="display: inline-block; margin: 10px;"><a href="%s" download="%s.png" target="_blank">%s</a></span>',$match[1], $image_slug.'_'.$imgcount, $image);
    }
    return sprintf('<p>*** IMAGE %s GOES HERE ***</p>',$imgcount);
}

function draft_from_google_docs_options() {
    if(isset($_POST['allowed_types']) && wp_verify_nonce( $_POST['drafty_field'], 'drafty' ) )
    {
        $types = get_post_types();
        $builtin = array("attachment", "revision", "nav_menu_item", "custom_css", "customize_changeset", "oembed_cache", "user_request", "wp_block", "wp_template", "wp_template_part", "wp_global_styles", "wp_navigation");
        $legal_types = array_diff($types,$builtin);
        foreach($_POST['allowed_types'] as $type) {
            if(in_array($type,$legal_types))
                $allowed_types[] = sanitize_text_field($type);
        }
        update_option('draft_from_google_docs_allowed',$allowed_types);
        $imgsize = intval($_POST['imgsize']);
        update_option('docs_from_google_image_size',$imgsize);
    }
}
add_action('admin_init','draft_from_google_docs_options');

function draft_from_google_docs($atts = array()) {

  global $imgcount, $image_slug, $download_images, $current_user, $download_ok, $editor_format, $wp_scripts;
  if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
  $editor_format = is_plugin_active('classic-editor/classic-editor.php') ? 'classic' : 'block';
  $download_ok = (is_admin() || isset($atts['download_ok']));
    $imgcount = 0;
    $download_images = '';
    $action = (is_admin()) ? admin_url('edit.php?page=draft_from_google_docs') : get_permalink();
    $types = get_post_types();
    $builtin = array("attachment", "revision", "nav_menu_item", "custom_css", "customize_changeset", "oembed_cache", "user_request", "wp_block", "wp_template", "wp_template_part", "wp_global_styles", "wp_navigation");
    $legal_types = array_diff($types,$builtin);

    $imgsize = get_option('docs_from_google_image_size');
    if(empty($imgsize))
        $imgsize = 600;

    ob_start();
    if(isset($_POST['copy']) && wp_verify_nonce( $_POST['drafty_field'], 'drafty' ) )
    {
        $target = sanitize_text_field($_POST['draft_from_google_docs_target']);
        ?>
        <div style="width: 200px; float: right; margin-left: 10px; padding: 5px; border: thin dotted #000"><a href="<?php echo esc_url($action) ?>">Show form again</a></div>
    <?php
        $rawcopy = $copy = stripslashes($_POST['copy']); //sanitized html
        preg_match('/<([^>]+)*><span([^>]+)*>([^<]+)/',$copy,$match);
        if(!empty($match[3]))
            $title = $match[3];
        else
            $title = substr(preg_replace('/\s{2,}/',' ',trim(strip_tags($copy))),0,100);
        $copy = preg_replace('/<span[^>]+bold[^>]+>([^<]+)<\/span>/',"<strong>$1</strong>",$copy);
        $copy = preg_replace('/<span[^>]+italic[^>]+>([^<]+)<\/span>/',"<em>$1</em>",$copy);
        $copy = preg_replace('/<(p|ul|li|h1|h2|h3|h4|h5|span|div)[^>]+>/',"<$1>",$copy);
        $copy = str_replace('<span>','',$copy);
        $copy = str_replace('</span>','',$copy);
        $copy = str_replace('<p>&nbsp;</p>','',$copy);
        $copy = preg_replace('/<li>\s*<p>/m','<li>',$copy);
        $copy = preg_replace('/<\/p>\s*<\/li>/m','</li>',$copy);
        //img should not be wrapped in a heading tag
        $copy = preg_replace('/<h.*>.*(<img[^>]+>).*<\/h.>/m',"<p>$1</p>", $copy);

        if(empty($_POST['image_slug']))
            {
                $p = explode(' ',strtolower($title));
                $p = array_slice($p,0,5);
                $image_slug = implode('_',$p);
            }
        else
            $image_slug = preg_replace('/[^a-z]+/','_',strtolower($_POST['image_slug']));
        if('_blank' == $target) {
            $copy = preg_replace_callback('/<a [^>]+/','draft_from_google_docs_link', $copy);
        }

        if('block' == $editor_format)
        {
            $copy = preg_replace('/<h.>\s*(<img[^>]>)\s*<\/h.>/m',"<p>$1</p>", $copy);
            $copy = preg_replace_callback('/<p>\s*<img[^>]+src="([^"]+)"[^>]*>\s*<\/p>/m','draft_from_google_docs_image', $copy);
            $copy = str_replace('<ul>',"\n<!-- wp:list -->\n<ul>",$copy);
            $copy = str_replace('</ul>',"</ul>\n<!-- /wp:list -->\n\n",$copy);
            $copy = preg_replace('/<p[^>]*>/',"<!-- wp:paragraph -->\n<p>",$copy);
            $copy = str_replace('</p>',"</p>\n<!-- /wp:paragraph -->\n\n",$copy);
            $copy = preg_replace('/<h([1-9])[^>]*>/',"\n<!-- wp:heading {\"level\":$1} -->\n<h$1>",$copy);
            $copy = preg_replace('/<\/h[1-9]>/',"$0\n<!-- /wp:heading -->\n\n",$copy);
        }
        else
            $copy = preg_replace_callback('/<img[^>]+src="([^"]+)"[^>]*>/','draft_from_google_docs_image', $copy);

        if(is_admin() && !empty($_POST['ptype']) && in_array($_POST['ptype'],$legal_types))
            {
                $new['post_title'] = sanitize_text_field($title);
                $new['post_content'] = wp_kses_post($copy);
                $new['post_type'] = $_POST['ptype'];
                $new['post_status'] = 'draft';
                $new['post_author'] = $current_user->ID;
                if(!empty($_POST['image_slug']))
                    $new['post_name'] = preg_replace('/[^A-Za-z]/','-',$_POST['image_slug']);
                if(!empty($_POST['category'])) {
                    $category = intval($_POST['category']);
                    $new['post_category'] = array($category);
                    update_user_meta($current_user->ID,'dfgd_category',$category);
                }
                $post_id = wp_insert_post($new);
                if($post_id)
                    printf('<h1>Create WordPress Draft from Google Docs Content</h1><p><a href="%s">Edit Draft in WordPress</a>, %s</p><p><a href="%s">Add another</a></p><p>A preview is shown below</p>',admin_url("post.php?post=$post_id&action=edit"), $new['post_title'], esc_url($action));
                //return;
            }
        if(!empty($download_images))
            echo '<p><strong>Step 1</strong> Click on the each of the images shown below to download copy to your computer.</p><p>'.wp_kses_post($download_images).'</p><p><strong>Step 2</strong> Copy the text below, which has been cleaned up to remove excess formatting commands, and paste it into the WordPress editor.</p><p><strong>Step 3</strong> Upload the full size images to replace the placeholders.</p>';
        echo '<hr><button onclick="copyDivToClipboard()">Copy text to clipboard</button><div id="cleancopy">'.wp_kses_post($copy).'</div><hr><button class="copy-text">Copy text to clipboard</button>';
        if(isset($_POST['debug']))
            echo '<h2>Google HTML:</h2>'.$rawcopy;
    }
    else {
        $target = get_option('draft_from_google_docs_target');
        if(empty($target))
            $target = '_blank';
?>
<h1>Create WordPress Draft from Google Docs Content</h1>
<form method="post" action="<?php echo esc_url($action); ?>">
<p>Paste copy from Google Docs into the textarea below and click submit.</p>
<p><textarea id="mytextarea" name="copy"></textarea></p>
<p>Label for url slug and image file names <input name="image_slug"> <em>a few keywords related the topic of your report</em></p>
<?php
    echo '<p>Open external links in <input type="radio" name="draft_from_google_docs_target" value="_blank" '.(('_blank' == $target) ? 'checked="checked"' : '').'> New tab <input type="radio" name="draft_from_google_docs_target" value="self" '.(('_blank' != $target) ? 'checked="checked"' : '').'> Same tab</p>';
    echo '<p><input type="checkbox" name="debug" value="1"> Show original Google HTML (for debugging)</p>';
    if(is_admin()) {
        $allowed_types = get_option('draft_from_google_docs_allowed');
        if(empty($allowed_types))
            $allowed_types = array('post');
        $rsvp_types = (is_plugin_active('rsvpmaker/rsvpmaker.php')) ? ' <input type="radio" name="ptype" value="rsvpmaker" > RSVPMaker Event <input type="radio" name="ptype" value="rsvpemail" > RSVPMaker Email ' : '';
        echo '<p>Create draft <input type="radio" name="ptype" value="post" checked="checked"> post ';
        foreach($allowed_types as $type)
            if('post' == $type)
                continue;
            else
                printf('<input type="radio" name="ptype" value="%s"> %s ',$type, $type);
        echo '<input type="radio" name="ptype" value="" > Do not create a draft, display for copy-and-paste </p>';
    }
    dfgd_category_picker(get_user_meta($current_user->ID,'dfgd_category',true));
    wp_nonce_field( 'drafty', 'drafty_field' );
    if(is_admin())
        submit_button('Submit');
    else
        echo '<p><button>Submit</button></p>';
    echo '</form>';

if(current_user_can('manage_options') && is_admin() )
        {
        $imgsize = draft_from_google_image_size();
        echo '<p><button id="showoptions">Show Options</button></p>';
        echo '<div id="options" style="display: none;"><h2>Options</h2>';
            echo '<form method="post" action="'.$action.'">';
            echo '<p>In addition to blog posts, allow import of post types including ';
            foreach($legal_types as $type)
                if($type == 'post')
                    echo '<input type="hidden" name="allowed_types[]" value="post" >';
                else
                    printf('<input type="checkbox" name="allowed_types[]" value="%s" %s > %s ',$type, (in_array($type,$allowed_types)) ? ' checked="checked" ' : '', $type);
            echo '</p>';
            printf('<p>Max Width for Images <input type="text" name="imgsize" value="%d" ></p>',$imgsize);
            wp_nonce_field( 'drafty', 'drafty_field' );
            submit_button('Set Options');
            echo '</form></div>';
        }

    }

    //print_r($wp_scripts);

    if(is_admin())
        ob_get_flush();
    else
        return ob_get_clean();
}

function draft_from_google_image_size() {
    $imgsize = get_option('docs_from_google_image_size');
    if(empty($imgsize))
        $imgsize = 512;
    return $imgsize;
}

// adapted from https://gist.github.com/m1r0/f22d5237ee93bcccb0d9
function draft_from_insert_attachment_from_url( $url, $file_name, $parent_post_id = null ) {

    if(strpos($url,'base64')) {
        $parts = explode('base64,',$url);
        $b64 = trim($parts[1]);
        $image = base64_decode($b64);
        $upload = wp_upload_bits( $file_name, null, $image );
    }
    else {
        if ( ! class_exists( 'WP_Http' ) ) {
            require_once ABSPATH . WPINC . '/class-http.php';
        }
    
        $http     = new WP_Http();
        $response = $http->request( $url );
         if ( !is_array($response) || ( 200 !== $response['response']['code'] ) ) {
            if(current_user_can('manage_options'))
                print_r($response);
            return false;
        }
        $upload = wp_upload_bits( $file_name, null, $response['body'] );
    }

	if ( ! empty( $upload['error'] ) ) {
		return false;
	}

	$file_path        = $upload['file'];
	$file_type        = 'image/png';
	$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
	$wp_upload_dir    = wp_upload_dir();

	$post_info = array(
		'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
		'post_mime_type' => $file_type,
		'post_title'     => $attachment_title,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	// Create the attachment.
	$attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

	// Include image.php.
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Generate the attachment metadata.
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
 
	// Assign metadata to attachment.
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;
}

function dfgd_category_picker($pick = '') {
    $o = '<option></option>';
    $categories = get_categories( array(
        'orderby' => 'name',
        'parent'  => 0 // top level only
    ) );
    foreach($categories as $category) {
        $s = ($pick == $category->term_id) ? ' selected="selected" ' : '';
        $o .= sprintf('<option value="%s" %s>%s</option>',$category->term_id, $s, $category->name);
    }
    echo '<p>Primary Category: <select name="category">'.$o.'</select></p>';
}