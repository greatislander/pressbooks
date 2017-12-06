<?php
/**
 * @author  Pressbooks <code@pressbooks.com>
 * @license GPLv2 (or any later version)
 */

namespace Pressbooks\Modules\ThemeOptions;

class EbookOptions extends \Pressbooks\Options {

	/**
	 * The value for option: pressbooks_theme_options_ebook_version
	 *
	 * @see upgrade()
	 * @var int
	 */
	const VERSION = 1;

	/**
	 * Web theme options.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Web theme defaults.
	 *
	 * @var array
	 */
	public $defaults;

	/**
	 * Constructor.
	 *
	 * @param array $options
	 */
	function __construct( array $options ) {
		$this->options = $options;
		$this->defaults = $this->getDefaults();
		$this->booleans = $this->getBooleanOptions();
		$this->strings = $this->getStringOptions();
		$this->integers = $this->getIntegerOptions();
		$this->floats = $this->getFloatOptions();
		$this->predefined = $this->getPredefinedOptions();

		foreach ( $this->defaults as $key => $value ) {
			if ( ! isset( $this->options[ $key ] ) ) {
				$this->options[ $key ] = $value;
			}
		}
	}

	/**
	 * Configure the ebook options tab using the settings API.
	 */
	function init() {
		$_option = 'pressbooks_theme_options_' . $this->getSlug();
		$_page = $_option;
		$_section = $this->getSlug() . '_options_section';

		if ( false === get_option( $_option ) ) {
			add_option( $_option, $this->defaults );
		}

		add_settings_section(
			$_section,
			$this->getTitle(),
			[ $this, 'display' ],
			$_page
		);

		add_settings_field(
			'ebook_paragraph_separation',
			__( 'Paragraph Separation', 'pressbooks' ),
			[ $this, 'renderParagraphSeparationField' ],
			$_page,
			$_section,
			[
				'indent' => __( 'Indent paragraphs', 'pressbooks' ),
				'skiplines' => __( 'Skip lines between paragraphs', 'pressbooks' ),
			]
		);

		add_settings_field(
			'ebook_compress_images',
			__( 'Compress Images', 'pressbooks' ),
			[ $this, 'renderCompressImagesField' ],
			$_page,
			$_section,
			[
				__( 'Reduce image size and quality', 'pressbooks' ),
			]
		);

		/**
		 * Add custom settings fields.
		 *
		 * @since 3.9.7
		 *
		 * @param string $arg1
		 * @param string $arg2
		 */
		do_action( 'pb_theme_options_ebook_add_settings_fields', $_page, $_section );

		register_setting(
			$_option,
			$_option,
			[ $this, 'sanitize' ]
		);
	}

	/**
	 * Display the Ebook options tab description.
	 */
	function display() {
		echo '<p>' . __( 'These options apply to ebook exports.', 'pressbooks' ) . '</p>';
	}

	/**
	 * Render the Ebook options tab form (NOT USED).
	 */
	function render() {
	}

	/**
	 * Upgrade handler for Ebook options.
	 *
	 * @param int $version
	 */
	function upgrade( $version ) {
		if ( $version < 1 ) {
			$this->doInitialUpgrade();
		}
	}

	/**
	 * Update values to human-readable equivalents within Ebook options.
	 */
	function doInitialUpgrade() {
		$_option = $this->getSlug();
		$options = get_option( 'pressbooks_theme_options_' . $_option, $this->defaults );

		if ( ! isset( $options['ebook_paragraph_separation'] ) || 1 === absint( $options['ebook_paragraph_separation'] ) ) {
			$options['ebook_paragraph_separation'] = 'indent';
		} elseif ( 2 === absint( $options['ebook_paragraph_separation'] ) ) {
			$options['ebook_paragraph_separation'] = 'skiplines';
		}

		update_option( 'pressbooks_theme_options_' . $_option, $options );
	}

	/**
	 * Render the ebook_paragraph_separation radio buttons.
	 *
	 * @param array $args
	 */
	function renderParagraphSeparationField( $args ) {
		$this->renderRadioButtons(
			[
				'id' => 'ebook_paragraph_separation',
				'name' => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'ebook_paragraph_separation',
				'value' => ( isset( $this->options['ebook_paragraph_separation'] ) ) ? $this->options['ebook_paragraph_separation'] : '',
				'choices' => $args,
			]
		);
	}

	/**
	 * Render the ebook_compress_images checkbox.
	 *
	 * @param array $args
	 */
	function renderCompressImagesField( $args ) {
		$this->renderCheckbox(
			[
				'id' => 'ebook_compress_images',
				'name' => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'ebook_compress_images',
				'value' => ( isset( $this->options['ebook_compress_images'] ) ) ? $this->options['ebook_compress_images'] : '',
				'label' => $args[0],
			]
		);
	}

