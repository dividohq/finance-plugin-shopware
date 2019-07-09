<?php
/**
 * File for TranslationService class
 *
 * PHP version 5.6
 */

namespace FinancePlugin\Components\Finance;

class TranslationService {

    const
        POEDITOR_URL = 'https://api.poeditor.com',
        TERMS_PATH   = '/v2/terms/list';

    private $api_token;
    private $project_id;


    public function __construct($api_token, $project_id) {
        $this->api_token  = $api_token;
        $this->project_id = $project_id;
    }

    private function generateCurlResource($method='POST', $url_path='', $body=[]) {
        // get cURL resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, self::POEDITOR_URL.$url_path);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
        ]);

        $body = http_build_query($body);

        // set body
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        return $ch;
    }

    public function getTranslationResponse($language) {

        // form body
        $body = array(
            'api_token' => $this->api_token,
            'id' => $this->project_id,
            'language' => $language
        );
        $ch = $this->generateCurlResource(self::TERMS_PATH, $body);

        // send the request and save response to $response
        $response = curl_exec($ch);

        // stop if fails
        if (!$response) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }

        // close curl resource to free up system resources
        curl_close($ch);

        return $response;
    }

    public function getResponseTerms(string $response){
        $responseArr = json_decode($response, true);

        if ('success' != $responseArr['response']['status']) {
            throw new Exception('Could not retrieve terms');
        }

        $terms = [];
        foreach($responseArr['terms'] as $t) {
            $terms[$t['term']] = array(
                'translation' => $t['translation']['content'],
                'reference'   => $t['reference']
            );
        }

        return $terms;
    }


}