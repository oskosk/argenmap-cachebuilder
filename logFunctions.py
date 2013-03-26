import datetime
'''
def pasarFechaTime(fecha)
def pasarFecha(fecha)

def cantSegundos()
def cantSegundosPorFecha(fecha)
def cantSegundosPorIntervalo(fechaInicio,fechaFin)

def cantTilesPorFecha(fecha)
def cantTilesPorIntervalo(fechaInicio,fechaFin)

def pesoPorSegundo(fecha)
def pesoPorFecha(fecha)
def pesoPorIntervalo(fechaInicio,fechaFin)


def cantIpPorFecha(fecha)
def cantIPs()

def cantReferers()
'''


def pesoPorSegundo(fecha):
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

def cantIpPorFecha(fecha):
    '''devuelve la cantidad de IPs distintos por fecha (dia)'''
    listaIp=[]
    corte=False
    for x in listaParseada:
        fb=x[0][:10].strip()
        if fb == fecha:
            if x[3] not in listaIp:
                listaIp.append(x[3])
                corte=True
        if fb!=fecha and corte==True:
            return len(listaIp)
    return len(listaIp)

def cantIPs():
    '''devuelve la cantidad de IPs distintos en todo el log'''
    listaIp=[]
    for x in listaParseada:
        if x[3] not in listaIp:
            listaIp.append(x[3])
    return len(listaIp)

def cantReferers():
    '''devuelve la cantidad de refers distintos en todo el log'''
    listaRef=[]
    for x in listaParseada:
        if x[2] not in listaRef:
            listaRef.append(x[2])
    return len(listaRef)


f = open('log.txt')
a = f.readlines()
f.close()
listaParseada = [x.split('\t') for x in a]
print cantSegundosPorIntervalo("2013-03-23 22:30:30", "2014-03-23 22:32:07")

