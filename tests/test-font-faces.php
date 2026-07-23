<?php
/**
 * Tests for bundled font faces.
 *
 * @package Snow_Monkey_Google_Fonts
 */

/**
 * Test bundled font faces in theme.json data.
 */
class Test_Font_Faces extends WP_UnitTestCase {

	/**
	 * Return a font family by slug.
	 *
	 * @param array  $font_families Font families.
	 * @param string $slug Font family slug.
	 * @return array
	 */
	protected function get_font_family( $font_families, $slug ) {
		foreach ( $font_families as $font_family ) {
			if ( isset( $font_family['slug'] ) && $slug === $font_family['slug'] ) {
				return $font_family;
			}
		}

		$this->fail( sprintf( 'Font family "%s" was not found.', $slug ) );
	}

	/**
	 * Add bundled font faces to origin-keyed theme.json data.
	 */
	public function test_adds_bundled_font_faces_to_origin_keyed_theme_json_data() {
		$font_families = array(
			array(
				'name'       => 'Noto Sans JP',
				'slug'       => 'noto-sans-jp',
				'fontFamily' => '"Noto Sans JP", sans-serif',
				'fontFace'   => array(
					array(
						'fontFamily' => '"Noto Sans JP"',
						'fontWeight' => '400',
						'fontStyle'  => 'normal',
						'src'        => array( 'file:./assets/fonts/NotoSansJP-Regular.woff2' ),
					),
					array(
						'fontFamily' => '"Noto Sans JP"',
						'fontWeight' => '700',
						'fontStyle'  => 'normal',
						'src'        => array( 'file:./assets/fonts/NotoSansJP-Bold.woff2' ),
					),
				),
			),
		);

		$theme_json = new WP_Theme_JSON_Data(
			array(
				'version'  => 3,
				'settings' => array(
					'typography' => array(
						'fontFamilies' => $font_families,
					),
				),
			),
			'theme'
		);

		$raw_data = $theme_json->get_data();
		$this->assertSame(
			$font_families,
			$raw_data['settings']['typography']['fontFamilies']['theme']
		);

		$bootstrap = new \Snow_Monkey\Plugin\GoogleFonts\Bootstrap();
		$result    = $bootstrap->_wp_theme_json_data_theme( $theme_json );

		$this->assertSame( $theme_json, $result );

		$raw_data    = $result->get_data();
		$font_family = $this->get_font_family(
			$raw_data['settings']['typography']['fontFamilies']['theme'],
			'noto-sans-jp'
		);
		$font_weights = array_column( $font_family['fontFace'], 'fontWeight' );
		sort( $font_weights, SORT_NUMERIC );

		$this->assertSame(
			array( '100', '300', '400', '500', '700', '900' ),
			$font_weights
		);

		$font_faces_by_weight = array_column( $font_family['fontFace'], null, 'fontWeight' );
		$this->assertSame(
			array( SNOW_MONKEY_GOOGLE_FONTS_URL . '/fonts/NotoSansJP-Light.woff2' ),
			$font_faces_by_weight['300']['src']
		);
	}
}
