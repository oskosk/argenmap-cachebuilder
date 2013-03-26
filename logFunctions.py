



def pesoPorSegundo(fecha):
    '''devuelve el trafico por segundo'''
    return pesoPorFecha(fecha)/cantSegundosPorFecha(fecha)

def cantTilesPorFecha(fecha):
    c=0
    corte=False
    for x in listaParseada:
        fb=x[0][:10]
        if fb == fecha:
            c+=1
            corte=True
        if fb!=fecha and corte==True:
            return c
            break
    return c

def cantSegundosPorFecha(fecha):
    listaFecha=[]
    fb=x[0][:10]
    for x in listaParseada:
        fb=x[0][:19]
        f=x[0][:10]
        if fb not in listaFecha and f == fecha:
                listaFecha.append(fb)
    return len(listaFecha)


def cantSegundos():
    listaFecha=[]
    for x in listaParseada:
        fb=x[0][:19]
        if fb not in listaFecha:
                listaFecha.append(fb)
    return len(listaFecha)


def pesoPorFecha(fecha):
    return 8*cantTilesPorFecha(fecha)


def cantIpPorFecha(fecha):
    listaIp=[]
    corte=False
    for x in listaParseada:
        fb=x[0][:10]
        if fb == fecha:
            if x[3] not in listaIp:
                listaIp.append(x[3])
                corte=True
        if fb!=fecha and corte==True:
            return len(listaIp)
            break
    return len(listaIp)

def cantPorIP():
    listaIp=[]
    for x in listaParseada:
        if x[3] not in listaIp:
            listaIp.append(x[3])
    return len(listaIp)

def cantPorReferer():
    listaRef=[]
    for x in listaParseada:
        if x[2] not in listaRef:
            listaRef.append(x[2])
    return len(listaRef)


f = open('log.txt')
a = f.readlines()
f.close()
listaParseada = [x.split('\t') for x in a]
print cantIpPorFecha("2013-03-20")
