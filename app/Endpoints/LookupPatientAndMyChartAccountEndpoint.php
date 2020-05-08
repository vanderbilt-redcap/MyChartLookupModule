<?php

namespace Vanderbilt\MyChartLookup\App\Endpoints
{

    class LookupPatientAndMyChartAccountEndpoint
    {

        private static $url_template = 'api/epic/2019/PatientAccess/Patient/LookupPatientAndMyChartAccount';

        /**
         * URL of the endpoint. computed at instantiation
         *
         * @var string
         */
        private $endpoint_url;

        public function __construct($fhir_endpoint_base_url)
        {
            $base_url = $this->getBaseUrl($fhir_endpoint_base_url);
            $this->endpoint_url = $base_url.self::$url_template;
        }

        public function check($access_token, $fhir_client_id, $person_id, $person_id_type=array('MRN'))
        {
            $http_settings = array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer '.$access_token,
                    'Epic-Client-ID' => $fhir_client_id,
                ),
                'form_params' => [
                    'PersonID' => $person_id,
                    'PersonIDType' => $person_id_type,
                ],
            );
            $response = $this->postData($this->endpoint_url, $http_settings);
            return $response;
        }

        /**
         * post data to the endpoint
         *
         * @param string $url
         * @param array $settings configuration for the HTTP client
         * @return string
         */
        function postData($url, $settings=[])
        {
            $default_settings = array(
                'headers' => array(),
                'form_params' => array(),
            );
            $options = $this->mergeParams($default_settings, $settings);

            $response = \HttpClient::request('POST', $url, $options);
            return $response->getBody();
        }

        /**
         * compute the web service base URL
         * from the FHIR endpoint base URL
         *
         * @param string$fhir_endpoint_base_url
         * @return void
         */
        private function getBaseUrl($fhir_endpoint_base_url)
        {
            $reg_exp = '/(?<base>.+?)api\/FHIR\/(?:DSTU2|STU3|R4)\/?$/i';
            $base_url = preg_replace($reg_exp, '\1', $fhir_endpoint_base_url);
            return $base_url;
        }

        private function mergeParams($defaultParams, $overrideParams=array())
        {
            $params = array_replace_recursive($defaultParams, $overrideParams);
            return $params;
        }

    }
}