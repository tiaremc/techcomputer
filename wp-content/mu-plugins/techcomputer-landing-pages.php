<?php
/**
 * Plugin Name: Techcomputer - Landing pages publicitarias
 * Description: Landings SEO (pantallas, bisagras, mantención, reparación) con el diseño actual del sitio.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TC_AD_LANDING_VERSION', '1' );

add_action( 'init', 'tc_ad_landing_register_shortcode' );
add_action( 'init', 'tc_ad_landing_maybe_create_pages', 20 );
add_action( 'wp_enqueue_scripts', 'tc_ad_landing_enqueue_assets' );
add_filter( 'the_content', 'tc_ad_landing_filter_content', 5 );
add_filter( 'document_title_parts', 'tc_ad_landing_document_title' );
add_action( 'wp_head', 'tc_ad_landing_meta_description', 1 );

/**
 * @return array<string, array<string, mixed>>
 */
function tc_ad_landing_definitions() {
	return array(
		'pantallas-notebook'              => array(
			'title'       => 'Pantallas Notebook',
			'meta'        => 'Cambio de pantalla notebook en Santiago con garantía de 6 meses. Todas las marcas, instalación en 30 minutos.',
			'badge'       => 'Instalación en 30 minutos',
			'headline'    => 'Cambio de Pantalla Notebook',
			'subheadline' => 'Garantía de 6 meses · Todas las marcas · Técnicos especializados',
			'cta'         => 'Cotizar por WhatsApp',
			'type'        => 'pantallas',
			'intro'       => 'Encuentra pantallas compatibles para HP, Lenovo, Dell, Asus y Acer. Cotiza según marca, tamaño y modelo de tu equipo.',
			'faq_title'   => 'Preguntas frecuentes – Cambio de pantallas notebook',
			'faq'         => tc_ad_landing_faq_pantallas(),
		),
		'pantalla-para-notebook-market'   => array(
			'title'       => 'Pantalla para Notebook Market',
			'meta'        => 'Pantallas para notebook en Santiago. Repuestos originales y compatibles con instalación profesional y garantía.',
			'badge'       => 'Repuestos con garantía',
			'headline'    => 'Pantallas para Notebook en Santiago',
			'subheadline' => 'Stock por marca y modelo · Instalación rápida · Las Condes',
			'cta'         => 'Cotizar pantalla',
			'type'        => 'pantallas',
			'intro'       => 'Compara pantallas por marca, pulgadas y resolución. Atención especializada en Los Militares, Las Condes.',
			'faq_title'   => 'Preguntas frecuentes – Pantallas notebook',
			'faq'         => tc_ad_landing_faq_pantallas(),
		),
		'cambio-de-pantalla-notebook'     => array(
			'title'       => 'Cambio de Pantalla Notebook',
			'meta'        => 'Cambio de pantalla notebook en 30 minutos. Servicio técnico en Las Condes con garantía escrita.',
			'badge'       => 'Servicio express',
			'headline'    => 'Cambio de Pantalla Notebook en 30 Minutos',
			'subheadline' => 'Diagnóstico profesional · Repuestos verificados · Garantía escrita',
			'cta'         => 'Cotizar cambio de pantalla',
			'type'        => 'pantallas',
			'intro'       => 'Reemplazamos pantallas rotas o con fallas de imagen. Trabajamos con las principales marcas del mercado.',
			'faq_title'   => 'Preguntas frecuentes – Cambio de pantalla',
			'faq'         => tc_ad_landing_faq_pantallas(),
		),
		'bisagras-para-notebook'          => array(
			'title'       => 'Bisagras para Notebook',
			'meta'        => 'Reparación de bisagras notebook en Las Condes. HP, Lenovo, Dell, Asus y Acer con garantía escrita.',
			'badge'       => 'Reparación profesional con garantía',
			'headline'    => '¿La bisagra de tu notebook está rota?',
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
			'meta'        => 'Mantención preventiva de notebook en Santiago. Limpieza interna, pasta térmica y revisión profesional.',
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
			'title'       => 'Reparación Notebook',
			'meta'        => 'Reparación notebook en Las Condes y Santiago. Pantallas, SSD, teclado, bisagras y placa madre con garantía.',
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

function tc_ad_landing_hero_image_url() {
	if ( function_exists( 'tc_single_product_hero_banner_url' ) ) {
		return tc_single_product_hero_banner_url();
	}
	return content_url( 'uploads/2026/06/tc-product-hero-banner.png' );
}

function tc_ad_landing_enqueue_assets() {
	if ( ! tc_ad_landing_get_slug() ) {
		return;
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
.tc-ad-landing{color:' . $p['body'] . ';font-size:1.05rem;line-height:1.65}
.tc-ad-landing__hero{position:relative;padding:72px 20px 64px;background:linear-gradient(135deg,rgba(30,41,59,.88),rgba(82,138,49,.82)),url("' . esc_url( tc_ad_landing_hero_image_url() ) . '") center/cover no-repeat;color:#fff;text-align:center}
.tc-ad-landing__hero-inner{max-width:920px;margin:0 auto}
.tc-ad-landing__badge{display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);padding:8px 16px;border-radius:999px;font-weight:700;font-size:.9rem;margin-bottom:18px}
.tc-ad-landing__hero h1{font-size:clamp(2rem,4vw,3rem);line-height:1.15;margin:0 0 14px;color:#fff}
.tc-ad-landing__sub{font-size:1.1rem;opacity:.95;margin-bottom:24px}
.tc-ad-landing__cta{display:inline-flex;align-items:center;gap:10px;background:#25D366;color:#fff!important;padding:14px 28px;border-radius:12px;font-weight:800;text-decoration:none!important;box-shadow:0 10px 30px rgba(0,0,0,.2)}
.tc-ad-landing__cta:hover{transform:translateY(-1px);color:#fff!important}
.tc-ad-landing__stats{display:flex;flex-wrap:wrap;justify-content:center;gap:20px 32px;margin-top:28px;font-size:.95rem}
.tc-ad-landing__section{max-width:1140px;margin:0 auto;padding:48px 20px}
.tc-ad-landing__section--tint{background:' . $p['tint'] . '}
.tc-ad-landing__section h2{color:' . $p['dark'] . ';font-size:clamp(1.5rem,3vw,2rem);margin:0 0 12px;text-align:center}
.tc-ad-landing__intro{text-align:center;max-width:760px;margin:0 auto 28px}
.tc-ad-landing__grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
.tc-ad-landing__card{background:#fff;border:1px solid #e8edf2;border-radius:16px;padding:22px;box-shadow:0 8px 24px rgba(15,23,42,.05)}
.tc-ad-landing__card-icon{font-size:1.8rem;margin-bottom:8px}
.tc-ad-landing__card h3{margin:0 0 8px;font-size:1.05rem;color:' . $p['dark'] . '}
.tc-ad-landing__products .products{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;list-style:none;margin:0;padding:0}
.tc-ad-landing__products .products li{margin:0;width:100%}
.tc-ad-landing__products .woocommerce-loop-product__title{font-size:1rem}
.tc-ad-landing__products .price{color:' . $p['primary'] . '!important;font-weight:700}
.tc-ad-landing__faq details{background:#fff;border:1px solid #e8edf2;border-radius:12px;padding:14px 18px;margin-bottom:10px}
.tc-ad-landing__faq summary{cursor:pointer;font-weight:700;color:' . $p['dark'] . '}
.tc-ad-landing__form-wrap{max-width:720px;margin:0 auto;background:#fff;border-radius:18px;padding:28px;box-shadow:0 12px 40px rgba(15,23,42,.08)}
.tc-ad-landing__form-wrap h2{text-align:left;margin-bottom:8px}
.tc-ad-landing__form-lead{margin:0 0 20px;color:#64748b}
.tc-ad-landing__location{text-align:center;margin-top:12px;font-size:.95rem}
@media(max-width:767px){.tc-ad-landing__hero{padding:56px 16px 48px}.tc-ad-landing__section{padding:36px 16px}}
';
	wp_register_style( 'tc-ad-landing', false, array( 'tc-brand-colors' ), TC_AD_LANDING_VERSION );
	wp_enqueue_style( 'tc-ad-landing' );
	wp_add_inline_style( 'tc-ad-landing', $css );
}

function tc_ad_landing_render_products_grid() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return '';
	}
	$slugs = function_exists( 'tc_get_pantallas_slugs' ) ? tc_get_pantallas_slugs() : array( 'pantallas-notebook' );
	$cat_ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$cat_ids[] = (int) $term->term_id;
		}
	}
	$args = array(
		'status' => 'publish',
		'limit'  => 8,
		'orderby'=> 'date',
		'order'  => 'DESC',
	);
	if ( $cat_ids ) {
		$args['category'] = $cat_ids;
	} else {
		$args['s'] = 'pantalla notebook';
	}
	$products = wc_get_products( $args );
	if ( ! $products ) {
		return '<p class="tc-ad-landing__intro">' . esc_html__( 'Próximamente más modelos. Cotiza por WhatsApp con tu marca y modelo.', 'techcomputer' ) . '</p>';
	}
	ob_start();
	echo '<ul class="products columns-4">';
	foreach ( $products as $product ) {
		$post_object = get_post( $product->get_id() );
		if ( ! $post_object ) {
			continue;
		}
		setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wc_get_template_part( 'content', 'product' );
	}
	echo '</ul>';
	wp_reset_postdata();
	return '<div class="tc-ad-landing__products woocommerce">' . ob_get_clean() . '</div>';
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

		<section class="tc-ad-landing__section">
			<div class="tc-ad-landing__form-wrap">
				<h2><?php esc_html_e( 'Ingresa tus datos y modelo de equipo', 'techcomputer' ); ?></h2>
				<p class="tc-ad-landing__form-lead"><?php esc_html_e( 'Déjanos tus datos y nuestro equipo te contactará de inmediato.', 'techcomputer' ); ?></p>
				<?php echo tc_ad_landing_contact_form( $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</section>

		<?php if ( 'pantallas' === $def['type'] ) : ?>
		<section class="tc-ad-landing__section tc-ad-landing__section--tint">
			<h2><?php esc_html_e( 'Pantallas para notebook', 'techcomputer' ); ?></h2>
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
		<section class="tc-ad-landing__section">
			<?php tc_render_directions_section(); ?>
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
					'post_title'   => $def['title'],
					'post_name'    => $slug,
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_content' => '[tc_ad_landing]',
				)
			);
		} else {
			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => '[tc_ad_landing]',
				)
			);
		}
	}
	update_option( 'tc_ad_landing_version', TC_AD_LANDING_VERSION );
}

add_filter( 'hello_elementor_page_title', 'tc_ad_landing_hide_page_title' );
add_filter( 'body_class', 'tc_ad_landing_body_class' );

function tc_ad_landing_hide_page_title( $show ) {
	return tc_ad_landing_get_slug() ? false : $show;
}

function tc_ad_landing_body_class( $classes ) {
	if ( tc_ad_landing_get_slug() ) {
		$classes[] = 'tc-ad-landing-page';
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
