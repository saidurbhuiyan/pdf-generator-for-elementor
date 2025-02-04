<?php

namespace ElementorPro\PDFGenerator;

use Mpdf\MpdfException;

class SettingsPage {

    public function __construct() {
        add_menu_page(
            'PDF Generator Settings',
            'PDF Generator',
            'manage_options',
            'pdf-generator-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-media-document'
        );

        add_action( 'plugins_loaded', [$this, 'pdf_generator_update_templates' ]);
    }

    /**
     * @throws MpdfException
     */
    public function render_settings_page(): void
    {
        global $wp;
        $templates = get_option( 'pdf_generator_templates', [] );
        if (isset($_POST['upload_template'], $_FILES['template_file'])) {
            $uploaded_file = $_FILES['template_file'];

            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'] . '/templates/' . $uploaded_file['name'];
            move_uploaded_file( $uploaded_file['tmp_name'], $file_path );

            $templates[] = [
                'id'   => uniqid('', true),
                'name'     => str_replace( '.php', '', $uploaded_file['name']),
                'title' => sanitize_text_field( $_POST['template_name'] ),
                'template' => $file_path,
                'header'   => '',
                'footer'   => '',
            ];

            update_option( 'pdf_generator_templates', $templates );
        }

        if(isset($_GET['type'], $_GET['file']) && $_GET['type'] === 'preview') {
            PDFGenerator::generate_pdf( [], $_GET['file'], 'preview' );
        }

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">PDF Generator Settings</h1>';
        echo '<form method="post" enctype="multipart/form-data" class="form-wrap">';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="template_name">Template Name</label></th>';
        echo '<td><input type="text" id="template_name" name="template_name" class="regular-text" required></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="template_file">Upload Template</label></th>';
        echo '<td><input type="file" id="template_file" name="template_file" required></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p class="submit">';
        echo '<button type="submit" name="upload_template" class="button button-primary">Upload</button>';
        echo '</p>';
        echo '</form>';

        echo '<h2>Existing Templates</h2>';
        if ( ! empty( $templates ) ) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col" class="manage-column column-primary">Name</th>';
            echo '<th scope="col" class="manage-column">Preview</th>';
            echo '<th scope="col" class="manage-column">Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ( $templates as $template ) {
                echo '<tr>';
                echo '<td class="column-primary">' . esc_html( $template['title'] ) . '</td>';
                echo '<td><a href="' . get_site_url( null, $_SERVER['REQUEST_URI'] ) . '&type=preview&name=' . $template['name'] . '" target="_blank" class="button-link">Preview</a></td>';
                echo '<td>';
                echo '<form method="post" style="display:inline;">';
                echo '<button type="submit" name="delete_template" value="' . esc_attr( $template['id'] ) . '" class="button button-secondary">Delete</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No templates found.</p>';
        }
        echo '</div>';

    }

/**
 * Update Hook
 * Update templates when the plugin is updated.
 */

public function pdf_generator_update_templates(): void
{
    // Define the default template
    $default_template = [
            'id'       => 1,
            'title'     => 'Default Membership Template',
            'name'     => 'default-template',
            'template' => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/default/default-template.php',
            'header'   => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/default/default-header.php',
            'footer'   => ELEMENTOR_PDF_GENERATOR_PATH . 'templates/default/default-footer.php',
            'preview'  => ELEMENTOR_PDF_GENERATOR_URL . 'templates/default/default-preview.png',
            'is_active'=> true, // Default template is active
        ];

    // Get existing templates
    $templates = get_option( 'pdf_generator_templates', [] );

    // Check if default template exists
    $default_exists = false;
    foreach ( $templates as $template ) {
        if ( $template['id'] === $default_template['id'] ) {
            $default_exists = true;
            break;
        }
    }

    // Add or update the default template
    if ( ! $default_exists ) {
        $templates[] = $default_template;
    } else {
        // Update the default template, if needed
        foreach ( $templates as &$template ) {
            if ( $template['id'] === $default_template['id'] ) {
                $template = $default_template; // Replace with updated default
            }
        }
        unset($template);
    }

    // Save the updated templates back to the database
    update_option( 'pdf_generator_templates', $templates );
}

}
