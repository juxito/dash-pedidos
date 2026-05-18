# Experiencia como Tech Lead guiando una IA

> Este documento trata sobre cómo guié a la IA para construir este proyecto tipo E-commerce,
> las decisiones ha tomar y qué correcciones técnicas fueron necesarias.

---

## Resumen

Desde el comienzo, el flujo y calidad del proyecto se definió por la precisión de los prompts. Más que pedir funcionalidades, se establecieron reglas estrictas de arquitectura y rendimiento desde el primer momento. Instrucciones como evitar completamente el lazy loading, obligar el uso de eager loading (`with()`), centralizar la lógica en Local Scopes y ejecutar operaciones en bulk (sin loops) marcaron una base sólida.

También fue clave delimitar el stack (Laravel + Inertia + React, Docker, Redis, OAuth con GitHub) y restringir herramientas innecesarias, lo que evitó complejidad futura. Estas decisiones iniciales no solo aceleraron el desarrollo, sino que evitaron deuda técnica desde el día uno.

---

## Prompts clave

Las instrucciones iniciales definieron tanto la arquitectura como la calidad del código:

- Prohibición explícita de lazy loading y uso obligatorio de eager loading.
- Uso de Local Scopes como única forma de filtrar lógica de negocio.
- Separación estricta entre backend (lógica) y frontend (vista).
- Restricción de dependencias innecesarias para mantener control del sistema.

Estas decisiones marcaron un buen estándar desde el inicio y redujeron la necesidad de modificaciones complejas futuras.

## Correcciones técnicas

Durante el desarrollo, varias propuestas de la IA fueron funcionales pero ineficientes. Las principales correcciones incluyeron:

- Reemplazo de filtros en memoria (`get()` + `filter`) por consultas optimizadas en base de datos.
- Eliminación de problemas N+1 mediante eager loading sistemático.
- Sustitución de loops con `save()` por operaciones masivas (`insert`, `update`).
- Uso de `whereHas()` en lugar de joins manuales para relaciones.
- Implementación de paginación real desde la base de datos.
- Eliminación de lógica de negocio en componentes React, manteniéndolos como vistas puras.

Estas correcciones mejoraron y optimizaron significativamente el rendimiento.

## Aprendizajes clave como Tech Lead

En el desarrollo de este proyecto, como Tech Lead, aprendí que no es solo validar resultados, sino definir instrucciones claras desde el inicio.

Las prácticas más valiosas fueron:

- Establecer reglas no negociables desde el primer prompt.
- Centralizar la lógica en modelos mediante scopes.
- Priorizar operaciones en base de datos sobre procesamiento en aplicación.
- Evitar dependencias innecesarias y mantener control arquitectónico.

Las decisiones que más ayudaron fueron, principalmente, aquellas que obligaban a mantener consistencia, eficiencia y claridad en todo el sistema.

## Conclusión

La principal lección es: cuando se trabaja con IA, la calidad del resultado depende directamente de la claridad, firmeza y nivel técnico de las instrucciones iniciales.

Este proyecto me deja con muchas enseñanzas, tanto como teach lead como desarrollador. La interacción al trabajar con la IA es sumamente interesante, se deben tener conceptos de lo que se esta realizando, pero la tecnología nos ayuda a agilizar estos procesos.
