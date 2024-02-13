<?php

namespace Saulmoralespa\Deprisa;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Utils;
use Spatie\ArrayToXml\ArrayToXml;

class Client
{
    const SANDBOX_URL_BASE = 'http://190.86.194.73:38080/conecta2/seam/resource/';
    const URL_BASE = 'https://conectados.avianca.com/conecta2/seam/resource/';
    const API_VERSION = "restv1";

    protected static bool $sandbox = false;
    private string $codeClient;
    private string $codeCenter;

    public function __construct($codeClient, $codeCenter)
    {
        $this->codeClient = $codeClient;
        $this->codeCenter = $codeCenter;
    }

    public function sandboxMode($status = false): void
    {
        self::$sandbox = $status;
    }

    public function client(): GuzzleClient
    {
        return new GuzzleClient([
            "base_uri" => $this->getBaseUrl(),
            "verify" => false
        ]);
    }

    public function getBaseUrl(): string
    {
        $url = self::URL_BASE;

        if(self::$sandbox)
            $url = self::SANDBOX_URL_BASE;
        return $url . self::API_VERSION . '/';
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function liquidation(array $params): array
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

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function admission(array $params): array
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


    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function labels(array $params): array
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

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function tracking($tracking): array
    {
        try{
            $response = $this->client()->get("tracking/$tracking");
            return self::responseArray($response);
        }catch (RequestException $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    public function getArrayXml(array $params): array
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

    public static function responseArray($response): array
    {
        $xml = $response->getBody()->getContents();

        $json =  Utils::jsonEncode(
            simplexml_load_string($xml)
        );

        return Utils::jsonDecode($json, true);
    }
}