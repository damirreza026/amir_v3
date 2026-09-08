<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public Invoice $invoice;

    public $sortBy = 'quantity';

    public $sortDirection = 'desc';

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['customer', 'profile']);
    }

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
    public function invoiceItems()
    {
        return InvoiceItem::query()
            ->with(['productBatch.product'])
            ->where('invoice_id', $this->invoice->id)
            ->when(
                $this->sortBy,
                fn ($query) => $query->orderBy($this->sortBy, $this->sortDirection)
            )
            ->paginate(15);
    }

    #[Computed]
    public function allInvoiceItems()
    {
        return InvoiceItem::query()
            ->with(['productBatch.product'])
            ->where('invoice_id', $this->invoice->id)
            ->orderBy('id', 'asc') // مرتب‌سازی ثابت برای چاپ
            ->get();
    }
};
