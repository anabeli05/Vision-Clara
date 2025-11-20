# 🆘 Guía de Soluciones - Si vuelve a ocurrir "Duplicate entry"

## ⚡ Resumen Rápido

Si ves el error `Duplicate entry 'N001' for key 'turnos.Numero_Turno'`:

1. **Primero**: Abre http://localhost/Vision-Clara/diagnostico-rapido.php
2. **Luego**: Sigue la solución sugerida según lo que encuentre

## 🔍 Diagnóstico Paso a Paso

### Paso 1: Verificar Sistema Completo
```
URL: http://localhost/Vision-Clara/diagnostico-rapido.php
```

Muestra 5 verificaciones:
- ✅ UNIQUE constraint existe?
- ✅ Columna Fecha_Solo existe?
- ✅ Tabla turno_sequences existe?
- ✅ Hay duplicados detectados?
- ✅ API está disponible?

**Si todo está en verde (✅)**: Tu sistema está OK, no hay problema.

**Si algo está en rojo (❌)**: Sigue la solución sugerida.

---

## 🛠️ Soluciones Disponibles

### 1️⃣ Limpiar Duplicados (SI HAY ERROR HOY)
```
URL: http://localhost/Vision-Clara/soluciones/limpiar-duplicados-hoy.php
```

**Cuándo usar**: Si ves `Duplicate entry 'N001'` HOY
**Qué hace**:
- Encuentra N001 duplicado (por ejemplo)
- Mantiene el PRIMER registro
- Elimina copias duplicadas
- Verifica que se limpió correctamente

**Resultado**: ✅ Pueden crear turnos nuevamente sin error

---

### 2️⃣ Restaurar UNIQUE Constraint (SI ESTÁ DAÑADO)
```
URL: http://localhost/Vision-Clara/soluciones/agregar-unique-constraint.php
```

**Cuándo usar**: Si diagnostico dice "UNIQUE constraint NO EXISTE"
**Qué hace**:
1. Limpia constraints antiguos dañados
2. Verifica columna Fecha_Solo
3. Crea nuevo UNIQUE constraint compuesto

**Resultado**: ✅ Protección restaurada contra duplicados

---

### 3️⃣ Recrear Tabla Secuencias (SI ESTÁ CORRUPTA)
```
URL: http://localhost/Vision-Clara/soluciones/recrear-turno-sequences.php
```

**Cuándo usar**: Si diagnostico dice "turno_sequences NO EXISTE"
**Qué hace**:
1. Elimina tabla corrupta
2. Crea tabla nueva
3. Inicializa con últimos números de hoy

**Resultado**: ✅ Generación de números funciona

---

### 4️⃣ Resetear Sistema Completo (ÚLTIMO RECURSO)
```
URL: http://localhost/Vision-Clara/soluciones/resetear-sistema-completo.php
```

**CUIDADO**: ⚠️ ELIMINA TODOS LOS TURNOS DE HOY

**Cuándo usar**: Si nada más funciona
**Qué hace**:
1. Elimina todos los turnos activos de hoy
2. Resetea secuencias a 0
3. Próximo turno será N001, C001, etc.

**Conserva**: Historial de Finalizado/Cancelado

**Resultado**: ✅ Sistema limpio, nuevamente operativo

---

## 📋 Árbol de Decisión

```
¿Error "Duplicate entry N001"?
│
├─ SÍ → Ejecutar diagnostico-rapido.php
│       │
│       ├─ Si dice "UNIQUE constraint NO EXISTE"
│       │  └─ Ejecutar: agregar-unique-constraint.php
│       │
│       ├─ Si dice "turno_sequences NO EXISTE"
│       │  └─ Ejecutar: recrear-turno-sequences.php
│       │
│       ├─ Si dice "DUPLICADOS ENCONTRADOS"
│       │  └─ Ejecutar: limpiar-duplicados-hoy.php
│       │
│       └─ Si TODO está OK (✅)
│          └─ Contactar soporte (error no diagnosticable)
│
└─ NO → Sistema funcionando correctamente ✅
```

---

## 🎯 Casos Comunes

### Caso 1: "Duplicate entry 'N001'"
```
1. Ejecutar: diagnostico-rapido.php
2. Si dice "DUPLICADOS ENCONTRADOS"
3. Ejecutar: limpiar-duplicados-hoy.php
4. Problema resuelto ✓
```

