<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <h3>{{ isset($title) ? $title : 'Tableau de bord' }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('pharmacist.dashboard') }}">Accueil</a></li>
                    @if(isset($breadcrumb))
                        @foreach($breadcrumb as $item)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                @if($loop->last)
                                    {{ $item['name'] }}
                                @else
                                    <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                </ol>
            </nav>
        </div>
        
        <div class="d-flex align-items-center">
            <div class="theme-toggle me-3">
                <i id="theme-icon" class="fas fa-moon"></i>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span class="badge bg-danger rounded-pill">3</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Stock critique pour Paracétamol</a></li>
                    <li><a class="dropdown-item" href="#">5 produits arrivent à expiration</a></li>
                    <li><a class="dropdown-item" href="#">Nouvelle commande à valider</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>