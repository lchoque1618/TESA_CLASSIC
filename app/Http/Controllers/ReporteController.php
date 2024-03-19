<?php

namespace App\Http\Controllers;

use App\Models\DetalleOrden;
use App\Models\HistorialAccion;
use App\Models\KardexProducto;
use App\Models\Material;
use App\Models\MovimientoMaterial;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReporteController extends Controller
{
    public function usuarios(Request $request)
    {
        $filtro =  $request->filtro;
        $usuarios = User::where('id', '!=', 1)->orderBy("paterno", "ASC")->get();

        if ($filtro == 'Tipo de usuario') {
            $request->validate([
                'tipo' => 'required',
            ]);
            $usuarios = User::where('id', '!=', 1)->where('tipo', $request->tipo)->orderBy("paterno", "ASC")->get();
        }

        $pdf = PDF::loadView('reportes.usuarios', compact('usuarios'))->setPaper('legal', 'landscape');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->download('Usuarios.pdf');
    }

    public function kardex(Request $request)
    {
        $filtro = $request->filtro;
        $producto_id = $request->producto_id;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;

        if ($request->filtro == 'Producto') {
            $request->validate([
                'producto_id' => 'required',
            ]);
        }

        if ($request->filtro == 'Rango de fechas') {
            $request->validate([
                'fecha_ini' => 'required|date',
                'fecha_fin' => 'required|date',
            ]);
        }

        $productos = Producto::all();
        if ($filtro != 'todos') {
            if ($filtro == 'Producto') {
                $productos = Producto::where("id", $producto_id)->get();
            }
        }

        $array_kardex = [];
        $array_saldo_anterior = [];
        foreach ($productos as $registro) {
            $kardex = KardexProducto::where('producto_id', $registro->id)->get();
            $array_saldo_anterior[$registro->id] = [
                'sw' => false,
                'saldo_anterior' => []
            ];
            if ($filtro == 'Rango de fechas') {
                $kardex = KardexProducto::where('producto_id', $registro->id)
                    ->whereBetween('fecha', [$fecha_ini, $fecha_fin])->get();
                // buscar saldo anterior si existe
                $saldo_anterior = KardexProducto::where('producto_id', $registro->id)
                    ->where('fecha', '<', $fecha_ini)
                    ->orderBy('created_at', 'asc')->get()->last();
                if ($saldo_anterior) {
                    $cantidad_saldo = $saldo_anterior->cantidad_saldo;
                    $monto_saldo = $saldo_anterior->monto_saldo;
                    $array_saldo_anterior[$registro->id] = [
                        'sw' => true,
                        'saldo_anterior' => [
                            'cantidad_saldo' => $cantidad_saldo,
                            'monto_saldo' => $monto_saldo,
                        ]
                    ];
                }
            }
            $array_kardex[$registro->id] = $kardex;
        }

        $pdf = PDF::loadView('reportes.kardex', compact('productos', 'array_kardex', 'array_saldo_anterior'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('kardex.pdf');
    }
    public function kardex_materials(Request $request)
    {
        $filtro = $request->filtro;
        $material_id = $request->material_id;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;

        if ($request->filtro == 'Material') {
            $request->validate([
                'material_id' => 'required',
            ]);
        }

        if ($request->filtro == 'Rango de fechas') {
            $request->validate([
                'fecha_ini' => 'required|date',
                'fecha_fin' => 'required|date',
            ]);
        }

        $materials = Material::all();
        if ($filtro != 'todos') {
            if ($filtro == 'Material') {
                $materials = Material::where("id", $material_id)->get();
            }
        }

        $array_kardex = [];
        $array_saldo_anterior = [];
        foreach ($materials as $registro) {
            $kardex = MovimientoMaterial::where('material_id', $registro->id)->get();
            $array_saldo_anterior[$registro->id] = [
                'sw' => false,
                'saldo_anterior' => []
            ];
            if ($filtro == 'Rango de fechas') {
                $kardex = MovimientoMaterial::where('material_id', $registro->id)
                    ->whereBetween('fecha', [$fecha_ini, $fecha_fin])->get();
                // buscar saldo anterior si existe
                $saldo_anterior = MovimientoMaterial::where('material_id', $registro->id)
                    ->where('fecha', '<', $fecha_ini)
                    ->orderBy('created_at', 'asc')->get()->last();
                if ($saldo_anterior) {
                    $cantidad_saldo = $saldo_anterior->cantidad_saldo;
                    $array_saldo_anterior[$registro->id] = [
                        'sw' => true,
                        'saldo_anterior' => [
                            'cantidad_saldo' => $cantidad_saldo,
                        ]
                    ];
                }
            }
            $array_kardex[$registro->id] = $kardex;
        }

        $pdf = PDF::loadView('reportes.kardex_materials', compact('materials', 'array_kardex', 'array_saldo_anterior'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('kardex_materials.pdf');
    }

    public function ventas(Request $request)
    {
        $filtro = $request->filtro;
        $producto_id = $request->producto_id;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;

        if ($filtro == 'Producto') {
            $request->validate([
                'producto_id' => 'required',
            ]);
        }
        if ($filtro == 'Rango de fechas') {
            $request->validate([
                'fecha_ini' => 'required|date',
                'fecha_fin' => 'required|date',
            ]);
        }

        $ventas = Venta::all();
        if ($filtro != 'todos') {
            if ($filtro == 'Producto') {
                $ventas = Venta::select("ventas.*")
                    ->join("detalle_ventas", "detalle_ventas.venta_id", "=", "ventas.id")
                    ->where("detalle_ventas.producto_id", $producto_id)
                    ->get();
            }
            if ($filtro == 'Rango de fechas') {
                $ventas = Venta::whereBetween("fecha_registro", [$fecha_ini, $fecha_fin])->get();
            }
        }
        $pdf = PDF::loadView('reportes.ventas', compact('ventas'))->setPaper('legal', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->download('ventas.pdf');
    }

    public function stock_productos(Request $request)
    {
        $filtro =  $request->filtro;
        $producto =  $request->producto;

        if ($filtro != 'TODOS') {
            $request->validate(['producto' => 'required']);
        }

        $registros = Producto::orderBy("productos.nombre")->get();
        if ($filtro != 'TODOS') {
            $registros = Producto::where("id", $producto)->orderBy("productos.nombre")->get();
        }


        $pdf = PDF::loadView('reportes.stock_productos', compact('registros'))->setPaper('legal', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));
        return $pdf->download('stock_productos.pdf');
    }

    public function stock_materials(Request $request)
    {
        $filtro =  $request->filtro;
        $material =  $request->material;

        if ($filtro != 'TODOS') {
            $request->validate(['material' => 'required']);
        }

        $registros = Material::orderBy("materials.nombre")->get();
        if ($filtro != 'TODOS') {
            $registros = Material::where("id", $material)->orderBy("materials.nombre")->get();
        }


        $pdf = PDF::loadView('reportes.stock_materials', compact('registros'))->setPaper('legal', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));
        return $pdf->download('stock_materials.pdf');
    }

    public function historial_accion(Request $request)
    {
        $historial_accions = HistorialAccion::orderBy("created_at", "desc")->get();

        if (isset($request->fecha_ini) && isset($request->fecha_fin)) {
            $historial_accions = HistorialAccion::with("user")->whereBetween("fecha", [$request->fecha_ini, $request->fecha_fin])->orderBy("created_at", "desc")->get();
        }

        $pdf = PDF::loadView('reportes.historial_accion', compact('historial_accions'))->setPaper('legal', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));
        return $pdf->download('historial_accions.pdf');
    }


    public function grafico_ingresos(Request $request)
    {
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $filtro =  $request->filtro;
        $producto_id =  $request->producto_id;

        if ($filtro == 'Producto') {
            $productos = Producto::select("productos.*")
                ->where("id", $producto_id)
                ->get();
        } else {
            $productos = Producto::select("productos.*")
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('detalle_ventas')
                        ->whereRaw('productos.id = detalle_ventas.producto_id');
                })
                ->get();
        }
        $data = [];
        foreach ($productos as $producto) {
            $cantidad = 0;
            if ($filtro == 'Rango de fechas') {
                $cantidad = DetalleOrden::select("detalle_ventas")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("ventas.estado", "CANCELADO")
                    ->where("detalle_ventas.producto_id", $producto->id)
                    ->whereBetween("fecha_registro", [$fecha_ini, $fecha_fin])
                    ->sum("detalle_ventas.subtotal");
            } else {
                $cantidad = DetalleOrden::where("producto_id", $producto->id)
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("ventas.estado", "CANCELADO")
                    ->sum("subtotal");
            }
            $data[] = [$producto->nombre, $cantidad ? (float)$cantidad : 0];
        }

        $fecha = date("d/m/Y");
        return response()->JSON([
            "sw" => true,
            "datos" => $data,
            "fecha" => $fecha
        ]);
    }

    public function grafico_orden(Request $request)
    {
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $filtro =  $request->filtro;
        $producto_id =  $request->producto_id;

        if ($filtro == 'Producto') {
            $productos = Producto::select("productos.*")
                ->where("id", $producto_id)
                ->get();
        } else {
            $productos = Producto::select("productos.*")
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('detalle_ventas')
                        ->whereRaw('productos.id = detalle_ventas.producto_id');
                })
                ->get();
        }
        $data = [];
        foreach ($productos as $producto) {
            $cantidad = 0;
            if ($filtro == 'Rango de fechas') {
                $cantidad = count(DetalleOrden::select("detalle_ventas")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("detalle_ventas.producto_id", $producto->id)
                    ->whereBetween("fecha_registro", [$fecha_ini, $fecha_fin])
                    ->get());
            } else {
                $cantidad = count(DetalleOrden::where("producto_id", $producto->id)->get());
            }
            $data[] = [$producto->nombre, $cantidad ? (float)$cantidad : 0];
        }

        $fecha = date("d/m/Y");
        return response()->JSON([
            "sw" => true,
            "datos" => $data,
            "fecha" => $fecha
        ]);
    }
}
