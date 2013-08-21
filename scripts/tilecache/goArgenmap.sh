#!/bin/bash
# provincias_lim
BBOX=-8240965.645900,-30240971.958400,-2785563.741300,-2484886.405700
#Antártida
#BBOX=-8237642.317555556,-59613540.1989548,-2782987.2694,-8399737.888649108
date
python ./tilecache_seed.py  --bbox=$BBOX capabaseargenmap 0 31
date