	/**
	 * Get the slug for the Ebook options tab.
	 *
	 * @return string $slug
	 */
	static function getSlug() {
		return 'ebook';
	}

	/**
	 * Get the localized title of the Ebook options tab.
	 *
	 * @return string $title
	 */
	static function getTitle() {
		return __( 'Ebook Options', 'pressbooks' );
	}

	/**
	 * Get an array of default values for the Ebook options tab.
	 *
	 * @return array $defaults
	 */
	static function getDefaults() {
		/**
		 * @since 3.9.7
		 *
		 * @param array $value
		 */
		return apply_filters(
			'pb_theme_options_ebook_defaults', [
				'ebook_paragraph_separation' => 'indent',
				'ebook_compress_images' => 0,
			]
		);
	}

	/**
	 * Filter the array of default values for the Ebook options tab.
	 *
	 * @param array $defaults
	 *
	 * @return array $defaults
	 */
	static function filterDefaults( $defaults ) {
		return $defaults;
	}

	/**
	 * Get an array of options which return booleans.
	 *
	 * @return array $options
	 */
	static function getBooleanOptions() {
		/**
		 * Allow custom boolean options to be passed to sanitization routines.
		 *
		 * @since 3.9.7
		 *
		 * @param array $value
		 */
		return apply_filters(
			'pb_theme_options_ebook_booleans', [
				'ebook_compress_images',
			]
		);
	}

	/**
	 * Get an array of options which return strings.
	 *
	 * @return array $options
	 */
	static function getStringOptions() {
		/**
		 * Allow custom string options to be passed to sanitization routines.
		 *
		 * @since 3.9.7
		 *
		 * @param array $value
		 */
		return apply_filters( 'pb_theme_options_ebook_strings', [] );
	}

	/**
	 * Get an array of options which return integers.
	 *
	 * @return array $options
	 */
	static function getIntegerOptions() {
		/**
		 * Allow custom integer options to be passed to sanitization routines.
		 *
		 * @since 3.9.7
		 *
		 * @param array $value
		 */
		return apply_filters( 'pb_theme_options_ebook_integers', [] );
	}

	/**
	 * Get an array of options which return floats.
	 *
	 * @return array $options
	 */
	static function getFloatOptions() {
		/**
		 * Allow custom float options to be passed to sanitization routines.
		 *
		 * @since 3.9.7
		 *
		 * @param array $value
		 */
		return apply_filters( 'pb_theme_options_ebook_floats', [] );
	}

	/**
	 * Get an array of options which return predefined values.
	 *
	 * @return array $options
	 */
	static function getPredefinedOptions() {
		/**
		 * Allow custom predifined options to be passed to sanitization routines.
		 *
		 * @since 3.9.7
		 *
		 * @param array $value
		 */
		return apply_filters(
			'pb_theme_options_ebook_predefined', [
				'ebook_paragraph_separation',
			]
		);
	}

	/**
	 * Apply overrides.
	 *
	 * @param string $scss
	 *
	 * @return string
	 *
	 * @since 3.9.8
	 */
	static function scssOverrides( $scss ) {

		$styles = \Pressbooks\Container::get( 'Styles' );
		$v2_compatible = $styles->isCurrentThemeCompatible( 2 );

		// --------------------------------------------------------------------
		// Global Options

		$options = get_option( 'pressbooks_theme_options_global' );

		if ( ! $options['chapter_numbers'] ) {
			if ( $v2_compatible ) {
				$styles->getSass()->setVariables(
					[
						'chapter-number-display' => 'none',
					]
				);
			} else {
				$scss .= "div.part-title-wrap > .part-number, div.chapter-title-wrap > .chapter-number { display: none !important; } \n";
			}
		}

		// --------------------------------------------------------------------
		// Ebook Options

		$options = get_option( 'pressbooks_theme_options_ebook' );

		// Indent paragraphs?
		if ( 'skiplines' === $options['ebook_paragraph_separation'] ) {
			if ( $v2_compatible ) {
				$styles->getSass()->setVariables(
					[
						'para-margin-top' => '1em',
						'para-indent' => '0',
					]
				);
			} else {
				$scss .= "p + p, .indent, div.ugc p.indent { text-indent: 0; margin-top: 1em; } \n";
			}
		} else {
			if ( $v2_compatible ) {
				$styles->getSass()->setVariables(
					[
						'para-margin-top' => '0',
						'para-indent' => '1em',
					]
				);
			} else {
				$scss .= "p + p, .indent, div.ugc p.indent { text-indent: 1em; margin-top: 0em; } \n";
			}
		}

		return $scss;
	}
}
