@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Réception - {{ $purchase->purchase_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la commande
        </a>
    </div>
</div>

<form action="{{ route('purchases.process-reception', $purchase->id) }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <!-- Informations fournisseur -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-truck me-2"></i>Informations fournisseur
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ $purchase->supplier->name }}</strong>
                            @if($purchase->supplier->contact_person)
                                <br>Contact: {{ $purchase->supplier->contact_person }}
                            @endif
                            @if($purchase->supplier->phone_number)
                                <br><i class="fas fa-phone me-1"></i>{{ $purchase->supplier->phone_number }}
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Commande:</strong> {{ $purchase->purchase_number }}<br>
                            <strong>Date commande:</strong> {{ $purchase->order_date->format('d/m/Y') }}<br>
                            @if($purchase->expected_date)
                                <strong>Date prévue:</strong> {{ $purchase->expected_date->format('d/m/Y') }}
                                @if($purchase->expected_date->isPast())
                                    <span class="text-danger">(En retard)</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produits à recevoir -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Produits à recevoir</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-center">Commandé</th>
                                    <th class="text-center">Déjà reçu</th>
                                    <th class="text-center">Reste à recevoir</th>
                                    <th class="text-center">Quantité reçue</th>
                                    <th>Stock actuel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseItems as $item)
                                    <tr class="{{ $item->product->stock_quantity <= $item->product->stock_threshold ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->product->dosage)
                                                <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                            @endif
                                            @if($item->notes)
                                                <br><small class="text-info">{{ $item->notes }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity_ordered }}</td>
                                        <td class="text-center">{{ $item->quantity_received }}</td>
                                        <td class="text-center">
                                            <strong>{{ $item->remaining_quantity }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if($item->remaining_quantity > 0)
                                                <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                                                <input type="number" 
                                                       class="form-control text-center quantity-input" 
                                                       name="items[{{ $loop->index }}][quantity_received]" 
                                                       value="{{ $item->remaining_quantity }}"
                                                       min="0" 
                                                       max="{{ $item->remaining_quantity }}"
                                                       style="width: 80px;">
                                            @else
                                                <span class="text-success">Complet</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $item->product->stock_quantity <= $item->product->stock_threshold ? 'text-danger' : 'text-success' }}">
                                                {{ $item->product->stock_quantity }}
                                            </span>
                                            @if($item->product->stock_quantity <= $item->product->stock_threshold)
                                                <br><small class="text-danger">Stock faible!</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Notes de réception</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" name="notes" rows="4" 
                                  placeholder="Notes sur la réception, problèmes rencontrés...">{{ old('notes', $purchase->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Aide -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-1"></i>Aide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <strong>💡 Conseils :</strong>
                        <ul class="mt-2 mb-0">
                            <li>Vérifiez la qualité des produits reçus</li>
                            <li>Contrôlez les dates d'expiration</li>
                            <li>Notez les éventuels problèmes</li>
                            <li>Le stock sera automatiquement mis à jour</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success" id="receiveBtn">
                            <i class="fas fa-check me-1"></i> Enregistrer la réception
                        </button>
                        <button type="button" class="btn btn-warning" onclick="fillAllRemaining()">
                            <i class="fas fa-fill me-1"></i> Tout recevoir
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearAll()">
                            <i class="fas fa-eraser me-1"></i> Tout vider
                        </button>
                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-outline-secondary">
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
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const value = parseInt(this.value);
            
            if (value > max) {
                this.value = max;
                alert('Quantité ajustée au maximum disponible: ' + max);
            }
            
            if (value < 0) {
                this.value = 0;
            }
        });
    });
    
    document.getElementById('receiveBtn').addEventListener('click', function(e) {
        const quantities = [];
        quantityInputs.forEach(input => {
            if (parseInt(input.value) > 0) {
                quantities.push(input.value);
            }
        });
        
        if (quantities.length === 0) {
            e.preventDefault();
            alert('Veuillez saisir au moins une quantité à recevoir.');
            return;
        }
        
        const confirm = window.confirm('Confirmer la réception de ces produits ? Le stock sera automatiquement mis à jour.');
        if (!confirm) {
            e.preventDefault();
        }
    });
});

function fillAllRemaining() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        const max = parseInt(input.getAttribute('max'));
        input.value = max;
    });
}

function clearAll() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.value = 0;
    });
}
</script>
@endsection