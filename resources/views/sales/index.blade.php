@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Gestion des ventes</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle vente
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total des ventes</h6>
                        <h4 class="mb-0">{{ number_format($totalSales, 2) }} €</h4>
                    </div>
                    <i class="fas fa-euro-sign fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Nombre de ventes</h6>
                        <h4 class="mb-0">{{ $salesCount }}</h4>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Vente moyenne</h6>
                        <h4 class="mb-0">{{ number_format($averageSale, 2) }} €</h4>
                    </div>
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Aujourd'hui</h6>
                        <h4 class="mb-0">{{ $sales->where('sale_date', '>=', today())->count() }}</h4>
                    </div>
                    <i class="fas fa-calendar-day fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres et recherche</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('sales.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="N° vente, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="payment_status" class="form-label">Statut paiement</label>
                <select class="form-select" id="payment_status" name="payment_status">
                    <option value="">Tous</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Payé</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date début</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date fin</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label for="has_prescription" class="form-label">Ordonnance</label>
                <select class="form-select" id="has_prescription" name="has_prescription">
                    <option value="">Toutes</option>
                    <option value="yes" {{ request('has_prescription') == 'yes' ? 'selected' : '' }}>Avec</option>
                    <option value="no" {{ request('has_prescription') == 'no' ? 'selected' : '' }}>Sans</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Liste des ventes ({{ $sales->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>N° Vente</th>
                        <th>Client</th>
                        <th>Vendeur</th>
                        <th>Produits</th>
                        <th>Montant</th>
                        <th>Paiement</th>
                        <th>Ordonnance</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>
                                <strong>{{ $sale->sale_number }}</strong>
                            </td>
                            <td>
                                @if($sale->client)
                                    {{ $sale->client->full_name }}
                                @else
                                    <span class="text-muted">Client anonyme</span>
                                @endif
                            </td>
                            <td>
                                {{ $sale->user->name }}
                            </td>
                            <td>
                                <small>
                                    @foreach($sale->saleItems->take(2) as $item)
                                        {{ $item->product->name }}
                                        @if($item->quantity > 1) ({{ $item->quantity }}) @endif
                                        @if(!$loop->last), @endif
                                    @endforeach
                                    @if($sale->saleItems->count() > 2)
                                        <br><span class="text-muted">+{{ $sale->saleItems->count() - 2 }} autres</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                <strong>{{ number_format($sale->total_amount, 2) }} €</strong>
                            </td>
                            <td>
                                <span class="badge {{ $sale->payment_status_badge }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                                <br><small class="text-muted">{{ ucfirst($sale->payment_method) }}</small>
                            </td>
                            <td>
                                @if($sale->has_prescription)
                                    <span class="badge bg-success">Oui</span>
                                    @if($sale->prescription_number)
                                        <br><small>{{ $sale->prescription_number }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td>
                                {{ $sale->sale_date->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if($sale->payment_status !== 'paid')
                                        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="text-muted mb-0">Aucune vente trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
        <div class="card-footer">
            {{ $sales->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection