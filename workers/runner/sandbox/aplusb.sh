timeout --preserve-status 1 ./timeout -t 1 -m 64000 python3 ./sandbox/aplusb.py < ./sandbox/aplusb.in 2>&1
echo $?
