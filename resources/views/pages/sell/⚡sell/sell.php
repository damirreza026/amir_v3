<?php

use App\Models\ProductBatch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public $prod_name = '';
    public $pro_id = '';
    public $sale_p = '';
    public $pro_date = '';
    public $ex_date = '';

    // تعداد انتخابی کاربر برای فروش در مودال
    public $quan = 1;

    // نگهداری کل موجودی بچ انتخاب شده
    public $available_qty = 0;

    // سبد خرید موقت
    public array $cart = [];

    // شناسه مغازه / مشتری انتخاب‌شده برای فاکتور
    public $customer_id = '';

    public $sortBy = 'expiry_date';
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
    public function productbatches()
    {
        return ProductBatch::query()
            ->with(['product', 'profile'])
            ->where('quantity', '>', 0)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    // دریافت لیست مغازه‌ها برای انتخاب در زمان فروش
    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->orderBy('shop_name')
            ->get(['id', 'shop_name']);
    }

    #[Computed]
    public function cartTotal(): float
    {
        return (float) collect($this->cart)->sum('total');
    }

    public function edit(ProductBatch $productbatche)
    {
        $this->resetValidation();

        $this->prod_name = $productbatche->product->name;
        $this->sale_p = (float) $productbatche->sale_price;
        $this->pro_date = $productbatche->production_date;
        $this->ex_date = $productbatche->expiry_date;

        $this->available_qty = (int) $productbatche->quantity;
        $this->quan = 1;
        $this->pro_id = $productbatche->id;

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        $this->validate([
            'quan' => ['required', 'integer', 'min:1'],
        ], [
            'quan.required' => 'تعداد فروش الزامی است.',
            'quan.integer' => 'تعداد باید عدد باشد.',
            'quan.min' => 'تعداد باید حداقل ۱ باشد.',
        ]);

        $requestedQty = (int) $this->quan;

        if ($requestedQty > $this->available_qty) {
            $this->addError(
                'quan',
                "تعداد درخواستی از موجودی بیشتر است (موجودی: {$this->available_qty})"
            );

            return;
        }

        // آیا این بچ قبلاً داخل سبد وجود دارد؟
        $existingIndex = collect($this->cart)->search(
            fn (array $item) => (int) $item['batch_id'] === (int) $this->pro_id
        );

        // تعداد فعلی همان بچ در سبد
        $currentInCart = $existingIndex !== false
            ? (int) $this->cart[$existingIndex]['qty']
            : 0;

        // موجودی کل بچ نباید از تعداد موجودی بیشتر شود
        if (($currentInCart + $requestedQty) > $this->available_qty) {
            $this->addError(
                'quan',
                'مجموع تعداد در سبد خرید نمی‌تواند از موجودی بچ بیشتر باشد.'
            );

            return;
        }

        // اگر بچ در سبد وجود داشت، تعداد آن را زیاد می‌کنیم
        if ($existingIndex !== false) {
            $newQty = $currentInCart + $requestedQty;

            $this->cart[$existingIndex]['qty'] = $newQty;
            $this->cart[$existingIndex]['total'] = $newQty * $this->sale_p;
        } else {
            // اگر وجود نداشت، به سبد اضافه می‌کنیم
            $this->cart[] = [
                'batch_id' => (int) $this->pro_id,
                'product_name' => $this->prod_name,
                'price' => $this->sale_p,
                'qty' => $requestedQty,
                'total' => $requestedQty * $this->sale_p,
            ];
        }

        Flux::modal('edit-user')->close();

        // ریست اطلاعات فرم مودال
        $this->prod_name = '';
        $this->sale_p = '';
        $this->pro_date = '';
        $this->ex_date = '';
        $this->quan = 1;
        $this->available_qty = 0;
        $this->pro_id = '';
    }

    public function removeFromCart(int $index)
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);

            // شماره indexهای آرایه را مرتب می‌کند
            $this->cart = array_values($this->cart);
        }
    }

    public function clearCart()
    {
        $this->cart = [];
    }

    public function checkout()
    {
        // پاک کردن خطای قبلی checkout
        $this->resetValidation('checkout_error');

        if (empty($this->cart)) {
            $this->addError('checkout_error', 'سبد خرید شما خالی است.');

            return;
        }

        // بررسی انتخاب مشتری / مغازه
        $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
        ], [
            'customer_id.required' => 'لطفاً مغازه یا مشتری را انتخاب کنید.',
            'customer_id.exists' => 'مغازه انتخاب‌شده معتبر نیست.',
        ]);

        // مشتری انتخاب‌شده از dropdown
        $customer = Customer::findOrFail($this->customer_id);

        // دریافت شناسه پروفایل کاربر واردشده
        $profileId = Auth::user()?->profile?->id
            ?? \App\Models\Profile::first()?->id
            ?? 1;

        try {
            DB::transaction(function () use ($customer, $profileId) {
                // ایجاد فاکتور
                $invoice = new Invoice();
                $invoice->customer_id = $customer->id;
                $invoice->profile_id = $profileId;
                $invoice->total_price = (string) $this->cartTotal;
                $invoice->invoice_date = now()->format('Y-m-d');
                $invoice->save();

                // ثبت آیتم‌های فاکتور و کم کردن موجودی
                foreach ($this->cart as $item) {
                    $batch = ProductBatch::findOrFail($item['batch_id']);

                    // بررسی نهایی موجودی
                    if ((int) $batch->quantity < (int) $item['qty']) {
                        throw new \Exception(
                            "موجودی محصول '{$item['product_name']}' در این لحظه کافی نیست."
                        );
                    }

                    // کاهش موجودی بچ
                    $batch->quantity -= (int) $item['qty'];
                    $batch->save();

                    // ثبت آیتم فاکتور
                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->product_batch_id = $batch->id;
                    $invoiceItem->quantity = (string) $item['qty'];
                    $invoiceItem->price = (string) $item['price'];
                    $invoiceItem->subtotal = (string) $item['total'];
                    $invoiceItem->save();
                }
            });

            session()->flash(
                'success',
                "فروش برای مغازه «{$customer->shop_name}» با موفقیت ثبت شد و موجودی انبار به‌روزرسانی گردید."
            );

            // خالی کردن سبد و انتخاب مشتری برای فروش بعدی
            $this->clearCart();
            $this->customer_id = '';

        } catch (\Exception $e) {
            $this->addError('checkout_error', 'خطا در ثبت فروش: ' . $e->getMessage());
        }
    }
};
