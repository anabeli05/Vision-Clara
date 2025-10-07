<?php 

?> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
<link rel="stylesheet" href='sidebar.css'> 

<header> 
    <div class="header-contenedor"> 
    <img class="vision-clara" src='../../Imagenes/logo/logo-white.png' alt="logo"> 
        <div class="principal"></div> 
    </div> 

    <div class="header_2"> 
        <div class="sa-controls"> 
            <div class="sa-boton" id="sidebarToggle"> 
                <span>Administrador</span> 
                <i class="fas fa-chevron-down"></i> 
                <img class="close-avatar" src='../../Imagenes/boton_dashboard.png' alt="avatar"> 
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
            <a href="../inicio/InicioSA.php" data-no-translate> 
                <i class="fas fa-home" data-no-translate></i> 
                <span class="link_name">Inicio</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Cliente/Gestion-Cliente.php" data-no-translate> 
                <i class="fas fa-users" data-no-translate></i> 
                <span class="link_name">Gestion de Clientes</span> 
            </a> 
        </li> 
            
        <li data-no-translate="true"> 
            <a href="../Turno/Gestion-Turnos.php" data-no-translate> 
                <i class="fas fa-ticket-alt" data-no-translate></i> 
                <span class="link_name">Gestion de Turnos</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Producto/producto.php" data-no-translate> 
                <i class="fas fa-glasses" data-no-translate></i> 
                <span class="link_name">Productos</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Registro-Cliente/Registro-Cliente.php" data-no-translate> 
                <i class="fas fa-user-plus" data-no-translate></i> 
                <span class="link_name">Registro de Clientes</span> 
            </a> 
        </li> 
        
        <li data-no-translate="true"> 
            <a href="../Estadisticas/Estadisticas.php" data-no-translate> 
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


// cerrar el dashboard dando cliack fuera de este 

document.addEventListener('click', (event) => { 
    if(sidebar.classList.contains('active') && 
    !sidebar.contains(event.target) && 
    !toggleBtn.contains(event.target)){
         closeSidebar(); 
        } 
    }); 
}); 
</script>