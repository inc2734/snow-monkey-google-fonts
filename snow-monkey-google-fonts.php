<?php
/**
 * Plugin name: Snow Monkey Google Fonts
 * Version: 1.2.7
 * Tested up to: 7.0
 * Requires at least: 7.0
 * Requires PHP: 7.4
 * Requires Snow Monkey: 30.0.2
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
		add_action( 'init', array( $this, '_load_textdomain' ) );

		new App\Updater();

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

		add_filter(
			'snow_monkey_font_family_settings',
			array( $this, '_snow_monkey_font_family_settings' )
		);

		add_filter(
			'wp_theme_json_data_theme',
			array( $this, '_wp_theme_json_data_theme' )
		);
	}

	/**
	 * Return quoted font-family name for fontFace.
	 *
	 * @param string $font_family Font family.
	 * @return string
	 */
	protected function _quote_font_face_family( $font_family ) {
		$font_family = trim( $font_family );

		if ( preg_match( '/^".+"$/', $font_family ) || preg_match( "/^'.+'$/", $font_family ) ) {
			return $font_family;
		}

		return '"' . addcslashes( $font_family, '"\\' ) . '"';
	}

	/**
	 * Return comparable font weight.
	 *
	 * @param string $font_weight Font weight.
	 * @return string
	 */
	protected function _normalize_font_weight( $font_weight ) {
		$font_weight = (string) $font_weight;
		if ( preg_match( '/^\d+/', $font_weight, $matches ) ) {
			return $matches[0];
		}

		return $font_weight;
	}

	/**
	 * Return whether the font face is defined.
	 *
	 * @param array  $font_faces Font faces.
	 * @param string $font_weight Font weight.
	 * @return boolean
	 */
	protected function _has_font_face( $font_faces, $font_weight ) {
		if ( ! is_array( $font_faces ) ) {
			return false;
		}

		$font_weight = $this->_normalize_font_weight( $font_weight );

		foreach ( $font_faces as $font_face ) {
			if ( empty( $font_face['fontWeight'] ) ) {
				continue;
			}

			if ( $font_weight === $this->_normalize_font_weight( $font_face['fontWeight'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add bundled font faces to theme.json.
	 *
	 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
	 * @return WP_Theme_JSON_Data
	 */
	public function _wp_theme_json_data_theme( $theme_json ) {
		$theme_json_data   = $theme_json->get_data();
		$font_families_raw = isset( $theme_json_data['settings']['typography']['fontFamilies'] )
			? $theme_json_data['settings']['typography']['fontFamilies']
			: array();

		$font_families_are_origin_keyed = (
			is_array( $font_families_raw ) &&
			isset( $font_families_raw['theme'] ) &&
			is_array( $font_families_raw['theme'] )
		);
		$font_families                  = $font_families_are_origin_keyed
			? $font_families_raw['theme']
			: $font_families_raw;

		if ( ! is_array( $font_families ) ) {
			return $theme_json;
		}

		$settings = $this->_snow_monkey_font_family_settings( array() );
		$updated  = false;

		foreach ( $font_families as $index => $font_family ) {
			if ( empty( $font_family['slug'] ) || empty( $settings[ $font_family['slug'] ]['variation'] ) ) {
				continue;
			}

			if ( empty( $font_families[ $index ]['fontFace'] ) || ! is_array( $font_families[ $index ]['fontFace'] ) ) {
				$font_families[ $index ]['fontFace'] = array();
			}

			$font_family_name = ! empty( $font_family['name'] )
				? $font_family['name']
				: $font_family['slug'];

			foreach ( $settings[ $font_family['slug'] ]['variation'] as $font_weight => $variation ) {
				if (
					empty( $variation['src'] ) ||
					$this->_has_font_face( $font_families[ $index ]['fontFace'], $font_weight )
				) {
					continue;
				}

				$font_families[ $index ]['fontFace'][] = array(
					'fontFamily' => $this->_quote_font_face_family( $font_family_name ),
					'fontWeight' => (string) $font_weight,
					'fontStyle'  => 'normal',
					'src'        => array( $variation['src'] ),
				);

				$updated = true;
			}
		}

		if ( ! $updated ) {
			return $theme_json;
		}

		if ( $font_families_are_origin_keyed ) {
			$font_families_raw['theme'] = $font_families;
		} else {
			$font_families_raw = $font_families;
		}

		$theme_json->update_with(
			array(
				'version'  => isset( $theme_json_data['version'] ) ? $theme_json_data['version'] : 3,
				'settings' => array(
					'typography' => array(
						'fontFamilies' => $font_families_raw,
					),
				),
			)
		);

		return $theme_json;
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
	 * Load textdomain
	 */
	public function _load_textdomain() {
		load_plugin_textdomain( 'snow-monkey-google-fonts', false, basename( __DIR__ ) . '/languages' );
	}
}

require_once SNOW_MONKEY_GOOGLE_FONTS_PATH . '/vendor/autoload.php';
new Bootstrap();
