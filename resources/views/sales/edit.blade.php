@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Modifier la vente {{ $sale->sale_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la vente
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Produits vendus</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">Prix unitaire</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleItems as $item)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->product->dosage)
                                                <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }} €</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($item->total_price, 2) }} €</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="table-primary">
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end">{{ number_format($sale->total_amount, 2) }} €</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Les produits vendus ne peuvent pas être modifiés après la création de la vente. 
                    Seul le statut de paiement et les notes peuvent être mis à jour.
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <form action="{{ route('sales.update', $sale->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations de la vente</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Numéro:</strong>
                            <span>{{ $sale->sale_number }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Date:</strong>
                            <span>{{ $sale->sale_date->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Vendeur:</strong>
                            <span>{{ $sale->user->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Client:</strong>
                            <span>
                                @if($sale->client)
                                    {{ $sale->client->full_name }}
                                @else
                                    Client anonyme
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Paiement:</strong>
                            <span>{{ ucfirst($sale->payment_method) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Modifier le statut</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Statut du paiement <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_status') is-invalid @enderror" 
                                id="payment_status" name="payment_status" required>
                            <option value="paid" {{ old('payment_status', $sale->payment_status) == 'paid' ? 'selected' : '' }}>
                                Payé
                            </option>
                            <option value="pending" {{ old('payment_status', $sale->payment_status) == 'pending' ? 'selected' : '' }}>
                                En attente
                            </option>
                            <option value="failed" {{ old('payment_status', $sale->payment_status) == 'failed' ? 'selected' : '' }}>
                                Échoué
                            </option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4" 
                                  placeholder="Ajoutez des notes sur cette vente...">{{ old('notes', $sale->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection