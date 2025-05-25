@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Délivrance - {{ $prescription->prescription_number }}</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à l'ordonnance
        </a>
    </div>
</div>

<form action="{{ route('prescriptions.process-delivery', $prescription->id) }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <!-- Informations patient -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Informations patient
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ $prescription->client->full_name }}</strong>
                            @if($prescription->client->date_of_birth)
                                <br>Âge: {{ $prescription->client->age }} ans
                            @endif
                            @if($prescription->client->phone)
                                <br><i class="fas fa-phone me-1"></i>{{ $prescription->client->phone }}
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Médecin:</strong> {{ $prescription->doctor_name }}
                            @if($prescription->doctor_speciality)
                                <br>{{ $prescription->doctor_speciality }}
                            @endif
                            <br><strong>Date:</strong> {{ $prescription->prescription_date->format('d/m/Y') }}
                        </div>
                    </div>
                    
                    @if($prescription->client->allergies)
                        <div class="alert alert-danger mt-3 mb-0">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i>ALLERGIES CONNUES:</strong>
                            {{ $prescription->client->allergies }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Médicaments à délivrer -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Médicaments à délivrer</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Médicament</th>
                                    <th class="text-center">Prescrit</th>
                                    <th class="text-center">Déjà délivré</th>
                                    <th class="text-center">Reste à délivrer</th>
                                    <th class="text-center">Quantité à délivrer</th>
                                    <th>Stock disponible</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prescription->prescriptionItems as $item)
                                    <tr class="{{ $item->product->stock_quantity < $item->remaining_quantity ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->product->dosage)
                                                <br><small class="text-muted">{{ $item->product->dosage }}</small>
                                            @endif
                                            <br><strong class="text-info">{{ $item->dosage_instructions }}</strong>
                                            @if($item->instructions)
                                                <br><small class="text-muted">{{ $item->instructions }}</small>
                                            @endif
                                            @if($item->duration_days)
                                                <br><small class="text-success">Durée: {{ $item->duration_days }} jour(s)</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity_prescribed }}</td>
                                        <td class="text-center">{{ $item->quantity_delivered }}</td>
                                        <td class="text-center">
                                            <strong>{{ $item->remaining_quantity }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if($item->remaining_quantity > 0)
                                                <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                                                <input type="number" 
                                                       class="form-control text-center quantity-input" 
                                                       name="items[{{ $loop->index }}][quantity_to_deliver]" 
                                                       value="{{ min($item->remaining_quantity, $item->product->stock_quantity) }}"
                                                       min="0" 
                                                       max="{{ min($item->remaining_quantity, $item->product->stock_quantity) }}"
                                                       data-max="{{ $item->remaining_quantity }}"
                                                       style="width: 80px;">
                                            @else
                                                <span class="text-success">Complet</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $item->product->stock_quantity < $item->remaining_quantity ? 'text-danger' : 'text-success' }}">
                                                {{ $item->product->stock_quantity }}
                                            </span>
                                            @if($item->product->stock_quantity < $item->remaining_quantity)
                                                <br><small class="text-danger">Stock insuffisant!</small>
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
                    <h5 class="card-title mb-0">Notes du pharmacien</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" name="pharmacist_notes" rows="4" 
                                  placeholder="Notes sur la délivrance, conseils au patient...">{{ old('pharmacist_notes', $prescription->pharmacist_notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success" id="deliverBtn">
                            <i class="fas fa-pills me-1"></i> Enregistrer la délivrance
                        </button>
                        <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-secondary">
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
        });
    });
    
    document.getElementById('deliverBtn').addEventListener('click', function(e) {
        const quantities = [];
        quantityInputs.forEach(input => {
            if (parseInt(input.value) > 0) {
                quantities.push(input.value);
            }
        });
        
        if (quantities.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins une quantité à délivrer.');
            return;
        }
        
        const confirm = window.confirm('Confirmer la délivrance de ces médicaments ?');
        if (!confirm) {
            e.preventDefault();
        }
    });
});
</script>
@endsection