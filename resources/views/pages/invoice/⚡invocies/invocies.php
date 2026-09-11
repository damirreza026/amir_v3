<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductBatch;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    use WithPagination;

    public $invoice_id = '';

    public $total_p = 0;

    public $invoice_date = '';

    public $invoice_year = '';

    public $invoice_month = '';

    public $invoice_day = '';

    public $customers_name = '';

    public $seller_name = '';

    public $customer_id = '';

    public $profile_id = '';

    public $invoice_to_delete_id = null;

    public $items = [];

    public $search = '';

    public $filter_year = '';

    public $filter_month = '';

    public $filter_day = '';

    public $sortBy = 'invoice_date';

    public $sortDirection = 'desc';

    public function mount()
    {
        $this->profile_id = auth()->user()->profile->id ?? null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterYear()
    {
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    public function updatedFilterDay()
    {
        $this->resetPage();
    }

    public function resetDateFilters()
    {
        $this->filter_year = '';
        $this->filter_month = '';
        $this->filter_day = '';
        $this->resetPage();
    }

    public function sort($column)
    {
        $allowedColumns = [
            'invoice_date',
            'total_price',
            'id',
        ];

        if (! in_array($column, $allowedColumns, true)) {
            return;
        }

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
    public function invoices()
    {
        return Invoice::query()
            ->with(['customer', 'profile'])
            ->when(
                filled(trim($this->search)),
                function ($query) {
                    $search = '%' . trim($this->search) . '%';

                    $query->whereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('shop_name', 'like', $search);
                    });
                }
            )
            ->when(
                filled($this->filter_year) || filled($this->filter_month) || filled($this->filter_day),
                function ($query) {
                    $y = filled($this->filter_year) ? (int) $this->filter_year : null;
                    $m = filled($this->filter_month) ? (int) $this->filter_month : null;
                    $d = filled($this->filter_day) ? (int) $this->filter_day : null;

                    if ($y && $m && $d) {
                        try {
                            $gDate = (new Jalalian($y, $m, $d))->toCarbon()->format('Y-m-d');
                            $query->whereDate('invoice_date', $gDate);
                        } catch (\Throwable $e) {
                        }
                    } elseif ($y && $m) {
                        try {
                            $daysInMonth = $m <= 6 ? 31 : ($m <= 11 ? 30 : 29);
                            $startDate = (new Jalalian($y, $m, 1))->toCarbon()->startOfDay()->format('Y-m-d');
                            $endDate = (new Jalalian($y, $m, $daysInMonth))->toCarbon()->endOfDay()->format('Y-m-d');
                            $query->whereBetween('invoice_date', [$startDate, $endDate]);
                        } catch (\Throwable $e) {
                        }
                    } elseif ($y) {
                        try {
                            $startDate = (new Jalalian($y, 1, 1))->toCarbon()->startOfDay()->format('Y-m-d');
                            $endDate = (new Jalalian($y, 12, 29))->toCarbon()->endOfDay()->format('Y-m-d');
                            $query->whereBetween('invoice_date', [$startDate, $endDate]);
                        } catch (\Throwable $e) {
                        }
                    }
                }
            )
            ->when(
                $this->sortBy,
                fn ($query) => $query->orderBy(
                    $this->sortBy,
                    $this->sortDirection
                )
            )
            ->paginate(15);
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->orderBy('shop_name')
            ->get(['id', 'shop_name']);
    }

    #[Computed]
    public function productBatches()
    {
        return ProductBatch::query()
            ->with('product')
            ->where('quantity', '>', 0)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function openSaveModal()
    {
        $this->resetForm();

        $nowJalali = Jalalian::now();
        $this->invoice_year = (string) $nowJalali->getYear();
        $this->invoice_month = (string) $nowJalali->getMonth();
        $this->invoice_day = (string) $nowJalali->getDay();
        $this->syncInvoiceDateFromJalali();

        $this->profile_id = auth()->user()->profile->id ?? null;

        $this->addItem();

        Flux::modal('save')->show();
    }

    public function updatedInvoiceYear()
    {
        $this->syncInvoiceDateFromJalali();
    }

    public function updatedInvoiceMonth()
    {
        $this->syncInvoiceDateFromJalali();
    }

    public function updatedInvoiceDay()
    {
        $this->syncInvoiceDateFromJalali();
    }

    protected function syncInvoiceDateFromJalali(): void
    {
        if (filled($this->invoice_year) && filled($this->invoice_month) && filled($this->invoice_day)) {
            try {
                $this->invoice_date = (new Jalalian((int) $this->invoice_year, (int) $this->invoice_month, (int) $this->invoice_day))->toCarbon()->format('Y-m-d');
            } catch (\Throwable $e) {
                $this->invoice_date = '';
            }
        } else {
            $this->invoice_date = '';
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'product_batch_id' => '',
            'quantity' => 1,
            'price' => 0,
            'subtotal' => 0,
        ];

        $this->calculateTotal();
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }

        if (count($this->items) === 0) {
            $this->addItem();
        }

        $this->calculateTotal();
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);

        if (count($parts) < 2) {
            $this->calculateTotal();

            return;
        }

        $index = (int) $parts[0];
        $field = $parts[1];

        if (! isset($this->items[$index])) {
            return;
        }

        if ($field === 'product_batch_id') {
            $batchId = $this->items[$index]['product_batch_id'] ?? null;

            if ($batchId) {
                $batch = ProductBatch::find($batchId);

                $this->items[$index]['price'] = $batch
                    ? (float) ($batch->sale_price ?? 0)
                    : 0;
            } else {
                $this->items[$index]['price'] = 0;
            }
        }

        $quantity = (int) ($this->items[$index]['quantity'] ?? 0);
        $price = (float) ($this->items[$index]['price'] ?? 0);

        $this->items[$index]['subtotal'] = $quantity * $price;

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $total = 0;

        foreach ($this->items as $index => $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $subtotal = $quantity * $price;

            $this->items[$index]['subtotal'] = $subtotal;
            $total += $subtotal;
        }

        $this->total_p = $total;
    }

    protected function rules()
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_year' => ['required'],
            'invoice_month' => ['required'],
            'invoice_day' => ['required'],
            'invoice_date' => ['required', 'date'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_batch_id' => [
                'required',
                'exists:product_batches,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    protected function messages()
    {
        return [
            'customer_id.required' => 'انتخاب مشتری الزامی است.',
            'customer_id.exists' => 'مشتری انتخاب شده معتبر نیست.',

            'invoice_year.required' => 'سال فاکتور الزامی است.',
            'invoice_month.required' => 'ماه فاکتور الزامی است.',
            'invoice_day.required' => 'روز فاکتور الزامی است.',
            'invoice_date.required' => 'تاریخ فاکتور معتبر نیست.',
            'invoice_date.date' => 'فرمت تاریخ معتبر نیست.',

            'items.required' => 'حداقل یک آیتم لازم است.',
            'items.min' => 'حداقل یک آیتم لازم است.',

            'items.*.product_batch_id.required' => 'انتخاب محصول الزامی است.',
            'items.*.product_batch_id.exists' => 'محصول انتخاب شده معتبر نیست.',

            'items.*.quantity.required' => 'تعداد الزامی است.',
            'items.*.quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'items.*.quantity.min' => 'تعداد باید حداقل 1 باشد.',
        ];
    }

    public function save()
    {
        $this->syncInvoiceDateFromJalali();
        $this->calculateTotal();
        $this->validate();

        try {
            DB::transaction(function () {
                $invoice = new Invoice;
                $invoice->total_price = $this->total_p;
                $invoice->invoice_date = $this->invoice_date;
                $invoice->customer_id = $this->customer_id;
                $invoice->profile_id = $this->profile_id;
                $invoice->save();

                foreach ($this->items as $index => $item) {
                    $batch = ProductBatch::query()
                        ->with('product')
                        ->lockForUpdate()
                        ->findOrFail($item['product_batch_id']);

                    $requestedQuantity = (int) $item['quantity'];
                    $availableQuantity = (int) $batch->quantity;

                    if ($requestedQuantity > $availableQuantity) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" =>
                                "موجودی محصول '{$batch->product?->name}' کافی نیست. "
                                . "موجودی فعلی: {$availableQuantity}",
                        ]);
                    }

                    $price = (float) $batch->sale_price;
                    $subtotal = $requestedQuantity * $price;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_batch_id' => $batch->id,
                        'quantity' => $requestedQuantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $batch->quantity = $availableQuantity - $requestedQuantity;
                    $batch->save();
                }
            });

            Flux::modal('save')->close();

            $this->resetForm();
            $this->profile_id = auth()->user()->profile->id ?? null;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'خطا در ثبت فاکتور: ' . $e->getMessage()
            );
        }
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'profile',
            'items.productBatch.product',
        ]);

        $this->resetForm();
        $this->resetValidation();

        $this->invoice_id = $invoice->id;
        $this->total_p = (float) $invoice->total_price;
        $this->invoice_date = $invoice->invoice_date;

        if ($invoice->invoice_date) {
            try {
                $jDate = Jalalian::fromCarbon(\Carbon\Carbon::parse($invoice->invoice_date));
                $this->invoice_year = (string) $jDate->getYear();
                $this->invoice_month = (string) $jDate->getMonth();
                $this->invoice_day = (string) $jDate->getDay();
            } catch (\Throwable $e) {
                $this->invoice_year = '';
                $this->invoice_month = '';
                $this->invoice_day = '';
            }
        }

        $this->customers_name = $invoice->customer->shop_name ?? '';
        $this->seller_name = trim(
            ($invoice->profile->first_name ?? '') . ' '
            . ($invoice->profile->last_name ?? '')
        );
        $this->customer_id = $invoice->customer_id;
        $this->profile_id = $invoice->profile_id;

        $this->items = [];

        foreach ($invoice->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'product_batch_id' => $item->product_batch_id,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ];
        }

        if (count($this->items) === 0) {
            $this->addItem();
        }

        $this->calculateTotal();

        Flux::modal('edit')->show();
    }

    public function update()
    {
        $this->syncInvoiceDateFromJalali();
        $this->calculateTotal();
        $this->validate();

        try {
            DB::transaction(function () {
                $invoice = Invoice::with('items')
                    ->lockForUpdate()
                    ->findOrFail($this->invoice_id);

                foreach ($invoice->items as $oldItem) {
                    $oldBatch = ProductBatch::lockForUpdate()
                        ->find($oldItem->product_batch_id);

                    if ($oldBatch) {
                        $oldBatch->quantity =
                            (int) $oldBatch->quantity
                            + (int) $oldItem->quantity;

                        $oldBatch->save();
                    }
                }

                $invoice->items()->delete();

                $invoice->total_price = $this->total_p;
                $invoice->invoice_date = $this->invoice_date;
                $invoice->customer_id = $this->customer_id;
                $invoice->profile_id = $this->profile_id;
                $invoice->save();

                foreach ($this->items as $index => $item) {
                    $batch = ProductBatch::query()
                        ->with('product')
                        ->lockForUpdate()
                        ->findOrFail($item['product_batch_id']);

                    $requestedQuantity = (int) $item['quantity'];
                    $availableQuantity = (int) $batch->quantity;

                    if ($requestedQuantity > $availableQuantity) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" =>
                                "موجودی محصول '{$batch->product?->name}' کافی نیست. "
                                . "موجودی فعلی: {$availableQuantity}",
                        ]);
                    }

                    $price = (float) $batch->sale_price;
                    $subtotal = $requestedQuantity * $price;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_batch_id' => $batch->id,
                        'quantity' => $requestedQuantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $batch->quantity = $availableQuantity - $requestedQuantity;
                    $batch->save();
                }
            });

            Flux::modal('edit')->close();

            $this->resetForm();
            $this->profile_id = auth()->user()->profile->id ?? null;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'خطا در ویرایش فاکتور: ' . $e->getMessage()
            );
        }
    }

    public function del_form(Invoice $invoice)
    {
        $this->invoice_to_delete_id = $invoice->id;

        Flux::modal('delete')->show();
    }

    public function delete()
    {
        try {
            DB::transaction(function () {
                $invoice = Invoice::with('items')
                    ->lockForUpdate()
                    ->findOrFail($this->invoice_to_delete_id);

                foreach ($invoice->items as $item) {
                    $batch = ProductBatch::lockForUpdate()
                        ->find($item->product_batch_id);

                    if ($batch) {
                        $batch->quantity =
                            (int) $batch->quantity
                            + (int) $item->quantity;

                        $batch->save();
                    }
                }

                $invoice->delete();
            });

            $this->invoice_to_delete_id = null;

            Flux::modal('delete')->close();
        } catch (\Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'خطا در حذف فاکتور: ' . $e->getMessage()
            );
        }
    }

    public function resetForm()
    {
        $this->resetValidation();

        $this->invoice_id = '';
        $this->total_p = 0;
        $this->invoice_date = '';
        $this->invoice_year = '';
        $this->invoice_month = '';
        $this->invoice_day = '';
        $this->customers_name = '';
        $this->seller_name = '';
        $this->customer_id = '';
        $this->invoice_to_delete_id = null;
        $this->items = [];
    }
};
