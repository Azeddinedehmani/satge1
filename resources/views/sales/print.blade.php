<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de vente - {{ $sale->sale_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
            font-size: 14px;
            line-height: 1.4;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .pharmacy-name {
            font-size: 28px;
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 5px;
        }
        .pharmacy-info {
            color: #666;
            font-size: 12px;
        }
        .sale-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .info-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px;
        }
        .info-title {
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-line {
            margin-bottom: 5px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .products-table th,
        .products-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .products-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .products-table .text-center {
            text-align: center;
        }
        .products-table .text-right {
            text-align: right;
        }
        .totals-section {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 15px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .total-line.final {
            font-weight: bold;
            font-size: 18px;
            border-top: 1px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            color: #666;
            font-size: 12px;
        }
        .prescription-info {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .client-allergies {
            background-color: #f8d7da;
            border: 1px solid #dc3545;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #721c24;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .receipt {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-button:hover {
            background-color: #357abd;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Imprimer
    </button>

    <div class="receipt">
        <!-- En-tête -->
        <div class="header">
            <div class="pharmacy-name">PHARMACIA</div>
            <div class="pharmacy-info">
                Système de Gestion de Pharmacie<br>
                123 Avenue de la Santé, 75001 Paris<br>
                Tél: 01 23 45 67 89 | Email: contact@pharmacia.com
            </div>
        </div>

        <!-- Informations de la vente -->
        <div class="sale-info">
            <div class="info-section">
                <div class="info-title">DÉTAILS DE LA VENTE</div>
                <div class="info-line"><strong>N° de vente:</strong> {{ $sale->sale_number }}</div>
                <div class="info-line"><strong>Date:</strong> {{ $sale->sale_date->format('d/m/Y H:i') }}</div>
                <div class="info-line"><strong>Vendeur:</strong> {{ $sale->user->name }}</div>
                <div class="info-line"><strong>Mode de paiement:</strong> {{ ucfirst($sale->payment_method) }}</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">INFORMATIONS CLIENT</div>
                @if($sale->client)
                    <div class="info-line"><strong>Nom:</strong> {{ $sale->client->full_name }}</div>
                    @if($sale->client->phone)
                        <div class="info-line"><strong>Téléphone:</strong> {{ $sale->client->phone }}</div>
                    @endif
                    @if($sale->client->email)
                        <div class="info-line"><strong>Email:</strong> {{ $sale->client->email }}</div>
                    @endif
                    @if($sale->client->insurance_number)
                        <div class="info-line"><strong>Assurance:</strong> {{ $sale->client->insurance_number }}</div>
                    @endif
                @else
                    <div class="info-line">Client anonyme</div>
                @endif
            </div>
        </div>

        <!-- Alerte allergies client -->
        @if($sale->client && $sale->client->allergies)
            <div class="client-allergies">
                <strong>⚠️ ALLERGIES CONNUES:</strong> {{ $sale->client->allergies }}
            </div>
        @endif

        <!-- Information ordonnance -->
        @if($sale->has_prescription)
            <div class="prescription-info">
                <strong>📋 VENTE AVEC ORDONNANCE</strong>
                @if($sale->prescription_number)
                    - N° {{ $sale->prescription_number }}
                @endif
            </div>
        @endif

        <!-- Tableau des produits -->
        <table class="products-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th class="text-center">Qté</th>
                    <th class="text-right">Prix unitaire</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if($item->product->dosage)
                                <br><small>{{ $item->product->dosage }}</small>
                            @endif
                            @if($item->product->prescription_required)
                                <br><small>⚕️ Ordonnance requise</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }} €</td>
                        <td class="text-right">{{ number_format($item->total_price, 2) }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Section totaux -->
        <div class="totals-section">
            <div class="total-line">
                <span>Sous-total:</span>
                <span>{{ number_format($sale->subtotal, 2) }} €</span>
            </div>
            <div class="total-line">
                <span>TVA (20%):</span>
                <span>{{ number_format($sale->tax_amount, 2) }} €</span>
            </div>
            @if($sale->discount_amount > 0)
                <div class="total-line">
                    <span>Remise:</span>
                    <span>-{{ number_format($sale->discount_amount, 2) }} €</span>
                </div>
            @endif
            <div class="total-line final">
                <span>TOTAL À PAYER:</span>
                <span>{{ number_format($sale->total_amount, 2) }} €</span>
            </div>
        </div>

        @if($sale->notes)
            <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #4a90e2;">
                <strong>Notes:</strong> {{ $sale->notes }}
            </div>
        @endif

        <!-- Pied de page -->
        <div class="footer">
            <div style="margin-bottom: 10px;">
                <strong>Merci pour votre confiance!</strong>
            </div>
            <div>
                Reçu imprimé le {{ now()->format('d/m/Y à H:i') }}<br>
                Conservez ce reçu pour vos remboursements d'assurance<br>
                N° SIRET: 123 456 789 00012 | TVA: FR12345678901
            </div>
            
            @if($sale->has_prescription)
                <div style="margin-top: 15px; font-weight: bold; color: #dc3545;">
                    Important: Respectez la posologie prescrite par votre médecin
                </div>
            @endif
        </div>
    </div>

    <script>
        // Auto-print when page loads (optionnel)
        // window.onload = function() { window.print(); }
        
        // Fermer la fenêtre après impression
        window.onafterprint = function() {
            // window.close(); // Décommentez si vous voulez fermer automatiquement
        }
    </script>
</body>
</html>