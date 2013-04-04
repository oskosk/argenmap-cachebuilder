import datetime
import sys
import math

class Coordenada:
	def __init__ (self, lat, lon):
		self.lat = lat
		self.lon = lon
	def __str__(self):
		return str(self.lat)+";"+ str(self.lon)


	

'''
FORMATO DE CADA LINEA DEL LOG:
Separados por \t: fecha (0), z (1), y (2), x (3), referer (4), ip (5), ip proxy (6)

def pasarFechaTime(fecha)
def pasarFecha(fecha)

def cantSegundos()
def cantSegundosPorFecha(fecha)
def cantSegundosPorIntervalo(fechaInicio,fechaFin)

def cantTilesPorFecha(fecha)
def cantTilesPorIntervalo(fechaInicio,fechaFin)

def traficoPorSegundo(fecha)
def pesoPorFecha(fecha)
def pesoPorIntervalo(fechaInicio,fechaFin)


def cantIPsPorFecha(fecha)
def cantIPs()
def cantIPsPorIntervalo(fechaInicio,fechaFin)

def cantReferers()
def cantReferersPorIntervalo(fechaInicio,fechaFin)
'''


def traficoPorSegundo(fecha):
    '''devuelve el trafico por segundo'''
    return pesoPorFecha(fecha)/cantSegundosPorFecha(fecha)

def pesoPorIntervalo(fechaIncio,fechaFin):
    '''devuelve el trafico por intervalo'''
    return pesoPorIntervalo(fechaIncio,fechaFin)/cantSegundosPorIntervalo(fechaIncio,fechaFin)

def pasarFechaTime(fecha):
    '''recibe una fecha en formato '####-##-## ##:##:##' y devuelve un objeto tipo datetime'''
    if fecha[12] == ':':
        fecha = fecha[:11]+'0'+fecha[11:]
    return datetime.datetime(int(fecha[:4]),int(fecha[5:7]),int(fecha[8:10]),int(fecha[11:13]),int(fecha[14:16]),int(fecha[17:19]))

def pasarFecha(fecha):
    '''recibe una fecha en formato '####-##-##' y devuelve un objeto tipo date'''
    return datetime.date(int(fecha[:4]),int(fecha[5:7]),int(fecha[8:10]))

def cantTilesPorFecha(fecha):
    ''' devuelve la cantidad de lineas de log (tiles) por fecha (dia)'''
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

def cantSegundosPorFecha(fecha):
    '''devuelve la cantidad de segundos/momentos distintos por fecha (dia)'''
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

def cantSegundosPorIntervalo(fechaInicio,fechaFin):
    '''Recibe dos fechas en formato '####-##-## ##:##:##' y devuelve la cantidad de segundos/momentos distintos en ese intervalo '''
    listaFecha=[]
    fechaInicio=pasarFechaTime(fechaInicio)
    fechaFin=pasarFechaTime(fechaFin)
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


def cantTilesPorIntervalo(fechaInicio,fechaFin):
    '''Recibe dos fechas en formato '####-##-## ##:##:##' y devuelve la cantidad de lineas de log (tiles) en ese intervalo '''
    c=0
    fechaInicio=pasarFechaTime(fechaInicio)
    fechaFin=pasarFechaTime(fechaFin)
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
            

def cantSegundos():
    ''' Devuelve la cantidad total de segundos/momentos distintos en todo el log'''
    listaFecha=[]
    for x in listaParseada:
        fb=x[0][:19].strip()
        if fb not in listaFecha:
                listaFecha.append(fb)
    return len(listaFecha)


def pesoPorFecha(fecha):
    ''' Recibe una fecha y calcula cantidad de bytes aprox (x8) de trafico llamando a cantTilesPorFecha'''
    return 8*cantTilesPorFecha(fecha)

def pesoPorIntervalo(fechaInicio,fechaFin):
    ''' Recibe dos fechas en formato '####-##-## ##:##:##' y calcula cantidad de bytes aprox (x8) de trafico en el intervalo llamando a cantTilesPorIntervalo'''
    return 8*cantTilesPorIntervalo(fechaInicio,fechaFin)

def cantIPsPorFecha(fecha):
    '''devuelve la cantidad de IPs distintos por fecha (dia)'''
    listaIPs=[]
    corte=False
    for x in listaParseada:
        fb=x[0][:10].strip()
        if fb == fecha:
            if x[5] not in listaIPs:
                listaIp.append(x[5])
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


''' Recibe x, y, z una linea de log. Lo pasa a degrees y devuelve una
instancia de la clase Coordenada. Si x o y se salen de rango, atrapa el
error y devuelve None.'''
def num2deg(xtile, ytile, zoom):
	
	n = 2.0 ** zoom
	lon_deg = xtile / n * 360.0 - 180.0
	division = (1 - 2 * ytile / n)
	try:
		senh = math.sinh(math.pi * division)
		lat_rad = math.atan(senh)
		lat_deg = math.degrees(lat_rad)
		coord = Coordenada(lat_deg, lon_deg)
		return coord

	except OverflowError:
		'''incalculable'''
		return None


