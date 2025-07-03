// resources/js/dashboard.js

// Variables globales para los gráficos
let ventasChart = null;
let reparacionesChart = null;
let productosChart = null;
let clientesChart = null;

// Función para destruir gráficos existentes
function destroyCharts() {
    if (ventasChart) {
        ventasChart.destroy();
        ventasChart = null;
    }
    if (reparacionesChart) {
        reparacionesChart.destroy();
        reparacionesChart = null;
    }
    if (productosChart) {
        productosChart.destroy();
        productosChart = null;
    }
    if (clientesChart) {
        clientesChart.destroy();
        clientesChart = null;
    }
}

// Función para crear gráfico de ventas
function createVentasChart(ctx, data) {
    // Destruir gráfico existente si existe
    if (ventasChart) {
        ventasChart.destroy();
    }
    
    ventasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Ventas',
                data: data.values || [1200, 1900, 1500, 2100, 2400, 1800, 2200],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'S/. ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/. ' + value;
                        }
                    }
                }
            }
        }
    });
}

// Función para crear gráfico de reparaciones
function createReparacionesChart(ctx, data) {
    // Destruir gráfico existente si existe
    if (reparacionesChart) {
        reparacionesChart.destroy();
    }
    
    reparacionesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || ['En Proceso', 'Completadas', 'Pendientes', 'Canceladas'],
            datasets: [{
                data: data.values || [12, 8, 5, 2],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}

// Función para crear gráfico de productos más vendidos
function createProductosChart(ctx, data) {
    // Destruir gráfico existente si existe
    if (productosChart) {
        productosChart.destroy();
    }
    
    productosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || ['Pantalla LCD', 'Batería', 'Cámara', 'Flex Carga', 'Tapa Trasera'],
            datasets: [{
                label: 'Unidades Vendidas',
                data: data.values || [25, 18, 15, 12, 8],
                backgroundColor: 'rgba(147, 51, 234, 0.8)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5
                    }
                }
            }
        }
    });
}

// Función para crear gráfico de clientes nuevos
function createClientesChart(ctx, data) {
    // Destruir gráfico existente si existe
    if (clientesChart) {
        clientesChart.destroy();
    }
    
    clientesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Nuevos Clientes',
                data: data.values || [12, 19, 15, 25, 22, 30],
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    });
}

// Función para inicializar todos los gráficos
function initializeDashboardCharts(chartsData = {}) {
    // Asegurarse de que Chart.js esté cargado
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está cargado');
        return;
    }
    
    // Destruir gráficos existentes
    destroyCharts();
    
    // Crear gráfico de ventas
    const ventasCtx = document.getElementById('ventasChart');
    if (ventasCtx) {
        createVentasChart(ventasCtx.getContext('2d'), chartsData.ventas || {});
    }
    
    // Crear gráfico de reparaciones
    const reparacionesCtx = document.getElementById('reparacionesChart');
    if (reparacionesCtx) {
        createReparacionesChart(reparacionesCtx.getContext('2d'), chartsData.reparaciones || {});
    }
    
    // Crear gráfico de productos
    const productosCtx = document.getElementById('productosChart');
    if (productosCtx) {
        createProductosChart(productosCtx.getContext('2d'), chartsData.productos || {});
    }
    
    // Crear gráfico de clientes
    const clientesCtx = document.getElementById('clientesChart');
    if (clientesCtx) {
        createClientesChart(clientesCtx.getContext('2d'), chartsData.clientes || {});
    }
}

// Función para actualizar los datos del dashboard
async function refreshDashboardData() {
    try {
        const response = await fetch('/dashboard/refresh', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            // Actualizar estadísticas
            updateDashboardStats(data.stats);
            
            // Actualizar gráficos
            if (data.charts) {
                initializeDashboardCharts(data.charts);
            }
            
            // Actualizar actividad reciente
            if (data.actividadReciente) {
                updateActivityFeed(data.actividadReciente);
            }
            
            return data;
        }
    } catch (error) {
        console.error('Error actualizando dashboard:', error);
    }
}

// Función para actualizar las estadísticas
function updateDashboardStats(stats) {
    if (!stats) return;
    
    // Actualizar contadores con animación
    animateCounter('stat-clientes', stats.totalClientes || 0);
    animateCounter('stat-ventas', stats.ventasMes || 0, true);
    animateCounter('stat-reparaciones', stats.reparacionesActivas || 0);
    animateCounter('stat-productos', stats.productosStock || 0);
}

// Función para animar contadores
function animateCounter(elementId, targetValue, isCurrency = false) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
    const duration = 1000;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
        
        if (isCurrency) {
            element.textContent = `S/. ${currentValue.toLocaleString('es-PE')}`;
        } else {
            element.textContent = currentValue.toLocaleString('es-PE');
        }
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Función para actualizar el feed de actividad
function updateActivityFeed(activities) {
    const feedContainer = document.getElementById('activity-feed');
    if (!feedContainer || !activities) return;
    
    feedContainer.innerHTML = activities.map((activity, index) => `
        <li class="relative pb-8">
            ${index !== activities.length - 1 ? '<span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>' : ''}
            <div class="relative flex space-x-3">
                <div>
                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white ${getActivityColor(activity.tipo)}">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                </div>
                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                    <div>
                        <p class="text-sm text-gray-500">${activity.descripcion}</p>
                    </div>
                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                        <time>${activity.tiempo}</time>
                    </div>
                </div>
            </div>
        </li>
    `).join('');
}

// Función auxiliar para obtener el color de la actividad
function getActivityColor(tipo) {
    const colors = {
        'venta': 'bg-green-500',
        'cliente': 'bg-blue-500',
        'reparacion': 'bg-yellow-500',
        'producto': 'bg-purple-500'
    };
    return colors[tipo] || 'bg-gray-500';
}

// Exportar funciones para uso global
window.dashboardCharts = {
    initialize: initializeDashboardCharts,
    destroy: destroyCharts,
    refresh: refreshDashboardData
};