<?php
/*
 * Elementor Events Addon for Elementor Typewriter Widget
 * Author & Copyright: NicheAddon
 */

namespace Elementor;

if (!isset(get_option( 'eafe_bw_settings' )['naeafe_typewriter'])) { // enable & disable

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Event_Elementor_Addon_Typewriter extends Widget_Base{

	/**
	 * Retrieve the widget name.
	*/
	public function get_name(){
		return 'naevents_basic_typewriter';
	}

	/**
	 * Retrieve the widget title.
	*/
	public function get_title(){
		return esc_html__( 'Typewriter', 'events-addon-for-elementor' );
	}

	/**
	 * Retrieve the widget icon.
	*/
	public function get_icon() {
		return 'eicon-animation-text';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	*/
	public function get_categories() {
		return ['naevents-basic-category'];
	}

	/**
	 * Register Events Addon for Elementor Typewriter widget controls.
	 * Adds different input fields to allow the user to change and customize the widget settings.
	*/
	protected function register_controls(){

		$this->start_controls_section(
			'section_typewriter',
			[
				'label' => __( ' Options', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'before_title',
			[
				'label' => esc_html__( 'Before Animation Title', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'This is an ', 'events-addon-for-elementor' ),
				'placeholder' => esc_html__( 'Type title here', 'events-addon-for-elementor' ),
				'label_block' => true,
			]
		);
		$repeater = new Repeater();

		$repeater->add_control(
			'animation_text',
			[
				'label' => esc_html__( 'Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => esc_html__( 'Amazing...', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'animation_groups',
			[
				'label' => esc_html__( 'Animation Text', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::REPEATER,
				'default' => [
					[
						'animation_text' => esc_html__( 'Amazing...', 'events-addon-for-elementor' ),
					],

				],
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ animation_text }}}',
			]
		);
		$this->add_control(
			'cursorChar',
			[
				'label' => esc_html__( 'Animation Text Cursor', 'events-addon-for-elementor' ),
				'default' => esc_html__( '|', 'events-addon-for-elementor' ),
				'placeholder' => esc_html__( 'Enter Cursor here', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);
		$this->add_control(
			'after_title',
			[
				'label' => esc_html__( 'After Animation Title', 'events-addon-for-elementor' ),
				'default' => esc_html__( ' Heading', 'events-addon-for-elementor' ),
				'placeholder' => esc_html__( 'Type title here', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
			]
		);
		$this->add_responsive_control(
			'content_alignment',
			[
				'label' => esc_html__( 'Content Alignment', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'events-addon-for-elementor' ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'events-addon-for-elementor' ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'events-addon-for-elementor' ),
						'icon' => 'fa fa-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .naeep-typewriter' => 'text-align: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'typeSpeed',
			[
				'label' => esc_html__( 'Type Speed', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 1500,
				'step' => 1,
				'default' => 100,
				'description' => esc_html__( 'Set the typing speed.', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'backSpeed',
			[
				'label' => esc_html__( 'Back Speed', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 1500,
				'step' => 1,
				'default' => 100,
				'description' => esc_html__( 'Set the back speed.', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'startDelay',
			[
				'label' => esc_html__( 'Start Delay', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 1500,
				'step' => 1,
				'default' => 100,
				'description' => esc_html__( 'Set the starting delay.', 'events-addon-for-elementor' ),
			]
		);
		$this->add_control(
			'backDelay',
			[
				'label' => esc_html__( 'Back Delay', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 1500,
				'step' => 1,
				'default' => 100,
				'description' => esc_html__( 'Set the back delay.', 'events-addon-for-elementor' ),
			]
		);
		$this->end_controls_section();// end: Section

		// Style
		// Animation Title
		$this->start_controls_section(
			'section_anim_title_style',
			[
				'label' => esc_html__( 'Animation Title', 'events-addon-for-elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'label' => esc_html__( 'Title Typography', 'events-addon-for-elementor' ),
				'name' => 'sasban_title_typography',
				'selector' => '{{WRAPPER}} .naeep-typewriter h1',
			]
		);
		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .naeep-typewriter h1' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'label' => esc_html__( 'Animated Title Typography', 'events-addon-for-elementor' ),
				'name' => 'sasban_anim_title_typography',
				'selector' => '{{WRAPPER}} .naeep-typewriter h1 span',
			]
		);
		$this->add_control(
			'anim_title_color',
			[
				'label' => esc_html__( 'Animated Title Color', 'events-addon-for-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .naeep-typewriter h1 span' => 'color: {{VALUE}};',
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
			case 'cursor':
				// Special handling for cursor character - only allow safe characters
				$cleaned = sanitize_text_field($value);
				// Remove any HTML tags or dangerous characters
				$cleaned = strip_tags($cleaned);
				// Limit to single character for cursor
				return mb_substr($cleaned, 0, 1);
			default:
				return sanitize_text_field($value);
		}
	}

	/**
	 * Render App Works widget output on the frontend.
	 * Written in PHP and used to generate the final HTML.
	*/
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		// Sanitize all inputs
		$before_title = !empty($settings['before_title']) ? $this->sanitize_input($settings['before_title']) : '';
		$animation_groups = !empty($settings['animation_groups']) ? $settings['animation_groups'] : '';
		$after_title = !empty($settings['after_title']) ? $this->sanitize_input($settings['after_title']) : '';
		$cursorChar = !empty($settings['cursorChar']) ? $this->sanitize_input($settings['cursorChar'], 'cursor') : '|';
		$typeSpeed = !empty($settings['typeSpeed']) ? $this->sanitize_input($settings['typeSpeed'], 'number') : 100;
		$backSpeed = !empty($settings['backSpeed']) ? $this->sanitize_input($settings['backSpeed'], 'number') : 100;
		$startDelay = !empty($settings['startDelay']) ? $this->sanitize_input($settings['startDelay'], 'number') : 100;
		$backDelay = !empty($settings['backDelay']) ? $this->sanitize_input($settings['backDelay'], 'number') : 100;

		// Validate numeric values
		$typeSpeed = max(0, min(1500, $typeSpeed));
		$backSpeed = max(0, min(1500, $backSpeed));
		$startDelay = max(0, min(1500, $startDelay));
		$backDelay = max(0, min(1500, $backDelay));

		$typed_id = uniqid();
		$id = rand(999, 9999);

		// Use wp_kses to allow only safe HTML attributes
		$allowed_html = array(
			'div' => array(
				'class' => array(),
				'data-type-id' => array(),
				'data-id' => array(),
				'data-type-speed' => array(),
				'data-back-speed' => array(),
				'data-back-delay' => array(),
				'data-start-delay' => array(),
				'data-cursor-char' => array(),
			),
			'h1' => array(),
			'span' => array(
				'class' => array(),
			),
		);

		$output = '<div class="naeep-typewriter" data-type-id="'.esc_attr($typed_id).'" data-id="'.esc_attr($id).'" data-type-speed="'.esc_attr($typeSpeed).'" data-back-speed="'.esc_attr($backSpeed).'" data-back-delay="'.esc_attr($backDelay).'" data-start-delay="'.esc_attr($startDelay).'" data-cursor-char="'.esc_attr($cursorChar).'">
		          <h1>'.esc_html($before_title).'
		            <span class="typed_'.esc_attr($typed_id).'_'.esc_attr($id).'_strings">';
		            
		            // Group Param Output - with proper sanitization
					if (is_array($animation_groups) && !empty($animation_groups)) {
					  foreach ($animation_groups as $each_list) {
							$animation_text = !empty($each_list['animation_text']) ? $this->sanitize_input($each_list['animation_text']) : '';
						  $output .= '<span>'. esc_html($animation_text) .'</span>';
					  }
					}
					
					$output .= '</span>
		            <span class="typed_'.esc_attr($typed_id).'_'.esc_attr($id).'"></span>
		          '.esc_html($after_title).'</h1>
		         </div>';

		echo wp_kses($output, $allowed_html);
	}

}
Plugin::instance()->widgets_manager->register_widget_type( new Event_Elementor_Addon_Typewriter() );

} // enable & disable