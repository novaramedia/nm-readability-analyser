<?php

defined( 'WPINC' ) || die;

/**
 * Plugin Name: NM Readability Analyser
 * Description: Analyses content of posts to give a readability score and displays this to editors
 * Version: 0.0.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Novara Media
 * Author URI: https://github.com/novaramedia/
 * License: GPL-3.0+
 * Text Domain: _nm_
 */

register_activation_hook(
	__FILE__,
	function () {

		// On activate do this
		\NMReadabilityAnalyser\Activator::activate();
	}
);

register_deactivation_hook(
	__FILE__,
	function () {

		// On deactivate do that
		\NMReadabilityAnalyser\Deactivator::deactivate();
	}
);

require 'run.php';
