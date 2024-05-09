<?php
if (!is_admin()) {
	add_shortcode('wm_single_poi', 'wm_single_poi');
}

function wm_single_poi($atts)
{
	if (defined('ICL_LANGUAGE_CODE')) {
		$language = ICL_LANGUAGE_CODE;
	} else {
		$language = 'it';
	}

	extract(shortcode_atts(array(
		'poi_id' => null,
	), $atts));

	$single_poi_base_url = get_option('poi_url');
	$geojson_url = $single_poi_base_url . $poi_id;

	$response = wp_remote_get($geojson_url);
	if (is_wp_error($response)) {
		return 'Failed to load POI data.';
	}

	$poi_data = wp_remote_retrieve_body($response);
	$poi = json_decode($poi_data, true);
	if (!$poi || !isset($poi['properties'])) {
		return 'Failed to load POI data.';
	}

	$poi_properties = $poi['properties'];
	$iframeUrl = "https://geohub.webmapp.it/poi/simple/" . $poi_id;

	$title = null;
	$description = null;
	$excerpt = null;
	$featured_image = null;
	$contact_phone = null;
	$contact_email = null;
	$addr_street = null;
	$addr_postcode = null;
	$addr_locality = null;
	$gallery = null;
	$related_urls = null;

	if (!empty($poi_properties)) {
		$title = $poi_properties['name'][$language] ?? '';
		$description = $poi_properties['description'][$language] ?? '';
		$excerpt = $poi_properties['excerpt'][$language] ?? '';
		$featured_image_url = $poi_properties['feature_image']['url'] ?? get_stylesheet_directory_uri() . '/assets/images/feature_image.jpg';
		$featured_image = $poi_properties['feature_image']['sizes']['1440x500'] ?? $featured_image_url;
		$contact_phone = $poi_properties['contact_phone'] ?? '';
		$contact_email = $poi_properties['contact_email'] ?? '';
		$addr_street = $poi_properties['addr_street'] ?? '';
		$addr_postcode = $poi_properties['addr_postcode'] ?? '';
		$addr_locality = $poi_properties['addr_locality'] ?? '';
		$gallery = $poi_properties['image_gallery'] ?? [];
		$related_urls = $poi_properties['related_url'] ?? [];
	}
	ob_start();
?>
	<section class="l-section wpb_row height_small with_img with_overlay wm_header_section">
		<div class="l-section-img loaded wm-header-image" style="background-image: url(<?= $featured_image ?>);background-repeat: no-repeat;">
		</div>
		<div class="l-section-h i-cf wm_header_wrapper">
		</div>
	</section>

	<div class="wm_body_poi_section">
		<div class="wm_body_map_wrapper">
			<?php if ($title) { ?>
				<h1 class="align_left wm_header_title">
					<?= $title ?>
				</h1>
			<?php } ?>
			<?php if ($excerpt) { ?>
				<p class="wm_excerpt"><?php echo wp_kses_post($excerpt); ?></p>
			<?php } ?>
			<iframe class="wm_iframe_map_poi" src="<?= esc_url($iframeUrl); ?>" loading="lazy"></iframe>
			<div class="wm_info">
				<?php
				$info_parts = [];
				if (!empty($addr_street) || !empty($addr_postcode) || !empty($addr_locality)) {
					$address = trim($addr_street . ', ' . $addr_postcode . ' ' . $addr_locality, ', ');
					$info_parts[] = '<span class="wm_address_info"><span class="fa fa-map-marker-alt"></span> ' . esc_html($address) . '</span>';
				}
				if (!empty($contact_phone)) {
					$info_parts[] = '<span class="wm_contact_phone"><span class="fa fa-phone"></span> ' . esc_html($contact_phone) . '</span>';
				}
				if (!empty($contact_email)) {
					$info_parts[] = '<span class="wm_contact_email"><span class="fa fa-envelope"></span> <a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . '</a></span>';
				}
				if (!empty($related_urls)) {
					$urls_output = [];
					foreach ($related_urls as $url_name => $url) {
						$urls_output[] = '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($url_name) . '</a>';
					}
					$info_parts[] = '<span class="wm_related_urls"> <span class="fa fa-external-link-alt"></span> ' . implode(', ', $urls_output) . '</span>';
				}
				echo implode(' - ', $info_parts);
				?>
			</div>
		</div>

		<?php if ($description) { ?>
			<div class="wm_body_description">
				<?php echo wp_kses_post($description); ?>
			</div>
		<?php } ?>

		<div class="wm_body_gallery">
			<?php if (is_array($gallery) && !empty($gallery)) : ?>
				<div class="swiper-container">
					<div class="swiper-wrapper">
						<?php foreach ($gallery as $image) : ?>
							<div class="swiper-slide">
								<?php
								$size_order = ['400x200', '1440x500', '335x250', '250x150'];
								$img_url = null;
								foreach ($size_order as $size) {
									if (isset($image['sizes'][$size])) {
										$img_url = esc_url($image['sizes'][$size]);
										break;
									}
								}
								if ($img_url) : ?>
									<img src="<?= $img_url ?>" alt="" loading="lazy">
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="swiper-pagination"></div>
					<div class="swiper-button-prev"></div>
					<div class="swiper-button-next"></div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var swiper = new Swiper('.swiper-container', {
				slidesPerView: 1,
				spaceBetween: 10,
				breakpoints: {
					768: {
						slidesPerView: 3,
						spaceBetween: 20
					},
				},
				freeMode: true,
				loop: true,
				pagination: {
					el: '.swiper-pagination',
					clickable: true,
				},
				navigation: {
					nextEl: '.swiper-button-next',
					prevEl: '.swiper-button-prev',
				},
			});
		});
	</script>

<?php

	return ob_get_clean();
}
?>