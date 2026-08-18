# Decisiones del proyecto (post-análisis)

Bitácora de decisiones tomadas durante la reconstrucción que no estaban cerradas en `docs/analisis-tecnico-TFPB-Maquinaria.md`, o que lo amplían. Se agrega una entrada por decisión, en orden cronológico.

---

## 2026-08-17 — Estrategia responsive por módulo (mobile-first vs desktop)

**Decisión:** el diseño no es uniforme entre módulos — se define por cómo se usa cada uno en la operación real.

| Módulo | Estrategia | Motivo |
|---|---|---|
| **Reportar** (Yarda → Tipo → Máquina → Registrar) | **Mobile-first** | Los operarios reportan el estado de la maquinaria desde el celular, parados en el campo/yarda. Es el flujo que más se usa fuera de un escritorio. |
| **Dashboard — resumen/KPIs básicos** | **Mobile-first** | Un supervisor debe poder revisar el estado general (resumen por tipo de maquinaria, alertas) desde el celular sin depender de una computadora. |
| **Dashboard — gráficas y reportes detallados** | **Desktop-only** | Las vistas de Chart.js con filtros de rango de fechas/campo y tablas detalladas no se adaptan a mobile; se diseñan para pantalla grande. En viewport mobile se debe mostrar un aviso ("esta vista requiere una pantalla más grande") en vez de forzar un layout roto. |
| **Configuración (catálogos: Field, Status, MachineryType, Machinery)** | Desktop-first, responsive | Uso administrativo, pero sin restricción dura — debe verse usable en tablet/mobile si hace falta, sin ser el caso de uso principal. |
| **Seguridad (roles, permisos, asignación)** | Desktop-first, responsive | Mismo criterio que Configuración — administración, no operación de campo. |

**Cómo aplicarlo:** con Tailwind (mobile-first por diseño: clases base = mobile, prefijos `sm:`/`md:`/`lg:` = breakpoints mayores), esto se traduce en:
- Reportar y el resumen del Dashboard: se construyen primero para viewport mobile, y se amplían con breakpoints solo si aporta valor en pantallas grandes.
- Gráficas/reportes detallados del Dashboard: se construyen para desktop (`lg:`/`xl:` como base funcional) y explícitamente muestran un estado "no disponible en este tamaño de pantalla" por debajo de cierto breakpoint, en vez de intentar comprimir tablas/gráficas complejas a un viewport angosto.

**Afecta a:** Fase 2 (sistema de diseño, se define junto con la paleta/branding), Fase 3 (Reportar), Fase 4 (Dashboard).
