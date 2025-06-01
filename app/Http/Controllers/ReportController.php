<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use App\Models\Prescription;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        $user = auth()->user();
        
        // Données pour les graphiques de base
        $salesStats = $this->getSalesStats();
        $inventoryStats = $this->getInventoryStats();
        $clientStats = $this->getClientStats();
        
        if ($user->isAdmin()) {
            $purchaseStats = $this->getPurchaseStats();
            $userStats = $this->getUserStats();
            return view('rapports.index', compact('salesStats', 'inventoryStats', 'clientStats', 'purchaseStats', 'userStats'));
        }
        
        return view('rapports.index', compact('salesStats', 'inventoryStats', 'clientStats'));
    }

    /**
     * Sales report
     */
    public function sales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        // Ventes par période
        $salesByPeriod = $this->getSalesByPeriod($dateFrom, $dateTo, $groupBy);
        
        // Top produits vendus
        $topProducts = $this->getTopProducts($dateFrom, $dateTo, 10);
        
        // Ventes par utilisateur
        $salesByUser = $this->getSalesByUser($dateFrom, $dateTo);
        
        // Ventes par méthode de paiement
        $salesByPaymentMethod = $this->getSalesByPaymentMethod($dateFrom, $dateTo);
        
        // Statistiques générales
        $totalSales = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])->sum('total_amount');
        $totalTransactions = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])->count();
        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        
        return view('rapports.sales', compact(
            'salesByPeriod', 'topProducts', 'salesByUser', 'salesByPaymentMethod',
            'totalSales', 'totalTransactions', 'averageTransaction',
            'dateFrom', 'dateTo', 'groupBy'
        ));
    }

    /**
     * Inventory report
     */
    public function inventory()
    {
        // Produits avec stock faible
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'stock_threshold')
            ->with('category', 'supplier')
            ->orderBy('stock_quantity')
            ->get();

        // Produits en rupture
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)
            ->with('category', 'supplier')
            ->get();

        // Produits qui expirent bientôt
        $expiringProducts = Product::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->with('category', 'supplier')
            ->orderBy('expiry_date')
            ->get();

        // Top catégories par valeur de stock
        $categoriesValue = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', 
                     DB::raw('SUM(products.stock_quantity * products.purchase_price) as total_value'),
                     DB::raw('SUM(products.stock_quantity) as total_quantity'))
            ->whereNull('products.deleted_at')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_value', 'desc')
            ->get();

        // Statistiques générales
        $totalProducts = Product::count();
        $totalStockValue = Product::sum(DB::raw('stock_quantity * purchase_price'));
        $averageStockLevel = Product::avg('stock_quantity');

        return view('rapports.inventory', compact(
            'lowStockProducts', 'outOfStockProducts', 'expiringProducts', 'categoriesValue',
            'totalProducts', 'totalStockValue', 'averageStockLevel'
        ));
    }

    /**
     * Client report
     */
    public function clients(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Top clients par montant dépensé
        $topClients = Client::withSum(['sales as total_spent' => function($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('sale_date', [$dateFrom, $dateTo]);
        }], 'total_amount')
        ->withCount(['sales as total_purchases' => function($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('sale_date', [$dateFrom, $dateTo]);
        }])
        ->having('total_spent', '>', 0)
        ->orderBy('total_spent', 'desc')
        ->take(20)
        ->get();

        // Nouveaux clients par mois
        $newClientsByMonth = Client::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Clients avec allergies
        $clientsWithAllergies = Client::whereNotNull('allergies')
            ->where('allergies', '!=', '')
            ->count();

        // Statistiques générales
        $totalClients = Client::count();
        $activeClients = Client::where('active', true)->count();
        $clientsWithPurchases = Client::has('sales')->count();

        return view('rapports.clients', compact(
            'topClients', 'newClientsByMonth', 'clientsWithAllergies',
            'totalClients', 'activeClients', 'clientsWithPurchases',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Financial report
     */
    public function financial(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Revenus et dépenses
        $revenue = Sale::whereBetween('sale_date', [$dateFrom, $dateTo])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $expenses = Purchase::whereBetween('order_date', [$dateFrom, $dateTo])
            ->where('status', 'received')
            ->sum('total_amount');

        $profit = $revenue - $expenses;

        // Revenus par mois (12 derniers mois)
        $revenueByMonth = DB::table('sales')
            ->select(
                DB::raw('DATE_FORMAT(sale_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )
            ->where('sale_date', '>=', now()->subMonths(12))
            ->where('payment_status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Dépenses par mois
        $expensesByMonth = DB::table('purchases')
            ->select(
                DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as expenses')
            )
            ->where('order_date', '>=', now()->subMonths(12))
            ->where('status', 'received')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Marges par produit
        $productMargins = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as total_cost'),
                DB::raw('SUM(sale_items.total_price - (sale_items.quantity * products.purchase_price)) as total_margin')
            )
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->where('sales.payment_status', 'paid')
            ->groupBy('products.id', 'products.name')
            ->having('total_sold', '>', 0)
            ->orderBy('total_margin', 'desc')
            ->take(20)
            ->get();

        return view('rapports.financial', compact(
            'revenue', 'expenses', 'profit', 'revenueByMonth', 'expensesByMonth', 'productMargins',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Prescriptions report
     */
    public function prescriptions(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Ordonnances par statut
        $prescriptionsByStatus = Prescription::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('prescription_date', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->get();

        // Ordonnances expirées
        $expiredPrescriptions = Prescription::where('expiry_date', '<', now())
            ->where('status', '!=', 'completed')
            ->with('client')
            ->orderBy('expiry_date', 'desc')
            ->take(20)
            ->get();

        // Top médicaments prescrits
        $topPrescribedMedications = DB::table('prescription_items')
            ->join('products', 'prescription_items.product_id', '=', 'products.id')
            ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->select(
                'products.name',
                DB::raw('SUM(prescription_items.quantity_prescribed) as total_prescribed'),
                DB::raw('SUM(prescription_items.quantity_delivered) as total_delivered'),
                DB::raw('COUNT(DISTINCT prescription_items.prescription_id) as prescription_count')
            )
            ->whereBetween('prescriptions.prescription_date', [$dateFrom, $dateTo])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_prescribed', 'desc')
            ->take(15)
            ->get();

        // Statistiques générales
        $totalPrescriptions = Prescription::whereBetween('prescription_date', [$dateFrom, $dateTo])->count();
        $completedPrescriptions = Prescription::whereBetween('prescription_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')->count();
        $completionRate = $totalPrescriptions > 0 ? ($completedPrescriptions / $totalPrescriptions) * 100 : 0;

        return view('rapports.prescriptions', compact(
            'prescriptionsByStatus', 'expiredPrescriptions', 'topPrescribedMedications',
            'totalPrescriptions', 'completedPrescriptions', 'completionRate',
            'dateFrom', 'dateTo'
        ));
    }

    // Méthodes privées pour les statistiques

    private function getSalesStats()
    {
        return [
            'today' => Sale::whereDate('sale_date', today())->sum('total_amount'),
            'week' => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount'),
            'month' => Sale::whereMonth('sale_date', now()->month)->sum('total_amount'),
            'year' => Sale::whereYear('sale_date', now()->year)->sum('total_amount'),
        ];
    }

    private function getInventoryStats()
    {
        return [
            'total_products' => Product::count(),
            'low_stock' => Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            'expiring_soon' => Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count(),
        ];
    }

    private function getClientStats()
    {
        return [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('active', true)->count(),
            'new_this_month' => Client::whereMonth('created_at', now()->month)->count(),
            'with_allergies' => Client::whereNotNull('allergies')->where('allergies', '!=', '')->count(),
        ];
    }

    private function getPurchaseStats()
    {
        return [
            'pending_purchases' => Purchase::where('status', 'pending')->count(),
            'total_this_month' => Purchase::whereMonth('order_date', now()->month)->sum('total_amount'),
            'overdue_purchases' => Purchase::where('status', 'pending')->where('expected_date', '<', now())->count(),
        ];
    }

    private function getUserStats()
    {
        return [
            'total_users' => User::count(),
            'admins' => User::where('role', 'responsable')->count(),
            'pharmacists' => User::where('role', 'pharmacien')->count(),
        ];
    }

    private function getSalesByPeriod($dateFrom, $dateTo, $groupBy)
    {
        $format = match($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };

        return Sale::select(
            DB::raw("DATE_FORMAT(sale_date, '{$format}') as period"),
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('sale_date', [$dateFrom, $dateTo])
        ->groupBy('period')
        ->orderBy('period')
        ->get();
    }

    private function getTopProducts($dateFrom, $dateTo, $limit)
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->take($limit)
            ->get();
    }

    private function getSalesByUser($dateFrom, $dateTo)
    {
        return Sale::select(
            'users.name',
            DB::raw('SUM(sales.total_amount) as total_sales'),
            DB::raw('COUNT(*) as total_transactions')
        )
        ->join('users', 'sales.user_id', '=', 'users.id')
        ->whereBetween('sale_date', [$dateFrom, $dateTo])
        ->groupBy('users.id', 'users.name')
        ->orderBy('total_sales', 'desc')
        ->get();
    }

    private function getSalesByPaymentMethod($dateFrom, $dateTo)
    {
        return Sale::select(
            'payment_method',
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('sale_date', [$dateFrom, $dateTo])
        ->groupBy('payment_method')
        ->orderBy('total', 'desc')
        ->get();
    }
}