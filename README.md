argenmap-cachebuilder
=====================

Genera un set de imágenes PNG en una estructura de directorios como  la estructura de [Tile Map Service](http://en.wikipedia.org/wiki/Tile_Map_Service).

Sirve para generar las imágenes de capas base que alimentan **argenmap.jquery v1** y **argenmap.jquery v2**.

La idea es poder generar un zip file con todos los PNGs de la grilla estándar en proyección EPSG:3857 en un set de directorios que corresponden a la estructura TMS para después poder desplegar esos directorios en un server web. 

De esta manera, no se requiere ningún preprocesamiento del lado del servidor para servir tiles para argenmap.jquery.

Estructura de directorios TMS
------------------------------

La estructura de una URL TMS es la siguiente

http://servidor/tms/1.0.0/z/x/y.png

en donde:

* http://servidor/tms es la URL de base para el servicio TMS.
* 1.0.0 es la versión del servicio TMS.
* z: es el nivel de zoom de la grilla estándar.
* x: es la columna de la grilla.
* y: es la fila de la grilla.

La grilla estándar
-----------------

La grilla estándar utilizada por los mapas web está muy bien explicada en el documento [Bing Maps Tile System](http://msdn.microsoft.com/en-us/library/bb259689.aspx).

Grilla de Google Maps vs TMS
-------------------------

La comparación entre ambas grillas y el sistema de coordenadas de tiles está perfectamente aclarada en[Tiles à la Google Maps: Coordinates, Tile Bounds and Projection](http://www.maptiler.org/google-maps-coordinates-tile-bounds-projection/)


argenmap.jquery
---------------

Más información acerca de argenmap.jquery en [http://www.ign.gob.ar/argenmap].


Autores
----------------

* Tomás Marcote
* Oscar López

Licencia
------------
**argenmap.cachebuilder** es software libre. Se distribuye bajo una licencia similar a la [licencia BSD de 2 cláusulas](http://es.wikipedia.org/wiki/Licencia_BSD#Licencia_BSD_simplificada_o_licencia_FreeBSD_.28de_2_cl.C3.A1usulas.29). Ver el archivo `LICENCIA`.

-Este software contiene código muy canibalizado de [TileCache](http://tilecache.org/).
