<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Client;
use App\Models\Product;

class SaleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['client', 'user', 'saleItems.product']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('prescription_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('first_name', 'like', "%{$search}%")
                                 ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Filter by prescription
        if ($request->has('has_prescription') && $request->has_prescription !== '') {
            $query->where('has_prescription', $request->has_prescription === 'yes');
        }

        $sales = $query->latest('sale_date')->paginate(15);
        
        // Calculate summary statistics
        $totalSales = $query->sum('total_amount');
        $salesCount = $query->count();
        $averageSale = $salesCount > 0 ? $totalSales / $salesCount : 0;
        
        return view('sales.index', compact('sales', 'totalSales', 'salesCount', 'averageSale'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create()
    {
        $clients = Client::active()->orderBy('first_name')->get();
        $products = Product::where('stock_quantity', '>', 0)->orderBy('name')->get();
        
        return view('sales.create', compact('clients', 'products'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'required|in:cash,card,insurance,other',
            'has_prescription' => 'boolean',
            'prescription_number' => 'nullable|string|max:255',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate stock availability
        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);
            if ($product->stock_quantity < $productData['quantity']) {
                return redirect()->back()
                    ->withErrors(['products' => "Stock insuffisant pour {$product->name}. Stock disponible: {$product->stock_quantity}"])
                    ->withInput();
            }
        }

        DB::beginTransaction();
        
        try {
            // Create sale
            $sale = new Sale();
            $sale->client_id = $request->client_id;
            $sale->user_id = auth()->id();
            $sale->payment_method = $request->payment_method;
            $sale->payment_status = 'paid';
            $sale->has_prescription = $request->has('has_prescription');
            $sale->prescription_number = $request->prescription_number;
            $sale->discount_amount = $request->discount_amount ?? 0;
            $sale->notes = $request->notes;
            $sale->sale_date = now();
            $sale->save();

            // Create sale items and update stock
            foreach ($request->products as $productData) {
                $product = Product::find($productData['id']);
                
                $saleItem = new SaleItem();
                $saleItem->sale_id = $sale->id;
                $saleItem->product_id = $product->id;
                $saleItem->quantity = $productData['quantity'];
                $saleItem->unit_price = $product->selling_price;
                $saleItem->save();

                // Update product stock
                $product->decrement('stock_quantity', $productData['quantity']);
            }

            // Calculate totals
            $sale->calculateTotals();

            DB::commit();

            return redirect()->route('sales.show', $sale->id)
                ->with('success', 'Vente enregistrée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de l\'enregistrement de la vente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified sale.
     */
    public function show($id)
    {
        $sale = Sale::with(['client', 'user', 'saleItems.product'])->findOrFail($id);
        
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit($id)
    {
        $sale = Sale::with(['saleItems.product'])->findOrFail($id);
        $clients = Client::active()->orderBy('first_name')->get();
        
        return view('sales.edit', compact('sale', 'clients'));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:paid,pending,failed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sale = Sale::findOrFail($id);
        $sale->payment_status = $request->payment_status;
        $sale->notes = $request->notes;
        $sale->save();

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Vente mise à jour avec succès!');
    }

    /**
     * Get product details for AJAX requests.
     */
    public function getProduct($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Produit non trouvé'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->selling_price,
            'stock' => $product->stock_quantity,
            'prescription_required' => $product->prescription_required,
        ]);
    }

    /**
     * Print sale receipt.
     */
    public function print($id)
    {
        $sale = Sale::with(['client', 'user', 'saleItems.product'])->findOrFail($id);
        
        return view('sales.print', compact('sale'));
    }
}