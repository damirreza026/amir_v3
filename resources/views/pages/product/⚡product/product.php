<?php

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public Category $category;
    public int $category_id;

    // متغیر جست‌وجو
    public string $search = '';

    // متغیرهای فرم
    public $name = '';
    public $pro_id = null;

    // متغیرهای مرتب‌سازی
    public $sortBy = 'name';
    public $sortDirection = 'desc';

    public function mount(Category $category)
    {
        $this->category = $category;
        $this->category_id = $category->id;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc'
                ? 'desc'
                : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('category_id', $this->category->id)
            ->when($this->search !== '', function ($query) {
                $query->where(
                    'name',
                    'like',
                    '%' . trim($this->search) . '%'
                );
            })
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy(
                        $this->sortBy,
                        $this->sortDirection
                    );
                }
            })
            ->paginate(15);
    }

    // متد باز کردن مودال ثبت محصول جدید
    public function openSaveModal()
    {
        $this->reset_data();
        Flux::modal('save')->show();
    }

    // پاک‌سازی فرم و خطاهای اعتبارسنجی
    public function reset_data()
    {
        $this->reset(['name', 'pro_id']);
        $this->resetValidation();
    }

    public function save()
    {
        // بررسی یکتا بودن نام محصول در این دسته‌بندی مشخص
        $this->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->where(function ($query) {
                        return $query->where(
                            'category_id',
                            $this->category_id
                        );
                    }),
            ],
        ], [
            'name.required' => 'وارد کردن نام محصول الزامی است.',
            'name.min' => 'نام محصول باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام محصول نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام محصول قبلاً در این دسته‌بندی ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $product = new Product();
                $product->name = $this->name;
                $product->category_id = $this->category_id;
                $product->save();
            });

            session()->flash(
                'success',
                'محصول جدید با موفقیت ثبت شد.'
            );

            Flux::modal('save')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError(
                'save_error',
                'خطایی در ثبت اطلاعات رخ داد: ' . $e->getMessage()
            );
        }
    }

    public function edit(Product $product)
    {
        $this->resetValidation();

        $this->name = $product->name;
        $this->pro_id = $product->id;

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        // بررسی یکتا بودن نام محصول به جز رکوردی که در حال ویرایش است
        $this->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->ignore($this->pro_id)
                    ->where(function ($query) {
                        return $query->where(
                            'category_id',
                            $this->category_id
                        );
                    }),
            ],
        ], [
            'name.required' => 'وارد کردن نام محصول الزامی است.',
            'name.min' => 'نام محصول باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام محصول نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام محصول قبلاً در این دسته‌بندی ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $product = Product::findOrFail($this->pro_id);
                $product->name = $this->name;
                $product->save();
            });

            session()->flash(
                'success',
                'محصول با موفقیت ویرایش شد.'
            );

            Flux::modal('edit-user')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError(
                'update_error',
                'خطایی در ویرایش اطلاعات رخ داد: ' . $e->getMessage()
            );
        }
    }

    public function delete_form(Product $product)
    {
        $this->reset_data();

        $this->name = $product->name;
        $this->pro_id = $product->id;

        Flux::modal('delete-user')->show();
    }

    public function delete()
    {
        try {
            DB::transaction(function () {
                $product = Product::findOrFail($this->pro_id);
                $product->delete();
            });

            session()->flash(
                'success',
                'محصول با موفقیت حذف شد.'
            );

            Flux::modal('delete-user')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            session()->flash(
                'error',
                'خطا در حذف محصول: ' . $e->getMessage()
            );

            Flux::modal('delete-user')->close();
        }
    }
};
