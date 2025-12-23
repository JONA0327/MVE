<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Services\Vucem\ConsultarCoveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class CoveControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario de prueba
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'rfc' => 'TEST123456789'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function puede_consultar_cove_exitosamente()
    {
        // Mock del servicio ConsultarCoveService
        $mockService = Mockery::mock(ConsultarCoveService::class);
        $mockService->shouldReceive('consultarCove')
            ->with('COVE123')
            ->andReturn([
                'success' => true,
                'data' => [
                    'cove' => 'COVE123',
                    'metodo_valoracion' => '1',
                    'numero_factura' => 'FAC-123456',
                    'fecha_expedicion' => '2025-12-22',
                    'emisor' => 'Test Emisor',
                    'edocument' => 'EDOC789'
                ]
            ]);

        $this->app->instance(ConsultarCoveService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->postJson('/api/coves/consultar', [
                'cove' => 'COVE123'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'COVE consultado exitosamente',
                'data' => [
                    'cove' => 'COVE123',
                    'metodo_valoracion' => '1',
                    'numero_factura' => 'FAC-123456',
                    'fecha_expedicion' => '2025-12-22',
                    'emisor' => 'Test Emisor'
                ]
            ]);
    }

    /** @test */
    public function maneja_cove_no_encontrado()
    {
        $mockService = Mockery::mock(ConsultarCoveService::class);
        $mockService->shouldReceive('consultarCove')
            ->with('COVE_INEXISTENTE')
            ->andReturn([
                'success' => false,
                'message' => 'El COVE no existe o no está asociado al RFC configurado',
                'error_type' => 'cove_not_found'
            ]);

        $this->app->instance(ConsultarCoveService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->postJson('/api/coves/consultar', [
                'cove' => 'COVE_INEXISTENTE'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error_type' => 'cove_not_found'
            ]);
    }

    /** @test */
    public function valida_folio_cove_requerido()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/coves/consultar', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Datos de entrada inválidos'
            ]);
    }

    /** @test */
    public function valida_formato_folio_cove()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/coves/consultar', [
                'cove' => 'COVE@#$%' // Formato inválido
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cove']);
    }

    /** @test */
    public function requiere_autenticacion()
    {
        $response = $this->postJson('/api/coves/consultar', [
            'cove' => 'COVE123'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function maneja_error_de_configuracion()
    {
        $mockService = Mockery::mock(ConsultarCoveService::class);
        $mockService->shouldReceive('consultarCove')
            ->andReturn([
                'success' => false,
                'message' => 'Credenciales VUCEM no configuradas',
                'error_type' => 'config_error'
            ]);

        $this->app->instance(ConsultarCoveService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->postJson('/api/coves/consultar', [
                'cove' => 'COVE123'
            ]);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'error_type' => 'config_error'
            ]);
    }
}
