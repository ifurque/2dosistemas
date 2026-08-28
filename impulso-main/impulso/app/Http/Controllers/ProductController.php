<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private const NON_NEGATIVE_RULE = 'min:0';
    private const POSITIVE_INTEGER_RULE = 'min:1';

    public function store(Request $request, Business $business)
    {
        abort_unless($business->owner_id === Auth::id(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'type' => ['required', 'in:product,service'],
            'price' => ['nullable', 'numeric', self::NON_NEGATIVE_RULE],
            'duration' => ['nullable', 'integer', self::POSITIVE_INTEGER_RULE],
            'stock' => ['nullable', 'integer', self::NON_NEGATIVE_RULE],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('products', 'public');
        }

        $data['stock'] = $data['stock'] ?? 0;

        $product = $business->products()->create($data);

        if ($product->stock > 0) {
            $this->recordMovement($product, [
                'type' => 'initial_stock',
                'quantity_change' => $product->stock,
                'stock_before' => 0,
                'stock_after' => $product->stock,
                'unit_price' => $product->price,
                'note' => 'Stock inicial cargado desde el catálogo.',
            ]);
        }

        return back()->with('success', 'Propuesta agregada a tu perfil.');
    }

    public function updateStock(Request $request, Business $business, Product $product)
    {
        abort_unless($business->owner_id === Auth::id(), 403);
        abort_unless($product->business_id === $business->id, 404);

        $data = $request->validate([
            'price' => ['nullable', 'numeric', self::NON_NEGATIVE_RULE],
            'stock' => ['required', 'integer', self::NON_NEGATIVE_RULE],
        ]);

        $stockBefore = (int) $product->stock;
        $stockAfter = (int) $data['stock'];

        $product->update($data);

        if ($stockBefore !== $stockAfter) {
            $this->recordMovement($product->fresh(), [
                'type' => 'manual_adjustment',
                'quantity_change' => $stockAfter - $stockBefore,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'unit_price' => $product->price,
                'note' => 'Ajuste manual desde el panel de administración.',
            ]);
        }

        return back()->with('success', 'Precio y stock actualizados.');
    }

    public function destroy(Business $business, Product $product)
    {
        abort_unless($business->owner_id === Auth::id(), 403);
        abort_unless($product->business_id === $business->id, 404);
        $product->delete();
        return back()->with('success', 'Propuesta eliminada.');
    }

    public function recordMovement(Product $product, array $attributes): void
    {
        StockMovement::create([
            'business_id' => $product->business_id,
            'product_id' => $product->id,
            'created_by' => Auth::id(),
            ...$attributes,
        ]);
    }
}
