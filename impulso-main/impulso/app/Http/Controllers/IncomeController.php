<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Income;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class IncomeController extends Controller
{
    public function index(Business $business)
    {
        $this->ownerOnly($business);
        return view('movements.index', [
            'business' => $business->load(['products' => fn ($query) => $query->orderBy('type')->orderBy('name')]),
            'expenses' => $business->expenses()->with('category')->latest('expense_date')->paginate(10, ['*'], 'expenses_page'),
            'incomes' => $business->incomes()->with('product')->latest('income_date')->paginate(10, ['*'], 'incomes_page'),
        ]);
    }

    public function store(Request $request, Business $business)
    {
        $this->ownerOnly($business);

        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:160'],
            'income_date' => ['required', 'date'],
            'source' => ['required', 'in:sale,service,other'],
            'product_id' => ['nullable', 'exists:products,id'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ]);

        $items = $this->resolveItems($request, $data);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Agrega al menos un producto o servicio al comprobante.',
            ]);
        }

        DB::transaction(function () use ($business, $data, $items) {
            [$receiptItems, $totalAmount, $movementData] = $this->buildReceipt($business, $data, $items);
            $description = $this->buildDescription($data['description'] ?? null, $receiptItems);

            $income = $business->incomes()->create([
                'created_by' => Auth::id(),
                'product_id' => $receiptItems[0]['product_id'] ?? null,
                'description' => $description,
                'amount' => $totalAmount,
                'income_date' => $data['income_date'],
                'source' => $data['source'],
                'items' => $receiptItems,
            ]);

            foreach ($movementData as $movement) {
                StockMovement::create($movement + ['income_id' => $income->id]);
            }
        });

        return back()->with('success', 'Pago entrante registrado.');
    }

    private function ownerOnly(Business $business): void
    {
        abort_unless($business->owner_id === Auth::id() || Auth::user()?->role === 'superadmin', 403);
    }

    private function resolveItems(Request $request, array $data): Collection
    {
        $items = collect($data['items'] ?? [])
            ->map(fn (array $item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->values();

        if ($items->isEmpty() && $request->filled('product_id')) {
            return collect([
                [
                    'product_id' => (int) $data['product_id'],
                    'quantity' => 1,
                ],
            ]);
        }

        return $items;
    }

    private function buildReceipt(Business $business, array $data, Collection $items): array
    {
        $receiptItems = [];
        $totalAmount = 0;
        $movementData = [];

        foreach ($items as $item) {
            $product = $business->products()->whereKey($item['product_id'])->lockForUpdate()->firstOrFail();
            $unitPrice = (float) ($product->price ?? 0);
            $quantity = $item['quantity'];

            if ($data['source'] === 'sale' && $product->type === 'product' && (int) $product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => "No hay stock suficiente para {$product->name}.",
                ]);
            }

            $subtotal = $unitPrice * $quantity;
            $totalAmount += $subtotal;

            $receiptItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];

            if ($data['source'] === 'sale' && $product->type === 'product') {
                $stockBefore = (int) $product->stock;
                $product->decrement('stock', $quantity);
                $product->refresh();

                $movementData[] = [
                    'business_id' => $business->id,
                    'product_id' => $product->id,
                    'created_by' => Auth::id(),
                    'type' => 'sale',
                    'quantity_change' => -$quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => (int) $product->stock,
                    'unit_price' => $unitPrice,
                    'note' => 'Descuento automático por venta registrada.',
                ];
            }
        }

        return [$receiptItems, $totalAmount, $movementData];
    }

    private function buildDescription(?string $description, array $receiptItems): string
    {
        $description = trim((string) $description);

        if ($description !== '') {
            return $description;
        }

        $summary = collect($receiptItems)
            ->map(fn (array $item) => $item['name'].' x'.$item['quantity'])
            ->take(3)
            ->implode(', ');

        if (count($receiptItems) > 3) {
            $summary .= '...';
        }

        return $summary === '' ? 'Venta registrada' : 'Venta: '.$summary;
    }
}
