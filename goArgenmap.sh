#!/bin/bash
BBOX=-20037508.34,-20037508.34,0,0
BBOX=-8240965.645900,-30240971.958400,-2785563.741300,-2484886.405700
date
python ./tilecache_seed.py  --bbox=$BBOX capabaseargenmap 0 31
date
