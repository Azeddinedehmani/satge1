@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-cogs me-2"></i>Administration du système</h2>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                <i class="fas fa-users me-1"></i> Gestion utilisateurs
            </a>
            <a href="{{ route('admin.settings') }}" class="btn btn-outline-primary">
                <i class="fas fa-sliders-h me-1"></i> Paramètres
            </a>
        </div>
    </div>
</div>

<!-- Informations système -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-server me-2"></i>Informations système
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Version PHP:</strong>
                        <span class="badge bg-info">{{ $systemInfo['php_version'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Version Laravel:</strong>
                        <span class="badge bg-success">{{ $systemInfo['laravel_version'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Serveur web:</strong>
                        <span>{{ $systemInfo['server_software'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Base de données:</strong>
                        <span>{{ Str::limit($systemInfo['database_version'], 30) }}</span>
                    </li>
                    <li class="list-group-item">
                        <strong>Utilisation disque:</strong>
                        <div class="progress mt-2">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $systemInfo['disk_usage']['used_percent'] }}%">
                                {{ $systemInfo['disk_usage']['used_percent'] }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            Libre: {{ $systemInfo['disk_usage']['free'] }} / {{ $systemInfo['disk_usage']['total'] }}
                        </small>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Mémoire utilisée:</strong>
                        <div>
                            <div>Actuelle: {{ $systemInfo['memory_usage']['current'] }}</div>
                            <div>Pic: {{ $systemInfo['memory_usage']['peak'] }}</div>
                            <small class="text-muted">Limite: {{ $systemInfo['memory_usage']['limit'] }}</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Statistiques d'activité
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-primary mb-0">{{ number_format($activityStats['total_activities']) }}</h3>
                            <small class="text-muted">Total activités</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-success mb-0">{{ $activityStats['activities_today'] }}</h3>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <strong>Cette semaine:</strong>
                        <span class="badge bg-info">{{ $activityStats['activities_week'] }}</span>
                    </li>
                    @if($activityStats['most_active_user'])
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Utilisateur le plus actif:</strong>
                            <div class="text-end">
                                <div>{{ $activityStats['most_active_user']->user->name ?? 'Inconnu' }}</div>
                                <small class="text-muted">{{ $activityStats['most_active_user']->count }} activités</small>
                            </div>
                        </li>
                    @endif
                    @if($activityStats['most_common_action'])
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Action la plus fréquente:</strong>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $activityStats['most_common_action']->action }}</span>
                                <br><small class="text-muted">{{ $activityStats['most_common_action']->count }} fois</small>
                            </div>
                        </li>
                    @endif
                </ul>
                
                <div class="d-grid mt-3">
                    <a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i> Voir tous les logs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Actions rapides
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <br>Nouvel utilisateur
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('admin.activity-logs') }}" class="btn btn-info btn-lg text-white">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <br>Logs d'activité
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="{{ route('admin.settings') }}" class="btn btn-warning btn-lg">
                                <i class="fas fa-cog fa-2x mb-2"></i>
                                <br>Paramètres système
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                                <i class="fas fa-trash fa-2x mb-2"></i>
                                <br>Nettoyer logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activités récentes du système -->
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-clock me-2"></i>Activités récentes du système
        </h5>
        <a href="{{ route('admin.activity-logs') }}" class="btn btn-sm btn-outline-primary">
            Voir tout
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date/Heure</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($systemActivities as $activity)
                        <tr>
                            <td>
                                <small>{{ $activity->created_at->format('d/m H:i') }}</small>
                                <br><small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                @if($activity->user)
                                    <div class="d-flex align-items-center">
                                        @if($activity->user->profile_photo)
                                            <img src="{{ asset('storage/'.$activity->user->profile_photo) }}" 
                                                 alt="{{ $activity->user->name }}" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 30px; height: 30px; font-size: 12px;">
                                                {{ substr($activity->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div>{{ $activity->user->name }}</div>
                                            <small class="text-muted">{{ $activity->user->role === 'responsable' ? 'Admin' : 'Pharmacien' }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Système</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $activity->action_badge }}">
                                    <i class="{{ $activity->action_icon }} me-1"></i>
                                    {{ ucfirst($activity->action) }}
                                </span>
                            </td>
                            <td>
                                {{ Str::limit($activity->description, 50) }}
                                @if($activity->model_type)
                                    <br><small class="text-muted">{{ $activity->model_name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($activity->ip_address)
                                    <code>{{ $activity->ip_address }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted mb-0">Aucune activité récente</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de nettoyage des logs -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nettoyer les logs d'activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.clear-old-logs') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action supprimera définitivement les logs d'activité anciens.
                    </div>
                    
                    <div class="mb-3">
                        <label for="days" class="form-label">Supprimer les logs de plus de:</label>
                        <select class="form-select" id="days" name="days" required>
                            <option value="30">30 jours</option>
                            <option value="60">60 jours</option>
                            <option value="90" selected>90 jours</option>
                            <option value="180">180 jours</option>
                            <option value="365">1 an</option>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmClear" required>
                        <label class="form-check-label" for="confirmClear">
                            Je comprends que cette action est irréversible
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Nettoyer les logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
