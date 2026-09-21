<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap wb-admin-wrap">

<h1><?php esc_html_e( 'Ayuda — Withdrawal Button Lite', 'eu2673-withdrawal-button' ); ?></h1>

<div class="wb-help-grid">

    <div class="wb-help-card">
        <h3><?php esc_html_e( '¿Qué es Withdrawal Button?', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Withdrawal Button añade un botón de desistimiento legal en tu tienda WooCommerce. Permite a tus clientes ejercer su derecho de desistimiento en 1 clic, con acuse de recibo automático por email.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php printf( wp_kses_post( __( 'Cumple con la <strong>Directiva UE 2023/2673</strong>, obligatoria desde el 19 de junio de 2026 para cualquier tienda online que venda a consumidores de la UE.', 'eu2673-withdrawal-button' ) ) ); ?></p>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( '¿Dónde aparece el botón?', 'eu2673-withdrawal-button' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'En la página de confirmación del pedido (thank-you page)', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'En Mi cuenta → detalle del pedido', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Solo aparece dentro del plazo legal de 14 días naturales', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Una vez ejercido, el botón queda deshabilitado para ese pedido', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( '¿Qué ocurre cuando el cliente pulsa el botón?', 'eu2673-withdrawal-button' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'Se abre un modal con el texto legal obligatorio', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'El cliente confirma (y opcionalmente indica un motivo)', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'La solicitud queda registrada en la base de datos', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'El cliente recibe un email de acuse de recibo', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Tú recibes una notificación con enlace directo al pedido', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'El estado del pedido cambia automáticamente (configurable)', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( 'Base legal', 'eu2673-withdrawal-button' ); ?></h3>
        <ul>
            <li><?php echo wp_kses_post( __( '<strong>Directiva UE 2023/2673</strong> — obligatoria desde el 19/06/2026', 'eu2673-withdrawal-button' ) ); ?></li>
            <li><?php esc_html_e( 'Art. 102 TRLGDCU — derecho de desistimiento en contratos a distancia', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Art. 107 TRLGDCU — reembolso en máximo 14 días naturales', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'El consumidor no está obligado a justificar el desistimiento', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( 'Configuración recomendada', 'eu2673-withdrawal-button' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'Mantén los 14 días como plazo (mínimo legal)', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Activa el email al cliente — es obligatorio por ley', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Activa el email al administrador para gestionar las solicitudes', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Activa el cambio automático de estado del pedido', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'Para productos físicos, configura el inicio del plazo desde la fecha de entrega', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( 'Emails automáticos', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'El plugin envía tres tipos de email:', 'eu2673-withdrawal-button' ); ?></p>
        <ul>
            <li><?php echo wp_kses_post( __( '<strong>Al cliente:</strong> acuse de recibo oficial con datos del pedido y referencia legal.', 'eu2673-withdrawal-button' ) ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>Al administrador:</strong> notificación con datos del cliente y enlace al pedido.', 'eu2673-withdrawal-button' ) ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>Actualización de estado:</strong> el cliente es notificado cuando su solicitud es aprobada, rechazada o completada.', 'eu2673-withdrawal-button' ) ); ?></li>
        </ul>
        <p><?php esc_html_e( 'Para máxima fiabilidad instala WP Mail SMTP con un servidor SMTP real.', 'eu2673-withdrawal-button' ); ?></p>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( 'Panel de solicitudes', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Estados disponibles:', 'eu2673-withdrawal-button' ); ?></p>
        <ul>
            <li><?php echo wp_kses_post( __( '<strong>Pendiente:</strong> solicitud recibida, sin procesar. Revisa el pedido y procede al reembolso.', 'eu2673-withdrawal-button' ) ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>Aprobada:</strong> has aceptado el desistimiento y estás procesando el reembolso.', 'eu2673-withdrawal-button' ) ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>Completada:</strong> reembolso realizado correctamente.', 'eu2673-withdrawal-button' ) ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>Rechazada:</strong> la solicitud no cumple los requisitos.', 'eu2673-withdrawal-button' ) ); ?></li>
        </ul>
        <p><?php echo wp_kses_post( __( '<strong>Recuerda:</strong> el plazo legal para el reembolso es de 14 días naturales desde la recepción de la solicitud.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( 'Requisitos técnicos', 'eu2673-withdrawal-button' ); ?></h3>
        <ul>
            <li><?php esc_html_e( 'WordPress 5.8 o superior', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'WooCommerce 6.0 o superior', 'eu2673-withdrawal-button' ); ?></li>
            <li><?php esc_html_e( 'PHP 7.4 o superior (recomendado 8.0+)', 'eu2673-withdrawal-button' ); ?></li>
        </ul>
    </div>

    <div class="wb-help-card">
        <h3><?php esc_html_e( 'Soporte', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Para la versión gratuita, usa el foro de soporte de WordPress.org.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php esc_html_e( 'Para la versión Pro, soporte directo en:', 'eu2673-withdrawal-button' ); ?><br>
        <a href="mailto:proyectos@adaptatuweb.com">proyectos@adaptatuweb.com</a><br>
        <a href="https://adaptatuweb.com/withdrawal-button-es/" target="_blank">adaptatuweb.com/withdrawal-button-es</a></p>
    </div>

</div>

<!-- Banner Pro -->
<div class="wb-help-pro-banner">
    <div>
        <h3>⭐ <?php esc_html_e( 'Withdrawal Button Pro — cumplimiento legal completo', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Pago único de €39. Sin cuotas. WooCommerce + PrestaShop incluidos.', 'eu2673-withdrawal-button' ); ?></p>
    </div>
    <a href="https://adaptatuweb.com/withdrawal-button-es/" target="_blank">
        <?php esc_html_e( 'Obtener Pro →', 'eu2673-withdrawal-button' ); ?>
    </a>
</div>

<div class="wb-help-grid" style="margin-top:8px;">

    <div class="wb-help-card wb-help-pro">
        <span class="wb-pro-badge">PRO</span>
        <h3>📄 <?php esc_html_e( 'PDF legal con acuse de recibo', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'El cliente recibe el acuse de recibo oficial en PDF adjunto al email, con todos los datos del pedido, referencia legal y código de verificación.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Beneficio:</strong> prueba documental inmediata para el cliente que acredita el ejercicio del desistimiento. Exigible ante cualquier reclamación.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

    <div class="wb-help-card wb-help-pro">
        <span class="wb-pro-badge">PRO</span>
        <h3>🔐 <?php esc_html_e( 'Hash SHA-256 de verificación', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Cada solicitud genera un código hash único basado en los datos del pedido, el email del cliente, la IP y la fecha exacta.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Beneficio:</strong> prueba criptográfica de integridad irrefutable. Si alguien alega que la solicitud fue manipulada o no existía, el hash lo desmiente ante cualquier tribunal.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

    <div class="wb-help-card wb-help-pro">
        <span class="wb-pro-badge">PRO</span>
        <h3>📋 <?php esc_html_e( 'Formulario Anexo B oficial', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Formulario normalizado exigido por la Directiva UE 2023/2673, prellenado con los datos del pedido y descargable en PDF por el cliente.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Beneficio:</strong> el Anexo B es el formulario oficial que la Directiva obliga a poner a disposición del consumidor. Tenerlo prellenado y en PDF es la implementación más completa posible.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

    <div class="wb-help-card wb-help-pro">
        <span class="wb-pro-badge">PRO</span>
        <h3>🌍 <?php esc_html_e( '13 idiomas incluidos', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'ES, EN, FR, DE, IT, PT, NL, PL, RO, SV, CA, EU, GL. Emails y PDFs en el idioma del cliente automáticamente.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Beneficio:</strong> si tu tienda vende en Francia, Alemania, Italia u otros países de la UE, el cliente recibe toda la documentación en su idioma. Requisito implícito de la Directiva.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

    <div class="wb-help-card wb-help-pro">
        <span class="wb-pro-badge">PRO</span>
        <h3>🔄 <?php esc_html_e( 'Automatización inversa', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Cuando el pedido cambia de estado en WooCommerce (reembolsado, cancelado, en proceso…), la solicitud de desistimiento se actualiza automáticamente.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Beneficio:</strong> el panel de solicitudes siempre refleja el estado real sin trabajo manual. Ahorra tiempo y evita inconsistencias.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

    <div class="wb-help-card wb-help-pro">
        <span class="wb-pro-badge">PRO</span>
        <h3>📊 <?php esc_html_e( 'Trazabilidad legal de emails', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Registro automático con fecha y hora exactas de cada email enviado: acuse de recibo, notificación al admin, actualizaciones de estado.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Beneficio:</strong> si un cliente alega que no recibió el acuse de recibo, tienes el registro exacto de cuándo y a qué dirección fue enviado. Prueba documental ante terceros.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

    <div class="wb-help-card wb-help-pro">
        <span class="wb-pro-badge">PRO</span>
        <h3>🛡️ <?php esc_html_e( 'Sistema antifraude', 'eu2673-withdrawal-button' ); ?></h3>
        <p><?php esc_html_e( 'Detecta patrones de abuso del derecho de desistimiento dentro de tu tienda: frecuencia excesiva por email o IP, solicitudes sistemáticas de último momento y blacklist manual de emails e IPs.', 'eu2673-withdrawal-button' ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Importante:</strong> el sistema nunca bloquea automáticamente — el derecho de desistimiento es irrenunciable por ley. Solo genera alertas con nivel de riesgo (Alto / Medio / Bajo) para que el administrador tome la decisión.', 'eu2673-withdrawal-button' ) ); ?></p>
        <p><?php echo wp_kses_post( __( '<strong>Beneficio:</strong> identifica clientes que abusan del sistema de forma sistemática antes de procesar el reembolso, ahorrando tiempo y dinero.', 'eu2673-withdrawal-button' ) ); ?></p>
    </div>

</div>
</div>
