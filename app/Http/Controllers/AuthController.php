<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\DTOs\LoginRequestDTO;
use App\DTOs\RegisterUserDTO;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $dto = LoginRequestDTO::fromRequest($request->all());
        
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            return $this->errorResponse('Credenciales inválidas', 401);
        }

        // Generar token simple (30 minutos de expiración)
        $token = Str::random(60);
        $user->api_token = hash('sha256', $token);
        $user->save();

        return $this->successResponse([
            'token' => $token,
            'expires_in' => 1800, // 30 minutos en segundos
            'token_type' => 'Bearer'
        ], 'Login exitoso');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->api_token = null;
        $user->save();

        return $this->successResponse(null, 'Sesión cerrada correctamente');
    }

    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8'
    ]);

    $dto = RegisterUserDTO::fromRequest($request->all());
    
    $user = $this->userRepository->create([
        'name' => $dto->name,
        'email' => $dto->email,
        'password' => bcrypt($dto->password)
    ]);

    // Generar token automáticamente
    $token = Str::random(60);
    $user->api_token = hash('sha256', $token);
    $user->save();

    return $this->successResponse([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ],
        'token' => $token,
        'expires_in' => 1800,
        'token_type' => 'Bearer'
    ], 'Usuario registrado exitosamente', 201);
}
}
