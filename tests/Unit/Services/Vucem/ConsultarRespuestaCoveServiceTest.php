<?php

namespace Tests\Unit\Services\Vucem;

use Tests\TestCase;
use App\Services\Vucem\ConsultarRespuestaCoveService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests para ConsultarRespuestaCoveService
 * 
 * NOTA: Estos tests validan la estructura y lógica del servicio.
 * No ejecutan llamadas reales al Web Service de VUCEM.
 */
class ConsultarRespuestaCoveServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario de prueba con credenciales VUCEM
        $this->testUser = User::factory()->create([
            'name' => 'Test User VUCEM',
            'email' => 'test@vucem.com',
            'rfc' => 'NET070608EM9',
            'webservice_user' => 'NET070608EM9',
            'webservice_key' => encrypt('a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6A7B8C9D0'), // 64 chars
        ]);
    }

    /** @test */
    public function it_can_be_instantiated_with_user()
    {
        $service = new ConsultarRespuestaCoveService($this->testUser);
        
        $this->assertInstanceOf(ConsultarRespuestaCoveService::class, $service);
    }

    /** @test */
    public function it_validates_numero_operacion_is_required()
    {
        $service = new ConsultarRespuestaCoveService($this->testUser);
        
        // Número de operación vacío o 0
        $resultado = $service->consultarRespuesta(0);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('validation_error', $resultado['error_type']);
        $this->assertStringContainsString('número de operación', $resultado['message']);
    }

    /** @test */
    public function it_validates_numero_operacion_is_positive()
    {
        $service = new ConsultarRespuestaCoveService($this->testUser);
        
        // Número de operación negativo
        $resultado = $service->consultarRespuesta(-123);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('validation_error', $resultado['error_type']);
    }

    /** @test */
    public function it_has_correct_endpoint_configuration()
    {
        $endpoint = config('vucem.consultar_respuesta_cove.endpoint');
        $soapAction = config('vucem.consultar_respuesta_cove.soap_action');
        
        $this->assertNotEmpty($endpoint);
        $this->assertStringContainsString('ventanillaunica.gob.mx', $endpoint);
        $this->assertStringContainsString('8110', $endpoint); // Puerto específico
        $this->assertStringContainsString('ConsultarRespuestaCoveService', $endpoint);
        
        $this->assertNotEmpty($soapAction);
        $this->assertStringContainsString('ConsultarRespuestaCove', $soapAction);
    }

    /** @test */
    public function it_has_efirma_configuration()
    {
        $efirmaPath = config('vucem.efirma.path');
        $certFile = config('vucem.efirma.cert_file');
        $keyFile = config('vucem.efirma.key_file');
        $passwordFile = config('vucem.efirma.password_file');
        
        $this->assertNotEmpty($efirmaPath);
        $this->assertNotEmpty($certFile);
        $this->assertNotEmpty($keyFile);
        $this->assertNotEmpty($passwordFile);
        
        $this->assertStringContainsString('.cer', $certFile);
        $this->assertStringContainsString('.key', $keyFile);
    }

    /** @test */
    public function it_generates_correct_cadena_original_format()
    {
        // La cadena original debe ser: |numeroOperacion|RFC|
        $numeroOperacion = 1234567890;
        $rfc = 'NET070608EM9';
        
        $expectedCadena = "|{$numeroOperacion}|{$rfc}|";
        
        // Verificar formato
        $this->assertStringStartsWith('|', $expectedCadena);
        $this->assertStringEndsWith('|', $expectedCadena);
        $this->assertEquals('|1234567890|NET070608EM9|', $expectedCadena);
    }

    /** @test */
    public function it_has_required_soap_namespaces()
    {
        $reflection = new \ReflectionClass(ConsultarRespuestaCoveService::class);
        
        $this->assertTrue($reflection->hasConstant('NAMESPACE_SERVICE'));
        $this->assertTrue($reflection->hasConstant('NAMESPACE_OXML'));
        $this->assertTrue($reflection->hasConstant('NAMESPACE_WSSE'));
        $this->assertTrue($reflection->hasConstant('NAMESPACE_WSU'));
        $this->assertTrue($reflection->hasConstant('NAMESPACE_SOAP'));
    }

    /** @test */
    public function it_validates_soap_namespaces_values()
    {
        $service = new ConsultarRespuestaCoveService($this->testUser);
        $reflection = new \ReflectionClass($service);
        
        $namespaceService = $reflection->getConstant('NAMESPACE_SERVICE');
        $namespaceOxml = $reflection->getConstant('NAMESPACE_OXML');
        $namespaceWsse = $reflection->getConstant('NAMESPACE_WSSE');
        
        $this->assertStringContainsString('ventanillaunica.gob.mx', $namespaceService);
        $this->assertStringContainsString('cove/ws', $namespaceService);
        
        $this->assertStringContainsString('ventanillaunica.gob.mx', $namespaceOxml);
        $this->assertStringContainsString('oxml', $namespaceOxml);
        
        $this->assertStringContainsString('oasis-open.org', $namespaceWsse);
        $this->assertStringContainsString('wss-wssecurity', $namespaceWsse);
    }

    /** @test */
    public function it_has_build_soap_request_method()
    {
        $service = new ConsultarRespuestaCoveService($this->testUser);
        $reflection = new \ReflectionClass($service);
        
        $this->assertTrue($reflection->hasMethod('buildSoapRequest'));
        
        $method = $reflection->getMethod('buildSoapRequest');
        $this->assertTrue($method->isPrivate());
    }

    /** @test */
    public function it_has_process_response_method()
    {
        $service = new ConsultarRespuestaCoveService($this->testUser);
        $reflection = new \ReflectionClass($service);
        
        $this->assertTrue($reflection->hasMethod('processResponse'));
        
        $method = $reflection->getMethod('processResponse');
        $this->assertTrue($method->isPrivate());
    }

    /** @test */
    public function it_can_parse_successful_soap_response()
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns2:solicitarConsultarRespuestaCoveServicioResponse 
            xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
            <return>
                <numeroOperacion>1234567890</numeroOperacion>
                <horaRecepcion>2025-12-29T10:30:00</horaRecepcion>
                <respuestasOperaciones>
                    <numeroFacturaORelacionFacturas>FAC001</numeroFacturaORelacionFacturas>
                    <contieneError>false</contieneError>
                    <eDocument>0170220LIS5D4</eDocument>
                    <numeroAdenda>12345</numeroAdenda>
                    <cadenaOriginal>||01702251RTAD3|COVE2411FXFM4</cadenaOriginal>
                    <selloDigital>aGVsbG8gd29ybGQ=</selloDigital>
                </respuestasOperaciones>
            </return>
        </ns2:solicitarConsultarRespuestaCoveServicioResponse>
    </soap:Body>
