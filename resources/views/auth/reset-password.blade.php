@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="m-0 fw-bold">PHARMACIA</h3>
                    <p class="m-0">Nouveau mot de passe</p>
                </div>

                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <i class="fas fa-key fa-3x text-muted mb-3"></i>
                        <h4>Créer un nouveau mot de passe</h4>
                        <p class="text-muted">Saisissez le code reçu par email et votre nouveau mot de passe</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Code envoyé à : <strong>{{ $email }}</strong>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('password.reset') }}">
                        @csrf

                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="row mb-3">
                            <label for="code" class="col-md-4 col-form-label text-md-end">{{ __('Code de vérification') }}</label>

                            <div class="col-md-6">
                                <input id="code" type="text" class="form-control text-center @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" required maxlength="6" style="font-size: 1.2rem; letter-spacing: 2px;" placeholder="000000">

                                @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                
                                <div class="form-text">
                                    <i class="fas fa-clock me-1"></i>
                                    Le code est valide pendant 10 minutes
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Nouveau mot de passe') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirmer le mot de passe') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fas fa-check me-1"></i> {{ __('Réinitialiser le mot de passe') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ route('password.forgot') }}" class="text-decoration-none me-3">
                            <i class="fas fa-redo me-1"></i> Renvoyer le code
                        </a>
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Retour à la connexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-format du code à 6 chiffres
document.getElementById('code').addEventListener('input', function(e) {
    // Supprimer tous les caractères non numériques
    let value = e.target.value.replace(/\D/g, '');
    
    // Limiter à 6 chiffres
    if (value.length > 6) {
        value = value.substring(0, 6);
    }
    
    e.target.value = value;
});
</script>
@endsection