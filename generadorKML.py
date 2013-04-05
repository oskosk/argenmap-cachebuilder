import parsearKML
import logFunctions
import sys
import random
import os
import string

#~ Protocolo: generadorKML fechaInicio fechaFin zoom(opcional)
#~ si no pasan zoom, hago todos los zooms
def createPath(path):
	if not os.path.isdir(path):
		os.mkdir(path)
	else:
		print "Error"

fechaInicio = sys.argv[1] + ' ' +sys.argv[2]
fechaFin = sys.argv[3] + ' ' +sys.argv[4]
'''Deberia haber algun filtro de parseo'''
nombreFolder = os.getcwd()+'/tiles_'+sys.argv[1]+'_'+string.replace(sys.argv[2], ':', '')+'__'+sys.argv[3]+'_'+string.replace(sys.argv[4], ':', '')
createPath(nombreFolder)

listaTiles=logFunctions.pasarLogAdegreesPorIntervalo(fechaInicio, fechaFin)

if len(sys.argv)==6:
	diccionarioZoomX = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)[int(sys.argv[3])]
	parsearKML.crearArchivoXML(diccionarioZoomX, sys.argv[3], nombreFolder)

elif len(sys.argv) == 5:
	diccionarioPorZooms = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)
	for key in diccionarioPorZooms:
		parsearKML.crearArchivoXML(diccionarioPorZooms[key], key, nombreFolder)

else:
	print ("Numero de parametros erroneo. Protocolo: generadorKML.py fechaInicio horaInicio fechaFin horaFin zoom(opcional)")

