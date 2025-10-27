<?php 

?> 

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 

<header> 
    <div class="header-contenedor"> 
    <img class="vision-clara" src='../../Imagenes/logo/logo-white.png' alt="logo"> 
        <div class="principal"></div> 
        <link rel="stylesheet" href="../"> 
    </div> 

    <div class="header_2"> 
        <div class="theme-controls">
            <!-- Botón Cambio de Tema -->
            <button class="theme-toggle" id="themeToggle">
                <i class="fas fa-moon"></i>
                <span>Modo Oscuro</span>
            </button>
            
            <!-- Botón Administrador (existente) -->
            <div class="sa-controls"> 
                <div class="sa-boton" id="sidebarToggle"> 
                    <span>Administrador</span> 
                    <i class="fas fa-chevron-down"></i> 
                    <img class="close-avatar" src='../../Imagenes/boton_dashboard.png' alt="avatar"> 
                </div> 
            </div>
        </div>
    </div> 
</header> 


<div class="sidebar" id="sidebar" aria-hidden="true"> 
    <div id="close-btn" style="text-align: left; padding: 30px; cursor: pointer;">
        <img src='../../Imagenes/boton_dashboard.png' alt='avatar' class=close-avatar ></img> 
        <i class="fas fa-times"></i> 
    </div> 
    <ul class="nav-links"> 
        <li data-no-translate="true"> 
            <a href="../inicio/InicioAdmin.php" data-no-translate> 
                <i class="fas fa-home" data-no-translate></i> 
                <span class="link_name">Inicio</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Cliente/GestionAdmin.php" data-no-translate> 
                <i class="fas fa-users" data-no-translate></i> 
                <span class="link_name">Gestion de Clientes</span> 
            </a> 
        </li> 
            
        <li data-no-translate="true"> 
            <a href="../Turno/TurnosAdmin.php" data-no-translate> 
                <i class="fas fa-ticket-alt" data-no-translate></i> 
                <span class="link_name">Gestion de Turnos</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Producto/ProductoAdmin.php" data-no-translate> 
                <i class="fas fa-glasses" data-no-translate></i> 
                <span class="link_name">Productos</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Registro-Cliente/RegistroAdmin.php" data-no-translate> 
                <i class="fas fa-user-plus" data-no-translate></i> 
                <span class="link_name">Registro de Clientes</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Estadisticas/EstadisticasAdmin.php" data-no-translate> 
                <i class="fas fa-chart-bar" data-no-translate></i> 
                <span class="link_name">Estadisticas</span> 
            </a> 
        </li> 
        <li class="logout-btn">
      <a href="../../inicio_sesion/logout.php" data-no-translate>
         <i class="fas fa-sign-out-alt" data-no-translate></i>
         <span class="link_name">Cerrar Sesión</span>
      </a>
   </li>
    </ul> 
</div> 

<div class="sidebar-overlay hidden" id="overlay"></div> 

<script> 
document.addEventListener('DOMContentLoaded', ()=> { 
    // Código existente del sidebar...

    // ==============================
    // FUNCIONALIDAD CAMBIO DE TEMA
    // ==============================
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Verificar preferencia guardada
    const savedTheme = localStorage.getItem('adminTheme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-theme');
        updateThemeIcon('dark');
    }

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-theme');
        
        if (body.classList.contains('dark-theme')) {
            localStorage.setItem('adminTheme', 'dark');
            updateThemeIcon('dark');
        } else {
            localStorage.setItem('adminTheme', 'light');
            updateThemeIcon('light');
        }
    });

    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        const text = themeToggle.querySelector('span');
        
        if (theme === 'dark') {
            icon.className = 'fas fa-sun';
            text.textContent = 'Modo Claro';
        } else {
            icon.className = 'fas fa-moon';
            text.textContent = 'Modo Oscuro';
        }
    }

    // Código existente del sidebar...
    const sidebar = document.getElementById('sidebar'); 
    const toggleBtn = document.getElementById('sidebarToggle'); 
    const closeBtn = document.getElementById('close-btn'); 
    const overlay = document.getElementById('overlay'); 

    function openSidebar(){
        sidebar.classList.add('active'); 
        overlay.classList.remove('hidden'); 
        sidebar.setAttribute('aria-hidden', 'false') 
    } 

    function closeSidebar(){
        sidebar.classList.remove('active'); 
        overlay.classList.add('hidden'); 
        sidebar.setAttribute('aria-hidden', 'true'); 
    } 

    toggleBtn.addEventListener('click', (e) => { 
        e.stopPropagation();
        if(sidebar.classList.contains('active')){ 
            closeSidebar();
        }else{ 
            openSidebar();
            } 
    }); 

    closeBtn.addEventListener('click', (e) => { 
        e.stopPropagation(); 
        closeSidebar(); 
    }); 

    overlay.addEventListener('click', (e) => {
         closeSidebar(); 
    }); 

    document.addEventListener('click', (event) => { 
        if(sidebar.classList.contains('active') && 
        !sidebar.contains(event.target) && 
        !toggleBtn.contains(event.target)){
             closeSidebar(); 
            } 
        }); 
}); 
</script>