import logFunctions

def crearArchivoXML(lista):
    f=open("tiles.kml")
    print'<?xml version="1.0" encoding="UTF-8"?>'
    print'<kml xmlns="http://earth.google.com/kml/2.2">'
    print'<Document>'
    print'\t','<name>toptiles.kml</name>'
    print'\t','<Folder>'
    print'\t','\t','<name>toptiles</name>'
    for i in lista:
        print'\t','\t','<Placemark>'
        print'\t','\t','\t','<name>polygon'+str(lista.index(i))+'</name>'
        print'\t','\t','\t','<Polygon>'
        print'\t','\t','\t','\t','<outerBoundaryIs>'
        print'\t','\t','\t','\t','\t','<LinearRing>'
        print'\t','\t','\t','\t','\t','\t','<coordinates>'
        
        print'\t','\t','\t','\t','\t','\t','\t',str(i[0][1])+','+str(i[0][0])+',0'
        print'\t','\t','\t','\t','\t','\t','\t',str(i[1][1])+','+str(i[1][0])+',0'
        print'\t','\t','\t','\t','\t','\t','\t',str(i[2][1])+','+str(i[2][0])+',0'
        print'\t','\t','\t','\t','\t','\t','\t',str(i[3][1])+','+str(i[3][0])+',0'
        print'\t','\t','\t','\t','\t','\t','\t',str(i[0][1])+','+str(i[0][0])+',0'

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
    
    
