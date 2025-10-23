<?php 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadisticas - Vision-Clara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Estadisticas/Estadisticas.css">
    <link rel="stylesheet" href='../Dashboard/sidebar.css'> 

</head>
<body>

    <?php include '../Dashboard/sidebar.php'; ?>
    <div class="contenedor-principal">
        <div class="header_1">
            <i class="fas fa-chart-bar" data-no-translate></i> 
            <h1>Estadisticas</h1>
        </div>

        <!-- Gráfico de Barras Horizontales -->
        <div class="contenedor-grafico">
            <div class="grafico-barras">
                <!-- Barra 1 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 95%" data-porcentaje="95%"></div>
                        <span class="porcentaje">95%</span>
                    </div>
                </div>
                
                <!-- Barra 2 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 80%" data-porcentaje="80%"></div>
                        <span class="porcentaje">80%</span>
                    </div>
                </div>
                
                <!-- Barra 3 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 70%" data-porcentaje="70%"></div>
                        <span class="porcentaje">70%</span>
                    </div>
                </div>
                
                <!-- Barra 4 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 50%" data-porcentaje="50%"></div>
                        <span class="porcentaje">50%</span>
                    </div>
                </div>
                
                <!-- Barra 5 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 30%" data-porcentaje="30%"></div>
                        <span class="porcentaje">30%</span>
                    </div>
                </div>
                
                <!-- Barra 6 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 15%" data-porcentaje="15%"></div>
                        <span class="porcentaje">15%</span>
                    </div>
                </div>
                
                <!-- Barra 7 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 35%" data-porcentaje="35%"></div>
                        <span class="porcentaje">35%</span>
                    </div>
                </div>
                
                <!-- Barra 8 -->
                <div class="item-barra">
                    <span class="texto-barra">Text Here</span>
                    <div class="contenedor-barra">
                        <div class="barra" style="width: 20%" data-porcentaje="20%"></div>
                        <span class="porcentaje">20%</span>
                    </div>
                </div>
            </div>

            <!-- Escala de porcentajes -->
            <div class="escala-porcentajes">
                <span>0%</span>
                <span>10%</span>
                <span>20%</span>
                <span>30%</span>
                <span>40%</span>
                <span>50%</span>
                <span>60%</span>
                <span>70%</span>
                <span>80%</span>
                <span>90%</span>
                <span>100%</span>
            </div>
        </div>
    </div>
</body>
</html>