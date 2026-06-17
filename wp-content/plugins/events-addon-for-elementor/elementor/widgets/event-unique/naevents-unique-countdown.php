<?php
/*
 * Elementor Events Addon for Elementor Unique Countdown Widget
 * Author & Copyright: NicheAddon
 * Security Fixed Version
 */

namespace Elementor;

if (!isset(get_option( 'eafe_unqw_settings' )['naeafe_unique_countdown'])) { // enable & disable

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Event_Elementor_Addon_Unique_Countdown extends Widget_Base{

	/**
	 * Retrieve the widget name.
	*/
	public function get_name(){
		return 'naevents_unique_countdown';
	}

	/**
	 * Retrieve the widget title.
	*/
	public function get_title(){
		return esc_html__( 'Countdown', 'events-addon-for-elementor' );
	}

	/**
	 * Retrieve the widget icon.
	*/
	public function get_icon() {
		return 'eicon-countdown';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	*/
	public function get_categories() {
		return ['naevents-unique-category'];
	}

	/**
	 * Register Events Addon for Elementor Unique Countdown widget controls.
	 * Adds different input fields to allow the user to change and customize the widget settings.
	*/
	protected function register_controls(){

		$this->start_controls_section(
			'countdown_date',
			[
				'label' => esc_html__( 'Settings', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'count_type',
			[
				'label' => __( 'Countdown Type', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'static' => esc_html__( 'Static Date', 'events-addon-for-elementor' ),
					'fake'    => esc_html__('Fake Date', 'events-addon-for-elementor'),
				],
				'default' => 'static',
				'description' => esc_html__( 'Select your countdown date type.', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'count_date_static',
			[
				'label' => esc_html__( 'Date & Time', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::DATE_TIME,
				'picker_options' => [
					'dateFormat' => 'm/d/Y G:i:S',
					'enableTime' => 'true',
					'enableSeconds' => 'true',
				],
				'placeholder' => esc_html__( 'mm/dd/yyyy hh:mm:ss', 'events-addon-for-elementor' ),
				'label_block' => true,
				'condition' => [
					'count_type' => 'static',
				],
			]
		);
		$this->add_control(
			'fake_date',
			[
				'label' => esc_html__( 'Fake Date', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( '3', 'events-addon-for-elementor' ),
				'description' => esc_html__( 'Enter your fake day count here. Ex: 2 or 3(in days)', 'events-addon-for-elementor' ),
				'condition' => [
					'count_type' => 'fake',
				],
			]
		);

		$this->add_responsive_control(
			'section_max_width',
			[
				'label' => esc_html__( 'Width', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1500,
						'step' => 2,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .naeep-countdown-wrap' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'countdown_format',
			[
				'label' => esc_html__( 'Countdown Format', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'dHMS', 'events-addon-for-elementor' ),
				'description' => __( '<b>For Countdown Format Reference : <a href="http://keith-wood.name/countdown.html" target="_blank">Click Here</a></b>.', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'timezone',
			[
				'label' => esc_html__( 'Timezone', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( '+6', 'events-addon-for-elementor' ),
				'description' => __( 'Leave empty if you want to track user timezone automatically. Reference : <a href="http://keith-wood.name/countdown.html#zones" target="_blank">Click Here</a>', 'events-addon-for-elementor' )
			]
		);			
		$this->add_control(
			'need_separator',
			[
				'label' => esc_html__( 'Need Separator?', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'events-addon-for-elementor' ),
				'label_off' => esc_html__( 'No', 'events-addon-for-elementor' ),
				'return_value' => 'true',
			]
		);
		$this->end_controls_section();// end: Section

		$this->start_controls_section(
			'countdown_labels',
			[
				'label' => esc_html__( 'Countdown Labels', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'plural_labels',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => '<div class="elementor-control-raw-html elementor-panel-alert elementor-panel-alert-warning"><b>Plural Labels</b></div>',
			]
		);

		$this->add_control(
			'label_years',
			[
				'label' => esc_html__( 'Years Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'years', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_months',
			[
				'label' => esc_html__( 'Months Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'months', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_weeks',
			[
				'label' => esc_html__( 'Weeks Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'weeks', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_days',
			[
				'label' => esc_html__( 'Days Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'days', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_hours',
			[
				'label' => esc_html__( 'Hours Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'hours', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_minutes',
			[
				'label' => esc_html__( 'Minutes Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'minutes', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_seconds',
			[
				'label' => esc_html__( 'Seconds Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'seconds', 'events-addon-for-elementor' ),
			]
		);

		$this->add_control(
			'singular_label',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => '<div class="elementor-control-raw-html elementor-panel-alert elementor-panel-alert-warning"><b>Singular Labels</b></div>',
			]
		);

		$this->add_control(
			'label_year',
			[
				'label' => esc_html__( 'Year Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'year', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_month',
			[
				'label' => esc_html__( 'Month Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'month', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_week',
			[
				'label' => esc_html__( 'Week Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'week', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_day',
			[
				'label' => esc_html__( 'Day Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'day', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_hour',
			[
				'label' => esc_html__( 'Hour Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'hour', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_minute',
			[
				'label' => esc_html__( 'Minute Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'minute', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'label_second',
			[
				'label' => esc_html__( 'Second Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'second', 'events-addon-for-elementor' ),
			]
		);
		$this->end_controls_section();// end: Section

		// Value
		$this->start_controls_section(
			'section_value_style',
			[
				'label' => esc_html__( 'Countdown Value', 'events-addon-for-elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_responsive_control(
			'min_width',
			[
				'label' => esc_html__( 'Width', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 50,
						'max' => 1000,
						'step' => 1,
					],
				],
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .naeep-countdown-wrap .countdown_section' => 'min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'value_typography',
				'selector' => '{{WRAPPER}} .naeep-countdown-wrap .countdown_section .countdown_amount',
			]
		);
		$this->add_control(
			'value_color',
			[
				'label' => esc_html__( 'Value Color', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .naeep-countdown-wrap .countdown_section .countdown_amount' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'value_bg_color',
			[
				'label' => esc_html__( 'Value Background Color', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .naeep-countdown-wrap .countdown_section' => 'background: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'value_box_border',
				'label' => esc_html__( 'Border', 'events-addon-for-elementor' ),
				'selector' => '{{WRAPPER}} .naeep-countdown-wrap .countdown_section',
			]
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'value_box_shadow',
				'label' => esc_html__( 'Image Box Shadow', 'events-addon-for-elementor' ),
				'selector' => '{{WRAPPER}} .naeep-countdown-wrap .countdown_section',
			]
		);
		$this->add_control(
			'value_border_radius',
			[
				'label' => __( 'Border Radius', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .naeep-countdown-wrap .countdown_section' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->end_controls_section();// end: Section

		// Separator
		$this->start_controls_section(
			'section_value_sep_style',
			[
				'label' => esc_html__( 'Countdown Separator', 'events-addon-for-elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'value_sep_typography',
				'selector' => '{{WRAPPER}} .countdown_section:after',
			]
		);
		$this->add_control(
			'value_sep_color',
			[
				'label' => esc_html__( 'Separator Color', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .countdown_section:after' => 'color: {{VALUE}};',
				],
			]
		);
		$this->end_controls_section();// end: Section

		// Title
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Countdown Title', 'events-addon-for-elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .naeep-countdown-wrap .countdown_section',
			]
		);
		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Color', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .naeep-countdown-wrap .countdown_section' => 'color: {{VALUE}};',
				],
			]
		);
		$this->end_controls_section();// end: Section

	}

	/**
	 * Sanitize and validate user input
	 */
	private function sanitize_input($value, $type = 'text') {
		switch ($type) {
			case 'text':
				return sanitize_text_field($value);
			case 'html':
				return wp_kses_post($value);
			case 'number':
				return intval($value);
			case 'date':
				// Special handling for dates - validate format
				return sanitize_text_field($value);
			case 'format':
				// Special handling for countdown format - only allow specific characters
				$cleaned = sanitize_text_field($value);
				// Only allow specific countdown format characters: Y, O, W, D, H, M, S, d, h, m, s
				$cleaned = preg_replace('/[^YOWDHMSdhms]/', '', $cleaned);
				return $cleaned;
			case 'timezone':
				// Special handling for timezone - validate format
				$cleaned = sanitize_text_field($value);
				// Only allow +/- followed by numbers for timezone
				if (preg_match('/^[+-]?\d{1,2}(\.\d{1,2})?$/', $cleaned)) {
					return $cleaned;
				}
				return '';
			case 'label':
				// CRITICAL: Extra strict sanitization for labels to prevent XSS in data attributes
				$cleaned = sanitize_text_field($value);
				$cleaned = strip_tags($cleaned);
				// Remove any characters that could break out of HTML attributes or execute JavaScript
				$cleaned = preg_replace('/[<>"\'`&=\(\)\[\]{}\\\\\/]/', '', $cleaned);
				// Remove JavaScript keywords and dangerous patterns
				$cleaned = preg_replace('/\b(script|javascript|eval|function|alert|prompt|confirm|onload|onerror|onclick)\b/i', '', $cleaned);
				// Only allow alphanumeric characters, spaces, and basic punctuation
				$cleaned = preg_replace('/[^a-zA-Z0-9\s\-_.,!?:]/', '', $cleaned);
				// Limit length to prevent buffer overflow attacks
				$cleaned = substr($cleaned, 0, 50);
				return trim($cleaned);
			default:
				return sanitize_text_field($value);
		}
	}

	/**
	 * Additional security: JSON encode labels for safe data attribute output
	 */
	private function safe_json_encode($value) {
		// First sanitize, then JSON encode for extra safety
		$sanitized = $this->sanitize_input($value, 'label');
		return wp_json_encode($sanitized, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}

	/**
	 * Render Countdown widget output on the frontend.
	 * Written in PHP and used to generate the final HTML.
	*/
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		// Sanitize all inputs
		$count_type = !empty($settings['count_type']) && in_array($settings['count_type'], ['static', 'fake']) 
			? $settings['count_type'] : 'static';
		$timezone = !empty($settings['timezone']) ? $this->sanitize_input($settings['timezone'], 'timezone') : '';
		$count_date_static = !empty($settings['count_date_static']) ? $this->sanitize_input($settings['count_date_static'], 'date') : '';
		$fake_date = !empty($settings['fake_date']) ? $this->sanitize_input($settings['fake_date'], 'number') : '';
		$countdown_format = !empty($settings['countdown_format']) ? $this->sanitize_input($settings['countdown_format'], 'format') : 'dHMS';
		$need_separator = !empty($settings['need_separator']) ? $settings['need_separator'] : '';

		// Sanitize Labels Plural
		$label_years = !empty($settings['label_years']) ? $this->sanitize_input($settings['label_years'], 'label') : '';
		$label_months = !empty($settings['label_months']) ? $this->sanitize_input($settings['label_months'], 'label') : '';
		$label_weeks = !empty($settings['label_weeks']) ? $this->sanitize_input($settings['label_weeks'], 'label') : '';
		$label_days = !empty($settings['label_days']) ? $this->sanitize_input($settings['label_days'], 'label') : '';
		$label_hours = !empty($settings['label_hours']) ? $this->sanitize_input($settings['label_hours'], 'label') : '';
		$label_minutes = !empty($settings['label_minutes']) ? $this->sanitize_input($settings['label_minutes'], 'label') : '';
		$label_seconds = !empty($settings['label_seconds']) ? $this->sanitize_input($settings['label_seconds'], 'label') : '';

		// Set defaults for plural labels
		$label_years = $label_years ? $label_years : esc_html__('Years','events-addon-for-elementor');
		$label_months = $label_months ? $label_months : esc_html__('Months','events-addon-for-elementor');
		$label_weeks = $label_weeks ? $label_weeks : esc_html__('Weeks','events-addon-for-elementor');
		$label_days = $label_days ? $label_days : esc_html__('Days','events-addon-for-elementor');
		$label_hours = $label_hours ? $label_hours : esc_html__('Hours','events-addon-for-elementor');
		$label_minutes = $label_minutes ? $label_minutes : esc_html__('Minutes','events-addon-for-elementor');
		$label_seconds = $label_seconds ? $label_seconds : esc_html__('Seconds','events-addon-for-elementor');

		// Sanitize Labels Singular
		$label_year = !empty($settings['label_year']) ? $this->sanitize_input($settings['label_year'], 'label') : '';
		$label_month = !empty($settings['label_month']) ? $this->sanitize_input($settings['label_month'], 'label') : '';
		$label_week = !empty($settings['label_week']) ? $this->sanitize_input($settings['label_week'], 'label') : '';
		$label_day = !empty($settings['label_day']) ? $this->sanitize_input($settings['label_day'], 'label') : '';
		$label_hour = !empty($settings['label_hour']) ? $this->sanitize_input($settings['label_hour'], 'label') : '';
		$label_minute = !empty($settings['label_minute']) ? $this->sanitize_input($settings['label_minute'], 'label') : '';
		$label_second = !empty($settings['label_second']) ? $this->sanitize_input($settings['label_second'], 'label') : '';

		// Set defaults for singular labels
		$label_year = $label_year ? $label_year : esc_html__('Year','events-addon-for-elementor');
		$label_month = $label_month ? $label_month : esc_html__('Month','events-addon-for-elementor');
		$label_week = $label_week ? $label_week : esc_html__('Week','events-addon-for-elementor');
		$label_day = $label_day ? $label_day : esc_html__('Day','events-addon-for-elementor');
		$label_hour = $label_hour ? $label_hour : esc_html__('Hour','events-addon-for-elementor');
		$label_minute = $label_minute ? $label_minute : esc_html__('Minute','events-addon-for-elementor');
		$label_second = $label_second ? $label_second : esc_html__('Second','events-addon-for-elementor');

		// Determine count date
		if ($count_type === 'fake') {
			$count_date_actual = $fake_date;
		} else {
			$count_date_actual = $count_date_static;
		}

		// Determine separator class
		$sep_class = ($need_separator) ? ' need-separator' : '';

		// Use wp_kses to allow only safe HTML attributes
		$allowed_html = array(
			'div' => array(
				'class' => array(),
				'data-date' => array(),
				'data-years' => array(),
				'data-months' => array(),
				'data-weeks' => array(),
				'data-days' => array(),
				'data-hours' => array(),
				'data-minutes' => array(),
				'data-seconds' => array(),
				'data-year' => array(),
				'data-month' => array(),
				'data-week' => array(),
				'data-day' => array(),
				'data-hour' => array(),
				'data-minute' => array(),
				'data-second' => array(),
				'data-format' => array(),
				'data-timezone' => array(),
			),
		);

		// Build output with enhanced security - using JSON encoding for label data attributes
		$output = '<div class="naeep-countdown-wrap'.esc_attr($sep_class).'">
		          <div class="naeep-countdown '.esc_attr($count_type).'" 
		               data-date="'.esc_attr($count_date_actual).'" 
		               data-years='.esc_attr($this->safe_json_encode($label_years)).' 
		               data-months='.esc_attr($this->safe_json_encode($label_months)).' 
		               data-weeks='.esc_attr($this->safe_json_encode($label_weeks)).' 
		               data-days='.esc_attr($this->safe_json_encode($label_days)).' 
		               data-hours='.esc_attr($this->safe_json_encode($label_hours)).' 
		               data-minutes='.esc_attr($this->safe_json_encode($label_minutes)).' 
		               data-seconds='.esc_attr($this->safe_json_encode($label_seconds)).' 
		               data-year='.esc_attr($this->safe_json_encode($label_year)).' 
		               data-month='.esc_attr($this->safe_json_encode($label_month)).' 
		               data-week='.esc_attr($this->safe_json_encode($label_week)).' 
		               data-day='.esc_attr($this->safe_json_encode($label_day)).' 
		               data-hour='.esc_attr($this->safe_json_encode($label_hour)).' 
		               data-minute='.esc_attr($this->safe_json_encode($label_minute)).' 
		               data-second='.esc_attr($this->safe_json_encode($label_second)).' 
		               data-format="'.esc_attr($countdown_format).'" 
		               data-timezone="'.esc_attr($timezone).'">
		            <div class="clearfix"></div>
		          </div>
		        </div>';

		echo wp_kses($output, $allowed_html);
	}

}
Plugin::instance()->widgets_manager->register_widget_type( new Event_Elementor_Addon_Unique_Countdown() );

} // enable & disable