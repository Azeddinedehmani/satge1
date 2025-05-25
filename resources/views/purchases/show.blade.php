@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Commande {{ $purchase->purchase_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour aux achats
        </a>
        <a href="{{ route('purchases.print', $purchase->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print me-1"></i> Imprimer
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

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Détails de la commande</h5>
                <span class="badge {{ $purchase->status_badge }}">
                    {{ $purchase->status_label }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Numéro de commande:</strong>
                                <span>{{ $purchase->purchase_number }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date de commande:</strong>
                                <span>{{ $purchase->order_date->format('d/m/Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date prévue:</strong>
                                <span>
                                    @if($purchase->expected_date)
                                        {{ $purchase->expected_date->format('d/m/Y') }}
                                        @if($purchase->expected_date->isPast() && $purchase->status === 'pending')
                                            <span class="text-danger">(En retard)</span>
                                        @endif
                                    @else
                                        Non définie
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Créée par:</strong>
                                <span>{{ $purchase->user->name }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Fournisseur:</strong>
                                <span>
                                    <a href="{{ route('suppliers.show', $purchase->supplier->id) }}">
                                        {{ $purchase->supplier->name }}
                                    </a>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Contact:</strong>
                                <span>{{ $purchase->supplier->contact_person ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Téléphone:</strong>
                                <span>{{ $purchase->supplier->phone_number ?? 'N/A' }}</span>
                            </li>
                            @if($purchase->received_at)
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Reçue le:</strong>
                                    <span>{{ $purchase->received_date->format('d/m/Y') }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if($purchase->notes)
                    <div class="alert alert-info">
                        <strong><i class="fas fa-sticky-note me-1"></i>Notes:</strong>
                        <p class="mb-0 mt-2">{{ $purchase->notes }}</p>
                    </div>
                @endif
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
                                <th>Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->product->dosage)
                                                <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                            @endif
                                            <br><small class="text-info">Stock actuel: {{ $item->product->stock_quantity }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity_ordered }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->isFullyReceived() ? 'text-success' : ($item->isPartiallyReceived() ? 'text-warning' : '') }}">
                                            {{ $item->quantity_received }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }} €</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($item->total_price, 2) }} €</strong>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $item->isFullyReceived() ? 'bg-success' : 'bg-info' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $item->progress_percentage }}%"
                                                 aria-valuenow="{{ $item->progress_percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $item->progress_percentage }}%
                                            </div>
                                        </div>
                                        @if($item->remaining_quantity > 0)
                                            <small class="text-muted">Reste: {{ $item->remaining_quantity }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Sous-total:</th>
                                <th class="text-end">{{ number_format($purchase->subtotal, 2) }} €</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">TVA (20%):</th>
                                <th class="text-end">{{ number_format($purchase->tax_amount, 2) }} €</th>
                                <th></th>
                            </tr>
                            <tr class="table-primary">
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-end">{{ number_format($purchase->total_amount, 2) }} €</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Résumé financier</h5>
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

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Progression</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Réception:</span>
                        <span>{{ $purchase->received_items }}/{{ $purchase->total_items }}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar {{ $purchase->status === 'received' ? 'bg-success' : 'bg-info' }}" 
                             role="progressbar" 
                             style="width: {{ $purchase->progress_percentage }}%">
                            {{ $purchase->progress_percentage }}%
                        </div>
                    </div>
                </div>
                
                @if($purchase->status !== 'received' && $purchase->status !== 'cancelled')
                    <small class="text-muted">
                        Encore {{ $purchase->total_items - $purchase->received_items }} produit(s) à recevoir
                    </small>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('purchases.print', $purchase->id) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-1"></i> Imprimer la commande
                    </a>
                    
                    @if($purchase->status !== 'received' && $purchase->status !== 'cancelled')
                        <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                        
                        <a href="{{ route('purchases.receive', $purchase->id) }}" class="btn btn-success">
                            <i class="fas fa-truck me-1"></i> Recevoir la livraison
                        </a>
                        
                        <form action="{{ route('purchases.cancel', $purchase->id) }}" method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-times me-1"></i> Annuler la commande
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('suppliers.show', $purchase->supplier->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-truck me-1"></i> Voir le fournisseur
                    </a>
                    
                    <a href="{{ route('purchases.create', ['supplier_id' => $purchase->supplier->id]) }}" class="btn btn-outline-success">
                        <i class="fas fa-plus me-1"></i> Nouvelle commande
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Informations fournisseur</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ $purchase->supplier->name }}</strong>
                    </li>
                    @if($purchase->supplier->contact_person)
                        <li class="list-group-item">
                            <i class="fas fa-user me-2"></i>{{ $purchase->supplier->contact_person }}
                        </li>
                    @endif
                    @if($purchase->supplier->phone_number)
                        <li class="list-group-item">
                            <i class="fas fa-phone me-2"></i>{{ $purchase->supplier->phone_number }}
                        </li>
                    @endif
                    @if($purchase->supplier->email)
                        <li class="list-group-item">
                            <i class="fas fa-envelope me-2"></i>{{ $purchase->supplier->email }}
                        </li>
                    @endif
                </ul>
                
                @if($purchase->supplier->phone_number || $purchase->supplier->email)
                    <div class="mt-3">
                        @if($purchase->supplier->phone_number)
                            <a href="tel:{{ $purchase->supplier->phone_number }}" class="btn btn-sm btn-outline-success me-2">
                                <i class="fas fa-phone"></i> Appeler
                            </a>
                        @endif
                        @if($purchase->supplier->email)
                            <a href="mailto:{{ $purchase->supplier->email }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection