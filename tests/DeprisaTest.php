<?php

use PHPUnit\Framework\TestCase;
use Saulmoralespa\Deprisa\Client;

class DeprisaTest extends TestCase
{
    public $deprisa;

    protected function setUp(): void
    {
        $dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/../');
        $dotenv->load();

        $codeClient = $_ENV['CODE_CLIENT'];
        $codeCenter = $_ENV['CODE_CENTER'];

        $this->deprisa = new Client($codeClient, $codeCenter);
        $this->deprisa->sandboxMode(false);
    }

    public function testLiquidation()
    {
        $params = [
            'TIPO_ENVIO' => 'N',  //N,I,C
            'NUMERO_BULTOS' => 1,
            'KILOS' => 5,
            'POBLACION_REMITENTE' => 'BOGOTA',
            'POBLACION_DESTINATARIO' => 'BOGOTA',
            'INCOTERM' => '',
            'CODIGO_SERVICIO'  => '',
            'LARGO' => 10,
            'ANCHO' => 20,
            'ALTO' => 15,
            'TIPO_MERCANCIA' => '',
            'CONTENEDOR_MERCANCIA' => '',
            'IMPORTE_VALOR_DECLARADO' => 5000,
            'TIPO_MONEDA' => 'COP'
        ];
        $res = $this->deprisa->liquidation($params);
        $this->assertArrayHasKey('RESPUESTA_COTIZACION', $res);
    }

    public function testAdmission()
    {
        $params = [
            'GRABAR_ENVIO' => 'S',
            'CODIGO_ADMISION' => '123',
            'NUMERO_ENVIO' => '',
            'NUMERO_BULTOS' => 2,
            'NOMBRE_REMITENTE' => '',
            'DIRECCION_REMITENTE' => '',
            'PAIS_REMITENTE' => '057',
            'CODIGO_POSTAL_REMITENTE' => '',
            'POBLACION_REMITENTE' => '',
            'TIPO_DOC_REMITENTE' => '',
            'DOCUMENTO_IDENTIDAD_REMITENTE' => '',
            'TELEFONO_CONTACTO_REMITENTE' => '',
            'DEPARTAMENTO_REMITENTE' => '',
            'EMAIL_REMITENTE' => '',
            'CLIENTE_DESTINATARIO' => '99999999',
            'CENTRO_DESTINATARIO' => '99',
            'NOMBRE_DESTINATARIO' => 'Pedro Perez',
            'DIRECCION_DESTINATARIO' => 'calle 50 N 3-23',
            'PAIS_DESTINATARIO' => '057',
            'CODIGO_POSTAL_DESTINATARIO' => '054040',
            'POBLACION_DESTINATARIO' => 'RIONEGRO',
            'TIPO_DOC_DESTINATARIO' => 'CC',
            'DOCUMENTO_IDENTIDAD_DESTINATARIO' => '73082468',
            'PERSONA_CONTACTO_DESTINATARIO' => 'Raul Reyes',
            'TELEFONO_CONTACTO_DESTINATARIO' => '3127534562',
            'DEPARTAMENTO_DESTINATARIO' => 'ANTIOQUIA',
            'EMAIL_DESTINATARIO' => 'leireoo@gmail.com',
            'INCOTERM' => '',
            'RAZON_EXPORTAR' => '',
            'EMBALAJE' => '',
            'CODIGO_SERVICIO' => '3005',
            'KILOS' => 4,
            'VOLUMEN' => '',
            'LARGO' => 10,
            'ANCHO' => 20,
            'ALTO' => 15,
            'NUMERO_REFERENCIA' => time(),
            'IMPORTE_REEMBOLSO' => '',
            'IMPORTE_VALOR_DECLARADO' => 1000,
            'TIPO_PORTES' => 'P',
            'OBSERVACIONES1' => 'Prueba de grabación en WEEX',
            'TIPO_MERCANCIA' => '',
            'ASEGURAR_ENVIO' => 'S',
            'TIPO_MONEDA' => 'COP',
            /*'BULTOS_ADMISION' => [
                'BULTO' => [
                    'REFERENCIA_BULTO_CLIENTE' => '111111',
                    'TIPO_BULTO' => '1425',
                    'LARGO' => 19,
                    'ANCHO' => 39,
                    'ALTO' => 29,
                    'VOLUMEN' => 9,
                    'KILOS' => 9,
                    'OBSERVACIONES' => 'obser bulto',
                    'CODIGO_BARRAS_CLIENTE' => '4534534534534'
                ]
            ]*/
        ];
        $res = $this->deprisa->admission($params);
        $this->assertTrue(key_exists('ADMISIONES', $res));
    }

    public function testLabels()
    {
        $labels = [];
        $labels['ETIQUETA'] = [
        'NUMERO_ENVIO' => '999061176505',
        'TIPO_IMPRESORA' => 'L' //láser
        ];
        $labels['ETIQUETA'] = [
            'NUMERO_ENVIO' => '999061176505',
            'TIPO_IMPRESORA' => 'T'
        ];

        $res = $this->deprisa->labels($labels);
    }

    public function testTracking()
    {
        $tracking = "999048263154";
        $res = $this->deprisa->tracking($tracking);
        $this->assertArrayHasKey('NUMERO_ENVIO', $res);
    }
}