'''crea lista_tiles_en_degrees, que contiene diccionarios "cuadrado".
Estos diccionarios tienen las keys NW, NE, SE, SW con valores de clase
Coordenada, y la key zoom, con valor zoom (int)'''
def pasarLogAdegrees ():
	lista_tiles_en_degrees = []
	for x in listaParseada:
		esq_NW = num2deg(float(x[3]), float(x[2]), float(x[1]))
		esq_NE = num2deg(float(x[3]), float(x[2])+1, float(x[1]))
		esq_SE = num2deg(float(x[3])+1, float(x[2])+1 , float(x[1]))
		esq_SW = num2deg(float(x[3])+1, float(x[2]), float(x[1]))
		if (esq_NW != 0 and esq_SE != 0 and esq_NE!=0 and esq_SW!=0):
			cuadrado = {'NW': esq_NW,'NE': esq_NE,'SE': esq_SE,'SW': esq_SW, 'zoom': int(x[1])}
			lista_tiles_en_degrees.append(cuadrado)
	return lista_tiles_en_degrees


def ordenarCuadradosPorZoomEnUnDic (lista_tiles_en_degrees):
	dicPorZoom = {}
	for cuadrado in lista_tiles_en_degrees:
		if cuadrado['zoom'] not in dicPorZoom.keys():
			dicPorZoom[cuadrado['zoom']] = []
		dicPorZoom[cuadrado['zoom']].append(cuadrado)
	return dicPorZoom
		
		
		
#~FUNCION TEST 
'''def print_test (booleano):
	if booleano:
		print 'OK'
	else:
		print 'ERROR'
	return 0
'''
#~ PRUEBAS 
f = open('log.txt')
a = f.readlines()
f.close()
listaParseada = [x.split('\t') for x in a]

#~ print_test (cantReferersPorIntervalo("2013-03-23 22:30:30", "2013-03-23 22:35:00")== 2)
#~ print_test (cantIPsPorIntervalo("2013-03-23 22:26:05", "2013-03-23 22:31:28")== 3)

#~ MANEJO DE LLAMADAS POR PARAMETRO
#~ 
#~ largo 13
#~ funciones = ["cantSegundos","cantSegundosPorFecha",
#~ "cantSegundosPorIntervalo","cantTilesPorFecha","cantTilesPorIntervalo",
#~ "traficoPorSegundo","pesoPorFecha", "pesoPorIntervalo","cantIPsPorFecha","cantIPs",
#~ "cantIPsPorIntervalo","cantReferers","cantReferersPorIntervalo"]
#~ 
#~ if sys.argv[1] not in funciones:
	#~ print "ERROR: funcion no existe"
#~ else:
	#~ if sys.argv[1]==funciones[0]:
		#~ if len(sys.argv) ==2:
			#~ print cantSegundos()
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 0)"
						#~ 
	#~ if sys.argv[1]==funciones[1]:
		#~ if len(sys.argv) ==3:
			#~ print cantSegundosPorFecha(sys.argv[2],sys.argv[3])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 1)"
						#~ 
	#~ if sys.argv[1]==funciones[2]:
		#~ if len(sys.argv) ==4:
			#~ print cantSegundosPorIntervalo(sys.argv[2],sys.argv[3])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 2)"
						#~ 
	#~ if sys.argv[1]==funciones[3]:
		#~ if len(sys.argv) ==3:
			#~ print cantTilesPorFecha(sys.argv[2])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 1)"
						#~ 
	#~ if sys.argv[1]==funciones[4]:
		#~ if len(sys.argv) ==4:
			#~ print cantTilesPorIntervalo(sys.argv[2],sys.argv[3])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 2)"
						#~ 
	#~ if sys.argv[1]==funciones[5]:
		#~ if len(sys.argv) ==3:
			#~ print traficoPorSegundo(sys.argv[2])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 1)"
						#~ 
	#~ if sys.argv[1]==funciones[6]:
		#~ if len(sys.argv) ==3:
			#~ print pesoPorFecha(sys.argv[2])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 1)"
						#~ 
	#~ if sys.argv[1]==funciones[7]:
		#~ if len(sys.argv) ==4:
			#~ print pesoPorIntervalo(sys.argv[2],sys.argv[3])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 2)"
						#~ 
	#~ if sys.argv[1]==funciones[8]:
		#~ if len(sys.argv) ==3:
			#~ print cantIPsPorFecha(sys.argv[2])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 1)"
			#~ 
	#~ if sys.argv[1]==funciones[9]:
		#~ if len(sys.argv) ==2:
			#~ print cantIPs()
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 0)"
			#~ 
	#~ if sys.argv[1]==funciones[10]:
		#~ if len(sys.argv) ==4:
			#~ print cantIPsPorIntervalo(sys.argv[2],sys.argv[3])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 2)"
#~ 
	#~ if sys.argv[1]==funciones[11]:
		#~ if len(sys.argv) ==2:
			#~ print cantReferers()
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 0)"
#~ 
	#~ if sys.argv[1]==funciones[12]:
		#~ if len(sys.argv) ==4:
			#~ print cantReferersPorIntervalo(sys.argv[2],sys.argv[3])
		#~ else:
			#~ print "ERROR: cantidad de parametros erronea (debe recibir 2)"
