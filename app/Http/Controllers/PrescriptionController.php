<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Client;
use App\Models\Product;

class PrescriptionController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index(Request $request)
    {
        $query = Prescription::with(['client', 'createdBy', 'prescriptionItems.product']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                  ->orWhere('doctor_name', 'like', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('first_name', 'like', "%{$search}%")
                                 ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('prescription_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('prescription_date', '<=', $request->date_to);
        }

        if ($request->has('expiry_filter') && $request->expiry_filter !== '') {
            if ($request->expiry_filter === 'expired') {
                $query->expired();
            } elseif ($request->expiry_filter === 'expiring_soon') {
                $query->active()->where('expiry_date', '<=', now()->addDays(7));
            }
        }

        $prescriptions = $query->latest('prescription_date')->paginate(15);
        
        $totalPrescriptions = $query->count();
        $pendingCount = Prescription::pending()->count();
        $expiredCount = Prescription::expired()->count();
        $expiringCount = Prescription::active()->where('expiry_date', '<=', now()->addDays(7))->count();
        
        return view('prescriptions.index', compact(
            'prescriptions', 'totalPrescriptions', 'pendingCount', 'expiredCount', 'expiringCount'
        ));
    }

    public function create()
    {
        $clients = Client::active()->orderBy('first_name')->get();
        $products = Product::orderBy('name')->get();
        return view('prescriptions.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'doctor_name' => 'required|string|max:255',
            'doctor_phone' => 'nullable|string|max:20',
            'doctor_speciality' => 'nullable|string|max:255',
            'prescription_date' => 'required|date',
            'expiry_date' => 'required|date|after:prescription_date',
            'medical_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_prescribed' => 'required|integer|min:1',
            'items.*.dosage_instructions' => 'required|string|max:255',
            'items.*.duration_days' => 'nullable|integer|min:1',
            'items.*.instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            $prescription = new Prescription();
            $prescription->fill($request->except('items'));
            $prescription->created_by = auth()->id();
            $prescription->save();

            foreach ($request->items as $itemData) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_prescribed' => $itemData['quantity_prescribed'],
                    'dosage_instructions' => $itemData['dosage_instructions'],
                    'duration_days' => $itemData['duration_days'] ?? null,
                    'instructions' => $itemData['instructions'] ?? null,
                    'is_substitutable' => isset($itemData['is_substitutable']),
                ]);
            }

            DB::commit();
            return redirect()->route('prescriptions.show', $prescription->id)
                ->with('success', 'Ordonnance créée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la création de l\'ordonnance: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $prescription = Prescription::with(['client', 'createdBy', 'deliveredBy', 'prescriptionItems.product'])->findOrFail($id);
        return view('prescriptions.show', compact('prescription'));
    }

    public function edit($id)
    {
        $prescription = Prescription::with(['prescriptionItems.product'])->findOrFail($id);
        
        if (in_array($prescription->status, ['completed', 'expired'])) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance ne peut plus être modifiée.']);
        }
        
        $clients = Client::active()->orderBy('first_name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('prescriptions.edit', compact('prescription', 'clients', 'products'));
    }

    public function update(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);
        
        if (in_array($prescription->status, ['completed', 'expired'])) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance ne peut plus être modifiée.']);
        }

        $validator = Validator::make($request->all(), [
            'doctor_name' => 'required|string|max:255',
            'doctor_phone' => 'nullable|string|max:20',
            'doctor_speciality' => 'nullable|string|max:255',
            'prescription_date' => 'required|date',
            'expiry_date' => 'required|date|after:prescription_date',
            'medical_notes' => 'nullable|string',
            'pharmacist_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $prescription->update($request->only([
            'doctor_name', 'doctor_phone', 'doctor_speciality',
            'prescription_date', 'expiry_date', 'medical_notes', 'pharmacist_notes'
        ]));

        return redirect()->route('prescriptions.show', $prescription->id)
            ->with('success', 'Ordonnance mise à jour avec succès!');
    }

    public function deliver($id)
    {
        $prescription = Prescription::with(['client', 'prescriptionItems.product'])->findOrFail($id);
        
        if ($prescription->status === 'completed') {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance a déjà été complètement délivrée.']);
        }
        
        if ($prescription->isExpired()) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance a expiré et ne peut plus être délivrée.']);
        }
        
        return view('prescriptions.deliver', compact('prescription'));
    }

    public function processDelivery(Request $request, $id)
    {
        $prescription = Prescription::with(['prescriptionItems'])->findOrFail($id);
        
        if ($prescription->isExpired()) {
            return redirect()->route('prescriptions.show', $prescription->id)
                ->withErrors(['error' => 'Cette ordonnance a expiré.']);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:prescription_items,id',
            'items.*.quantity_to_deliver' => 'required|integer|min:0',
            'pharmacist_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            foreach ($request->items as $itemData) {
                $prescriptionItem = PrescriptionItem::find($itemData['item_id']);
                $quantityToDeliver = (int) $itemData['quantity_to_deliver'];
                
                $maxQuantity = $prescriptionItem->quantity_prescribed - $prescriptionItem->quantity_delivered;
                if ($quantityToDeliver > $maxQuantity) {
                    throw new \Exception("Quantité trop élevée pour {$prescriptionItem->product->name}. Maximum: {$maxQuantity}");
                }
                
                $prescriptionItem->quantity_delivered += $quantityToDeliver;
                $prescriptionItem->save();
                
                if ($quantityToDeliver > 0) {
                    $prescriptionItem->product->decrement('stock_quantity', $quantityToDeliver);
                }
            }
            
            if ($request->pharmacist_notes) {
                $prescription->pharmacist_notes = $request->pharmacist_notes;
            }
            $prescription->updateStatus();
            
            DB::commit();

            return redirect()->route('prescriptions.show', $prescription->id)
                ->with('success', 'Délivrance enregistrée avec succès!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function print($id)
    {
        $prescription = Prescription::with(['client', 'createdBy', 'prescriptionItems.product'])->findOrFail($id);
        return view('prescriptions.print', compact('prescription'));
    }
}