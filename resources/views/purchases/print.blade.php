<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande d'achat - {{ $purchase->purchase_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
            font-size: 14px;
            line-height: 1.4;
        }
        .purchase-order {
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
        .purchase-info {
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
        .supplier-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin-bottom: 20px;
        }
        .order-info {
            background-color: #e8f4fd;
            padding: 15px;
            border-left: 4px solid #4a90e2;
            margin-bottom: 20px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .products-table th,
        .products-table td {
            border: 1px solid #333;
            padding: 12px;
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
        .order-footer {
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            width: 200px;
            height: 80px;
            border: 1px solid #333;
            text-align: center;
            padding: 10px;
        }
        .status-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #0c5460;
        }
        .notes-section {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .purchase-order {
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

    <div class="purchase-order">
        <!-- En-tête -->
        <div class="header">
            <div class="pharmacy-name">PHARMACIA</div>
            <div class="pharmacy-info">
                Système de Gestion de Pharmacie<br>
                123 Avenue de la Santé, 75001 Paris<br>
                Tél: 01 23 45 67 89 | Email: contact@pharmacia.com
            </div>
        </div>

        <!-- Titre de la commande -->
        <div style="text-align: center; margin: 30px 0; font-size: 20px; font-weight: bold; border: 2px solid #333; padding: 15px;">
            BON DE COMMANDE N° {{ $purchase->purchase_number }}
        </div>

        <!-- Statut de la commande -->
        <div class="status-info">
            <strong>Statut de la commande:</strong> {{ $purchase->status_label }}
            @if($purchase->expected_date && $purchase->expected_date->isPast() && $purchase->status === 'pending')
                - <span style="color: #dc3545;"><strong>EN RETARD</strong> (prévu le {{ $purchase->expected_date->format('d/m/Y') }})</span>
            @endif
        </div>

        <!-- Informations fournisseur -->
        <div class="supplier-info">
            <div class="info-title">FOURNISSEUR</div>
            <div style="display: flex; justify-content: space-between;">
                <div style="flex: 1;">
                    <div class="info-line"><strong>{{ $purchase->supplier->name }}</strong></div>
                    @if($purchase->supplier->contact_person)
                        <div class="info-line">Contact: {{ $purchase->supplier->contact_person }}</div>
                    @endif
                    @if($purchase->supplier->address)
                        <div class="info-line">{{ $purchase->supplier->address }}</div>
                    @endif
                </div>
                <div style="flex: 1;">
                    @if($purchase->supplier->phone_number)
                        <div class="info-line"><strong>Tél:</strong> {{ $purchase->supplier->phone_number }}</div>
                    @endif
                    @if($purchase->supplier->email)
                        <div class="info-line"><strong>Email:</strong> {{ $purchase->supplier->email }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations commande -->
        <div class="order-info">
            <div class="info-title">DÉTAILS DE LA COMMANDE</div>
            <div style="display: flex; justify-content: space-between;">
                <div style="flex: 1;">
                    <div class="info-line"><strong>Date de commande:</strong> {{ $purchase->order_date->format('d/m/Y') }}</div>
                    @if($purchase->expected_date)
                        <div class="info-line"><strong>Date de livraison prévue:</strong> {{ $purchase->expected_date->format('d/m/Y') }}</div>
                    @endif
                    <div class="info-line"><strong>Commandé par:</strong> {{ $purchase->user->name }}</div>
                </div>
                <div style="flex: 1;">
                    <div class="info-line"><strong>Date d'impression:</strong> {{ now()->format('d/m/Y à H:i') }}</div>
                    @if($purchase->received_date)
                        <div class="info-line"><strong>Date de réception:</strong> {{ $purchase->received_date->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes de commande -->
        @if($purchase->notes)
            <div class="notes-section">
                <strong>📝 NOTES DE COMMANDE:</strong>
                <p style="margin: 10px 0 0 0;">{{ $purchase->notes }}</p>
            </div>
        @endif

        <!-- Tableau des produits -->
        <table class="products-table">
            <thead>
                <tr>
                    <th>PRODUIT</th>
                    <th class="text-center">QTÉ COMMANDÉE</th>
                    <th class="text-center">QTÉ REÇUE</th>
                    <th class="text-right">PRIX UNITAIRE</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->purchaseItems as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if($item->product->dosage)
                                <br><small>{{ $item->product->dosage }}</small>
                            @endif
                            @if($item->notes)
                                <br><small style="color: #0066cc;">{{ $item->notes }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity_ordered }}</td>
                        <td class="text-center">
                            {{ $item->quantity_received }}
                            @if($item->remaining_quantity > 0)
                                <br><small style="color: #dc3545;">Reste: {{ $item->remaining_quantity }}</small>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }} €</td>
                        <td class="text-right"><strong>{{ number_format($item->total_price, 2) }} €</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot style="background-color: #f8f9fa;">
                <tr>
                    <th colspan="4" class="text-right">Sous-total HT:</th>
                    <th class="text-right">{{ number_format($purchase->subtotal, 2) }} €</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">TVA (20%):</th>
                    <th class="text-right">{{ number_format($purchase->tax_amount, 2) }} €</th>
                </tr>
                <tr style="background-color: #007bff; color: white;">
                    <th colspan="4" class="text-right">TOTAL TTC:</th>
                    <th class="text-right">{{ number_format($purchase->total_amount, 2) }} €</th>
                </tr>
            </tfoot>
        </table>

        <!-- Pied de page -->
        <div class="order-footer">
            <div style="text-align: center; margin-bottom: 30px;">
                <strong>CONDITIONS DE LIVRAISON</strong><br>
                <small>
                    - Livraison à l'adresse de la pharmacie aux heures d'ouverture<br>
                    - Vérification de la conformité et des dates d'expiration obligatoire<br>
                    - Tout produit non conforme sera refusé
                </small>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-box">
                    <strong>Signature du responsable<br>des achats</strong><br>
                    {{ $purchase->user->name }}
                </div>
                <div class="signature-box">
                    <strong>Cachet et signature<br>du fournisseur</strong><br>
                    {{ $purchase->supplier->name }}
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">
                Pharmacia - Système de Gestion de Pharmacie<br>
                N° SIRET: 123 456 789 00012 | Responsable: {{ auth()->user()->name }}<br>
                Ce bon de commande fait foi jusqu'à réception complète de la marchandise
            </div>
        </div>
    </div>

    <script>
        // Fermer la fenêtre après impression
        window.onafterprint = function() {
            // window.close(); // Décommentez si vous voulez fermer automatiquement
        }
    </script>
</body>
</html>