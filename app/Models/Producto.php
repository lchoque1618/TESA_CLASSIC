<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        "codigo_almacen", "codigo_producto", "nombre", "descripcion", "color", "unidad_medida", "precio",
        "stock_min", "stock_actual", "imagen", "categoria_id", "fecha_registro",
    ];

    protected $appends = ["url_imagen"];

    public function getUrlImagenAttribute()
    {
        if ($this->imagen && trim($this->imagen)) {
            return asset("imgs/productos/" . $this->imagen);
        }
        return asset("imgs/productos/default.png");
    }

    public function fecha_stocks()
    {
        return $this->hasMany(FechaStock::class, 'producto_id');
    }

    // FUNCIONES PARA INCREMETAR Y DECREMENTAR EL STOCK
    public static function incrementarStock($producto, $cantidad)
    {
        $producto->stock_actual = (float)$producto->stock_actual + $cantidad;
        $producto->save();

        $fecha_actual = date("Y-m-d");
        self::registraFechaStock($producto, $fecha_actual);
        return true;
    }
    public static function decrementarStock($producto, $cantidad)
    {
        $producto->stock_actual = (float)$producto->stock_actual - $cantidad;
        $producto->save();

        $fecha_actual = date("Y-m-d");
        self::registraFechaStock($producto, $fecha_actual);
        return true;
    }

    public static function registraFechaStock($producto, $fecha)
    {
        $existe = FechaStock::where("producto_id", $producto->id)
            ->where("fecha", $fecha)
            ->get()
            ->first();
        if ($existe) {
            $existe->stock = $producto->stock_actual;
            $existe->save();
        } else {
            $producto->fecha_stocks()->create([
                "stock" => $producto->stock_actual,
                "fecha" => $fecha
            ]);
        }
        return true;
    }

    public function kardex_productos()
    {
        return $this->hasMany(KardexProducto::class, 'producto_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function ingreso_productos()
    {
        return $this->hasMany(IngresoProducto::class, 'producto_id');
    }
    public function salida_productos()
    {
        return $this->hasMany(SalidaProducto::class, 'producto_id');
    }
}
