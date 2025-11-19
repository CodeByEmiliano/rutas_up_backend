<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vehículos</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Iconos (Bootstrap Icons funcionan bien con Tailwind) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb', // blue-600
                        primaryHover: '#1d4ed8', // blue-700
                    }
                }
            }
        }
    </script>

    <style>
        /* Animación suave para el modal */
        .modal-transition {
            transition: opacity 0.3s ease-out;
        }
        /* Ocultar scrollbar cuando el modal está abierto */
        body.modal-open {
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800">

    <!-- Spinner de carga -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-80 z-[60] hidden items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-primary"></div>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Tarjeta Principal -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
            
            <!-- Encabezado -->
            <div class="bg-primary px-6 py-4 flex flex-col md:flex-row justify-between items-center">
                <h5 class="text-white text-lg font-bold flex items-center mb-2 md:mb-0">
                    <i class="bi bi-car-front-fill mr-3 text-xl"></i>
                    Listado de Vehículos
                </h5>
                <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-sm font-medium backdrop-blur-sm" id="contadorRegistros">
                    0 registros
                </span>
            </div>

            <!-- Barra de Herramientas -->
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
                    <!-- Buscador -->
                    <div class="relative w-full md:w-1/2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-search"></i>
                        </div>
                        <input type="text" id="inputBuscador" 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow" 
                            placeholder="Buscar por placa o serie...">
                    </div>
                    
                    <!-- Botón Nuevo -->
                    <div class="w-full md:w-auto">
                        <button onclick="abrirModalCrear()" 
                            class="w-full md:w-auto bg-primary hover:bg-primaryHover text-white font-medium py-2 px-6 rounded-full shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-center gap-2">
                            <i class="bi bi-plus-lg text-lg"></i>
                            Nuevo Vehículo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                            <th class="py-3 px-6 text-left font-bold">ID</th>
                            <th class="py-3 px-6 text-left font-bold">Serie</th>
                            <th class="py-3 px-6 text-left font-bold">Placa</th>
                            <th class="py-3 px-6 text-left font-bold">Económico</th>
                            <th class="py-3 px-6 text-left font-bold">Marca</th>
                            <th class="py-3 px-6 text-left font-bold">Modelo</th>
                            <th class="py-3 px-6 text-center font-bold">Año</th>
                            <th class="py-3 px-6 text-center font-bold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody" class="text-gray-600 text-sm font-light">
                        <!-- Las filas se generan con JS aquí -->
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación simple (Visual) -->
            <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <span class="text-sm text-gray-500">Mostrando resultados</span>
                <div class="inline-flex mt-2 xs:mt-0 gap-1">
                    <!-- Botones visuales, funcionalidad dependerá de API paginada -->
                    <button class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l hover:bg-gray-100" disabled>
                        Anterior
                    </button>
                    <button class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r hover:bg-gray-100" disabled>
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL (Tailwind Custom Implementation) -->
    <!-- Capa de fondo (Overlay) -->
    <div id="modalVehiculo" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Fondo oscuro -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="cerrarModal()"></div>

        <!-- Contenedor del Modal -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Panel del Modal -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    
                    <!-- Header del Modal -->
                    <div class="bg-primary px-4 py-3 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg font-semibold leading-6 text-white flex items-center gap-2" id="modalTitulo">
                            <i class="bi bi-car-front"></i> Nuevo Vehículo
                        </h3>
                        <button type="button" onclick="cerrarModal()" class="text-white hover:text-gray-200 transition-colors focus:outline-none">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <!-- Body del Modal -->
                    <div class="px-4 py-5 sm:p-6">
                        <form id="formVehiculo">
                            <input type="hidden" id="vehiculo_id">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Serie -->
                                <div>
                                    <label for="num_serie" class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
                                    <input type="text" id="num_serie" required pattern="[A-Z0-9\-]+" 
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm"
                                        placeholder="Ej: ABC-12345">
                                </div>

                                <!-- Placa -->
                                <div>
                                    <label for="placa" class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                                    <input type="text" id="placa" required 
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm"
                                        placeholder="Ej: XYZ-999">
                                </div>

                                <!-- Económico -->
                                <div>
                                    <label for="num_economico" class="block text-sm font-medium text-gray-700 mb-1">Num. Económico</label>
                                    <input type="text" id="num_economico" required 
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm">
                                </div>

                                <!-- Año -->
                                <div>
                                    <label for="anio" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                                    <input type="number" id="anio" min="1900" max="2099" required 
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm">
                                </div>

                                <!-- Marca -->
                                <div>
                                    <label for="marca_id" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                    <select id="marca_id" required onchange="cargarModelos(this.value)"
                                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm">
                                        <option value="">Seleccione Marca...</option>
                                    </select>
                                </div>

                                <!-- Modelo -->
                                <div>
                                    <label for="modelo_id" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                                    <select id="modelo_id" required disabled
                                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm disabled:bg-gray-100 disabled:text-gray-400">
                                        <option value="">Seleccione una marca primero</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Footer del Modal -->
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                        <button type="button" onclick="guardarVehiculo()" 
                            class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primaryHover sm:w-auto transition-colors">
                            Guardar
                        </button>
                        <button type="button" onclick="cerrarModal()" 
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==========================================
        //  CONFIGURACIÓN DE APIs
        // ==========================================
        const BASE_URL = "https://rutas-up-backend.onrender.com";

        const API_URLS = {
            GET_VEHICULOS:   `${BASE_URL}/api/vehiculos`, 
            POST_VEHICULO:   `${BASE_URL}/api/vehiculos/store`, 
            PUT_VEHICULO:    `${BASE_URL}/api/vehiculos/update`, // Se le concatenará /ID
            DELETE_VEHICULO: `${BASE_URL}/api/vehiculos/delete`, // Se le concatenará /ID
            
            GET_MARCAS:      `${BASE_URL}/api/marcas`, 
            
            GET_MODELOS:     `${BASE_URL}/api/modelos/marca` // Se le concatenará /ID_MARCA
        };

        // ==========================================
        //  ESTADO GLOBAL Y UTILIDADES
        // ==========================================
        let listaVehiculos = []; 
        const loading = document.getElementById('loadingOverlay');
        const modal = document.getElementById('modalVehiculo');

        document.addEventListener('DOMContentLoaded', () => {
            cargarDatosDesdeAPI(); 
            cargarMarcas();    
        });

        // ==========================================
        //  LOGICA DE MODAL (Tailwind)
        // ==========================================
        function abrirModal() {
            modal.classList.remove('hidden');
            document.body.classList.add('modal-open');
        }

        function cerrarModal() {
            modal.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }

        function abrirModalCrear() {
            document.getElementById('formVehiculo').reset();
            document.getElementById('vehiculo_id').value = "";
            document.getElementById('modalTitulo').innerHTML = '<i class="bi bi-car-front"></i> Nuevo Vehículo';
            document.getElementById('modelo_id').innerHTML = '<option value="">Seleccione una marca primero</option>';
            document.getElementById('modelo_id').disabled = true;
            abrirModal();
        }

        function abrirModalEditar(vehiculo) {
            document.getElementById('modalTitulo').innerHTML = '<i class="bi bi-pencil-square"></i> Editar Vehículo';
            document.getElementById('vehiculo_id').value = vehiculo.vehiculo_id; // Usamos vehiculo_id según tu backend Laravel
            
            document.getElementById('num_serie').value = vehiculo.num_serie;
            document.getElementById('placa').value = vehiculo.placa;
            document.getElementById('num_economico').value = vehiculo.num_economico;
            document.getElementById('anio').value = vehiculo.anio;
            document.getElementById('marca_id').value = vehiculo.marca_id;

            // Cargar modelos y preseleccionar
            cargarModelos(vehiculo.marca_id, vehiculo.modelo_id);
            abrirModal();
        }

        // ==========================================
        //  FUNCIONES DE DATOS (CRUD REAL)
        // ==========================================

        async function cargarDatosDesdeAPI() {
            loading.classList.remove('hidden');
            loading.classList.add('flex');
            try {
                const response = await fetch(API_URLS.GET_VEHICULOS);
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                
                const data = await response.json();
                
                // Ajuste por si tu API devuelve { data: [...] } o directamente [...]
                listaVehiculos = Array.isArray(data) ? data : (data.data || []); 
                
                renderizarTabla();
            } catch (error) {
                console.error("Error:", error);
                Swal.fire('Error', 'No se pudieron cargar los vehículos. Verifica la conexión.', 'error');
            } finally {
                loading.classList.add('hidden');
                loading.classList.remove('flex');
            }
        }

        async function guardarVehiculo() {
            const form = document.getElementById('formVehiculo');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const id = document.getElementById('vehiculo_id').value;
            const datos = {
                num_serie: document.getElementById('num_serie').value,
                placa: document.getElementById('placa').value,
                num_economico: document.getElementById('num_economico').value,
                anio: document.getElementById('anio').value,
                marca_id: document.getElementById('marca_id').value,
                modelo_id: document.getElementById('modelo_id').value
            };

            loading.classList.remove('hidden');
            loading.classList.add('flex');
            
            try {
                let url, method;

                if (id) {
                    // EDITAR: PUT .../update/{id}
                    url = `${API_URLS.PUT_VEHICULO}/${id}`;
                    method = 'PUT';
                } else {
                    // CREAR: POST .../store
                    url = API_URLS.POST_VEHICULO;
                    method = 'POST';
                }

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Error al guardar');
                }

                await cargarDatosDesdeAPI(); // Recargar tabla
                cerrarModal();
                
                Swal.fire({
                    title: 'Éxito', 
                    text: 'Operación realizada correctamente', 
                    icon: 'success',
                    confirmButtonColor: '#2563eb'
                });

            } catch (error) {
                console.error("Error:", error);
                Swal.fire('Error', error.message || 'Hubo un problema al guardar.', 'error');
            } finally {
                loading.classList.add('hidden');
                loading.classList.remove('flex');
            }
        }

        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Eliminar vehículo?',
                text: "No podrás revertir esto",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', 
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    loading.classList.remove('hidden');
                    loading.classList.add('flex');
                    
                    try {
                        // ELIMINAR: DELETE .../delete/{id}
                        const url = `${API_URLS.DELETE_VEHICULO}/${id}`;
                        
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) throw new Error('Error al eliminar');

                        await cargarDatosDesdeAPI();
                        
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'El registro ha sido eliminado',
                            icon: 'success',
                            confirmButtonColor: '#2563eb'
                        });

                    } catch (error) {
                        console.error(error);
                        Swal.fire('Error', 'No se pudo eliminar el registro', 'error');
                    } finally {
                        loading.classList.add('hidden');
                        loading.classList.remove('flex');
                    }
                }
            });
        }

        // ==========================================
        //  RENDERIZADO UI
        // ==========================================

        function renderizarTabla() {
            const tbody = document.getElementById('tablaBody');
            const contador = document.getElementById('contadorRegistros');
            const filtro = document.getElementById('inputBuscador').value.toLowerCase();

            tbody.innerHTML = '';
            
            // Filtro client-side sobre los datos cargados
            const datosFiltrados = listaVehiculos.filter(item => 
                (item.placa && String(item.placa).toLowerCase().includes(filtro)) || 
                (item.num_serie && String(item.num_serie).toLowerCase().includes(filtro))
            );

            contador.innerText = `${datosFiltrados.length} registros`;

            if(datosFiltrados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-gray-400">No se encontraron datos</td></tr>`;
                return;
            }

            datosFiltrados.forEach(vehiculo => {
                // Aseguramos que accedemos a las propiedades correctas, a veces vienen como objetos anidados
                const nombreMarca = vehiculo.marca ? (vehiculo.marca.marca || vehiculo.marca.nombre || vehiculo.marca) : 'N/A';
                const nombreModelo = vehiculo.modelo ? (vehiculo.modelo.modelo || vehiculo.modelo.nombre || vehiculo.modelo) : 'N/A';

                const tr = `
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-6 text-left whitespace-nowrap font-medium">${vehiculo.vehiculo_id || vehiculo.id}</td>
                        <td class="py-3 px-6 text-left">${vehiculo.num_serie}</td>
                        <td class="py-3 px-6 text-left">
                            <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-xs font-bold border border-blue-200 shadow-sm">
                                ${vehiculo.placa}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-left text-gray-600">${vehiculo.num_economico}</td>
                        <td class="py-3 px-6 text-left">${nombreMarca}</td>
                        <td class="py-3 px-6 text-left">${nombreModelo}</td>
                        <td class="py-3 px-6 text-center">
                            <span class="bg-gray-100 text-gray-700 py-1 px-2 rounded text-xs">
                                ${vehiculo.anio}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center gap-2">
                                <button onclick='abrirModalEditar(${JSON.stringify(vehiculo)})' 
                                    class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200 flex items-center justify-center transition-colors" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="confirmarEliminar(${vehiculo.vehiculo_id || vehiculo.id})" 
                                    class="w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition-colors" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += tr;
            });
        }

        document.getElementById('inputBuscador').addEventListener('keyup', renderizarTabla);

        // ==========================================
        //  CARGA DE COMBOS (API REAL)
        // ==========================================

        async function cargarMarcas() {
            try {
                const response = await fetch(API_URLS.GET_MARCAS);
                if (!response.ok) throw new Error('Error cargando marcas');
                
                const data = await response.json();
                const marcas = Array.isArray(data) ? data : (data.data || []);

                const select = document.getElementById('marca_id');
                select.innerHTML = '<option value="">Seleccione Marca...</option>';
                
                marcas.forEach(m => {
                    // Ajusta 'marca_id' y 'marca' según devuelva tu backend
                    select.innerHTML += `<option value="${m.marca_id || m.id}">${m.marca || m.nombre}</option>`;
                });
            } catch (e) { 
                console.error("Error marcas", e); 
                Swal.fire('Aviso', 'No se pudieron cargar las marcas', 'warning');
            }
        }

        async function cargarModelos(marcaId, modeloSeleccionado = null) {
            const selectModelo = document.getElementById('modelo_id');
            selectModelo.innerHTML = '<option>Cargando...</option>';
            selectModelo.disabled = false;
            selectModelo.classList.remove('disabled:bg-gray-100', 'disabled:text-gray-400');

            if (!marcaId) {
                 selectModelo.innerHTML = '<option value="">Seleccione una marca primero</option>';
                 selectModelo.disabled = true;
                 selectModelo.classList.add('disabled:bg-gray-100', 'disabled:text-gray-400');
                 return;
            }

            try {
                // Ruta: /api/modelos/marca/{marca_id}
                const response = await fetch(`${API_URLS.GET_MODELOS}/${marcaId}`);
                
                if (!response.ok) throw new Error('Error cargando modelos');
                
                const data = await response.json();
                const modelos = Array.isArray(data) ? data : (data.data || []);

                selectModelo.innerHTML = '<option value="">Seleccione Modelo...</option>';
                
                modelos.forEach(m => {
                    const idModelo = m.modelo_id || m.id;
                    const nombreModelo = m.modelo || m.nombre;
                    const selected = (modeloSeleccionado && modeloSeleccionado == idModelo) ? 'selected' : '';
                    
                    selectModelo.innerHTML += `<option value="${idModelo}" ${selected}>${nombreModelo}</option>`;
                });

                if (modelos.length === 0) {
                    selectModelo.innerHTML = '<option value="">Sin modelos disponibles</option>';
                }

            } catch (error) {
                console.error(error);
                selectModelo.innerHTML = '<option value="">Error al cargar</option>';
            }
        }

    </script>
</body>
</html>