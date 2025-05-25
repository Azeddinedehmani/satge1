@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Modifier la commande {{ $purchase->purchase_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la commande
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

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

<form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fournisseur</label>
                            <input type="text" class="form-control" value="{{ $purchase->supplier->name }}" readonly>
                            <small class="text-muted">Le fournisseur ne peut pas être modifié</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Date de commande</label>
                            <input type="date" class="form-control" value="{{ $purchase->order_date->format('Y-m-d') }}" readonly>
                            <small class="text-muted">La date de commande ne peut pas être modifiée</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expected_date" class="form-label">Date de livraison prévue</label>
                            <input type="date" class="form-control @error('expected_date') is-invalid @enderror" 
                                   id="expected_date" name="expected_date" 
                                   value="{{ old('expected_date', $purchase->expected_date ? $purchase->expected_date->format('Y-m-d') : '') }}">
                            @error('expected_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status', $purchase->status) == 'pending' ? 'selected' : '' }}>
                                    En attente
                                </option>
                                <option value="cancelled" {{ old('status', $purchase->status) == 'cancelled' ? 'selected' : '' }}>
                                    Annulé
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4" 
                                  placeholder="Notes sur la commande...">{{ old('notes', $purchase->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Produits commandés</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-center">Qté commandée</th>
                                    <th class="text-center">Qté reçue</th>
                                    <th class="text-end">Prix unitaire</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->product->dosage)
                                                <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity_ordered }}</td>
                                        <td class="text-center">
                                            <span class="{{ $item->isFullyReceived() ? 'text-success' : ($item->isPartiallyReceived() ? 'text-warning' : '') }}">
                                                {{ $item->quantity_received }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }} €</td>
                                        <td class="text-end">{{ number_format($item->total_price, 2) }} €</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Sous-total:</th>
                                    <th class="text-end">{{ number_format($purchase->subtotal, 2) }} €</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">TVA (20%):</th>
                                    <th class="text-end">{{ number_format($purchase->tax_amount, 2) }} €</th>
                                </tr>
                                <tr class="table-primary">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-end">{{ number_format($purchase->total_amount, 2) }} €</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Seules les informations générales peuvent être modifiées. 
                Les produits commandés ne peuvent pas être modifiés après la création de la commande.
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations fournisseur</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Nom:</strong> {{ $purchase->supplier->name }}
                    </div>
                    @if($purchase->supplier->contact_person)
                        <div class="mb-2">
                            <strong>Contact:</strong> {{ $purchase->supplier->contact_person }}
                        </div>
                    @endif
                    @if($purchase->supplier->phone_number)
                        <div class="mb-2">
                            <strong>Téléphone:</strong> {{ $purchase->supplier->phone_number }}
                        </div>
                    @endif
                    @if($purchase->supplier->email)
                        <div class="mb-2">
                            <strong>Email:</strong> {{ $purchase->supplier->email }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Résumé</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Sous-total:</span>
                            <span>{{ number_format($purchase->subtotal, 2) }} €</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>TVA:</span>
                            <span>{{ number_format($purchase->tax_amount, 2) }} €</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-primary text-white">
                            <strong>Total:</strong>
                            <strong>{{ number_format($purchase->total_amount, 2) }} €</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection