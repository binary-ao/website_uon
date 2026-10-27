<?php

namespace App\Support;

use App\Models\User;

/**
 * Catálogo de permissões e atribuição por omissão às funções.
 * O superadministrador tem acesso global (Gate::before).
 */
class Permissions
{
    /** Módulos de conteúdo com fluxo editorial. */
    public const CONTENT_MODULES = [
        'pages' => 'Páginas',
        'news' => 'Notícias e comunicados',
        'events' => 'Eventos',
        'documents' => 'Documentos',
        'courses' => 'Cursos',
        'units' => 'Unidades orgânicas',
        'gallery' => 'Galeria e multimédia',
    ];

    public const CONTENT_ACTIONS = [
        'create' => 'Criar e editar os próprios rascunhos',
        'edit' => 'Editar qualquer conteúdo',
        'publish' => 'Publicar, agendar e despublicar',
        'delete' => 'Arquivar e eliminar',
    ];

    public const GLOBAL = [
        'media.upload' => 'Carregar ficheiros na biblioteca',
        'media.manage' => 'Editar e eliminar ficheiros da biblioteca',
        'banners.manage' => 'Gerir o banner (carrossel) da página inicial',
        'menus.manage' => 'Gerir menus',
        'settings.manage' => 'Gerir Header, Footer e configurações globais',
        'taxonomies.manage' => 'Gerir categorias, etiquetas e graus académicos',
        'notices.manage' => 'Gerir avisos da página inicial',
        'partners.manage' => 'Gerir parceiros',
        'redirects.manage' => 'Gerir redireccionamentos',
        'messages.manage' => 'Ler mensagens de contacto',
        'activity.view' => 'Consultar o registo de actividade',
        'users.manage' => 'Gerir utilizadores',
        'roles.manage' => 'Gerir permissões das funções',
    ];

    /** Permissões que só o superadministrador pode atribuir (protecção contra escalada). */
    public const SUPERADMIN_ONLY = ['users.manage', 'roles.manage'];

    public static function all(): array
    {
        $out = [];
        foreach (array_keys(self::CONTENT_MODULES) as $m) {
            foreach (array_keys(self::CONTENT_ACTIONS) as $a) {
                $out[] = "$m.$a";
            }
        }

        return array_merge($out, array_keys(self::GLOBAL));
    }

    public static function label(string $permission): string
    {
        if (isset(self::GLOBAL[$permission])) {
            return self::GLOBAL[$permission];
        }
        [$m, $a] = explode('.', $permission) + [null, null];

        return (self::CONTENT_MODULES[$m] ?? $m).' — '.(self::CONTENT_ACTIONS[$a] ?? $a);
    }

    public static function defaultsForRoles(): array
    {
        $content = fn (array $modules, array $actions) => collect($modules)
            ->flatMap(fn ($m) => array_map(fn ($a) => "$m.$a", $actions))->all();
        $modules = array_keys(self::CONTENT_MODULES);

        return [
            User::ROLE_SUPERADMIN => self::all(),
            User::ROLE_ADMIN => array_values(array_diff(self::all(), self::SUPERADMIN_ONLY)),
            User::ROLE_EDITOR => array_merge(
                $content($modules, ['create', 'edit', 'publish']),
                ['media.upload', 'notices.manage', 'banners.manage']
            ),
            User::ROLE_AUTHOR => array_merge(
                $content(['news', 'events', 'documents'], ['create']),
                ['media.upload']
            ),
        ];
    }
}
