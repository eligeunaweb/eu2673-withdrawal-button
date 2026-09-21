<?php defined( 'ABSPATH' ) || exit; ?>
<div
    id="wbdesist-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="wbdesist-title"
    aria-describedby="wbdesist-desc"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
        <button
            id="wbdesist-close"
            type="button"
            aria-label="<?php esc_attr_e( 'Cerrar ventana de desistimiento', 'eu2673-withdrawal-button' ); ?>"
            style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#888;z-index:1;">&#x2715;</button>
        <div style="background:#2c3e7a;color:#fff;padding:24px 28px 20px;text-align:center;">
            <div style="font-size:32px;margin-bottom:8px;" aria-hidden="true">&#x21A9;</div>
            <h2 id="wbdesist-title" style="margin:0;font-size:18px;font-weight:700;color:#fff;"><?php esc_html_e( 'Ejercer derecho de desistimiento', 'eu2673-withdrawal-button' ); ?></h2>
        </div>
        <div style="padding:22px 28px;">
            <div id="wbdesist-desc" style="background:#f8f9ff;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                <p style="margin:0 0 8px;font-size:13px;color:#555;"><?php esc_html_e( 'Según la Directiva UE 2023/2673, tienes derecho a desistir de este contrato en un plazo de 14 días naturales sin necesidad de justificación alguna.', 'eu2673-withdrawal-button' ); ?></p>
                <p style="margin:0;font-size:13px;color:#555;"><?php esc_html_e( 'Al confirmar, recibirás un email de acuse de recibo y nuestro equipo procesará el reembolso en un máximo de 14 días naturales.', 'eu2673-withdrawal-button' ); ?></p>
            </div>
            <div
                id="wbdesist-msg"
                role="alert"
                aria-live="polite"
                aria-atomic="true"
                style="display:none;padding:12px 16px;border-radius:6px;font-size:14px;margin-top:12px;"></div>
        </div>
        <div style="padding:4px 28px 20px;display:flex;gap:10px;justify-content:flex-end;">
            <button
                id="wbdesist-cancel"
                type="button"
                style="padding:10px 20px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;border:1px solid #ddd;background:#f0f0f0;color:#555;"><?php esc_html_e( 'Cancelar', 'eu2673-withdrawal-button' ); ?></button>
            <button
                id="wbdesist-confirm"
                type="button"
                style="padding:10px 20px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;border:none;background:#2c3e7a;color:#fff;"><?php esc_html_e( 'Confirmar desistimiento', 'eu2673-withdrawal-button' ); ?></button>
        </div>
    </div>
</div>
