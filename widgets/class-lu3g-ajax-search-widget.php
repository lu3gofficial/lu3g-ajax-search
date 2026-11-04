<?php
/**
 * Widget Elementor: LU3G Ricerca Ajax.
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
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

		$this->add_control( 'search_sku', array(
			'label'        => 'Cerca nello SKU dei prodotti',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => 'WooCommerce: trova il prodotto anche con una parte del codice, comprese le varianti.',
		) );

		$this->add_control( 'meta_keys', array(
			'label'       => 'Meta field in cui cercare',
			'type'        => Controls_Manager::TEXT,
			'placeholder' => 'marca, codice_prodotto',
			'label_block' => true,
			'dynamic'     => array( 'active' => false ),
			'description' => 'Nomi dei campi separati da virgola, scritti a mano e senza spazi. Solo campi semplici (testo, select, numero): repeater e checkbox sono salvati serializzati e danno risultati poco precisi.',
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

		/* ---------- Contenuto: icona ---------- */
		$this->start_controls_section( 'section_ui', array(
			'label' => 'Icona',
		) );

		$this->add_control( 'icon', array(
			'label'       => 'Icona',
			'type'        => Controls_Manager::ICONS,
			'default'     => array( 'value' => '', 'library' => '' ),
			'skin'        => 'inline',
			'label_block' => false,
			'description' => 'Vuota = lente predefinita (segue il colore del testo).',
		) );

		$this->end_controls_section();

		/* ---------- Contenuto: posizione ---------- */
		$this->start_controls_section( 'section_position', array(
			'label' => 'Posizione e comportamento',
		) );

		$this->add_responsive_control( 'display_mode', array(
			'label'          => 'Mostra come',
			'type'           => Controls_Manager::SELECT,
			'options'        => array(
				'icon' => 'Icona',
				'bar'  => 'Barra di input',
			),
			'default'        => 'icon',
			'tablet_default' => 'icon',
			'mobile_default' => 'icon',
			'description'    => 'Si sceglie per ogni dispositivo. Con "Barra di input" il campo sta dove si trova il widget e i risultati scendono a tendina.',
		) );

		$this->add_responsive_control( 'bar_align', array(
			'label'                => 'Allineamento della barra',
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => array(
				'left'   => array( 'title' => 'Sinistra', 'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => 'Centro', 'icon' => 'eicon-text-align-center' ),
				'right'  => array( 'title' => 'Destra', 'icon' => 'eicon-text-align-right' ),
			),
			'selectors_dictionary' => array(
				'left'   => 'flex-start',
				'center' => 'center',
				'right'  => 'flex-end',
			),
			'description'          => 'Si nota quando la barra è più stretta del suo contenitore: la larghezza si regola in Stile → Pannello.',
			'selectors'            => array( '{{WRAPPER}}' => '--lu3g-inline-align: {{VALUE}};' ),
		) );

		// Stessa chiave di prima (desktop_position): i valori già salvati restano
		$this->add_responsive_control( 'desktop_position', array(
			'label'          => 'Posizione del box',
			'type'           => Controls_Manager::SELECT,
			'options'        => array(
				'icon'       => 'Allineato all\'icona',
				'center'     => 'Sotto l\'header, al centro',
				'left'       => 'Sotto l\'header, a sinistra',
				'right'      => 'Sotto l\'header, a destra',
				'full'       => 'Sotto l\'header, a tutta larghezza',
				'top'        => 'In cima allo schermo',
				'middle'     => 'Al centro dello schermo',
				'bottom'     => 'In basso',
				'fullscreen' => 'A tutto schermo',
				'inline'     => 'Nella pagina, sempre visibile',
			),
			'default'        => 'icon',
			'tablet_default' => 'full',
			'mobile_default' => 'full',
			'description'    => 'Dove si apre il box quando si clicca l\'icona. Non vale per i dispositivi con "Barra di input".',
		) );

		$this->add_responsive_control( 'panel_offset_y', array(
			'label'      => 'Distanza verticale',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-panel-offset-y: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'panel_offset_x', array(
			'label'       => 'Distanza dai bordi laterali',
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
			'description' => 'Il box non esce mai dallo schermo: resta almeno a questa distanza dai bordi.',
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-panel-offset-x: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'panel_max_height', array(
			'label'      => 'Altezza massima',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'vh', 'px' ),
			'range'      => array(
				'vh' => array( 'min' => 20, 'max' => 100 ),
				'px' => array( 'min' => 200, 'max' => 1200 ),
			),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-panel-max: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'overlay_show', array(
			'label'        => 'Velo sullo sfondo',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => 'Colore e sfocatura si regolano in Stile → Sfondo sfocato. Non si usa con "Nella pagina".',
		) );

		$this->add_control( 'editor_open', array(
			'label'        => 'Tieni aperto nell\'editor',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => '',
			'description'  => 'Solo per lavorare sulla grafica: nell\'editor il box resta aperto e si aggiorna mentre cambi lo stile. Sul sito non ha effetto.',
		) );

		$this->end_controls_section();

		/* ---------- Contenuto: animazioni ---------- */
		$this->start_controls_section( 'section_animation', array(
			'label' => 'Animazioni',
		) );

		$this->add_control( 'panel_animation', array(
			'label'   => 'Apertura del box',
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				'slide-down'  => 'Scende dall\'alto',
				'slide-up'    => 'Sale dal basso',
				'slide-left'  => 'Entra da destra',
				'slide-right' => 'Entra da sinistra',
				'fade'        => 'Dissolvenza',
				'zoom'        => 'Zoom in avanti',
				'zoom-out'    => 'Zoom all\'indietro',
				'pop'         => 'Rimbalzo',
				'flip'        => 'Rotazione 3D',
				'blur'        => 'Da sfocato a nitido',
				'none'        => 'Nessuna',
			),
			'default' => 'slide-down',
		) );

		$this->add_control( 'panel_anim_duration', array(
			'label'      => 'Durata',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'ms' ),
			'range'      => array( 'ms' => array( 'min' => 100, 'max' => 1500, 'step' => 10 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-anim-duration: {{SIZE}}ms;' ),
		) );

		$this->add_control( 'panel_anim_ease', array(
			'label'     => 'Andamento',
			'type'      => Controls_Manager::SELECT,
			'options'   => array(
				''                                => 'Predefinito',
				'linear'                          => 'Costante',
				'ease-in-out'                     => 'Morbido',
				'cubic-bezier(0.2, 0.8, 0.2, 1)'  => 'Veloce, poi rallenta',
				'cubic-bezier(0.34, 1.56, 0.64, 1)' => 'Con rimbalzo',
			),
			'default'   => '',
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-anim-ease: {{VALUE}};' ),
		) );

		$this->add_control( 'results_animation', array(
			'label'     => 'Comparsa dei risultati',
			'type'      => Controls_Manager::SELECT,
			'separator' => 'before',
			'options'   => array(
				'fade-up' => 'Salgono',
				'fade'    => 'Dissolvenza',
				'slide'   => 'Da sinistra',
				'zoom'    => 'Zoom',
				'none'    => 'Nessuna',
			),
			'default'   => 'fade-up',
		) );

		$this->add_control( 'results_stagger', array(
			'label'      => 'Ritardo tra un risultato e l\'altro',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'ms' ),
			'range'      => array( 'ms' => array( 'min' => 0, 'max' => 200, 'step' => 5 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-results-stagger: {{SIZE}}ms;' ),
		) );

		$this->end_controls_section();

		/* ---------- Contenuto: testi ---------- */
		$this->start_controls_section( 'section_texts', array(
			'label' => 'Testi',
		) );

		$this->add_control( 'placeholder', array(
			'label'       => 'Placeholder',
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Cerca…',
			'label_block' => true,
		) );

		$this->add_control( 'hint_text', array(
			'label'       => 'Testo iniziale',
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Scrivi almeno %d lettere per iniziare.',
			'label_block' => true,
			'description' => '%d diventa il numero di caratteri minimi. Vuoto = nessun testo.',
		) );

		$this->add_control( 'empty_text', array(
			'label'       => 'Nessun risultato',
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Nessun risultato. Prova con un\'altra parola.',
			'label_block' => true,
		) );

		$this->add_control( 'error_text', array(
			'label'       => 'Errore',
			'type'        => Controls_Manager::TEXT,
			'default'     => 'La ricerca non ha risposto. Controlla la connessione e riprova.',
			'label_block' => true,
		) );

		$this->add_control( 'texts_a11y_heading', array(
			'label'     => 'Letti dagli screen reader',
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		) );

		$this->add_control( 'results_text', array(
			'label'   => 'Più risultati',
			'type'    => Controls_Manager::TEXT,
			'default' => '%d risultati',
		) );

		$this->add_control( 'result_text', array(
			'label'   => 'Un risultato',
			'type'    => Controls_Manager::TEXT,
			'default' => '1 risultato',
		) );

		$this->add_control( 'open_label', array(
			'label'   => 'Apri',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Apri la ricerca',
		) );

		$this->add_control( 'close_label', array(
			'label'   => 'Chiudi',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Chiudi la ricerca',
		) );

		$this->add_control( 'dialog_label', array(
			'label'   => 'Nome del box',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Ricerca',
		) );

		$this->add_control( 'results_label', array(
			'label'   => 'Nome dell\'elenco',
			'type'    => Controls_Manager::TEXT,
			'default' => 'Risultati',
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

		$this->add_control( 'panel_font', array(
			'label'     => 'Font',
			'type'      => Controls_Manager::FONT,
			'default'   => '',
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-panel-font: "{{VALUE}}", sans-serif;' ),
		) );

		// Stesse chiavi di prima (panel_radius, panel_width): ora si regolano per dispositivo
		$this->add_responsive_control( 'panel_radius', array(
			'label'      => 'Arrotondamento',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-panel-radius: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'panel_width', array(
			'label'       => 'Larghezza',
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px', 'vw' ),
			'range'       => array(
				'px' => array( 'min' => 240, 'max' => 1400 ),
				'vw' => array( 'min' => 20, 'max' => 100 ),
			),
			'description' => 'Non vale per le posizioni a tutta larghezza.',
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-panel-width: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'panel_padding', array(
			'label'      => 'Spaziatura interna dei risultati',
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-body-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->add_control( 'panel_border_width', array(
			'label'      => 'Spessore del bordo',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 10 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-panel-border-width: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'panel_border_color', array(
			'label'     => 'Colore del bordo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-panel-border-color: {{VALUE}};' ),
		) );

		$this->add_control( 'panel_shadow', array(
			'label'                => 'Ombra',
			'type'                 => Controls_Manager::SELECT,
			'options'              => array(
				''       => 'Predefinita',
				'none'   => 'Nessuna',
				'light'  => 'Leggera',
				'strong' => 'Forte',
			),
			'default'              => '',
			'selectors_dictionary' => array(
				'none'   => 'none',
				'light'  => '0 8px 24px -8px rgba(0, 0, 0, 0.18)',
				'strong' => '0 40px 90px -16px rgba(0, 0, 0, 0.55)',
			),
			'selectors'            => array( '{{WRAPPER}}' => '--lu3g-panel-shadow: {{VALUE}};' ),
		) );

		$this->add_control( 'panel_glass', array(
			'label'       => 'Effetto vetro',
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'description' => 'Sfoca ciò che sta dietro al box. Si vede solo con uno sfondo semitrasparente.',
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-panel-glass: blur({{SIZE}}{{UNIT}});' ),
		) );

		$this->end_controls_section();

		/* ---------- Stile: campo di ricerca ---------- */
		$this->start_controls_section( 'style_bar', array(
			'label' => 'Campo di ricerca',
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'bar_bg', array(
			'label'     => 'Sfondo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-bar-bg: {{VALUE}};' ),
		) );

		$this->add_control( 'input_color', array(
			'label'     => 'Colore del testo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-input-color: {{VALUE}};' ),
		) );

		$this->add_control( 'placeholder_color', array(
			'label'     => 'Colore del placeholder',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-placeholder-color: {{VALUE}};' ),
		) );

		$this->add_responsive_control( 'input_size', array(
			'label'       => 'Dimensione del testo',
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => array( 'px' ),
			'range'       => array( 'px' => array( 'min' => 12, 'max' => 40 ) ),
			'description' => 'Su iPhone, sotto i 16px il telefono ingrandisce la pagina quando si tocca il campo.',
			'selectors'   => array( '{{WRAPPER}}' => '--lu3g-input-size: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'bar_padding', array(
			'label'      => 'Spaziatura interna',
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-bar-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->add_control( 'bar_divider_width', array(
			'label'      => 'Spessore della linea sotto il campo',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 6 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-bar-divider-width: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'bar_divider_color', array(
			'label'     => 'Colore della linea',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-bar-divider-color: {{VALUE}};' ),
		) );

		$this->add_control( 'bar_icon_show', array(
			'label'        => 'Lente nel campo',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		) );

		$this->add_control( 'bar_icon_color', array(
			'label'     => 'Colore della lente',
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'bar_icon_show' => 'yes' ),
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-bar-icon-color: {{VALUE}};' ),
		) );

		$this->add_control( 'bar_icon_size', array(
			'label'      => 'Dimensione della lente',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 10, 'max' => 40 ) ),
			'condition'  => array( 'bar_icon_show' => 'yes' ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-bar-icon-size: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'close_color', array(
			'label'     => 'Colore della X',
			'type'      => Controls_Manager::COLOR,
			'separator' => 'before',
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-close-color: {{VALUE}};' ),
		) );

		$this->add_control( 'close_color_hover', array(
			'label'     => 'Colore della X in hover',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => '--lu3g-close-hover: {{VALUE}};' ),
		) );

		$this->add_control( 'message_size', array(
			'label'      => 'Dimensione dei messaggi',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 11, 'max' => 24 ) ),
			'separator'  => 'before',
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-message-size: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();

		/* ---------- Stile: barra di input ---------- */
		// La barra resta dentro il widget (non va nel <body>): qui i selettori di Elementor funzionano direttamente.
		$bar_box   = '{{WRAPPER}} .lu3g-search__panel.lu3g-pos-inline';
		$bar_input = '{{WRAPPER}} .lu3g-search__panel.lu3g-pos-inline .lu3g-search__input';
		$bar_lens  = '{{WRAPPER}} .lu3g-search__panel.lu3g-pos-inline .lu3g-search__bar-icon';

		$this->start_controls_section( 'style_input_bar', array(
			'label' => 'Barra di input',
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'ibar_note', array(
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => 'Vale per i dispositivi con "Mostra come: Barra di input". La tendina dei risultati si regola in Stile → Pannello e Risultati.',
			'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
		) );

		$this->add_responsive_control( 'ibar_width', array(
			'label'      => 'Larghezza',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%', 'vw' ),
			'range'      => array(
				'px' => array( 'min' => 120, 'max' => 1200 ),
				'%'  => array( 'min' => 10, 'max' => 100 ),
				'vw' => array( 'min' => 10, 'max' => 100 ),
			),
			'selectors'  => array( $bar_box => 'width: {{SIZE}}{{UNIT}}; max-width: 100%;' ),
		) );

		$this->add_responsive_control( 'ibar_height', array(
			'label'      => 'Altezza',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 28, 'max' => 120 ) ),
			'selectors'  => array( $bar_box . ' .lu3g-search__bar' => 'min-height: {{SIZE}}{{UNIT}}; padding-top: 0; padding-bottom: 0;' ),
		) );

		$this->add_responsive_control( 'ibar_padding', array(
			'label'      => 'Spaziatura interna',
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors'  => array( $bar_box . ' .lu3g-search__bar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'ibar_gap', array(
			'label'      => 'Distanza tra lente e testo',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'selectors'  => array( $bar_box . ' .lu3g-search__bar' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'ibar_radius', array(
			'label'      => 'Arrotondamento',
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array( $bar_box => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'ibar_typography',
			'label'    => 'Caratteri',
			'selector' => $bar_input,
		) );

		$this->add_group_control( Group_Control_Border::get_type(), array(
			'name'     => 'ibar_border',
			'label'    => 'Bordo',
			'selector' => $bar_box,
		) );

		$this->add_control( 'ibar_icon_side', array(
			'label'                => 'Lente',
			'type'                 => Controls_Manager::CHOOSE,
			'options'              => array(
				'left'  => array( 'title' => 'A sinistra', 'icon' => 'eicon-h-align-left' ),
				'right' => array( 'title' => 'A destra', 'icon' => 'eicon-h-align-right' ),
			),
			'selectors_dictionary' => array(
				'left'  => '0',
				'right' => '2',
			),
			'selectors'            => array( $bar_lens => 'order: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_close', array(
			'label'   => 'X per svuotare',
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				'typing' => 'Solo quando c\'è del testo',
				'always' => 'Sempre',
				'never'  => 'Mai',
			),
			'default' => 'typing',
		) );

		$this->add_responsive_control( 'ibar_dropdown_gap', array(
			'label'      => 'Distanza della tendina',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-dd-gap: {{SIZE}}{{UNIT}};' ),
		) );

		$this->start_controls_tabs( 'ibar_tabs', array( 'separator' => 'before' ) );

		/* Normale */
		$this->start_controls_tab( 'ibar_tab_normal', array( 'label' => 'Normale' ) );

		$this->add_control( 'ibar_bg', array(
			'label'     => 'Sfondo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_color', array(
			'label'     => 'Testo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_input => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_placeholder', array(
			'label'     => 'Placeholder',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_input . '::placeholder' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_lens_color', array(
			'label'     => 'Lente',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_lens => 'color: {{VALUE}};' ),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'ibar_shadow',
			'label'    => 'Ombra',
			'selector' => $bar_box,
		) );

		$this->end_controls_tab();

		/* Hover */
		$this->start_controls_tab( 'ibar_tab_hover', array( 'label' => 'Hover' ) );

		$this->add_control( 'ibar_bg_hover', array(
			'label'     => 'Sfondo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box . ':hover' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_border_hover', array(
			'label'     => 'Colore del bordo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box . ':hover' => 'border-color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_lens_hover', array(
			'label'     => 'Lente',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box . ':hover .lu3g-search__bar-icon' => 'color: {{VALUE}};' ),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'ibar_shadow_hover',
			'label'    => 'Ombra',
			'selector' => $bar_box . ':hover',
		) );

		$this->end_controls_tab();

		/* Attiva: mentre si scrive */
		$this->start_controls_tab( 'ibar_tab_focus', array( 'label' => 'Attiva' ) );

		$this->add_control( 'ibar_bg_focus', array(
			'label'     => 'Sfondo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box . ':focus-within' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_color_focus', array(
			'label'     => 'Testo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box . ':focus-within .lu3g-search__input' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_border_focus', array(
			'label'     => 'Colore del bordo',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box . ':focus-within' => 'border-color: {{VALUE}};' ),
		) );

		$this->add_control( 'ibar_lens_focus', array(
			'label'     => 'Lente',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( $bar_box . ':focus-within .lu3g-search__bar-icon' => 'color: {{VALUE}};' ),
		) );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array(
			'name'     => 'ibar_shadow_focus',
			'label'    => 'Ombra',
			'selector' => $bar_box . ':focus-within',
		) );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		/* ---------- Stile: risultati ---------- */
		$this->start_controls_section( 'style_results', array(
			'label' => 'Risultati',
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'title_part', array(
			'label'       => 'Parte del titolo da mostrare',
			'type'        => Controls_Manager::SELECT,
			'options'     => array(
				'full'   => 'Titolo intero',
				'after'  => 'Solo dopo il separatore',
				'before' => 'Solo prima del separatore',
			),
			'default'     => 'full',
			'description' => 'Esempio: "Abito Da Sposa Demetrios – Style 1285" diventa "Style 1285". La ricerca continua a cercare nel titolo completo.',
		) );

		$this->add_control( 'title_separator', array(
			'label'       => 'Separatore',
			'type'        => Controls_Manager::TEXT,
			'default'     => '–',
			'condition'   => array( 'title_part!' => 'full' ),
			'description' => 'Il testo che divide il titolo. Con un trattino valgono tutti i tipi (-, –, —), purché abbiano uno spazio prima e dopo. Si usa il primo che compare; i titoli senza separatore restano interi.',
		) );

		$this->add_responsive_control( 'title_lines', array(
			'label'          => 'Troncare il titolo dopo',
			'type'           => Controls_Manager::SELECT,
			'options'        => array(
				'1'    => '1 riga',
				'2'    => '2 righe',
				'3'    => '3 righe',
				'4'    => '4 righe',
				'none' => 'Mai (titolo intero)',
			),
			'default'        => '1',
			'tablet_default' => '1',
			'mobile_default' => '1',
			'description'    => 'I titoli più lunghi finiscono con "…".',
			'selectors'      => array( '{{WRAPPER}}' => '--lu3g-title-lines: {{VALUE}};' ),
		) );

		$this->add_control( 'title_max_chars', array(
			'label'       => 'Numero massimo di caratteri del titolo',
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 300,
			'default'     => '',
			'description' => 'Vuoto = nessun limite. Taglia alla fine dell\'ultima parola intera e aggiunge "…"; il titolo completo resta visibile passando il mouse.',
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

		$this->add_responsive_control( 'sub_size', array(
			'label'      => 'Dimensione sottotitolo',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 10, 'max' => 22 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-sub-size: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'thumb_size', array(
			'label'      => 'Dimensione immagine',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 24, 'max' => 160 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-thumb-size: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'thumb_radius', array(
			'label'      => 'Arrotondamento immagine',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-thumb-radius: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'item_padding', array(
			'label'      => 'Spaziatura del risultato',
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-item-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->add_control( 'item_gap', array(
			'label'      => 'Distanza immagine e testo',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-item-gap: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'item_radius', array(
			'label'      => 'Arrotondamento del risultato',
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
			'selectors'  => array( '{{WRAPPER}}' => '--lu3g-item-radius: {{SIZE}}{{UNIT}};' ),
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

		// Valore salvato, non quello dei tag dinamici: qui serve il nome di un campo, non il valore di un prodotto.
		// Un nome con spazi non è un campo valido: va ignorato, non trasformato in un altro nome.
		$meta_keys = array_filter( array_map( 'trim', explode( ',', (string) $this->get_settings( 'meta_keys' ) ) ), static function ( $key ) {
			return '' !== $key && ! preg_match( '/\s/', $key );
		} );
		$min       = max( 1, min( 5, (int) ( $s['min_chars'] ?? 2 ) ) );

		list( $payload, $signature ) = Lu3g_Search_Config::sign( array(
			'types'  => (array) ( $s['post_types'] ?? array() ),
			'meta'   => $meta_keys,
			'sku'    => 'yes' === ( $s['search_sku'] ?? 'yes' ),
			'terms'  => 'yes' === ( $s['search_terms'] ?? '' ),
			'subtax' => (string) ( $s['subtitle_tax'] ?? '' ),
			'thumb'  => 'yes' === ( $s['show_thumb'] ?? '' ),
			'limit'  => (int) ( $s['limit'] ?? 8 ),
			'min'    => $min,
		) );

		$text = static function ( $key, $fallback ) use ( $s ) {
			$value = $s[ $key ] ?? '';
			return '' !== (string) $value ? (string) $value : $fallback;
		};

		$i18n = array(
			'results' => $text( 'results_text', '%d risultati' ),
			'result'  => $text( 'result_text', '1 risultato' ),
			'empty'   => (string) ( $s['empty_text'] ?? '' ),
			'error'   => (string) ( $s['error_text'] ?? '' ),
			'open'    => $text( 'open_label', 'Apri la ricerca' ),
			'close'   => $text( 'close_label', 'Chiudi la ricerca' ),
		);

		$hint = str_replace( '%d', (string) $min, (string) ( $s['hint_text'] ?? '' ) );

		// Posizione per dispositivo: vuoto = eredita dal dispositivo più grande
		$allowed    = array( 'icon', 'center', 'left', 'right', 'full', 'top', 'middle', 'bottom', 'fullscreen', 'inline' );
		$pos_of     = static function ( $key, $fallback ) use ( $s, $allowed ) {
			$value = (string) ( $s[ $key ] ?? '' );
			return in_array( $value, $allowed, true ) ? $value : $fallback;
		};
		$pos_desk   = $pos_of( 'desktop_position', 'icon' );
		$pos_tablet = $pos_of( 'desktop_position_tablet', $pos_desk );
		$positions  = array(
			'desktop' => $pos_desk,
			'tablet'  => $pos_tablet,
			'mobile'  => $pos_of( 'desktop_position_mobile', $pos_tablet ),
		);
		// "Barra di input" è la modalità "Nella pagina", qualunque sia la posizione scelta
		foreach ( array( 'desktop' => 'display_mode', 'tablet' => 'display_mode_tablet', 'mobile' => 'display_mode_mobile' ) as $device => $key ) {
			if ( 'bar' === ( $s[ $key ] ?? '' ) ) {
				$positions[ $device ] = 'inline';
			}
		}

		$anims         = array( 'slide-down', 'slide-up', 'slide-left', 'slide-right', 'fade', 'zoom', 'zoom-out', 'pop', 'flip', 'blur', 'none' );
		$panel_anim    = in_array( $s['panel_animation'] ?? '', $anims, true ) ? $s['panel_animation'] : 'slide-down';
		$results_anim  = in_array( $s['results_animation'] ?? '', array( 'fade-up', 'fade', 'slide', 'zoom', 'none' ), true ) ? $s['results_animation'] : 'fade-up';
		$is_editor     = \Elementor\Plugin::$instance->editor->is_edit_mode();

		// ID univoci anche con più copie dello stesso widget nella pagina
		$uid        = $id . '-' . wp_unique_id();
		$panel_id   = 'lu3g-search-panel-' . $uid;
		$results_id = 'lu3g-search-results-' . $uid;

		if ( empty( $s['post_types'] ) && $is_editor ) {
			echo '<div class="lu3g-search__notice">LU3G Ricerca Ajax: scegli almeno un post type in "Cerca in".</div>';
		}
		?>
		<div class="lu3g-search"
			data-config="<?php echo esc_attr( $payload ); ?>"
			data-sig="<?php echo esc_attr( $signature ); ?>"
			data-min="<?php echo esc_attr( $min ); ?>"
			data-title-max="<?php echo esc_attr( max( 0, (int) ( $s['title_max_chars'] ?? 0 ) ) ); ?>"
			data-title-part="<?php echo esc_attr( in_array( $s['title_part'] ?? '', array( 'after', 'before' ), true ) ? $s['title_part'] : 'full' ); ?>"
			data-title-sep="<?php echo esc_attr( (string) ( $s['title_separator'] ?? '' ) ); ?>"
			data-pos="<?php echo esc_attr( wp_json_encode( $positions ) ); ?>"
			data-bp="<?php echo esc_attr( wp_json_encode( self::breakpoints() ) ); ?>"
			data-overlay="<?php echo 'yes' === ( $s['overlay_show'] ?? 'yes' ) ? 'yes' : 'no'; ?>"
			<?php if ( $is_editor && 'yes' === ( $s['editor_open'] ?? '' ) ) : ?>data-editor-open="yes"<?php endif; ?>
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
				data-anim="<?php echo esc_attr( $panel_anim ); ?>"
				data-results-anim="<?php echo esc_attr( $results_anim ); ?>"
				data-bar-close="<?php echo esc_attr( in_array( $s['ibar_close'] ?? '', array( 'typing', 'always', 'never' ), true ) ? $s['ibar_close'] : 'typing' ); ?>"
				role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $text( 'dialog_label', 'Ricerca' ) ); ?>">

				<div class="lu3g-search__bar">
					<?php if ( 'yes' === ( $s['bar_icon_show'] ?? 'yes' ) ) : ?>
						<span class="lu3g-search__bar-icon" aria-hidden="true"><?php echo self::lens_svg(); // phpcs:ignore ?></span>
					<?php endif; ?>
					<input type="search" class="lu3g-search__input"
						placeholder="<?php echo esc_attr( $s['placeholder'] ?? '' ); ?>"
						aria-label="<?php echo esc_attr( $text( 'placeholder', 'Cerca' ) ); ?>"
						autocomplete="off" autocapitalize="off" spellcheck="false" enterkeyhint="search"
						role="combobox" aria-autocomplete="list" aria-expanded="false"
						aria-controls="<?php echo esc_attr( $results_id ); ?>">
					<button type="button" class="lu3g-search__close" aria-label="<?php echo esc_attr( $i18n['close'] ); ?>">
						<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
					</button>
				</div>

				<div class="lu3g-search__progress" aria-hidden="true"></div>

				<div class="lu3g-search__body">
					<p class="lu3g-search__hint"><?php echo esc_html( $hint ); ?></p>

					<div class="lu3g-search__skeleton" aria-hidden="true">
						<?php for ( $i = 0; $i < 3; $i++ ) : ?>
							<div class="lu3g-search__sk-row">
								<span class="lu3g-search__sk-thumb"></span>
								<span class="lu3g-search__sk-lines"><span></span><span></span></span>
							</div>
						<?php endfor; ?>
					</div>

					<ul class="lu3g-search__results" id="<?php echo esc_attr( $results_id ); ?>" role="listbox" aria-label="<?php echo esc_attr( $text( 'results_label', 'Risultati' ) ); ?>"></ul>

					<p class="lu3g-search__message"></p>
				</div>

				<div class="lu3g-search__live" aria-live="polite"></div>
			</div>
		</div>
		<?php
	}

	/** Breakpoint attivi di Elementor, per scegliere la posizione giusta nel browser. */
	private static function breakpoints() {
		$out     = array( 'mobile' => 767, 'tablet' => 1024 );
		$manager = \Elementor\Plugin::$instance->breakpoints ?? null;
		if ( $manager ) {
			foreach ( array_keys( $out ) as $name ) {
				$bp = $manager->get_breakpoints( $name );
				if ( $bp ) {
					$out[ $name ] = (int) $bp->get_value();
				}
			}
		}
		return $out;
	}

	private static function lens_svg() {
		return '<svg class="lu3g-search__lens" viewBox="0 0 24 24" width="1em" height="1em" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M15.5 15.5L21 21" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
	}
}
