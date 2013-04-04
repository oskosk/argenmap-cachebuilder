def crearArchivoXML(lista, nro_zoom):
    nombre = 'tiles'+str(nro_zoom)+'.kml'
    f = open (nombre, "w")
    f.write('<?xml version="1.0" encoding="UTF-8"?>')
    f.write('<kml xmlns="http://earth.google.com/kml/2.2">')
    f.write('<Document>')
    f.write('\t','<name>',nombre,'</name>')
    f.write('\t','<Folder>')
    f.write('\t','\t','<name>toptiles</name>')
    lista_sin_repetir = []
    for i in lista:
		if i not in lista_sin_repetir:
			lista_sin_repetir.append(i)
			f.write('\t','\t','<Placemark>')
			f.write('\t','\t','\t','<name>polygon'+str(i['nombre'])+'</name>')
			#~ f.write('\t','\t','\t','<name>polygon'+str(lista.index(i))+'</name>'
			f.write('\t','\t','\t','<Polygon>')
			f.write('\t','\t','\t','\t','<outerBoundaryIs>')
			f.write('\t','\t','\t','\t','\t','<LinearRing>')
			f.write('\t','\t','\t','\t','\t','\t','<coordinates>')
			
			f.write('\t','\t','\t','\t','\t','\t','\t',str(i['NW'].lon)+','+str(i['NW'].lat)+',0')
			f.write('\t','\t','\t','\t','\t','\t','\t',str(i['NE'].lon)+','+str(i['NE'].lat)+',0')
			f.write('\t','\t','\t','\t','\t','\t','\t',str(i['SE'].lon)+','+str(i['SE'].lat)+',0')
			f.write('\t','\t','\t','\t','\t','\t','\t',str(i['SW'].lon)+','+str(i['SW'].lat)+',0')
			f.write('\t','\t','\t','\t','\t','\t','\t',str(i['NW'].lon)+','+str(i['NW'].lat)+',0')

			f.write('\t','\t','\t','\t','\t','\t','</coordinates>')
			f.write('\t','\t','\t','\t','\t','</LinearRing>')
			f.write('\t','\t','\t','\t','</outerBoundaryIs>')
			f.write('\t','\t','\t','</Polygon>')
			f.write('\t','\t','</Placemark>')
    f.write('\t','</Folder>')
    f.write('</Document>')
    f.write('</kml>')
    f.close()
