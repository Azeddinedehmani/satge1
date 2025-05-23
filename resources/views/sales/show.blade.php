@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Vente {{ $sale->sale_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('sales.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour aux ventes
        </a>
        <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print me-1"></i> Imprimer
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Détails de la vente</h5>
                <span class="badge {{ $sale->payment_status_badge }}">
                    {{ ucfirst($sale->payment_status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Numéro de vente:</strong>
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
                                        <a href="{{ route('clients.show', $sale->client->id) }}">
                                            {{ $sale->client->full_name }}
                                        </a>
                                    @else
                                        Client anonyme
                                    @endif
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Mode de paiement:</strong>
                                <span>{{ ucfirst($sale->payment_method) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Statut paiement:</strong>
                                <span class="badge {{ $sale->payment_status_badge }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Ordonnance:</strong>
                                <span>
                                    @if($sale->has_prescription)
                                        <span class="badge bg-success">Oui</span>
                                        @if($sale->prescription_number)
                                            <br><small>{{ $sale->prescription_number }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Non</span>
                                    @endif
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                @if($sale->notes)
                    <div class="alert alert-info">
                        <strong>Notes:</strong> {{ $sale->notes }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Produits vendus</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
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
                                            @if($item->product->prescription_required)
                                                <br><small class="text-warning">
                                                    <i class="fas fa-prescription-bottle me-1"></i>
                                                    Ordonnance requise
                                                </small>
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
                            <tr>
                                <th colspan="3" class="text-end">Sous-total:</th>
                                <th class="text-end">{{ number_format($sale->subtotal, 2) }} €</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">TVA (20%):</th>
                                <th class="text-end">{{ number_format($sale->tax_amount, 2) }} €</th>
                            </tr>
                            @if($sale->discount_amount > 0)
                                <tr>
                                    <th colspan="3" class="text-end">Remise:</th>
                                    <th class="text-end text-danger">-{{ number_format($sale->discount_amount, 2) }} €</th>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end">{{ number_format($sale->total_amount, 2) }} €</th>
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
                        <span>{{ number_format($sale->subtotal, 2) }} €</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>TVA:</span>
                        <span>{{ number_format($sale->tax_amount, 2) }} €</span>
                    </li>
                    @if($sale->discount_amount > 0)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Remise:</span>
                            <span class="text-danger">-{{ number_format($sale->discount_amount, 2) }} €</span>
                        </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between bg-primary text-white">
                        <strong>Total:</strong>
                        <strong>{{ number_format($sale->total_amount, 2) }} €</strong>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-1"></i> Imprimer le reçu
                    </a>
                    
                    @if($sale->payment_status !== 'paid')
                        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Modifier le statut
                        </a>
                    @endif
                    
                    @if($sale->client)
                        <a href="{{ route('clients.show', $sale->client->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-user me-1"></i> Voir le client
                        </a>
                    @endif
                    
                    <a href="{{ route('sales.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Nouvelle vente
                    </a>
                </div>
            </div>
        </div>

        @if($sale->client)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations client</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>{{ $sale->client->full_name }}</strong>
                        </li>
                        @if($sale->client->phone)
                            <li class="list-group-item">
                                <i class="fas fa-phone me-2"></i>{{ $sale->client->phone }}
                            </li>
                        @endif
                        @if($sale->client->email)
                            <li class="list-group-item">
                                <i class="fas fa-envelope me-2"></i>{{ $sale->client->email }}
                            </li>
                        @endif
                        <li class="list-group-item">
                            Total dépensé: <strong>{{ number_format($sale->client->total_spent, 2) }} €</strong>
                        </li>
                    </ul>
                    
                    @if($sale->client->allergies)
                        <div class="alert alert-warning mt-3 mb-0">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i>Allergies:</strong>
                            <br>{{ $sale->client->allergies }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection