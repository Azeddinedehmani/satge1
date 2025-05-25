<div class="sidebar p-3">
    <div class="d-flex justify-content-center mb-4">
        <h3 class="my-3 text-center fw-bold">PHARMACIA</h3>
    </div>
    
    <div class="my-4 border-top border-bottom py-3">
        <div class="d-flex align-items-center mb-3">
            <div class="border rounded-circle p-2 me-3">
                <i class="fas fa-user fa-lg"></i>
            </div>
            <div>
                <div class="fw-bold">{{ Auth::user()->name }}</div>
                <small>{{ Auth::user()->isAdmin() ? 'Responsable' : 'Pharmacien' }}</small>
            </div>
        </div>
    </div>
    
    <nav class="mt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('*/dashboard') ? 'active' : '' }}" href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('pharmacist.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('inventory*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                    <i class="fas fa-pills"></i> Inventaire
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('sales*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                    <i class="fas fa-cash-register"></i> Ventes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('clients*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                    <i class="fas fa-users"></i> Clients
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('prescriptions*') ? 'active' : '' }}" href="{{ route('prescriptions.index') }}">
                    <i class="fas fa-file-prescription"></i> Ordonnances
                    @php
                        $pendingCount = \App\Models\Prescription::pending()->count();
                        $expiringCount = \App\Models\Prescription::active()->where('expiry_date', '<=', now()->addDays(7))->count();
                        $totalAlerts = $pendingCount + $expiringCount;
                    @endphp
                    @if($totalAlerts > 0)
                        <span class="badge bg-danger rounded-pill ms-2">{{ $totalAlerts }}</span>
                    @endif
                </a>
            </li>
            
            <!-- MISE À JOUR : Lien vers les fournisseurs pour tous les utilisateurs -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <i class="fas fa-truck"></i> Fournisseurs
                    @php
                        $inactiveSuppliers = \App\Models\Supplier::where('active', false)->count();
                    @endphp
                    @if($inactiveSuppliers > 0)
                        <span class="badge bg-warning text-dark rounded-pill ms-2">{{ $inactiveSuppliers }}</span>
                    @endif
                </a>
            </li>
            
            @if(Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-shopping-cart"></i> Achats
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-chart-line"></i> Rapports
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cog"></i> Administration
                </a>
            </li>
            @endif
        </ul>
    </nav>
    
    <div class="mt-auto pt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light w-100">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </button>
        </form>
    </div>
</div>