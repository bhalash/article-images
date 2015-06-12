<?php

/**
 * Article Image Functions
 * -----------------------------------------------------------------------------
 * This script contains a number of functions that expand the image functions in
 * WordPress. I do not use the WordPress media manage on my own blog. I am in 
 * blogging for the long term, and expect to move away from WordPres eventually.
 * 
 * Because of this, I use the first image found in content as the featured image
 * on my posts. 
 * 
 * This library is self-contained and aimed at developers who might appreciate
 * a wider range of functinos for their own WordPress project. :)
 * 
 * @category   PHP Script
 * @package    WordPress Article Images
 * @author     Mark Grealish <mark@bhalash.com>
 * @copyright  Copyright (c) 2015 Mark Grealish
 * @license    https://www.gnu.org/copyleft/gpl.html The GNU General Public License v3.0
 * @version    3.0
 * @link       https://github.com/bhalash/article-images
 *
 * This file is part of Article Images.
 *
 * Article Images is free software: you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software 
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 * 
 * Article Images is distributed in the hope that it will be useful, but WITHOUT ANY 
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * Article Images. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Set Fallback Image Path and URL
 * -----------------------------------------------------------------------------
 * Feel free to set your own fallback image URL and path, and aim them wherever.
 */

if (!isset($fallback_image)) {
    $url = plugin_dir_path(__FILE__);
    $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $url);
    $url .= 'fallback.jpg';
    $url = get_site_url() . $url;

    $fallback_image = array(
        // Web-accessible URL. This is a little hacky.
        'url' => $url,
        // Path on the local filesystem relative to this script.
        'path' => __DIR__ . '/fallback.jpg'
    );
}

/** 
 * Return Thumbnail Image URL
 * -----------------------------------------------------------------------------
 * WordPress, by default, only has a handy function to return a glob of HTML-an 
 * image inside an anchor-for a post thumbnail. This wrapper extracts and 
 * returns only the URL.
 * 
 * See: http://www.wpbeginner.com/wp-themes/how-to-get-the-post-thumbnail-url-in-wordpress/
 * 
 * @param   int     $post_id        The ID of the post.
 * @param   int     $thumb_size     The requested size of the thumbnail.
 * @param   bool    $return_arr     Return either the entire thumbnail object or just the URL.
 * @return  string  $thumb_url[0]   URL of the thumbnail.
 * @return  array   $thumb_url      All information on the attachment.
 */

function get_post_thumbnail_url($post_id = null, $thumb_size = 'large', $return_arr = false) {
    if (is_null($post_id)) {
        $post_id = get_the_ID();
    }

    $thumb_id = get_post_thumbnail_id($post_id);
    $thumb_url = wp_get_attachment_image_src($thumb_id, $thumb_size, true);
    return ($return_arr) ? $thumb_url : $thumb_url[0];
}

/**
 * Retrive first image in content.
 * -----------------------------------------------------------------------------
 * I chose not to use the featured image feature in WordPress, because
 * I do not want to be ultimately tied to WordPress as a blogging CMS.
 * 
 * This functions extracts and returns the first found image in the post,
 * no matter what that image happens to be.
 * 
 * See: http://css-tricks.com/snippets/wordpress/get-the-first-image-from-a-post
 *
 * @param   int     $post_id        ID of candidate post.
 * @return  string                  Full URL of the first image found.
 * @return  bool                    Return false if no image found.
 */

function content_first_image($post_id = null) {
    $content = '';
    $matches = array();

    if (is_null($post_id)) { 
        global $post;
        $content = $post->post_content;
    } else {
        $content = get_post($post_id)->post_content;
    }

    preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
    return (!empty($matches[1])) ? $matches[1] : false;
}

/**
 * Determine if Post Content has Image
 * -----------------------------------------------------------------------------
 * Because I habitually do not use post thumbnails, I need to instead determine
 * whether the post's content has an image, and thereafter I grab the first one. 
 * 
 * @param   int     $post_id        ID of candidate post.
 * @return  bool                    Post content has image true/false.
 */

function has_post_image($post_id = null) {
    if (is_null($post_id)) { 
        global $post;
        $content = $post->post_content;
        $post_id = $post->ID;
    } else {
        $content = get_post($post_id)->post_content;
    }

    return (strpos($content, '<img src') !== false);
}

/**
 * Get Post Image for Background
 * -----------------------------------------------------------------------------
 * Returns an image in this order:
 * 
 * 1. Specified post thumbnail in it's large size.
 * 2. First image in post's content.
 * 3. Sitewide fallback image.
 * 
 * @param  int      $post_id
 * @return string   $header_thumb     Thumbnail image, if it exists.
 */

function get_post_image($post_id = null, $fallback_image = null) {
    if (is_null($post_id)) {
        global $post;
        $post_id = $post->ID;
    }

    if (is_null($fallback_image)) {
        global $fallback_image;
    }

    if (has_post_thumbnail($post_id)) {
        $post_image = get_post_thumbnail_url($post_id, 'large'); 
    } else if (has_post_image($post_id)) {
        $post_image = content_first_image($post_id);
    } else {
        $post_image = $fallback_image['url'];
    }

    return $post_image;
}

