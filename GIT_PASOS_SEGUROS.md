# 🔒 Pasos Seguros para Bajar y Subir Cambios en Git

## ⚠️ ANTES DE EMPEZAR - Reglas de Oro

1. **SIEMPRE** haz backup de tu BD antes de cualquier operación Git
2. **SIEMPRE** crea una rama nueva si vas a hacer cambios grandes
3. **SIEMPRE** revisa `git status` antes de hacer commits
4. **NUNCA** hagas `git push -f` (force push) sin estar seguro

---

## 🔄 FLUJO SEGURO: Bajar Cambios (Pull)

### Paso 1: Ver el estado actual
```powershell
cd c:\xampp\htdocs\Vision-Clara
git status
```

**Qué esperar:**
- `On branch main` - Estás en rama correcta
- `Your branch is up to date` - Sin cambios del servidor
- `nothing to commit` - Todo guardado localmente

---

### Paso 2: Guardar cambios locales (IMPORTANTE)

Si tienes cambios sin commitear:

```powershell
# Ver qué cambios tienes
git status

# Si hay cambios, guárdalos en un "stash"
git stash save "Mi trabajo del día"
```

**Esto temporalmente guarda tus cambios sin perderlos**

---

### Paso 3: Actualizar tu rama local

```powershell
# Traer cambios del servidor
git fetch origin

# Ver qué cambios vienen
git log main..origin/main --oneline
```

---

### Paso 4: Aplicar los cambios (OPCIÓN A - Sin conflictos)

```powershell
# Si no hay conflictos, simplemente:
git pull origin main
```

**Resultado esperado:**
```
From github.com:anabeli05/Vision-Clara
 * branch            main       -> FETCH_HEAD
Already up to date.
# o
Fast-forward
 archivo.php | 10 ++
```

---

### Paso 5: Recuperar tu trabajo guardado

```powershell
# Ver qué stash guardaste
git stash list

# Recuperar el último stash
git stash pop

# O recuperar uno específico
git stash pop stash@{0}
```

---

## 🆕 FLUJO SEGURO: Bajar Cambios (Pull) CON CONFLICTOS

### Si tienes conflictos (aparece error)

```powershell
# Ver el estado
git status
```

**Verás algo como:**
```
both modified:   archivo.php
```

### Resolver conflictos manualmente:

1. **Abre el archivo** con conflicto en VS Code
2. **Busca las marcas de conflicto:**
```
<<<<<<< HEAD
Mi código local
=======
Código del servidor
>>>>>>> origin/main
```

3. **Decide qué guardar:**
   - Borra lo que NO quieres
   - Mantén lo que SÍ quieres
   - Elimina las marcas `<<<<`, `====`, `>>>>`

4. **Guarda el archivo**

5. **Marca como resuelto:**
```powershell
git add archivo.php
git commit -m "Resolver conflicto en archivo.php"
```

---

## 📤 FLUJO SEGURO: Subir Cambios (Push)

### Paso 1: Revisar cambios pendientes

```powershell
cd c:\xampp\htdocs\Vision-Clara
git status
```

---

### Paso 2: Ver qué vas a subir

```powershell
# Ver diferencias de tu rama con origin/main
git log origin/main..main --oneline

# O ver cambios en archivos específicos
git diff origin/main archivo.php
```

---

### Paso 3: Agregar archivos

```powershell
# Agregar TODOS los cambios (recomendado si ya revisaste)
git add .

# O agregar archivos específicos
git add archivo1.php archivo2.js
```

**Verificar:**
```powershell
git status
# Debe mostrar "Changes to be committed:"
```

---

### Paso 4: Crear commit

```powershell
git commit -m "Descripción clara del cambio"
```

**Ejemplos buenos:**
```
- "feat: Agregar diagnóstico de turnos"
- "fix: Resolver duplicados en turnos"
- "docs: Actualizar guía de ejecución"
- "style: Mejorar visualización"
```

---

### Paso 5: Subir cambios

```powershell
# Ver antes de subir
git log origin/main..main --oneline

# Subir
git push origin main
```

**Resultado esperado:**
```
Enumerating objects: 5, done.
Counting objects: 100% (5/5), done.
Writing objects: 100% (3/3), 245 bytes...
To github.com:anabeli05/Vision-Clara
   8d62a5e..a1b2c3d  main -> main
```

---

## 🔄 FLUJO COMPLETO (Lo más común)

### Escenario: Bajaste cambios del servidor y quieres subir los tuyos

```powershell
# 1. Ver estado
git status

# 2. Guardar trabajo pendiente
git stash save "Mi trabajo"

# 3. Traer cambios del servidor
git pull origin main

# 4. Recuperar trabajo
git stash pop

# 5. Revisar cambios
git status
git diff

# 6. Agregar cambios
git add .

# 7. Crear commit
git commit -m "Mi descripción"

# 8. Subir
git push origin main

# 9. Verificar
git log --oneline -5
```

