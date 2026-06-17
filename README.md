# Reporte de Caja Chica

Aplicación web PHP para consultar, filtrar y exportar reportes de caja chica desde una API REST. Interfaz limpia con Bootstrap 5, búsqueda con Select2 y exportación a Excel/PDF.

## Funcionalidades

- **Consulta paginada** — Visualización de movimientos con paginación configurable.
- **Filtros combinados** — Por rango de fechas, sucursal (`OcrCode`), descripción y comentarios de factura. Los filtros textuales son **case-insensitive** y combinan con los filtros de fecha que se resuelven del lado de la API.
- **Búsqueda en dropdowns** — Los selects de descripción y comentarios usan [Select2](https://select2.org/) con búsqueda incluida.
- **Exportación a Excel** — Descargá todos los registros filtrados en formato `.xlsx` con formato profesional (encabezados, colores alternados, total).
- **Exportación a PDF** — Vista imprimible con hasta 200 registros, diseño horizontal, ideal para reportes físicos.
- **Filtros activos** — Badges que muestran los filtros aplicados, con botón "x" para remover cada uno individualmente.

## Requisitos

- PHP 8.1 o superior
- [Composer](https://getcomposer.org/)
- Extensión `curl` habilitada
- Extensión `mbstring` habilitada
- Extensión `gd` o `imagick` (requerida por Dompdf para algunos caracteres)
- Una API REST que provea los datos de caja chica

## Instalación

```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/reporte-caja-chica.git
cd reporte-caja-chica

# Instalar dependencias de PHP
composer install

# Configurar el entorno
cp .env.example .env
# Editar .env con los valores de tu entorno
```

## Configuración

Toda la configuración sensible se maneja via variables de entorno en el archivo `.env`:

| Variable | Defecto | Descripción |
|---|---|---|
| `API_BASE` | `http://localhost:8000/...` | URL del endpoint REST que provee los datos |
| `API_TIMEOUT` | `30` | Timeout de cada llamada a la API (segundos) |
| `API_CONNECT_TIMEOUT` | `10` | Timeout de conexión a la API (segundos) |
| `API_SSL_VERIFY` | `false` | Verificar certificado SSL (usar `true` en producción) |
| `DEFAULT_LIMIT` | `100` | Registros por página por defecto |
| `MAX_LIMIT` | `500` | Máximo de registros por página |
| `MIN_LIMIT` | `1` | Mínimo de registros por página |
| `APP_NAME` | `"Reporte de Caja Chica"` | Nombre visible en la interfaz |
| `APP_SUBTITLE` | `"Consulta y seguimiento de movimientos"` | Subtítulo en la barra de navegación |
| `APP_DEBUG` | `false` | Modo debug (errores detallados en pantalla) |

## Uso

Serví la aplicación con cualquier servidor web Apache o Nginx apuntando al directorio del proyecto:

```bash
# Con PHP built-in server (desarrollo)
php -S localhost:8080
```

Luego abrí `http://localhost:8080` en tu navegador.

### Formato de respuesta esperado de la API

La API externa debe responder en formato JSON con esta estructura:

```json
{
    "ok": true,
    "data": [
        {
            "DocDate": "2024-01-15",
            "Descripcion": "Pago de servicios",
            "OcrCode": "SUC-001",
            "CommentsFactura": "Factura F001-123",
            "GTotal": 1500.00,
            "TipoDetalle": "FACTURA"
        }
    ],
    "total": 42,
    "pages_total": 5,
    "records_count": 10
}
```

La aplicación soporta los siguientes parámetros query en la API:
- `desde` / `hasta` — Filtro de rango de fechas (`DocDate`)
- `page` — Número de página
- `limit` — Registros por página

## Estructura del proyecto

```
├── index.php               # Punto de entrada principal
├── export-excel.php        # Exportación a Excel (PhpSpreadsheet)
├── generate-pdf.php        # Exportación a PDF (Dompdf)
├── config/
│   └── app.php             # Configuración desde variables de entorno
├── includes/
│   ├── api.php             # Cliente HTTP para la API (cURL)
│   └── helpers.php         # Funciones de filtrado y formateo
├── templates/
│   ├── header.php          # Encabezado HTML, navbar, loader
│   ├── filter-panel.php    # Panel de filtros colapsable
│   ├── active-filters.php  # Badges de filtros activos
│   ├── info-bar.php        # Barra de información y exportación
│   ├── table.php           # Tabla de datos
│   ├── pagination.php      # Paginación y selector de límite
│   └── footer.php          # Scripts JS y cierre de HTML
├── assets/
│   ├── css/style.css       # Estilos personalizados + tema Select2
│   └── js/app.js           # Lógica del frontend (URL params, Select2)
├── .env.example            # Plantilla de configuración de entorno
└── vendor/                 # Dependencias (Composer)
```

## Tecnologías

| Capa | Tecnología |
|---|---|
| **Backend** | PHP 8.1+ (vanilla, sin framework) |
| **Frontend** | Bootstrap 5.3, jQuery 3.6, Select2 4.1 |
| **PDF** | Dompdf ^3.1 |
| **Excel** | PhpSpreadsheet ^5.8 |
| **API client** | cURL |
| **Íconos** | Bootstrap Icons 1.11 |

## Contribuir

1. Hacé un fork del repositorio.
2. Creá una rama para tu feature: `git checkout -b feature/nueva-funcionalidad`.
3. Commiteá tus cambios: `git commit -am 'Agrega nueva funcionalidad'`.
4. Hacé push a la rama: `git push origin feature/nueva-funcionalidad`.
5. Abrí un Pull Request.

## Licencia

MIT — ver [LICENSE](LICENSE).
