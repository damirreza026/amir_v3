<?php

use App\Models\Category;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public Category $category;

    // اطلاعات فرم
    public string $product_name = '';

    public ?int $u_id = null;

    public ?int $batch_id = null;

    public ?int $cat_id = null;

    public ?int $pro_id = null;

    public $sale_price = '';

    public $p_date = '';

    public $ex_date = '';

    public $quantity = '';

    // مرتب‌سازی
    public string $sortBy = 'expiry_date';

    public string $sortDirection = 'desc';

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->cat_id = $category->id;
        $this->u_id = auth()->user()?->profile?->id;
        $this->pro_id = $category->products()->value('id');
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
    public function productbatchs()
    {
        return ProductBatch::query()
            ->with([
                'product.category',
                'profile',
            ])
            ->whereHas('product', function ($query) {
                $query->where('category_id', $this->category->id);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    protected function formRules(int $minimumQuantity = 1): array
    {
        return [
            'pro_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $exists = $this->category
                        ->products()
                        ->whereKey($value)
                        ->exists();

                    if (! $exists) {
                        $fail('محصول انتخاب‌شده به این دسته‌بندی تعلق ندارد.');
                    }
                },
            ],
            'sale_price' => ['required', 'numeric', 'min:0'],

            // اجباری بودن فیلدهای تاریخ
            'p_date' => ['required', 'date'],
            'ex_date' => ['required', 'date', 'after_or_equal:p_date'],

            'quantity' => ['required', 'integer', 'min:'.$minimumQuantity],
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'pro_id.required' => 'انتخاب محصول الزامی است.',
            'pro_id.integer' => 'محصول انتخاب‌شده نامعتبر است.',

            'sale_price.required' => 'وارد کردن قیمت فروش الزامی است.',
            'sale_price.numeric' => 'قیمت فروش باید عدد باشد.',
            'sale_price.min' => 'قیمت فروش نمی‌تواند منفی باشد.',

            'p_date.required' => 'انتخاب تاریخ تولید الزامی است.',
            'p_date.date' => 'تاریخ تولید معتبر نیست.',

            'ex_date.required' => 'انتخاب تاریخ انقضا الزامی است.',
            'ex_date.date' => 'تاریخ انقضا معتبر نیست.',
            'ex_date.after_or_equal' => 'تاریخ انقضا باید برابر یا بعد از تاریخ تولید باشد.',

            'quantity.required' => 'وارد کردن تعداد الزامی است.',
            'quantity.integer' => 'تعداد باید یک عدد صحیح باشد.',
            'quantity.min' => 'تعداد باید حداقل ۱ باشد.',
        ];
    }

    public function openSaveModal(): void
    {
        $this->reset_data();
        $this->pro_id = $this->category->products()->value('id');
        Flux::modal('save')->show();
    }

    public function reset_data(): void
    {
        $this->reset([
            'product_name',
            'batch_id',
            'pro_id',
            'sale_price',
            'p_date',
            'ex_date',
            'quantity',
        ]);

        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate(
            $this->formRules(1),
            $this->validationMessages()
        );

        try {
            DB::transaction(function () {
                $productBatch = new ProductBatch;

                $productBatch->product_id = $this->pro_id;
                $productBatch->profile_id = $this->u_id;
                $productBatch->sale_price = $this->sale_price;
                $productBatch->production_date = $this->p_date;
                $productBatch->expiry_date = $this->ex_date;
                $productBatch->quantity = $this->quantity;

                $productBatch->save();
            });

            unset($this->productbatchs);
            session()->flash('success', 'موجودی جدید با موفقیت ثبت شد.');
            Flux::modal('save')->close();
            $this->reset_data();
        } catch (Throwable $e) {
            report($e);
            $this->addError(
                'save_error',
                'خطایی هنگام ثبت موجودی رخ داد. لطفاً دوباره تلاش کنید.'
            );
        }
    }

    public function edit(ProductBatch $productBatch): void
    {
        abort_unless(
            $productBatch->product?->category_id === $this->category->id,
            404
        );

        $this->resetValidation();

        $this->batch_id = $productBatch->id;
        $this->pro_id = $productBatch->product_id;
        $this->sale_price = $productBatch->sale_price;
        $this->quantity = $productBatch->quantity;

        /*
        |----------------------------------------------------------------------
        | input با type="date" فقط فرمت Y-m-d را قبول می‌کند.
        | مثل: 2026-08-04
        | اگر مقدار دیتابیس datetime باشد (مثل 2026-08-04 00:00:00)،
        | بدون این تبدیل داخل تقویم مودال نمایش داده نمی‌شود.
        |----------------------------------------------------------------------
        */
        $this->p_date = $productBatch->production_date
            ? Carbon::parse($productBatch->production_date)->format('Y-m-d')
            : '';

        $this->ex_date = $productBatch->expiry_date
            ? Carbon::parse($productBatch->expiry_date)->format('Y-m-d')
            : '';

        Flux::modal('edit-user')->show();
    }

    public function update(): void
    {
        $this->validate(
            $this->formRules(1),
            $this->validationMessages()
        );

        try {
            DB::transaction(function () {
                $productBatch = ProductBatch::findOrFail($this->batch_id);

                abort_unless(
                    $productBatch->product?->category_id === $this->category->id,
                    404
                );

                $productBatch->product_id = $this->pro_id;
                $productBatch->profile_id = $this->u_id;
                $productBatch->sale_price = $this->sale_price;
                $productBatch->production_date = $this->p_date;
                $productBatch->expiry_date = $this->ex_date;
                $productBatch->quantity = $this->quantity;

                $productBatch->save();
            });

            unset($this->productbatchs);
            session()->flash('success', 'اطلاعات موجودی با موفقیت ویرایش شد.');
            Flux::modal('edit-user')->close();
            $this->reset_data();
        } catch (Throwable $e) {
            report($e);
            $this->addError(
                'update_error',
                'خطایی هنگام ویرایش موجودی رخ داد. لطفاً دوباره تلاش کنید.'
            );
        }
    }

    public function delete_form(ProductBatch $productBatch): void
    {
        abort_unless(
            $productBatch->product?->category_id === $this->category->id,
            404
        );

        $this->reset_data();

        $this->batch_id = $productBatch->id;
        $this->product_name = $productBatch->product?->name ?? 'این محصول';

        Flux::modal('delete-user')->show();
    }

    public function delete(): void
    {
        try {
            DB::transaction(function () {
                $productBatch = ProductBatch::findOrFail($this->batch_id);

                abort_unless(
                    $productBatch->product?->category_id === $this->category->id,
                    404
                );

                $productBatch->delete();
            });

            unset($this->productbatchs);
            session()->flash('success', 'موجودی با موفقیت حذف شد.');
            Flux::modal('delete-user')->close();
            $this->reset_data();
        } catch (Throwable $e) {
            report($e);
            session()->flash(
                'error',
                'خطایی هنگام حذف موجودی رخ داد. لطفاً دوباره تلاش کنید.'
            );
            Flux::modal('delete-user')->close();
        }
    }
};
