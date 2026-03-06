<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\DTOs\LoginRequestDTO;
use App\DTOs\RegisterUserDTO;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Log;

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
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            $dto = LoginRequestDTO::fromRequest($request->all());
            
            $user = $this->userRepository->findByEmail($dto->email);

            if (!$user || !Hash::check($dto->password, $user->password)) {
                return $this->errorResponse('Invalid Credentials', 401);
            }

            $token = Str::random(60);
            $user->api_token = hash('sha256', $token);
            $user->save();

            return $this->successResponse([
                'token' => $token,
                'expires_in' => 1800,
                'token_type' => 'Bearer'
            ], 'Login success');
            
        } catch (ValidationException $e) {
            Log::warning('Invalid login data', [
                'errors' => $e->errors(),
                'email' => $request->email
            ]);
            return $this->errorResponse('Invalid login data', 422, $e->errors());
            
        } catch (QueryException $e) {
            Log::error('Error connection to DB in login: ' . $e->getMessage(), [
                'email' => $request->email
            ]);
            return $this->errorResponse('Error servidor', 500);
            
        } catch (\Exception $e) {
            Log::error('unexpected error in login: ' . $e->getMessage(), [
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Error Servidor', 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $user->api_token = null;
            $user->save();

            return $this->successResponse(null, 'Logout success');
            
        } catch (\Exception $e) {
            Log::error('unexpected error in logout : ' . $e->getMessage());
            return $this->errorResponse('error close session', 500);
        }
    }

    public function register(Request $request)
    {
        try {
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
            ], 'User created', 201);
            
        } catch (ValidationException $e) {
            Log::warning('Invalid register data', [
                'errors' => $e->errors(),
                'email' => $request->email
            ]);
            return $this->errorResponse('Invalid register data', 422, $e->errors());
            
        } catch (QueryException $e) {
            Log::error('Error connection to DB in register: ' . $e->getMessage(), [
                'email' => $request->email
            ]);
            
            if ($e->getCode() == 23000) {
                return $this->errorResponse('Email already exists', 409);
            }
            
            return $this->errorResponse('Error servidor', 500);
            
        } catch (\Exception $e) {
            Log::error('unexpected error in register: ' . $e->getMessage(), [
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Error Servidor', 500);
        }
    }
}