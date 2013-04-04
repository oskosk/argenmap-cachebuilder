import parsearKML
import logFunctions
import sys

listaTiles=logFunctions.pasarLogAdegrees()
if len(sys.argv)==2:
	diccionarioZoomX = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)[int(sys.argv[1])]
	parsearKML.crearArchivoXML(diccionarioZoomX, sys.argv[1])
else:
	diccionarioPorZooms = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)
	for key in diccionarioPorZooms:
		parsearKML.crearArchivoXML(diccionarioPorZooms[key], key)
