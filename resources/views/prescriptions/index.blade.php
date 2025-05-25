@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Gestion des ordonnances</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('prescriptions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle ordonnance
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
                        <h6 class="card-title">Total ordonnances</h6>
                        <h4 class="mb-0">{{ $totalPrescriptions }}</h4>
                    </div>
                    <i class="fas fa-file-prescription fa-2x"></i>
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
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Expire bientôt</h6>
                        <h4 class="mb-0">{{ $expiringCount }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Expirées</h6>
                        <h4 class="mb-0">{{ $expiredCount }}</h4>
                    </div>
                    <i class="fas fa-calendar-times fa-2x"></i>
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
        <form action="{{ route('prescriptions.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="N° ordonnance, client, médecin..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="partially_delivered" {{ request('status') == 'partially_delivered' ? 'selected' : '' }}>Partiellement</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complètement</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirée</option>
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
                <label for="expiry_filter" class="form-label">Expiration</label>
                <select class="form-select" id="expiry_filter" name="expiry_filter">
                    <option value="">Toutes</option>
                    <option value="expiring_soon" {{ request('expiry_filter') == 'expiring_soon' ? 'selected' : '' }}>Expire dans 7j</option>
                    <option value="expired" {{ request('expiry_filter') == 'expired' ? 'selected' : '' }}>Expirées</option>
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
        <h5 class="card-title mb-0">Liste des ordonnances ({{ $prescriptions->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>N° Ordonnance</th>
                        <th>Client</th>
                        <th>Médecin</th>
                        <th>Date prescription</th>
                        <th>Date expiration</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $prescription)
                        <tr class="{{ $prescription->isExpired() ? 'table-danger' : ($prescription->isAboutToExpire() ? 'table-warning' : '') }}">
                            <td>
                                <strong>{{ $prescription->prescription_number }}</strong>
                                @if($prescription->isAboutToExpire() && !$prescription->isExpired())
                                    <br><small class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Expire dans {{ $prescription->expiry_date->diffInDays(now()) }} jour(s)
                                    </small>
                                @endif
                            </td>
                            <td>
                                {{ $prescription->client->full_name }}
                                @if($prescription->client->allergies)
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Allergies
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $prescription->doctor_name }}</strong>
                                @if($prescription->doctor_speciality)
                                    <br><small class="text-muted">{{ $prescription->doctor_speciality }}</small>
                                @endif
                                @if($prescription->doctor_phone)
                                    <br><small><i class="fas fa-phone me-1"></i>{{ $prescription->doctor_phone }}</small>
                                @endif
                            </td>
                            <td>{{ $prescription->prescription_date->format('d/m/Y') }}</td>
                            <td>
                                {{ $prescription->expiry_date->format('d/m/Y') }}
                                @if($prescription->isExpired())
                                    <br><small class="text-danger">Expirée</small>
                                @elseif($prescription->isAboutToExpire())
                                    <br><small class="text-warning">Expire bientôt</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $prescription->status_badge }}">
                                    {{ $prescription->status_label }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if(!in_array($prescription->status, ['completed', 'expired']))
                                        <a href="{{ route('prescriptions.edit', $prescription->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($prescription->status !== 'completed' && !$prescription->isExpired())
                                            <a href="{{ route('prescriptions.deliver', $prescription->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-pills"></i>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Aucune ordonnance trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($prescriptions->hasPages())
        <div class="card-footer">
            {{ $prescriptions->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection