@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2>Modifier le produit</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Informations du produit</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nom du produit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Le reste du formulaire est similaire à celui de création mais avec les valeurs actuelles -->
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>
@endsection