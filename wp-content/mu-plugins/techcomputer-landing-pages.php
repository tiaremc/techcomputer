<?php
/**
 * Plugin Name: Techcomputer - Landing pages publicitarias
 * Description: Landings SEO (pantallas, bisagras, mantención, reparación) con el diseño actual del sitio.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TC_AD_LANDING_VERSION', '15' );
define( 'TC_AD_LANDING_VIDEO_URL', 'https://techcomputer.cl/wp-content/uploads/2024/11/A3Directo.mp4' );

add_action( 'init', 'tc_ad_landing_register_shortcode' );
add_action( 'init', 'tc_ad_landing_maybe_create_pages', 20 );
add_action( 'wp_enqueue_scripts', 'tc_ad_landing_enqueue_assets' );
add_filter( 'the_content', 'tc_ad_landing_filter_content', 11 );
add_filter( 'document_title_parts', 'tc_ad_landing_document_title' );
add_action( 'wp_head', 'tc_ad_landing_meta_description', 1 );
add_action( 'wp_head', 'tc_ad_landing_faq_schema', 2 );
add_action( 'wp_head', 'tc_ad_landing_canonical_tag', 3 );
add_action( 'add_meta_boxes', 'tc_ad_landing_add_video_metabox' );
add_action( 'save_post', 'tc_ad_landing_save_video_metabox' );
add_action( 'admin_enqueue_scripts', 'tc_ad_landing_admin_scripts' );

function tc_ad_landing_admin_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_media();
}

function tc_ad_landing_add_video_metabox() {
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	if ( ! $post_id ) {
		return;
	}
	$slug = get_post_field( 'post_name', $post_id );
	if ( ! isset( tc_ad_landing_definitions()[ $slug ] ) ) {
		return;
	}
	add_meta_box(
		'tc_ad_landing_video',
		'🎬 Video de la landing',
		'tc_ad_landing_render_video_metabox',
		'page',
		'normal',
		'high'
	);
}

function tc_ad_landing_render_video_metabox( $post ) {
	$video_url = get_post_meta( $post->ID, '_tc_ad_landing_video_url', true );
	$hero_img  = get_post_meta( $post->ID, '_tc_ad_landing_hero_image', true );
	wp_nonce_field( 'tc_ad_landing_video_nonce', 'tc_ad_landing_video_nonce' );
	?>
	<table class="form-table" style="margin:0">
		<tr>
			<th style="width:160px"><label>📹 Video</label></th>
			<td>
				<div style="display:flex;gap:8px;align-items:center;">
					<input type="url" id="tc_ad_landing_video_url" name="tc_ad_landing_video_url"
						value="<?php echo esc_attr( $video_url ); ?>"
						placeholder="Dejar vacío para usar el video por defecto"
						style="flex:1;"/>
					<button type="button" class="button" id="tc_landing_pick_video">Seleccionar video</button>
				</div>
				<?php if ( $video_url ) : ?>
				<p style="margin:4px 0 0;color:#555;font-size:12px">Actual: <a href="<?php echo esc_url( $video_url ); ?>" target="_blank"><?php echo esc_html( basename( $video_url ) ); ?></a></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th><label>🖼 Imagen de fondo (hero)</label></th>
			<td>
				<div style="display:flex;gap:8px;align-items:center;">
					<input type="url" id="tc_ad_landing_hero_image" name="tc_ad_landing_hero_image"
						value="<?php echo esc_attr( $hero_img ); ?>"
						placeholder="Dejar vacío para usar la imagen por defecto"
						style="flex:1;"/>
					<button type="button" class="button" id="tc_landing_pick_hero">Seleccionar imagen</button>
				</div>
				<?php if ( $hero_img ) : ?>
				<div style="margin-top:8px"><img src="<?php echo esc_url( $hero_img ); ?>" style="max-height:80px;border-radius:6px;border:1px solid #ddd"/></div>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<script>
	function tcLandingMediaPicker(btnId, inputId, type) {
		document.getElementById(btnId).addEventListener('click', function() {
			var frame = wp.media({ title: 'Seleccionar archivo', button: { text: 'Usar este archivo' }, library: { type: type }, multiple: false });
			frame.on('select', function() {
				var url = frame.state().get('selection').first().toJSON().url;
				document.getElementById(inputId).value = url;
			});
			frame.open();
		});
	}
	tcLandingMediaPicker('tc_landing_pick_video', 'tc_ad_landing_video_url', 'video');
	tcLandingMediaPicker('tc_landing_pick_hero',  'tc_ad_landing_hero_image', 'image');
	</script>
	<?php
}

function tc_ad_landing_save_video_metabox( $post_id ) {
	if ( ! isset( $_POST['tc_ad_landing_video_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tc_ad_landing_video_nonce'] ) ), 'tc_ad_landing_video_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$video = isset( $_POST['tc_ad_landing_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['tc_ad_landing_video_url'] ) ) : '';
	if ( $video ) {
		update_post_meta( $post_id, '_tc_ad_landing_video_url', $video );
	} else {
		delete_post_meta( $post_id, '_tc_ad_landing_video_url' );
	}
	$hero = isset( $_POST['tc_ad_landing_hero_image'] ) ? esc_url_raw( wp_unslash( $_POST['tc_ad_landing_hero_image'] ) ) : '';
	if ( $hero ) {
		update_post_meta( $post_id, '_tc_ad_landing_hero_image', $hero );
	} else {
		delete_post_meta( $post_id, '_tc_ad_landing_hero_image' );
	}
}

/**
 * @return array<string, array<string, mixed>>
 */
function tc_ad_landing_definitions() {
	return array(
		'pantallas-notebook'              => array(
			'title'       => 'Pantallas Notebook',
			'meta'        => 'Cambia la pantalla de tu notebook en 30 minutos · Garantía escrita · HP, Lenovo, Dell, Asus, Acer · Las Condes, Santiago.',
			'badge'       => 'Instalación en 30 minutos',
			'headline'    => 'Cambio de Pantalla Notebook',
			'subheadline' => 'Garantía de 6 meses · Todas las marcas · Técnicos especializados',
			'cta'         => 'Reserva tu hora por WhatsApp',
			'type'        => 'pantallas',
			'hero_layout' => 'pantallas-promo',
			'intro'       => 'Encuentra pantallas compatibles para HP, Lenovo, Dell, Asus y Acer. Cotiza según marca, tamaño y modelo de tu equipo.',
			'faq_title'   => 'Preguntas frecuentes – Cambio de pantallas notebook',
			'faq'         => tc_ad_landing_faq_pantallas(),
		),
		'pantalla-para-notebook-market'   => array(
			'title'       => 'Pantalla para Notebook Market',
			'meta'        => 'Pantallas para notebook en Santiago con instalación profesional. Cotiza gratis · Garantía escrita · Las Condes.',
			'badge'       => 'Repuestos con garantía',
			'headline'    => 'Pantallas para Notebook en Santiago',
			'subheadline' => 'Stock por marca y modelo · Instalación rápida · Las Condes',
			'cta'         => 'Reserva tu hora por WhatsApp',
			'type'        => 'pantallas',
			'hero_layout' => 'pantallas-promo',
			'intro'       => 'Compara pantallas por marca, pulgadas y resolución. Atención especializada en Los Militares, Las Condes.',
			'faq_title'   => 'Preguntas frecuentes – Pantallas notebook',
			'faq'         => tc_ad_landing_faq_pantallas(),
		),
		'cambio-de-pantalla-notebook'     => array(
			'title'       => 'Cambio de Pantalla Notebook',
			'meta'        => 'Cambio de pantalla notebook en 30 minutos · Garantía escrita · Diagnóstico profesional · Las Condes, Santiago. Cotiza ahora.',
			'badge'       => 'Servicio express',
			'headline'    => 'Cambio de Pantalla Notebook en 30 Minutos',
			'subheadline' => 'Diagnóstico profesional · Repuestos verificados · Garantía escrita',
			'cta'         => 'Reserva tu hora por WhatsApp',
			'type'        => 'pantallas',
			'hero_layout' => 'pantallas-promo',
			'intro'       => 'Reemplazamos pantallas rotas o con fallas de imagen. Trabajamos con las principales marcas del mercado.',
			'faq_title'   => 'Preguntas frecuentes – Cambio de pantalla',
			'faq'         => tc_ad_landing_faq_pantallas(),
		),
		'bisagras-para-notebook'          => array(
			'title'       => 'Bisagras para Notebook',
			'meta'        => 'Reparación de bisagras para notebook en Las Condes · HP, Lenovo, Dell, Asus, Acer · Garantía escrita · Cotiza en 2 minutos.',
			'badge'       => 'Reparación profesional con garantía',
			'headline'    => 'Reparación de Bisagras para Notebook en Santiago',
			'subheadline' => 'Reparamos bisagras quebradas, tapas sueltas y carcasas dañadas',
			'cta'         => 'Cotizar reparación de bisagras',
			'type'        => 'bisagras',
			'intro'       => 'Evita daños mayores en la pantalla. Diagnóstico técnico, refuerzo de carcasa y garantía en cada reparación.',
			'faq_title'   => 'Preguntas frecuentes – Bisagras notebook',
			'faq'         => tc_ad_landing_faq_bisagras(),
			'benefits'    => tc_ad_landing_benefits_bisagras(),
		),
		'mantencion-notebook'             => array(
			'title'       => 'Mantención Notebook',
			'meta'        => 'Mantención preventiva de notebook en Santiago · Limpieza interna, pasta térmica y revisión profesional · Garantía escrita · Las Condes.',
			'badge'       => 'Mantención preventiva',
			'headline'    => 'Mantención Profesional de Notebook',
			'subheadline' => 'Limpieza interna · Pasta térmica · Revisión completa del equipo',
			'cta'         => 'Agendar mantención',
			'type'        => 'mantencion',
			'intro'       => 'Prolonga la vida útil de tu notebook con mantención preventiva. Ideal si tu equipo se calienta, hace ruido o va lento.',
			'faq_title'   => 'Preguntas frecuentes – Mantención notebook',
			'faq'         => tc_ad_landing_faq_mantencion(),
			'benefits'    => tc_ad_landing_benefits_mantencion(),
		),
		'reparacion-notebook'             => array(
			'video_url'   => content_url( 'uploads/2026/06/reparacion-notebook.mp4' ),
			'title'       => 'Reparación Notebook',
			'meta'        => 'Reparación notebook en Las Condes · Pantallas, SSD, teclado, bisagras, placa madre · Diagnóstico gratis · Garantía escrita. Cotiza hoy.',
			'badge'       => 'Servicio técnico especializado',
			'headline'    => 'Reparación de Notebook en Santiago',
			'subheadline' => 'Pantallas · SSD · Teclado · Bisagras · Placa madre',
			'cta'         => 'Cotizar reparación',
			'type'        => 'reparacion',
			'intro'       => 'Diagnóstico profesional y reparación con garantía escrita. Atención en Los Militares 5620, Oficina 1801, Las Condes.',
			'faq_title'   => 'Preguntas frecuentes – Reparación notebook',
			'faq'         => tc_ad_landing_faq_reparacion(),
			'benefits'    => tc_ad_landing_benefits_reparacion(),
		),
	);
}

function tc_ad_landing_faq_pantallas() {
	return array(
		array( 'q' => '¿Qué tipos de pantallas instalan?', 'a' => 'Pantallas LED de 14", 15.6", 16.0" y 16.1". Compatibles con HP, Lenovo, Dell, Acer y Asus.' ),
		array( 'q' => '¿Cuánto demora el cambio?', 'a' => 'Entre 15 y 30 minutos según el modelo del equipo.' ),
		array( 'q' => '¿Las pantallas son nuevas?', 'a' => 'Sí, utilizamos pantallas nuevas y verificadas para asegurar calidad y rendimiento.' ),
		array( 'q' => '¿Tienen garantía?', 'a' => 'Sí, todas las pantallas cuentan con garantía por defectos de fabricación.' ),
		array( 'q' => '¿Dónde están ubicados?', 'a' => 'Los Militares 5620, Oficina 1801, Las Condes, Santiago.' ),
	);
}

function tc_ad_landing_faq_bisagras() {
	return array(
		array( 'q' => '¿Qué marcas reparan?', 'a' => 'HP, Lenovo, Dell, Asus, Acer, Samsung, Huawei y notebooks gamer.' ),
		array( 'q' => '¿Por qué reparar a tiempo?', 'a' => 'Una bisagra dañada puede romper la pantalla o la carcasa si no se corrige pronto.' ),
		array( 'q' => '¿Incluye garantía?', 'a' => 'Sí, todas las reparaciones de bisagras incluyen garantía escrita.' ),
		array( 'q' => '¿Cuánto demora?', 'a' => 'Depende del daño; tras el diagnóstico te indicamos el plazo estimado.' ),
	);
}

function tc_ad_landing_faq_mantencion() {
	return array(
		array( 'q' => '¿Qué incluye la mantención?', 'a' => 'Limpieza interna, cambio de pasta térmica y revisión general del equipo.' ),
		array( 'q' => '¿Cada cuánto se recomienda?', 'a' => 'Idealmente una vez al año o si el notebook se calienta mucho o hace ruido.' ),
		array( 'q' => '¿Pierdo mis archivos?', 'a' => 'No, la mantención es física del hardware; no formateamos ni borramos datos.' ),
		array( 'q' => '¿Tienen garantía?', 'a' => 'Sí, garantía en el servicio realizado.' ),
	);
}

function tc_ad_landing_faq_reparacion() {
	return array(
		array( 'q' => '¿Qué reparan?', 'a' => 'Pantallas, SSD, teclados, bisagras, puertos de carga, placa madre y más.' ),
		array( 'q' => '¿Hacen diagnóstico?', 'a' => 'Sí, evaluación profesional antes de cualquier reparación.' ),
		array( 'q' => '¿Trabajan con empresas?', 'a' => 'Sí, atendemos particulares y empresas en Santiago.' ),
		array( 'q' => '¿Dónde están?', 'a' => 'Los Militares 5620, Oficina 1801, Las Condes.' ),
	);
}

function tc_ad_landing_benefits_bisagras() {
	return array(
		array( 'icon' => '🔧', 'title' => 'Reparación de bisagras', 'text' => 'Bisagras quebradas, sueltas o duras.' ),
		array( 'icon' => '🛠', 'title' => 'Refuerzo de carcasa', 'text' => 'Anclajes internos y zonas dañadas.' ),
		array( 'icon' => '⚡', 'title' => 'Diagnóstico técnico', 'text' => 'Evaluación antes de reparar.' ),
		array( 'icon' => '📌', 'title' => 'Ajuste de bisagras', 'text' => 'Reduce tensión y futuras roturas.' ),
	);
}

function tc_ad_landing_benefits_mantencion() {
	return array(
		array( 'icon' => '🌡', 'title' => 'Menos calor', 'text' => 'Mejor disipación y ventilación interna.' ),
		array( 'icon' => '🔇', 'title' => 'Menos ruido', 'text' => 'Ventiladores más limpios y eficientes.' ),
		array( 'icon' => '⚡', 'title' => 'Mejor rendimiento', 'text' => 'Pasta térmica nueva y componentes revisados.' ),
		array( 'icon' => '🛡', 'title' => 'Mayor duración', 'text' => 'Previene fallas por suciedad y sobrecalentamiento.' ),
	);
}

function tc_ad_landing_benefits_reparacion() {
	return array(
		array( 'icon' => '💻', 'title' => 'Pantallas', 'text' => 'Cambio express con garantía.' ),
		array( 'icon' => '💾', 'title' => 'SSD y almacenamiento', 'text' => 'Migración y mejora de velocidad.' ),
		array( 'icon' => '⌨', 'title' => 'Teclado y bisagras', 'text' => 'Repuestos según marca y modelo.' ),
		array( 'icon' => '🔌', 'title' => 'Placa y carga', 'text' => 'Diagnóstico de fallas eléctricas.' ),
	);
}

function tc_ad_landing_get_slug() {
	if ( ! is_page() ) {
		return '';
	}
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	return isset( tc_ad_landing_definitions()[ $slug ] ) ? $slug : '';
}