### Caso 2: No se generan turnos (sin error)
```
1. Ejecutar: diagnostico-rapido.php
2. Si dice "turno_sequences NO EXISTE"
3. Ejecutar: recrear-turno-sequences.php
4. Problema resuelto ✓
```

### Caso 3: UNIQUE constraint no funciona
```
1. Ejecutar: diagnostico-rapido.php
2. Si dice "UNIQUE constraint NO EXISTE"
3. Ejecutar: agregar-unique-constraint.php
4. Problema resuelto ✓
```

### Caso 4: Todo está dañado
```
1. Ejecutar: diagnostico-rapido.php
2. Si hay MUCHOS problemas (❌ ❌ ❌)
3. Ejecutar: resetear-sistema-completo.php
4. (⚠️ Esto eliminará turnos de hoy)
5. Problema resuelto ✓
```

---

## 📞 Centro de Soluciones Web

Acceso visual a todas las soluciones:
```
http://localhost/Vision-Clara/soluciones/
```

Interfaz gráfica con:
- ✅ Botones claros por problema
- ✅ Explicaciones detalladas
- ✅ Confirmaciones de seguridad
- ✅ Feedback visual durante ejecución

---

## ⚙️ Archivos de Solución

```
soluciones/
├── index.html                      ← Centro de Soluciones (INTERFAZ)
├── limpiar-duplicados-hoy.php      ← Solución 1
├── agregar-unique-constraint.php   ← Solución 2
├── recrear-turno-sequences.php     ← Solución 3
└── resetear-sistema-completo.php   ← Solución 4 (NUCLEAR)

../diagnostico-rapido.php           ← Diagnóstico (INICIO)
```

---

## 🔒 Prevención

Para **evitar que ocurra nuevamente**:

1. **Limpieza automática diaria**: Ya está configurada
   ```
   php Pantalla_Turnos/limpiar-turnos-diarios-mejorado.php
   ```

2. **Monitoreo regular**: Ejecutar diagnóstico 1x por semana
   ```
   http://localhost/Vision-Clara/diagnostico-rapido.php
   ```

3. **Backups**: Realizar backup de BD regularmente

4. **Actualizar**: Mantener la API actualizada

---

## 📝 Notas Técnicas

### Estructura de la Protección
```
NIVEL 1: UNIQUE Constraint
├─ Columna: Numero_Turno
└─ Columna: Fecha_Solo (derivada de Fecha)
   Resultado: N001 en 2025-11-19 = válido
             N001 en 2025-11-20 = válido (diferente día)
             N001 en 2025-11-19 (nueva inserción) = BLOQUEADO

NIVEL 2: Tabla turno_sequences
├─ Controla: tipo (Visitante/Cliente)
├─ Controla: fecha_secuencia
└─ Controla: ultimo_numero
   Resultado: Próximo N siempre = último + 1

NIVEL 3: Transacciones en API
├─ BEGIN TRANSACTION
├─ SELECT...FOR UPDATE (lock)
├─ UPDATE/INSERT
└─ COMMIT/ROLLBACK
   Resultado: Sin race conditions
```

---

## ✅ Checklist de Salud

Ejecutar diariamente:
- [ ] ✓ Diagnóstico sin errores
- [ ] ✓ UNIQUE constraint presente
- [ ] ✓ turno_sequences poblada
- [ ] ✓ Sin duplicados detectados
- [ ] ✓ API respondiendo

Si todo está ✓: **Sistema saludable** 🎉

---

## 📞 Contacto

Si después de todas las soluciones persiste el problema:
1. Ejecutar: `diagnostico-rapido.php`
2. Captura pantalla con resultado
3. Contactar soporte con información de:
   - Versión de MySQL
   - Versión de PHP
   - Resultado del diagnóstico

---

## 📚 Referencias Relacionadas

- [README.md](../README.md) - Documentación general
- [api-turnos-sequences.php](../Pantalla_Turnos/api-turnos-sequences.php) - Código API
- [limpiar-turnos-diarios-mejorado.php](../Pantalla_Turnos/limpiar-turnos-diarios-mejorado.php) - Mantenimiento automático

---

**Última actualización**: Noviembre 2025
**Estado**: ✅ Activo y funcional
