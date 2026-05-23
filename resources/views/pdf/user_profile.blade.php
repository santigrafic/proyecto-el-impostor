@extends('pdf.layout')

@section('title', 'Perfil de Usuario')

@section('content')

    <div class="profile-card">

        <div class="logo">
            <img src="{{ public_path('images/impostor-logo.png') }}">
        </div>

        <div class="title">
            PERFIL DE USUARIO
        </div>

        <div class="terminal-box">

            <table class="data-table">

                <tr>
                    <td class="label">Nombre</td>
                    <td class="value">{{ $user_profile->name }}</td>
                </tr>

                <tr>
                    <td class="label">Nickname</td>
                    <td class="value">{{ $user_profile->nickname }}</td>
                </tr>

                <tr>
                    <td class="label">Email</td>
                    <td class="value">{{ $user_profile->email }}</td>
                </tr>

            </table>

        </div>

        <div class="stats-section">

            <div style="
                font-size: 16px;
                margin-bottom: 15px;
                text-transform: uppercase;
                font-weight: bold;
            ">
                Estadísticas
            </div>

            <table class="data-table">

                <tr>
                    <td class="label">Partidas jugadas</td>
                    <td class="value">{{ $user_profile->games_played }}</td>
                </tr>

                <tr>
                    <td class="label">Partidas ganadas</td>
                    <td class="value">{{ $user_profile->games_won }}</td>
                </tr>

                <tr>
                    <td class="label">Veces siendo El Impostor</td>
                    <td class="value">{{ $user_profile->times_impostor }}</td>
                </tr>

            </table>

        </div>

        <div class="footer-text">
            EL IMPOSTOR SYSTEM TERMINAL v1.0
        </div>

    </div>

@endsection