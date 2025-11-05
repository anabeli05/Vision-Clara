<?php 
?> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
<link rel="stylesheet" href='SuperSidebar.css'> 

<header> 
    <div class="header-contenedor"> 
    <a href='../inicio/SuperInicio.php'><img  class="vision-clara" src='../../Imagenes/logo/logo-white.png' alt="logo"> </a> 
        <div class="principal"></div> 
    </div> 

    <div class="header_2"> 
        <div class="sa-controls"> 
            <!-- Botón para cambiar tema en el header -->
            <div class="theme-toggle-header" id="themeToggleHeader">
                <i class="fas fa-moon"></i>
                <span class="theme-text">Modo Oscuro</span>
            </div>
            
            <div class="sa-boton" id="sidebarToggle"> 
                <span>Super Administrador</span> 
                <i class="fas fa-chevron-down"></i> 
                <img class="close-avatar" src='../../Imagenes/boton_dashboard.png' alt="avatar"> 
            </div> 
        </div> 
</header> 


<div class="super-sidebar" id="super-sidebar" aria-hidden="true"> 
    <div id="close-btn" style="text-align: left; padding: 30px; cursor: pointer;">
        <img src='../../Imagenes/boton_dashboard.png' alt='avatar' class=close-avatar ></img> 
        <i class="fas fa-times"></i> 
    </div> 
    <ul class="nav-links"> 
        <li data-no-translate="true"> 
            <a href='../inicio/SuperInicio.php' data-no-translate> 
                <i class="fas fa-home" data-no-translate></i> 
                <span class="link_name">Inicio</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Cliente/SuperGestion.php" data-no-translate> 
                <i class="fas fa-users" data-no-translate></i> 
                <span class="link_name">Gestion de Clientes</span> 
            </a> 
        </li> 
            
        <li data-no-translate="true"> 
            <a href="../Turno/SuperTurnos.php" data-no-translate> 
                <i class="fas fa-ticket-alt" data-no-translate></i> 
                <span class="link_name">Gestion de Turnos</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Producto/SuperProducto.php" data-no-translate> 
                <i class="fas fa-glasses" data-no-translate></i> 
                <span class="link_name">Productos</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Registro-Cliente/SuperRcliente.php" data-no-translate> 
                <i class="fas fa-user-plus" data-no-translate></i> 
                <span class="link_name">Registro de Clientes</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Usuario/SuperGestionU.php" data-no-translate> 
                <i class="fas fa-user-cog" data-no-translate></i> 
                <span class="link_name">Gestion de Usuarios</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Registro-Usuario/SuperRegistroA.php" data-no-translate> 
                <i class="fas fa-user-edit" data-no-translate></i> 
                <span class="link_name">Registro de Usuarios</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href='../Estadisticas/SuperEstadisticas.php' data-no-translate> 
                <i class="fas fa-chart-bar" data-no-translate></i> 
                <span class="link_name">Estadisticas</span> 
            </a> 
        </li> 
        
        <li class="logout-btn">
      <a href="../../Login/logout.php" data-no-translate>
         <i class="fas fa-sign-out-alt" data-no-translate></i>
         <span class="link_name">Cerrar Sesión</span>
      </a>
   </li>
    </ul> 
</div> 

<div class="sidebar-overlay hidden" id="overlay"></div> 

<script> 

document.addEventListener('DOMContentLoaded', ()=> { 

//<!--funciones para cerrar, abrir, y oscurecer el resto menos el dashboard en js---> 

const sidebar = document.getElementById('super-sidebar'); 
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

// cerrar el dashboard dando cliack fuera de este 
document.addEventListener('click', (event) => { 
    if(sidebar.classList.contains('active') && 
    !sidebar.contains(event.target) && 
    !toggleBtn.contains(event.target)){
         closeSidebar(); 
        } 
    }); 

// Función para cambiar entre modo claro y oscuro
const themeToggleHeader = document.getElementById('themeToggleHeader');
const themeIcon = themeToggleHeader.querySelector('i');
const themeText = themeToggleHeader.querySelector('.theme-text');

// Verificar si hay una preferencia guardada
const currentTheme = localStorage.getItem('theme') || 'light';

// Aplicar el tema guardado al cargar la página
if (currentTheme === 'dark') {
    document.body.classList.add('dark-theme');
    themeIcon.classList.remove('fa-moon');
    themeIcon.classList.add('fa-sun');
    themeText.textContent = 'Modo Claro';
}

// Alternar entre modos
themeToggleHeader.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    document.body.classList.toggle('dark-theme');
    
    if (document.body.classList.contains('dark-theme')) {
        localStorage.setItem('theme', 'dark');
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        themeText.textContent = 'Modo Claro';
    } else {
        localStorage.setItem('theme', 'light');
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        themeText.textContent = 'Modo Oscuro';
    }
});
}); 
</script>