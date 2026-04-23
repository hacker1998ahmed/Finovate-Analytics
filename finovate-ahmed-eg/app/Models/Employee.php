<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'department_id',
        'employee_code',
        'name',
        'national_id',
        'email',
        'phone',
        'address',
        'hire_date',
        'job_title',
        'salary_basic',
        'salary_allowances',
        'salary_deductions',
        'social_insurance_number',
        'bank_account',
        'status',
        'manager_id',
        'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary_basic' => 'decimal:2',
        'salary_allowances' => 'decimal:2',
        'salary_deductions' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(ProjectTimesheet::class);
    }
}