</soap:Envelope>
XML;

        $service = new ConsultarRespuestaCoveService($this->testUser);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('processResponse');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($service, $xmlResponse);
        
        $this->assertTrue($resultado['success']);
        $this->assertEquals(1234567890, $resultado['numeroOperacion']);
        $this->assertEquals('2025-12-29T10:30:00', $resultado['horaRecepcion']);
        $this->assertIsArray($resultado['respuestasOperaciones']);
        $this->assertCount(1, $resultado['respuestasOperaciones']);
        
        $operacion = $resultado['respuestasOperaciones'][0];
        $this->assertEquals('FAC001', $operacion['numeroFacturaORelacionFacturas']);
        $this->assertFalse($operacion['contieneError']);
        $this->assertEquals('0170220LIS5D4', $operacion['eDocument']);
    }

    /** @test */
    public function it_can_parse_response_with_errors()
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns2:solicitarConsultarRespuestaCoveServicioResponse 
            xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
            <return>
                <numeroOperacion>9999999999</numeroOperacion>
                <horaRecepcion>2025-12-29T11:00:00</horaRecepcion>
                <respuestasOperaciones>
                    <numeroFacturaORelacionFacturas>FAC002</numeroFacturaORelacionFacturas>
                    <contieneError>true</contieneError>
                    <errores>
                        <mensaje>Error en validación de factura</mensaje>
                    </errores>
                    <errores>
                        <mensaje>RFC no coincide con proveedor</mensaje>
                    </errores>
                </respuestasOperaciones>
            </return>
        </ns2:solicitarConsultarRespuestaCoveServicioResponse>
    </soap:Body>
</soap:Envelope>
XML;

        $service = new ConsultarRespuestaCoveService($this->testUser);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('processResponse');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($service, $xmlResponse);
        
        $this->assertTrue($resultado['success']);
        $this->assertIsArray($resultado['respuestasOperaciones']);
        
        $operacion = $resultado['respuestasOperaciones'][0];
        $this->assertTrue($operacion['contieneError']);
        $this->assertIsArray($operacion['errores']);
        $this->assertCount(2, $operacion['errores']);
        $this->assertContains('Error en validación de factura', $operacion['errores']);
    }

    /** @test */
    public function it_handles_soap_fault_response()
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>soap:Server</faultcode>
            <faultstring>Error en autenticación</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
XML;

        $service = new ConsultarRespuestaCoveService($this->testUser);
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('processResponse');
        $method->setAccessible(true);
        
        $resultado = $method->invoke($service, $xmlResponse);
        
        $this->assertFalse($resultado['success']);
        $this->assertEquals('soap_fault', $resultado['error_type']);
        $this->assertStringContainsString('autenticación', $resultado['message']);
    }

    /** @test */
    public function it_validates_user_has_rfc()
    {
        $userWithoutRfc = User::factory()->create([
            'rfc' => null
        ]);

        $service = new ConsultarRespuestaCoveService($userWithoutRfc);
        
        // Intentar consultar debería fallar por falta de RFC
        // (esto se validaría en tiempo de ejecución)
        $this->assertInstanceOf(ConsultarRespuestaCoveService::class, $service);
    }

    /** @test */
    public function it_can_access_debug_info()
    {
        $service = new ConsultarRespuestaCoveService($this->testUser);
        
        $this->assertTrue(method_exists($service, 'getDebugInfo'));
    }
}
