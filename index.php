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
    $meta_fields = [
        'nm_readability_age' => [
            'type'        => 'number',
            'description' => 'Estimated reading age (average of multiple readability formulas)',
        ],
        'nm_read_time' => [
            'type'        => 'integer',
            'description' => 'Estimated read time in minutes',
        ],
    ];

    foreach ($meta_fields as $key => $args) {
        register_post_meta('post', $key, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => $args['type'],
            'description'   => $args['description'],
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
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

    global $post;
    $post_id = 0;

    if ($post && isset($post->ID)) {
        $post_id = (int) $post->ID;
    }

    if (!$post_id && 'post.php' === $hook && isset($_GET['post'])) {
        $post_id = absint($_GET['post']);
    }

    wp_localize_script('nm-readability-analyser', 'NMReadabilityAnalyser', [
        'nonce'   => wp_create_nonce('nm_readability_save'),
        'post_id' => $post_id,
    ]);
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
        $age = floatval($_POST['nm_readability_age']);
        update_post_meta($post_id, 'nm_readability_age', $age);
    }

    if (isset($_POST['nm_read_time'])) {
        $time = absint($_POST['nm_read_time']);
        update_post_meta($post_id, 'nm_read_time', $time);
    }
});

/**
 * WP-CLI: Backfill read time for all posts.
 *
 * Usage: wp nm-readability backfill
 * Calculates read time (238 wpm) for all published posts and saves as nm_read_time meta.
 */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('nm-readability backfill', function () {
        $count_query = new \WP_Query([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        $total = (int) $count_query->found_posts;

        if ($total === 0) {
            WP_CLI::success('No published posts found.');
            return;
        }

        $progress   = \WP_CLI\Utils\make_progress_bar('Backfilling read time', $total);
        $count      = 0;
        $paged      = 1;
        $batch_size = 100;

        do {
            $query = new \WP_Query([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => $batch_size,
                'paged'          => $paged,
            ]);

            if (!$query->have_posts()) {
                break;
            }

            foreach ($query->posts as $post) {
                $words = str_word_count(strip_tags($post->post_content));
                $time  = max(1, (int) ceil($words / 238));
                update_post_meta($post->ID, 'nm_read_time', $time);
                $count++;
                $progress->tick();
            }

            wp_reset_postdata();
            $paged++;
        } while ($paged <= $query->max_num_pages);

        $progress->finish();
        WP_CLI::success("Backfilled read time for {$count} posts.");
    });
}
