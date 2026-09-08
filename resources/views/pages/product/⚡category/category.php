<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    // متغیرهای فرم
    public string $name = '';
    public ?int $cat_id = null;

    // متغیر جست‌وجوی زنده
    public string $search = '';

    // متغیرهای مرتب‌سازی جدول
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    // ریست صفحه‌بندی هنگام تغییر عبارت جست‌وجو
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->when(filled(trim($this->search)), function ($query) {
                $query->where('name', 'like', '%' . trim($this->search) . '%');
            })
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(15);
    }

    // متد باز کردن مودال ثبت دسته‌بندی جدید
    public function openSaveModal(): void
    {
        $this->reset_data();
        Flux::modal('save-category')->show();
    }

    // پاک‌سازی فرم و خطاها
    public function reset_data(): void
    {
        $this->reset(['name', 'cat_id']);
        $this->resetValidation();
    }

    // ثبت دسته‌بندی جدید
    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:categories,name'],
        ], [
            'name.required' => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.string' => 'نام دسته‌بندی باید متنی باشد.',
            'name.min' => 'نام دسته‌بندی باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام دسته‌بندی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                Category::create([
                    'name' => trim($this->name),
                ]);
            });

            session()->flash('success', 'دسته‌بندی جدید با موفقیت ثبت شد.');
            Flux::modal('save-category')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    // باز کردن مودال ویرایش
    public function edit(int $categoryId): void
    {
        $this->reset_data();

        $category = Category::findOrFail($categoryId);
        $this->cat_id = $category->id;
        $this->name = $category->name;

        Flux::modal('edit-category')->show();
    }

    // ذخیره تغییرات ویرایش
    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:categories,name,' . $this->cat_id],
        ], [
            'name.required' => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.string' => 'نام دسته‌بندی باید متنی باشد.',
            'name.min' => 'نام دسته‌بندی باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام دسته‌بندی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $category = Category::findOrFail($this->cat_id);
                $category->name = trim($this->name);
                $category->save();
            });

            session()->flash('success', 'دسته‌بندی با موفقیت ویرایش شد.');
            Flux::modal('edit-category')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            $this->addError('update_error', 'خطایی در ویرایش اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    // باز کردن مودال حذف
    public function delete_form(int $categoryId): void
    {
        $this->reset_data();

        $category = Category::findOrFail($categoryId);
        $this->cat_id = $category->id;
        $this->name = $category->name;

        Flux::modal('delete-category')->show();
    }

    // عملیات حذف
    public function delete(): void
    {
        try {
            DB::transaction(function () {
                $category = Category::findOrFail($this->cat_id);
                $category->delete();
            });

            session()->flash('success', 'دسته‌بندی با موفقیت حذف شد.');
            Flux::modal('delete-category')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در حذف دسته‌بندی: ' . $e->getMessage());
            Flux::modal('delete-category')->close();
        }
    }
};
