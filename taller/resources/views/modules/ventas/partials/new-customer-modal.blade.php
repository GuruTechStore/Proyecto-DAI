{{-- resources/views/modules/ventas/partials/new-customer-modal.blade.php --}}
<div x-show="showNewCustomerModal" 
     x-transition.opacity 
     class="fixed inset-0 z-50 overflow-y-auto" 
     x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="showNewCustomerModal" 
             x-transition.opacity
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="showNewCustomerModal = false"></div>

        <!-- Modal panel -->
        <div x-show="showNewCustomerModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gestion-100 dark:bg-gestion-900 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Registrar Nuevo Cliente
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Completa los datos del cliente para agregarlo al sistema
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="createCustomer" class="mt-6" x-data="newCustomerForm()">
                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="customer_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="customer_nombre" 
                               x-model="customerForm.nombre"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="customerErrors.nombre ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               required>
                        <div x-show="customerErrors.nombre" class="mt-1 text-sm text-red-600" x-text="customerErrors.nombre"></div>
                    </div>

                    <!-- Document Type and Number -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <label for="customer_tipo_documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tipo Doc.
                            </label>
                            <select id="customer_tipo_documento" 
                                    x-model="customerForm.tipo_documento"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">C.E.</option>
                                <option value="PASAPORTE">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label for="customer_documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Número de Documento <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex">
                                <input type="text" 
                                       id="customer_documento" 
                                       x-model="customerForm.documento"
                                       class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       :class="customerErrors.documento ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       required>
                                <button type="button" 
                                        @click="validateDocument"
                                        class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-r-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <svg x-show="!validatingDoc" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg x-show="validatingDoc" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="customerErrors.documento" class="mt-1 text-sm text-red-600" x-text="customerErrors.documento"></div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="customer_telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Teléfono
                        </label>
                        <input type="tel" 
                               id="customer_telefono" 
                               x-model="customerForm.telefono"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="+51 999 999 999">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email
                        </label>
                        <input type="email" 
                               id="customer_email" 
                               x-model="customerForm.email"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="customerErrors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               placeholder="cliente@email.com">
                        <div x-show="customerErrors.email" class="mt-1 text-sm text-red-600" x-text="customerErrors.email"></div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="customer_direccion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Dirección
                        </label>
                        <textarea id="customer_direccion" 
                                  x-model="customerForm.direccion"
                                  rows="2"
                                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Dirección completa del cliente"></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            @click="showNewCustomerModal = false; resetCustomerForm()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="customerLoading"
                            :class="customerLoading ? 'opacity-50 cursor-not-allowed' : ''"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                        <svg x-show="customerLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!customerLoading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="customerLoading ? 'Guardando...' : 'Guardar Cliente'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function newCustomerForm() {
    return {
        customerLoading: false,
        validatingDoc: false,
        customerErrors: {},
        
        customerForm: {
            nombre: '',
            tipo_documento: 'DNI',
            documento: '',
            telefono: '',
            email: '',
            direccion: ''
        },

        async validateDocument() {
            if (!this.customerForm.documento) return;
            
            this.validatingDoc = true;
            try {
                // Validate document format
                const tipoDoc = this.customerForm.tipo_documento;
                const documento = this.customerForm.documento;
                
                if (tipoDoc === 'DNI' && documento.length === 8) {
                    // Call API to validate DNI and get customer data
                    const response = await fetch(`/api/reniec/dni/${documento}`);
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            this.customerForm.nombre = data.nombres + ' ' + data.apellidoPaterno + ' ' + data.apellidoMaterno;
                            alert('Documento válido. Datos completados automáticamente.');
                        }
                    }
                } else if (tipoDoc === 'RUC' && documento.length === 11) {
                    // Call API to validate RUC and get company data
                    const response = await fetch(`/api/sunat/ruc/${documento}`);
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            this.customerForm.nombre = data.razonSocial;
                            this.customerForm.direccion = data.direccion;
                            alert('RUC válido. Datos completados automáticamente.');
                        }
                    }
                } else {
                    alert('Formato de documento incorrecto para el tipo seleccionado.');
                }
            } catch (error) {
                console.error('Error validating document:', error);
            } finally {
                this.validatingDoc = false;
            }
        },

        async createCustomer() {
            this.customerLoading = true;
            this.customerErrors = {};

            try {
                const response = await fetch('{{ route("clientes.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.customerForm)
                });

                const data = await response.json();

                if (response.ok) {
                    // Add to customer select
                    const select = document.getElementById('cliente_id');
                    const option = document.createElement('option');
                    option.value = data.cliente.id;
                    option.text = `${data.cliente.nombre} - ${data.cliente.documento}`;
                    option.selected = true;
                    select.appendChild(option);
                    
                    // Update form data
                    this.$parent.form.cliente_id = data.cliente.id;
                    this.$parent.selectedCustomer = data.cliente;
                    
                    // Close modal and reset form
                    this.$parent.showNewCustomerModal = false;
                    this.resetCustomerForm();
                    
                    alert('Cliente registrado exitosamente');
                } else {
                    // Handle validation errors
                    this.customerErrors = data.errors || {};
                    
                    if (data.message) {
                        alert(data.message);
                    }
                }
            } catch (error) {
                console.error('Error creating customer:', error);
                alert('Ocurrió un error al registrar el cliente. Por favor, inténtelo nuevamente.');
            } finally {
                this.customerLoading = false;
            }
        },

        resetCustomerForm() {
            this.customerForm = {
                nombre: '',
                tipo_documento: 'DNI',
                documento: '',
                telefono: '',
                email: '',
                direccion: ''
            };
            this.customerErrors = {};
        }
    };
}
</script>