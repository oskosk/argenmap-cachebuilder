import datetime
import sys
import math
import globalmaptiles
'''FORMATO DE CADA LINEA DEL LOG:
Separados por \t: fecha (0), z (1), y (2), x (3), referer (4), ip (5), ip proxy (6)'''

class Coordenada:
	def __init__ (self, lat, lon):
		self.lat = lat
		self.lon = lon
	def __str__(self):
		return str(self.lat)+";"+ str(self.lon)



'''devuelve el trafico por segundo'''
def traficoPorSegundo(fecha):
    return pesoPorFecha(fecha)/cantSegundosPorFecha(fecha)

'''devuelve el trafico por intervalo'''
def pesoPorIntervalo(fechaIncio,fechaFin):
	return pesoPorIntervalo(fechaIncio,fechaFin)/cantSegundosPorIntervalo(fechaIncio,fechaFin)


'''recibe una fecha en formato '####-##-## ##:##:##' y devuelve un objeto tipo datetime'''
def pasarFechaTime(fecha):
	try:
		if fecha[12] == ':':
			fecha = fecha[:11]+'0'+fecha[11:]
		return datetime.datetime(int(fecha[:4]),int(fecha[5:7]),int(fecha[8:10]),int(fecha[11:13]),int(fecha[14:16]),int(fecha[17:19]))
	except IndexError:
		return False
	except TypeError:
		return False
	except ValueError:
		return False


'''recibe una fecha en formato '####-##-##' y devuelve un objeto tipo date'''
def pasarFecha(fecha):
	try:
		return datetime.date(int(fecha[:4]),int(fecha[5:7]),int(fecha[8:10]))
	except IndexError:
		return False
	except TypeError:
		return False
	except ValueError:
		return False


''' devuelve la cantidad de lineas de log (tiles) por fecha (dia)'''
def cantTilesPorFecha(fecha):
	if pasarFecha(fecha) == False:
		return None
	c=0
	corte=False
	for x in listaParseada:
		fb=x[0][:10].strip()
		if fb == fecha:
			c+=1
			corte=True
		if fb!=fecha and corte==True:
			return c
	return c


'''devuelve la cantidad de segundos/momentos distintos por fecha (dia)'''
def cantSegundosPorFecha(fecha):
	if pasarFecha(fecha) == False:
		return None
	listaFecha=[]
	corte=False
	for x in listaParseada:
		fb=x[0][:19].strip()
		f=x[0][:10].strip()
		if fb not in listaFecha and f == fecha:
			corte=True
			listaFecha.append(fb)
		if corte==True and f!=fecha:
			return len(listaFecha)
	return len(listaFecha) 


'''Recibe dos fechas en formato '####-##-## ##:##:##' y devuelve la 
    cantidad de segundos/momentos distintos en ese intervalo '''
def cantSegundosPorIntervalo(fechaInicio,fechaFin):
    listaFecha=[]
    fechaInicio=pasarFechaTime(fechaInicio)
    fechaFin=pasarFechaTime(fechaFin)
    if not fechaInicio or not fechaFin:
		return False
    '''swapea las fechas por si estan desordenadas '''
    if fechaInicio > fechaFin:
        aux = fechaInicio
        fechaInicio = fechaFin
        fechaFin = aux
        
    for x in listaParseada:
        fb=pasarFechaTime(x[0][:19].strip())
        if fb >= fechaInicio and fb < fechaFin and fb not in listaFecha:
            listaFecha.append(fb)
        if fb == fechaFin:
            return len(listaFecha)
    return len(listaFecha)    


'''Recibe dos fechas en formato '####-##-## ##:##:##' y devuelve la cantidad de lineas de log (tiles) en ese intervalo '''
def cantTilesPorIntervalo(fechaInicio,fechaFin):
    c=0
    fechaInicio=pasarFechaTime(fechaInicio)
    fechaFin=pasarFechaTime(fechaFin)
    if not fechaInicio or not fechaFin:
		return False
    '''swapea las fechas por si estan desordenadas '''
    if fechaInicio > fechaFin:
        aux = fechaInicio
        fechaInicio = fechaFin
        fechaFin = aux
        
    for x in listaParseada:
        fb=pasarFechaTime(x[0][:19].strip()) 
        if ((fb >= fechaInicio) and (fb < fechaFin)):
            c+=1
        if fb == fechaFin:
            return c
    return c
            
            
''' Devuelve la cantidad total de segundos/momentos distintos en todo el log'''
def cantSegundos():
    listaFecha=[]
    for x in listaParseada:
        fb=x[0][:19].strip()
        if fb not in listaFecha:
                listaFecha.append(fb)
    return len(listaFecha)


''' Recibe una fecha y calcula cantidad de bytes aprox (x8) de trafico 
llamando a cantTilesPorFecha'''
def pesoPorFecha(fecha):
	cantTiles =cantTilesPorFecha(fecha)
	if cantTiles== None:
		return None
	return 8*cantTiles


