<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>Salida de Materiales para Fabricación > Editar</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <Formulario
                                    :accion="'edit'"
                                    :salida_material="oSalidaMaterial"
                                ></Formulario>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Formulario from "./Formulario.vue";
export default {
    props: ["id"],
    components: {
        Formulario,
    },
    data() {
        return {
            permisos: localStorage.getItem("permisos"),
            fullscreenLoading: true,
            loadingWindow: Loading.service({
                fullscreen: this.fullscreenLoading,
            }),
            oSalidaMaterial: {
                id: 0,
                producto_id: "",
                cantidad: "",
                fecha_salida: "",
                estado: "",
                salida_detalles: [],
            },
        };
    },
    mounted() {
        this.loadingWindow.close();
        this.getSalidaMaterial();
    },
    methods: {
        getSalidaMaterial() {
            axios.get("/admin/salida_materials/" + this.id).then((response) => {
                this.oSalidaMaterial = response.data.salida_material;
            });
        },
    },
};
</script>

<style></style>
