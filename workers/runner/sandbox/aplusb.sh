timeout --preserve-status 1 ./timeout -t 1 -m 64000 python3 ./sandbox/aplusb.py 2>&1
echo $?