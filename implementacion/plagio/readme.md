## Sistema de plagio
sistema antiplagio con url de la pagina [Editor Online Java](https://www.online-java.com/)

    npx puppeteer browsers install && node index.js

para sql

    ALTER TABLE cursos
    ADD CONSTRAINT fk_usuario
    FOREIGN KEY (usuario) REFERENCES usuarios(id);

para 

    SELECT da.id, u.nombre, u.apellidos, da.estado, da.similitud, da.url
    FROM detalleact da join usuarios u on da.alumno=u.id
    WHERE da.alumno = 1 and da.actividad = 1


- videojuegos -> 923BA0
- algoritmos -> B8FF6E

DELETE from detallecurso WHERE curso=4 and alumno=5

SELECT da.id, a.titulo, a.fechai, a.fechaf, a.descripcion, a.estado FROM detalleact da join actividades a on da.actividad=a.id
WHERE da.alumno = 5 and a.id = 1 ORDER BY da.id ASC


## Algoritmos

- [x] El algoritmo de Aho-Corasick
- [ ] Arborles de suffix
- [x] Levenshtein Distance
- [ ] arboles de sintaxis abstracta

## Tecnologías

- php
- html
- css
- javascript
- bootstrap
- jquery
- puppeteer
- phpdotenv
- sweetalert2
- ajax
- jwt

## Dependencias Composer
- firebase/php-jwt
- vlucas/phpdotenv
- swiftmailer/swiftmailer
## Base de datos
- [x] MySQL
- [ ] Tabla usuarios
- [ ] Tabla cursos
- [ ] Tabla actividades
- [ ] Tabla detalles actividades
- [ ] Tabla detalles cursos