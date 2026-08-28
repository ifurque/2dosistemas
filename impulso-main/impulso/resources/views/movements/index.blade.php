@extends('layouts.app')

@section('content')
<section class="dashboard wrap">
  <a class="back" href="{{ route('dashboard', $business) }}">← Volver al panel</a>

  <div class="dashboard-top">
    <div>
      <p class="eyebrow">Finanzas de {{ $business->name }}</p>
      <h1>Movimientos.</h1>
      <p class="muted">Consulta gastos e ingresos en un solo lugar.</p>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="panel income-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Ingresos</p>
          <h2>Registrar pago entrante</h2>
        </div>
        <div class="income-total-box">
          <span>Monto total</span>
          <strong id="receipt-total">$ 0</strong>
        </div>
      </div>

      <p class="muted">Pulsa el botón + para armar un comprobante con productos y servicios del catálogo.</p>

      <div class="catalog-list" id="catalog-list">
        @forelse($business->products as $product)
          <article class="catalog-item">
            <div>
              <strong>{{ $product->name }}</strong>
              <small>{{ $product->type === 'product' ? 'Producto' : 'Servicio' }} · @if($product->price !== null)$ {{ number_format($product->price, 0, ',', '.') }}@else Sin precio @endif · Stock {{ $product->stock ?? 0 }}</small>
            </div>
            <button
              type="button"
              class="button button-small add-item"
              data-product-id="{{ $product->id }}"
              data-product-name="{{ e($product->name) }}"
              data-product-type="{{ $product->type }}"
              data-product-price="{{ $product->price ?? 0 }}"
              data-product-stock="{{ $product->stock ?? 0 }}"
              @disabled($product->type === 'product' && (int) ($product->stock ?? 0) <= 0)
            >+</button>
          </article>
        @empty
          <p class="muted">Todavía no agregaste productos ni servicios al catálogo.</p>
        @endforelse
      </div>

      <form method="POST" action="{{ route('incomes.store', $business) }}" class="form" id="income-form">
        @csrf
        <label>Descripción / referencia<input name="description" placeholder="Ej. Venta mostrador, corte y producto"></label>
        <label>Origen
          <select name="source">
            <option value="sale">Venta</option>
            <option value="service">Servicio</option>
            <option value="other">Otro</option>
          </select>
        </label>

        <div class="receipt-box">
          <div class="receipt-head">
            <strong>Comprobante</strong>
            <small>Lista de productos y servicios incluidos en el ingreso.</small>
          </div>
          <div class="receipt-empty" id="receipt-empty">Agregá items con el botón +.</div>
          <div class="receipt-items" id="receipt-items"></div>
        </div>

        <label>Monto total<input type="text" id="income-total-input" value="$ 0" readonly></label>
        <label>Fecha<input name="income_date" type="date" value="{{ now()->format('Y-m-d') }}" required></label>
        <button class="button">Registrar ingreso <span>→</span></button>
      </form>
    </div>

    <div class="panel">
      <p class="eyebrow">Egresos</p>
      <h2>Gastos realizados</h2>
      <p class="muted">Consulta y registra tus gastos desde el panel de gastos.</p>
      <a class="button" href="{{ route('expenses.index', $business) }}">Ir a gastos <span>→</span></a>
      <div class="expense-list">
        @forelse($expenses as $expense)
          <div>
            <span class="expense-icon">−</span>
            <span>
              <strong>{{ $expense->description }}</strong>
              <small>{{ $expense->category->name }} · {{ $expense->expense_date->format('d/m/Y') }}</small>
            </span>
            <b>$ {{ number_format($expense->amount, 0, ',', '.') }}</b>
          </div>
        @empty
          <p class="muted">No hay gastos registrados.</p>
        @endforelse
      </div>
    </div>
  </div>

  <div class="panel recent">
    <div class="panel-head">
      <div>
        <p class="eyebrow">Pagos entrantes</p>
        <h2>Ingresos recientes</h2>
      </div>
    </div>
    <div class="expense-list">
      @forelse($incomes as $income)
        <div>
          <span class="expense-icon income-icon">+</span>
          <span>
            <strong>{{ $income->description }}</strong>
            <small>{{ ucfirst($income->source) }} · {{ $income->income_date->format('d/m/Y') }}</small>
            @if(!empty($income->items))
              <small class="receipt-summary">{{ collect($income->items)->map(fn ($item) => $item['name'].' x'.$item['quantity'])->implode(' · ') }}</small>
            @endif
          </span>
          <b>$ {{ number_format($income->amount, 0, ',', '.') }}</b>
        </div>
      @empty
        <p class="muted">Todavía no registraste pagos entrantes.</p>
      @endforelse
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const catalogButtons = document.querySelectorAll('.add-item');
  const receiptItems = document.getElementById('receipt-items');
  const receiptEmpty = document.getElementById('receipt-empty');
  const totalView = document.getElementById('receipt-total');
  const totalInput = document.getElementById('income-total-input');
  const descriptionInput = document.querySelector('input[name="description"]');
  const receiptState = new Map();

  const formatMoney = (value) => new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    maximumFractionDigits: 0,
  }).format(value);

  const render = () => {
    const items = Array.from(receiptState.values());
    receiptItems.innerHTML = '';
    receiptEmpty.style.display = items.length ? 'none' : 'block';

    let total = 0;

    items.forEach((item, index) => {
      const subtotal = item.price * item.quantity;
      total += subtotal;

      const row = document.createElement('div');
      row.className = 'receipt-row';
      row.innerHTML = `
        <div>
          <strong>${item.name}</strong>
          <small>${item.type === 'product' ? 'Producto' : 'Servicio'} · ${formatMoney(item.price)}</small>
        </div>
        <div class="receipt-controls">
          <button type="button" class="plain-button" data-action="decrease" data-product-id="${item.id}">−</button>
          <input type="number" min="1" step="1" value="${item.quantity}" data-action="quantity" data-product-id="${item.id}">
          <button type="button" class="plain-button" data-action="increase" data-product-id="${item.id}">+</button>
        </div>
        <b>${formatMoney(subtotal)}</b>
        <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
        <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
      `;
      receiptItems.appendChild(row);
    });

    totalView.textContent = formatMoney(total);
    totalInput.value = formatMoney(total);

    if (!descriptionInput.value.trim()) {
      descriptionInput.placeholder = items.length
        ? 'Ej. Venta: ' + items.slice(0, 3).map((item) => `${item.name} x${item.quantity}`).join(', ')
        : 'Ej. Venta mostrador';
    }
  };

  const addItem = (button) => {
    const id = button.dataset.productId;
    const name = button.dataset.productName;
    const type = button.dataset.productType;
    const price = Number(button.dataset.productPrice || 0);
    const stock = Number(button.dataset.productStock || 0);

    if (type === 'product' && stock <= 0) {
      return;
    }

    const current = receiptState.get(id);
    const limit = type === 'product' ? stock : Infinity;

    if (current) {
      if (current.quantity >= limit) {
        return;
      }

      current.quantity += 1;
      receiptState.set(id, current);
    } else {
      receiptState.set(id, {
        id,
        name,
        type,
        price,
        stock,
        quantity: 1,
      });
    }

    render();
  };

  catalogButtons.forEach((button) => {
    button.addEventListener('click', () => addItem(button));
  });

  receiptItems.addEventListener('click', (event) => {
    const actionButton = event.target.closest('[data-action]');

    if (!actionButton) {
      return;
    }

    const productId = actionButton.dataset.productId;
    const item = receiptState.get(productId);

    if (!item) {
      return;
    }

    if (actionButton.dataset.action === 'increase') {
      if (item.type === 'product' && item.quantity >= item.stock) {
        return;
      }

      item.quantity += 1;
    }

    if (actionButton.dataset.action === 'decrease') {
      item.quantity -= 1;

      if (item.quantity < 1) {
        receiptState.delete(productId);
      } else {
        receiptState.set(productId, item);
      }
    }

    render();
  });

  receiptItems.addEventListener('input', (event) => {
    if (event.target.dataset.action !== 'quantity') {
      return;
    }

    const productId = event.target.dataset.productId;
    const item = receiptState.get(productId);

    if (!item) {
      return;
    }

    const quantity = Number(event.target.value || 1);
    const max = item.type === 'product' ? item.stock : Infinity;
    item.quantity = Math.max(1, Math.min(quantity, max));
    receiptState.set(productId, item);
    render();
  });

  render();
});
</script>
@endsection
