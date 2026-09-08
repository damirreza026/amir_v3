<?php

use App\Models\Month;
use App\Models\Year;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Year $year;

    public $month_id = null;

    public $month_name = '';

    public function mount(Year $year): void
    {
        $this->year = $year;
    }

    #[Computed]
    public function months()
    {
        return Month::query()
            ->where('payroll_year_id', $this->year->id)
            ->orderBy('month')
            ->get();
    }

    public function resetForm(): void
    {
        $this->month_id = null;
        $this->month_name = '';
        $this->resetValidation();
    }

    public function edit(int $monthId): void
    {
        $month = Month::query()
            ->where('id', $monthId)
            ->where('payroll_year_id', $this->year->id)
            ->firstOrFail();

        $this->month_id = $month->id;
        $this->month_name = $month->month_name;

        $this->resetValidation();

        Flux::modal('edit-month')->show();
    }

    public function update(): void
    {
        $this->validate([
            'month_name' => ['required', 'string', 'max:255'],
        ], [
            'month_name.required' => 'نام ماه الزامی است.',
            'month_name.max' => 'نام ماه نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        ]);

        try {
            $month = Month::query()
                ->where('id', $this->month_id)
                ->where('payroll_year_id', $this->year->id)
                ->firstOrFail();

            $month->update([
                'month_name' => $this->month_name,
            ]);

            session()->flash('success', 'نام ماه با موفقیت ویرایش شد.');

            $this->resetForm();

            Flux::modal('edit-month')->close();
        } catch (\Throwable $e) {
            $this->addError(
                'update_error',
                'خطا در ویرایش ماه: ' . $e->getMessage()
            );
        }
    }
};
