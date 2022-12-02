<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database schema.
     *
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = Schema::connection($this->getConnection());
    }

    /**
     * Get the migration connection name.
     *
     * @return string|null
     */
    public function getConnection()
    {
        return config('spyglass.storage.database.connection');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('spyglass_entries', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->string('type', 20);
            $table->longText('content');

            $table->binary('prof_data')->nullable();
            $table->integer('pmu')->nullable();
            $table->integer('wt')->nullable();
            $table->integer('cpu')->nullable();

            $table->dateTime('created_at')->nullable();

//            $table->char('aggregateCalls_include', 255)->nullable();

            $table->unique('uuid');
            $table->index(['type', 'created_at']);
            $table->index('cpu');
            $table->index('wt');
            $table->index('pmu');
        });

        if(DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE spyglass_entries MODIFY COLUMN `prof_data` LONGBLOB');
        }

        $this->schema->create('spyglass_entries_tags', function (Blueprint $table) {
            $table->uuid('entry_uuid');
            $table->string('tag');

            $table->index(['entry_uuid', 'tag']);
            $table->index('tag');

            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('spyglass_entries')
                ->onDelete('cascade');
        });

        $this->schema->create('spyglass_monitoring', function (Blueprint $table) {
            $table->string('tag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('spyglass_entries');
    }
};