/**
 * Wrap Post Image as Background Style
 * -----------------------------------------------------------------------------
 * @param   int         $post_id        ID of the post.
 * @return  string      $image          The image, wrapped as background-image.
 */

function post_image_css($post_id = null, $echo = false) {
    $image = 'style="background-image: url(' . get_post_image($post_id) . ');"';

    if ($echo) {
        printf($image);
        return;
    }

    return $image;
}

/**
 * Wrap Post Image as <img>
 * -----------------------------------------------------------------------------
 * @param   int         $post_id        ID of the post.
 * @return  string      $image          The image, wrapped as <img>
 */

function post_image_html($post_id = null, $echo = false, $alt ='') {
    $image = '<img src="' . get_post_image($post_id) . '" alt="' . $alt . '"/>';

    if ($echo) {
        printf($image);
        return;
    }

    return $image;
}

/**
 * Post Attachment Filesystem Path
 * -----------------------------------------------------------------------------
 * @param   int       $post_id        ID of the post.
 * @return  string                    Filesystem path to the attachment.
 */

function post_attachment_path($post_id = null) {
    if (is_null($post_id)) {
        global $post;
        $post_id = $post->ID;
    } 

    return get_attached_file(get_post_thumbnail_id($post_id), 'large');
}

/**
 * Content First Image Filesystem Path
 * -----------------------------------------------------------------------------
 * @param   int       $post_id        ID of the post.
 * @return  string                    Filesystem path to the attachment.
 */


function content_first_image_path($post_id = null) {
    if (is_null($post_id)) {
        global $post;
        $post_id = $post->ID;
    } 

    return url_to_path(content_first_image($post_id));
}

/**
 * Convert URL to Filesystem Path
 * -----------------------------------------------------------------------------
 * /This does not guarantee the file or folder exists/. You must independently
 * test for its existence! 
 * 
 * @param   string      $url        URL to be converted into a local path.
 * @return  string      $path       Path converted from the URL.
 */

function url_to_path($url) {
    $url = parse_url($url);

    $path = array(dirname($_SERVER['DOCUMENT_ROOT']));
    $path[] = '/';
    $path[] = $url['host'];
    $path[] = $url['path'];

    return implode('', $path);
}

/**
 * Get Remote Image Header
 * -----------------------------------------------------------------------------
 * The metadata of a JPG image is stored at the beginning of the file. Only the 
 * header of the file is needed for the purposes of establishing the dimensions.
 * 
 * See: http://stackoverflow.com/a/4635991/1433400
 * 
 * @param   string      $url        URL of the remote file.
 * @return  binary      $data       The binary image.
 */

function get_image_header($url) {
    $headers = array('Range: bytes=0-32768');

    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $data = curl_exec($curl);
    
    curl_close($curl);

    return imagecreatefromstring($data);
}

/**
 * Get Dimensions of Remote Image File
 * -----------------------------------------------------------------------------
 * @param   string      $url            URL of the remote file.
 * @return  array       $dimensions     The dimensions of the remote file.
 */

function get_remote_image_dimensions($url = null) {
filter_var($url, FILTER_VALIDATE_URL)

    $dimensions = array();

    $image = imagecreatefromstring(get_image_header($candidate));
    $dimensions[] = imagesx($image);
    $dimensions[] = imagesy($image);

    return $dimensions;
}

/**
 * Get Post Image Dimensions
 * -----------------------------------------------------------------------------
 * This function uses the same logical priority as get_post_image, with 
 * modifications:
 * 
 * 1. Dimensions of specified post thumbnail in it's large size.
 * 2. Dimensions of first image in post's content.
 * 3. Dimensions of sitewide fallback image.
 * 
 * The modification is for #2, the content image. If the URL isn't explicitly 
 * local, then the URL is first tested as local and then fetched remotely if 
 * that fails.
 * 
 * @param   int     $post_id        ID of the post.
 * @param   string  $fallback       Path to the fallback image.
 * @return  array   $dimensions     The dimensions of the image.
 */

function get_post_image_dimensions($post_id = null, $fallback = null) {
    if (is_null($post_id)) {
        global $post;
        $post_id = $post->ID;
    } 

    if (is_null($fallback_image)) {
        global $fallback_image;
        $fallback = $fallback_image['path'];
    }

    $image = $fallback;
    $dimensions = array();

    if (has_post_thumbnail($post_id)) {
        // 1. Pull image from post thumbnail.
        $image = post_attachment_path($post_id);
    } else if (has_post_image($post_id)) {
        // 2. Pull image from content. Test as local, then treat as remote.
        $candidate_path = content_first_image_path($post_id);

        if (file_exists($candidate_path)) {
            $image = $candidate_path;
        } else {
            $candidate_url = content_first_image($post_id);

            if (function_exists('curl_init') && filter_var($candidate_url, FILTER_VALIDATE_URL)) {
                $dimensions = get_remote_image_dimensions($candidate_url);
            }
        }
    }

    if (empty($dimensions)) {
        // 3. If all else has failed, $image will be the fallback image.
        $dimensions = array_slice(getimagesize($image), 0, 2);
    }

    return $dimensions;
}

?>