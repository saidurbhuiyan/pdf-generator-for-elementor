<?php

namespace ElementorPro\PDFGenerator;

use ElementorPro\Core\Utils\Collection;
use ElementorPro\Modules\Forms\Fields\Upload;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

class PDFGenerator {

    public array $submission_pdf = [];
    public array $attachments_array = [];

    public function __construct() {
        // Hook into Elementor form submission.
        add_action( 'elementor_pro/forms/process', [ $this, 'handle_form_submission' ], 11, 2 );
        add_action('elementor_pro/forms/new_record', [$this,'submit_update_data']);
        add_action('template_redirect', [$this,'preview_pdf']);
        add_filter( 'wp_mail_content_type',array($this,'set_content_type') );
        add_filter('upload_mimes', array($this,'mime_types'));
    }

    public function set_content_type(): string
    {
        return "text/html";
    }
    public function mime_types($mimes) {
        $mimes['json'] = 'text/plain';
        return $mimes;
    }

    /**
     * @throws MpdfException
     */
    public function handle_form_submission( $record, $handler ): void
    {
        // Ensure it's our target form.
        $form_name = $record->get_form_settings( "template_pdf_elementor" );
        if ( '' === $form_name ) {
            return;
        }

        // Get form data.
        $raw_fields = $record->get( 'fields' );
        $form_data = [];
        foreach ( $raw_fields as $id => $field ) {
            $form_data[ $id ] = $field['value'];
        }

        // Generate the PDF.
        $pdf_file = self::generate_pdf( $form_data, $form_name );
        $this->submission_pdf[] = $pdf_file['url'];

        $check_attach = $record->get_form_settings('attach_email_pdf_elementor');

        $this->attachments_array[] = $pdf_file['path'];
        if($check_attach === "yes" && defined('ElementorPro\Modules\Forms\Fields\Upload::MODE_ATTACH')){
            $settings = $record->get( 'form_settings' );
            $attachments_mode_attach = $this->get_file_by_attachment_type( $settings['form_fields'], $record, Upload::MODE_ATTACH );
            $attachments_mode_both = $this->get_file_by_attachment_type( $settings['form_fields'], $record, Upload::MODE_BOTH );
            $attachments_mode = array_merge($attachments_mode_attach,$attachments_mode_both);
            $this->attachments_array = array_merge($attachments_mode,$this->attachments_array);
        }


        if($check_attach === "yes" && count($this->attachments_array) > 0 ) {
            // Attach the PDF to the email.
            add_filter('wp_mail', [ $this, 'wp_mail' ] );
            //add_action( 'elementor_pro/forms/mail_sent', [ $this, 'remove_wp_mail_filter' ], 5 );
        }
    }

//    /**
//     * @return void
//     */
//    public function remove_wp_mail_filter(): void
//    {
//        $this->attachments_array = [];
//        remove_filter( 'wp_mail', [ $this, 'wp_mail' ] );
//    }

    /**
     * @param $args
     * @return mixed
     */
    public function wp_mail( $args ): mixed
    {
        $args['attachments'] = array_merge($args['attachments'],$this->attachments_array);
        return $args;
    }

    /**
     * @param $form_fields
     * @param $record
     * @param $type
     * @return array
     */
    public function get_file_by_attachment_type( $form_fields, $record, $type ): array
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
     * @throws MpdfException
     */
    public static function generate_pdf( $form_data, $template_name , $type = 'submission' ): array
    {
        $current_template = self::get_current_template($template_name);

        // Get the template file.
        $template_file = $current_template['template'] ?? '';
        $template_header = $current_template['header'] ?? '';
        $template_footer = $current_template['footer'] ?? '';
        $upload_dir = wp_upload_dir();

        // get header
        $html_header = '';
        if ($template_header !== ''){
            ob_start();
        include $template_header;
        $html_header = ob_get_clean();
        if (ob_get_length() > 0) {
            ob_clean();
        }
        }
        $html_footer = '';
        if ($template_footer !== ''){
        // get footer
        ob_start();
        include $template_footer;
        $html_footer = ob_get_clean();
        if(ob_get_length() > 0) {
            ob_clean();
        }
        }

        // get template
        $html = '';
        if ($template_file !== '') {
            ob_start();
            include $template_file;
            $html = ob_get_clean();
            if (ob_get_length() > 0) {
                ob_clean();
            }
        }

        $tmp_dir = $upload_dir['basedir'] . '/pdfs/tmp';
        $download_dir = $upload_dir['basedir'] . '/pdfs/download';
        if (!file_exists($tmp_dir) && !mkdir($tmp_dir, 0755, true) && !is_dir($tmp_dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $tmp_dir));
        }

