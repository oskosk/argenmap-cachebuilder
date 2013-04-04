import logFunctions

def crearArchivoXML(lista):
    f=open("tiles.kml")
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
			print'\t','\t','\t','<name>polygon'+str(lista.index(i))+'</name>'
			print'\t','\t','\t','<Polygon>'
			print'\t','\t','\t','\t','<outerBoundaryIs>'
			print'\t','\t','\t','\t','\t','<LinearRing>'
			print'\t','\t','\t','\t','\t','\t','<coordinates>'
			
			print'\t','\t','\t','\t','\t','\t','\t',str(i['NW'][1])+','+str(i['NW'][0])+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['NE'][1])+','+str(i['NE'][0])+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['SE'][1])+','+str(i['SE'][0])+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['SW'][1])+','+str(i['SW'][0])+',0'
			print'\t','\t','\t','\t','\t','\t','\t',str(i['NW'][1])+','+str(i['NW'][0])+',0'

			print'\t','\t','\t','\t','\t','\t','</coordinates>'
			print'\t','\t','\t','\t','\t','</LinearRing>'
			print'\t','\t','\t','\t','</outerBoundaryIs>'
			print'\t','\t','\t','</Polygon>'
			print'\t','\t','</Placemark>'
    print'\t','</Folder>'
    print'</Document>'
    print'</kml>'
    f.close()


##a=[]
##b=[]
##
##b.append("obj1.1")
##b.append("obj1.2")
##a.append(b)
listaTiles=logFunctions.pasarLogAdegrees()
crearArchivoXML(listaTiles)
    
    
