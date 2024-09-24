<?php
/**
 * Plugin name: Snow Monkey Google Fonts
 * Version: 1.2.1
 * Tested up to: 6.6
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Snow Monkey: 20.0.0
 * Description: This plugin adds Google Fonts to the basic font settings.
 * Author: inc2734
 * Author URI: https://2inc.org
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-monkey-google-fonts
 *
 * @package snow-monkey-google-fonts
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\GoogleFonts;

use Inc2734\WP_GitHub_Plugin_Updater\Bootstrap as Updater;

define( 'SNOW_MONKEY_GOOGLE_FONTS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SNOW_MONKEY_GOOGLE_FONTS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

class Bootstrap {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_bootstrap' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function _bootstrap() {
		load_plugin_textdomain( 'snow-monkey-google-fonts', false, basename( __DIR__ ) . '/languages' );

		add_action( 'init', array( $this, '_activate_autoupdate' ) );

		$theme = wp_get_theme( get_template() );
		if ( 'snow-monkey' !== $theme->template && 'snow-monkey/resources' !== $theme->template ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php esc_html_e( '[Snow Monkey Google Fonts] Needs the Snow Monkey.', 'snow-monkey-google-fonts' ); ?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		$data = get_file_data(
			__FILE__,
			array(
				'RequiresSnowMonkey' => 'Requires Snow Monkey',
			)
		);

		if (
			isset( $data['RequiresSnowMonkey'] ) &&
			version_compare( $theme->get( 'Version' ), $data['RequiresSnowMonkey'], '<' )
		) {
			add_action(
				'admin_notices',
				function () use ( $data ) {
					?>
					<div class="notice notice-warning is-dismissible">
						<p>
							<?php
							echo esc_html(
								sprintf(
									// translators: %1$s: version.
									__(
										'[Snow Monkey Google Fonts] Needs the Snow Monkey %1$s or more.',
										'snow-monkey-google-fonts'
									),
									'v' . $data['RequiresSnowMonkey']
								)
							);
							?>
						</p>
					</div>
					<?php
				}
			);
			return;
		}

		add_filter( 'snow_monkey_font_family_settings', array( $this, '_snow_monkey_font_family_settings' ) );
	}

	/**
	 * Add fonts settings.
	 *
	 * @param array $settings See snow-monkey/Framework/Helper.php: get_font_family_settings().
	 * @return array
	 */
	public function _snow_monkey_font_family_settings( $settings ) {
		// m-plus-1p.
		$settings['m-plus-1p']['variation']['100'] = array(
			'label' => __( 'Thin 100', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUS1p-Thin.woff2',
		);
		$settings['m-plus-1p']['variation']['300'] = array(
			'label' => __( 'Light 300', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUS1p-Light.woff2',
		);
		$settings['m-plus-1p']['variation']['500'] = array(
			'label' => __( 'Medium 500', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUS1p-Medium.woff2',
		);
		$settings['m-plus-1p']['variation']['800'] = array(
			'label' => __( 'Extra-Bold 800', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUS1p-ExtraBold.woff2',
		);
		$settings['m-plus-1p']['variation']['900'] = array(
			'label' => __( 'Black 900', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUS1p-Black.woff2',
		);

		// m-plus-rounded-1c.
		$settings['m-plus-rounded-1c']['variation']['100'] = array(
			'label' => __( 'Thin 100', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUSRounded1c-Thin.woff2',
		);
		$settings['m-plus-rounded-1c']['variation']['300'] = array(
			'label' => __( 'Light 300', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUSRounded1c-Light.woff2',
		);
		$settings['m-plus-rounded-1c']['variation']['500'] = array(
			'label' => __( 'Medium 500', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUSRounded1c-Medium.woff2',
		);
		$settings['m-plus-rounded-1c']['variation']['800'] = array(
			'label' => __( 'Extra-Bold 800', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUSRounded1c-ExtraBold.woff2',
		);
		$settings['m-plus-rounded-1c']['variation']['900'] = array(
			'label' => __( 'Black 900', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/MPLUSRounded1c-Black.woff2',
		);

		// noto-sans-jp.
		$settings['noto-sans-jp']['variation']['100'] = array(
			'label' => __( 'Thin 100', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSansJP-Thin.woff2',
		);
		$settings['noto-sans-jp']['variation']['300'] = array(
			'label' => __( 'Light 300', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSansJP-Light.woff2',
		);
		$settings['noto-sans-jp']['variation']['500'] = array(
			'label' => __( 'Medium 500', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSansJP-Medium.woff2',
		);
		$settings['noto-sans-jp']['variation']['900'] = array(
			'label' => __( 'Black 900', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSansJP-Black.woff2',
		);

		// noto-serif-jp.
		$settings['noto-serif-jp']['variation']['200'] = array(
			'label' => __( 'Extra-Light 200', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSerifJP-ExtraLight.woff2',
		);
		$settings['noto-serif-jp']['variation']['300'] = array(
			'label' => __( 'Light 300', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSerifJP-Light.woff2',
		);
		$settings['noto-serif-jp']['variation']['500'] = array(
			'label' => __( 'Medium 500', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSerifJP-Medium.woff2',
		);
		$settings['noto-serif-jp']['variation']['600'] = array(
			'label' => __( 'Semi-Bold 600', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSerifJP-SemiBold.woff2',
		);
		$settings['noto-serif-jp']['variation']['900'] = array(
			'label' => __( 'Black 900', 'snow-monkey-google-fonts' ),
			'src'   => SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSerifJP-Black.woff2',
		);

		return $settings;
	}

	/**
	 * Activate auto update using GitHub.
	 */
	public function _activate_autoupdate() {
		new \Inc2734\WP_GitHub_Plugin_Updater\Bootstrap(
			plugin_basename( __FILE__ ),
			'inc2734',
			'snow-monkey-google-fonts',
			array(
				'homepage' => 'https://snow-monkey.2inc.org',
			)
		);
	}
}

require_once SNOW_MONKEY_GOOGLE_FONTS_PATH . '/vendor/autoload.php';
new Bootstrap();
