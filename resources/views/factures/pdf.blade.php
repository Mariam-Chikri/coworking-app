<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        * { font-family: 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { font-size: 12px; color: #333; padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 3px solid #667eea; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 800; color: #667eea; }
        .logo strong { color: #764ba2; }
        .company-info { text-align: right; color: #666; font-size: 11px; line-height: 1.6; }
        .invoice-title { font-size: 28px; font-weight: 700; color: #667eea; margin-bottom: 5px; }
        .invoice-meta { display: flex; justify-content: space-between; margin-bottom: 30px; gap: 20px; }
        .meta-box { background: #f9f9f9; padding: 15px; border-radius: 8px; flex: 1; }
        .meta-box h4 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 8px; }
        .meta-box p { font-size: 12px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #667eea; color: white; }
        th { padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .totaux { float: right; width: 280px; }
        .totaux table td { padding: 6px 10px; }
        .totaux .total-ttc { background: #667eea; color: white; font-weight: 700; font-size: 14px; }
        .footer { margin-top: 60px; padding-top: 20px; border-top: 1px solid #eee; font-size: 10px; color: #999; text-align: center; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-emise { background: #fff7ed; color: #c2410c; }
        .badge-payee { background: #ecfdf5; color: #065f46; }
    </style>
</head>
<body>
<div class="header">
    <div>
        <div class="logo">CoWork<strong>Space</strong></div>
        <div style="color:#666;margin-top:5px;font-size:11px">
            42 Rue du Coworking, 75001 Paris<br>
            contact@coworking.fr | +33 1 23 45 67 89<br>
            SIRET : 12345678900012 | TVA : FR12345678900
        </div>
    </div>
    <div class="company-info">
        <div class="invoice-title">FACTURE</div>
        <div style="font-size:16px;font-weight:700;color:#764ba2">{{ $facture->numero }}</div>
        <div style="margin-top:10px">
            Émise le : {{ $facture->date_emission->format('d/m/Y') }}<br>
            Échéance : {{ $facture->date_echeance?->format('d/m/Y') ?? 'N/A' }}<br>
            Statut : <span class="badge badge-{{ $facture->statut }}">{{ strtoupper($facture->statut) }}</span>
        </div>
    </div>
</div>

<div class="invoice-meta">
    <div class="meta-box">
        <h4>Client</h4>
        <p>
            <strong>{{ $facture->user->name }}</strong><br>
            {{ $facture->user->email }}<br>
            @if($facture->user->entreprise){{ $facture->user->entreprise }}<br>@endif
            @if($facture->user->telephone){{ $facture->user->telephone }}@endif
        </p>
    </div>
    <div class="meta-box">
        <h4>Réservation</h4>
        <p>
            <strong>{{ $facture->reservation->numero }}</strong><br>
            Espace : {{ $facture->reservation->espace->nom }}<br>
            Du : {{ $facture->reservation->debut->format('d/m/Y H:i') }}<br>
            Au : {{ $facture->reservation->fin->format('d/m/Y H:i') }}<br>
            Durée : {{ $facture->reservation->duree_heures }}h
        </p>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Qté</th>
            <th>Prix unitaire HT</th>
            <th>Total HT</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                Location espace : {{ $facture->reservation->espace->nom }}<br>
                <small style="color:#999">{{ $facture->reservation->debut->format('d/m/Y H:i') }} → {{ $facture->reservation->fin->format('H:i') }}</small>
            </td>
            <td>{{ $facture->reservation->duree_heures }}h</td>
            <td>{{ number_format($facture->reservation->espace->prix_heure / 1.20, 2) }} €</td>
            <td>{{ number_format($facture->montant_ht, 2) }} €</td>
        </tr>
        @if($facture->reservation->prix_prolongation > 0)
        <tr>
            <td>Prolongation de réservation</td>
            <td>—</td>
            <td>—</td>
            <td>{{ number_format($facture->reservation->prix_prolongation / 1.20, 2) }} €</td>
        </tr>
        @endif
    </tbody>
</table>

<div class="totaux">
    <table>
        <tr><td>Sous-total HT</td><td>{{ number_format($facture->montant_ht, 2) }} €</td></tr>
        <tr><td>TVA ({{ $facture->tva }}%)</td><td>{{ number_format($facture->montant_ttc - $facture->montant_ht, 2) }} €</td></tr>
        <tr class="total-ttc"><td><strong>TOTAL TTC</strong></td><td><strong>{{ number_format($facture->montant_ttc, 2) }} €</strong></td></tr>
    </table>
</div>

<div style="clear:both"></div>

@if($facture->statut === 'payee')
<div style="margin-top:20px;padding:15px;background:#ecfdf5;border-radius:8px;border-left:4px solid #10b981;font-size:11px">
    <strong style="color:#065f46">✓ PAYÉE</strong> — Règlement reçu le {{ $facture->date_paiement?->format('d/m/Y') }}
    @if($facture->methode_paiement) par {{ ucfirst($facture->methode_paiement) }}@endif
</div>
@endif

<div class="footer">
    <p>CoWorkSpace — 42 Rue du Coworking, 75001 Paris | contact@coworking.fr | +33 1 23 45 67 89</p>
    <p style="margin-top:5px">Règlement par virement : IBAN FR76 1234 5678 9012 3456 7890 123 | BIC XXXXXXXX</p>
    <p style="margin-top:5px;color:#bbb">Facture générée automatiquement — Merci pour votre confiance !</p>
</div>
</body>
</html>
