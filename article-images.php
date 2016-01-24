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
 * @license    https://www.gnu.org/copyleft/gpl.html The GNU GPL v3.0
 * @version    1.0
 * @link       https://github.com/bhalash/article-images
 */

/**
 * Set Fallback Image Path and URL
 * -----------------------------------------------------------------------------
 * This runs during WordPress init, or whenever else you call it. If the option
 * hasn't been set, or $fallback isn't null, it'll set up the image. Correct
 * format is up to yourself.
 *
 * @param   array       $fallback           Fallback images.
 */

function set_post_fallback_image($fallback = null) {
    if ($fallback || !get_option('article_images_fallback')) {
        $fallback = $fallback ?: [
            // Web-accessible URL from directory path.
            'url' => str_replace($_SERVER['DOCUMENT_ROOT'], get_site_url(), __DIR__) . '/images/ai_fallback.jpg',
            // Path on the local filesystem relative current directory.
            'path' => __DIR__ . '/images/ai_fallback.jpg',
        ];

        update_option('article_images_fallback', $fallback, true);
    }
}

add_action('init', 'set_post_fallback_image', 10, 1);

/**
 * Determine if Post Content has Image
 * -----------------------------------------------------------------------------
 * Because I habitually do not use post thumbnails, I need to instead determine
 * whether the post's content has an image, and thereafter I grab the first one.
 *
 * @param   int     $post        ID of candidate post.
 * @return  bool    Post content has image true/false.
 */

function has_post_image($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return false;
    }

    return has_post_thumbnail($post) || preg_match($post->post_content, '/<img\s.*?src=".*?\/>$/m') !== false;
}

/**
 * Return Thumbnail Image URL
 * -----------------------------------------------------------------------------
 * WordPress, by default, only has a handy function to return a glob of HTML-an
 * image inside an anchor-for a post thumbnail. This wrapper extracts and
 * returns only the URL.
 *
 * @link http://www.wpbeginner.com/wp-themes/how-to-get-the-post-thumbnail-url-in-wordpress/
 * @param   int     $post           The ID of the post.
 * @param   int     $thumb_size     The requested size of the thumbnail.
 * @param   bool    $return_arr     Return either the entire thumbnail object or just the URL.
 * @return  string  $thumb_url[0]   URL of the thumbnail.
 * @return  array   $thumb_url      All information on the attachment.
 */

function get_post_thumbnail_url($post = null, $thumb_size = 'large', $return_arr = false) {
    $post = get_post($post);

    if (!$post) {
        return false;
    }

    $thumb_id = get_post_thumbnail_id($post->ID);
    $thumb_url = wp_get_attachment_image_src($thumb_id, $thumb_size, true);
    return ($return_arr) ? $thumb_url : $thumb_url[0];
}

/**
 * Retrieve First Content Image
 * -----------------------------------------------------------------------------
 * I chose not to use the featured image feature in WordPress, because
 * I do not want to be ultimately tied to WordPress as a blogging CMS.
 *
 * This functions extracts and returns the first found image in the post,
 * no matter what that image happens to be.
 *
 * @param   int     $post        ID of candidate post.
 * @return  string  Full URL of the first image found.
 * @return  bool    Return false if no image found.
 * @link http://css-tricks.com/snippets/wordpress/get-the-first-image-from-a-post
 */

function content_first_image($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return;
    }

    $post = $post->post_content;
    $matches = array();

    preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post, $matches);
    return (!empty($matches[1])) ? $matches[1] : false;
}

/**
 * Get Post Image
 * -----------------------------------------------------------------------------
 * Returns an image in this order:
 *
 * 1. Specified post thumbnail in it's large size.
 * 2. First image in post's content.
 * 3. Sitewide fallback image.
 *
 * @param   int/object      $post
 * @return  string                                  Thumbnail image, if it exists.
 */

function get_post_image($post = null, $size = 'large', $fallback_image = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return;
    }

    if (!$fallback_image) {
        $fallback_image = get_option('article_images_fallback');

        if (!$fallback_image) {
            $fallback_image = set_fallback_image();
        }
    }

    if (has_post_thumbnail($post->ID)) {
        $post_image = get_post_thumbnail_url($post->ID, $size);
    } else if (has_post_image($post->ID)) {
        $post_image = content_first_image($post->ID);
    } else {
        $post_image = $fallback_image['url'];
    }

    return $post_image;
}

/**
 * Echo Post Image
 * -----------------------------------------------------------------------------
 * @param   int/object      $post
 * @param   string          $fallback_image         URL or path to fallback image.
 * @return  string                                  Thumbnail image, if it exists.
 */

function the_post_image($post = null, $size = null, $fallback_image = null) {
    printf(get_post_image($post, $size, $fallback_image));
}

/**
 * Wrap Post Image as Background Style
 * -----------------------------------------------------------------------------
 * @param   int         $post        ID of the post.
 * @return  string      $image          The image, wrapped as background-image.
 */

function post_image_css($post = null, $echo = false) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return;
    }

    $image = 'style="background-image: url(' . get_post_image($post->ID) . ');"';

    if (!$echo) {
        return $image;
    }

    printf($image);
}

/**
 * Wrap Post Image as <img>
 * -----------------------------------------------------------------------------
 * @param   int         $post        ID of the post.
 * @return  string      $image          The image, wrapped as <img>
 */

function post_image_html($post = null, $size = 'large', $echo = false, $alt = '') {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return;
    }

    if (!$alt) {
        $alt = the_title_attribute(array(
            'post' => $post,
            'echo' => false
        ));
    }

    $src = get_post_image($post->ID, $size);

    $image = sprintf('<img class="%s" src="%s" alt="%s" />',
        'post-image post-thumbnail',
        $src,
        $alt
    );

    if (!$echo) {
        return $image;
    }

    printf($image);
}

/**
 * Post Attachment Filesystem Path
 * -----------------------------------------------------------------------------
 * @param   int       $post        ID of the post.
 * @return  string                    Filesystem path to the attachment.
 */

function post_attachment_path($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return;
    }

    return get_attached_file(get_post_thumbnail_id($post->ID), 'large');
}

/**
 * Content First Image Filesystem Path
 * -----------------------------------------------------------------------------
 * @param   int       $post        ID of the post.
 * @return  string                    Filesystem path to the attachment.
 */


function content_first_image_path($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return;
    }

    return url_to_path(content_first_image($post->ID));
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
 * @param   int     $post        ID of the post.
 * @param   string  $fallback       Path to the fallback image.
 * @return  array   $dimensions     The dimensions of the image.
 */

function get_post_image_dimensions($post = null, $fallback_image = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return;
    }

    if (!$fallback_image) {
        $fallback_image = get_option('article_images_fallback')['path'];

        if (!$fallback_image) {
            $fallback_image = set_fallback_image()['path'];
        }
    }

    $image = $fallback_image;
    $dimensions = array();

    if (has_post_thumbnail($post)) {
        // 1. Pull image from post thumbnail.
        $image = post_attachment_path($post);
    } else if (has_post_image($post)) {
        // 2. Pull image from content. Test as local, then treat as remote.
        $candidate_path = content_first_image_path($post);

        if (file_exists($candidate_path)) {
            $image = $candidate_path;
        }
    }

    if (empty($dimensions)) {
        // 3. If all else has failed, $image will be the fallback image.
        $dimensions = array_slice(getimagesize($image), 0, 2);
    }

    return $dimensions;
}

?>
