@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Nouvelle vente</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour aux ventes
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('sales.store') }}" method="POST" id="saleForm">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Produits</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product_search" class="form-label">Rechercher un produit</label>
                        <select class="form-select" id="product_search">
                            <option value="">Sélectionner un produit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-name="{{ $product->name }}"
                                        data-dosage="{{ $product->dosage ?? '' }}"
                                        data-price="{{ $product->selling_price }}"
                                        data-stock="{{ $product->stock_quantity }}"
                                        data-prescription="{{ $product->prescription_required ? 'true' : 'false' }}">
                                    {{ $product->name }} 
                                    @if($product->dosage) - {{ $product->dosage }} @endif
                                    - {{ number_format($product->selling_price, 2) }}€ 
                                    (Stock: {{ $product->stock_quantity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th width="100">Quantité</th>
                                    <th width="120">Prix unitaire</th>
                                    <th width="120">Total</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <tr id="noProductsRow">
                                    <td colspan="5" class="text-center text-muted">
                                        Aucun produit ajouté
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Sous-total:</th>
                                    <th id="subtotal">0.00 €</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">TVA (20%):</th>
                                    <th id="tax">0.00 €</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Remise:</th>
                                    <th>
                                        <input type="number" class="form-control form-control-sm" id="discount" name="discount_amount" 
                                               min="0" step="0.01" value="{{ old('discount_amount', 0) }}" onchange="calculateTotals()">
                                    </th>
                                    <th></th>
                                </tr>
                                <tr class="table-primary">
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th id="total">0.00 €</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations client</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id">
                            <option value="">Client anonyme</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" 
                                        {{ old('client_id', $selectedClientId ?? '') == $client->id ? 'selected' : '' }}
                                        data-allergies="{{ $client->allergies ?? '' }}">
                                    {{ $client->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Alerte allergies client -->
                    <div class="alert alert-warning" id="allergiesAlert" style="display: none;">
                        <strong><i class="fas fa-exclamation-triangle me-1"></i>Allergies connues:</strong>
                        <div id="allergiesText"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Paiement</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                            <option value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'selected' : '' }}>Espèces</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Carte bancaire</option>
                            <option value="insurance" {{ old('payment_method') == 'insurance' ? 'selected' : '' }}>Assurance</option>
                            <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Ordonnance</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_prescription" name="has_prescription" 
                                   value="1" {{ old('has_prescription') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_prescription">
                                Vente avec ordonnance
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="prescription_number_field" style="display: {{ old('has_prescription') ? 'block' : 'none' }};">
                        <label for="prescription_number" class="form-label">Numéro d'ordonnance</label>
                        <input type="text" class="form-control @error('prescription_number') is-invalid @enderror" 
                               id="prescription_number" name="prescription_number" value="{{ old('prescription_number') }}">
                        @error('prescription_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" placeholder="Notes additionnelles...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-success px-4" id="submitBtn" disabled>
                <i class="fas fa-save me-1"></i> Enregistrer la vente
            </button>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
let productCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    const productSearch = document.getElementById('product_search');
    const hasPrescription = document.getElementById('has_prescription');
    const prescriptionField = document.getElementById('prescription_number_field');
    const clientSelect = document.getElementById('client_id');

    // Gérer l'affichage du champ numéro d'ordonnance
    hasPrescription.addEventListener('change', function() {
        if (this.checked) {
            prescriptionField.style.display = 'block';
        } else {
            prescriptionField.style.display = 'none';
        }
    });

    // Gérer l'affichage des allergies client
    clientSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const allergies = selectedOption.dataset.allergies;
        const allergiesAlert = document.getElementById('allergiesAlert');
        const allergiesText = document.getElementById('allergiesText');
        
        if (allergies && allergies.trim() !== '') {
            allergiesText.textContent = allergies;
            allergiesAlert.style.display = 'block';
        } else {
            allergiesAlert.style.display = 'none';
        }
    });

    // Trigger allergies check on page load if client is pre-selected
    if (clientSelect.value) {
        clientSelect.dispatchEvent(new Event('change'));
    }

    // Ajouter un produit
    productSearch.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const productData = {
                id: this.value,
                name: option.dataset.name,
                dosage: option.dataset.dosage,
                price: parseFloat(option.dataset.price),
                stock: parseInt(option.dataset.stock),
                prescription: option.dataset.prescription === 'true'
            };

            addProduct(productData);
            this.value = '';
        }
    });

    // Submit form validation
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const tbody = document.getElementById('productsTableBody');
        const hasProducts = tbody.querySelectorAll('tr[data-product-id]').length > 0;
        
        if (!hasProducts) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit à la vente.');
            return false;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...';
    });
});

