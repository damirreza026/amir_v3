<?php

use App\Models\Customer;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?int $cust_id = null;

    // متغیر جست‌وجو
    public string $search = '';

    // متغیرهای فرم
    public string $shop_name = '';

    public string $phone = '';

    public string $address = '';

    // مرتب‌سازی
    public string $sortBy = 'shop_name';

    public string $sortDirection = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        $allowed = ['shop_name', 'phone', 'address', 'id', 'created_at'];
        if (! in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->when(filled($this->search), function ($query) {
                $query->where('shop_name', 'like', '%' . trim($this->search) . '%');
            })
            ->when($this->sortBy, fn ($q) => $q->orderBy($this->sortBy, $this->sortDirection))
            ->paginate(15);
    }

    /** قوانین اعتبارسنجی */
    protected function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\d{11}$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'shop_name.required' => 'نام فروشگاه الزامی است.',
            'shop_name.min' => 'نام فروشگاه حداقل باید ۲ کاراکتر باشد.',
            'shop_name.max' => 'نام فروشگاه حداکثر ۱۰۰ کاراکتر باشد.',

            'phone.regex' => 'شماره تماس شما باید ۱۱ رقم باشد.',
            'phone.max' => 'شماره تلفن حداکثر ۳۰ کاراکتر باشد.',

            'address.max' => 'آدرس حداکثر ۲۵۵ کاراکتر باشد.',
        ];
    }

    /** ریست فرم + خطاها */
    public function resetForm(): void
    {
        $this->reset(['cust_id', 'shop_name', 'phone', 'address']);
        $this->resetValidation();
    }

    public function openSaveModal(): void
    {
        $this->resetForm();
        Flux::modal('save')->show();
    }

    public function save(): void
    {
        $data = $this->validate();

        try {
            Customer::create($data);

            unset($this->customers);
            session()->flash('success', 'مشتری جدید با موفقیت ثبت شد.');
            Flux::modal('save')->close();
            $this->resetForm();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('save_error', 'خطایی هنگام ثبت اطلاعات رخ داد. لطفاً دوباره تلاش کنید.');
        }
    }

    public function edit(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->cust_id = $customer->id;
        $this->shop_name = (string) $customer->shop_name;
        $this->phone = (string) ($customer->phone ?? '');
        $this->address = (string) ($customer->address ?? '');

        $this->resetValidation();
        Flux::modal('edit')->show();
    }

    public function update(): void
    {
        if (! $this->cust_id) {
            $this->addError('cust_id', 'شناسه مشتری نامعتبر است.');
            return;
        }

        $data = $this->validate();

        try {
            $customer = Customer::findOrFail($this->cust_id);
            $customer->update($data);

            unset($this->customers);
            session()->flash('success', 'اطلاعات مشتری با موفقیت ویرایش شد.');
            Flux::modal('edit')->close();
            $this->resetForm();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('update_error', 'خطایی هنگام ویرایش اطلاعات رخ داد. لطفاً دوباره تلاش کنید.');
        }
    }

    public function del_form(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->cust_id = $customer->id;
        $this->shop_name = (string) $customer->shop_name;

        $this->resetValidation();
        Flux::modal('delete')->show();
    }

    public function delete(): void
    {
        if (! $this->cust_id) {
            $this->addError('cust_id', 'شناسه مشتری نامعتبر است.');
            return;
        }

        try {
            Customer::whereKey($this->cust_id)->delete();

            unset($this->customers);
            session()->flash('success', 'مشتری با موفقیت حذف شد.');
            Flux::modal('delete')->close();
            $this->resetForm();
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'خطایی هنگام حذف مشتری رخ داد. لطفاً دوباره تلاش کنید.');
            Flux::modal('delete')->close();
        }
    }
};

