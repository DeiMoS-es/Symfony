# 🎨 Frontend Agent — UI / UX / Interacciones

## Misión
Diseñar y mantener la experiencia de usuario: vistas Twig, componentes reutilizables, accesibilidad y responsividad.

## Principios visuales
- Simplicidad y claridad: Bootstrap 5, tipografía legible.
- Componentes atómicos: `MovieCard`, `RatingForm`, `WeeklyBanner`, `Leaderboard`.
- Mobile-first.

## Estructura recomendada
- `templates/` (Twig)
  - `base.html.twig`
  - `movie/` (index, show, form)
  - `rating/` (form, list)
- `assets/` (JS y SCSS)
- Usar Stimulus o vanilla JS para pequeñas interacciones.

## Checklists por vista
- Movie show:
  - [ ] Mostrar poster, título, año
  - [ ] Mostrar rating medio con número (1-5) y stars visuales
  - [ ] Form para puntuar (si user logueado)
- Dashboard:
  - [ ] Banner con película de la semana
  - [ ] Link rápido a puntuar y ver comentarios

## Accesibilidad
- Formularios con labels asociados
- Colores con contraste suficiente
- `aria` en componentes dinámicos

## Comandos / Build
```bash
# si usas Symfony UX + Webpack Encore
yarn install
yarn encore dev
