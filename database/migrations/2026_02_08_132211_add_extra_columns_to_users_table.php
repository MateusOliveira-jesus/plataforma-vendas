<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Para controle de acesso/admin
            $table->boolean('is_admin')->default(false)->after('password');
            $table->boolean('is_active')->default(true)->after('is_admin');
            
            // Para perfil do usuário
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('cpf_cnpj', 20)->nullable()->after('avatar')->unique();
            $table->date('birth_date')->nullable()->after('cpf_cnpj');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('birth_date');
            
            // Para endereço
            $table->string('street')->nullable()->after('gender');
            $table->string('number')->nullable()->after('street');
            $table->string('complement')->nullable()->after('number');
            $table->string('neighborhood')->nullable()->after('complement');
            $table->string('city')->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('zip_code', 10)->nullable()->after('state');
            
            // Para auditoria
            $table->timestamp('last_login_at')->nullable()->after('zip_code');
            $table->ipAddress('last_login_ip')->nullable()->after('last_login_at');
            $table->softDeletes(); // Para soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_admin',
                'is_active',
                'phone',
                'avatar',
                'cpf_cnpj',
                'birth_date',
                'gender',
                'street',
                'number',
                'complement',
                'neighborhood',
                'city',
                'state',
                'zip_code',
                'last_login_at',
                'last_login_ip'
            ]);
            $table->dropSoftDeletes();
        });
    }
};