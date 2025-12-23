<?php

namespace Tests\Unit\Vucem;

use PHPUnit\Framework\TestCase;
use App\Services\Vucem\ConsultarCoveService;
use SoapClient;
use SoapFault;
use Mockery;

class ConsultarCoveServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function puede_consultar_cove_exitosamente()
    {
        // Simular respuesta exitosa del servicio SOAP
        $mockResponse = (object) [
            'respuestasOperaciones' => (object) [
                'contieneError' => false,
                'numeroFacturaORelacionFacturas' => 'FAC-123456',
                'eDocument' => 'EDOC789',
                'cadenaOriginal' => 'cadena_original_test',
                'selloDigital' => 'sello_digital_test'
            ],
            'horaRecepcion' => '2025-12-22T10:30:00'
        ];

        // Mock del SoapClient
        $mockSoapClient = Mockery::mock(SoapClient::class);
        $mockSoapClient->shouldReceive('__soapCall')
            ->with('ConsultarRespuestaCove', Mockery::any(), Mockery::any())
            ->andReturn($mockResponse);
        $mockSoapClient->shouldReceive('__setSoapHeaders')->once();

        // Mock parcial del servicio para inyectar el SoapClient mock
        $service = Mockery::mock(ConsultarCoveService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();

        // Simular que el servicio tiene las credenciales configuradas
        $reflection = new \ReflectionClass($service);
        $usernameProperty = $reflection->getProperty('username');
        $usernameProperty->setAccessible(true);
        $usernameProperty->setValue($service, 'RFC123456789');

        $passwordProperty = $reflection->getProperty('password');
        $passwordProperty->setAccessible(true);
        $passwordProperty->setValue($service, 'password123');

        $soapClientProperty = $reflection->getProperty('soapClient');
        $soapClientProperty->setAccessible(true);
        $soapClientProperty->setValue($service, $mockSoapClient);

        $result = $service->consultarCove('COVE123');

        $this->assertTrue($result['success']);
        $this->assertEquals('COVE123', $result['data']['cove']);
        $this->assertEquals('FAC-123456', $result['data']['numero_factura']);
        $this->assertEquals('EDOC789', $result['data']['edocument']);
    }

    /** @test */
    public function valida_credenciales_faltantes()
    {
        $service = Mockery::mock(ConsultarCoveService::class)->makePartial();

        // Configurar credenciales vacías
        $reflection = new \ReflectionClass($service);
        $usernameProperty = $reflection->getProperty('username');
        $usernameProperty->setAccessible(true);
        $usernameProperty->setValue($service, '');

        $passwordProperty = $reflection->getProperty('password');
        $passwordProperty->setAccessible(true);
        $passwordProperty->setValue($service, '');

        $result = $service->consultarCove('COVE123');

        $this->assertFalse($result['success']);
        $this->assertEquals('config_error', $result['error_type']);
        $this->assertStringContainsString('Credenciales VUCEM no configuradas', $result['message']);
    }

    /** @test */
    public function valida_folio_cove_vacio()
    {
        $service = Mockery::mock(ConsultarCoveService::class)->makePartial();

        // Configurar credenciales válidas
        $reflection = new \ReflectionClass($service);
        $usernameProperty = $reflection->getProperty('username');
        $usernameProperty->setAccessible(true);
        $usernameProperty->setValue($service, 'RFC123456789');

        $passwordProperty = $reflection->getProperty('password');
        $passwordProperty->setAccessible(true);
        $passwordProperty->setValue($service, 'password123');

        $result = $service->consultarCove('');

        $this->assertFalse($result['success']);
        $this->assertEquals('validation_error', $result['error_type']);
        $this->assertStringContainsString('El folio de COVE es requerido', $result['message']);
    }
}
