import parsearKML
import logFunctions
import sys
import random

#~ Protocolo: generadorKML fechaInicio fechaFin zoom(opcional)
#~ si no pasan zoom, hago todos los zooms


fechaInicio = sys.argv[1] + ' ' +sys.argv[2]
fechaFin = sys.argv[3] + ' ' +sys.argv[4]
'''Deberia haber algun filtro de parseo'''
#~ print logFunctions.pasarFechaTime(fechaInicio)
#~ print logFunctions.pasarFechaTime(fechaFin)

listaTiles=logFunctions.pasarLogAdegreesPorIntervalo(fechaInicio, fechaFin)

if len(sys.argv)==6:
	diccionarioZoomX = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)[int(sys.argv[3])]
	parsearKML.crearArchivoXML(diccionarioZoomX, sys.argv[3])

elif len(sys.argv) == 5:
	diccionarioPorZooms = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)
	for key in diccionarioPorZooms:
		parsearKML.crearArchivoXML(diccionarioPorZooms[key], key)

else:
	print ("Numero de parametros erroneo. Protocolo: generadorKML.py fechaInicio horaInicio fechaFin horaFin zoom(opcional)")

