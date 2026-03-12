<?php
/**
 * Plugin Name: NM Readability Analyser
 * Description: Analyses content readability and estimates read time for posts
 * Version: 2.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Novara Media
 * Author URI: https://github.com/novaramedia/
 * License: GPL-3.0+
 */

defined('WPINC') || die;

/**
 * Register post meta fields with REST API exposure.
 */
add_action('init', function () {
    register_post_meta('post', 'nm_readability_age', [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'number',
        'description'       => 'Estimated reading age (average of multiple readability formulas)',
        'default'           => 0,
        'sanitize_callback' => function ($value, $meta_key, $object_type) {
            $value = floatval($value);
            return max(0, min(22, $value));
        },
        'auth_callback'     => function ($allowed, $meta_key, $post_id, $user_id, $cap, $caps) {
            return current_user_can('edit_post', $post_id);
        },
    ]);

    register_post_meta('post', 'nm_read_time', [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'integer',
        'description'       => 'Estimated read time in minutes',
        'default'           => 0,
        'sanitize_callback' => function ($value, $meta_key, $object_type) {
            return max(0, absint($value));
        },
        'auth_callback'     => function ($allowed, $meta_key, $post_id, $user_id, $cap, $caps) {
            return current_user_can('edit_post', $post_id);
        },
    ]);
});

/**
 * Enqueue admin script on post editor screens only.
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'post') {
        return;
    }

    $dist = plugin_dir_path(__FILE__) . 'dist/admin.js';
    $version = file_exists($dist) ? filemtime($dist) : '2.0.0';

    wp_enqueue_script(
        'nm-readability-analyser',
        plugin_dir_url(__FILE__) . 'dist/admin.js',
        ['wp-dom-ready', 'wp-data'],
        $version,
        true
    );

});

/**
 * Register the metabox.
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'nm-readability-analyser',
        'Readability Analysis',
        'nm_readability_display_metabox',
        'post',
        'advanced',
        'low'
    );
});

/**
 * Render the metabox.
 */
function nm_readability_display_metabox($post) {
    wp_nonce_field('nm_readability_save', 'nm_readability_nonce');
    ?>
    <div class="nm-readability">
        <p style="margin-bottom: 8px;">
            Analyses readability using
            <a href="https://en.wikipedia.org/wiki/Dale%E2%80%93Chall_readability_formula">Dale-Chall</a>,
            <a href="https://en.wikipedia.org/wiki/Automated_readability_index">ARI</a>,
            <a href="https://en.wikipedia.org/wiki/Coleman%E2%80%93Liau_index">Coleman-Liau</a>,
            <a href="https://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests#Flesch_reading_ease">Flesch-Kincaid</a>,
            <a href="https://en.wikipedia.org/wiki/Gunning_fog_index">Gunning Fog</a>, and
            <a href="https://en.wikipedia.org/wiki/SMOG">SMOG</a>.
            Updates on editor changes.
        </p>
        <table class="widefat striped" style="max-width: 400px;">
            <tr>
                <th>Estimated reading age</th>
                <td><strong id="nm-readability-age">—</strong></td>
            </tr>
            <tr>
                <th>Estimated read time</th>
                <td><strong id="nm-read-time">—</strong></td>
            </tr>
            <tr>
                <th>Word count</th>
                <td><span id="nm-readability-words">—</span></td>
            </tr>
            <tr>
                <th>Sentence count</th>
                <td><span id="nm-readability-sentences">—</span></td>
            </tr>
            <tr>
                <th>Polysyllabic words</th>
                <td><span id="nm-readability-polysyllabic">—</span> (<span id="nm-readability-polysyllabic-pct">—</span>%)</td>
            </tr>
            <tr>
                <th>Dale-Chall difficult words</th>
                <td><span id="nm-readability-dale-chall">—</span></td>
            </tr>
        </table>
        <input type="hidden" id="nm-readability-age-input" name="nm_readability_age" />
        <input type="hidden" id="nm-read-time-input" name="nm_read_time" />
    </div>
    <?php
}

/**
 * Save meta on post save.
 */
add_action('save_post_post', function ($post_id) {
    if (!isset($_POST['nm_readability_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['nm_readability_nonce'], 'nm_readability_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['nm_readability_age'])) {
        if ($_POST['nm_readability_age'] === '') {
            delete_post_meta($post_id, 'nm_readability_age');
        } else {
            $age = max(0, min(22, floatval($_POST['nm_readability_age'])));
            update_post_meta($post_id, 'nm_readability_age', $age);
        }
    }

    if (isset($_POST['nm_read_time'])) {
        if ($_POST['nm_read_time'] === '') {
            delete_post_meta($post_id, 'nm_read_time');
        } else {
            $time = max(0, absint($_POST['nm_read_time']));
            update_post_meta($post_id, 'nm_read_time', $time);
        }
    }
});

/**
 * WP-CLI: Backfill read time for all posts.
 *
 * Usage: wp nm-readability backfill
 * Calculates read time (238 wpm) for all published posts and saves as nm_read_time meta.
 */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('nm-readability backfill', function ($args, $assoc_args) {
        $category = $assoc_args['category'] ?? 'articles';
        $all      = isset($assoc_args['all']);

        $query_args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ];

        if (!$all) {
            $query_args['category_name'] = $category;
        }

        $count_query = new \WP_Query($query_args);
        $total = (int) $count_query->found_posts;

        if ($total === 0) {
            WP_CLI::success('No published posts found.');
            return;
        }

        $scope = $all ? 'all posts' : "'{$category}' category";
        WP_CLI::log("Backfilling read time for {$total} posts in {$scope}...");

        $progress   = \WP_CLI\Utils\make_progress_bar('Backfilling read time', $total);
        $count      = 0;
        $last_id    = 0;
        $batch_size = 100;

        $batch_args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $batch_size,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ];

        if (!$all) {
            $batch_args['category_name'] = $category;
        }

        do {
            $batch_args['post__in'] = [];
            $batch_args['where']    = '';

            // Cursor-based pagination: only fetch posts with ID > last processed
            global $wpdb;
            add_filter('posts_where', $cursor_filter = function ($where) use ($wpdb, $last_id) {
                return $where . $wpdb->prepare(" AND {$wpdb->posts}.ID > %d", $last_id);
            });

            $ids = get_posts($batch_args);

            remove_filter('posts_where', $cursor_filter);

            if (empty($ids)) {
                break;
            }

            foreach ($ids as $id) {
                $content = get_post_field('post_content', $id);
                $words   = str_word_count(strip_tags($content));
                $time    = max(1, (int) ceil($words / 238));
                update_post_meta($id, 'nm_read_time', $time);
                $count++;
                $progress->tick();
            }

            $last_id = end($ids);
        } while (count($ids) === $batch_size);

        $progress->finish();
        WP_CLI::success("Backfilled read time for {$count} posts.");
    });
}