''' Recibe dos fechas en formato '####-##-## ##:##:##' y calcula cantidad 
de bytes aprox (x8) de trafico en el intervalo llamando a cantTilesPorIntervalo'''
def pesoPorIntervalo(fechaInicio,fechaFin):
	cantTiles = cantTilesPorIntervalo(fechaInicio,fechaFin)
	if cantTiles==None:
		return None
	return 8*cantTiles

'''devuelve la cantidad de IPs distintos por fecha (dia)'''
def cantIPsPorFecha(fecha):
	if pasarFecha(fecha) == False:
		return False
	listaIPs=[]
	corte=False
	for x in listaParseada:
		fb=x[0][:10].strip()
		if fb == fecha:
			if x[5] not in listaIPs:
				listaIPs.append(x[5])
				corte=True
		if fb!=fecha and corte==True:
			return len(listaIPs)
	return len(listaIPs)

def cantIPs():
    '''devuelve la cantidad de IPs distintos en todo el log'''
    listaIPs=[]
    for x in listaParseada:
        if x[5] not in listaIPs:
            listaIPs.append(x[5])
    return len(listaIPs)

def cantReferers():
    '''devuelve la cantidad de refers distintos en todo el log'''
    listaRef=[]
    for x in listaParseada:
        if x[4] not in listaRef:
            listaRef.append(x[4])
    return len(listaRef)

'''Recibe dos fechas en formato '####-##-## ##:##:##' y devuelve la 
cantidad de referers distintos en ese intervalo '''
def cantReferersPorIntervalo(fechaInicio,fechaFin):
    listaReferers=[]
    fechaInicio=pasarFechaTime(fechaInicio)
    fechaFin=pasarFechaTime(fechaFin)
    if not fechaInicio or not fechaFin:
		return False
    '''swapea las fechas por si estan desordenadas '''
    if fechaInicio > fechaFin:
        aux = fechaInicio
        fechaInicio = fechaFin
        fechaFin = aux
    
    for x in listaParseada:
        fb = pasarFechaTime(x[0][:19].strip())
        if fb >= fechaInicio and fb < fechaFin and x[4] not in listaReferers:
            listaReferers.append(x[4])
        if fb > fechaFin:
            return len(listaReferers)
    return len(listaReferers) 

'''Recibe dos fechas en formato '####-##-## ##:##:##' y devuelve la 
cantidad de IPs distintos en ese intervalo '''
def cantIPsPorIntervalo(fechaInicio,fechaFin):
    listaIPs=[]
    fechaInicio=pasarFechaTime(fechaInicio)
    fechaFin=pasarFechaTime(fechaFin)
    if not fechaInicio or not fechaFin:
		return False
    '''swapea las fechas por si estan desordenadas '''
    if fechaInicio > fechaFin:
        aux = fechaInicio
        fechaInicio = fechaFin
        fechaFin = aux
    
    for x in listaParseada:
        fb = pasarFechaTime(x[0][:19].strip())
        if fb >= fechaInicio and fb < fechaFin and x[5] not in listaIPs:
            listaIPs.append(x[5])
        if fb > fechaFin:
            return len(listaIPs)
    return len(listaIPs) 


'''crea lista_tiles_en_degrees POR INTERVALO, que contiene diccionarios "cuadrado".
Estos diccionarios tienen las keys NW, NE, SE, SW con valores de clase
Coordenada, y la key zoom, con valor zoom (int)'''
def pasarLogAdegreesPorIntervalo (fechaInicio, fechaFin):
	fechaInicio = pasarFechaTime(fechaInicio)
	fechaFin = pasarFechaTime(fechaFin)
	if not fechaInicio or not fechaFin:
		return False
	'''swapea las fechas por si estan desordenadas '''
	if fechaInicio > fechaFin:
		aux = fechaInicio
		fechaInicio = fechaFin
		fechaFin = aux

	lista_tiles_en_degrees = []
	for x in listaParseada:
		fb = pasarFechaTime(x[0][:19].strip())
		if fb >= fechaInicio and fb < fechaFin:
			tileBounds = globalmaptiles.GlobalMercator()
			bounds = tileBounds.TileLatLonBounds(float(x[2]), float(x[3]), float(x[1])) # tupla
			#~ ( minLat, minLon, maxLat, maxLon )
			esq_NW = Coordenada(bounds[2], bounds[1]) # maxLat y MinLon
			esq_SW = Coordenada(bounds[0], bounds[1]) # minLat y MinLon
			esq_NE = Coordenada(bounds[2], bounds[3]) # maxLat y MaxLon
			esq_SE = Coordenada(bounds[0], bounds[3]) # minLat y MaxLon
			
			cuadrado = {'NW': esq_NW,'SW': esq_SW,'NE': esq_NE,'SE': esq_SE, 
			'zoom': int(x[1]),'nombre': (x[2], x[3], x[1])}
			
			lista_tiles_en_degrees.append(cuadrado)
		
		if fb > fechaFin:
			return lista_tiles_en_degrees
	return lista_tiles_en_degrees


