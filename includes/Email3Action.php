<?php

namespace ElementorPro\PDFGenerator;

use ElementorPro\Core\Utils;
use ElementorPro\Core\Utils\Collection;
use ElementorPro\Modules\Forms\Actions\Email;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Modules\Forms\Fields\Upload;

class Email3Action extends Email
{

    public function get_name(): string
    {
        return 'email3';
    }

    public function get_label(): string
    {
        return esc_html__( 'Email 3', 'elementor-pro' );
    }

    protected function get_control_id( $control_id ): string
    {
        return $control_id . '_3';
    }

    /**
     * @param Form_Record $record
     * @param Ajax_Handler $ajax_handler
     * @throws \Exception
     */
    public function run( $record, $ajax_handler ): void
    {
        $settings = $record->get( 'form_settings' );
        $send_html = 'plain' !== $settings[ $this->get_control_id( 'email_content_type' ) ];
        $line_break = $send_html ? '<br>' : "\n";

        $fields = [
            'email_to' => get_option( 'admin_email' ),
            'email_subject' => sprintf( esc_html__( 'New message from "%s"', 'elementor-pro' ), get_bloginfo( 'name' ) ),
            'email_content' => '[all-fields]',
            'email_from_name' => get_bloginfo( 'name' ),
            'email_from' => get_bloginfo( 'admin_email' ),
            'email_reply_to' => 'noreply@' . Utils::get_site_domain(),
            'email_to_cc' => '',
            'email_to_bcc' => '',
        ];

        foreach ( $fields as $key => $default ) {
            $setting = trim( $settings[ $this->get_control_id( $key ) ] );

            if($key ==='email_content'){
                $setting = $this->format_empty_field( $setting, $record );
            }
            $setting = $record->replace_setting_shortcodes( $setting );
            if ( ! empty( $setting ) ) {
                $fields[ $key ] = $setting;
            }
        }
        error_log( $fields['email_content']);
        error_log(abc);

        $email_reply_to = $this->get_reply_to( $record, $fields );

        $fields['email_content'] = $this->replace_content_shortcodes( $fields['email_content'], $record, $line_break );

        $email_meta = '';

        $form_metadata_settings = $settings[ $this->get_control_id( 'form_metadata' ) ];

        foreach ( $record->get( 'meta' ) as $id => $field ) {
            if (in_array($id, $form_metadata_settings, true)) {
                $email_meta .= $this->field_formatted( $field ) . $line_break;
            }
        }

        if ( ! empty( $email_meta ) ) {
            $fields['email_content'] .= $line_break . '---' . $line_break . $line_break . $email_meta;
        }

        $headers = sprintf( 'From: %s <%s>' . "\r\n", $fields['email_from_name'], $fields['email_from'] );
        $headers .= sprintf( 'Reply-To: %s' . "\r\n", $email_reply_to );

        if ( $send_html ) {
            $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        }

        $cc_header = '';
        if ( ! empty( $fields['email_to_cc'] ) ) {
            $cc_header = 'Cc: ' . $fields['email_to_cc'] . "\r\n";
        }

        /**
         * Email headers.
         *
         * Filters the headers sent when an email is sent from Elementor forms. This
         * hook allows developers to alter email headers triggered by Elementor forms.
         *
         * @since 1.0.0
         *
         * @param string|array $headers Additional headers.
         */
        $headers = apply_filters( 'elementor_pro/forms/wp_mail_headers', $headers );

        /**
         * Email content.
         *
         * Filters the content of the email sent by Elementor forms. This hook allows
         * developers to alter the content of the email sent by Elementor forms.
         *
         * @since 1.0.0
         *
         * @param string $email_content Email content.
         */
        $fields['email_content'] = apply_filters( 'elementor_pro/forms/wp_mail_message', $fields['email_content'] );

        $attachments_mode_attach = $this->get_file_by_attachment_type( $settings['form_fields'], $record, Upload::MODE_ATTACH );
        $attachments_mode_both = $this->get_file_by_attachment_type( $settings['form_fields'], $record, Upload::MODE_BOTH );

        $email_sent = wp_mail(
            $fields['email_to'],
            $fields['email_subject'],
            $fields['email_content'],
            $headers . $cc_header,
            array_merge( $attachments_mode_attach, $attachments_mode_both )
        );

        if ( ! empty( $fields['email_to_bcc'] ) ) {
            $bcc_emails = explode( ',', $fields['email_to_bcc'] );
            foreach ( $bcc_emails as $bcc_email ) {
                wp_mail(
                    trim( $bcc_email ),
                    $fields['email_subject'],
                    $fields['email_content'],
                    $headers,
                    array_merge( $attachments_mode_attach, $attachments_mode_both )
                );
            }
        }

        foreach ( $attachments_mode_attach as $file ) {
            @unlink( $file );
        }

        /**
         * Elementor form mail sent.
         *
         * Fires when an email was sent successfully by Elementor forms. This
         * hook allows developers to add functionality after mail sending.
         *
         * @since 1.0.0
         *
         * @param array       $settings Form settings.
         * @param Form_Record $record   An instance of the form record.
         */
        do_action( 'elementor_pro/forms/mail_sent', $settings, $record );

        if ( ! $email_sent ) {
            $message = Ajax_Handler::get_default_message( Ajax_Handler::SERVER_ERROR, $settings );

            $ajax_handler->add_error_message( $message );

            throw new \RuntimeException( $message );
        }
    }

