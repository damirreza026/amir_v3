<?php

namespace App\Livewire;

use App\Models\Year;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $year = '';

    public $year_id = null;

    public $sortBy = 'year';

    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function years()
    {
        return Year::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(15);
    }

    public function openSaveModal()
    {
        $this->reset_data();
        Flux::modal('save')->show();
    }

    public function reset_data()
    {
        $this->reset(['year', 'year_id']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'year' => ['required', 'digits:4', 'unique:payroll_years,year'],
        ], [
            'year.required' => 'وارد کردن سال الزامی است.',
            'year.digits' => 'نام سال باید حتما 4 رقم باشد.',
            'year.unique' => 'این سال قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $year = new Year;
                $year->year = $this->year;
                $year->save();
            });

            session()->flash('success', 'سال جدید با موفقیت ثبت شد.');
            Flux::modal('save')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    public function edit(int $yearId)
    {
        $this->resetValidation();

        $year = Year::findOrFail($yearId);

        $this->year_id = $year->id;
        $this->year = $year->year;

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        $this->validate([
            'year' => [
                'required',
                'digits:4',
                Rule::unique('payroll_years', 'year')->ignore($this->year_id),
            ],
        ], [
            'year.required' => 'وارد کردن سال الزامی است.',
            'year.digits' => 'نام سال باید حتما 4 رقم باشد.',
            'year.unique' => 'این سال قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $year = Year::findOrFail($this->year_id);
                $year->year = $this->year;
                $year->save();
            });

            session()->flash('success', 'دسته‌بندی با موفقیت ویرایش شد.');
            Flux::modal('edit-user')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError('update_error', 'خطایی در ویرایش اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    public function delete_form(int $yearId)
    {
        $this->resetValidation();

        $year = Year::findOrFail($yearId);
        $this->year_id = $year->id;
        $this->year = $year->year;

        Flux::modal('delete-user')->show();
    }

    public function delete()
    {
        try {
            DB::transaction(function () {
                $year = Year::findOrFail($this->year_id);
                $year->delete();
            });

            session()->flash('success', 'سال با موفقیت حذف شد.');
            Flux::modal('delete-user')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در حذف سال: '.$e->getMessage());
            Flux::modal('delete-user')->close();
        }
    }
};
