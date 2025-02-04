<?php

namespace ElementorPro\PDFGenerator;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;
use JsonException;
use RuntimeException;

const HUBSPOT_BATCH_API_URL = "https://api.hubapi.com/crm/v3/objects/contacts/batch/create";

class HubspotMailListAction extends Action_Base
{


    public function get_name(): string
    {
        return 'hubspot_mail_list';
    }

    public function get_label(): string
    {
        return esc_html__('Hubspot Mail List', 'pdf-generator-for-elementor');
    }

    protected function get_control_id($control_id)
    {
        return $control_id;
    }

    protected function get_title(): string
    {
        return esc_html__('Hubspot Mail List', 'pdf-generator-for-elementor');
    }


    public function register_settings_section($widget): void
    {

        $widget->start_controls_section(
            $this->get_control_id('section_send_mail_list_hubspot'),
            [
                'label'     => $this->get_title(),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );
        $widget->add_control(
            $this->get_control_id('hubspot_access_token'),
            [
                'label'       => esc_html__('Hubspot Access Token', 'pdf-for-elementor-forms'),
                'type'        => Controls_Manager::TEXT,
                'description' => esc_html__('Enter the hubspot access token.', 'pdf-for-elementor-forms'),
            ]
        );

        $widget->add_control(
            $this->get_control_id('hubspot_options'),
            [
                'label'       => esc_html__('Options With ID', 'pdf-for-elementor-forms'),
                'type'        => Controls_Manager::TEXTAREA,
                'description' => esc_html__('Options With ID, like if the id value is text field_ead47bc=firstname (id=value). if array field_ead47bc=name|firstname,surname|lastname,email|email (id=keys|values). add id by line separate.', 'pdf-for-elementor-forms'),
            ]
        );

        $widget->end_controls_section();
    }

    /**
     * @throws JsonException
     */
    public function run($record, $handler): void
    {
        $settings = $record->get( 'form_settings' );
       $access_token =  $settings[ $this->get_control_id( 'hubspot_access_token' ) ];
       $options = trim($settings[ $this->get_control_id( 'hubspot_options' ) ]);

       if(empty($options)){
           return;
       }

        $lines = explode("\n", $options);
        $shortIds = [];
        foreach ($lines as $line) {
            // Split each line by the '=' character
            [$key, $value] = explode('=', $line, 2);

            // If the value contains '|', split into key-value pairs
            if (str_contains($value, '|')) {
                $subArray = [];
                $pairs = explode(',', $value); // Split by ','
                foreach ($pairs as $pair) {
                    [$subKey, $subValue] = explode('|', $pair);
                    $subArray[$subKey] = $subValue;
                }
                $shortIds[$key] = $subArray;
            } else {
                $shortIds[$key] = $value;
            }
        }


       $contacts = [];
       $contactMailList = [];
       foreach ($record->get( 'fields' ) as $field) {
           if(isset($shortIds[$field['id']])){
               $fieldMap = $shortIds[$field['id']];
               if(is_array($fieldMap)){
                   $contactMailList[] = $this->humanReadableStringToArray($field['value'], $fieldMap);
               }else{
                   $contacts["properties"][$fieldMap] = $field['value'];
               }
           }
       }

        $contacts = [$contacts];
       foreach ($contactMailList as $contactMail) {
           foreach ($contactMail as $contact) {
               $contacts[]["properties" ] = $contact;
           }

       }

        $this->curl_request($access_token, $contacts);

//       if (!$response['status'])
//       {
//           throw new RuntimeException(json_encode($response['message'], JSON_THROW_ON_ERROR));
//       }


    }


    private function humanReadableStringToArray(string $string, $fieldMap): array
    {
        $result = [];
        $rows = explode(',<br>', $string);

        foreach ($rows as $row) {
            $item = [];
            $pairs = explode(' | ', $row);

            foreach ($pairs as $pair) {
                if (str_contains($pair, ': ')) {
                    [$key, $value] = explode(': ', $pair, 2);
                    $key = strtolower(str_replace(' ', '_', $key));
                    if (isset($fieldMap[$key])) {
                        $item[$fieldMap[$key]] = $value;
                    }
                }
            }

            if (!empty($item)) {
                $result[] = $item;
            }
        }



        return $result;
    }

    /**
     * @throws JsonException
     */
    private function curl_request($access_token, $contacts): array
    {
        $ch = curl_init(HUBSPOT_BATCH_API_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["inputs" => $contacts], JSON_THROW_ON_ERROR));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 201) {
            return ["status"=>true, "message" => "Contacts successfully created!", "response" => $response];
        }

        return ["status"=>false, "message" => "Error creating contacts.", "response" => $response];

    }

    /**
     * Handle export
     *
     * This method is required by the abstract class.
     *
     * @param array $element
     * @return array
     */
    public function on_export($element): array
    {
        return $element;
    }


}