# Vision-Clara - Sistema de Gestión de Turnos

## Descripción
Sistema integral de gestión de turnos para centros de atención, con control de clientes y visitantes.

## Características
- ✅ Generación de turnos sin duplicados
- ✅ Gestión de estado de turnos (Espera, Atendiendo, Finalizado, Cancelado)
- ✅ Diferenciación entre Clientes (con afiliado) y Visitantes
- ✅ Pantalla de espera en tiempo real
- ✅ Admin dashboard
- ✅ Autenticación de usuarios
- ✅ Estadísticas y reportes

## API de Turnos

### Crear Turno Visitante
```http
POST /Vision-Clara/Pantalla_Turnos/api-turnos-sequences.php
Content-Type: application/x-www-form-urlencoded

tipo=Visitante
```

### Crear Turno Cliente
```http
POST /Vision-Clara/Pantalla_Turnos/api-turnos-sequences.php

tipo=Cliente&afiliado=123456
```

### Obtener Turnos
```http
GET /Vision-Clara/Pantalla_Turnos/api-turnos-sequences.php
```

## Base de Datos

### Tablas principales
- **turnos**: Registro de turnos con estados
- **turno_sequences**: Control de secuencias por día
- **clientes**: Datos de clientes afiliados

## Mantenimiento

### Limpieza de Turnos Antiguos
```bash
php Pantalla_Turnos/limpiar-turnos-diarios-mejorado.php
```

Elimina turnos finalizados/cancelados más antiguos de 7 días.

## Características Técnicas

### Protección contra Duplicados ✅
- UNIQUE constraint sobre (Numero_Turno, Fecha_Solo)
- Permite reutilizar números en días diferentes
- Bloquea duplicados en el mismo día

### Seguridad
- ✅ Autenticación de usuarios
- ✅ Control de acceso por roles
- ✅ Validación de entrada
- ✅ Prepared statements (SQL injection)
- ✅ CORS configurado

## Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx

## Acceso
http://localhost/Vision-Clara/