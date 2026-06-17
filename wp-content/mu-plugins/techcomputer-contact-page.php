<?php
/**
 * Plugin Name: Techcomputer - Página Contáctanos
 * Description: Página de contacto completa (como techcomputer.cl) con diseño actual del sitio.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TC_CONTACT_PAGE_VERSION', '2' );

add_action( 'init', 'tc_contact_page_register_shortcode' );
add_action( 'init', 'tc_contact_page_maybe_rebuild', 25 );
add_filter( 'the_content', 'tc_contact_page_filter_content', 5 );
add_filter( 'hello_elementor_page_title', 'tc_contact_page_hide_title' );
add_action( 'wp_enqueue_scripts', 'tc_contact_page_enqueue_assets', 103 );
add_filter( 'elementor/frontend/builder_content_data', 'tc_contact_page_disable_elementor_builder', 1, 2 );

function tc_contact_page_register_shortcode() {
	add_shortcode( 'tc_contact_page', 'tc_contact_page_render' );
}

function tc_contact_page_is_active() {
	if ( ! is_page() ) {
		return false;
	}
	return 'contactanos' === get_post_field( 'post_name', get_queried_object_id() );
}

function tc_contact_page_hide_title( $show ) {
	return tc_contact_page_is_active() ? false : $show;
}

function tc_contact_page_disable_elementor_builder( $data, $post_id ) {
	if ( tc_get_contact_page_id() === (int) $post_id ) {
		return array();
	}
	return $data;
}

function tc_contact_page_get_id() {
	return function_exists( 'tc_get_contact_page_id' ) ? tc_get_contact_page_id() : 0;
}

function tc_contact_page_maybe_rebuild() {
	if ( get_option( 'tc_contact_page_version' ) === TC_CONTACT_PAGE_VERSION ) {
		return;
	}
	tc_contact_page_force_rebuild();
}

function tc_contact_page_force_rebuild() {
	$page = get_page_by_path( 'contactanos' );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Contáctanos',
				'post_name'    => 'contactanos',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '[tc_contact_page]',
			)
		);
	} else {
		$page_id = (int) $page->ID;
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => '[tc_contact_page]',
			)
		);
	}

	if ( ! $page_id || is_wp_error( $page_id ) ) {
		return false;
	}

	delete_post_meta( $page_id, '_elementor_data' );
	delete_post_meta( $page_id, '_elementor_edit_mode' );
	delete_post_meta( $page_id, '_elementor_template_type' );
	delete_post_meta( $page_id, '_elementor_css' );
	update_post_meta( $page_id, '_wp_page_template', 'default' );

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	update_option( 'tc_contact_page_version', TC_CONTACT_PAGE_VERSION, false );
	flush_rewrite_rules( false );
	return true;
}

function tc_contact_page_filter_content( $content ) {
	if ( ! tc_contact_page_is_active() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	return tc_contact_page_render();
}

function tc_contact_page_hero_url() {
	if ( function_exists( 'tc_single_product_hero_banner_url' ) ) {
		return tc_single_product_hero_banner_url();
	}
	return content_url( 'uploads/2026/06/tc-product-hero-banner.png' );
}

function tc_contact_page_render() {
	$phone   = defined( 'TC_PHONE' ) ? TC_PHONE : '+56 9 3219 4619';
	$email   = defined( 'TC_EMAIL' ) ? TC_EMAIL : 'ventas@techcomputer.cl';
	$address = defined( 'TC_ADDRESS' ) ? TC_ADDRESS : 'Los Militares 5620, Oficina 1801, Las Condes';
	$hours   = defined( 'TC_HOURS' ) ? TC_HOURS : "Lunes - Viernes 10:00 am - 17:30 hrs\nSábado 11:00 a 15:00 hrs";
	$wa      = defined( 'TC_WHATSAPP' ) ? TC_WHATSAPP : 'https://wa.me/56932194619';

	$form = function_exists( 'tc_contact_form_panel_shortcode' )
		? tc_contact_form_panel_shortcode()
		: ( function_exists( 'tc_contact_form_shortcode' ) ? tc_contact_form_shortcode() : '' );

	$wa_card = function_exists( 'tc_contact_whatsapp_card_shortcode' )
		? tc_contact_whatsapp_card_shortcode()
		: '';

	ob_start();
	?>
	<div class="tc-contact-page">
		<section class="tc-contact-page__hero" style="background-image:url('<?php echo esc_url( tc_contact_page_hero_url() ); ?>')">
			<div class="tc-contact-page__hero-inner">
				<h1><?php esc_html_e( 'Contáctanos', 'techcomputer' ); ?></h1>
			</div>
		</section>

		<section class="tc-contact-page__body">
			<nav class="tc-contact-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'techcomputer' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'techcomputer' ); ?></a>
				<span aria-hidden="true"> / </span>
				<span><?php esc_html_e( 'Contáctanos', 'techcomputer' ); ?></span>
			</nav>

			<div class="tc-contact-page__grid">
				<aside class="tc-contact-page__sidebar">
					<div class="tc-contact-page__card">
						<h4><?php esc_html_e( 'Dirección', 'techcomputer' ); ?></h4>
						<p><?php echo esc_html( $address ); ?></p>
					</div>
					<div class="tc-contact-page__card">
						<h4><?php esc_html_e( 'Celular', 'techcomputer' ); ?></h4>
						<p>
							<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><br>
							<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
						</p>
					</div>
					<div class="tc-contact-page__card">
						<h4><?php esc_html_e( 'Horario de atención', 'techcomputer' ); ?></h4>
						<p><?php echo nl2br( esc_html( $hours ) ); ?></p>
					</div>
					<?php if ( $wa_card ) : ?>
						<?php echo $wa_card; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<div class="tc-contact-wa-card">
							<div class="tc-contact-wa-card__body">
								<p class="tc-contact-wa-card__label">WhatsApp</p>
								<p class="tc-contact-wa-card__phone"><?php echo esc_html( $phone ); ?></p>
								<a class="tc-contact-wa-card__link" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Escríbenos', 'techcomputer' ); ?></a>
							</div>
						</div>
					<?php endif; ?>
				</aside>

				<div class="tc-contact-page__main">
					<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</section>

		<section class="tc-contact-page__banner">
			<h2><?php esc_html_e( 'Contáctanos por WhatsApp', 'techcomputer' ); ?></h2>
			<p><?php esc_html_e( 'Respuesta rápida para cotizaciones y consultas técnicas.', 'techcomputer' ); ?></p>
			<a class="tc-contact-page__wa-btn" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Escríbenos por WhatsApp', 'techcomputer' ); ?></a>
		</section>

		<?php if ( function_exists( 'tc_render_directions_section' ) ) : ?>
		<section class="tc-contact-page__directions">
			<?php tc_render_directions_section(); ?>
		</section>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

function tc_contact_page_enqueue_assets() {
	if ( ! tc_contact_page_is_active() ) {
		return;
	}

	$p = function_exists( 'tc_brand_palette' ) ? tc_brand_palette() : array(
		'primary'   => '#528A31',
		'primary_d' => '#3d6a24',
		'dark'      => '#1e293b',
		'body'      => '#334155',
		'tint'      => '#EEF4EA',
	);

	$css = '
.tc-contact-page{color:' . $p['body'] . ';line-height:1.65}
.tc-contact-page__hero{background:linear-gradient(135deg,rgba(30,41,59,.86),rgba(82,138,49,.78)) center/cover no-repeat;padding:72px 20px 64px;text-align:center;color:#fff;border-radius:0 0 24px 24px;margin-bottom:32px}
.tc-contact-page__hero h1{margin:0;font-size:clamp(2rem,4vw,2.8rem);color:#fff}
.tc-contact-page__body{max-width:1140px;margin:0 auto;padding:0 20px 48px}
.tc-contact-breadcrumb{text-align:center;margin:0 0 28px;color:#64748b;font-size:.95rem}
.tc-contact-breadcrumb a{color:' . $p['primary'] . ';font-weight:600;text-decoration:none}
.tc-contact-page__grid{display:grid;grid-template-columns:minmax(280px,38%) 1fr;gap:28px;align-items:start}
.tc-contact-page__card{border:1px solid ' . $p['primary'] . ';border-radius:20px;padding:22px 28px;background:#fff;margin-bottom:18px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.tc-contact-page__card h4{margin:0 0 10px;color:' . $p['primary'] . ';font-size:1.05rem}
.tc-contact-page__card p{margin:0;white-space:pre-line}
.tc-contact-page__card a{color:' . $p['dark'] . ';font-weight:600;text-decoration:none}
.tc-contact-page__card a:hover{color:' . $p['primary'] . '}
.tc-contact-page__main .tc-contact-form-panel{background:#fff;border:1px solid ' . $p['primary'] . ';border-radius:20px;padding:28px 32px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.tc-contact-page__banner{max-width:1140px;margin:0 auto 40px;padding:36px 28px;border-radius:20px;background:' . $p['tint'] . ';text-align:center;border:1px solid ' . $p['primary'] . '}
.tc-contact-page__banner h2{margin:0 0 8px;color:' . $p['dark'] . '}
.tc-contact-page__banner p{margin:0 0 18px}
.tc-contact-page__wa-btn{display:inline-flex;background:#25D366;color:#fff!important;padding:14px 26px;border-radius:12px;font-weight:800;text-decoration:none!important}
.tc-contact-page__directions{max-width:1140px;margin:0 auto;padding:0 20px 48px}
@media(max-width:900px){.tc-contact-page__grid{grid-template-columns:1fr}}
';

	wp_register_style( 'tc-contact-page-full', false, array( 'tc-brand-colors' ), TC_CONTACT_PAGE_VERSION );
	wp_enqueue_style( 'tc-contact-page-full' );
	wp_add_inline_style( 'tc-contact-page-full', $css );
}