        if (!file_exists($download_dir) && !mkdir($download_dir, 0755, true) && !is_dir($download_dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $download_dir));
        }
        $mpdf = new Mpdf( [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 30,
            'margin_bottom' => 40,
            'margin_header' => 0,
            'margin_footer' => 0,
            'orientation' => 'P',
            'debugfonts' => false,
            'debug' => false,
            'useActiveForms' => false,
            'tempDir' => $tmp_dir,
            'use_kwt' => true,
        ]);

        $mpdf->SetHTMLHeader($html_header);
        $mpdf->SetHTMLFooter($html_footer);
        $mpdf->WriteHTML($html);


        $templateName = $current_template['name'] ?? 'default';
        $file_name =  '/'.$templateName . '-' . time() . '.pdf';
        if($type === 'preview') {
            $file_name = '/'. $templateName . '-preview.pdf';
        }

        $mpdf->Output($download_dir .$file_name, "F");

        return [
            "name"=>$templateName,
            "path"=>$upload_dir['basedir'].'/pdfs/download'.$file_name,"url"=>$upload_dir['baseurl'].'/pdfs/download'.$file_name,
        ];
    }

    /**
     * @param $key
     * @return array|mixed
     */
    public static function get_current_template($key): mixed
    {
        $templates = get_option( 'pdf_generator_templates', [] );
        return array_filter($templates, static fn($template) => $template['name'] === $key || $template['id'] === (int)$key)[0] ?? [];


    }

    /**
     * @param $record
     * @return void
     */
    public function submit_update_data($record): void
    {
        update_option("pdf_download_last",implode(",",$this->submission_pdf));
        if( count($this->submission_pdf) > 0  ){
            $form_post_id = $record->get_form_settings( 'id' );
            $submission_id = $this->get_last_submission_id($form_post_id);
            if($submission_id > 0){
                foreach($this->submission_pdf as $path_main){
                    if($path_main !== ""){
                        $this->add_meta_submission($submission_id,$path_main);
                    }
                }
            }
        }
    }

    /**
     * @param $form_id
     * @return mixed|null
     */
    public function get_last_submission_id($form_id = null) {
        global $wpdb;
        $table_e_submissions = $wpdb->prefix."e_submissions";
        if(!$form_id){
            $datas = $wpdb->get_row("SELECT * FROM $table_e_submissions ORDER BY id DESC",ARRAY_A);
        }else{
            $datas = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_e_submissions WHERE element_id = %s ORDER BY id DESC", $form_id), ARRAY_A);
        }

        return $datas["id"] ?? null;
    }

    /**
     * @param $submission_id
     * @param $link
     * @return int
     */
    public function add_meta_submission($submission_id,$link): int
    {
        global $wpdb;
        $table_e_submissions_meta = $wpdb->prefix."e_submissions_values";
        $wpdb->insert(
            $table_e_submissions_meta,
            array(
                'submission_id' => $submission_id,
                'key' => 'PDF',
                'value' => $link,
            ),
            array(
                '%d',
                '%s',
                '%s',
            )
        );
        return $wpdb->insert_id;
    }

    /**
     * @throws MpdfException
     */
    public function preview_pdf(): void
    {
        // Check if the query parameters are set correctly
        if (isset($_GET['page'], $_GET['type']) && $_GET['page'] === 'pdf-generator-settings' && $_GET['type'] === 'preview' && !empty($_GET['name'])) {
            $file = self::generate_pdf(['organisation_name' => 'preview',
                'need_po' => 'No'], $_GET['name'],  'preview');
            header('Location: ' . $file['url']);

        }
    }
}