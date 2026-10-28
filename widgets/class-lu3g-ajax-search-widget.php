<?php
/**
 * Widget Elementor: LU3G Ricerca Ajax.
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

class Lu3g_Ajax_Search_Widget extends Widget_Base {

	public function get_name() {
		return 'lu3g-ajax-search';
	}

	public function get_title() {
		return 'LU3G Ricerca Ajax';
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'search', 'ricerca', 'ajax', 'lu3g', 'jetengine' );
	}

	public function get_style_depends() {
		return array( 'lu3g-ajax-search' );
	}

	public function get_script_depends() {
		return array( 'lu3g-ajax-search' );
	}

	/* ------------------------------------------------------------------ */
	/* Opzioni                                                            */
	/* ------------------------------------------------------------------ */

	private function post_type_options() {
		$options = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			if ( in_array( $type->name, array( 'attachment', 'elementor_library', 'e-landing-page' ), true ) ) {
				continue;
			}
			$options[ $type->name ] = $type->label . ' (' . $type->name . ')';
		}
		return $options;
	}

	private function taxonomy_options() {
		$options = array( '' => 'Nessuno' );
		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $tax ) {
			$options[ $tax->name ] = $tax->label . ' (' . $tax->name . ')';
		}
		return $options;
	}

	/* ------------------------------------------------------------------ */
	/* Controlli                                                          */
	/* ------------------------------------------------------------------ */

	protected function register_controls() {

		/* ---------- Contenuto: ricerca ---------- */
		$this->start_controls_section( 'section_search', array(
			'label' => 'Ricerca',
		) );

		$this->add_control( 'post_types', array(
			'label'       => 'Cerca in',
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => $this->post_type_options(),
			'label_block' => true,
			'description' => 'Uno o più post type (es. il CPT prodotti di JetEngine).',
		) );

		$this->add_control( 'meta_keys', array(
			'label'       => 'Meta field in cui cercare',
			'type'        => Controls_Manager::TEXT,
			'placeholder' => 'stilista, codice_modello',
			'label_block' => true,
			'description' => 'Nomi dei campi JetEngine separati da virgola. Solo campi semplici (testo, select, numero): repeater e checkbox sono salvati serializzati e danno risultati poco precisi.',
		) );

		$this->add_control( 'search_terms', array(
			'label'        => 'Cerca anche nelle tassonomie',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => 'Scrivendo il nome di una categoria mostra i prodotti di quella categoria.',
		) );

		$this->add_control( 'subtitle_tax', array(
			'label'   => 'Sottotitolo risultato',
			'type'    => Controls_Manager::SELECT,
			'options' => $this->taxonomy_options(),
			'default' => '',
			'description' => 'Tassonomia da mostrare sotto il titolo (primo termine).',
		) );

		$this->add_control( 'show_thumb', array(
			'label'        => 'Mostra immagine',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->add_control( 'limit', array(
			'label'   => 'Numero massimo di risultati',
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 20,
			'default' => 8,
		) );

		$this->add_control( 'min_chars', array(
			'label'   => 'Caratteri minimi',
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 5,
			'default' => 2,
		) );

		$this->end_controls_section();

		/* ---------- Contenuto: aspetto e testi ---------- */
		$this->start_controls_section( 'section_ui', array(
			'label' => 'Pannello e testi',
		) );

		$this->add_control( 'icon', array(
			'label'       => 'Icona',
			'type'        => Controls_Manager::ICONS,
			'default'     => array( 'value' => '', 'library' => '' ),
			'skin'        => 'inline',
			'label_block' => false,
			'description' => 'Vuota = lente predefinita (segue il colore del testo).',
		) );

		$this->add_control( 'desktop_position', array(
			'label'   => 'Pannello su desktop',
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				'center' => 'Centrato sotto l\'header',
				'icon'   => 'Allineato all\'icona',
			),
			'default' => 'icon',
		) );

		$this->add_control( 'placeholder', array(
			'label'   => 'Placeholder',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Cerca un abito, uno stilista…',
		) );

		$this->add_control( 'hint_text', array(
			'label'   => 'Testo iniziale',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Scrivi almeno 2 lettere per iniziare.',
		) );

		$this->add_control( 'empty_text', array(
			'label'   => 'Nessun risultato',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Nessun risultato. Prova con un\'altra parola o con il nome di una categoria.',
		) );

		$this->add_control( 'error_text', array(
			'label'   => 'Errore',
			'type'    => Controls_Manager::TEXT,
			'default' => 'La ricerca non ha risposto. Controlla la connessione e riprova.',
		) );

		$this->end_controls_section();

		/* ---------- Stile: icona ---------- */
		$this->start_controls_section( 'style_icon', array(
			'label' => 'Icona',
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'icon_size', array(
			'label'      => 'Dimensione',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 12, 'max' => 48 ) ),
			'selectors'  => array(
				'{{WRAPPER}} .lu3g-search__toggle' => 'font-size: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'icon_color', array(
			'label'       => 'Colore',
			'type'        => Controls_Manager::COLOR,
			'description' => 'Su tablet e mobile può essere sovrascritto dal CSS dell\'header (cambio colore allo scroll).',
			'selectors'   => array(
				'{{WRAPPER}} .lu3g-search__toggle' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'focus_ring', array(
			'label'        => 'Contorno da tastiera',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => 'Al clic il contorno non compare mai. Questo lo mostra solo a chi naviga con Tab (consigliato per l\'accessibilità).',
		) );

		$this->add_control( 'icon_color_hover', array(
			'label'     => 'Colore hover',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .lu3g-search__toggle:hover' => 'color: {{VALUE}};',
			),
		) );

		$this->end_controls_section();

		/* ---------- Stile: pannello ---------- */
		// Il pannello viene spostato nel <body> via JS: lo stile passa tramite variabili CSS.
		$this->start_controls_section( 'style_panel', array(
			'label' => 'Pannello',
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'panel_bg', array(
			'label'     => 'Sfondo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-panel-bg: {{VALUE}};' ),
		) );

		$this->add_control( 'panel_text', array(
			'label'     => 'Testo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-panel-text: {{VALUE}};' ),
		) );

		$this->add_control( 'panel_muted', array(
			'label'     => 'Testo secondario',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-panel-muted: {{VALUE}};' ),
		) );

		$this->add_control( 'accent', array(
			'label'       => 'Colore accento',
			'type'        => Controls_Manager::COLOR,
			'description' => 'Barra di caricamento e lettere evidenziate nei risultati.',
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-accent: {{VALUE}};' ),
		) );

		$this->add_control( 'panel_radius', array(
			'label'      => 'Arrotondamento',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-panel-radius: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'panel_width', array(
			'label'       => 'Larghezza su desktop',
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array( 'px' => array( 'min' => 320, 'max' => 900 ) ),
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-panel-width: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();

		/* ---------- Stile: risultati ---------- */
		$this->start_controls_section( 'style_results', array(
			'label' => 'Risultati',
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'title_size', array(
			'label'      => 'Dimensione titolo',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 11, 'max' => 28 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-title-size: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'title_weight', array(
			'label'     => 'Peso titolo',
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				''    => 'Predefinito',
				'300' => '300',
				'400' => '400',
				'500' => '500',
				'600' => '600',
				'700' => '700',
			),
			'default'   => '',
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-title-weight: {{VALUE}};' ),
		) );

		$this->add_control( 'title_transform', array(
			'label'     => 'Trasformazione titolo',
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				''           => 'Predefinita',
				'none'       => 'Nessuna',
				'uppercase'  => 'Maiuscolo',
				'capitalize' => 'Iniziali maiuscole',
			),
			'default'   => '',
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-title-transform: {{VALUE}};' ),
		) );

		$this->add_control( 'title_spacing', array(
			'label'      => 'Spaziatura lettere titolo',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'em' ),
			'range'      => array(
				'px' => array( 'min' => -1, 'max' => 5, 'step' => 0.1 ),
				'em' => array( 'min' => -0.1, 'max' => 0.5, 'step' => 0.01 ),
			),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-title-spacing: {{SIZE}}{{UNIT}};' ),
		) );

		$this->start_controls_tabs( 'results_tabs' );

		/* Normale */
		$this->start_controls_tab( 'results_tab_normal', array( 'label' => 'Normale' ) );

		$this->add_control( 'item_bg', array(
			'label'     => 'Sfondo risultato',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-item-bg: {{VALUE}};' ),
		) );

		$this->add_control( 'title_color', array(
			'label'     => 'Colore titolo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-title-color: {{VALUE}};' ),
		) );

		$this->add_control( 'mark_color', array(
			'label'       => 'Colore lettere evidenziate',
			'type'        => Controls_Manager::COLOR,
			'description' => 'Se vuoto usa il colore accento.',
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-mark-color: {{VALUE}};' ),
		) );

		$this->add_control( 'sub_color', array(
			'label'     => 'Colore sottotitolo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-sub-color: {{VALUE}};' ),
		) );

		$this->end_controls_tab();

		/* Hover */
		$this->start_controls_tab( 'results_tab_hover', array( 'label' => 'Hover' ) );

		// Stessa chiave di prima (hover_bg): i valori già salvati restano
		$this->add_control( 'hover_bg', array(
			'label'     => 'Sfondo risultato',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-hover-bg: {{VALUE}};' ),
		) );

		$this->add_control( 'title_color_hover', array(
			'label'     => 'Colore titolo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-title-hover: {{VALUE}};' ),
		) );

		$this->add_control( 'title_deco_hover', array(
			'label'     => 'Decorazione titolo',
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				''          => 'Nessuna',
				'underline' => 'Sottolineato',
			),
			'default'   => '',
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-title-deco-hover: {{VALUE}};' ),
		) );

		$this->add_control( 'mark_color_hover', array(
			'label'     => 'Colore lettere evidenziate',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-mark-hover: {{VALUE}};' ),
		) );

		$this->add_control( 'sub_color_hover', array(
			'label'     => 'Colore sottotitolo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-sub-hover: {{VALUE}};' ),
		) );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		/* ---------- Stile: sfondo sfocato ---------- */
		$this->start_controls_section( 'style_overlay', array(
			'label' => 'Sfondo sfocato',
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'overlay_color', array(
			'label'     => 'Colore velo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-overlay-color: {{VALUE}};' ),
		) );

		$this->add_control( 'overlay_blur', array(
			'label'       => 'Blur durante la ricerca',
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
			'description' => 'All\'apertura il blur è leggero; raggiunge questo valore quando inizi a scrivere.',
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-overlay-blur: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------ */
	/* Render                                                             */
	/* ------------------------------------------------------------------ */

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = $this->get_id();

		$meta_keys = array_filter( array_map( 'trim', explode( ',', (string) ( $s['meta_keys'] ?? '' ) ) ) );
		$min       = max( 1, min( 5, (int) ( $s['min_chars'] ?? 2 ) ) );

		list( $payload, $signature ) = Lu3g_Search_Config::sign( array(
			'types'  => (array) ( $s['post_types'] ?? array() ),
			'meta'   => $meta_keys,
			'terms'  => 'yes' === ( $s['search_terms'] ?? '' ),
			'subtax' => (string) ( $s['subtitle_tax'] ?? '' ),
			'thumb'  => 'yes' === ( $s['show_thumb'] ?? '' ),
			'limit'  => (int) ( $s['limit'] ?? 8 ),
			'min'    => $min,
		) );

		$i18n = array(
			'results' => '%d risultati',
			'result'  => '1 risultato',
			'empty'   => (string) ( $s['empty_text'] ?? '' ),
			'error'   => (string) ( $s['error_text'] ?? '' ),
			'open'    => 'Apri la ricerca',
			'close'   => 'Chiudi la ricerca',
		);

		// ID univoci anche con più copie dello stesso widget nella pagina
		$uid        = $id . '-' . wp_unique_id();
		$panel_id   = 'lu3g-search-panel-' . $uid;
		$results_id = 'lu3g-search-results-' . $uid;

		if ( empty( $s['post_types'] ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="lu3g-search__notice">LU3G Ricerca Ajax: scegli almeno un post type in "Cerca in".</div>';
		}
		?>
		<div class="lu3g-search"
			data-config="<?php echo esc_attr( $payload ); ?>"
			data-sig="<?php echo esc_attr( $signature ); ?>"
			data-min="<?php echo esc_attr( $min ); ?>"
			data-desktop-pos="<?php echo esc_attr( $s['desktop_position'] ?? 'icon' ); ?>"
			data-focus-ring="<?php echo 'yes' === ( $s['focus_ring'] ?? 'yes' ) ? 'yes' : 'no'; ?>"
			data-i18n="<?php echo esc_attr( wp_json_encode( $i18n ) ); ?>">

			<button type="button" class="lu3g-search__toggle"
				aria-label="<?php echo esc_attr( $i18n['open'] ); ?>"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $panel_id ); ?>">
				<?php
				if ( ! empty( $s['icon']['value'] ) ) {
					Icons_Manager::render_icon( $s['icon'], array( 'aria-hidden' => 'true' ) );
				} else {
					echo self::lens_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</button>

			<div class="lu3g-search__overlay" aria-hidden="true"></div>

			<div class="lu3g-search__panel is-idle" id="<?php echo esc_attr( $panel_id ); ?>"
				role="dialog" aria-modal="true" aria-label="Ricerca">

				<div class="lu3g-search__bar">
					<span class="lu3g-search__bar-icon" aria-hidden="true"><?php echo self::lens_svg(); // phpcs:ignore ?></span>
					<input type="search" class="lu3g-search__input"
						placeholder="<?php echo esc_attr( $s['placeholder'] ?? '' ); ?>"
						aria-label="<?php echo esc_attr( $s['placeholder'] ?: 'Cerca' ); ?>"
						autocomplete="off" autocapitalize="off" spellcheck="false" enterkeyhint="search"
						role="combobox" aria-autocomplete="list" aria-expanded="false"
						aria-controls="<?php echo esc_attr( $results_id ); ?>">
					<button type="button" class="lu3g-search__close" aria-label="<?php echo esc_attr( $i18n['close'] ); ?>">
						<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
					</button>
				</div>

				<div class="lu3g-search__progress" aria-hidden="true"></div>

				<div class="lu3g-search__body">
					<p class="lu3g-search__hint"><?php echo esc_html( $s['hint_text'] ?? '' ); ?></p>

					<div class="lu3g-search__skeleton" aria-hidden="true">
						<?php for ( $i = 0; $i < 3; $i++ ) : ?>
							<div class="lu3g-search__sk-row">
								<span class="lu3g-search__sk-thumb"></span>
								<span class="lu3g-search__sk-lines"><span></span><span></span></span>
							</div>
						<?php endfor; ?>
					</div>

					<ul class="lu3g-search__results" id="<?php echo esc_attr( $results_id ); ?>" role="listbox" aria-label="Risultati"></ul>

					<p class="lu3g-search__message"></p>
				</div>

				<div class="lu3g-search__live" aria-live="polite"></div>
			</div>
		</div>
		<?php
	}

	private static function lens_svg() {
		return '<svg class="lu3g-search__lens" viewBox="0 0 24 24" width="1em" height="1em" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M15.5 15.5L21 21" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
	}
}
