<?php


namespace Saulmoralespa\Deprisa;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Spatie\ArrayToXml\ArrayToXml;

class Client
{
    const SANDBOX_URL_BASE = 'http://190.86.194.73:38080/conecta2/seam/resource/';
    const URL_BASE = 'https://conectados.avianca.com/conecta2/seam/resource/';
    const API_VERSION = "restv1";

    protected static $_sandbox = false;
    private $codeClient;
    private $codeCenter;

    public function __construct($codeClient, $codeCenter)
    {
        $this->codeClient = $codeClient;
        $this->codeCenter = $codeCenter;
    }

    public function sandboxMode($status = false)
    {
        self::$_sandbox = $status;
    }

    public function client()
    {
        return new GuzzleClient([
            "base_uri" => $this->getBaseUrl()
        ]);
    }

    public function getBaseUrl()
    {
        $url = self::URL_BASE;

        if(self::$_sandbox)
            $url = self::SANDBOX_URL_BASE;
        return $url . self::API_VERSION . '/';
    }

    public function liquidation(array $params)
    {
        try{
            $params = ArrayToXml::convert($this->getArrayXml($params), 'COTIZACIONES');
            $response = $this->client()->post("admision_envios/cotizar", [
               "headers" => [
                   "Content-Type" => "application/xml"
               ],
               "body" => $params
            ]);

            $body = self::responseArray($response);
            if (isset($body['ERRORES']['ERROR']))
                throw new \Exception($this->getErrors($body['ERRORES']['ERROR']));
            return $body;
        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    public function admission(array $params)
    {
        try{
            $params = ArrayToXml::convert($this->getArrayXml($params), 'ADMISIONES');
            $response = $this->client()->post("admision_envios", [
                "headers" => [
                    "Content-Type" => "application/xml"
                ],
                "body" => $params
            ]);

            $body = self::responseArray($response);
            if (isset($body['ERRORES']['ERROR']))
                throw new \Exception($this->getErrors($body['ERRORES']['ERROR']));
            return $body;
        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }


    public function labels(array $params)
    {
        try{
            $params = ArrayToXml::convert($params, 'ETIQUETAS');
            $response = $this->client()->post("admision_envios/etiquetas", [
                "headers" => [
                    "Content-Type" => "application/xml"
                ],
                "body" => $params
            ]);

            $body = self::responseArray($response);
            if (isset($body['ERRORES']['ERROR']))
                throw new \Exception($this->getErrors($body['ERRORES']['ERROR']));
            return $body;
        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    public function tracking($tracking)
    {
        try{
            $response = $this->client()->get("tracking/$tracking");
            return self::responseArray($response);
        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    public function getArrayXml(array $params)
    {
        $params = array_merge($params, [
            'CLIENTE_REMITENTE' => $this->codeClient,
            'CENTRO_REMITENTE' => $this->codeCenter
        ]);

        return ['ADMISION' => $params];

    }

    public function getErrors(array $errors)
    {
        $messages = [];

        if (count($errors) === 1)
            return $errors['@attributes']['ERROR_DESCRIPCION'];

        foreach ($errors as $error) {
            $messages[] = $error['@attributes']['ERROR_DESCRIPCION'];
        }

        return implode(PHP_EOL, $messages);

    }

    public static function responseArray($response)
    {
        $xml = $response->getBody()->getContents();

        $json =  \GuzzleHttp\json_encode(
            simplexml_load_string($xml)
        );

        return \GuzzleHttp\json_decode($json, true);
    }
}