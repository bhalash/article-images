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
 * @version    1.5
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
    if ($fallback || !get_option('article_images_fallback' || WP_DEBUG)) {
        $fallback = $fallback ?: [
            // Web-accessible URL from directory path.
            'url' => str_replace($_SERVER['DOCUMENT_ROOT'], get_site_url(), __DIR__) . '/ai_fallback.jpg',
            // Path on the local filesystem relative current directory.
            'path' => __DIR__ . '/ai_fallback.jpg',
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
 * This extends the functionality of has_post_thumbnail() to include post
 * content images.
 *
 * @param   int     $post       Post ID or object.
 * @return  bool                Post has image true/false.
 */

function has_post_image($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return false;
    }

    return has_post_thumbnail($post) || has_post_content_image($post);
}

/**
 * See if Post Content Contains Image
 * -----------------------------------------------------------------------------
 * @param   int/object  $post           Post ID or post object.
 * @return                              Post content has image, true/false.
 */

function has_post_content_image($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return false;
    }

    return preg_match('/<img\s.*?src=".*?\/>/', $post->post_content) !== false;
}

/**
 * Return Thumbnail Image URL
 * -----------------------------------------------------------------------------
 * WordPress, by default, only has a handy function to return a glob of HTML-an
 * image inside an anchor-for a post thumbnail. This wrapper extracts and
 * returns only the URL.
 *
 * @param   int/object  $post           Post ID or post object.
 * @param   int         $thumb_size     The requested size of the thumbnail.
 * @param   bool        $return_arr     Return thumbnail object or just the URL.
 * @return  string      $thumb_url[0]   URL of the thumbnail.
 * @return  array       $thumb_url      All information on the attachment.
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
 * @param   int/object  $post           Post ID or post object.
 * @return  string                      Full URL of the first image found.
 * @link    http://goo.gl/WIloQw
 */

function content_first_image($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return '';
    }

    $post = $post->post_content;
    $matches = [];

    preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post, $matches);
    return (!empty($matches[1])) ? $matches[1] : '';
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
 * @param   int/object  $post           Post ID or post object.
 * @return  string                      Thumbnail image, if it exists.
 */

function get_post_image($post = null, $size = 'large') {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return '';
    }

    $image = get_option('article_images_fallback')['url'];

    if (has_post_thumbnail($post->ID)) {
        $image = get_post_thumbnail_url($post->ID, $size);
    } else if (has_post_content_image($post)) {
        $image = content_first_image($post->ID);
    }

    return $image;
}

/**
 * Echo Post Image
 * -----------------------------------------------------------------------------
 * @param   int/object  $post           Post ID or post object.
 * @return                              Thumbnail image, if it exists.
 */

function the_post_image($post = null, $size = null) {
    printf(get_post_image($post, $size));
}

/**
 * Wrap Post Image as Background Style
 * -----------------------------------------------------------------------------
 * @param   int/object  $post           Post ID or post object.
 * @return  string      $image          The image, wrapped as background-image.
 */

function post_image_css($post = null, $echo = false) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return '';
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
 * @param   int/object  $post           Post ID or post object.
 * @return  string      $image          The image, wrapped as <img>
 */

function post_image_html($post = null, $size = 'large', $echo = false, $alt = '') {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return '';
    }

    if (!$alt) {
        $alt = the_title_attribute([
            'post' => $post,
            'echo' => false
        ]);
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
 * @param   int/object  $post           Post ID or post object.
 * @return  string                      Filesystem path to the attachment.
 */

function post_attachment_path($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return '';
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
        return '';
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

    $path = [dirname($_SERVER['DOCUMENT_ROOT'])];
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
 * @param   int/object  $post           Post ID or post object.
 * @return  array       $dimensions     The dimensions of the image.
 */

function get_local_image_dimensions($post = null) {
    if (!($post = get_post($post))) {
        global $post;
    }

    if (!$post) {
        return [];
    }

    $image = '';

    if (has_post_thumbnail($post)) {
        // 1. Pull image from post thumbnail.
        $image = post_attachment_path($post);
    } else if (has_post_content_image($post)) {
        // 2. Pull image from content. Test as local, then treat as remote.
        $image = content_first_image_path($post);
    }

    // FIXME
    // Empty image getting passed by default image.

    if (!$image || !file_exists($image)) {
        // 0. If all else has failed, $image will be the fallback image.
        $image = get_option('article_images_fallback')['path'];
    }

    // Return first two array elements: width and height as ints;
    return $image ? array_slice(getimagesize($image), 0, 2) : [];
}

?>
