<?php

/*
  Plugin Name: Head META Facebook
  BASED on Head META description Plugin by Kaf Oseo (http://szub.net)
  Plugin URI: http://www.non.co.il
  Description: Insert HTML META for facebook opengraph
  Author: Yoni Jah <yoni@non.co.il>
  Version: 1.03
  Author URI: http://www.non.co.il

  Copyright (c) 2011, 2012 Yoni Jah (http://www.non.co.il)
  Head META Description is released under the GNU General Public
  License (GPL) http://www.gnu.org/licenses/gpl.txt

  This is a WordPress plugin (http://wordpress.org).
 *
 * ##change log##
 *
 * 1.03 plugin will now use image thumbnails instead of fullsized images
 * 1.02 added missing Samech to hebrew letter arrray
 * 1.01 added suppot for multiple image and maybe other tags
 * 1.01 cleared default settings
 * 
 *
 */

function head_meta_facebook() {
    // >> user-configurable variables
    /* the following two variables wiil be set for tag og:type acordingly
     * they default should be sufficent to all blogs by facebook recomendations  */
    $home_type = 'blog';
    $single_type = 'article';

    /** array of tags to be set tag name => tag value.
     * you can add or remove tags as you wish (check facebook opengraph protocol
     * Tags with empty value will not be displayed
     * if value is an array multiple tags will be created (mainily for multiple og:image tag)
     *
     * @link https://developers.facebook.com/docs/opengraph/
     */
    $tags = array(
        'og:title' => head_meta_facebook_title('»'),
        'og:url' => head_meta_facebook_url(),
        'og:type' => '', //leave empty if you wish to get tag from setting
        'og:image' => head_meta_facebook_image('url_to_deafult_image','thumbnail'),
        'og:site_name' => get_bloginfo('name'),
        'og:description' => head_meta_facebook_description(),
        'fb:admins' => '',
        'fb:app_id' => '',
    );
    // << user-configurable variables
    global $wp_query;

    if (!$tags['og:type']) {
        if (is_home() || is_front_page())
            $tags['og:type'] = $home_type;
        else
            $tags['og:type'] = $single_type;
    }

    foreach ($tags as $name => $val) {
        if (is_array($val)) {
            /* some tags allow multiple info to be returned
             * so if set array of vals build a tag for every object */
            foreach ($val as $item) {
                if ($item)
                    echo "<meta property=\"$name\" content=\"$item\"/>\n";
            }
        }
        else if ($val)
            echo "<meta property=\"$name\" content=\"$val\"/>\n";
    }
    return TRUE;
}

/** Return the description of the current page.
 * this is basiclly a copy of the original head_meta_description function
 *
 * @return string description of page
 */
function head_meta_facebook_description() {
    /* >> user-configurable variables */
    $default_blog_desc = ''; // default description (setting overrides blog tagline)
    $post_desc_length = 20; // description length in # words for post/Page
    $post_use_excerpt = 1; // 0 (zero) to force content as description for post/Page
    $custom_desc_key = 'description'; // custom field key; if used, overrides excerpt/content
    /* << user-configurable variables */

    global $cat, $cache_categories, $wp_query, $wp_version;
    if (is_single() || is_page()) {
        $post = $wp_query->post;
        $post_custom = get_post_custom($post->ID);
        if (isset($post_custom["$custom_desc_key"][0]))
            $custom_desc_value = $post_custom["$custom_desc_key"][0];

        if (isset($custom_desc_value)) {
            $text = $custom_desc_value;
        } elseif ($post_use_excerpt && !empty($post->post_excerpt)) {
            $text = $post->post_excerpt;
        } else {
            $text = $post->post_content;
        }
        $text = str_replace(array("\r\n", "\r", "\n", "  "), " ", $text);
        $text = str_replace(array("\""), "", $text);
        $text = trim(strip_tags($text));
        $text = explode(' ', $text);
        if (count($text) > $post_desc_length) {
            $l = $post_desc_length;
            $ellipsis = '...';
        } else {
            $l = count($text);
            $ellipsis = '';
        }
        $description = '';
        for ($i = 0; $i < $l; $i++)
            $description .= $text[$i] . ' ';

        $description .= $ellipsis;
    } elseif (is_category ()) {
        $category = $wp_query->get_queried_object();
        $description = trim(strip_tags($category->category_description));
    } else {
        $description = (empty($default_blog_desc)) ? trim(strip_tags(get_bloginfo('description'))) : $default_blog_desc;
    }
    return $description;
}

