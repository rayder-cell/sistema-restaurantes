<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // ÍNDICES DE RENDIMIENTO
        // ============================================================

        // Multi-tenant
        DB::statement('CREATE INDEX idx_usuario_restaurante       ON usuario(restaurante_id)');
        DB::statement('CREATE INDEX idx_producto_restaurante      ON producto(restaurante_id)');
        DB::statement('CREATE INDEX idx_mesa_restaurante          ON mesa(restaurante_id)');
        DB::statement('CREATE INDEX idx_pedido_restaurante        ON pedido(restaurante_id)');
        DB::statement('CREATE INDEX idx_pedido_estado             ON pedido(estado)');
        DB::statement('CREATE INDEX idx_comprobante_restaurante   ON comprobante(restaurante_id)');
        DB::statement('CREATE INDEX idx_notificacion_usuario      ON notificacion(usuario_id, leida)');
        DB::statement('CREATE INDEX idx_notificacion_restaurante  ON notificacion(restaurante_id, created_at)');
        DB::statement('CREATE INDEX idx_kardex_insumo             ON kardex(insumo_id, fecha)');
        DB::statement('CREATE INDEX idx_insumo_restaurante        ON insumo(restaurante_id)');
        DB::statement('CREATE INDEX idx_proveedor_restaurante     ON proveedor(restaurante_id)');
        DB::statement('CREATE INDEX idx_orden_compra_restaurante  ON orden_compra(restaurante_id)');
        DB::statement('CREATE INDEX idx_entrada_restaurante       ON entrada_compra(restaurante_id)');

        // Reservas
        DB::statement('CREATE INDEX idx_reserva_restaurante       ON reserva(restaurante_id, fecha)');
        DB::statement('CREATE INDEX idx_reserva_mesa              ON reserva(mesa_id)');
        DB::statement('CREATE INDEX idx_reserva_confirmador       ON reserva(confirmado_por)');
        DB::statement('CREATE INDEX idx_pago_reserva              ON pago_reserva(reserva_id)');

        // Búsquedas frecuentes
        DB::statement('CREATE INDEX idx_pedido_mesa               ON pedido(mesa_id)');
        DB::statement('CREATE INDEX idx_pedido_fecha              ON pedido(created_at)');
        DB::statement('CREATE INDEX idx_mesa_qr                   ON mesa(qr_token)');
        DB::statement('CREATE INDEX idx_apertura_caja_estado      ON apertura_caja(caja_id, estado)');
        DB::statement('CREATE INDEX idx_detalle_pedido_pedido     ON detalle_pedido(pedido_id)');
        DB::statement('CREATE INDEX idx_producto_categoria        ON producto(categoria_id)');


        // ============================================================
        // TRIGGERS
        // ============================================================

        // T1: Actualizar stock al registrar entrada de compra + kardex
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_actualizar_stock_entrada()
            RETURNS TRIGGER AS \$\$
            BEGIN
                UPDATE insumo
                SET stock_actual = stock_actual + NEW.cantidad_recibida
                WHERE id = NEW.insumo_id;

                INSERT INTO kardex (
                    restaurante_id, insumo_id, tipo_movimiento, referencia_doc,
                    cantidad_entrada, cantidad_salida, saldo_resultante, costo_unitario
                )
                SELECT
                    i.restaurante_id,
                    NEW.insumo_id,
                    'entrada',
                    (SELECT numero FROM entrada_compra WHERE id = NEW.entrada_id),
                    NEW.cantidad_recibida,
                    0,
                    i.stock_actual,
                    NEW.precio_unitario
                FROM insumo i WHERE i.id = NEW.insumo_id;

                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE TRIGGER trg_entrada_stock
            AFTER INSERT ON detalle_entrada
            FOR EACH ROW EXECUTE FUNCTION fn_actualizar_stock_entrada()
        ');

        // T2: Notificación al cambiar estado del pedido
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_notificar_cambio_estado()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF OLD.estado <> NEW.estado THEN
                    INSERT INTO notificacion (restaurante_id, usuario_id, pedido_id, tipo, mensaje)
                    SELECT
                        NEW.restaurante_id,
                        u.id,
                        NEW.id,
                        'cambio_estado_pedido',
                        'Pedido #' || NEW.id || ' cambió de ' || OLD.estado || ' a ' || NEW.estado
                    FROM usuario u
                    WHERE u.restaurante_id = NEW.restaurante_id
                      AND u.rol_id IN (SELECT id FROM rol WHERE nombre IN ('mesero','administrador'))
                      AND u.activo = TRUE;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE TRIGGER trg_cambio_estado_pedido
            AFTER UPDATE OF estado ON pedido
            FOR EACH ROW EXECUTE FUNCTION fn_notificar_cambio_estado()
        ');

        // T3: Alerta de stock mínimo
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_alerta_stock_minimo()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.stock_actual <= NEW.stock_minimo AND NEW.stock_actual < OLD.stock_actual THEN
                    INSERT INTO notificacion (restaurante_id, usuario_id, pedido_id, tipo, mensaje)
                    SELECT
                        NEW.restaurante_id,
                        u.id,
                        NULL,
                        'stock_minimo',
                        'Stock mínimo alcanzado: ' || NEW.nombre ||
                        ' (' || NEW.stock_actual || ' ' || NEW.unidad_medida || ' restantes)'
                    FROM usuario u
                    WHERE u.restaurante_id = NEW.restaurante_id
                      AND u.rol_id IN (SELECT id FROM rol WHERE nombre IN ('administrador','propietario'))
                      AND u.activo = TRUE;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE TRIGGER trg_stock_minimo
            AFTER UPDATE OF stock_actual ON insumo
            FOR EACH ROW EXECUTE FUNCTION fn_alerta_stock_minimo()
        ');

        // T4: Recalcular total del pedido
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_recalcular_total_pedido()
            RETURNS TRIGGER AS \$\$
            BEGIN
                UPDATE pedido
                SET total = (
                    SELECT COALESCE(SUM(subtotal), 0)
                    FROM detalle_pedido
                    WHERE pedido_id = COALESCE(NEW.pedido_id, OLD.pedido_id)
                ),
                updated_at = NOW()
                WHERE id = COALESCE(NEW.pedido_id, OLD.pedido_id);
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE TRIGGER trg_total_pedido
            AFTER INSERT OR UPDATE OR DELETE ON detalle_pedido
            FOR EACH ROW EXECUTE FUNCTION fn_recalcular_total_pedido()
        ');

        // T5: Incrementar correlativo de comprobante
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_incrementar_correlativo()
            RETURNS TRIGGER AS \$\$
            BEGIN
                UPDATE serie_comprobante
                SET correlativo_actual = correlativo_actual + 1
                WHERE id = NEW.serie_id;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE TRIGGER trg_correlativo_comprobante
            AFTER INSERT ON comprobante
            FOR EACH ROW EXECUTE FUNCTION fn_incrementar_correlativo()
        ');

        // T6: Sincronizar estado de mesa con la reserva
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_sincronizar_estado_mesa()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.estado = 'confirmada' AND NEW.mesa_id IS NOT NULL THEN
                    UPDATE mesa SET estado = 'reservada' WHERE id = NEW.mesa_id;
                END IF;
                IF NEW.estado IN ('cancelada', 'completada') AND NEW.mesa_id IS NOT NULL THEN
                    UPDATE mesa SET estado = 'libre' WHERE id = NEW.mesa_id;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE TRIGGER trg_reserva_mesa_estado
            AFTER INSERT OR UPDATE OF estado ON reserva
            FOR EACH ROW EXECUTE FUNCTION fn_sincronizar_estado_mesa()
        ');

        // T7: Notificación al crear reserva nueva
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_notificar_reserva_nueva()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    INSERT INTO notificacion (restaurante_id, usuario_id, pedido_id, tipo, mensaje)
                    SELECT
                        NEW.restaurante_id,
                        u.id,
                        NULL,
                        'reserva_nueva',
                        'Nueva reserva: ' || NEW.cliente_nombre ||
                        ' — ' || NEW.fecha || ' ' || NEW.hora ||
                        ' — ' || NEW.num_personas || ' personas'
                    FROM usuario u
                    WHERE u.restaurante_id = NEW.restaurante_id
                      AND u.rol_id IN (SELECT id FROM rol WHERE nombre IN ('administrador','propietario'))
                      AND u.activo = TRUE;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('
            CREATE TRIGGER trg_notificar_reserva_nueva
            AFTER INSERT ON reserva
            FOR EACH ROW EXECUTE FUNCTION fn_notificar_reserva_nueva()
        ');

        // T8: updated_at automático
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_set_updated_at()
            RETURNS TRIGGER AS \$\$
            BEGIN
                NEW.updated_at = NOW();
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");
        DB::statement('CREATE TRIGGER trg_upd_restaurante BEFORE UPDATE ON restaurante FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at()');
        DB::statement('CREATE TRIGGER trg_upd_usuario     BEFORE UPDATE ON usuario     FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at()');
        DB::statement('CREATE TRIGGER trg_upd_producto    BEFORE UPDATE ON producto    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at()');
        DB::statement('CREATE TRIGGER trg_upd_pedido      BEFORE UPDATE ON pedido      FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at()');
        DB::statement('CREATE TRIGGER trg_upd_reserva     BEFORE UPDATE ON reserva     FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at()');


        // ============================================================
        // VISTAS
        // ============================================================

        DB::statement("
            CREATE OR REPLACE VIEW v_pedidos_activos AS
            SELECT
                p.id            AS pedido_id,
                p.restaurante_id,
                r.nombre        AS restaurante,
                m.numero        AS mesa,
                p.origen,
                p.estado,
                p.total,
                p.created_at,
                COUNT(dp.id)    AS total_items
            FROM pedido p
            JOIN restaurante r         ON r.id = p.restaurante_id
            JOIN mesa m                ON m.id = p.mesa_id
            LEFT JOIN detalle_pedido dp ON dp.pedido_id = p.id
            WHERE p.estado NOT IN ('entregado','cancelado')
            GROUP BY p.id, p.restaurante_id, r.nombre, m.numero,
                     p.origen, p.estado, p.total, p.created_at
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_stock_critico AS
            SELECT
                i.restaurante_id,
                r.nombre            AS restaurante,
                i.nombre            AS insumo,
                i.unidad_medida,
                i.stock_actual,
                i.stock_minimo,
                (i.stock_actual - i.stock_minimo) AS diferencia
            FROM insumo i
            JOIN restaurante r ON r.id = i.restaurante_id
            WHERE i.stock_actual <= i.stock_minimo AND i.activo = TRUE
            ORDER BY diferencia ASC
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_ventas_diarias AS
            SELECT
                c.restaurante_id,
                r.nombre                AS restaurante,
                DATE(c.emitido_at)      AS fecha,
                COUNT(c.id)             AS total_comprobantes,
                SUM(c.total)            AS total_ventas,
                SUM(c.igv)              AS total_igv
            FROM comprobante c
            JOIN restaurante r ON r.id = c.restaurante_id
            WHERE c.anulado = FALSE
            GROUP BY c.restaurante_id, r.nombre, DATE(c.emitido_at)
            ORDER BY fecha DESC
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_kardex_detalle AS
            SELECT
                k.id,
                k.restaurante_id,
                r.nombre            AS restaurante,
                i.nombre            AS insumo,
                i.unidad_medida,
                k.tipo_movimiento,
                k.referencia_doc,
                k.cantidad_entrada,
                k.cantidad_salida,
                k.saldo_resultante,
                k.costo_unitario,
                k.costo_total,
                k.fecha
            FROM kardex k
            JOIN insumo i      ON i.id = k.insumo_id
            JOIN restaurante r ON r.id = k.restaurante_id
            ORDER BY k.fecha DESC
        ");

        DB::statement("
            CREATE OR REPLACE VIEW v_reservas_vigentes AS
            SELECT
                rv.id               AS reserva_id,
                rv.restaurante_id,
                r.nombre            AS restaurante,
                m.numero            AS mesa,
                m.capacidad         AS mesa_capacidad,
                rv.cliente_nombre,
                rv.cliente_telefono,
                rv.cliente_email,
                rv.num_personas,
                rv.fecha,
                rv.hora,
                rv.precio_base,
                rv.monto_adelanto,
                rv.estado,
                pr.metodo           AS pago_metodo,
                pr.monto_pagado     AS pago_monto,
                pr.estado           AS pago_estado,
                u.nombre            AS confirmado_por,
                rv.observacion,
                rv.created_at
            FROM reserva rv
            JOIN restaurante r        ON r.id  = rv.restaurante_id
            LEFT JOIN mesa m          ON m.id  = rv.mesa_id
            LEFT JOIN pago_reserva pr ON pr.reserva_id = rv.id
            LEFT JOIN usuario u       ON u.id  = rv.confirmado_por
            WHERE rv.estado IN ('pendiente','confirmada')
            ORDER BY rv.fecha, rv.hora
        ");
    }

    public function down(): void
    {
        // Vistas
        DB::statement('DROP VIEW IF EXISTS v_reservas_vigentes');
        DB::statement('DROP VIEW IF EXISTS v_kardex_detalle');
        DB::statement('DROP VIEW IF EXISTS v_ventas_diarias');
        DB::statement('DROP VIEW IF EXISTS v_stock_critico');
        DB::statement('DROP VIEW IF EXISTS v_pedidos_activos');

        // Triggers y funciones
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_reserva     ON reserva');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_pedido      ON pedido');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_producto     ON producto');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_usuario      ON usuario');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_restaurante  ON restaurante');
        DB::statement('DROP TRIGGER IF EXISTS trg_notificar_reserva_nueva  ON reserva');
        DB::statement('DROP TRIGGER IF EXISTS trg_reserva_mesa_estado      ON reserva');
        DB::statement('DROP TRIGGER IF EXISTS trg_correlativo_comprobante  ON comprobante');
        DB::statement('DROP TRIGGER IF EXISTS trg_total_pedido             ON detalle_pedido');
        DB::statement('DROP TRIGGER IF EXISTS trg_stock_minimo             ON insumo');
        DB::statement('DROP TRIGGER IF EXISTS trg_cambio_estado_pedido     ON pedido');
        DB::statement('DROP TRIGGER IF EXISTS trg_entrada_stock            ON detalle_entrada');

        DB::statement('DROP FUNCTION IF EXISTS fn_set_updated_at()');
        DB::statement('DROP FUNCTION IF EXISTS fn_notificar_reserva_nueva()');
        DB::statement('DROP FUNCTION IF EXISTS fn_sincronizar_estado_mesa()');
        DB::statement('DROP FUNCTION IF EXISTS fn_incrementar_correlativo()');
        DB::statement('DROP FUNCTION IF EXISTS fn_recalcular_total_pedido()');
        DB::statement('DROP FUNCTION IF EXISTS fn_alerta_stock_minimo()');
        DB::statement('DROP FUNCTION IF EXISTS fn_notificar_cambio_estado()');
        DB::statement('DROP FUNCTION IF EXISTS fn_actualizar_stock_entrada()');
    }
};
