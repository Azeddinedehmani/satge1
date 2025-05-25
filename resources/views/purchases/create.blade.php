@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Nouvelle commande d'achat</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour aux achats
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

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label">Fournisseur <span class="text-danger">*</span></label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                                <option value="">Sélectionner un fournisseur</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                            {{ old('supplier_id', $selectedSupplierId ?? '') == $supplier->id ? 'selected' : '' }}
                                            data-contact="{{ $supplier->contact_person }}"
                                            data-phone="{{ $supplier->phone_number }}"
                                            data-email="{{ $supplier->email }}">
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="order_date" class="form-label">Date de commande <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                   id="order_date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expected_date" class="form-label">Date de livraison prévue</label>
                            <input type="date" class="form-control @error('expected_date') is-invalid @enderror" 
                                   id="expected_date" name="expected_date" value="{{ old('expected_date') }}">
                            @error('expected_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Notes sur la commande...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Produits à commander</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product_search" class="form-label">Ajouter un produit</label>
                        <select class="form-select" id="product_search">
                            <option value="">Sélectionner un produit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-name="{{ $product->name }}"
                                        data-dosage="{{ $product->dosage }}"
                                        data-current-stock="{{ $product->stock_quantity }}"
                                        data-threshold="{{ $product->stock_threshold }}"
                                        data-purchase-price="{{ $product->purchase_price }}">
                                    {{ $product->name }} 
                                    {{ $product->dosage ? '- ' . $product->dosage : '' }}
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
                                    <th width="80">Stock actuel</th>
                                    <th width="100">Quantité</th>
                                    <th width="120">Prix unitaire</th>
                                    <th width="120">Total</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <tr id="noProductsRow">
                                    <td colspan="6" class="text-center text-muted">
                                        Aucun produit ajouté
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Sous-total:</th>
                                    <th id="subtotal">0.00 €</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">TVA (20%):</th>
                                    <th id="tax">0.00 €</th>
                                    <th></th>
                                </tr>
                                <tr class="table-primary">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th id="total">0.00 €</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @error('products')
                        <div class="alert alert-danger mt-3">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Informations fournisseur -->
            <div class="card mb-3" id="supplierInfo" style="display: none;">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-truck me-2"></i>Informations fournisseur
                    </h5>
                </div>
                <div class="card-body">
                    <div id="supplierDetails"></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Résumé de la commande</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Fournisseur:</strong> <span id="selectedSupplier">Non sélectionné</span>
                    </div>
                    <div class="mb-2">
                        <strong>Date de commande:</strong> <span id="selectedDate">{{ date('d/m/Y') }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Date prévue:</strong> <span id="selectedExpectedDate">Non définie</span>
                    </div>
                    <div>
                        <strong>Nombre de produits:</strong> <span id="itemsCount">0</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save me-1"></i> Créer la commande
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
let productCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    const supplierSelect = document.getElementById('supplier_id');
    const productSearch = document.getElementById('product_search');
    const orderDate = document.getElementById('order_date');
    const expectedDate = document.getElementById('expected_date');

    // Gérer la sélection du fournisseur
    supplierSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        
        document.getElementById('selectedSupplier').textContent = option.text || 'Non sélectionné';
        
        // Afficher les informations du fournisseur
        const supplierInfo = document.getElementById('supplierInfo');
        const supplierDetails = document.getElementById('supplierDetails');
        
        if (this.value) {
            const contact = option.dataset.contact;
            const phone = option.dataset.phone;
            const email = option.dataset.email;
            
            let details = '<div class="mb-1"><strong>' + option.text + '</strong></div>';
            if (contact) details += '<div class="mb-1"><i class="fas fa-user me-1"></i>' + contact + '</div>';
            if (phone) details += '<div class="mb-1"><i class="fas fa-phone me-1"></i>' + phone + '</div>';
            if (email) details += '<div class="mb-1"><i class="fas fa-envelope me-1"></i>' + email + '</div>';
            
            supplierDetails.innerHTML = details;
            supplierInfo.style.display = 'block';
        } else {
            supplierInfo.style.display = 'none';
        }
        
        updateSubmitButton();
    });

    // Trigger sur la sélection pré-existante
    if (supplierSelect.value) {
        supplierSelect.dispatchEvent(new Event('change'));
    }

    // Mettre à jour les dates affichées
    orderDate.addEventListener('change', function() {
        const date = new Date(this.value);
        document.getElementById('selectedDate').textContent = date.toLocaleDateString('fr-FR');
    });
    
    expectedDate.addEventListener('change', function() {
        const date = this.value ? new Date(this.value) : null;
        document.getElementById('selectedExpectedDate').textContent = date ? date.toLocaleDateString('fr-FR') : 'Non définie';
    });

    // Ajouter un produit
    productSearch.addEventListener('change', function() {
        if (this.value) {
            const option = this.options[this.selectedIndex];
            const productData = {
                id: this.value,
                name: option.dataset.name,
                dosage: option.dataset.dosage,
                currentStock: parseInt(option.dataset.currentStock),
                threshold: parseInt(option.dataset.threshold),
                purchasePrice: parseFloat(option.dataset.purchasePrice)
            };

            addProduct(productData);
            this.value = '';
        }
    });

    // Submit form validation
    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        const tbody = document.getElementById('productsTableBody');
        const hasProducts = tbody.querySelectorAll('tr[data-product-id]').length > 0;
        
        if (!hasProducts) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit à la commande.');
            return false;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Création...';
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
        alert('Ce produit est déjà dans la liste');
        return;
    }

    // Calculer la quantité suggérée (seuil - stock actuel, minimum 1)
    const suggestedQty = Math.max(1, product.threshold - product.currentStock);

    const row = document.createElement('tr');
    row.setAttribute('data-product-id', product.id);
    row.innerHTML = `
        <td>
            <strong>${product.name}</strong>
            ${product.dosage ? '<br><small class="text-muted">' + product.dosage + '</small>' : ''}
            <input type="hidden" name="products[${productCounter}][id]" value="${product.id}">
        </td>
        <td class="text-center">
            <span class="${product.currentStock <= product.threshold ? 'text-danger' : 'text-success'}">
                ${product.currentStock}
            </span>
            <br><small class="text-muted">Seuil: ${product.threshold}</small>
        </td>
        <td>
            <input type="number" class="form-control quantity-input" name="products[${productCounter}][quantity]" 
                   value="${suggestedQty}" min="1" onchange="updateRowTotal(this.closest('tr'))" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control price-input" name="products[${productCounter}][price]" 
                   value="${product.purchasePrice.toFixed(2)}" min="0" onchange="updateRowTotal(this.closest('tr'))" required>
        </td>
        <td>
            <span class="row-total">${(suggestedQty * product.purchasePrice).toFixed(2)} €</span>
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
    updateItemsCount();
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
                <td colspan="6" class="text-center text-muted">
                    Aucun produit ajouté
                </td>
            </tr>
        `;
    }

    calculateTotals();
    updateItemsCount();
    updateSubmitButton();
}

function updateRowTotal(row) {
    const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = quantity * price;
    
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

    const tax = subtotal * 0.20;
    const total = subtotal + tax;

    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' €';
    document.getElementById('tax').textContent = tax.toFixed(2) + ' €';
    document.getElementById('total').textContent = total.toFixed(2) + ' €';
}

function updateItemsCount() {
    const count = document.querySelectorAll('#productsTableBody tr[data-product-id]').length;
    document.getElementById('itemsCount').textContent = count;
}

function updateSubmitButton() {
    const hasSupplier = document.getElementById('supplier_id').value !== '';
    const hasProducts = document.querySelectorAll('#productsTableBody tr[data-product-id]').length > 0;
    const submitBtn = document.getElementById('submitBtn');
    
    submitBtn.disabled = !(hasSupplier && hasProducts);
}
</script>
@endsection