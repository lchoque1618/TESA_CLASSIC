import Vue from "vue";
import Router from "vue-router";

Vue.use(Router);

export default new Router({
    routes: [
        // INICIO
        {
            path: "/",
            name: "inicio",
            component: require("./components/Inicio.vue").default,
        },

        // LOGIN
        {
            path: "/login",
            name: "login",
            component: require("./Auth.vue").default,
        },

        // Usuarios
        {
            path: "/usuarios/perfil/:id",
            name: "usuarios.perfil",
            component: require("./components/modulos/usuarios/perfil.vue")
                .default,
            props: true,
        },
        {
            path: "/usuarios",
            name: "usuarios.index",
            component: require("./components/modulos/usuarios/index.vue")
                .default,
        },

        // proveedores
        {
            path: "/proveedors",
            name: "proveedors.index",
            component: require("./components/modulos/proveedors/index.vue")
                .default,
        },

        // productos
        {
            path: "/productos",
            name: "productos.index",
            component: require("./components/modulos/productos/index.vue")
                .default,
        },

        // materials
        {
            path: "/materials",
            name: "materials.index",
            component: require("./components/modulos/materials/index.vue")
                .default,
        },

        // cuantificador_produccions
        {
            path: "/cuantificador_produccions",
            name: "cuantificador_produccions.create",
            component: require("./components/modulos/cuantificador_produccions/create.vue")
                .default,
        },

        // ingreso_materials
        {
            path: "/ingreso_materials",
            name: "ingreso_materials.index",
            component:
                require("./components/modulos/ingreso_materials/index.vue")
                    .default,
        },

        // salida_materials
        {
            path: "/salida_materials",
            name: "salida_materials.index",
            component:
                require("./components/modulos/salida_materials/index.vue")
                    .default,
        },
        {
            path: "/salida_materials/create",
            name: "salida_materials.create",
            component:
                require("./components/modulos/salida_materials/create.vue")
                    .default,
            props: true,
        },
        {
            path: "/salida_materials/:id",
            name: "salida_materials.edit",
            component: require("./components/modulos/salida_materials/edit.vue")
                .default,
            props: true,
        },
        {
            path: "/salida_materials/show/:id",
            name: "salida_materials.show",
            component: require("./components/modulos/salida_materials/show.vue")
                .default,
            props: true,
        },

        // fabricacion
        {
            path: "/fabricacion",
            name: "fabricacion.index",
            component: require("./components/modulos/fabricacion/index.vue")
                .default,
        },

        // categorias
        {
            path: "/categorias",
            name: "categorias.index",
            component: require("./components/modulos/categorias/index.vue")
                .default,
        },

        // tipo ingresos
        {
            path: "/tipo_ingresos",
            name: "tipo_ingresos.index",
            component: require("./components/modulos/tipo_ingresos/index.vue")
                .default,
        },

        // ingreso productos
        {
            path: "/ingreso_productos",
            name: "ingreso_productos.index",
            component:
                require("./components/modulos/ingreso_productos/index.vue")
                    .default,
        },

        // tipo salidas
        {
            path: "/tipo_salidas",
            name: "tipo_salidas.index",
            component: require("./components/modulos/tipo_salidas/index.vue")
                .default,
        },

        // salida productos
        {
            path: "/salida_productos",
            name: "salida_productos.index",
            component:
                require("./components/modulos/salida_productos/index.vue")
                    .default,
        },

        // Clientes
        {
            path: "/clientes",
            name: "clientes.index",
            component: require("./components/modulos/clientes/index.vue")
                .default,
        },

        // Ventas
        {
            path: "/ventas",
            name: "ventas.index",
            component: require("./components/modulos/ventas/index.vue").default,
        },

        {
            path: "/ventas/create",
            name: "ventas.create",
            component: require("./components/modulos/ventas/create.vue")
                .default,
        },

        {
            path: "/ventas/:id",
            name: "ventas.edit",
            props: true,
            component: require("./components/modulos/ventas/edit.vue").default,
        },

        {
            path: "/ventas/ticket_orden/:id",
            name: "ventas.ticket",
            props: true,
            component: require("./components/modulos/ventas/Ticket.vue")
                .default,
        },

        // Configuración
        {
            path: "/configuracion",
            name: "configuracion",
            component: require("./components/modulos/configuracion/index.vue")
                .default,
            props: true,
        },

        // Analisis fabricacion
        {
            path: "/analisis_fabricacion",
            name: "analisis_fabricacion.index",
            component:
                require("./components/modulos/analisis_fabricacion/index.vue")
                    .default,
        },
        // Analisis inventarios
        {
            path: "/analisis_inventarios_materiales",
            name: "analisis_inventarios_materiales.index",
            component:
                require("./components/modulos/analisis_inventarios_materiales/index.vue")
                    .default,
        },
        {
            path: "/analisis_inventarios",
            name: "analisis_inventarios.index",
            component:
                require("./components/modulos/analisis_inventarios/index.vue")
                    .default,
        },

        // Analisis proveedors
        {
            path: "/analisis_proveedors",
            name: "analisis_proveedors.index",
            component:
                require("./components/modulos/analisis_proveedors/index.vue")
                    .default,
        },

        // Analisis ventas
        {
            path: "/analisis_ventas",
            name: "analisis_ventas.index",
            component: require("./components/modulos/analisis_ventas/index.vue")
                .default,
        },

        // Analisis clientes
        {
            path: "/analisis_clientes",
            name: "analisis_clientes.index",
            component:
                require("./components/modulos/analisis_clientes/index.vue")
                    .default,
        },

        // Reportes
        {
            path: "/reportes/usuarios",
            name: "reportes.usuarios",
            component: require("./components/modulos/reportes/usuarios.vue")
                .default,
            props: true,
        },
        {
            path: "/reportes/kardex",
            name: "reportes.kardex",
            component: require("./components/modulos/reportes/kardex.vue")
                .default,
            props: true,
        },
        {
            path: "/reportes/kardex_materials",
            name: "reportes.kardex_materials",
            component:
                require("./components/modulos/reportes/kardex_materials.vue")
                    .default,
            props: true,
        },
        {
            path: "/reportes/ventas",
            name: "reportes.ventas",
            component: require("./components/modulos/reportes/ventas.vue")
                .default,
            props: true,
        },
        {
            path: "/reportes/stock_productos",
            name: "reportes.stock_productos",
            component:
                require("./components/modulos/reportes/stock_productos.vue")
                    .default,
            props: true,
        },
        {
            path: "/reportes/stock_materials",
            name: "reportes.stock_materials",
            component:
                require("./components/modulos/reportes/stock_materials.vue")
                    .default,
            props: true,
        },
        {
            path: "/reportes/historial_acciones",
            name: "reportes.historial_acciones",
            component:
                require("./components/modulos/reportes/historial_acciones.vue")
                    .default,
            props: true,
        },
        {
            path: "/reportes/grafico_ingresos",
            name: "reportes.grafico_ingresos",
            component:
                require("./components/modulos/reportes/grafico_ingresos.vue")
                    .default,
            props: true,
        },
        {
            path: "/reportes/grafico_orden",
            name: "reportes.grafico_orden",
            component:
                require("./components/modulos/reportes/grafico_orden.vue")
                    .default,
            props: true,
        },

        // PÁGINA NO ENCONTRADA
        {
            path: "*",
            component: require("./components/modulos/errors/404.vue").default,
        },
    ],
    mode: "history",
    linkActiveClass: "active",
});
