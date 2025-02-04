# PDF Generator for Elementor

This plugin generates a PDF from Elementor Pro form submissions and attaches it to the email. It is designed to work seamlessly with Elementor Pro's form widget, allowing you to create professional PDF documents from form data and send them as email attachments.

**Note: This plugin is for testing purposes only and is not intended for use in production environments. You must manually add the template and change the default template name to the actual template name before installation.**

---

## Features

- **Generate PDFs from Form Submissions**: Automatically creates a PDF document from the data submitted through Elementor Pro forms.
- **Attach PDF to Email**: Sends the generated PDF as an attachment to the email configured in the Elementor form.
- **Customizable PDF Template**: Allows you to customize the layout and design of the PDF using simple placeholders.
- **Support for Multiple Fields**: Works with all standard Elementor form fields, including text, email, dropdowns, checkboxes, and more.
- **Easy Integration**: No coding required—just install, configure, and start generating PDFs.

---

## Installation

1. **Prepare the Template**:
    - Before installation, ensure you have a PDF template ready.
    - Replace the default template file with your actual template file and rename it appropriately.

2. **Install the Plugin**:
    - Download the plugin ZIP file.
    - Go to your WordPress Dashboard > Plugins > Add New > Upload Plugin.
    - Upload the ZIP file and click "Install Now."
    - Activate the plugin.

---

## Usage

1. **Create or Edit an Elementor Form**:
    - Open the Elementor editor and add or edit a Form widget.
    - Configure your form fields as needed.

2. **Enable PDF Generation**:
    - In the Form settings, navigate to the "Actions After Submit" section.
    - Enable the "PDF Generator" option.

3. **Customize the PDF Template**:
    - Use placeholders like `{field_id}` to dynamically insert form data into the PDF.
    - Customize the PDF layout, font, and styling using the provided options.

4. **Configure Email Settings**:
    - Ensure the email action is enabled in the form settings.
    - The generated PDF will automatically be attached to the email.

5. **Test the Form**:
    - Submit the form to verify that the PDF is generated and attached to the email.

---

## Configuration

### PDF Template Customization
- Use placeholders like `{field_id}` to insert form data into the PDF.
- Customize the PDF title, font, and layout.
- Add headers, footers, and custom branding.

### Email Settings
- Ensure the email action is enabled in the Elementor form settings.
- The PDF will be attached to the email automatically.


---

## Contributing

We welcome contributions! If you'd like to contribute to this project, please follow these steps:

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Commit your changes.
4. Submit a pull request.

---

## License

This plugin is licensed under the GNU General Public License v2 or later. See the [LICENSE](LICENSE) file for details.

---

## Disclaimer

**This plugin is for testing purposes only and is not intended for use in production environments.** Use it at your own risk. The developers are not responsible for any issues or data loss that may occur. Additionally, you must manually add the template and change the default template name to the actual template name before installation.
