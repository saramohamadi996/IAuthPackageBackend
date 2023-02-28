<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 *
 */
class DeployController extends Controller
{
    /**
     * @param Request $request
     * @return void
     */
    public function backendDeploy(Request $request)
    {
        $secret_key = env('APP_DEPLOY_SECRET');

        // check for POST request
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo('FAILED - not POST - '. $_SERVER['REQUEST_METHOD']);
            return;
        }

        // get content type
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';

        if ($content_type != 'application/json') {
            echo('FAILED - not application/json - '. $content_type);
            return;
        }

        // get payload
        $payload = trim(file_get_contents("php://input"));

        if (empty($payload)) {
            echo('FAILED - no payload');
            return;
        }

        // get header signature
        $header_signature = isset($_SERVER['HTTP_X_GITEA_SIGNATURE']) ? $_SERVER['HTTP_X_GITEA_SIGNATURE'] : '';
        if (empty($header_signature)) {
            echo('FAILED - header signature missing');
            return;
        }

        // calculate payload signature
        $payload_signature = hash_hmac('sha256', $payload, $secret_key, false);

        //check payload signature against header signature
        if ($header_signature !== $payload_signature) {
            echo('FAILED - payload signature');
            return;
        }

        // convert json to array
        $decoded = json_decode($payload, true);

        // check for json decode errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo('FAILED - json decode - '. json_last_error());
            return;
        }

        // success, do something
        chdir(base_path());
        echo shell_exec('/bin/sh deploy.sh');

    }

    /**
     * @param Request $request
     * @return void
     */
    public function frontendDeploy(Request $request)
    {
        $secret_key = env('APP_DEPLOY_SECRET');

        // check for POST request
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo('FAILED - not POST - '. $_SERVER['REQUEST_METHOD']);
            return;
        }

        // get content type
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';

        if ($content_type != 'application/json') {
            echo('FAILED - not application/json - '. $content_type);
            return;
        }

        // get payload
        $payload = trim(file_get_contents("php://input"));

        if (empty($payload)) {
            echo('FAILED - no payload');
            return;
        }

        // get header signature
        $header_signature = isset($_SERVER['HTTP_X_GITEA_SIGNATURE']) ? $_SERVER['HTTP_X_GITEA_SIGNATURE'] : '';
        if (empty($header_signature)) {
            echo('FAILED - header signature missing');
            return;
        }

        // calculate payload signature
        $payload_signature = hash_hmac('sha256', $payload, $secret_key, false);

        //check payload signature against header signature
        if ($header_signature !== $payload_signature) {
            echo('FAILED - payload signature');
            return;
        }

        // convert json to array
        $decoded = json_decode($payload, true);

        // check for json decode errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo('FAILED - json decode - '. json_last_error());
            return;
        }

        // success, do something
        $frontend_base_path = str_replace('-api', '', base_path());
        chdir($frontend_base_path);
        echo shell_exec('/bin/sh deploy.sh');
    }

    public function migrateFresh(Request $request)
    {
        $secret_key = env('APP_DEPLOY_SECRET');

        // get header signature
        $header_signature = isset($_SERVER['HTTP_X_GITEA_SIGNATURE']) ? $_SERVER['HTTP_X_GITEA_SIGNATURE'] : '';
        if (empty($header_signature)) {
            echo('FAILED - header signature missing');
            return;
        }

        //check payload signature against header signature
        if ($header_signature !== $secret_key) {
            echo('FAILED - payload signature');
            return;
        }

        // success, do something
        chdir(base_path());
        echo shell_exec('/bin/sh migrate.sh');
    }

    public function test1()
    {
        return "salam3";
    }
}