/** Return the title of the current page.
 * title will not nesseserly be identicle to page title
 * but wiil be calculated from page data
 *
 * @param string $sep seperator to use betwin title section default is »
 * @return string title of page
 */
function head_meta_facebook_title($sep = '»') {
    global $page, $paged;
    $title = get_bloginfo('name');
    $title .= wp_title($sep, false);
    // Add a page number if necessary:
    if ($paged >= 2 || $page >= 2)
        $title .= $sep . sprintf(__('Page %s', 'head-facebook-meta'), max($paged, $page));
    return $title;
}

/** Return the url of the current page. 
 *
 * @return string url of page
 */
function head_meta_facebook_url() {
    global $wp_query;
    if (is_single() || is_page()) {
        $post = $wp_query->post;
        $post_custom = get_post_custom($post->ID);
        return head_meta_facebook_heb_fix(get_permalink($post->ID));
    }
    else
        return get_bloginfo('wpurl'); //assuming non single pages has no unique url
}

/** Return the url after urldecode hebrew charecters.
 * will make sure url will look nicely on facebookshare
 *
 * @param string $url url
 * @return string url of page
 */
function head_meta_facebook_heb_fix($url) {
    $heb = array('א', 'ב', 'ג', 'ד', 'ה', 'ו', 'ז', 'ח', 'ט', 'י', 'כ', 'ך', 'ל', 'מ', 'ם', 'נ', 'ן', 'ס', 'ע', 'פ', 'ף', 'צ', 'ץ', 'ק', 'ר', 'ש', 'ת');
    $enc = array('%D7%90', '%D7%91', '%D7%92', '%D7%93', '%D7%94', '%D7%95', '%D7%96', '%D7%97', '%D7%98', '%D7%99', '%D7%9B', '%D7%9A', '%D7%9C', '%D7%9E', '%D7%9D', '%D7%A0', '%D7%9F', '%D7%A1', '%D7%A2', '%D7%A4', '%D7%A3', '%D7%A6', '%D7%A5', '%D7%A7', '%D7%A8', '%D7%A9', '%D7%AA');
    return str_ireplace($enc, $heb, $url);
}

/** Return the url of the first post image.
 * title will not be identiclr to page title
 * but wiil be calculated from page data
 *
 * @param string $default default image url in case no image was found (default is null)
 * @param mixed $size size to pass to image_get_intermediate_size deafult = thumbnail, if set to NULL function will return fullsize images
 * @return string url of page
 */
function head_meta_facebook_image($default = '', $size='thumbnail') {
    global $wp_query;
    $images = array();
    if (is_single() || is_page()) {
        $post = $wp_query->post;
        $post_custom = get_post_custom($post->ID);
        $attachments = get_children($post->ID);
        //runs on all the attacments and return the url of the first image attachment
        while ($attachment = array_shift($attachments)) {
            $url = '';
            if (strpos($attachment->post_mime_type, 'image') !== false) {
                //if size was set get thumbnail by size
                if ($size) {
                    $url = image_get_intermediate_size($attachment->ID, $size);
                    $url = $url['url'];
                }
                //if no thumbnail was found use fullsize image
                if ($url)
                    $images[] = $url;
                else
                    $images[] = $attachment->guid;
            }
        }
        //return $default in case no other url was returned
        if (empty($images))
            return $default;
        else
            return $images;
    }
    else
        return $default; //assuming non single pages has no images attachments
}

add_action('wp_head', 'head_meta_facebook');
?>
