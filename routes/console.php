<?php

use Illuminate\Support\Facades\Schedule;

/*
| Tarefas agendadas. Requer no servidor:
|   Linux (cron):   * * * * * cd /caminho/uon_edu && php artisan schedule:run >> /dev/null 2>&1
|   Windows (Agendador de Tarefas): executar a cada minuto
|       C:\xampp\php\php.exe C:\xampp\htdocs\uon_edu\artisan schedule:run
| Ver docs/INSTALACAO.md.
*/

Schedule::command('content:publish-scheduled')->everyFiveMinutes()->withoutOverlapping();

// Limpeza de tokens de recuperação de palavra-passe expirados.
Schedule::command('auth:clear-resets')->daily();
