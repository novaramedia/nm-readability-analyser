<?php

namespace NMReadabilityAnalyser\Admin;

// Exit if accessed directly
defined( 'WPINC' ) || die;

/**
 * @file
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Init {

	use \NMReadabilityAnalyser\Traits\HelpersTrait;

	// Main plugin instance.
	protected static $instance = null;

	// Assets loader class.
	protected $assets;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		// Main plugin instance.
		$instance     = \NMReadabilityAnalyser\plugin_instance();
		$hooker       = $instance->get_hooker();
		$this->assets = $instance->get_assets();

		// Admin hooks.
		$hooker->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		$hooker->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );

		$hooker->add_action( 'add_meta_boxes', $this, 'register_metabox' );
		$hooker->add_action( 'save_post', $this, 'on_save' );
	}

	/**
	 * Enqueue the stylesheets for wp-admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
			$this->get_plugin_id( '/wp/css' ),
			$this->assets->get( 'admin.css' ),
			array(),
			$this->get_plugin_version(),
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$script_id = $this->get_plugin_id( '/wp/js' );

		wp_enqueue_script(
			$script_id,
			$this->assets->get( 'admin.js' ),
			array(),
			$this->get_plugin_version(),
			false
		);

		wp_localize_script(
			$script_id,
			'NMReadabilityAnalyser',
			array(
				'nonce'    => wp_create_nonce( 'NMReadabilityAnalyser_wp_xhr_nonce' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'post_id'  => get_the_ID(),
			)
		);
	}

	public function register_metabox() {

		add_meta_box( 'meta-box-id', 'Readability Analysis', array( $this, 'display_metabox' ), 'post', 'advanced', 'low' );
	}

	public function display_metabox( $post ) {
		?>
<div class="nm_readability-plugin">
	<div class="nm_readability-plugin__content">
	<div class="nm_readability-plugin__content__text">
		<p>
		This is an analysis of the ease of reading on the entirity of the post content. This uses several formulas: <a href="https://en.wikipedia.org/wiki/Dale–Chall_readability_formula">Dale–Chall</a>, <a href="https://en.wikipedia.org/wiki/Automated_readability_index">Automated Readability</a>, <a href="https://en.wikipedia.org/wiki/Coleman–Liau_index">Coleman–Liau</a>, <a href="https://en.wikipedia.org/wiki/Flesch–Kincaid_readability_tests#Flesch_reading_ease">Flesch</a>, <a href="https://en.wikipedia.org/wiki/Gunning_fog_index">Gunning fog</a> and <a href="https://en.wikipedia.org/wiki/SMOG">SMOG</a>. The results are averaged to an estimated reading age.
		</p>
		<p>
		<em>This score updates on post save or update, not live with the editor.</em>
		</p>
	</div>
	<div class="nm_readability-plugin__content__body">
		<p style="font-size: 1.5rem; line-height: 1">Estimated reading age: <span id="nm_readability-age"></span></p>
		Dale-Chall Difficult Word Count: <span id="nm_readability-dale-chall-difficult-word-count"></span><br/>
		Polysyllabic Word Count: <span id="nm_readability-polysyllabic-word-count"></span><br/>
		Percentage of All Words Polysyllabic: <span id="nm_readability-polysyllabic-word-percentage"></span>%<br/>
		Word Count: <span id="nm_readability-word-count"></span><br/>
		Sentence Count: <span id="nm_readability-sentence-count"></span>
		</p>
	</div>
	</div>
</div>
		<?php
	}

	public function on_save( $post ) {
	}
}