---

## 🛡️ CREAR RAMA NUEVA (Si quieres ser extra cuidadoso)

### Para cambios grandes o experimentales:

```powershell
# 1. Crear rama nueva
git checkout -b feature/mi-cambio

# 2. Hacer cambios y commits como normal
git add .
git commit -m "Mi cambio"

# 3. Subir rama nueva
git push origin feature/mi-cambio

# 4. En GitHub, crear "Pull Request" (integrar a main)
# 5. Después de revisar, integrar

# 6. Volver a main
git checkout main
git pull origin main

# 7. Eliminar rama local
git branch -d feature/mi-cambio
```

---

## 🔍 COMANDOS ÚTILES

```powershell
# Ver historial
git log --oneline -10

# Ver cambios no commitados
git diff

# Ver qué cambios subirás
git log origin/main..main --oneline

# Deshacer último commit (CUIDADO)
git reset --soft HEAD~1

# Ver ramas disponibles
git branch -a

# Limpiar stash antiguo
git stash drop stash@{0}
```

---

## ⚠️ SITUACIONES DE EMERGENCIA

### Accidentalmente borraste un archivo
```powershell
# Ver qué borraste
git status

# Restaurar
git restore nombre-archivo.php

# O si ya hiciste commit
git revert HEAD
```

### Hiciste commit pero no querías
```powershell
# Deshacer último commit PERO guardar cambios
git reset --soft HEAD~1

# O deshacer todo el commit
git reset --hard HEAD~1
```

### Necesitas ver qué cambios hay en el servidor
```powershell
git fetch origin
git log origin/main --oneline -5
```

---

## 📋 CHECKLIST ANTES DE PUSH

- [ ] ¿Está XAMPP corriendo? (para probar)
- [ ] ¿He revisado `git status`?
- [ ] ¿He visto `git diff` de mis cambios?
- [ ] ¿El mensaje del commit describe bien el cambio?
- [ ] ¿No hay conflictos (`git status` limpio)?
- [ ] ¿Backup de BD hecho?
- [ ] ¿Los cambios funcionan localmente?

---

## 🎯 FLUJO RECOMENDADO DIARIO

### Mañana (Empezar el día)
```powershell
cd c:\xampp\htdocs\Vision-Clara
git pull origin main  # Traer cambios de otros
```

### Durante el día
```powershell
# Trabajar como normal
# Guardar cambios cada cierto tiempo:
git add .
git commit -m "Descripción"
```

### Tarde (Subir cambios)
```powershell
git push origin main
```

### Antes de salir
```powershell
git log --oneline -5  # Verificar que subió
```

---

## 🚀 COMANDO RÁPIDO (Si confías)

```powershell
cd c:\xampp\htdocs\Vision-Clara; git pull origin main; git add .; git commit -m "Cambios del día"; git push origin main
```

---

## ❌ NO HAGAS ESTO

```powershell
❌ git push -f                  # Force push (borra servidor)
❌ git reset --hard            # Sin estar seguro
❌ git rebase (si eres nuevo)   # Complejo
❌ git cherry-pick (sin saber)  # Puede confundir
❌ Editar .git directamente     # Se daña el repo
```

---

## ✅ VERIFICA QUE TODO ESTÁ BIEN

```powershell
# Después de push
git log origin/main..main --oneline
# Debe estar vacío (quiere decir que todo subió)

git status
# Debe decir "Your branch is up to date with 'origin/main'"
```

---

## 📞 Ayuda Rápida

| Problema | Comando |
|----------|---------|
| No subieron cambios | `git push origin main` |
| Cambios no aparecen | `git add .` → `git commit` → `git push` |
| Conflicto | Resuelve en VS Code → `git add .` → `git commit` |
| Quiero deshacer | `git reset --soft HEAD~1` |
| Ver qué hice | `git diff` o `git log --oneline` |

---

## 🎓 Explicación Simple

```
Tu Computadora (Local)
    ↓
    ├─ Archivos modificados
    ├─ git add .      ← "Preparo para guardar"
    ├─ git commit     ← "Guardo localmente"
    └─ git push       ← "Subo al servidor (GitHub)"
    
    ↑
    └─ git pull       ← "Traigo cambios del servidor"
```

---

## 📌 RESUMEN EN 3 PASOS

### 1️⃣ Bajarlo (SEGURO)
```powershell
git fetch origin
git pull origin main
```

### 2️⃣ Hacerlo (COMO SIEMPRE)
```powershell
# Editar archivos normalmente
git add .
git commit -m "Mi cambio"
```

### 3️⃣ Subirlo (SEGURO)
```powershell
git push origin main
```

**¡Listo!** 🎉
