<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateOpenDentalMigrations extends Command
{
    protected $signature = 'opendental:generate-migrations';

    protected $description = 'Generate Laravel migrations from Open Dental XML schema';

    public function handle()
    {

        $xml = simplexml_load_file(
            storage_path('app/opendental-schema.xml')
        );

        foreach ($xml->table as $table) {

            $tableName = strtolower((string) $table['name']);

            $migrationName = date('Y_m_d_His')
                ."_create_{$tableName}_table.php";

            $path = database_path(
                'migrations/'.$migrationName
            );

            $columns = '';

            foreach ($table->column as $column) {

                $name = (string) $column['name'];

                $type = strtolower(
                    (string) $column['type']
                );

                $nullable =
                    ((string) $column['null'] === 'true')
                    ? '->nullable()'
                    : '';

                $columns .= $this->convertType(
                    $name,
                    $type
                )
                    .$nullable
                    .";\n\n";

            }

            $stub = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

public function up()
{

Schema::create('$tableName', function(Blueprint \$table){

$columns

});

}


public function down()
{
Schema::dropIfExists('$tableName');
}

};

PHP;

            file_put_contents(
                $path,
                $stub
            );

            $this->info(
                "Created $tableName"
            );

        }

    }

    private function convertType($name, $type)
    {

        if (str_contains($type, 'int')) {

            return "\$table->integer('$name')";

        }

        if (
            str_contains($type, 'varchar')
        ) {

            return "\$table->string('$name')";

        }

        if (
            str_contains($type, 'text')
        ) {

            return "\$table->text('$name')";

        }

        if (
            str_contains($type, 'date')
        ) {

            return "\$table->date('$name')";

        }

        if (
            str_contains($type, 'datetime')
        ) {

            return "\$table->dateTime('$name')";

        }

        if (
            str_contains($type, 'decimal')
        ) {

            return "\$table->decimal('$name',10,2)";

        }

        return "\$table->string('$name')";

    }
}
