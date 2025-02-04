<?php

namespace ElementorPro\PDFGenerator;

use ElementorPro\Modules\Forms\Classes\Action_Base;
use Elementor\Controls_Manager;

class PDFGenerateAction extends Action_Base {

    public function get_name() {
        return 'pdf_generate';
    }

    public function get_label() {
        return esc_html__( 'Generate PDF', 'pdf-generator-for-elementor' );
    }

    protected function get_control_id( $control_id ) {
        return $control_id;
    }

    protected function get_title(): string
    {
        return esc_html__( 'Generate PDF', 'pdf-generator-for-elementor' );
    }


    public function register_settings_section( $widget ) : void {
        $templates = $this->get_templates();

        $widget->start_controls_section(
            $this->get_control_id('section_send_pdf_elementor'),
            [
                'label' => $this->get_title(),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );
        $widget->add_control(
            $this->get_control_id('template_pdf_elementor'),
            [
                'label' => esc_html__( 'Choose PDF Template', 'pdf-for-elementor-forms' ),
                'type' => Controls_Manager::SELECT2,
                'options' => $templates,
                'description' => esc_html__( 'Enter the pdf template.', 'pdf-for-elementor-forms' ),
            ]
        );

        $widget->add_control(
            $this->get_control_id('attach_email_pdf_elementor'),
            [
                'label' => esc_html__( 'Attach PDF in email', 'pdf-for-elementor-forms' ),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $widget->end_controls_section();
    }

    public function run( $record, $handler ) :void {
    }

    /**
     * Handle export
     *
     * This method is required by the abstract class.
     *
     * @param array $element
     * @return array
     */
    public function on_export( $element ): array
    {
        return $element;
    }

    private function get_templates(): array
    {
        $templates = get_option( 'pdf_generator_templates', [] );
        $options = [];
        foreach ( $templates as $template ) {
            $options[ $template['id'] ] = $template['title'];
        }
        return $options;
    }
}
