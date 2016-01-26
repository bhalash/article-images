# Article Images
[article-images.php](article-images.php) is a small library that extends some of the benefits of WordPress' [Post Thumbmails][1] feature to images in the content of a blog post. article-images can:

1. Evaluate whether an article contains *any* image content in the form of an `<img>` tag.
2. Agnostically evaluate whether an article contains *any* associated image, either as WordPress Post Thumbnail, or within the article content.
3. Return the first image from the content of a post, convert it to a filesystem path and return its size.
4. Agnostically return the firat image from an article, either from its content or from the WordPress thumbnail.

## S-senpai...why?
[My blog][2] was eight years old when I discovered the [Long Now Foundationn][3] through Neal Stephenson's novel [*Anathem*][4]. *Anathem* and the LNF raised questions about the *long*-term integrity and accessibility of my blog archive. In short: how able am I to move away from WordPress if I decide to do so? article-images is part of my answer to this question: It permits me to enjoy some of the benefits of WordPress without tying my media library to the CMS.

## Usage
Several functions in article-images blindly grab an image from an article without consideration as to what the image may be. Images are used in this order, if they exist:

1. The feature image of the article.
2. The first content image of the article.
3. The script's designated fallback image.

## Fallback Image
Several functions in article-images.php use a last-resort fallback image when the article has neither a featured image nor a content image. This file is [fallback.jpg](fallback.jpg), and its usage is set at the head of article-images.php

You are absolutely welcome to add and set your own fallback image in your own theme!

## Support
This library is suitable for my site, and its compatibility with yours is unknowable. Pull requests and forks are welcome. If you have a simple support question, email <mark@bhalash.com>.

## Copyright and License
All code is Copyright (c) 2015 [http://www.bhalash.com/][2]. All of the code in the project is licensed under the GPL v3 or later, except as may be otherwise noted.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public > License, version 3, as published by the Free Software Foundation.
>
> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
>
> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named LICENSE.

[1]: https://codex.wordpress.org/Post_Thumbnails
[2]: https://www.bhalash.com
[3]: http://longnow.org/
[4]: https://en.wikipedia.org/wiki/Anathem
