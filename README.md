<table width="100%">
	<tr>
		<td align="left" width="70">
			<strong>Gaussholder</strong><br />
			 Fast and lightweight image previews, using Gaussian blur.
		</td>
		<td align="right" width="20%">
			<!--
			<a href="https://travis-ci.org/humanmade/Gaussholder">
				<img src="https://travis-ci.org/humanmade/Gaussholder.svg?branch=master" alt="Build status">
			</a>
			<a href="http://codecov.io/github/humanmade/Gaussholder?branch=master">
				<img src="http://codecov.io/github/humanmade/Gaussholder/coverage.svg?branch=master" alt="Coverage via codecov.io" />
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

<img src="preview.gif" />

That's a **800 byte** preview image, for a **109 kilobyte** image. 800 bytes still too big? Tune the size to your liking in your configuration.

**Please note:** This is still in development, and we're working on getting this production-ready, so things might not be settled yet. In particular, we're still working on tweaking the placeholder size and improving the lazyloading code. Avoid using this in production.

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
	$sizes['medium'] = 16;
	$sizes['large'] = 32;
	$sizes['full'] = 84;
	return $sizes;
} );
```

The keys are registered image sizes (plus `full` for the original size), with the value as the desired blur radius in pixels.

By default, Gaussholder won't generate any placeholders, and you need to opt-in to using it. Simply filter here, and add the size names for what you want generated.

Be aware that for every size you add, a placeholder will be generated and stored in the database. If you have a lot of sizes, this will be a _lot_ of data.

### Blur radius

The blur radius controls how much blur we use. The image is pre-scaled down by this factor, and this is really the key to how the placeholders work. Increasing radius decreases the required data quadratically: a radius of 2 uses a quarter as much data as the full image; a radius of 8 uses 1/64 the amount of data. (Due to compression, the final result will *not* follow this scaling.)

Be careful tuning this, as decreasing the radius too much will cause a huge amount of data in the body; increasing it will end up with not enough data to be an effective placeholder.

The radius needs to be tuned to each size individually. Facebook uses about 200 bytes of data for their placeholders, but you may want higher quality placeholders. There's no ideal radius, as you simply want to balance having a useful placeholder with the extra time needed to process the data on the page.

Gaussholder includes a CLI command to help you tune the radius: pick a representative attachment or image file and use `wp gaussholder check-size <id_or_image> <radius>`. Adjust the radius until you get to roughly 200B, then check against other attachments to ensure they're in the ballpark.

Note: changing the radius requires regenerating the placeholder data. Run `wp gaussholder process-all --regenerate` after changing radii or adding new sizes.

## License
Gaussholder is licensed under the GPLv2 or later.

Gaussholder uses StackBlur, licensed under the MIT license.

See [LICENSE.md](LICENSE.md) for further details.

## Credits
Created by Human Made for high volume and large-scale sites.

Written and maintained by [Ryan McCue](https://github.com/rmccue). Thanks to all our [contributors](https://github.com/humanmade/Gaussholder/graphs/contributors). (Thanks also to fellow humans Matt and Paul for the initial placeholder code.)

Gaussholder is heavily inspired by [Facebook Engineering's post][fbeng], and would not have been possible without it. In particular, the techniques of downscaling before blurring and extracting the JPEG header are particularly novel, and the key to why Gaussholder exists.

Interested in joining in on the fun? [Join us, and become human!](https://hmn.md/is/hiring/)
