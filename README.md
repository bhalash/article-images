# Article Images
[article-images.php](article-images.php) is a small library intended for use in either a WordPress theme or plugin. To give pointless exposition:

I had kept a blog for several years before I discovered the [Long Now Foundationn](http://longnow.org/) through Neal Stephenson's 2011 novel [*Anathem*](https://en.wikipedia.org/wiki/Anathem). I've committed to keeping a blog so long as I am able, and I realize that, given enough time, I will eventually move away from WordPress. To that end, I keep [my site's](http://www.bhalash.com/) media content separate from the WordPress media manager.

This convoluted circumstance required me to write an additional set of functions to treat the first image in an article's content as the article's featured image.

## Usage
Several functions in Article Images blindly grab an image for the article without consideration as to what the image may be. Images are used in this order, if they exist:

1. The feature image of the article.
2. The first content image of the article.
3. The fallback image. 

### Fallback Image
Several functions in article-images.php use a last-resort fallback image when the article has neither a featured image nor a content image. This file is [fallback.jpg](fallback.jpg), and its usage is set at the head of article-images.php

You are absolutely welcome to add and set your own fallback image in your own theme! 

### get_post_image_dimensions
This complex function is required for [Open Graph](http://ogp.me/) integration with Facebook. If you use og:image, Facebook [expects](https://developers.facebook.com/docs/sharing/best-practices#images) it to be accompanied by og:image:width and og:image:height respectively.

The content image will be fetched via curl if it does not exist on the local filesystem.

### get_post_image
This function grabs an image for the article without consideration as to what that image may be.

### post_image_css/post_image_html
Get the post image and wrap it as either an <img> tag or as background-image in CSS.

## Support
Your mileage will vary; while this library is suitable for my site, it's compatibility with yours is unknowable. Caveat emptor! Pull requests and forks are welcome. If you have a simple support question, email <mark@bhalash.com>. I'm happy to take on paid commissions for more advanced requests, or paid commissions for other work, period. :) 

## Copyright and License
All code is Copyright (c) 2015 [http://www.bhalash.com/](Mark Grealish). All of the code in the project is licensed under the GPL v3 or later, except as may be otherwise noted.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public > License, version 3, as published by the Free Software Foundation.
> 
> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
> 
> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named LICENSE.