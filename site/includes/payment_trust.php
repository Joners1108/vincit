<?php
/**
 * Blocos de confiança — métodos de pagamento (Stripe: cartão, MB Way, Multibanco)
 *
 * @param string $variant footer | product | checkout | faq
 */
function payment_trust_render($variant = 'footer') {
    static $css_out = false;
    if (!$css_out) {
        $css_out = true;
        ?>
<style id="payment-trust-css">
.payment-trust { --pt-fg: rgba(255,255,255,0.92); --pt-muted: rgba(255,255,255,0.65); }
.payment-trust--footer {
    max-width: 1280px;
    margin: 0 auto;
    padding: 1.25rem 1.25rem 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    text-align: center;
}
.payment-trust--footer .payment-trust__lead {
    font-size: 0.82rem;
    color: var(--pt-muted);
    line-height: 1.45;
    margin: 0 0 0.85rem;
    max-width: 36rem;
    margin-left: auto;
    margin-right: auto;
}
.payment-trust__icons {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.65rem 1rem;
    margin-bottom: 0.75rem;
}
.payment-trust__icons .pay-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 2rem;
    padding: 0.2rem 0.5rem;
    background: rgba(255,255,255,0.08);
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.12);
    font-size: 1.65rem;
    line-height: 1;
}
.payment-trust__icons .pay-icon--visa { color: #1a1f71; background: #fff; }
.payment-trust__icons .pay-icon--mc { color: #eb001b; background: #fff; font-size: 1.4rem; }
.payment-trust__icons .pay-icon--mbway {
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #00d639;
    background: #fff;
    padding: 0.35rem 0.55rem;
    font-family: system-ui, sans-serif;
}
.payment-trust__icons .pay-icon--multibanco {
    font-size: 0.62rem;
    font-weight: 700;
    color: #111;
    background: linear-gradient(180deg, #f8f8f8, #e8e8e8);
    padding: 0.35rem 0.45rem;
    max-width: 5.2rem;
    text-align: center;
    line-height: 1.15;
    font-family: system-ui, sans-serif;
}
.payment-trust__badges {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.4rem 0.5rem;
}
.payment-trust__badges span {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--pt-fg);
    padding: 0.35rem 0.55rem;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(0,0,0,0.2);
}
.payment-trust--product {
    margin-top: 1.25rem;
    padding: 1rem 1rem 1.1rem;
    background: linear-gradient(145deg, #fafafa 0%, #f0f0f0 100%);
    border: 1px solid #e5e5e5;
    border-radius: 12px;
}
.payment-trust--product .payment-trust__lead {
    font-size: 0.8rem;
    color: #4b5563;
    margin: 0 0 0.65rem;
    line-height: 1.45;
}
.payment-trust--product .payment-trust__icons .pay-icon {
    background: #fff;
    border-color: #e5e7eb;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.payment-trust--product .payment-trust__badges span {
    color: #374151;
    border-color: #d1d5db;
    background: #fff;
}
.payment-trust--checkout {
    padding: 0;
    margin: 0;
}
.payment-trust--checkout .payment-trust__prechoice-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6b7280;
    margin: 0 0 0.65rem;
}
.payment-trust--checkout .payment-trust__methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    margin-bottom: 0.85rem;
}
@media (max-width: 520px) {
    .payment-trust--checkout .payment-trust__methods { grid-template-columns: 1fr; }
}
.payment-trust--checkout .pay-choice {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 0.75rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #111;
}
.payment-trust--checkout .pay-choice i { color: #635bff; font-size: 1.1rem; }
.payment-trust--checkout .payment-trust__lead {
    font-size: 0.88rem;
    color: #4b5563;
    margin: 0 0 0.75rem;
    line-height: 1.5;
}
.payment-trust--checkout .payment-trust__icons { margin-bottom: 0.5rem; }
.payment-trust--checkout .payment-trust__icons .pay-icon { background: #fff; }
.payment-trust--checkout .payment-trust__stripe-note {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
    padding-top: 0.5rem;
    border-top: 1px solid #eee;
}
.payment-trust--faq {
    margin-top: 0.75rem;
    padding: 0.85rem 1rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}
.payment-trust--faq .payment-trust__lead { font-size: 0.88rem; color: #374151; margin: 0 0 0.5rem; }
.payment-trust--faq .payment-trust__badges span {
    color: #374151;
    border-color: #d1d5db;
    background: #fff;
}
</style>
        <?php
    }

    $variant = preg_replace('/[^a-z]/', '', strtolower($variant));
    if (!in_array($variant, ['footer', 'product', 'checkout', 'faq'], true)) {
        $variant = 'footer';
    }

    if ($variant === 'footer') : ?>
<div class="payment-trust payment-trust--footer" role="region" aria-label="Pagamentos">
    <p class="payment-trust__lead">Pagamentos seguros via Stripe: Cartão, MB Way e Referência Multibanco</p>
    <div class="payment-trust__icons" aria-hidden="true">
        <span class="pay-icon pay-icon--visa" title="Visa"><i class="fab fa-cc-visa"></i></span>
        <span class="pay-icon pay-icon--mc" title="Mastercard"><i class="fab fa-cc-mastercard"></i></span>
        <span class="pay-icon pay-icon--mbway" title="MB Way">MB WAY</span>
        <span class="pay-icon pay-icon--multibanco" title="Multibanco">REFERÊNCIA MULTIBANCO</span>
    </div>
    <div class="payment-trust__badges">
        <span><i class="fab fa-stripe" style="margin-right:4px;opacity:.9"></i> Stripe</span>
        <span>SSL seguro</span>
        <span>MB Way</span>
        <span>Multibanco</span>
    </div>
</div>
    <?php elseif ($variant === 'product') : ?>
<div class="payment-trust payment-trust--product" role="region" aria-label="Pagamentos">
    <p class="payment-trust__lead">Pagamentos seguros via Stripe: Cartão (Visa, Mastercard), MB Way e Referência Multibanco</p>
    <div class="payment-trust__icons" aria-hidden="true">
        <span class="pay-icon pay-icon--visa"><i class="fab fa-cc-visa"></i></span>
        <span class="pay-icon pay-icon--mc"><i class="fab fa-cc-mastercard"></i></span>
        <span class="pay-icon pay-icon--mbway">MB WAY</span>
        <span class="pay-icon pay-icon--multibanco">MULTIBANCO</span>
    </div>
    <div class="payment-trust__badges">
        <span>Pagamento seguro via Stripe</span>
        <span>SSL</span>
    </div>
</div>
    <?php elseif ($variant === 'checkout') : ?>
<div class="payment-trust payment-trust--checkout" role="region" aria-label="Métodos de pagamento">
    <p class="payment-trust__prechoice-title">No passo seguinte (Stripe) poderá escolher:</p>
    <div class="payment-trust__methods">
        <div class="pay-choice"><i class="fas fa-mobile-alt"></i> MB Way</div>
        <div class="pay-choice"><i class="fas fa-barcode"></i> Multibanco</div>
        <div class="pay-choice"><i class="fas fa-credit-card"></i> Cartão</div>
    </div>
    <p class="payment-trust__lead">Pagamentos seguros via Stripe: Cartão (Visa, Mastercard), MB Way e Referência Multibanco</p>
    <div class="payment-trust__icons" aria-hidden="true">
        <span class="pay-icon pay-icon--visa"><i class="fab fa-cc-visa"></i></span>
        <span class="pay-icon pay-icon--mc"><i class="fab fa-cc-mastercard"></i></span>
        <span class="pay-icon pay-icon--mbway">MB WAY</span>
        <span class="pay-icon pay-icon--multibanco">MULTIBANCO</span>
    </div>
    <div class="payment-trust__badges">
        <span><i class="fab fa-stripe" style="margin-right:4px"></i> Pagamento seguro via Stripe</span>
        <span>SSL 100% seguro</span>
        <span>MB Way disponível</span>
        <span>Referência Multibanco</span>
    </div>
    <p class="payment-trust__stripe-note"><i class="fas fa-info-circle" style="opacity:.7"></i> O pagamento é concluído no ambiente seguro da Stripe; aqui apenas confirma os dados de envio.</p>
</div>
    <?php else : ?>
<div class="payment-trust payment-trust--faq" role="region" aria-label="Métodos aceites">
    <p class="payment-trust__lead"><strong>Métodos aceites:</strong> Cartão (Visa, Mastercard), MB Way e Referência Multibanco — processados de forma segura pela Stripe.</p>
    <div class="payment-trust__icons" aria-hidden="true" style="margin-bottom:0.5rem;">
        <span class="pay-icon pay-icon--visa"><i class="fab fa-cc-visa"></i></span>
        <span class="pay-icon pay-icon--mc"><i class="fab fa-cc-mastercard"></i></span>
        <span class="pay-icon pay-icon--mbway">MB WAY</span>
        <span class="pay-icon pay-icon--multibanco">MULTIBANCO</span>
    </div>
    <div class="payment-trust__badges">
        <span>Pagamento seguro via Stripe</span>
        <span>SSL</span>
    </div>
</div>
    <?php endif;
}
