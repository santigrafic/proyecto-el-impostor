<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'games_played' => $this->games_played,
            'games_won' => $this->games_won,
            'win_rate' => $this->games_played > 0
                ? round(($this->games_won / $this->games_played) * 100)
                : 0,
        ];
    }
}