function tc_ad_landing_register_shortcode() {
	add_shortcode( 'tc_ad_landing', 'tc_ad_landing_shortcode' );
	add_shortcode( 'tc_ad_landing_form', 'tc_ad_landing_form_shortcode' );
	add_shortcode( 'tc_ad_landing_faq', 'tc_ad_landing_faq_shortcode' );
	add_shortcode( 'tc_ad_landing_benefits', 'tc_ad_landing_benefits_shortcode' );
}

function tc_ad_landing_resolve_slug( $atts = [] ) {
	$slug = ! empty( $atts['slug'] ) ? sanitize_title( $atts['slug'] ) : '';
	if ( ! $slug ) {
		$slug = tc_ad_landing_get_slug();
	}
	if ( ! $slug ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
	}
	return isset( tc_ad_landing_definitions()[ $slug ] ) ? $slug : '';
}

function tc_ad_landing_form_shortcode( $atts ) {
	$slug = tc_ad_landing_resolve_slug( (array) $atts );
	if ( ! $slug ) {
		return '';
	}
	$def = tc_ad_landing_definitions()[ $slug ];
	ob_start();
	?>
	<div class="tc-ad-landing__form-wrap" style="background:#fff;border-radius:18px;padding:28px;box-shadow:0 12px 40px rgba(15,23,42,.08)">
		<h2 style="text-align:left;margin-bottom:8px"><?php esc_html_e( 'Ingresa tus datos y modelo de equipo', 'techcomputer' ); ?></h2>
		<p style="margin:0 0 20px;color:#64748b"><?php esc_html_e( 'Déjanos tus datos y nuestro equipo te contactará de inmediato.', 'techcomputer' ); ?></p>
		<?php echo tc_ad_landing_contact_form( $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
	return ob_get_clean();
}

function tc_ad_landing_faq_shortcode( $atts ) {
	$slug = tc_ad_landing_resolve_slug( (array) $atts );
	if ( ! $slug ) {
		return '';
	}
	$def = tc_ad_landing_definitions()[ $slug ];
	if ( empty( $def['faq'] ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="tc-ad-landing__faq" style="max-width:860px;margin:0 auto">
		<h2 style="text-align:center;margin-bottom:28px"><?php echo esc_html( $def['faq_title'] ); ?></h2>
		<div class="tc-ad-landing__faq-list">
		<?php foreach ( $def['faq'] as $item ) : ?>
			<details style="border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;margin-bottom:10px">
				<summary style="font-weight:700;cursor:pointer;list-style:none"><?php echo esc_html( $item['q'] ); ?></summary>
				<p style="margin:10px 0 0;color:#475569"><?php echo esc_html( $item['a'] ); ?></p>
			</details>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function tc_ad_landing_benefits_shortcode( $atts ) {
	$slug = tc_ad_landing_resolve_slug( (array) $atts );
	if ( ! $slug ) {
		return '';
	}
	$def = tc_ad_landing_definitions()[ $slug ];
	if ( empty( $def['benefits'] ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="tc-ad-landing__grid" style="display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
	<?php foreach ( $def['benefits'] as $b ) : ?>
		<div style="background:#fff;border:1px solid #e8edf2;border-radius:16px;padding:22px;box-shadow:0 8px 24px rgba(15,23,42,.05)">
			<div style="font-size:1.8rem;margin-bottom:8px"><?php echo esc_html( $b['icon'] ); ?></div>
			<h3 style="margin:0 0 8px;font-size:1.05rem"><?php echo esc_html( $b['title'] ); ?></h3>
			<p style="margin:0;color:#64748b;font-size:.95rem"><?php echo esc_html( $b['text'] ); ?></p>
		</div>
	<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}


function tc_ad_landing_shortcode() {
	$slug = tc_ad_landing_get_slug();
	if ( ! $slug ) {
		$slug = get_post_field( 'post_name', get_the_ID() );
	}
	if ( ! isset( tc_ad_landing_definitions()[ $slug ] ) ) {
		return '';
	}
	return tc_ad_landing_render( $slug );
}

function tc_ad_landing_filter_content( $content ) {
	if ( ! is_singular( 'page' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$slug = tc_ad_landing_get_slug();
	if ( ! $slug ) {
		return $content;
	}
	return tc_ad_landing_render( $slug );
}

function tc_ad_landing_document_title( $parts ) {
	$slug = tc_ad_landing_get_slug();
	if ( ! $slug ) {
		return $parts;
	}
	$def = tc_ad_landing_definitions()[ $slug ];
	$parts['title'] = $def['title'] . ' | Techcomputer';
	return $parts;
}

function tc_ad_landing_meta_description() {
	$slug = tc_ad_landing_get_slug();
	if ( ! $slug ) {
		return;
	}
	$def = tc_ad_landing_definitions()[ $slug ];
	echo '<meta name="description" content="' . esc_attr( $def['meta'] ) . '" />' . "\n";
}

function tc_ad_landing_faq_schema() {
	$slug = tc_ad_landing_get_slug();
	if ( ! $slug ) {
		return;
	}
	$def  = tc_ad_landing_definitions()[ $slug ];
	$faqs = $def['faq'] ?? array();
	if ( empty( $faqs ) ) {
		return;
	}
	$entities = array();
	foreach ( $faqs as $item ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $item['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $item['a'],
			),
		);
	}
	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

function tc_ad_landing_canonical_tag() {
	$slug = tc_ad_landing_get_slug();
	if ( ! $slug ) {
		return;
	}
	$pantallas_variants = array( 'pantalla-para-notebook-market', 'cambio-de-pantalla-notebook' );
	if ( in_array( $slug, $pantallas_variants, true ) ) {
		$canonical = home_url( '/pantallas-notebook/' );
	} else {
		$canonical = get_permalink();
	}
	if ( $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
	}
}

function tc_ad_landing_hero_image_url() {
	if ( function_exists( 'tc_single_product_hero_banner_url' ) ) {
		return tc_single_product_hero_banner_url();
	}
	return content_url( 'uploads/2026/06/tc-product-hero-banner.png' );
}

/**
 * Banner promocional completo (la imagen que enviaste).
 */
function tc_ad_landing_pantallas_promo_banner_candidates() {
	return array(
		WP_CONTENT_DIR . '/uploads/2026/06/tc-landing-pantallas-promo.jpg',
		WP_CONTENT_DIR . '/mu-plugins/assets/tc-landing-pantallas-promo.jpg',
	);
}

function tc_ad_landing_pantallas_promo_banner_url() {
	foreach ( tc_ad_landing_pantallas_promo_banner_candidates() as $path ) {
		if ( file_exists( $path ) ) {
			$uploads = wp_get_upload_dir();
			if ( str_starts_with( $path, $uploads['basedir'] ) ) {
				return $uploads['baseurl'] . str_replace( $uploads['basedir'], '', $path );
			}
			return content_url( str_replace( WP_CONTENT_DIR, '', $path ) );
		}
	}
	return content_url( 'mu-plugins/assets/tc-landing-pantallas-promo.jpg' );
}

function tc_ad_landing_ensure_pantallas_promo_banner() {
	foreach ( tc_ad_landing_pantallas_promo_banner_candidates() as $path ) {
		if ( file_exists( $path ) ) {
			return true;
		}
	}
	$dir = WP_CONTENT_DIR . '/mu-plugins/assets';
	if ( ! wp_mkdir_p( $dir ) ) {
		return false;
	}
	$dest = trailingslashit( $dir ) . 'tc-landing-pantallas-promo.jpg';
	if ( file_exists( $dest ) ) {
		return true;
	}
	return false;
}

function tc_ad_landing_pantallas_hero_highlights( $def ) {
	if ( ! empty( $def['hero_highlights'] ) && is_array( $def['hero_highlights'] ) ) {
		return $def['hero_highlights'];
	}
	if ( empty( $def['subheadline'] ) ) {
		return array();
	}
	$parts = preg_split( '/\s*[·|]\s*/u', $def['subheadline'] );
	return array_values(
		array_filter(
			array_map(
				static function ( $part ) {
					return trim( $part );
				},
				$parts
			)
		)
	);
}

function tc_ad_landing_render_hero_pantallas_mobile( $def, $wa, $msg ) {
	$highlights = tc_ad_landing_pantallas_hero_highlights( $def );
	$cta        = ! empty( $def['hero_cta_mobile'] ) ? $def['hero_cta_mobile'] : __( 'Cotizar por WhatsApp', 'techcomputer' );
	$address    = defined( 'TC_ADDRESS' ) ? TC_ADDRESS : 'Los Militares 5620, Oficina 1801, Las Condes';
	ob_start();
	?>
	<div class="tc-ad-landing__hero-mobile">
		<div class="tc-ad-landing__hero-mobile-inner">
			<h1 class="tc-ad-landing__hero-mobile-title"><?php echo esc_html( $def['headline'] ); ?></h1>
			<?php if ( $highlights ) : ?>
			<ul class="tc-ad-landing__hero-pills">
				<?php foreach ( $highlights as $item ) : ?>
				<li><span class="tc-ad-landing__hero-pill-check" aria-hidden="true">✓</span><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			<a
				class="tc-ad-landing__hero-wa-btn"
				href="<?php echo esc_url( $wa . '?text=' . $msg ); ?>"
				target="_blank"
				rel="noopener noreferrer"
			>
				<span class="tc-ad-landing__hero-wa-btn-icon" aria-hidden="true">📲</span>
				<?php echo esc_html( $cta ); ?>
			</a>
			<div class="tc-ad-landing__hero-proof">
				<p class="tc-ad-landing__hero-proof-num">+1.000</p>
				<p class="tc-ad-landing__hero-proof-text"><?php esc_html_e( 'Clientes Han Confiado en TechComputer', 'techcomputer' ); ?></p>
				<p class="tc-ad-landing__hero-stars" aria-label="<?php esc_attr_e( '5 estrellas', 'techcomputer' ); ?>">★★★★★</p>
				<p class="tc-ad-landing__hero-address">
					<span class="tc-ad-landing__hero-address-icon" aria-hidden="true">📍</span>
					<?php echo esc_html( $address ); ?>
				</p>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function tc_ad_landing_render_hero_pantallas_promo( $def, $wa, $msg ) {
	tc_ad_landing_ensure_pantallas_promo_banner();
	$banner = tc_ad_landing_pantallas_promo_banner_url();
	ob_start();
	?>
	<section class="tc-ad-landing__hero tc-ad-landing__hero--pantallas">
		<div class="tc-ad-landing__hero-banner">
			<img
				class="tc-ad-landing__hero-banner-img"
				src="<?php echo esc_url( $banner ); ?>"
				alt="<?php echo esc_attr( $def['title'] ); ?>"
				width="1536"
				height="1024"
				loading="eager"
				decoding="async"
			>
			<a
				class="tc-ad-landing__hero-banner-wa"
				href="<?php echo esc_url( $wa . '?text=' . $msg ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php echo esc_attr( $def['cta'] ); ?>"
			></a>
		</div>
		<?php echo tc_ad_landing_render_hero_pantallas_mobile( $def, $wa, $msg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
	return ob_get_clean();
}

function tc_ad_landing_enqueue_assets() {
	if ( ! tc_ad_landing_get_slug() ) {
		return;
	}

	if ( function_exists( 'wc_enqueue_styles' ) ) {
		wc_enqueue_styles();
	}
	if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
		wp_enqueue_script( 'wc-add-to-cart' );
	}
	wp_enqueue_script( 'wc-cart-fragments' );
	if ( ! wp_style_is( 'jkit-elements-main', 'registered' ) ) {
		wp_register_style(
			'jkit-elements-main',
			plugins_url( 'jeg-elementor-kit/assets/css/elements/main.css' ),
			array(),
			defined( 'JEG_ELEMENTOR_KIT_VERSION' ) ? JEG_ELEMENTOR_KIT_VERSION : '1.0'
		);
	}
	if ( wp_style_is( 'jkit-elements-main', 'registered' ) ) {
		wp_enqueue_style( 'jkit-elements-main' );
	}
	if ( function_exists( 'tc_enqueue_directions_assets' ) ) {
		tc_enqueue_directions_assets();
	}

	$p = function_exists( 'tc_brand_palette' ) ? tc_brand_palette() : array(
		'primary'    => '#528A31',
		'primary_d'  => '#3d6a24',
		'blue'       => '#2563eb',
		'dark'       => '#1e293b',
		'body'       => '#334155',
		'tint'       => '#EEF4EA',
	);
	$css = '
.tc-ad-landing{color:' . $p['body'] . ';font-size:1.05rem;line-height:1.65;width:100%;max-width:100%}
.tc-ad-landing__hero{position:relative;padding:72px 20px 64px;background:linear-gradient(135deg,rgba(30,41,59,.88),rgba(82,138,49,.82)),url("' . esc_url( get_post_meta( get_queried_object_id(), '_tc_ad_landing_hero_image', true ) ?: tc_ad_landing_hero_image_url() ) . '") center/cover no-repeat;color:#fff;text-align:center}
.tc-ad-landing__hero-inner{max-width:920px;margin:0 auto}
.tc-ad-landing__badge{display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);padding:8px 16px;border-radius:999px;font-weight:700;font-size:.9rem;margin-bottom:18px}
.tc-ad-landing__hero h1{font-size:clamp(2rem,4vw,3rem);line-height:1.15;margin:0 0 14px;color:#fff}
.tc-ad-landing__sub{font-size:1.1rem;opacity:.95;margin-bottom:24px}
.tc-ad-landing__cta{display:inline-flex;align-items:center;gap:10px;background:#25D366;color:#fff!important;padding:14px 28px;border-radius:12px;font-weight:800;text-decoration:none!important;box-shadow:0 10px 30px rgba(0,0,0,.2)}
.tc-ad-landing__cta:hover{transform:translateY(-1px);color:#fff!important}
.tc-ad-landing__stats{display:flex;flex-wrap:wrap;justify-content:center;gap:20px 32px;margin-top:28px;font-size:.95rem}
.tc-ad-landing__hero--pantallas{padding:0;background:#000;color:#fff;text-align:left;overflow:hidden}
.tc-ad-landing__hero-banner{position:relative;display:block;max-width:1400px;margin:0 auto;line-height:0}
.tc-ad-landing__hero-banner-img{display:block;width:100%;height:auto;vertical-align:top}
.tc-ad-landing__hero-banner-wa{position:absolute;left:4.5%;bottom:28%;width:52%;height:11%;border-radius:999px;cursor:pointer}
.tc-ad-landing__hero-banner-wa:hover{background:rgba(255,255,255,.04)}
.tc-ad-landing__hero-mobile{display:none;background:#fff;padding:28px 20px 32px;color:' . $p['dark'] . ';text-align:center}
.tc-ad-landing__hero-mobile-inner{max-width:420px;margin:0 auto}
.tc-ad-landing__hero-mobile .tc-ad-landing__hero-mobile-title{margin:0 0 22px;font-size:clamp(1.85rem,7vw,2.35rem);line-height:1.12;font-weight:800;color:#111;text-align:center;letter-spacing:-.02em}
.tc-ad-landing__hero-pills{list-style:none;margin:0 0 24px;padding:0;display:grid;gap:12px}
.tc-ad-landing__hero-pills li{display:flex;align-items:center;justify-content:center;gap:10px;background:#fff;border-radius:999px;padding:14px 20px;font-size:1rem;font-weight:600;color:#111;box-shadow:0 6px 24px rgba(15,23,42,.08);border:1px solid rgba(15,23,42,.04)}
.tc-ad-landing__hero-pill-check{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:999px;background:' . $p['tint'] . ';color:' . $p['primary'] . ';font-size:.85rem;font-weight:800;flex-shrink:0}
.tc-ad-landing__hero-wa-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;margin:0 0 28px;padding:16px 20px;border-radius:14px;background:linear-gradient(135deg,#25D366 0%,' . $p['primary'] . ' 100%);color:#fff!important;font-size:1.05rem;font-weight:800;text-decoration:none!important;box-shadow:0 10px 28px rgba(37,211,102,.28);box-sizing:border-box}
.tc-ad-landing__hero-wa-btn:hover{color:#fff!important;transform:translateY(-1px)}
.tc-ad-landing__hero-wa-btn-icon{font-size:1.15rem;line-height:1}
.tc-ad-landing__hero-proof{text-align:center}
.tc-ad-landing__hero-proof-num{margin:0 0 6px;font-size:clamp(2.4rem,10vw,3rem);line-height:1;font-weight:800;color:' . $p['primary_d'] . '}
.tc-ad-landing__hero-proof-text{margin:0 0 10px;font-size:1rem;font-weight:600;color:#111;line-height:1.35}
.tc-ad-landing__hero-stars{margin:0 0 14px;font-size:1.35rem;line-height:1;letter-spacing:.12em;color:#f5b301}
.tc-ad-landing__hero-address{margin:0;font-size:.92rem;line-height:1.45;color:' . $p['body'] . ';display:flex;align-items:flex-start;justify-content:center;gap:6px;text-align:left;max-width:320px;margin-left:auto;margin-right:auto}
.tc-ad-landing__hero-address-icon{flex-shrink:0;line-height:1.35}
@media(max-width:767px){.tc-ad-landing__hero-mobile{display:block}}
.tc-ad-landing__section{max-width:1140px;margin:0 auto;padding:48px 20px;width:100%;box-sizing:border-box}
.tc-ad-landing__section--tint{background:' . $p['tint'] . ';width:100vw;max-width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);padding:48px max(20px,calc(50vw - 570px))}
.tc-ad-landing__section h2{color:' . $p['dark'] . ';font-size:clamp(1.5rem,3vw,2rem);margin:0 0 12px;text-align:center}
.tc-ad-landing__intro{text-align:center;max-width:760px;margin:0 auto 32px;font-size:1.05rem;line-height:1.6}
.tc-ad-landing__grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
.tc-ad-landing__card{background:#fff;border:1px solid #e8edf2;border-radius:16px;padding:22px;box-shadow:0 8px 24px rgba(15,23,42,.05)}
.tc-ad-landing__card-icon{font-size:1.8rem;margin-bottom:8px}
.tc-ad-landing__card h3{margin:0 0 8px;font-size:1.05rem;color:' . $p['dark'] . '}
.tc-ad-landing__products{width:100%;max-width:1140px;margin:0 auto}
.tc-ad-landing__products .tc-ad-landing__catalog{width:100%!important;max-width:100%!important;margin:0!important}
.tc-ad-landing__products .tc-ad-landing__catalog .woocommerce{width:100%!important;max-width:100%!important;margin:0!important}
.tc-ad-landing__products .tc-ad-landing__catalog .woocommerce ul.products.jkit-products{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:24px!important;list-style:none!important;margin:0!important;padding:0!important;width:100%!important}
.tc-ad-landing__products .tc-ad-landing__catalog .woocommerce ul.products.jkit-products::before,.tc-ad-landing__products .tc-ad-landing__catalog .woocommerce ul.products.jkit-products::after{display:none!important;content:none!important}
.tc-ad-landing__products .tc-ad-landing__product-item{list-style:none!important;margin:0!important;padding:0!important;width:100%!important;min-width:0!important;display:block!important}
.tc-ad-landing__products .tc-ad-landing__product-card{display:flex!important;flex-direction:column!important;height:100%!important;background:#fff!important;border-radius:16px!important;padding:16px!important;box-shadow:0 4px 16px rgba(15,23,42,.06)!important;box-sizing:border-box!important}
.tc-ad-landing__products .jkit-product{display:flex!important;flex-direction:column!important;flex:1 1 auto!important;text-decoration:none!important;color:inherit!important}
.tc-ad-landing__products .product-link{position:relative!important;display:flex!important;align-items:center!important;justify-content:center!important;background:#f8fafc!important;border-radius:12px!important;padding:20px 16px!important;margin:0 0 14px!important;min-height:170px!important;overflow:hidden!important}
.tc-ad-landing__products .product-link img{max-height:140px!important;width:auto!important;max-width:100%!important;object-fit:contain!important;margin:0 auto!important;display:block!important}
.tc-ad-landing__products .product-link .onsale{position:absolute!important;top:10px!important;left:10px!important;right:auto!important;bottom:auto!important;background:' . $p['primary'] . '!important;color:#fff!important;font-size:.72rem!important;font-weight:700!important;padding:6px 10px!important;border-radius:999px!important;text-transform:uppercase!important;letter-spacing:normal!important;width:auto!important;height:auto!important;min-width:0!important;min-height:0!important;line-height:1.2!important;margin:0!important;display:inline-flex!important;align-items:center!important;justify-content:center!important}
.tc-ad-landing__products .product-title{margin:0 0 8px!important;font-size:clamp(1rem,1.8vw,1.08rem)!important;line-height:1.4!important;font-weight:600!important;color:' . $p['dark'] . '!important;display:-webkit-box!important;-webkit-line-clamp:3!important;-webkit-box-orient:vertical!important;overflow:hidden!important}
.tc-ad-landing__products .product-categories{margin:0 0 6px!important;font-size:12px!important;line-height:1.35!important;font-weight:600!important;letter-spacing:.04em!important;text-transform:uppercase!important;color:' . ( $p['muted'] ?? '#64748b' ) . '!important}
.tc-ad-landing__products .price{font-size:1.05rem!important;color:' . $p['primary'] . '!important;font-weight:700!important;margin:0 0 4px!important}
.tc-ad-landing__products .product-categories,.tc-ad-landing__products .product-title,.tc-ad-landing__products .price{letter-spacing:normal!important;word-spacing:normal!important}
.tc-ad-landing__products .product-categories a{letter-spacing:normal!important;text-transform:none!important;text-decoration:none!important;color:inherit!important}
.tc-ad-landing__products .price,.tc-ad-landing__products .price *{word-spacing:normal!important}
.tc-ad-landing__products .tc-ad-landing__product-actions{display:flex!important;flex-direction:column!important;gap:8px!important;margin-top:auto!important;padding-top:12px!important;flex-shrink:0!important}
.tc-ad-landing__products .tc-ad-landing__product-actions .button,.tc-ad-landing__products .tc-ad-landing__product-actions .add_to_cart_button{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:100%!important;margin:0!important;padding:11px 14px!important;border-radius:10px!important;font-size:.88rem!important;font-weight:700!important;line-height:1.2!important;text-align:center!important;text-decoration:none!important;background:' . $p['primary'] . '!important;border:1px solid ' . $p['primary'] . '!important;color:#fff!important;box-sizing:border-box!important;float:none!important;position:static!important}
.tc-ad-landing__products .tc-ad-landing__product-actions .button:hover,.tc-ad-landing__products .tc-ad-landing__product-actions .add_to_cart_button:hover{background:' . $p['primary_d'] . '!important;border-color:' . $p['primary_d'] . '!important;color:#fff!important}
.tc-ad-landing__products .tc-ad-landing__product-actions .tc-wa-consult-btn{background:#25D366!important;border-color:#25D366!important;margin-top:0!important}
.tc-ad-landing__products .tc-ad-landing__product-actions .tc-wa-consult-btn:hover{background:#1ebe5b!important;border-color:#1ebe5b!important}
.tc-ad-landing__location{text-align:center;margin-top:28px;font-size:1rem;font-weight:600}
.tc-ad-landing__location a{color:' . $p['primary'] . '!important;text-decoration:none!important}
.tc-ad-landing__location a:hover{text-decoration:underline!important}
.tc-ad-landing__faq details{background:#fff;border:1px solid #e8edf2;border-radius:12px;padding:14px 18px;margin-bottom:10px}
.tc-ad-landing__faq summary{cursor:pointer;font-weight:700;color:' . $p['dark'] . '}
.tc-ad-landing__section--directions{padding:0;max-width:none}
.tc-ad-landing__section--directions .tc-directions-section{margin:0;width:100%;max-width:100%;box-sizing:border-box}
.tc-ad-landing__form-split{display:grid;gap:28px 32px;align-items:start;max-width:1140px;margin:0 auto}
@media(min-width:900px){.tc-ad-landing__form-split{grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr)}}
.tc-ad-landing__video-wrap{display:flex;justify-content:center;align-items:flex-start;background:#111;border-radius:18px;overflow:hidden;box-shadow:0 12px 40px rgba(15,23,42,.12);padding:12px}
.tc-ad-landing__video{display:block;width:auto;max-width:100%;max-height:min(72vh,640px);height:auto;object-fit:contain;background:#111;margin:0 auto}
.tc-ad-landing__form-wrap{max-width:none;margin:0;background:#fff;border-radius:18px;padding:28px;box-shadow:0 12px 40px rgba(15,23,42,.08)}
.tc-ad-landing__form-wrap h2{text-align:left;margin-bottom:8px}
.tc-ad-landing__form-lead{margin:0 0 20px;color:#64748b}
body.tc-ad-landing-page .site-header,body.tc-ad-landing-page header.site-header,body.tc-ad-landing-page .site-footer,body.tc-ad-landing-page footer.site-footer,body.tc-ad-landing-page .hfe-before-footer-wrap,body.tc-ad-landing-page .page-header{display:none!important}
body.tc-ad-landing-page{overflow-x:hidden}
body.tc-ad-landing-page .site-main,body.tc-ad-landing-page .site-main .page-content,body.tc-ad-landing-page .site-main .entry-content{max-width:none!important;width:100%!important;padding-left:0!important;padding-right:0!important}
body.tc-ad-landing-page .site-main .page-content,body.tc-ad-landing-page .site-main{padding-top:0}
body.tc-ad-landing-page--pantallas .tc-ad-landing__hero--pantallas{width:100vw;max-width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw)}
@media(max-width:1024px){.tc-ad-landing__products .tc-ad-landing__catalog .woocommerce ul.products.jkit-products{grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:20px!important}}
@media(max-width:767px){.tc-ad-landing__hero{padding:56px 16px 48px}.tc-ad-landing__section{padding:36px 16px}.tc-ad-landing__section--tint{padding:36px 16px}.tc-ad-landing__products .tc-ad-landing__catalog .woocommerce ul.products.jkit-products{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:16px!important}.tc-ad-landing__hero-banner-wa{left:5%;bottom:30%;width:90%;height:9%}.tc-ad-landing__form-split{gap:20px}}
@media(max-width:480px){.tc-ad-landing__products .tc-ad-landing__catalog .woocommerce ul.products.jkit-products{grid-template-columns:minmax(0,1fr)!important}}
';
	wp_register_style( 'tc-ad-landing', false, array( 'tc-brand-colors', 'woocommerce-general', 'jkit-elements-main' ), TC_AD_LANDING_VERSION );
	wp_enqueue_style( 'tc-ad-landing' );
	wp_add_inline_style( 'tc-ad-landing', $css );
}

function tc_ad_landing_render_form_video( $def = array() ) {
	$page_url = get_post_meta( get_the_ID(), '_tc_ad_landing_video_url', true );
	if ( $page_url ) {
		$url = $page_url;
	} elseif ( ! empty( $def['video_url'] ) ) {
		$url = $def['video_url'];
	} else {
		$url = TC_AD_LANDING_VIDEO_URL;
	}
	ob_start();
	?>
	<div class="tc-ad-landing__video-wrap">
		<video
			class="tc-ad-landing__video"
			controls
			playsinline
			preload="metadata"
			src="<?php echo esc_url( $url ); ?>"
		>
			<?php esc_html_e( 'Tu navegador no soporta la reproducción de video.', 'techcomputer' ); ?>
		</video>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Slugs de pantallas más buscadas (techcomputer.cl + catálogo histórico).
 *
 * @return string[]
 */
function tc_ad_landing_popular_pantallas_catalog() {
	return array(
		'hp-240-g7',
		'pantalla-notebook-hp-240-g7',
		'asus-tuf-gaming-f15-fx506hc-hn111w-2',
		'pantalla-notebook-15-6-full-hd-144hz-40pin',
		'pantalla-notebook-16-1-full-hd-30pin',
		'pantalla-para-notebook-hp-victus',
		'pantalla-para-notebook-lenovo',
		'lenovo-ideapad-320-15abr-2',
		'cambio-de-pantalla-notebook-hp',
		'cambio-de-pantalla-notebook-acer',
		'hp-pavilion-14-ab156la-2',
		'acer-aspire-5-a515-51g-59b5-hd',
		'hp-15-ef1018la',
		'lenovo-thinkpad-t490-hd-2',
	);
}

/**
 * @return int[]
 */
function tc_ad_landing_get_pantallas_category_ids() {
	$slugs   = function_exists( 'tc_get_pantallas_slugs' ) ? tc_get_pantallas_slugs() : array( 'pantallas-notebook' );
	$cat_ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$cat_ids[] = (int) $term->term_id;
		}
	}
	return $cat_ids;
}

/**
 * Solo productos reales de pantallas (no servicios virtuales ni $0 sin imagen).
 *
 * @param \WC_Product|null $product Producto.
 */
function tc_ad_landing_is_valid_pantalla_product( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return false;
	}

	$product_id = $product->get_id();
	if ( get_post_meta( $product_id, '_tc_featured_service', true ) ) {
		return false;
	}

	if ( ! $product->get_image_id() ) {
		return false;
	}

	if ( function_exists( 'tc_catalog_resolve_product_tipo' ) && 'productos' !== tc_catalog_resolve_product_tipo( $product_id ) ) {
		return false;
	}

	$price = (float) $product->get_price();
	if ( $price <= 0 ) {
		return false;
	}

	return true;
}

/**
 * @return \WC_Product[]
 */
function tc_ad_landing_query_pantalla_products( $limit = 8, $exclude = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$limit   = max( 1, (int) $limit );
	$exclude = array_filter( array_map( 'intval', (array) $exclude ) );
	$out     = array();

	if ( function_exists( 'tc_catalog_build_query_args' ) ) {
		$query = new WP_Query(
			tc_catalog_build_query_args(
				array(
					'tipo'     => 'productos',
					'repuesto' => 'pantallas-notebook',
					'marca'    => '',
					'tamano'   => '',
					'resolucion' => '',
					'servicio' => '',
					'q'        => '',
				),
				1,
				$limit + count( $exclude ) + 4
			)
		);
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! tc_ad_landing_is_valid_pantalla_product( $product ) ) {
					continue;
				}
				if ( in_array( $product->get_id(), $exclude, true ) ) {
					continue;
				}
				$out[] = $product;
				if ( count( $out ) >= $limit ) {
					break;
				}
			}
			wp_reset_postdata();
		}
	}

	if ( count( $out ) >= $limit ) {
		return $out;
	}

	$cat_ids = tc_ad_landing_get_pantallas_category_ids();
	$args    = array(
		'status'  => 'publish',
		'limit'   => $limit + count( $exclude ) + 8,
		'orderby' => 'date',
		'order'   => 'DESC',
		'exclude' => array_merge(
			$exclude,
			function_exists( 'tc_get_featured_service_product_ids' ) ? tc_get_featured_service_product_ids() : array()
		),
	);
	if ( $cat_ids ) {
		$args['category'] = $cat_ids;
	}

	foreach ( wc_get_products( $args ) as $product ) {
		if ( ! tc_ad_landing_is_valid_pantalla_product( $product ) ) {
			continue;
		}
		$id = $product->get_id();
		if ( in_array( $id, $exclude, true ) ) {
			continue;
		}
		$exclude[] = $id;
		$out[]     = $product;
		if ( count( $out ) >= $limit ) {
			break;
		}
	}

	return $out;
}

/**
 * @return \WC_Product[]
 */
function tc_ad_landing_get_popular_pantallas_products( $limit = 8 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$limit    = max( 1, (int) $limit );
	$seen     = array();
	$products = array();
	$cat_ids  = tc_ad_landing_get_pantallas_category_ids();

	foreach ( tc_ad_landing_popular_pantallas_catalog() as $slug ) {
		if ( count( $products ) >= $limit ) {
			break;
		}

		$found = wc_get_products(
			array(
				'slug'   => $slug,
				'status' => 'publish',
				'limit'  => 1,
			)
		);

		if ( empty( $found ) && $cat_ids ) {
			$found = wc_get_products(
				array(
					's'        => str_replace( '-', ' ', $slug ),
					'status'   => 'publish',
					'limit'    => 3,
					'category' => $cat_ids,
				)
			);
		}

		if ( empty( $found ) ) {
			continue;
		}

		foreach ( $found as $product ) {
			$id = $product->get_id();
			if ( isset( $seen[ $id ] ) || ! tc_ad_landing_is_valid_pantalla_product( $product ) ) {
				continue;
			}
			$seen[ $id ] = true;
			$products[]  = $product;
			if ( count( $products ) >= $limit ) {
				break 2;
			}
		}
	}

	if ( count( $products ) < $limit ) {
		foreach ( tc_ad_landing_query_pantalla_products( $limit - count( $products ), array_keys( $seen ) ) as $product ) {
			$id = $product->get_id();
			if ( isset( $seen[ $id ] ) ) {
				continue;
			}
			$seen[ $id ] = true;
			$products[]  = $product;
		}
	}

	return $products;
}

/**
 * @param \WC_Product $product Producto.
 */
function tc_ad_landing_render_product_card( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$category_label = function_exists( 'tc_catalog_product_card_category' )
		? tc_catalog_product_card_category( $product->get_id() )
		: '';
	?>
	<li <?php wc_product_class( 'jkit-product-block tc-ad-landing__product-item', $product ); ?>>
		<div class="tc-ad-landing__product-card">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="jkit-product">
				<div class="product-link">
					<?php if ( $product->is_on_sale() ) : ?>
						<span class="onsale text"><?php esc_html_e( 'Oferta', 'techcomputer' ); ?></span>
					<?php endif; ?>
					<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'wp-post-image product-image' ) ); ?>
				</div>
				<?php if ( $category_label ) : ?>
					<div class="product-categories"><object><span><?php echo esc_html( $category_label ); ?></span></object></div>
				<?php endif; ?>
				<h3 class="product-title"><?php echo esc_html( $product->get_name() ); ?></h3>
				<?php if ( $price_html = $product->get_price_html() ) : ?>
					<span class="price"><?php echo wp_kses_post( $price_html ); ?></span>
				<?php endif; ?>
			</a>
			<div class="tc-ad-landing__product-actions">
				<?php
				$button_classes = array_filter(
					array(
						'button',
						'product_type_' . $product->get_type(),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
					)
				);
				$button_attrs = array(
					'data-product_id'  => $product->get_id(),
					'data-product_sku' => $product->get_sku(),
					'aria-label'       => $product->add_to_cart_description(),
					'rel'              => 'nofollow',
				);
				echo apply_filters(
					'woocommerce_loop_add_to_cart_link',
					sprintf(
						'<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
						esc_url( $product->add_to_cart_url() ),
						esc_attr( implode( ' ', $button_classes ) ),
						wc_implode_html_attributes( $button_attrs ),
						esc_html( $product->add_to_cart_text() )
					),
					$product,
					array(
						'class'      => implode( ' ', $button_classes ),
						'attributes' => $button_attrs,
					)
				);
				if ( function_exists( 'tc_woocommerce_wa_consult_button' ) ) {
					echo tc_woocommerce_wa_consult_button( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		</div>
	</li>
	<?php
}

function tc_ad_landing_render_products_grid() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return '';
	}

	$products = tc_ad_landing_get_popular_pantallas_products( 8 );
	if ( ! $products ) {
		return '<p class="tc-ad-landing__intro">' . esc_html__( 'Próximamente más modelos. Cotiza por WhatsApp con tu marca y modelo.', 'techcomputer' ) . '</p>';
	}

	ob_start();
	echo '<div class="jeg-elementor-kit jkit-product-grid tc-ad-landing__catalog">';
	echo '<div class="woocommerce tc-catalog-woocommerce">';
	echo '<ul class="products jkit-products jkit-align-left tc-catalog-grid">';
	foreach ( $products as $product ) {
		tc_ad_landing_render_product_card( $product );
	}
	echo '</ul></div></div>';

	return '<div class="tc-ad-landing__products">' . ob_get_clean() . '</div>';
}

function tc_ad_landing_contact_form( $slug ) {
	$def     = tc_ad_landing_definitions()[ $slug ];
	$action  = admin_url( 'admin-post.php' );
	$notice  = '';
	if ( isset( $_GET['tc_landing'] ) && 'sent' === $_GET['tc_landing'] ) {
		$notice = '<div class="tc-contact-form__notice tc-contact-form__notice--ok">' . esc_html__( 'Mensaje enviado. Te contactaremos pronto.', 'techcomputer' ) . '</div>';
	} elseif ( isset( $_GET['tc_landing'] ) && 'error' === $_GET['tc_landing'] ) {
		$notice = '<div class="tc-contact-form__notice tc-contact-form__notice--error">' . esc_html__( 'Revisa los campos e inténtalo de nuevo.', 'techcomputer' ) . '</div>';
	}
	ob_start();
	echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	<form class="tc-contact-form" method="post" action="<?php echo esc_url( $action ); ?>">
		<input type="hidden" name="action" value="tc_ad_landing_form">
		<input type="hidden" name="tc_landing_slug" value="<?php echo esc_attr( $slug ); ?>">
		<?php wp_nonce_field( 'tc_ad_landing_form', 'tc_ad_landing_nonce' ); ?>
		<div class="tc-contact-form__row tc-contact-form__row--2">
			<div>
				<label for="tc_landing_name"><?php esc_html_e( 'Nombre', 'techcomputer' ); ?> *</label>
				<input id="tc_landing_name" type="text" name="tc_landing_name" required>
			</div>
			<div>
				<label for="tc_landing_email"><?php esc_html_e( 'Correo electrónico', 'techcomputer' ); ?> *</label>
				<input id="tc_landing_email" type="email" name="tc_landing_email" required>
			</div>
		</div>
		<div class="tc-contact-form__row tc-contact-form__row--2">
			<div>
				<label for="tc_landing_phone"><?php esc_html_e( 'Teléfono', 'techcomputer' ); ?></label>
				<input id="tc_landing_phone" type="tel" name="tc_landing_phone">
			</div>
			<div>
				<label for="tc_landing_model"><?php esc_html_e( 'Modelo de equipo', 'techcomputer' ); ?></label>
				<input id="tc_landing_model" type="text" name="tc_landing_model" placeholder="Ej: HP 240 G7">
			</div>
		</div>
		<div>
			<span class="tc-contact-form__label"><?php esc_html_e( '¿Cómo desea ser contactado?', 'techcomputer' ); ?> *</span>
			<div class="tc-contact-form__choices">
				<label><input type="radio" name="tc_landing_method" value="Whatsapp" required> WhatsApp</label>
				<label><input type="radio" name="tc_landing_method" value="Email"> Email</label>
				<label><input type="radio" name="tc_landing_method" value="Llamado"> <?php esc_html_e( 'Llamado', 'techcomputer' ); ?></label>
			</div>
		</div>
		<div>
			<label for="tc_landing_message"><?php esc_html_e( 'Comentario o mensaje', 'techcomputer' ); ?></label>
			<textarea id="tc_landing_message" name="tc_landing_message"></textarea>
		</div>
		<button type="submit" class="button tc-contact-form__submit"><?php esc_html_e( 'Enviar', 'techcomputer' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}

function tc_ad_landing_render( $slug ) {
	$def = tc_ad_landing_definitions()[ $slug ];
	$wa  = defined( 'TC_WHATSAPP' ) ? TC_WHATSAPP : 'https://wa.me/56932194619';
	$msg = rawurlencode( 'Hola, quiero cotizar: ' . $def['title'] );
	ob_start();
	?>
	<div class="tc-ad-landing">
		<?php
		if ( ! empty( $def['hero_layout'] ) && 'pantallas-promo' === $def['hero_layout'] ) {
			echo tc_ad_landing_render_hero_pantallas_promo( $def, $wa, $msg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			?>
		<section class="tc-ad-landing__hero">
			<div class="tc-ad-landing__hero-inner">
				<span class="tc-ad-landing__badge"><?php echo esc_html( $def['badge'] ); ?></span>
				<h1><?php echo esc_html( $def['headline'] ); ?></h1>
				<p class="tc-ad-landing__sub"><?php echo esc_html( $def['subheadline'] ); ?></p>
				<a class="tc-ad-landing__cta" href="<?php echo esc_url( $wa . '?text=' . $msg ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $def['cta'] ); ?></a>
				<div class="tc-ad-landing__stats">
					<span>+1.000 clientes</span>
					<span>⭐ 9.5 en Google</span>
					<span>📍 <?php echo esc_html( defined( 'TC_ADDRESS' ) ? TC_ADDRESS : 'Las Condes, Santiago' ); ?></span>
				</div>
			</div>
		</section>
			<?php
		}
		?>

		<section class="tc-ad-landing__section">
			<div class="tc-ad-landing__form-split">
				<?php echo tc_ad_landing_render_form_video( $def ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="tc-ad-landing__form-wrap">
					<h2><?php esc_html_e( 'Ingresa tus datos y modelo de equipo', 'techcomputer' ); ?></h2>
					<p class="tc-ad-landing__form-lead"><?php esc_html_e( 'Déjanos tus datos y nuestro equipo te contactará de inmediato.', 'techcomputer' ); ?></p>
					<?php echo tc_ad_landing_contact_form( $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</section>

		<?php if ( 'pantallas' === $def['type'] ) : ?>
		<section class="tc-ad-landing__section tc-ad-landing__section--tint">
			<h2><?php esc_html_e( 'Las pantallas más buscadas', 'techcomputer' ); ?></h2>
			<p class="tc-ad-landing__intro"><?php echo esc_html( $def['intro'] ); ?></p>
			<?php echo tc_ad_landing_render_products_grid(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p class="tc-ad-landing__location">
				<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? add_query_arg( array( 'tc_tipo' => 'productos', 'tc_repuesto' => 'pantallas-notebook' ), wc_get_page_permalink( 'shop' ) ) : home_url( '/shop/' ) ); ?>">
					<?php esc_html_e( 'Ver catálogo completo de pantallas', 'techcomputer' ); ?> →
				</a>
			</p>
		</section>
		<?php else : ?>
		<section class="tc-ad-landing__section tc-ad-landing__section--tint">
			<h2><?php echo esc_html( $def['title'] ); ?></h2>
			<p class="tc-ad-landing__intro"><?php echo esc_html( $def['intro'] ); ?></p>
			<?php if ( ! empty( $def['benefits'] ) ) : ?>
			<div class="tc-ad-landing__grid">
				<?php foreach ( $def['benefits'] as $card ) : ?>
				<div class="tc-ad-landing__card">
					<div class="tc-ad-landing__card-icon"><?php echo esc_html( $card['icon'] ); ?></div>
					<h3><?php echo esc_html( $card['title'] ); ?></h3>
					<p><?php echo esc_html( $card['text'] ); ?></p>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</section>
		<?php endif; ?>

		<section class="tc-ad-landing__section">
			<h2><?php echo esc_html( $def['faq_title'] ); ?></h2>
			<div class="tc-ad-landing__faq">
				<?php foreach ( $def['faq'] as $item ) : ?>
				<details>
					<summary><?php echo esc_html( $item['q'] ); ?></summary>
					<p><?php echo esc_html( $item['a'] ); ?></p>
				</details>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="tc-ad-landing__section tc-ad-landing__section--tint" style="text-align:center">
			<h2><?php esc_html_e( '¿Listo para cotizar?', 'techcomputer' ); ?></h2>
			<p class="tc-ad-landing__intro"><?php esc_html_e( 'Atención especializada, diagnóstico profesional y garantía escrita.', 'techcomputer' ); ?></p>
			<a class="tc-ad-landing__cta" href="<?php echo esc_url( $wa . '?text=' . $msg ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $def['cta'] ); ?></a>
		</section>

		<?php if ( function_exists( 'tc_render_directions_section' ) ) : ?>
		<section class="tc-ad-landing__section tc-ad-landing__section--directions">
			<?php tc_render_directions_section( true ); ?>
		</section>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

function tc_ad_landing_maybe_create_pages() {
	if ( get_option( 'tc_ad_landing_version' ) === TC_AD_LANDING_VERSION ) {
		return;
	}
	foreach ( tc_ad_landing_definitions() as $slug => $def ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			wp_insert_post(
				array(
					'post_title'        => $def['title'],
					'post_name'         => $slug,
					'post_type'         => 'page',
					'post_status'       => 'publish',
					'post_content'      => '[tc_ad_landing]',
					'page_template'     => 'elementor_canvas',
					'comment_status'    => 'closed',
					'ping_status'       => 'closed',
				)
			);
		} else {
			wp_update_post(
				array(
					'ID'             => $page->ID,
					'post_content'   => '[tc_ad_landing]',
					'page_template'  => 'elementor_canvas',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
		}
	}
	update_option( 'tc_ad_landing_version', TC_AD_LANDING_VERSION );
}

add_filter( 'hello_elementor_page_title', 'tc_ad_landing_hide_page_title' );
add_filter( 'hello_elementor_header_footer_display', 'tc_ad_landing_hide_theme_chrome' );
add_filter( 'hfe_header_enabled', 'tc_ad_landing_hide_theme_chrome' );
add_filter( 'hfe_footer_enabled', 'tc_ad_landing_hide_theme_chrome' );
add_filter( 'hfe_before_footer_enabled', 'tc_ad_landing_hide_theme_chrome' );
add_filter( 'body_class', 'tc_ad_landing_body_class' );

function tc_ad_landing_hide_page_title( $show ) {
	return tc_ad_landing_get_slug() ? false : $show;
}

/**
 * @param bool $show Mostrar header/footer del tema o HFE.
 */
function tc_ad_landing_hide_theme_chrome( $show ) {
	return tc_ad_landing_get_slug() ? false : $show;
}

function tc_ad_landing_body_class( $classes ) {
	$slug = tc_ad_landing_get_slug();
	if ( $slug ) {
		$classes[] = 'tc-ad-landing-page';
		$def = tc_ad_landing_definitions()[ $slug ];
		if ( ! empty( $def['hero_layout'] ) && 'pantallas-promo' === $def['hero_layout'] ) {
			$classes[] = 'tc-ad-landing-page--pantallas';
		}
	}
	return $classes;
}

add_action( 'admin_post_tc_ad_landing_form', 'tc_ad_landing_handle_form' );
add_action( 'admin_post_nopriv_tc_ad_landing_form', 'tc_ad_landing_handle_form' );

function tc_ad_landing_handle_form() {
	$slug = isset( $_POST['tc_landing_slug'] ) ? sanitize_title( wp_unslash( $_POST['tc_landing_slug'] ) ) : '';
	$url  = $slug ? home_url( '/' . $slug . '/' ) : home_url( '/' );
	if ( ! isset( $_POST['tc_ad_landing_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tc_ad_landing_nonce'] ) ), 'tc_ad_landing_form' ) ) {
		wp_safe_redirect( add_query_arg( 'tc_landing', 'error', $url ) );
		exit;
	}
	$name    = isset( $_POST['tc_landing_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tc_landing_name'] ) ) : '';
	$email   = isset( $_POST['tc_landing_email'] ) ? sanitize_email( wp_unslash( $_POST['tc_landing_email'] ) ) : '';
	$phone   = isset( $_POST['tc_landing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['tc_landing_phone'] ) ) : '';
	$model   = isset( $_POST['tc_landing_model'] ) ? sanitize_text_field( wp_unslash( $_POST['tc_landing_model'] ) ) : '';
	$method  = isset( $_POST['tc_landing_method'] ) ? sanitize_text_field( wp_unslash( $_POST['tc_landing_method'] ) ) : '';
	$message = isset( $_POST['tc_landing_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tc_landing_message'] ) ) : '';
	if ( '' === $name || '' === $email || '' === $method ) {
		wp_safe_redirect( add_query_arg( 'tc_landing', 'error', $url ) );
		exit;
	}
	$title = isset( tc_ad_landing_definitions()[ $slug ] ) ? tc_ad_landing_definitions()[ $slug ]['title'] : 'Landing';
	$body  = "Landing: {$title}\nNombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\nModelo: {$model}\nContacto: {$method}\n\n{$message}\n";
	$to    = defined( 'TC_EMAIL' ) ? TC_EMAIL : get_option( 'admin_email' );
	$sent  = wp_mail( $to, 'Cotización landing - ' . $title . ' - ' . $name, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	wp_safe_redirect( add_query_arg( 'tc_landing', $sent ? 'sent' : 'error', $url ) );
	exit;
}

/**
 * Permite recrear landings desde código (p. ej. panel admin).
 */
function tc_ad_landing_reset_pages() {
	delete_option( 'tc_ad_landing_version' );
	tc_ad_landing_maybe_create_pages();
}
