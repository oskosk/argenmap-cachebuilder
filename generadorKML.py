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
		"El intervalo ya ha sido pedido. Se sobreescribiran los datos."



fechaInicio = sys.argv[1] + ' ' +sys.argv[2]
fechaFin = sys.argv[3] + ' ' +sys.argv[4]
'''Deberia haber algun filtro de parseo'''


colores = ['7f3300ff','7f33ff00','7f33ffff', '7f990000', '7f99cc33', '7f99ff99', '7fcc6633', '7fcc00ff', '7fccffcc', '7fff0000', '7fff00ff', '7fffcc00', '7fffffff', '7fcc00ff', '7fff0099', '7f0066cc', '7fcc99ff', '7f660033']
listaTiles=logFunctions.pasarLogAdegreesPorIntervalo(fechaInicio, fechaFin)
if len(sys.argv)==6:
	nombreFolder = os.getcwd()+'/tiles_'+sys.argv[1]+'_'+string.replace(sys.argv[2], ':', '')+'__'+sys.argv[3]+'_'+string.replace(sys.argv[4], ':', '')+'__'+sys.argv[5]
	createPath(nombreFolder)
	diccionarioZoomX = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)[int(sys.argv[5])]
	parsearKML.crearArchivoXML(diccionarioZoomX, sys.argv[5], nombreFolder, colores[random.randint(0, len(colores)-1)])

elif len(sys.argv) == 5:
	nombreFolder = os.getcwd()+'/tiles_'+sys.argv[1]+'_'+string.replace(sys.argv[2], ':', '')+'__'+sys.argv[3]+'_'+string.replace(sys.argv[4], ':', '')
	createPath(nombreFolder)
	diccionarioPorZooms = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)
	i = 0
	for key in diccionarioPorZooms:
		parsearKML.crearArchivoXML(diccionarioPorZooms[key], key, nombreFolder, colores[i])
		i+=1

else:
	print ("Numero de parametros erroneo. Protocolo: generadorKML.py fechaInicio horaInicio fechaFin horaFin zoom(opcional)")

