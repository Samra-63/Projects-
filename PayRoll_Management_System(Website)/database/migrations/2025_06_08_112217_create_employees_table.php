<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->string('country');
            $table->unsignedBigInteger('department_id');
            $table->string('position');
            $table->decimal('base_salary', 10, 2);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->date('hire_date');
            $table->date('date_of_birth');
            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
