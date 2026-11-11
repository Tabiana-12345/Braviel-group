<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->nullable()->unique()->after('name');
        });

        DB::table('users')->whereNull('username')->orderBy('id')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                $base = Str::slug(Str::before($user->email, '@'), '_');
                if ($base === '') {
                    $base = 'user_'.$user->id;
                }
                $username = $base;
                while (DB::table('users')->where('username', $username)->exists()) {
                    $username = $base.'_'.$user->id;
                    $base = $username;
                }

                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