    private function format_empty_field( $content, $record )
    {
        foreach ($record->get('fields') as $field) {
            if (empty($field['value'])) {
                // Extract the field ID in the format: [field id="field_xyz"]
                $field_id_placeholder = "[field id=\"{$field['id']}\"]";

                // Check if the placeholder exists in the <td> within the $content
                if (str_contains($content, $field_id_placeholder)) {
                    error_log(print_r($field, true));
                    // Find the <tr> containing the placeholder and update its style
                    $content = preg_replace(
                        '/<tr style="display: flex;">(.*?)' . preg_quote($field_id_placeholder, '/') . '(.*?)<\/tr>/s',
                        '<tr style="display: none;">$1' . $field_id_placeholder . '$2</tr>',
                        $content,
                        1
                    );

                }
            }
        }

        return $content;
    }


    /**
     * @param array $form_fields
     * @param Form_Record $record
     * @param string $type
     *
     * @return array
     */
    private function get_file_by_attachment_type( array $form_fields, Form_Record $record, string $type ): array
    {
        return Collection::make( $form_fields )
            ->filter( function ( $field ) use ( $type ) {
                return $type === $field['attachment_type'];
            } )
            ->map( function ( $field ) use ( $record ) {
                $id = $field['custom_id'];

                return $record->get( 'files' )[ $id ]['path'] ?? null;
            } )
            ->filter()
            ->flatten()
            ->values();
    }

    /**
     * @param $field
     * @return string
     */
    private function field_formatted( $field ): string
    {
        $formatted = '';
        if ( ! empty( $field['title'] ) ) {
            $formatted = sprintf( '%s: %s', $field['title'], $field['value'] );
        } elseif ( ! empty( $field['value'] ) ) {
            $formatted = sprintf( '%s', $field['value'] );
        }

        return $formatted;
    }

    /**
     * @param string $email_content
     * @param Form_Record $record
     * @param $line_break
     * @return string
     */
    private function replace_content_shortcodes( $email_content, $record, $line_break ): string
    {
        $email_content = do_shortcode( $email_content );
        $all_fields_shortcode = '[all-fields]';

        if (str_contains($email_content, $all_fields_shortcode)) {
            $text = '';

            foreach ( $record->get( 'fields' ) as $field ) {
                // Skip upload fields that only attached to the email
                if ( isset( $field['attachment_type'] ) && Upload::MODE_ATTACH === $field['attachment_type'] ) {
                    continue;
                }

                $formatted = $this->field_formatted( $field );
                if ( ( 'textarea' === $field['type'] ) && ( '<br>' === $line_break ) ) {
                    $formatted = str_replace( [ "\r\n", "\n", "\r" ], '<br />', $formatted );
                }

                $text .= $formatted . $line_break;
            }

            $email_content = str_replace($all_fields_shortcode, $text, $email_content);

        }

        return $email_content;
    }

}