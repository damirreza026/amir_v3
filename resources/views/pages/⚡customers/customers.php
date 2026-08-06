<?php

use App\Models\Customer;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?int $cust_id = null;

    public string $shop_name = '';

    public string $phone = '';

    public string $address = '';

    public string $sortBy = 'shop_name';

    public string $sortDirection = 'asc';

    // اگر وقتی تایپ می‌کنی paginate برگرده صفحه 1
    public function updatingShopName()
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
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->when($this->sortBy, fn ($q) => $q->orderBy($this->sortBy, $this->sortDirection))
            ->paginate(15);
    }

    /** ولیدیشن */
    protected function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'shop_name.required' => 'نام فروشگاه الزامی است.',
            'shop_name.min' => 'نام فروشگاه حداقل باید ۲ کاراکتر باشد.',
            'shop_name.max' => 'نام فروشگاه حداکثر ۱۰۰ کاراکتر باشد.',

            'phone.regex' => 'فرمت شماره تلفن معتبر نیست.',
            'phone.max' => 'شماره تلفن حداکثر ۳۰ کاراکتر باشد.',

            'address.max' => 'آدرس حداکثر ۲۵۵ کاراکتر باشد.',
        ];
    }

    /** ریست فرم + ارورها */
    private function resetForm(): void
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

        Customer::create($data);

        Flux::modal('save')->close();
        $this->resetForm();

        // اگر Toast/Notification داری اینجا بزن
        // $this->dispatch('notify', type:'success', message:'فروشگاه با موفقیت ثبت شد.');
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

        $customer = Customer::findOrFail($this->cust_id);
        $customer->update($data);

        Flux::modal('edit')->close();
        $this->resetForm();
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

        Customer::whereKey($this->cust_id)->delete();

        Flux::modal('delete')->close();
        $this->resetForm();
    }
};
