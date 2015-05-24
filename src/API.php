<?php
/**
 * Created by PhpStorm.
 * User: michael1
 * Date: 2015-05-22
 * Time: 3:12 PM
 */

namespace mcarpenter\vinephp;
/**
 * Class VineAPI
 *
 * @method login(array $params)
 */
class API {
    private $username = null;
    private $password = null;
    private $sessionId = null;
    private $deviceToken = null;
    private $userId = null;

    public $user = null;

    public function __construct($username = "", $password = "", $deviceToken = "") {
        $this->username = $username;
        $this->password = $password;
        $this->sessionId = null;
        $this->deviceToken = $deviceToken ? $deviceToken : dechex(rand(0, 0xFFFFFFFF));

        if ($this->username && $this->password) {
            $this->user = $this->login(array("username" => $this->username, "password" => $this->password, "device_token" => $this->deviceToken));
        }
    }

    public function __call($name, $arguments) {
        if (!isset(Endpoint::$ENDPOINTS[$name])) {
            throw new Exception("Invalid Endpoint");
        };

        return $this->apiCall(Endpoint::$ENDPOINTS[$name], $arguments);
    }

    public function getUserId() {
        return $this->userId;
    }

    private function apiCall($metadata, $arguments) {

        $params = $this->checkParams($metadata, $arguments);

        $response = $this->doRequest($metadata, $params);

        if (!$metadata['model']) {
            return $response;
        } else {
            /**
             * @var Model $class
             */
            $class = $metadata['model'];
            $model = $class::fromStdClass($response);
            $model->connectApi($this);
            return $model;
        }
    }

    private function checkParams($metadata, $arguments) {
        $missing_params = [];
        $url_params = [];

        if (!is_array($arguments) || !is_array($arguments[0])) {
            throw new InvalidArgumentException("arguments is not an array");
        }

        $arguments = $arguments[0];

        # page, size and anchor are data_params for get requests

        foreach($metadata['url_params'] as $param) {
            if (!isset($arguments[$param])) {
                $missing_params[] = $param;
            } else {
                $url_params[] = $arguments[$param];
                unset($arguments[$param]);
            }
        }

        if (count($missing_params)) {
            throw new InvalidArgumentException(sprintf("Missing URL Paramater: [%s]", join(",", $missing_params)));
        }

        // $url_params shouldn't have default params, I guess
        $data_params = $arguments;
        if (isset($metadata['default_params']) && count($metadata['default_params'])) {
            $default_params = $metadata['default_params'];
            $data_params = array_merge($default_params, $data_params);
        }

        $missing_params = [];
        if (isset($metadata['required_params'])) {
            foreach ($metadata['required_params'] as $param) {
                if (!isset($data_params[$param])) {
                    $missing_params[] = $param;
                }
            }
        }
        if (count($missing_params)) {
            throw new InvalidArgumentException(sprintf("Missing URL Paramater: [%s]", join(",", $missing_params)));
        }

        // Check for unsupported params?

        $params = new \stdClass();
        $params->url = $url_params;
        $params->data = $data_params;
        return $params;
    }

    private function buildRequestUrl($protocol, $host, $endpoint) {
        $url = sprintf('%s://%s/%s', $protocol, $host, $endpoint);
        // encode url params
        return $url;
    }

    private function doRequest($metadata, $params)
    {
        $headers = Endpoint::$HEADERS;

        $endpoint = null;
        if (count($params->url)) {
            $endpoint = vsprintf($metadata['endpoint'],  array_values($params->url));
        } else {
            $endpoint = $metadata['endpoint'];
         }

        $host = Endpoint::API_HOST;
        // Upload methods, change host to specific host
        if (isset($metadata['host'])) {
            $host = $metadata['host'];
        }

        $url = $this->buildRequestUrl(Endpoint::PROTOCOL, $host, $endpoint);

        $built_params = $built_data = null;
        $built_data = $data = $params->data;

        if ($metadata['request_type'] == 'get') {
            $built_params = $data;
        }
        else if ($metadata['request_type'] == 'post') {
            if (isset($metadata['json']) && $metadata['json']) {
                $built_data = json_encode($data);
                $headers['Content-Type'] = 'application/json; charset=utf-8';
            }

        }
        else if(isset($data['filename']) && $data['filename']) {
            $pieces = preg_split('/./', $data['filename']);

            if ($pieces[count($pieces) - 1] == 'mp4') {
                $headers['Content-Type'] = 'video/mp4';
            } else {
                $headers['Content-Type'] = 'image/jpeg';
            }
            $built_data = fopen($data['filename'], "rb");
        }

        if ($this->sessionId) {
            $headers[] = "vine-session-id: {$this->sessionId}";
        }

        $response = $this->makeApiCall($metadata['request_type'], $url, $built_params, $built_data, $headers);

        if (isset($response->headers['X-Upload-Key'])) {
            return $response->headers['X-Upload-Key'];
        }

        switch($response->statusCode) {
            case 200:
            case 400:
            case 404:
            case 420:
                $data = null;
                try {
                    $data = $response->getResponseObject();
                } catch (Exception $e) {
                    throw new VineException("Vine replied with non-json encoded content:" . $response->responseText);
                }
                if (!$data->success) {
                    throw new VineException($data->error, $data->code);
                }
                return $data->data;
                break;
        }
        throw new VineException($response->responseText, $response->statusCode);
    }

    private function makeApiCall($type, $url, $params, $data, $headers) {
        $response = new VineApiResponse();

        $first = true;
        $builtUrl = $url;
        if ($params && count($params)) {
            foreach ($params as $key => $value) {
                $builtUrl .= ($first ? "?" : "&") . urlencode($key) . "=" . urlencode($value);
            }
        }

        $opts = array(
            CURLOPT_URL => $builtUrl,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => 1
//            ,CURLOPT_VERBOSE => 1
        );

        switch ($type) {
            case 'post':
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $data;
                break;
            case 'put':
                $stat = fstat($data);
                $opts[CURLOPT_PUT] = 1;
                $opts[CURLOPT_INFILE] = $data;
                $opts[CURLOPT_INFILESIZE] = $stat['size'];
                break;
            case 'delete':
                $opts[CURLOPT_CUSTOMREQUEST] = "DELETE";
                break;
            case 'get':
            default:
                break;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);

        $httpResponse = curl_exec($ch);

        $headers = array();

        $headerEndPosition = strpos($httpResponse, "\r\n\r\n");
        $headerText = trim(substr($httpResponse, 0, $headerEndPosition));
        if (strpos($headerText, "100 Continue") !== false) {
            $httpResponse = trim(substr($httpResponse, $headerEndPosition));
            $headerEndPosition = strpos($httpResponse, "\r\n\r\n");
            $headerText = trim(substr($httpResponse, 0, $headerEndPosition));
        }

        $bodyText = trim(substr($httpResponse, $headerEndPosition));
        $statusCode = 0;

        foreach (explode("\r\n", $headerText) as $i => $line) {
            if ($i === 0) {
                $statusCode = preg_split("/ /", $line)[1];
            } else {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }

        $response->statusCode = $statusCode;
        $response->responseText = $bodyText;
        $response->headers = $headers;

        return $response;
    }

    public function authenticate($user) {
        $this->user = $user;
        $this->sessionId = $user->key;
        $this->userId = $user->id;

    }
}

class VineException extends \Exception {
}

class VineApiResponse {
    public $headers = [];
    public $statusCode = 0;
    public $responseText;

    public function getResponseObject() {
        return json_decode($this->responseText);
    }
}