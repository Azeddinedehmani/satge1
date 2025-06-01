<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use App\Models\Prescription;
use App\Models\Purchase;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        // Dashboard statistics
        $stats = [
            'sales_today' => Sale::whereDate('sale_date', today())->sum('total_amount'),
            'clients_today' => Sale::whereDate('sale_date', today())->distinct('client_id')->count(),
            'products_low_stock' => Product::whereColumn('stock_quantity', '<=', 'stock_threshold')->count(),
            'products_expiring' => Product::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count(),
            'prescriptions_pending' => Prescription::where('status', 'pending')->count(),
            'purchases_pending' => Purchase::where('status', 'pending')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
        ];

        // Recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Sales chart data (last 7 days)
        $salesChart = Sale::selectRaw('DATE(sale_date) as date, SUM(total_amount) as total')
            ->where('sale_date', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // User activity chart (last 30 days)
        $userActivityChart = ActivityLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivities', 'salesChart', 'userActivityChart'));
    }

    /**
     * Show administration panel
     */
    public function administration()
    {
        // System information
        $systemInfo = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown',
            'disk_usage' => $this->getDiskUsage(),
            'memory_usage' => $this->getMemoryUsage(),
        ];

        // Activity statistics
        $activityStats = [
            'total_activities' => ActivityLog::count(),
            'activities_today' => ActivityLog::whereDate('created_at', today())->count(),
            'activities_week' => ActivityLog::where('created_at', '>=', now()->subDays(7))->count(),
            'most_active_user' => $this->getMostActiveUser(),
            'most_common_action' => $this->getMostCommonAction(),
        ];

        // Recent system activities
        $systemActivities = ActivityLog::with('user')
            ->whereIn('action', ['login', 'logout', 'create', 'update', 'delete'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.administration', compact('systemInfo', 'activityStats', 'systemActivities'));
    }

    /**
     * System settings management
     */
    public function settings()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('admin.settings', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        foreach ($request->settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $oldValue = $setting->value;
                $setting->value = $value;
                $setting->save();

                // Log setting change
                ActivityLog::logActivity(
                    'update',
                    "Paramètre système modifié: {$key}",
                    $setting,
                    ['value' => $oldValue],
                    ['value' => $value]
                );
            }
        }

        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès!');
    }

    /**
     * Activity logs overview
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action') && $request->action !== '') {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->has('model_type') && $request->model_type !== '') {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(50);
        
        // Get filter options
        $users = User::orderBy('name')->get();
        $actions = ActivityLog::distinct()->pluck('action')->filter()->sort()->values();
        $modelTypes = ActivityLog::distinct()->pluck('model_type')->filter()->sort()->values();

        return view('admin.activity-logs', compact('activities', 'users', 'actions', 'modelTypes'));
    }

    /**
     * Export activity logs
     */
    public function exportActivityLogs(Request $request)
    {
        $query = ActivityLog::with('user');

        // Apply same filters as in activityLogs method
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && $request->action !== '') {
            $query->where('action', $request->action);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->get();

        // Log export activity
        ActivityLog::logActivity(
            'export',
            'Export des logs d\'activité (' . $activities->count() . ' entrées)'
        );

        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, [
                'Date/Heure',
                'Utilisateur',
                'Action',
                'Description',
                'Modèle',
                'IP',
                'Navigateur'
            ], ';');

            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->created_at->format('d/m/Y H:i:s'),
                    $activity->user ? $activity->user->name : 'Système',
                    $activity->action,
                    $activity->description,
                    $activity->model_name,
                    $activity->ip_address,
                    $activity->user_agent
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear old activity logs
     */
    public function clearOldLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $days = $request->days;
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->count();
        ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        // Log this action
        ActivityLog::logActivity(
            'delete',
            "Suppression des logs d'activité de plus de {$days} jours ({$deletedCount} entrées supprimées)"
        );

        return redirect()->back()
            ->with('success', "{$deletedCount} logs d'activité supprimés avec succès!");
    }

    /**
     * Get disk usage information
     */
    private function getDiskUsage()
    {
        $bytes = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());
        
        return [
            'free' => $this->formatBytes($bytes),
            'total' => $this->formatBytes($total),
            'used_percent' => round((($total - $bytes) / $total) * 100, 2)
        ];
    }

    /**
     * Get memory usage information
     */
    private function getMemoryUsage()
    {
        return [
            'current' => $this->formatBytes(memory_get_usage()),
            'peak' => $this->formatBytes(memory_get_peak_usage()),
            'limit' => ini_get('memory_limit')
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get most active user
     */
    private function getMostActiveUser()
    {
        return ActivityLog::select('user_id', DB::raw('COUNT(*) as count'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->first();
    }

    /**
     * Get most common action
     */
    private function getMostCommonAction()
    {
        return ActivityLog::select('action', DB::raw('COUNT(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->first();
    }
}