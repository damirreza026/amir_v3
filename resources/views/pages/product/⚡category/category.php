<?php

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    // متغیرهای فرم
    public $name = '';

    public $cat_id = null;

    // متغیرهای مرتب‌سازی جدول
    public $sortBy = 'name';

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
    public function categories()
    {
        return Category::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(15);
    }

    // متد باز کردن مودال ثبت دسته‌بندی جدید
    public function openSaveModal()
    {
        $this->reset_data();
        Flux::modal('save')->show();
    }

    // پاک‌سازی فرم و خطاها
    public function reset_data()
    {
        $this->reset(['name', 'cat_id']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:categories,name'],
        ], [
            'name.required' => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.min' => 'نام دسته‌بندی باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام دسته‌بندی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                // بدون استفاده از create برای جلوگیری از خطای fillable
                $category = new Category;
                $category->name = $this->name;
                $category->save();
            });

            session()->flash('success', 'دسته‌بندی جدید با موفقیت ثبت شد.');
            Flux::modal('save')->close();
            $this->reset_data();

        } catch (Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    public function edit(int $categoryId)
    {
        $this->resetValidation();

        $category = Category::findOrFail($categoryId);

        $this->cat_id = $category->id;
        $this->name = $category->name;

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:categories,name,' . $this->cat_id],
        ], [
            'name.required' => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.min' => 'نام دسته‌بندی باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام دسته‌بندی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                // بدون استفاده از update برای جلوگیری از خطای fillable
                $category = Category::findOrFail($this->cat_id);
                $category->name = $this->name;
                $category->save();
            });

            session()->flash('success', 'دسته‌بندی با موفقیت ویرایش شد.');
            Flux::modal('edit-user')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            $this->addError('update_error', 'خطایی در ویرایش اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    public function delete_form(int $categoryId)
    {
        $this->reset_data();

        $category = Category::findOrFail($categoryId);
        $this->cat_id = $category->id;
        $this->name = $category->name;

        Flux::modal('delete-user')->show();
    }

    public function delete()
    {
        try {
            DB::transaction(function () {
                $category = Category::findOrFail($this->cat_id);
                $category->delete();
            });

            session()->flash('success', 'دسته‌بندی با موفقیت حذف شد.');
            Flux::modal('delete-user')->close();
            $this->reset_data();

        } catch (Throwable $e) {
            session()->flash('error', 'خطا در حذف دسته‌بندی: '.$e->getMessage());
            Flux::modal('delete-user')->close();
        }
    }
};
