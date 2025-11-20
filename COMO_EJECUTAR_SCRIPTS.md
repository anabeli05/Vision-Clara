# 🚀 Cómo Ejecutar los Scripts de Diagnóstico

## Opción 1: Desde el Navegador (MÁS FÁCIL) ✅

### Diagnóstico Rápido
```
http://localhost/Vision-Clara/diagnostico-rapido.php
```

### Centro de Soluciones
```
http://localhost/Vision-Clara/soluciones/
```

### Soluciones Individuales
```
http://localhost/Vision-Clara/soluciones/limpiar-duplicados-hoy.php
http://localhost/Vision-Clara/soluciones/agregar-unique-constraint.php
http://localhost/Vision-Clara/soluciones/recrear-turno-sequences.php
http://localhost/Vision-Clara/soluciones/resetear-sistema-completo.php
```

---

## Opción 2: Desde PowerShell / Terminal

### Ejecutar diagnostico-rapido.php
```powershell
# Opción A: Usando curl (si está disponible)
curl http://localhost/Vision-Clara/diagnostico-rapido.php

# Opción B: Usando Invoke-WebRequest (PowerShell nativo)
Invoke-WebRequest -Uri "http://localhost/Vision-Clara/diagnostico-rapido.php" -UseBasicParsing | Select-Object -ExpandProperty Content
```

### Ejecutar solución específica
```powershell
# Limpiar duplicados de hoy
Invoke-WebRequest -Uri "http://localhost/Vision-Clara/soluciones/limpiar-duplicados-hoy.php" -UseBasicParsing | Select-Object -ExpandProperty Content

# Restaurar UNIQUE constraint
Invoke-WebRequest -Uri "http://localhost/Vision-Clara/soluciones/agregar-unique-constraint.php" -UseBasicParsing | Select-Object -ExpandProperty Content
```

---

## Opción 3: Desde PHP CLI (Línea de comandos)

### Si tienes PHP instalado localmente
```bash
# En Windows (PowerShell)
php c:\xampp\htdocs\Vision-Clara\diagnostico-rapido.php

# O en Terminal/CMD
php "C:\xampp\htdocs\Vision-Clara\diagnostico-rapido.php"
```

### Ejemplo completo:
```powershell
cd c:\xampp\htdocs\Vision-Clara
php diagnostico-rapido.php
```

---

## Opción 4: Desde XAMPP Control Panel

1. Abre **XAMPP Control Panel**
2. Asegúrate de que Apache y MySQL estén corriendo
3. Abre tu navegador
4. Ve a: `http://localhost/Vision-Clara/diagnostico-rapido.php`

---

## 🎯 RECOMENDACIÓN (Lo más Fácil)

**Opción 1 - Navegador**: Es la más simple y visual

```
1. Abre tu navegador (Chrome, Firefox, Edge, etc.)
2. Escribe en la barra de direcciones:
   http://localhost/Vision-Clara/diagnostico-rapido.php
3. Presiona ENTER
4. Lee el resultado (busca ✅ o ❌)
5. Si hay ❌, haz clic en la solución sugerida
```

---

## 📊 Comparativa de Métodos

| Método | Facilidad | Salida | Recomendado |
|--------|-----------|--------|------------|
| Navegador | ⭐⭐⭐⭐⭐ | Visual, HTML | ✅ SÍ |
| PowerShell (curl) | ⭐⭐⭐ | Texto | Para automatización |
| PHP CLI | ⭐⭐⭐ | Texto puro | Para scripts batch |
| XAMPP Panel | ⭐⭐⭐⭐ | Visual | Alternativa |

---

## ⚡ Scripts Rápidos para PowerShell

### Ejecutar diagnóstico y ver resultado
```powershell
$resultado = Invoke-WebRequest -Uri "http://localhost/Vision-Clara/diagnostico-rapido.php" -UseBasicParsing | Select-Object -ExpandProperty Content
$resultado | Write-Host
```

### Copiar resultado a clipboard
```powershell
$resultado = Invoke-WebRequest -Uri "http://localhost/Vision-Clara/diagnostico-rapido.php" -UseBasicParsing | Select-Object -ExpandProperty Content
$resultado | Set-Clipboard
```

