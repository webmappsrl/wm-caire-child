<?php

add_shortcode('wm_grid_poi', 'wm_grid_poi');

function wm_grid_poi($atts)
{
    if (!is_admin()) {
        if (defined('ICL_LANGUAGE_CODE')) {
            $language = ICL_LANGUAGE_CODE;
        } else {
            $language = 'it';
        }

        extract(shortcode_atts([
            'poi_type_id' => '',
            'poi_type_ids' => '',
            'quantity' => -1,
            'random' => 'false'
        ], $atts));

        $poi_data = [];
        $poi_type_ids_array = !empty($poi_type_ids) ? explode(',', $poi_type_ids) : (!empty($poi_type_id) ? [$poi_type_id] : []);

        foreach ($poi_type_ids_array as $id) {
            $app_id = get_option('app_configuration_id');
            $poi_url = "https://geohub.webmapp.it/api/app/webapp/$app_id/taxonomies/poi_type/$id";
            $response = wp_remote_get($poi_url);

            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($data) && isset($data['features'])) {
                    foreach ($data['features'] as $feature) {
                        if (isset($data['icon'])) {
                            $feature['svg_icon'] = $data['icon'];
                        }
                        $poi_data[] = $feature;
                    }
                }
            }
        }

        usort($poi_data, function ($a, $b) use ($language) {
            return compare_pois($a['name'][$language] ?? '', $b['name'][$language] ?? '');
        });

        if ('true' === $random) {
            shuffle($poi_data);
        }

        if ($quantity > 0 && count($poi_data) > $quantity) {
            $poi_data = array_slice($poi_data, 0, $quantity);
        }

        ob_start();
?>

        <div class="wm_poi_grid">
            <?php foreach ($poi_data as $poi) : ?>
                <div class="wm_grid_poi_item">
                    <?php
                    $name = $poi['name'][$language] ?? '';
                    $feature_image_url = $poi['featureImage']['thumbnail'] ?? get_stylesheet_directory_uri() . '/assets/images/grid_background.png';
                    $name_url = wm_custom_slugify($name);
                    $language_prefix = $language === 'en' ? '/en' : '';
                    $poi_page_url = "{$language_prefix}/poi/{$name_url}/";
                    $svg_icon = $poi['svg_icon'] ?? '';
                    ?>
                    <a href="<?= esc_url($poi_page_url); ?>">
                        <?php if ($feature_image_url) : ?>
                            <div class="wm_grid_poi_image" style="background-image: url('<?= esc_url($feature_image_url); ?>');">
                                <?php if ($svg_icon) : ?>
                                    <div class="wm_grid_icon"><?= $svg_icon; ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($name) : ?>
                            <div class="wm_grid_poi_name"><?= esc_html($name); ?></div>
                        <?php endif; ?>

                    </a>
                </div>
            <?php endforeach; ?>
        </div>

<?php
        return ob_get_clean();
    } else {
        return;
    }
}

function compare_pois($a, $b)
{
    preg_match('/\d+/', $a, $matchesA);
    preg_match('/\d+/', $b, $matchesB);

    $numA = isset($matchesA[0]) ? (int)$matchesA[0] : 0;
    $numB = isset($matchesB[0]) ? (int)$matchesB[0] : 0;

    if ($numA !== $numB) {
        return $numA - $numB;
    }

    return strcmp($a, $b);
}
?>