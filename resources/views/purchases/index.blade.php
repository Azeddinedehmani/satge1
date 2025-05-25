@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Gestion des achats</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle commande
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
                        <h6 class="card-title">Total achats</h6>
                        <h4 class="mb-0">{{ number_format($totalPurchases, 2) }} €</h4>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">En attente</h6>
                        <h4 class="mb-0">{{ $pendingCount }}</h4>
                    </div>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">En retard</h6>
                        <h4 class="mb-0">{{ $overdueCount }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Reçues</h6>
                        <h4 class="mb-0">{{ $receivedCount }}</h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
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
        <form action="{{ route('purchases.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="N° commande, fournisseur..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partiellement reçu</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Reçu</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="supplier" class="form-label">Fournisseur</label>
                <select class="form-select" id="supplier" name="supplier">
                    <option value="">Tous</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
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
        <h5 class="card-title mb-0">Liste des commandes ({{ $purchases->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Fournisseur</th>
                        <th>Date commande</th>
                        <th>Date prévue</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Progression</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr class="{{ $purchase->status === 'cancelled' ? 'table-secondary' : '' }}">
                            <td>
                                <strong>{{ $purchase->purchase_number }}</strong>
                                @if($purchase->expected_date && $purchase->expected_date->isPast() && $purchase->status === 'pending')
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        En retard ({{ $purchase->expected_date->diffForHumans() }})
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $purchase->supplier->name }}</strong>
                                @if($purchase->supplier->contact_person)
                                    <br><small class="text-muted">{{ $purchase->supplier->contact_person }}</small>
                                @endif
                            </td>
                            <td>{{ $purchase->order_date->format('d/m/Y') }}</td>
                            <td>
                                @if($purchase->expected_date)
                                    {{ $purchase->expected_date->format('d/m/Y') }}
                                    @if($purchase->expected_date->isPast() && $purchase->status === 'pending')
                                        <br><small class="text-danger">En retard</small>
                                    @endif
                                @else
                                    <span class="text-muted">Non définie</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($purchase->total_amount, 2) }} €</strong>
                                <br><small class="text-muted">HT: {{ number_format($purchase->subtotal, 2) }} €</small>
                            </td>
                            <td>
                                <span class="badge {{ $purchase->status_badge }}">
                                    {{ $purchase->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($purchase->status !== 'cancelled')
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $purchase->status === 'received' ? 'bg-success' : 'bg-info' }}" 
                                             role="progressbar" 
                                             style="width: {{ $purchase->progress_percentage }}%"
                                             aria-valuenow="{{ $purchase->progress_percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $purchase->progress_percentage }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $purchase->received_items }}/{{ $purchase->total_items }}</small>
                                @else
                                    <span class="text-muted">Annulé</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('purchases.print', $purchase->id) }}" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if($purchase->status !== 'received' && $purchase->status !== 'cancelled')
                                        <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('purchases.receive', $purchase->id) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-truck"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted mb-0">Aucune commande trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($purchases->hasPages())
        <div class="card-footer">
            {{ $purchases->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection