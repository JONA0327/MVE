<?php

namespace App\Http\Controllers;

use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Mostrar la página de gestión de certificados
     */
    public function index()
    {
        $user = Auth::user();
        $certificateInfo = $this->certificateService->getCertificateInfo($user);

        return view('certificates.index', [
            'user' => $user,
            'certificateInfo' => $certificateInfo,
            'hasCertificates' => $user->hasFielCertificates()
        ]);
    }

    /**
     * Cargar certificados desde archivos
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificate_file' => 'required|file|max:2048|mimes:cer',
            'private_key_file' => 'required|file|max:2048',
            'password' => 'required|string|min:1',
        ], [
            'certificate_file.required' => 'Debe seleccionar el archivo del certificado (.cer)',
            'certificate_file.mimes' => 'El certificado debe ser un archivo .cer',
            'private_key_file.required' => 'Debe seleccionar el archivo de la llave privada (.key)',
            'password.required' => 'Debe ingresar la contraseña de la llave privada',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        
        // Validar que el archivo de llave tenga extensión .key
        $keyFile = $request->file('private_key_file');
        if (strtolower($keyFile->getClientOriginalExtension()) !== 'key') {
            return redirect()->back()
                ->withErrors(['private_key_file' => 'El archivo de la llave privada debe tener extensión .key'])
                ->withInput();
        }

        $result = $this->certificateService->uploadCertificatesToUser(
            $user,
            $request->file('certificate_file'),
            $request->file('private_key_file'),
            $request->input('password')
        );

        if ($result['success']) {
            return redirect()->route('certificates.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->withErrors(['upload' => $result['message']])
                ->withInput();
        }
    }

    /**
     * Eliminar certificados del sistema
     */
    public function remove()
    {
        $user = Auth::user();
        $this->certificateService->removeCertificatesFromUser($user);

        return redirect()->route('certificates.index')
            ->with('success', 'Certificados eliminados exitosamente del sistema');
    }

    /**
     * Cambiar el modo de certificados (sistema vs manual)
     */
    public function toggleMode(Request $request)
    {
        $user = Auth::user();
        $useSystem = $request->input('use_system', false);

        // Solo permitir modo sistema si tiene certificados cargados
        if ($useSystem && !$user->hasFielCertificates()) {
            return redirect()->back()
                ->withErrors(['mode' => 'No puede usar certificados del sistema porque no ha cargado ninguno']);
        }

        $user->use_system_certificates = $useSystem;
        $user->save();

        $message = $useSystem 
            ? 'Ahora se usarán los certificados del sistema para firmar' 
            : 'Ahora se pedirán los certificados manualmente para firmar';

        return redirect()->route('certificates.index')
            ->with('success', $message);
    }

    /**
     * Obtener información del certificado via AJAX
     */
    public function getCertificateInfo()
    {
        $user = Auth::user();
        $info = $this->certificateService->getCertificateInfo($user);

        return response()->json([
            'success' => $info !== null,
            'data' => $info
        ]);
    }

    /**
     * Verificar si el usuario puede firmar (tiene certificados o prefiere manual)
     */
    public function checkSigningCapability()
    {
        $user = Auth::user();
        
        $canSign = $user->use_system_certificates 
            ? $user->hasFielCertificates() 
            : true; // Si prefiere manual, siempre puede firmar

        return response()->json([
            'can_sign' => $canSign,
            'use_system' => $user->use_system_certificates,
            'has_certificates' => $user->hasFielCertificates(),
            'certificate_info' => $user->hasFielCertificates() 
                ? $this->certificateService->getCertificateInfo($user) 
                : null
        ]);
    }
}