@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert bg-accent text-white">
            <h4 class="alert-heading"><i class="fas fa-user-circle me-2"></i>Bienvenue, {{ Auth::user()->name }}!</h4>
            <p>Vous êtes connecté en tant que Pharmacien. Vous avez accès aux fonctionnalités de vente et de gestion des clients.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Ventes du jour</h6>
                    <h3 class="mb-0">1,250 €</h3>
                </div>
                <i class="fas fa-shopping-cart fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Clients aujourd'hui</h6>
                    <h3 class="mb-0">24</h3>
                </div>
                <i class="fas fa-users fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Ordonnances</h6>
                    <h3 class="mb-0">18</h3>
                </div>
                <i class="fas fa-file-prescription fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Alertes Stock</h6>
                    <h3 class="mb-0">7</h3>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Accès rapide</h5>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-primary w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-cash-register fa-2x mb-2"></i>
                            <span>Nouvelle vente</span>
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-secondary w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-file-prescription fa-2x mb-2"></i>
                            <span>Nouvelle ordonnance</span>
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-info text-white w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span>Nouveau client</span>
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-warning w-100 h-100 py-4 d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <span>Recherche produit</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Alertes Stock</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Paracétamol 500mg</h6>
                            <small class="text-danger">Stock: 5 boîtes</small>
                        </div>
                        <span class="badge bg-danger rounded-pill">Critique</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Ibuprofène 400mg</h6>
                            <small class="text-warning">Stock: 12 boîtes</small>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill">Faible</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Amoxicilline 1g</h6>
                            <small class="text-warning">Stock: 15 boîtes</small>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill">Faible</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Doliprane sirop enfant</h6>
                            <small class="text-danger">Stock: 3 flacons</small>
                        </div>
                        <span class="badge bg-danger rounded-pill">Critique</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Ventoline</h6>
                            <small class="text-warning">Stock: 8 inhalateurs</small>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill">Faible</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="#" class="btn btn-sm btn-primary">Voir toutes les alertes</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Ventes récentes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Produits</th>
                                <th>Ordonnance</th>
                                <th>Montant</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Martin Dupont</td>
                                <td>Doliprane, Vitamines</td>
                                <td><span class="badge bg-secondary">Non</span></td>
                                <td>18.50 €</td>
                                <td>10:25 AM</td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Sophie Laurent</td>
                                <td>Antibiotique, Sirop</td>
                                <td><span class="badge bg-success">Oui</span></td>
                                <td>42.75 €</td>
                                <td>09:40 AM</td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Jean Petit</td>
                                <td>Crème hydratante</td>
                                <td><span class="badge bg-secondary">Non</span></td>
                                <td>12.90 €</td>
                                <td>09:15 AM</td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Marie Leclerc</td>
                                <td>Paracétamol, Ibuprofène</td>
                                <td><span class="badge bg-success">Oui</span></td>
                                <td>8.25 €</td>
                                <td>Hier</td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Lucas Moreau</td>
                                <td>Pansements, Antiseptique</td>
                                <td><span class="badge bg-secondary">Non</span></td>
                                <td>15.30 €</td>
                                <td>Hier</td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="#" class="btn btn-sm btn-primary">Voir toutes les ventes</a>
            </div>
        </div>
    </div>
</div>
@endsection