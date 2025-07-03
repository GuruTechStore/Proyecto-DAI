<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Gestión Empresarial') }} - @yield('title', 'Bienvenido')</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Sistema integral de gestión empresarial para optimizar procesos y maximizar eficiencia">
    <meta name="keywords" content="gestión empresarial, sistema de gestión, ERP, CRM, inventario">
    <meta name="author" content="Gestión Empresarial">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ config('app.name') }} - Sistema de Gestión Empresarial">
    <meta property="og:description" content="Plataforma integral para la gestión de procesos empresariales">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ config('app.name') }} - Sistema de Gestión Empresarial">
    <meta property="twitter:description" content="Plataforma integral para la gestión de procesos empresariales">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Guest Styles -->
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 40px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        .animate-fade-in-left {
            animation: fadeInLeft 0.8s ease-out;
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translate3d(-40px, 0, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        .animate-fade-in-right {
            animation: fadeInRight 0.8s ease-out;
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translate3d(40px, 0, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        .scroll-indicator {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0,-30px,0);
            }
            70% {
                transform: translate3d(0,-15px,0);
            }
            90% {
                transform: translate3d(0,-4px,0);
            }
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    
    @stack('styles')
</head>

<body class="h-full font-sans antialiased bg-gray-50" x-data="{ 
    mobileMenuOpen: false,
    scrolled: false 
}" 
x-init="
    window.addEventListener('scroll', () => {
        scrolled = window.scrollY > 10;
    });
">
    <!-- Navigation -->
    <nav class="fixed w-full z-50 transition-all duration-300"
         :class="scrolled ? 'glass-effect shadow-lg' : 'bg-transparent'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <span class="ml-3 text-xl font-bold" 
                              :class="scrolled ? 'text-gray-900' : 'text-white'">
                            {{ config('app.name', 'Gestión Empresarial') }}
                        </span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" 
                       class="text-base font-medium transition-colors hover:text-indigo-600"
                       :class="scrolled ? 'text-gray-700 hover:text-indigo-600' : 'text-white hover:text-indigo-200'">
                        Características
                    </a>
                    <a href="#about" 
                       class="text-base font-medium transition-colors hover:text-indigo-600"
                       :class="scrolled ? 'text-gray-700 hover:text-indigo-600' : 'text-white hover:text-indigo-200'">
                        Acerca de
                    </a>
                    <a href="#contact" 
                       class="text-base font-medium transition-colors hover:text-indigo-600"
                       :class="scrolled ? 'text-gray-700 hover:text-indigo-600' : 'text-white hover:text-indigo-200'">
                        Contacto
                    </a>
                </div>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    @guest
                        @if(Route::has('login'))
                            <a href="{{ route('login') }}" 
                               class="text-base font-medium transition-colors"
                               :class="scrolled ? 'text-gray-700 hover:text-indigo-600' : 'text-white hover:text-indigo-200'">
                                Iniciar Sesión
                            </a>
                        @endif
                        
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" 
                               class="block px-3 py-2 text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md transition-colors">
                                Registrarse
                            </a>
                        @endif
                    @else
                        <a href="{{ route('dashboard') }}" 
                           class="block px-3 py-2 text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md transition-colors">
                            Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
            <div class="xl:grid xl:grid-cols-3 xl:gap-8">
                <!-- Company Info -->
                <div class="space-y-8 xl:col-span-1">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <span class="ml-3 text-xl font-bold text-white">
                            {{ config('app.name', 'Gestión Empresarial') }}
                        </span>
                    </div>
                    <p class="text-gray-400 text-base">
                        Solución integral para la gestión empresarial moderna. Optimiza tus procesos, 
                        maximiza tu eficiencia y toma decisiones basadas en datos en tiempo real.
                    </p>
                    <div class="flex space-x-6">
                        <!-- Social Media Links -->
                        <a href="#" class="text-gray-400 hover:text-gray-300 transition-colors">
                            <span class="sr-only">Facebook</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-300 transition-colors">
                            <span class="sr-only">Twitter</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-300 transition-colors">
                            <span class="sr-only">LinkedIn</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M19 0H5a5 5 0 00-5 5v14a5 5 0 005 5h14a5 5 0 005-5V5a5 5 0 00-5-5zM8 19H5V8h3v11zM6.5 6.732c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zM20 19h-3v-5.604c0-3.368-4-3.113-4 0V19h-3V8h3v1.765c1.396-2.586 7-2.777 7 2.476V19z" clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Links -->
                <div class="mt-12 grid grid-cols-2 gap-8 xl:mt-0 xl:col-span-2">
                    <div class="md:grid md:grid-cols-2 md:gap-8">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Producto</h3>
                            <ul class="mt-4 space-y-4">
                                <li><a href="#features" class="text-base text-gray-300 hover:text-white transition-colors">Características</a></li>
                                <li><a href="#pricing" class="text-base text-gray-300 hover:text-white transition-colors">Precios</a></li>
                                <li><a href="#integrations" class="text-base text-gray-300 hover:text-white transition-colors">Integraciones</a></li>
                                <li><a href="#updates" class="text-base text-gray-300 hover:text-white transition-colors">Actualizaciones</a></li>
                            </ul>
                        </div>
                        <div class="mt-12 md:mt-0">
                            <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Soporte</h3>
                            <ul class="mt-4 space-y-4">
                                <li><a href="#documentation" class="text-base text-gray-300 hover:text-white transition-colors">Documentación</a></li>
                                <li><a href="#help" class="text-base text-gray-300 hover:text-white transition-colors">Centro de Ayuda</a></li>
                                <li><a href="#contact" class="text-base text-gray-300 hover:text-white transition-colors">Contacto</a></li>
                                <li><a href="#status" class="text-base text-gray-300 hover:text-white transition-colors">Estado del Sistema</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="md:grid md:grid-cols-2 md:gap-8">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Empresa</h3>
                            <ul class="mt-4 space-y-4">
                                <li><a href="#about" class="text-base text-gray-300 hover:text-white transition-colors">Acerca de</a></li>
                                <li><a href="#blog" class="text-base text-gray-300 hover:text-white transition-colors">Blog</a></li>
                                <li><a href="#careers" class="text-base text-gray-300 hover:text-white transition-colors">Carreras</a></li>
                                <li><a href="#partners" class="text-base text-gray-300 hover:text-white transition-colors">Socios</a></li>
                            </ul>
                        </div>
                        <div class="mt-12 md:mt-0">
                            <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Legal</h3>
                            <ul class="mt-4 space-y-4">
                                <li><a href="#privacy" class="text-base text-gray-300 hover:text-white transition-colors">Privacidad</a></li>
                                <li><a href="#terms" class="text-base text-gray-300 hover:text-white transition-colors">Términos</a></li>
                                <li><a href="#cookies" class="text-base text-gray-300 hover:text-white transition-colors">Cookies</a></li>
                                <li><a href="#licenses" class="text-base text-gray-300 hover:text-white transition-colors">Licencias</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="mt-12 border-t border-gray-700 pt-8">
                <div class="max-w-md">
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">
                        Suscríbete a nuestro boletín
                    </h3>
                    <p class="mt-2 text-base text-gray-300">
                        Recibe las últimas noticias y actualizaciones sobre nuestro sistema.
                    </p>
                    <form class="mt-4 sm:flex sm:max-w-md" x-data="{ email: '' }" @submit.prevent="submitNewsletter">
                        <label for="email-address" class="sr-only">Dirección de email</label>
                        <input type="email" 
                               x-model="email"
                               id="email-address" 
                               autocomplete="email" 
                               required 
                               class="appearance-none min-w-0 w-full bg-white border border-transparent rounded-md py-2 px-4 text-base text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white focus:border-white focus:placeholder-gray-400"
                               placeholder="Ingresa tu email">
                        <div class="mt-3 rounded-md sm:mt-0 sm:ml-3 sm:flex-shrink-0">
                            <button type="submit" 
                                    class="w-full bg-indigo-500 border border-transparent rounded-md py-2 px-4 flex items-center justify-center text-base font-medium text-white hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500 transition-colors">
                                Suscribirse
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-8 border-t border-gray-700 pt-8 md:flex md:items-center md:justify-between">
                <div class="flex space-x-6 md:order-2">
                    <p class="text-xs text-gray-400">
                        Versión {{ config('app.version', '1.0.0') }}
                    </p>
                </div>
                <p class="mt-8 text-base text-gray-400 md:mt-0 md:order-1">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Gestión Empresarial') }}. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button x-data="{ show: false }" 
            x-show="show" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90"
            x-init="
                window.addEventListener('scroll', () => {
                    show = window.scrollY > 400;
                });
            "
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-8 right-8 z-50 bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-full shadow-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
        <span class="sr-only">Volver arriba</span>
    </button>

    @stack('scripts')
    
    <!-- Global Guest Scripts -->
    <script>
        // Smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        const navHeight = document.querySelector('nav').offsetHeight;
                        const targetPosition = targetElement.offsetTop - navHeight - 20;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });

        // Newsletter subscription
        document.addEventListener('alpine:init', () => {
            Alpine.data('newsletter', () => ({
                email: '',
                loading: false,
                
                async submitNewsletter() {
                    if (!this.email) return;
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch('/api/newsletter/subscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ email: this.email })
                        });
                        
                        if (response.ok) {
                            alert('¡Gracias por suscribirte! Te mantendremos informado.');
                            this.email = '';
                        } else {
                            alert('Error al suscribirse. Por favor intenta de nuevo.');
                        }
                    } catch (error) {
                        console.error('Newsletter subscription error:', error);
                        alert('Error al suscribirse. Por favor intenta de nuevo.');
                    } finally {
                        this.loading = false;
                    }
                }
            }));
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, observerOptions);

        // Observe elements when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.animate-on-scroll');
            animatedElements.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>