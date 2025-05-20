@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert bg-accent text-white">
            <h4 class="alert-heading"><i class="fas fa-user-circle me-2"></i>Bienvenue, {{ Auth::user()->name }}!</h4>
            <p>Vous êtes connecté en tant que Responsable. Vous avez un accès complet à toutes les fonctionnalités du système.</p>
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
        <div class="card bg-warning text-dark">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Produits en rupture</h6>
                    <h3 class="mb-0">7</h3>
                </div>
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Expiration proche</h6>
                    <h3 class="mb-0">12</h3>
                </div>
                <i class="fas fa-clock fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Ventes mensuelles</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                    <p class="text-muted">Graphique des ventes mensuelles ici</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
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
    <div class="col-md-6">
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
                                <th>Montant</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Martin Dupont</td>
                                <td>Doliprane, Vitamines</td>
                                <td>18.50 €</td>
                                <td>10:25 AM</td>
                            </tr>
                            <tr>
                                <td>Sophie Laurent</td>
                                <td>Antibiotique, Sirop</td>
                                <td>42.75 €</td>
                                <td>09:40 AM</td>
                            </tr>
                            <tr>
                                <td>Jean Petit</td>
                                <td>Crème hydratante</td>
                                <td>12.90 €</td>
                                <td>09:15 AM</td>
                            </tr>
                            <tr>
                                <td>Marie Leclerc</td>
                                <td>Paracétamol, Ibuprofène</td>
                                <td>8.25 €</td>
                                <td>Hier</td>
                            </tr>
                            <tr>
                                <td>Lucas Moreau</td>
                                <td>Pansements, Antiseptique</td>
                                <td>15.30 €</td>
                                <td>Hier</td>
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
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Produits à commander</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Fournisseur</th>
                                <th>Stock actuel</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Paracétamol 500mg</td>
                                <td>Pharma Distrib</td>
                                <td>5 boîtes</td>
                                <td><button class="btn btn-sm btn-primary">Commander</button></td>
                            </tr>
                            <tr>
                                <td>Doliprane sirop enfant</td>
                                <td>MediStock</td>
                                <td>3 flacons</td>
                                <td><button class="btn btn-sm btn-primary">Commander</button></td>
                            </tr>
                            <tr>
                                <td>Ibuprofène 400mg</td>
                                <td>Pharma Distrib</td>
                                <td>12 boîtes</td>
                                <td><button class="btn btn-sm btn-primary">Commander</button></td>
                            </tr>
                            <tr>
                                <td>Ventoline</td>
                                <td>MediStock</td>
                                <td>8 inhalateurs</td>
                                <td><button class="btn btn-sm btn-primary">Commander</button></td>
                            </tr>
                            <tr>
                                <td>Amoxicilline 1g</td>
                                <td>BioPharm</td>
                                <td>15 boîtes</td>
                                <td><button class="btn btn-sm btn-primary">Commander</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="#" class="btn btn-sm btn-primary">Voir tous les produits</a>
            </div>
        </div>
    </div>
</div>
@endsection