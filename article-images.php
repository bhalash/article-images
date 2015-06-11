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
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @version    3.0
 * @link       https://github.com/bhalash/sheepie
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Set Fallback Image Path and URL
 * -----------------------------------------------------------------------------
 * Feel free to set your own fallback image URL and path, and aim them wherever.
 */

if (!defined(FALLBACK_IMAGE_URL)) {
    // Web-accessible URL. This is a little hacky.
    $path = plugin_dir_path(__FILE__);
    $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
    $path .= 'fallback.jpg';
    define('FALLBACK_IMAGE_URL', get_site_url() . $path);
}

if (!defined(FALLBACK_IMAGE_PATH)) {
    // Path on the local filesystem relative to this script.
    define('FALLBACK_IMAGE_PATH' , __DIR__ . '/fallback.jpg');
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

function get_post_image($post_id = null, $fallback_image = FALLBACK_IMAGE_URL) {
    if (is_null($post_id)) {
        global $post;
        $post_id = $post->ID;
    }

    if (has_post_thumbnail($post_id)) {
        $post_image = get_post_thumbnail_url($post_id, 'large'); 
    } else if (has_post_image($post_id)) {
        $post_image = content_first_image($post_id);
    } else {
        $post_image = $fallback_image;
    }

    return $post_image;
}

/**
 * Wrap Background Image in HTML Style
 * -----------------------------------------------------------------------------
 * @param  int    $post_id
 */

function post_image_background($post_id = null, $echo = false) {
    $image = 'style="background-image: url(' . get_post_image($post_id) . ');"';

    if ($echo) {
        printf($image);
        return;
    }

    return $image;
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

function get_post_image_dimensions($post_id = null, $fallback = FALLBACK_IMAGE_PATH) {
    if (is_null($post_id)) {
        global $post;
        $post_id = $post->ID;
    } 

    $image = $fallback;
    $dimensions = array();

    if (has_post_thumbnail($post_id)) {
        // 1. Pull image from post thumbnail.
        $image = get_attached_file(get_post_thumbnail_id($post_id), 'large');
    } else if (has_post_image($post_id)) {
        // 2. Pull image from content. Assuemd to be local path.
        $candidate = content_first_image($post_id);

        if (filter_var($candidate, FILTER_VALIDATE_URL)) {
            /* 2a. If the path instead validates to be a URL. If the file cannot
             * be validated as either existing locally, or fetch and tested, 
             * then the fallback image will be used instead. */

            $candidate = url_to_path($candidate);

            if (file_exists($candidate)) {
                $image = $candidate;
            } else if (function_exists('curl_init')) {
                /* 2b. If the file is not testably local, then it probably 
                 * resides on a different server. Fetch the image header if curl
                 * is available. Otherwise use the fallback image. */
                $image = imagecreatefromstring(get_image_header($candidate));
                $dimensions[] = imagesx($image);
                $dimensions[] = imagesy($image);
            }
        } else {
            $image = $candidate;
        }
    }

    if (empty($dimensions)) {
        // 3. If all else fails, just use the fallback image.
        $dimensions[] = imagesx($image);
        $dimensions[] = imagesy($image);
    }

    return $dimensions;
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

?>