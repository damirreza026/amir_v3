<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductBatch;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $prod_name = '';
    public string|int $pro_id = '';
    public string|float $sale_p = '';
    public string $pro_date = '';
    public string $ex_date = '';
    public int $quan = 1;
    public int $available_qty = 0;
    public array $cart = [];
    public string $customer_id = '';
    public string $sortBy = 'expiry_date';
    public string $sortDirection = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        $allowed = ['expiry_date', 'production_date', 'sale_price', 'quantity', 'id'];
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
    public function productbatches()
    {
        return ProductBatch::query()
            ->with(['product', 'profile'])
            ->where('quantity', '>', 0)
            ->when(filled($this->search), function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->whereHas('product', function ($q) use ($term) {
                    $q->where('name', 'like', $term);
                });
            })
            ->when($this->sortBy, fn ($q) => $q->orderBy($this->sortBy, $this->sortDirection))
            ->paginate(15);
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->orderBy('shop_name')
            ->get(['id', 'shop_name', 'phone']);
    }

    #[Computed]
    public function cartTotal(): float
    {
        return (float) collect($this->cart)->sum('total');
    }

    #[Computed]
    public function cartCount(): int
    {
        return (int) collect($this->cart)->sum('qty');
    }

    public function edit(int $batchId): void
    {
        $productBatch = ProductBatch::with('product')->findOrFail($batchId);

        $this->resetValidation();

        $this->prod_name = (string) ($productBatch->product->name ?? '-');
        $this->sale_p = (float) $productBatch->sale_price;

        // تبدیل تاریخ‌ها به شمسی برای نمایش در مودال
        try {
            $this->pro_date = $productBatch->production_date ? Jalalian::fromCarbon(\Carbon\Carbon::parse($productBatch->production_date))->format('Y/m/d') : '-';
            $this->ex_date = $productBatch->expiry_date ? Jalalian::fromCarbon(\Carbon\Carbon::parse($productBatch->expiry_date))->format('Y/m/d') : '-';
        } catch (\Exception $e) {
            $this->pro_date = (string) ($productBatch->production_date ?? '-');
            $this->ex_date = (string) ($productBatch->expiry_date ?? '-');
        }

        $this->available_qty = (int) $productBatch->quantity;
        $this->quan = 1;
        $this->pro_id = $productBatch->id;

        Flux::modal('edit-user')->show();
    }

    public function update(): void
    {
        $this->validate([
            'quan' => ['required', 'integer', 'min:1'],
        ], [
            'quan.required' => 'تعداد فروش الزامی است.',
            'quan.integer' => 'تعداد باید عددی معتبر باشد.',
            'quan.min' => 'تعداد باید حداقل ۱ باشد.',
        ]);

        $requestedQty = (int) $this->quan;

        if ($requestedQty > $this->available_qty) {
            $this->addError('quan', "تعداد درخواستی از موجودی این بچ بیشتر است (موجودی: {$this->available_qty})");
            return;
        }

        $existingIndex = collect($this->cart)->search(
            fn (array $item) => (int) $item['batch_id'] === (int) $this->pro_id
        );

        $currentInCart = $existingIndex !== false ? (int) $this->cart[$existingIndex]['qty'] : 0;

        if (($currentInCart + $requestedQty) > $this->available_qty) {
            $this->addError('quan', "مجموع تعداد در سبد خرید نمی‌تواند از موجودی انبار ({$this->available_qty}) بیشتر باشد.");
            return;
        }

        if ($existingIndex !== false) {
            $newQty = $currentInCart + $requestedQty;
            $this->cart[$existingIndex]['qty'] = $newQty;
            $this->cart[$existingIndex]['total'] = $newQty * (float) $this->sale_p;
        } else {
            $this->cart[] = [
                'batch_id' => (int) $this->pro_id,
                'product_name' => $this->prod_name,
                'price' => (float) $this->sale_p,
                'qty' => $requestedQty,
                'total' => $requestedQty * (float) $this->sale_p,
            ];
        }

        Flux::modal('edit-user')->close();
        $this->reset(['prod_name', 'sale_p', 'pro_date', 'ex_date', 'quan', 'available_qty', 'pro_id']);
    }

    public function removeFromCart(int $index): void
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function checkout(): void
    {
        $this->resetValidation('checkout_error');
        if (empty($this->cart)) {
            $this->addError('checkout_error', 'سبد خرید شما خالی است.');
            return;
        }

        $this->validate(['customer_id' => ['required', 'exists:customers,id']], [
            'customer_id.required' => 'لطفاً نام مغازه / مشتری را انتخاب کنید.',
            'customer_id.exists' => 'مشتری انتخاب‌شده در سیستم معتبر نیست.',
        ]);

        $customer = Customer::findOrFail($this->customer_id);
        $profileId = Auth::user()?->profile?->id ?? \App\Models\Profile::first()?->id ?? 1;

        try {
            DB::transaction(function () use ($customer, $profileId) {
                $invoice = new Invoice();
                $invoice->customer_id = $customer->id;
                $invoice->profile_id = $profileId;
                $invoice->total_price = (string) $this->cartTotal;
                $invoice->invoice_date = now()->format('Y-m-d');
                $invoice->save();

                foreach ($this->cart as $item) {
                    $batch = ProductBatch::lockForUpdate()->findOrFail($item['batch_id']);
                    if ((int) $batch->quantity < (int) $item['qty']) {
                        throw new \Exception("موجودی کالای «{$item['product_name']}» کافی نیست.");
                    }
                    $batch->quantity -= (int) $item['qty'];
                    $batch->save();
                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->product_batch_id = $batch->id;
                    $invoiceItem->quantity = (string) $item['qty'];
                    $invoiceItem->price = (string) $item['price'];
                    $invoiceItem->subtotal = (string) $item['total'];
                    $invoiceItem->save();
                }
            });

            unset($this->productbatches);
            session()->flash('success', "فروش برای مشتری «{$customer->shop_name}» ثبت شد.");
            $this->clearCart();
            $this->customer_id = '';
        } catch (\Throwable $e) {
            report($e);
            $this->addError('checkout_error', 'خطا در ثبت فاکتور: ' . $e->getMessage());
        }
    }
};
