# First Coffee Chile — sitio WordPress

Tema y plugins a medida de **firstcoffeechile.cl**: dispensador portátil de café caliente (portada, páginas de producto, calculadora y cotizador).
Desarrollo: GeekDev — Diego Catalán.

## Estructura

```
wp-content/
├── themes/firstcoffee/            Tema: solo presentación (plantillas, CSS, JS, logos)
└── plugins/
    ├── fc-core/                   Contenidos (CPT), ajustes, cotizador, correos, panel y rol Gestor
    └── first-coffee-telon/        Telón «Estamos preparando café» mientras el sitio no se lanza
```

El repositorio refleja la carpeta `wp-content` del sitio. No incluye el núcleo de WordPress,
`wp-config.php`, la base de datos ni `uploads/` (ahí quedan los logos de las cotizaciones).

## Tipos de contenido (plugin fc-core)

| CPT | Uso |
|---|---|
| `fc_cotizacion` | Solicitudes del cotizador. Privadas; estados Nueva / Respondida / Cerrada. |
| `fc_paso` | «Cuatro pasos, sin electricidad» (Carga, Mochila, Presión, Servicio). |
| `fc_pieza` | Componentes en la ilustración «El equipo» (estanque, mochila, bomba y línea). |
| `fc_beneficio` | «Lo que cambia en tu jornada» (6 beneficios con ícono). |
| `fc_uso` | «Un equipo, varios negocios» (calle, eventos, oficinas, reparto). |
| `fc_faq` | Preguntas frecuentes. |
| `fc_video` | Videos (sin uso en la portada actual; se conserva). |

Páginas (las crea **First Coffee → Ajustes → Cargar contenido del sitio**): La empresa, Ficha y rentabilidad,
Manual de operación, Servicio y repuestos, Brochures (+ Calle, Eventos, Negocios).

Shortcodes para las páginas: `[fc_calculadora]`, `[fc_escenarios]`, `[fc_brochures]`, `[fc_precio]`,
`[fc_whatsapp texto="" mensaje=""]`, `[fc_imprimir]`. Los precios, vasos por carga, margen y jornadas
se editan en **Ajustes → Precios y rentabilidad** y se recalculan en todo el sitio.

Ajustes generales en **First Coffee → Ajustes** (opción `fc_ajustes`).
Endpoint del cotizador: `POST /wp-json/fc/v1/cotizacion` (nonce, honeypot, tiempo mínimo y límite por IP).

## Instalación manual

1. Copiar `wp-content/plugins/fc-core` y `wp-content/themes/firstcoffee` al sitio (o subir sus zip desde el escritorio).
2. Activar el plugin **First Coffee — Núcleo del sitio** y luego el tema **First Coffee**.
3. Completar **First Coffee → Ajustes** (WhatsApp, correo de avisos) y, si se quiere partir con algo, usar **Cargar contenido de ejemplo**.
4. Configurar SMTP (FluentSMTP + Google Workspace) para que lleguen los correos de cotización.

## Despliegue con Git en cPanel

El archivo `.cpanel.yml` copia el tema y los plugins a `~/public_html/wp-content/` cada vez que se despliega.

1. En cPanel → **Git™ Version Control** → *Create* → *Clone a Repository*, con la URL de este repo
   (para un repo privado, primero agregar la clave SSH de cPanel como *Deploy key* en GitHub).
2. Ruta del repositorio en el servidor: por ejemplo `~/repositories/firstcoffee-wp` (fuera de `public_html`).
3. Para publicar cambios: *Manage* → *Pull or Deploy* → **Update from Remote** y luego **Deploy HEAD Commit**.

Si WordPress no está en `public_html`, ajustar `DEPLOYPATH` en `.cpanel.yml`.

## Flujo de trabajo

- `main`: lo que está en producción.
- Cambios en ramas (`feature/…`, `fix/…`) y merge a `main` cuando estén probados.
- Subir la versión en el encabezado del plugin/tema cuando cambie algo visible (sirve para el caché de CSS/JS).

## Pendientes antes de lanzar

- Logo vectorial (SVG/PDF) y colores definitivos según el Manual de marca.
- Reemplazar los datos entre corchetes del contenido de ejemplo.
- SMTP configurado y prueba de envío real.
- Desactivar el telón.
