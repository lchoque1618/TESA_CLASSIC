<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class KardexProducto extends Model
{
    use HasFactory;

    protected $fillable = [
        "tipo_registro", "registro_id",
        "producto_id", "detalle", "precio", "tipo_is",
        "cantidad_ingreso", "cantidad_salida", "cantidad_saldo", "cu",
        "monto_ingreso", "monto_salida", "monto_saldo", "fecha",
    ];


    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // REGISTRAR INGRESO
    public static function registroIngreso($tipo_registro, $registro_id = 0, Producto $producto, $cantidad, $precio, $detalle = "")
    {
        //buscar el ultimo registro y usar sus valores
        $ultimo = KardexProducto::where('producto_id', $producto->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->last();
        $monto = (float)$cantidad * (float)$precio;
        if ($ultimo) {
            if (!$detalle || $detalle == "") {
                $detalle = "INGRESO DE PRODUCTO";
            }
            KardexProducto::create([
                'tipo_registro' => $tipo_registro, //INGRESO, EGRESO, VENTA, COMPRA,etc...
                'registro_id' => $registro_id,
                'producto_id' => $producto->id,
                'detalle' => $detalle,
                'precio' => $precio,
                'tipo_is' => 'INGRESO',
                'cantidad_ingreso' => $cantidad,
                'cantidad_saldo' => (float)$ultimo->cantidad_saldo + (float)$cantidad,
                'cu' => $producto->precio,
                'monto_ingreso' => $monto,
                'monto_saldo' => (float)$ultimo->monto_saldo + $monto,
                'fecha' => date('Y-m-d'),
            ]);
        } else {
            $detalle = "VALOR INICIAL";
            KardexProducto::create([
                'tipo_registro' => $tipo_registro, //INGRESO, EGRESO, VENTA,etc...
                'registro_id' => $registro_id,
                'producto_id' => $producto->id,
                'detalle' => $detalle,
                'precio' => $precio,
                'tipo_is' => 'INGRESO',
                'cantidad_ingreso' => $cantidad,
                'cantidad_saldo' => (float)$cantidad,
                'cu' => $producto->precio,
                'monto_ingreso' => $monto,
                'monto_saldo' =>  $monto,
                'fecha' => date('Y-m-d'),
            ]);
        }

        // INCREMENTAR STOCK
        Producto::incrementarStock($producto, $cantidad);

        return true;
    }

    // REGISTRAR EGRESO
    public static function registroEgreso($tipo_registro, $registro_id = 0, Producto $producto, $cantidad, $precio, $detalle = "")
    {
        //buscar el ultimo registro y usar sus valores
        $ultimo = KardexProducto::where('producto_id', $producto->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->last();
        $monto = (float)$cantidad * (float)$precio;

        if (!$detalle || $detalle == "") {
            $detalle = "SALIDA DE PRODUCTO";
        }

        KardexProducto::create([
            'tipo_registro' => $tipo_registro,
            'registro_id' => $registro_id,
            'producto_id' => $producto->id,
            'detalle' => $detalle,
            'precio' => $precio,
            'tipo_is' => 'EGRESO',
            'cantidad_salida' => $cantidad,
            'cantidad_saldo' => (float)$ultimo->cantidad_saldo - (float)$cantidad,
            'cu' => $producto->precio,
            'monto_salida' => $monto,
            'monto_saldo' => (float)$ultimo->monto_saldo - $monto,
            'fecha' => date('Y-m-d'),
        ]);

        Producto::decrementarStock($producto, $cantidad);

        return true;
    }

    // ACTUALIZA REGISTROS KARDEX
    // FUNCIÓN QUE ACTUALIZA LOS REGISTROS DEL KARDEX DE UN LUGAR
    // SOLO ACTUALIZARA LOS REGISTROS POSTERIORES AL REGISTRO ACTUALIZADO
    public static function actualizaRegistrosKardex($id, $producto_id)
    {
        $siguientes = KardexProducto::where("producto_id", $producto_id)
            ->where("id", ">=", $id)
            ->get();

        foreach ($siguientes as $item) {
            $anterior = KardexProducto::where("producto_id", $producto_id)
                ->where("id", "<", $item->id)->get()
                ->last();

            $datos_actualizacion = [
                "precio" => 0,
                "cantidad_ingreso" => NULL,
                "cantidad_salida" => NULL,
                "cantidad_saldo" => 0,
                "cu" => 0,
                "monto_ingreso" => NULL,
                "monto_salida" => NULL,
                "monto_saldo" => 0,
            ];

            switch ($item->tipo_registro) {
                case 'INGRESO':
                    $ingreso_producto = IngresoProducto::find($item->registro_id);
                    $monto = (float)$ingreso_producto->cantidad * (float)$ingreso_producto->producto->precio;
                    if ($anterior) {
                        $datos_actualizacion["precio"] = $ingreso_producto->producto->precio;
                        $datos_actualizacion["cantidad_ingreso"] =  $ingreso_producto->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$anterior->cantidad_saldo + (float)$ingreso_producto->cantidad;
                        $datos_actualizacion["cu"] = $ingreso_producto->producto->precio;
                        $datos_actualizacion["monto_ingreso"] = $monto;
                        $datos_actualizacion["monto_saldo"] = (float)$anterior->monto_saldo + $monto;
                    } else {
                        $datos_actualizacion["precio"] = $ingreso_producto->producto->precio;
                        $datos_actualizacion["cantidad_ingreso"] =  $ingreso_producto->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$ingreso_producto->cantidad;
                        $datos_actualizacion["cu"] = $ingreso_producto->producto->precio;
                        $datos_actualizacion["monto_ingreso"] = $monto;
                        $datos_actualizacion["monto_saldo"] = $monto;
                    }
                    break;
                case 'SALIDA':
                    $salida_producto = SalidaProducto::find($item->registro_id);
                    $monto = (float)$salida_producto->cantidad * (float)$salida_producto->producto->precio;

                    if ($anterior) {
                        $datos_actualizacion["precio"] = $salida_producto->producto->precio;
                        $datos_actualizacion["cantidad_salida"] =  $salida_producto->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$anterior->cantidad_saldo - (float)$salida_producto->cantidad;
                        $datos_actualizacion["cu"] = $salida_producto->producto->precio;
                        $datos_actualizacion["monto_salida"] = $monto;
                        $datos_actualizacion["monto_saldo"] =  (float)$anterior->monto_saldo - $monto;
                    } else {
                        $datos_actualizacion["precio"] = $salida_producto->producto->precio;
                        $datos_actualizacion["cantidad_salida"] =  $salida_producto->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$salida_producto->cantidad * (-1);
                        $datos_actualizacion["cu"] = $salida_producto->producto->precio;
                        $datos_actualizacion["monto_salida"] = $monto;
                        $datos_actualizacion["monto_saldo"] = $monto * (-1);
                    }

                    break;
                case 'VENTA':
                    $detalle_orden = DetalleVenta::find($item->registro_id);
                    $monto = (float)$detalle_orden->cantidad * (float)$detalle_orden->precio;
                    if ($anterior) {
                        $datos_actualizacion["precio"] = $detalle_orden->precio;
                        $datos_actualizacion["cantidad_salida"] =  $detalle_orden->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$anterior->cantidad_saldo - (float)$detalle_orden->cantidad;
                        $datos_actualizacion["cu"] = $detalle_orden->precio;
                        $datos_actualizacion["monto_salida"] = $monto;
                        $datos_actualizacion["monto_saldo"] =  (float)$anterior->monto_saldo - $monto;
                    } else {
                        $datos_actualizacion["precio"] = $detalle_orden->precio;
                        $datos_actualizacion["cantidad_salida"] =  $detalle_orden->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$detalle_orden->cantidad * (-1);
                        $datos_actualizacion["cu"] = $detalle_orden->precio;
                        $datos_actualizacion["monto_salida"] = $monto;
                        $datos_actualizacion["monto_saldo"] = $monto * (-1);
                    }
                    break;
                case 'DEVOLUCION':
                    $devolucion_detalle = DevolucionDetalle::find($item->registro_id);
                    $monto = (float)$devolucion_detalle->cantidad * (float)$devolucion_detalle->detalle_orden->precio;
                    if ($anterior) {
                        $datos_actualizacion["precio"] = $devolucion_detalle->detalle_orden->precio;
                        $datos_actualizacion["cantidad_ingreso"] =  $devolucion_detalle->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$anterior->cantidad_saldo + (float)$devolucion_detalle->cantidad;
                        $datos_actualizacion["cu"] = $devolucion_detalle->detalle_orden->precio;
                        $datos_actualizacion["monto_ingreso"] = $monto;
                        $datos_actualizacion["monto_saldo"] =  (float)$anterior->monto_saldo + $monto;
                    } else {
                        $datos_actualizacion["precio"] = $devolucion_detalle->detalle_orden->precio;
                        $datos_actualizacion["cantidad_ingreso"] =  $devolucion_detalle->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$devolucion_detalle->cantidad;
                        $datos_actualizacion["cu"] = $devolucion_detalle->detalle_orden->precio;
                        $datos_actualizacion["monto_ingreso"] = $monto;
                        $datos_actualizacion["monto_saldo"] = $monto;
                    }
                    break;
            }

            $item->update($datos_actualizacion);
        }
    }
}
