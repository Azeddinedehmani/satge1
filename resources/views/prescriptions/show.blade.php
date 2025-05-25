@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Ordonnance {{ $prescription->prescription_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
        <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-primary" target="_blank">
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

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Détails de l'ordonnance</h5>
                <span class="badge {{ $prescription->status_badge }}">
                    {{ $prescription->status_label }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Numéro:</strong>
                                <span>{{ $prescription->prescription_number }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date de prescription:</strong>
                                <span>{{ $prescription->prescription_date->format('d/m/Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Date d'expiration:</strong>
                                <span class="{{ $prescription->isExpired() ? 'text-danger' : ($prescription->isAboutToExpire() ? 'text-warning' : '') }}">
                                    {{ $prescription->expiry_date->format('d/m/Y') }}
                                    @if($prescription->isExpired())
                                        (Expirée)
                                    @elseif($prescription->isAboutToExpire())
                                        (Expire dans {{ $prescription->expiry_date->diffInDays(now()) }} jour(s))
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Créée par:</strong>
                                <span>{{ $prescription->createdBy->name }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Client:</strong>
                                <span>
                                    <a href="{{ route('clients.show', $prescription->client->id) }}">
                                        {{ $prescription->client->full_name }}
                                    </a>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Médecin:</strong>
                                <span>{{ $prescription->doctor_name }}</span>
                            </li>
                            @if($prescription->doctor_speciality)
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Spécialité:</strong>
                                    <span>{{ $prescription->doctor_speciality }}</span>
                                </li>
                            @endif
                            @if($prescription->doctor_phone)
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Téléphone:</strong>
                                    <span>{{ $prescription->doctor_phone }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if($prescription->medical_notes)
                    <div class="alert alert-info">
                        <strong><i class="fas fa-notes-medical me-1"></i>Notes médicales:</strong>
                        <p class="mb-0 mt-2">{{ $prescription->medical_notes }}</p>
                    </div>
                @endif

                @if($prescription->pharmacist_notes)
                    <div class="alert alert-secondary">
                        <strong><i class="fas fa-user-md me-1"></i>Notes du pharmacien:</strong>
                        <p class="mb-0 mt-2">{{ $prescription->pharmacist_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($prescription->client->allergies)
            <div class="alert alert-danger">
                <strong><i class="fas fa-exclamation-triangle me-1"></i>Allergies connues du client:</strong>
                <p class="mb-0 mt-2">{{ $prescription->client->allergies }}</p>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Médicaments prescrits</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Médicament</th>
                                <th class="text-center">Quantité prescrite</th>
                                <th class="text-center">Quantité délivrée</th>
                                <th>Posologie</th>
                                <th>Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescription->prescriptionItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->product->dosage)
                                            <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                        @endif
                                        @if($item->duration_days)
                                            <br><small class="text-info">Durée: {{ $item->duration_days }} jour(s)</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity_prescribed }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->isFullyDelivered() ? 'text-success' : ($item->isPartiallyDelivered() ? 'text-warning' : '') }}">
                                            {{ $item->quantity_delivered }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $item->dosage_instructions }}</strong>
                                        @if($item->instructions)
                                            <br><small class="text-muted">{{ $item->instructions }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $item->isFullyDelivered() ? 'bg-success' : 'bg-warning' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $item->delivery_progress }}%"
                                                 aria-valuenow="{{ $item->delivery_progress }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $item->delivery_progress }}%
                                            </div>
                                        </div>
                                        @if($item->remaining_quantity > 0)
                                            <small class="text-muted">Reste: {{ $item->remaining_quantity }}</small>
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
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('prescriptions.print', $prescription->id) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-1"></i> Imprimer l'ordonnance
                    </a>
                    
                    @if(!in_array($prescription->status, ['completed', 'expired']))
                        <a href="{{ route('prescriptions.edit', $prescription->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                    @endif
                    
                    @if($prescription->status !== 'completed' && !$prescription->isExpired())
                        <a href="{{ route('prescriptions.deliver', $prescription->id) }}" class="btn btn-success">
                            <i class="fas fa-pills me-1"></i> Délivrer médicaments
                        </a>
                    @endif
                    
                    <a href="{{ route('clients.show', $prescription->client->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user me-1"></i> Voir le client
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection