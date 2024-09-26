<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (isset($_ENV['CACHE_STORE'])) {
            return;
        }

        throw new Exception('CACHE_STORE env variable is not set, see .env.example (change CACHE_DRIVER to CACHE_STORE)');
    }
};
