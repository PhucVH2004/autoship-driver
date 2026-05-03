<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Đăng ký command classes trong thư mục app/Console/Commands.
     *
     * Laravel sẽ tự discovery/resolve các command nằm ở đây.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}

