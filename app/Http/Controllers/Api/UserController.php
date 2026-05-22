<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PdfService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Mostrar listado de usuarios
     */
    public function index()
    {
        $users = User::select(
                'id',
                'name',
                'nickname',
                'email',
                'games_played',
                'games_won',
                'times_impostor',
                'role_user'
            )
            ->paginate(10);

        return response()->json($users);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(Request $request)
    {
        Log::info('STORE USER REQUEST', $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_user' => 'user',
            'games_played' => 0,
            'games_won' => 0,
            'times_impostor' => 0,
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $user,
        ], 201);
    }

    /**
     * Mostrar usuario concreto
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    // RANKING
    public function ranking()
    {
        $users = User::orderByDesc('games_won')
            ->take(16)
            ->get();

        return response()->json($users);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'nickname' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
        ]);

        $user->update($request->only([
            'name',
            'nickname',
            'email',
            'games_played',
            'games_won',
            'times_impostor',
        ]));

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
            $user->save();
        }

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $user,
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
        ]);
    }

     /**
     * Imprimir info de usuario
     */
    public function profilePdf(Request $request, PdfService $pdfService)
    {
        $user_profile = $request->user();
        $pdf = $pdfService->render('pdf.user_profile', compact('user_profile'));
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="perfil_el_impostor.pdf"');
    }
}