'''crea lista_tiles_en_degrees, que contiene diccionarios "cuadrado".
Estos diccionarios tienen las keys NW, NE, SE, SW con valores de clase
Coordenada, y la key zoom, con valor zoom (int)'''
def pasarLogAdegrees ():
	lista_tiles_en_degrees = []
	for x in listaParseada:
		tileBounds = globalmaptiles.GlobalMercator()
		bounds = tileBounds.TileLatLonBounds(float(x[2]), float(x[3]), float(x[1])) # tupla
		#~ ( minLat, minLon, maxLat, maxLon )
		esq_NW = Coordenada(bounds[2], bounds[1]) # maxLat y MinLon
		esq_SW = Coordenada(bounds[0], bounds[1]) # minLat y MinLon
		esq_NE = Coordenada(bounds[2], bounds[3]) # maxLat y MaxLon
		esq_SE = Coordenada(bounds[0], bounds[3]) # minLat y MaxLon
		cuadrado = {'NW': esq_NW,'SW': esq_SW,'NE': esq_NE,'SE': esq_SE, 'zoom': int(x[1]),'nombre': (x[2], x[3], x[1])}
		lista_tiles_en_degrees.append(cuadrado)
	return lista_tiles_en_degrees

'''Recibe la lista de cuadrados que devuelve pasarLogAdegrees y la ordena
por zoom en un diccionario. El diccionario queda tipo {zoom1: lista de cuadrados,
zoom2: lista de cuadrados...}. Devuelve el diccionario'''
def ordenarCuadradosPorZoomEnUnDic (lista_tiles_en_degrees):
	dicPorZoom = {}
	for cuadrado in lista_tiles_en_degrees:
		if cuadrado['zoom'] not in dicPorZoom.keys():
			dicPorZoom[cuadrado['zoom']] = []
		dicPorZoom[cuadrado['zoom']].append(cuadrado)
	return dicPorZoom
		
		
		
f = open('log.txt')
a = f.readlines()
f.close()
listaParseada = [x.split('\t') for x in a]




#~ MANEJO DE LLAMADAS POR PARAMETRO

funciones = ["cantSegundos","cantSegundosPorFecha",
"cantSegundosPorIntervalo","cantTilesPorFecha","cantTilesPorIntervalo",
"traficoPorSegundo","pesoPorFecha", "pesoPorIntervalo","cantIPsPorFecha","cantIPs",
"cantIPsPorIntervalo","cantReferers","cantReferersPorIntervalo"] #largo 13

if sys.argv[1]==funciones[0]:
	if len(sys.argv) ==2:
		print cantSegundos()
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 0)"
					
if sys.argv[1]==funciones[1]:
	if len(sys.argv) ==3:
		ans = cantSegundosPorFecha(sys.argv[2])
		if ans == None:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 1)"
					
if sys.argv[1]==funciones[2]:
	if len(sys.argv) ==4:
		ans= cantSegundosPorIntervalo(sys.argv[2],sys.argv[3])
		if not ans:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 2)"
					
if sys.argv[1]==funciones[3]:
	if len(sys.argv) ==3:
		ans= cantTilesPorFecha(sys.argv[2])
		if not ans:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 1)"
					
if sys.argv[1]==funciones[4]:
	if len(sys.argv) ==4:
		ans= cantTilesPorIntervalo(sys.argv[2],sys.argv[3])
		if not ans:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 2)"
					
if sys.argv[1]==funciones[5]:
	if len(sys.argv) ==3:
		ans= traficoPorSegundo(sys.argv[2])
		if not ans:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 1)"
					
if sys.argv[1]==funciones[6]:
	if len(sys.argv) ==3:
		ans= pesoPorFecha(sys.argv[2])
		if ans == None:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 1)"
					
if sys.argv[1]==funciones[7]:
	if len(sys.argv) ==4:
		ans= pesoPorIntervalo(sys.argv[2],sys.argv[3])
		if ans == None:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 2)"
					
if sys.argv[1]==funciones[8]:
	if len(sys.argv) ==3:
		ans= cantIPsPorFecha(sys.argv[2])
		if not ans:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 1)"
		
if sys.argv[1]==funciones[9]:
	if len(sys.argv) ==2:
		print cantIPs()
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 0)"
		
if sys.argv[1]==funciones[10]:
	if len(sys.argv) ==4:
		ans= cantIPsPorIntervalo(sys.argv[2],sys.argv[3])
		if not ans:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 2)"

if sys.argv[1]==funciones[11]:
	if len(sys.argv) ==2:
		print cantReferers()
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 0)"

if sys.argv[1]==funciones[12]:
	if len(sys.argv) ==4:
		ans= cantReferersPorIntervalo(sys.argv[2],sys.argv[3])
		if not ans:
			print "Formato de fecha invalido"
		else:
			print ans
	else:
		print "ERROR: cantidad de parametros erronea (debe recibir 2)"
