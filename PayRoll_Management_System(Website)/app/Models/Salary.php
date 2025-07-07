<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'employee_id',
        'base_salary',
        'overtime_hours',
        'overtime_rate',
        'overtime_pay',
        'bonus',
        'allowances',
        'deductions',
        'tax',
        'net_salary',
        'payment_method',
        'payment_status',
        'salary_date',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'salary_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the employee that owns the salary.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate overtime pay based on hours and rate.
     */
    public function calculateOvertimePay(): float
    {
        return $this->overtime_hours * $this->overtime_rate;
    }

    /**
     * Calculate gross salary (base + overtime + bonus + allowances).
     */
    public function calculateGrossSalary(): float
    {
        return $this->base_salary + $this->overtime_pay + $this->bonus + $this->allowances;
    }

    /**
     * Calculate tax based on gross salary.
     * This uses a progressive tax system with configurable tax brackets.
     */
    public function calculateTax(): float
    {
        $grossSalary = $this->calculateGrossSalary();
        $taxBrackets = config('payroll.tax_brackets', [
            ['limit' => 1000, 'rate' => 0.10],  // 10% up to 1000
            ['limit' => 5000, 'rate' => 0.15],  // 15% up to 5000
            ['limit' => PHP_FLOAT_MAX, 'rate' => 0.20]  // 20% above 5000
        ]);

        $tax = 0;
        $remainingSalary = $grossSalary;
        $previousLimit = 0;

        foreach ($taxBrackets as $bracket) {
            $taxableAmount = min($remainingSalary, $bracket['limit'] - $previousLimit);
            $tax += $taxableAmount * $bracket['rate'];
            $remainingSalary -= $taxableAmount;
            $previousLimit = $bracket['limit'];

            if ($remainingSalary <= 0) {
                break;
            }
        }

        return round($tax, 2);
    }

    /**
     * Calculate net salary.
     */
    public function calculateNetSalary(): float
    {
        $grossSalary = $this->calculateGrossSalary();
        $tax = $this->calculateTax();
        return round($grossSalary - $tax - $this->deductions, 2);
    }

    /**
     * Save the calculated values before saving the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($salary) {
            if ($salary->overtime_hours && $salary->overtime_rate) {
                $salary->overtime_pay = $salary->calculateOvertimePay();
            }
            $salary->tax = $salary->calculateTax();
            $salary->net_salary = $salary->calculateNetSalary();
        });
    }
}
