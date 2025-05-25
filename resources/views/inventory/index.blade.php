@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Gestion des produits</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Ajouter un produit
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtres et recherche</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Nom, code barre..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="category" class="form-label">Catégorie</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="supplier" class="form-label">Fournisseur</label>
                <select class="form-select" id="supplier" name="supplier">
                    <option value="">Tous les fournisseurs</option>
                    @foreach(\App\Models\Supplier::where('active', true)->get() as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="stock_status" class="form-label">État du stock</label>
                <select class="form-select" id="stock_status" name="stock_status">
                    <option value="">Tous les produits</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stock faible</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Rupture de stock</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Liste des produits</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix d'achat</th>
                        <th>Prix de vente</th>
                        <th>Stock</th>
                        <th>Date d'expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary text-white rounded me-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-pills"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->dosage)
                                            <br><small>{{ $product->dosage }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category ? $product->category->name : 'N/A' }}</td>
                            <td>{{ number_format($product->purchase_price, 2) }} €</td>
                            <td>{{ number_format($product->selling_price, 2) }} €</td>
                            <td>
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger">Rupture</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark">Faible ({{ $product->stock_quantity }})</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->expiry_date)
                                    @if($product->isAboutToExpire())
                                        <span class="text-danger">{{ $product->expiry_date->format('d/m/Y') }}</span>
                                    @else
                                        {{ $product->expiry_date->format('d/m/Y') }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('inventory.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Modal de confirmation de suppression -->
                                <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $product->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $product->id }}">Confirmer la suppression</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Êtes-vous sûr de vouloir supprimer le produit <strong>{{ $product->name }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <form action="{{ route('inventory.destroy', $product->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Aucun produit trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection