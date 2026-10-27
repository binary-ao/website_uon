<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_url', 'status_code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_hit_at' => 'datetime'];
    }

    public static function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?? $path;

        return '/'.trim(rawurldecode($path), '/');
    }

    /**
     * Cria (ou actualiza) um redireccionamento automático quando um URL muda,
     * evitando cadeias e ciclos.
     */
    public static function remember(string $from, string $to): void
    {
        $from = static::normalizePath($from);
        $toPath = static::normalizePath($to);
        if ($from === $toPath || $from === '/') {
            return;
        }
        // Se o novo destino tinha um redireccionamento a partir dele, remove-o (evita ciclos).
        static::where('from_path', $toPath)->delete();
        // Redireccionamentos que apontavam para o URL antigo passam a apontar para o novo.
        static::where('to_url', $from)->update(['to_url' => $toPath]);
        static::updateOrCreate(['from_path' => $from], ['to_url' => $toPath, 'status_code' => 301, 'is_active' => true]);
    }
}