function addProduct(product) {
    const tbody = document.getElementById('productsTableBody');
    const noProductsRow = document.getElementById('noProductsRow');
    
    // Supprimer la ligne "Aucun produit"
    if (noProductsRow) {
        noProductsRow.remove();
    }

    // Vérifier si le produit existe déjà
    const existingRow = document.querySelector(`tr[data-product-id="${product.id}"]`);
    if (existingRow) {
        const quantityInput = existingRow.querySelector('.quantity-input');
        const currentQty = parseInt(quantityInput.value);
        const newQty = currentQty + 1;
        
        if (newQty <= product.stock) {
            quantityInput.value = newQty;
            updateRowTotal(existingRow);
        } else {
            alert(`Stock insuffisant. Maximum disponible: ${product.stock}`);
        }
        return;
    }

    const row = document.createElement('tr');
    row.setAttribute('data-product-id', product.id);
    row.innerHTML = `
        <td>
            ${product.name}
            ${product.dosage ? '<br><small class="text-muted">' + product.dosage + '</small>' : ''}
            ${product.prescription ? '<br><small class="text-warning"><i class="fas fa-prescription-bottle me-1"></i>Ordonnance requise</small>' : ''}
            <input type="hidden" name="products[${productCounter}][id]" value="${product.id}">
        </td>
        <td>
            <input type="number" class="form-control quantity-input" name="products[${productCounter}][quantity]" 
                   value="1" min="1" max="${product.stock}" onchange="updateRowTotal(this.closest('tr'))" required>
        </td>
        <td>
            <span class="unit-price">${product.price.toFixed(2)} €</span>
        </td>
        <td>
            <span class="row-total">${product.price.toFixed(2)} €</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeProduct(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    productCounter++;
    calculateTotals();
    updateSubmitButton();
}

function removeProduct(button) {
    const row = button.closest('tr');
    row.remove();

    // Si plus de produits, afficher la ligne "Aucun produit"
    const tbody = document.getElementById('productsTableBody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="noProductsRow">
                <td colspan="5" class="text-center text-muted">
                    Aucun produit ajouté
                </td>
            </tr>
        `;
    }

    calculateTotals();
    updateSubmitButton();
}

function updateRowTotal(row) {
    const quantity = parseInt(row.querySelector('.quantity-input').value);
    const unitPriceText = row.querySelector('.unit-price').textContent;
    const unitPrice = parseFloat(unitPriceText.replace(' €', ''));
    const total = quantity * unitPrice;
    
    row.querySelector('.row-total').textContent = total.toFixed(2) + ' €';
    calculateTotals();
}

function calculateTotals() {
    const rows = document.querySelectorAll('#productsTableBody tr[data-product-id]');
    let subtotal = 0;

    rows.forEach(row => {
        const totalText = row.querySelector('.row-total').textContent;
        const total = parseFloat(totalText.replace(' €', ''));
        subtotal += total;
    });

    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const tax = subtotal * 0.20;
    const total = subtotal + tax - discount;

    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' €';
    document.getElementById('tax').textContent = tax.toFixed(2) + ' €';
    document.getElementById('total').textContent = total.toFixed(2) + ' €';
}

function updateSubmitButton() {
    const tbody = document.getElementById('productsTableBody');
    const hasProducts = tbody.querySelectorAll('tr[data-product-id]').length > 0;
    const submitBtn = document.getElementById('submitBtn');
    
    submitBtn.disabled = !hasProducts;
}
</script>
@endsection