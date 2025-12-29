<?php

namespace Tests\Unit\Vucem;

use PHPUnit\Framework\TestCase;
use App\Services\Vucem\ConsultarEdocumentService;
use SoapClient;
use Mockery;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ConsultarEdocumentServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function puede_consultar_edocument_exitosamente()
    {
        // Simular usuario autenticado (mock parcial para Eloquent)
        $mockUser = Mockery::mock(User::class)->makePartial();
        $mockUser->rfc = 'RFC123456789';
        $mockUser->shouldReceive('getDecryptedWebserviceKey')->andReturn('password123');
        // Permitir métodos Eloquent comunes para evitar errores
        $mockUser->shouldIgnoreMissing();
        Auth::shouldReceive('user')->andReturn($mockUser);

        // Simular respuesta exitosa del servicio SOAP
        $mockResponse = (object) [
            'contieneError' => false,
            'mensaje' => 'Consulta exitosa',
            'resultadoBusqueda' => (object) [
                'cove' => (object) [
                    'eDocument' => 'EDOC789',
                    'tipoOperacion' => 'IMPORT',
                    'numeroFacturaRelacionFacturas' => 'FAC-123456',
                    'relacionFacturas' => null,
                    'automotriz' => null
                ]
            ]
        ];

        // Mock del SoapClient
        $mockSoapClient = Mockery::mock(SoapClient::class);
        $mockSoapClient->shouldReceive('__soapCall')
            ->with('ConsultarEdocument', Mockery::any(), Mockery::any())
            ->andReturn($mockResponse);
        $mockSoapClient->shouldReceive('__getLastRequest')->andReturn('request');
        $mockSoapClient->shouldReceive('__getLastResponse')->andReturn('response');
        $mockSoapClient->shouldReceive('__getLastRequestHeaders')->andReturn('headers');
        $mockSoapClient->shouldReceive('__getLastResponseHeaders')->andReturn('headers');

        // Mock parcial del servicio para inyectar el SoapClient mock
        $service = Mockery::mock(ConsultarEdocumentService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();
        $reflection = new \ReflectionClass($service);
        $soapClientProperty = $reflection->getProperty('soapClient');
        $soapClientProperty->setAccessible(true);
        $soapClientProperty->setValue($service, $mockSoapClient);
        $efirmaServiceProperty = $reflection->getProperty('efirmaService');
        $efirmaServiceProperty->setAccessible(true);
        $efirmaServiceProperty->setValue($service, Mockery::mock());

        $result = $service->consultarEdocument('EDOC789');

        $this->assertTrue($result['success']);
        $this->assertEquals('Consulta exitosa', $result['message']);
        $this->assertEquals('EDOC789', $result['cove_data']['eDocument']);
    }
}
