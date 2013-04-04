import logFunctions

def crearArchivoXML(lista):
    print'<?xml version="1.0" encoding="UTF-8"?>'
    print'<kml xmlns="http://earth.google.com/kml/2.2">'
    print'<Document>'
    print'\t','<name>toptiles.kml</name>'
    print'\t','<Folder>'
    print'\t','\t','<name>toptiles</name>'
    lista_sin_repetir = []
    for i in lista:
		if i not in lista_sin_repetir:
			lista_sin_repetir.append(i)
			print'\t','\t','<Placemark>'
			print'\t','\t','\t','<name>polygon'+str(i['nombre'])+'</name>'
			print'\t','\t','\t','<Polygon>'
			print'\t','\t','\t','\t','<outerBoundaryIs>'
			print'\t','\t','\t','\t','\t','<LinearRing>'
			print'\t','\t','\t','\t','\t','\t','<coordinates>'
			
			print'\t','\t','\t','\t','\t','\t','\t',str(i['NW'].lon)+','+str(i['NW'].lat)+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['NE'].lon)+','+str(i['NE'].lat)+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['SE'].lon)+','+str(i['SE'].lat)+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['SW'].lon)+','+str(i['SW'].lat)+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['NW'].lon)+','+str(i['NW'].lat)+',0'

			print'\t','\t','\t','\t','\t','\t','</coordinates>'
			print'\t','\t','\t','\t','\t','</LinearRing>'
			print'\t','\t','\t','\t','</outerBoundaryIs>'
			print'\t','\t','\t','</Polygon>'
			print'\t','\t','</Placemark>'
    print'\t','</Folder>'
    print'</Document>'
    print'</kml>'



##a=[]
##b=[]
##
##b.append("obj1.1")
##b.append("obj1.2")
##a.append(b)
listaTiles=logFunctions.pasarLogAdegrees()
diccionarioZoom11 = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)[11]
crearArchivoXML(listaTiles)
    
#~ diccionarioPorZooms = logFunctions.ordenarCuadradosPorZoomEnUnDic(listaTiles)
#~ for key in diccionarioPorZooms:
	#~ crearArchivoXML(key)

