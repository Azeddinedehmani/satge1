<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class LogActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users and successful requests
        if (auth()->check() && $response->getStatusCode() < 400) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log the activity
     */
    private function logActivity(Request $request, Response $response)
    {
        $method = $request->method();
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $uri = $request->getRequestUri();

        // Skip logging for certain routes
        $skipRoutes = [
            'admin.activity-logs',
            'admin.export-activity-logs',
            'dashboard',
            'admin.dashboard',
            'pharmacist.dashboard'
        ];

        if (in_array($routeName, $skipRoutes) || str_contains($uri, '/api/')) {
            return;
        }

        // Determine action based on HTTP method and route
        $action = $this->determineAction($method, $routeName, $uri);
        $description = $this->generateDescription($action, $routeName, $request);

        // Create activity log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Determine the action based on method and route
     */
    private function determineAction(string $method, ?string $routeName, string $uri): string
    {
        if ($method === 'GET') {
            if (str_contains($uri, '/export') || str_contains($uri, '/download')) {
                return 'export';
            }
            return 'view';
        }

        if ($method === 'POST') {
            return 'create';
        }

        if (in_array($method, ['PUT', 'PATCH'])) {
            return 'update';
        }

        if ($method === 'DELETE') {
            return 'delete';
        }

        return 'action';
    }

    /**
     * Generate description for the activity
     */
    private function generateDescription(string $action, ?string $routeName, Request $request): string
    {
        $descriptions = [
            // Users
            'admin.users.index' => 'Consultation de la liste des utilisateurs',
            'admin.users.show' => 'Consultation du profil utilisateur',
            'admin.users.create' => 'Affichage du formulaire de création d\'utilisateur',
            'admin.users.store' => 'Création d\'un nouvel utilisateur',
            'admin.users.edit' => 'Affichage du formulaire de modification d\'utilisateur',
            'admin.users.update' => 'Modification d\'un utilisateur',
            'admin.users.destroy' => 'Suppression d\'un utilisateur',
            'admin.users.toggle-status' => 'Changement de statut d\'un utilisateur',
            'admin.users.reset-password' => 'Réinitialisation du mot de passe d\'un utilisateur',

            // Sales
            'sales.index' => 'Consultation de la liste des ventes',
            'sales.show' => 'Consultation d\'une vente',
            'sales.create' => 'Affichage du formulaire de nouvelle vente',
            'sales.store' => 'Création d\'une nouvelle vente',
            'sales.edit' => 'Affichage du formulaire de modification de vente',
            'sales.update' => 'Modification d\'une vente',
            'sales.destroy' => 'Suppression d\'une vente',
            'sales.print' => 'Impression d\'une facture de vente',

            // Products/Inventory
            'inventory.index' => 'Consultation de l\'inventaire',
            'inventory.show' => 'Consultation d\'un produit',
            'inventory.create' => 'Affichage du formulaire de nouveau produit',
            'inventory.store' => 'Ajout d\'un nouveau produit',
            'inventory.edit' => 'Affichage du formulaire de modification de produit',
            'inventory.update' => 'Modification d\'un produit',
            'inventory.destroy' => 'Suppression d\'un produit',

            // Clients
            'clients.index' => 'Consultation de la liste des clients',
            'clients.show' => 'Consultation d\'un client',
            'clients.create' => 'Affichage du formulaire de nouveau client',
            'clients.store' => 'Création d\'un nouveau client',
            'clients.edit' => 'Affichage du formulaire de modification de client',
            'clients.update' => 'Modification d\'un client',
            'clients.destroy' => 'Suppression d\'un client',

            // Prescriptions
            'prescriptions.index' => 'Consultation des ordonnances',
            'prescriptions.show' => 'Consultation d\'une ordonnance',
            'prescriptions.create' => 'Affichage du formulaire de nouvelle ordonnance',
            'prescriptions.store' => 'Création d\'une nouvelle ordonnance',
            'prescriptions.edit' => 'Affichage du formulaire de modification d\'ordonnance',
            'prescriptions.update' => 'Modification d\'une ordonnance',
            'prescriptions.deliver' => 'Affichage du formulaire de délivrance d\'ordonnance',
            'prescriptions.process-delivery' => 'Délivrance d\'une ordonnance',
            'prescriptions.print' => 'Impression d\'une ordonnance',

            // Suppliers
            'suppliers.index' => 'Consultation de la liste des fournisseurs',
            'suppliers.show' => 'Consultation d\'un fournisseur',
            'suppliers.create' => 'Affichage du formulaire de nouveau fournisseur',
            'suppliers.store' => 'Création d\'un nouveau fournisseur',
            'suppliers.edit' => 'Affichage du formulaire de modification de fournisseur',
            'suppliers.update' => 'Modification d\'un fournisseur',
            'suppliers.destroy' => 'Suppression d\'un fournisseur',

            // Purchases
            'purchases.index' => 'Consultation des commandes d\'achat',
            'purchases.show' => 'Consultation d\'une commande d\'achat',
            'purchases.create' => 'Affichage du formulaire de nouvelle commande',
            'purchases.store' => 'Création d\'une nouvelle commande d\'achat',
            'purchases.edit' => 'Affichage du formulaire de modification de commande',
            'purchases.update' => 'Modification d\'une commande d\'achat',
            'purchases.receive' => 'Affichage du formulaire de réception de commande',
            'purchases.process-reception' => 'Réception d\'une commande d\'achat',
            'purchases.cancel' => 'Annulation d\'une commande d\'achat',
            'purchases.print' => 'Impression d\'une commande d\'achat',

            // Reports
            'reports.index' => 'Consultation du tableau de bord des rapports',
            'reports.sales' => 'Consultation du rapport des ventes',
            'reports.inventory' => 'Consultation du rapport d\'inventaire',
            'reports.clients' => 'Consultation du rapport des clients',
            'reports.prescriptions' => 'Consultation du rapport des ordonnances',
            'reports.financial' => 'Consultation du rapport financier',

            // Administration
            'admin.administration' => 'Consultation du panneau d\'administration',
            'admin.settings' => 'Consultation des paramètres système',
            'admin.settings.update' => 'Modification des paramètres système',
            'admin.clear-old-logs' => 'Nettoyage des anciens logs d\'activité',
        ];

        // Return specific description if available
        if (isset($descriptions[$routeName])) {
            return $descriptions[$routeName];
        }

        // Generate generic description
        $resource = $this->extractResourceFromRoute($routeName);
        
        return match($action) {
            'view' => "Consultation de {$resource}",
            'create' => "Création de {$resource}",
            'update' => "Modification de {$resource}",
            'delete' => "Suppression de {$resource}",
            'export' => "Export de {$resource}",
            default => "Action {$action} sur {$resource}"
        };
    }

    /**
     * Extract resource name from route name
     */
    private function extractResourceFromRoute(?string $routeName): string
    {
        if (!$routeName) {
            return 'ressource inconnue';
        }

        $parts = explode('.', $routeName);
        $resource = $parts[0] ?? 'ressource';

        $translations = [
            'admin' => 'administration',
            'users' => 'utilisateurs',
            'sales' => 'ventes',
            'inventory' => 'inventaire',
            'clients' => 'clients',
            'prescriptions' => 'ordonnances',
            'suppliers' => 'fournisseurs',
            'purchases' => 'achats',
            'reports' => 'rapports',
        ];

        return $translations[$resource] ?? $resource;
    }
}