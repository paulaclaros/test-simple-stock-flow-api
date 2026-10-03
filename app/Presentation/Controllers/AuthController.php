<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\LoginDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\RegisterUserUseCase;
use App\Domain\Exceptions\UnauthorizedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController
{
    public function __construct(
        private readonly LoginUseCase $loginUseCase,
        private readonly RegisterUserUseCase $registerUserUseCase
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');

        if (trim($username) === '' || trim($password) === '') {
            return response()->json([
                'title' => 'Datos inválidos',
                'status' => 400,
                'detail' => 'Nombre de usuario y contraseña son requeridos.',
            ], 400, ['Content-Type' => 'application/problem+json']);
        }

        try {
            $result = $this->loginUseCase->execute(new LoginDTO($username, $password));

            return response()->json([
                'accessToken' => $result->accessToken,
                'tokenType' => $result->tokenType,
                'expiresIn' => $result->expiresIn,
                'user' => $result->user,
            ], 200);
        } catch (UnauthorizedException $e) {
            // CA-07.2: Rechazo que no revela si falló usuario o contraseña
            return response()->json([
                'title' => 'No autorizado',
                'status' => 401,
                'detail' => $e->getMessage(),
            ], 401, ['Content-Type' => 'application/problem+json']);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        $fullName = (string) $request->input('fullName', '');
        $role = (string) $request->input('role', '');

        if (trim($username) === '' || trim($password) === '' || trim($fullName) === '' || trim($role) === '') {
            return response()->json([
                'title' => 'Datos incompletos',
                'status' => 400,
                'detail' => 'Todos los campos son obligatorios.',
            ], 400, ['Content-Type' => 'application/problem+json']);
        }

        $userId = $this->registerUserUseCase->execute(new RegisterUserDTO(
            username: $username,
            password: $password,
            fullName: $fullName,
            role: $role
        ));

        // D-C6: 201 Created con {"id": "<uuid>"} y sin cabecera Location
        return response()->json(['id' => $userId], 201);
    }
}