### Guardar resultado en archivo
```powershell
$resultado = Invoke-WebRequest -Uri "http://localhost/Vision-Clara/diagnostico-rapido.php" -UseBasicParsing | Select-Object -ExpandProperty Content
$resultado | Out-File -FilePath "C:\temp\diagnostico.html" -Encoding UTF8
# Luego abre con: Invoke-Item "C:\temp\diagnostico.html"
```

---

## 🔄 Ejecutar Soluciones en Secuencia

### Script PowerShell para ejecutar todo
```powershell
$base = "http://localhost/Vision-Clara"

# 1. Diagnóstico
Write-Host "=== EJECUTANDO DIAGNÓSTICO ===" -ForegroundColor Green
Invoke-WebRequest -Uri "$base/diagnostico-rapido.php" -UseBasicParsing | Select-Object -ExpandProperty Content

# 2. Si necesitas limpiar duplicados
Write-Host "`n=== LIMPIANDO DUPLICADOS ===" -ForegroundColor Yellow
Invoke-WebRequest -Uri "$base/soluciones/limpiar-duplicados-hoy.php" -UseBasicParsing | Select-Object -ExpandProperty Content

# 3. Verificar resultado
Write-Host "`n=== VERIFICANDO ===" -ForegroundColor Green
Invoke-WebRequest -Uri "$base/diagnostico-rapido.php" -UseBasicParsing | Select-Object -ExpandProperty Content
```

---

## 📱 Interfaz Visual (RECOMENDADO)

Para la mejor experiencia visual, abre esto en tu navegador:

```
http://localhost/Vision-Clara/soluciones/
```

Tiene:
- ✅ Botones visuales por problema
- ✅ Explicaciones claras
- ✅ Confirmaciones de seguridad
- ✅ Feedback en tiempo real
- ✅ Interfaz atractiva

---

## ❌ Solución de Problemas

### "No se puede conectar a localhost"
- Asegúrate de que XAMPP está corriendo
- Apache debe estar activo (luz verde)
- MySQL debe estar activo (luz verde)

### "Página no encontrada 404"
- Verifica la URL exacta
- Asegúrate de escribir `/Vision-Clara/` correctamente

### "Error de conexión a BD"
- MySQL debe estar corriendo
- Verifica credenciales en `Base de Datos/conexion.php`

---

## 🎓 Flujo Recomendado

```
1. Abre navegador
   ↓
2. http://localhost/Vision-Clara/diagnostico-rapido.php
   ↓
3. Lee resultado (¿hay ❌ en rojo?)
   ↓
   SI: Anota qué problema encontró
   NO: Sistema OK, termina aquí
   ↓
4. Abre: http://localhost/Vision-Clara/soluciones/
   ↓
5. Haz clic en la solución sugerida
   ↓
6. Vuelve a ejecutar diagnóstico para verificar ✅
```

---

## 📞 Comandos Útiles

```powershell
# Ir a carpeta del proyecto
cd c:\xampp\htdocs\Vision-Clara

# Listar archivos de soluciones
Get-ChildItem soluciones/

# Abrir soluciones en navegador
Start-Process "http://localhost/Vision-Clara/soluciones/"

# Abrir diagnóstico en navegador
Start-Process "http://localhost/Vision-Clara/diagnostico-rapido.php"
```

---

## ✅ Resumen

| Quiero... | Hago esto |
|-----------|----------|
| Diagnosticar rápido | Abre http://localhost/Vision-Clara/diagnostico-rapido.php |
| Interfaz visual | Abre http://localhost/Vision-Clara/soluciones/ |
| Limpiar duplicados | Haz clic en "Limpiar Duplicados HOY" |
| Restaurar protección | Haz clic en "Restaurar UNIQUE Constraint" |
| Ejecutar desde terminal | `Invoke-WebRequest -Uri "http://localhost/Vision-Clara/diagnostico-rapido.php"` |

**Lo más fácil: Abre el navegador y ve a `http://localhost/Vision-Clara/soluciones/`** 🎉
