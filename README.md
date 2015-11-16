<table width="100%">
	<tr>
		<td align="left" width="70">
			<strong>Gaussholder</strong><br />
			 Fast and lightweight image previews, using Gaussian blur.
		</td>
		<td align="right" width="20%">
			<!--
			<a href="https://travis-ci.org/humanmade/Mercator">
				<img src="https://travis-ci.org/humanmade/Mercator.svg?branch=master" alt="Build status">
			</a>
			<a href="http://codecov.io/github/humanmade/Mercator?branch=master">
				<img src="http://codecov.io/github/humanmade/Mercator/coverage.svg?branch=master" alt="Coverage via codecov.io" />
			</a>
			-->
		</td>
	</tr>
	<tr>
		<td>
			A <strong><a href="https://hmn.md/">Human Made</a></strong> project. Maintained by @rmccue.
		</td>
		<td align="center">
			<img src="https://hmn.md/content/themes/hmnmd/assets/images/hm-logo.svg" width="100" />
		</td>
	</tr>
</table>

Gaussholder is an image placeholder utility, generating accurate preview images using an amazingly small amount of data.

<img src="http://zippy.gfycat.com/MarriedWearyCoelacanth.gif" />

## How does it work?

Gaussholder is inspired by [Facebook Engineering's fantastic post][fbeng] on generating tiny preview images. Gaussholder takes the concepts from this post and applies them to the wild world of WordPress.

In a nutshell, Gaussholder takes a Gaussian blur and applies it to an image to generate a preview image. Gaussian blurs work as a low-pass filter, allowing us to throw away a lot of the data. We then further reduce the amount of data per image by removing the JPEG header and rebuilding it on the client side (this eliminates ~800 bytes from each image).

We further reduce the amount of data for some requests by lazyloading images.

[fbeng]: https://code.facebook.com/posts/991252547593574

## How do I use it?

Gaussholder is designed for high-volume sites for seriously advanced users. Do _not_ install this on your regular WP site.

1. Download and activate the plugin from this repo.
2. Select the image sizes to use Gaussholder on, and add them to the array on the `gaussholder.image_sizes` filter.
3. If you have existing images, regenerate the image thumbnails.

Your filter should look something like this:

```php
add_filter( 'gaussholder.image_sizes', function ( $sizes ) {
	$sizes[] = 'medium';
	$sizes[] = 'large';
	$sizes[] = 'full';
	return $sizes;
} );
```

## License
Gaussholder is licensed under the GPLv2 or later.

Gaussholder uses StackBlur, licensed under the MIT license.

See [LICENSE.md](LICENSE.md) for further details.

## Credits
Created by Human Made for high volume and large-scale sites.

Written and maintained by [Ryan McCue](https://github.com/rmccue). Thanks to all our [contributors](https://github.com/humanmade/Gaussholder/graphs/contributors). (Thanks also to fellow humans Matt and Paul for the initial placeholder code.)

Gaussholder is heavily inspired by [Facebook Engineering's post][fbeng], and would not have been possible without it. In particular, the techniques of downscaling before blurring and extracting the JPEG header are particularly novel, and the key to why Gaussholder exists.

Interested in joining in on the fun? [Join us, and become human!](https://hmn.md/is/hiring/)
