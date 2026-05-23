<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PdfService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\RankingResource;

class UserController extends Controller
{
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

    public function me(Request $request)
    {
        return response()->json([
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'nickname' => $request->user()->nickname,
            'gamesPlayed' => $request->user()->games_played,
            'gamesWon' => $request->user()->games_won,
            'timesImpostor' => $request->user()->times_impostor,
            'isPremium' => $request->user()->subscribed('premium'),
        ]);
    }

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

        // return RankingResource::collection($users);
        return response()->json([
            'data' => RankingResource::collection($users),
            'meta' => [
            'total' => $users->count()
            ]
        ]);
    }

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

    public function update(UpdateUserRequest $request)
    {
        $user = $request->user();

        $user->update($request->only([
            'name',
            'nickname',
            'email',
        ]));

        $user->isPremium = $user->subscribed('premium');

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $user,
        ]);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
        ]);
    }

    // Imprimir info de usuario
    public function profilePdf(Request $request, PdfService $pdfService)
    {
        $user_profile = $request->user();
        $pdf = $pdfService->render('pdf.user_profile', compact('user_profile'));
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="perfil_el_impostor.pdf"');
    }
}
