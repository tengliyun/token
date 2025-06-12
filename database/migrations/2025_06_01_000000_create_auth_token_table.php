<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function __construct()
    {
        $this->connection = config('token.connection');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('token.table', 'auth_tokens'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('Token Name');
            $table->string('package')->comment('Package-bound token types');
            $table->morphs('tokenable', 'idx_tokenable');
            $table->text('access_token')->nullable()->comment('Access Token');
            $table->text('refresh_token')->nullable()->comment('Refresh Token');
            $table->text('scopes')->comment('Token Scopes');
            $table->timestamp('access_token_expire_at')->comment('Token expiration time');
            $table->timestamp('refresh_token_expire_at')->comment('Refresh token expiration time');
            $table->timestamp('last_used_at')->nullable()->comment('Last used time');
            $table->timestamp('created_at')->comment('Created Time');
            $table->timestamp('updated_at')->comment('Updated Time');
            $table->timestamp('deleted_at')->nullable()->comment('Deleted Time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('token.table', 'auth_tokens'));
    }
};
