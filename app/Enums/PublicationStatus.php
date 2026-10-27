<?php

namespace App\Enums;

/**
 * Modelo de publicação comum a páginas, notícias, eventos, documentos,
 * cursos, unidades orgânicas e itens de galeria.
 */
enum PublicationStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Pending => 'Em revisão',
            self::Scheduled => 'Agendado',
            self::Published => 'Publicado',
            self::Archived => 'Arquivado',
        };
    }

    /** Classe do componente .s-badge (Universidades.ao) */
    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'off',
            self::Pending => 'wait',
            self::Scheduled => 'info',
            self::Published => 'ok',
            self::Archived => 'no',
        };
    }

    /** Estados que um utilizador sem permissão de publicação pode atribuir. */
    public static function nonPublishing(): array
    {
        return [self::Draft, self::Pending];
    }

    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    public function isPublic(): bool
    {
        return in_array($this, [self::Published, self::Scheduled], true);
    }
}
